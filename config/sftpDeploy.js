/**
 * SFTP deploy helper.
 *
 * Reemplaza a gulp-sftp-up4, que dependía de ssh2-streams@0.2.1 (abandonado) y
 * rompía en Node >= 23 porque usa util.isDate, removida en esa versión (DEP0047).
 * Acá usamos ssh2-sftp-client (ssh2 v1.x), que no toca APIs removidas.
 *
 * Mantiene la misma semántica de destino que el plugin anterior:
 *   remoto = remotePath + "/" + <ruta del archivo relativa al base del glob>
 * y crea los directorios padre que falten.
 */
var path = require("path");
var gulp = require("gulp");
var Client = require("ssh2-sftp-client");
var { Writable } = require("stream");
var { pipeline } = require("stream/promises");

// Windows -> POSIX para rutas remotas
function toPosix(p) {
  return p.split(path.sep).join("/");
}

// Un glob sin caracteres mágicos es una ruta literal: si no existe, es un error
// de configuración y no queremos que pase en silencio (allowEmpty lo silenciaría).
function warnMissingLiteralGlobs(globs) {
  var fs = require("fs");

  globs
    .filter(function (g) {
      return typeof g === "string" && g.charAt(0) !== "!" && !/[*?[\]{}]/.test(g);
    })
    .filter(function (g) {
      return !fs.existsSync(g);
    })
    .forEach(function (g) {
      console.warn("SFTP: OJO, no existe y no se va a subir: " + g);
    });
}

/**
 * Resuelve los globs con gulp.src y devuelve los archivos (sin leer contenido).
 * read:false mantiene el uso de memoria plano: subimos leyendo del disco.
 */
async function collectFiles(globs, srcOptions) {
  var files = [];

  warnMissingLiteralGlobs(globs);

  await pipeline(
    gulp.src(globs, Object.assign({ base: "./", read: false, allowEmpty: true }, srcOptions)),
    new Writable({
      objectMode: true,
      write: function (file, _enc, cb) {
        // gulp.src emite directorios; el plugin anterior también los ignoraba
        if (!file.isDirectory()) {
          files.push(file);
        }
        cb();
      },
    })
  );

  return files;
}

/**
 * Sube todos los archivos que matcheen los globs.
 *
 * @param {string[]} globs        Patrones para gulp.src
 * @param {object}   sftpConfig   { host, port, user/username, pass/password, remotePath }
 * @param {object}   [srcOptions] Overrides para gulp.src (ej: { base: "./" })
 * @returns {Promise<number>}     Cantidad de archivos subidos
 */
async function deploy(globs, sftpConfig, srcOptions) {
  if (!sftpConfig || !sftpConfig.host) {
    throw new Error("sftpConfig.host es requerido");
  }

  var files = await collectFiles(globs, srcOptions);

  if (!files.length) {
    console.log("SFTP: ningún archivo coincide con los patrones, nada que subir.");
    return 0;
  }

  var remoteRoot = toPosix(sftpConfig.remotePath || "/");
  var client = new Client();

  await client.connect({
    host: sftpConfig.host,
    port: Number(sftpConfig.port) || 22,
    username: sftpConfig.username || sftpConfig.user,
    password: sftpConfig.password || sftpConfig.pass,
    readyTimeout: Number(sftpConfig.timeout) || 20000,
  });

  console.log("SFTP: conectado a " + sftpConfig.host + " -> " + remoteRoot);

  var createdDirs = new Set();
  var uploaded = 0;

  try {
    for (var file of files) {
      var remoteFile = path.posix.join(remoteRoot, toPosix(file.relative));
      var remoteDir = path.posix.dirname(remoteFile);

      // mkdir recursivo, idempotente; cacheado para no repetir el round-trip
      if (!createdDirs.has(remoteDir)) {
        await client.mkdir(remoteDir, true);
        createdDirs.add(remoteDir);
      }

      await client.put(file.path, remoteFile);
      uploaded++;
      console.log("SFTP: subido " + toPosix(file.relative) + " (" + uploaded + "/" + files.length + ")");
    }
  } finally {
    await client.end();
  }

  console.log("SFTP: listo, " + uploaded + " archivo(s) subido(s) a " + remoteRoot);
  return uploaded;
}

/**
 * Elimina archivos o directorios remotos
 */
async function remove(filePath, sftpConfig) {
  var remoteRoot = toPosix(sftpConfig.remotePath || "/");
  var remotePath = path.posix.join(
    remoteRoot,
    toPosix(filePath)
  );

  var client = new Client();
  await client.connect({
    host: sftpConfig.host,
    port: Number(sftpConfig.port) || 22,
    username: sftpConfig.username || sftpConfig.user,
    password: sftpConfig.password || sftpConfig.pass,
    readyTimeout: Number(sftpConfig.timeout) || 20000,
  });

  console.log("SFTP: conectado a " + sftpConfig.host + " -> " + remoteRoot);

  try {
    var exists = await client.exists(remotePath);
    if (!exists) {
      console.log("SFTP: no existe " + remotePath + ", nada que eliminar.");
      return;
    }

    if (exists === "d") {
      await client.rmdir(remotePath, true);
      console.log("SFTP: directorio eliminado " + remotePath);
    } else {
      await client.delete(remotePath);
      console.log("SFTP: archivo eliminado " + remotePath);
    }
  } finally {
    await client.end();
  }
}

/**
 * Extracts the current asset hash from the remote hash.php file.
 *
 * @param {object} client
 * @param {string} remotePath
 * @returns {Promise<string>}
 */
async function getCurrentHash(client, remotePath) {
  var content = await client.get(remotePath);

  if (!Buffer.isBuffer(content)) {
    content = Buffer.from(content);
  }

  var hashFile = content.toString("utf8");

  var match = hashFile.match(
    /define\s*\(\s*['"]hash['"]\s*,\s*['"]([^'"]+)['"]\s*\)/
  );

  if (!match) {
    throw new Error(
      "SFTP: could not find the hash constant in " + remotePath
    );
  }

  return match[1];
}

/**
 * Extracts the hash information from a dist asset filename.
 *
 * Expected format:
 *   filename.hash.extension
 *
 * @param {string} filename
 * @returns {object|null}
 */
function parseHashFile(filename) {
  var match = filename.match(/^(.+)\.([^.]+)\.([^.]+)$/);

  if (!match) {
    return null;
  }

  return {
    name: match[1],
    hash: match[2],
    extension: match[3],
  };
}

/**
 * Removes old hashed assets from the remote dist directory,
 * keeping files with the current or previous hash.
 *
 * Files explicitly listed in hashFilesToExclude are never removed,
 * regardless of their hash.
 *
 * @param {object} sftpConfig
 * @param {string[]} hashFilesToExclude
 * @returns {Promise<void>}
 */
async function cleanOldHash(sftpConfig, hashFilesToExclude = []) {
  var remoteRoot = toPosix(sftpConfig.remotePath || "/");

  var hashFilePath = path.posix.join(
    remoteRoot,
    "functions/project/deploy/hash.php"
  );

  var distPath = path.posix.join(
    remoteRoot,
    "dist"
  );

  var client = new Client();

  await client.connect({
    host: sftpConfig.host,
    port: Number(sftpConfig.port) || 22,
    username: sftpConfig.username || sftpConfig.user,
    password: sftpConfig.password || sftpConfig.pass,
    readyTimeout: Number(sftpConfig.timeout) || 20000,
  });

  console.log(
    "SFTP: conectado a " + sftpConfig.host + " -> " + remoteRoot
  );

  try {
    // Get current hash from hash.php.
    var currentHash = await getCurrentHash(
      client,
      hashFilePath
    );

    console.log("SFTP: current hash: " + currentHash);

    // Get files from dist.
    var distFiles = await client.list(distPath);
    var hashedFiles = [];

    for (var file of distFiles) {
      // Only process files.
      if (file.type !== "-") {
        continue;
      }

      var parsed = parseHashFile(file.name);

      // Ignore files that don't follow the hashed asset pattern.
      if (!parsed) {
        continue;
      }

      hashedFiles.push({
        name: file.name,
        hash: parsed.hash,
        assetName: parsed.name,
        extension: parsed.extension,
        modifyTime: Number(file.modifyTime) || 0,
      });
    }

    if (!hashedFiles.length) {
      console.log("SFTP: no hashed files found in dist.");
      return;
    }

    // Group files by hash and get the latest modification date
    // for each hash.
    var hashDates = {};

    for (var hashedFile of hashedFiles) {
      if (
        !hashDates[hashedFile.hash] ||
        hashedFile.modifyTime > hashDates[hashedFile.hash]
      ) {
        hashDates[hashedFile.hash] = hashedFile.modifyTime;
      }
    }

    // Find the most recent hash excluding the current hash.
    var previousHash = null;
    var previousHashDate = -1;

    for (var hash in hashDates) {
      if (hash === currentHash) {
        continue;
      }

      if (hashDates[hash] > previousHashDate) {
        previousHash = hash;
        previousHashDate = hashDates[hash];
      }
    }

    if (previousHash) {
      console.log("SFTP: previous hash: " + previousHash);
    } else {
      console.log("SFTP: no previous hash found.");
    }

    // Group files by asset.
    var assets = {};

    for (var hashedFile of hashedFiles) {
      var assetKey =
        hashedFile.assetName + "." + hashedFile.extension;

      if (!assets[assetKey]) {
        assets[assetKey] = [];
      }

      assets[assetKey].push(hashedFile);
    }

    var filesToKeep = new Set();
    var filesToRemove = [];

    // Decide what to keep for every asset.
    for (var assetKey in assets) {
      var assetFiles = assets[assetKey];

      for (var assetFile of assetFiles) {
        // Files explicitly excluded from deletion are always kept.
        if (hashFilesToExclude.includes(assetFile.name)) {
          filesToKeep.add(assetFile.name);
          continue;
        }

        // Keep files belonging to the current or previous hash.
        if (
          assetFile.hash === currentHash ||
          assetFile.hash === previousHash
        ) {
          filesToKeep.add(assetFile.name);
        }
      }
    }

    // Determine which hashed files can be removed.
    for (var hashedFile of hashedFiles) {
      if (!filesToKeep.has(hashedFile.name)) {
        filesToRemove.push(hashedFile);
      }
    }

    console.log("");
    console.log("SFTP: hash cleanup summary");
    console.log("SFTP: current hash: " + currentHash);
    console.log(
      "SFTP: previous hash: " + (previousHash || "none")
    );
    console.log(
      "SFTP: files to keep: " + filesToKeep.size
    );
    console.log(
      "SFTP: files to remove: " + filesToRemove.length
    );

    if (!filesToRemove.length) {
      console.log("SFTP: nothing to remove.");
      return;
    }

    console.log("");
    console.log("SFTP: removing old hash files:");

    for (var fileToRemove of filesToRemove) {
      var remoteFile = path.posix.join(
        distPath,
        fileToRemove.name
      );

      await client.delete(remoteFile);

      console.log(
        "SFTP: removed " + fileToRemove.name
      );
    }

    console.log("");
    console.log(
      "SFTP: hash cleanup completed. " +
      filesToRemove.length +
      " file(s) removed."
    );
  } finally {
    await client.end();
  }
}

module.exports = { deploy: deploy, remove: remove, cleanOldHash: cleanOldHash, collectFiles: collectFiles, toPosix: toPosix };
