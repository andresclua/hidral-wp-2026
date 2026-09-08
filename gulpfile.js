var gulp = require("gulp");
var minimist = require('minimist');

// SFTP helper (ssh2-sftp-client). Reemplaza a gulp-sftp-up4, que rompe en Node >= 23.
var { deploy, remove, cleanOldHash } = require('./config/sftpDeploy.js');

// Import SFTP configurations
var { devSFTPConfig, stageSFTPConfig, prodSFTPConfig, filesToExclude, hashFileExclude, hashFilesToExclude } = require('./config/sftpConfig.js');

var args = minimist(process.argv.slice(2), {
  string: ['env', 'path', 'hash']
});

// Get SFTP config based on environment ( based on task argument  --env=dev/stage/production )
function getSFTPConfig(env) {
  switch(env) {
    case 'dev':
      return devSFTPConfig;
    case 'stage':
      return stageSFTPConfig;
    case 'production':
      return prodSFTPConfig;
    default:
      throw new Error("Unknown environment: " + env);
  }
}

// define environment
//! this is a must or args.env is undefined
if(args.dev){
  args.env = 'dev';
} else if (args.stage) {
  args.env = 'stage';
} else if (args.production) {
  args.env = 'production';
}

// Resolve config + log env, shared by every deploy task
function resolveEnv() {
  var env = args.env;
  console.log('Environment:', env);
  return getSFTPConfig(env);
}

var { exec } = require("child_process");
var { promisify } = require("util");
var execAsync = promisify(exec);

// Runs the project build before deploying dist scripts
async function build() {
  console.log("Running npm run build...");

  try {
    var result = await execAsync("npm run build");
    if (result.stdout) {
      console.log(result.stdout);
    }
    if (result.stderr) {
      console.error(result.stderr);
    }
    console.log("Build completed successfully.");
  
  } catch (error) {
    console.error("Build failed:", error);
    if (error.stdout) {
      console.log(error.stdout);
    }
    if (error.stderr) {
      console.error(error.stderr);
    }
    throw error;
  }

  console.log("Build finished successfully. Starting deploy...");
}

// Task to deploy PHP files
function dphp() {
  return deploy(["**/*.php", ...filesToExclude, ...hashFileExclude], resolveEnv());
}
exports.dphp = dphp;

// Task to deploy all files in the dist folder + functions/project/deploy/hash.php
async function ddisthash() {
  await build();
  return deploy(["dist/**/*", "functions/project/deploy/hash.php"], resolveEnv());
}
exports.ddisthash = ddisthash;

// Task to deploy all flexible modules
function dfm() {
  return deploy(["flexible/**/*", ...filesToExclude, ...hashFileExclude], resolveEnv());
}
exports.dfm = dfm;

// Task to deploy all flexible modules config
function dfmconfig() {
  return deploy(["functions/project/config/flexible-heros/**/*", "functions/project/config/flexible-modules/**/*", ...filesToExclude, ...hashFileExclude], resolveEnv());
}
exports.dfmconfig = dfmconfig;

// Task to deploy all components
function dc() {
  return deploy(["components/**/*", ...filesToExclude, ...hashFileExclude], resolveEnv());
}
exports.dc = dc;

// Task to deploy a single file or folder
function ds() {
  var sftpConfig = resolveEnv();
  var filePath = args.path;

  if (!filePath) {
    // Error limpio: gulp-cli respeta showStack=false y no imprime el stack trace
    var err = new Error("Falta --path. Ej: gulp ds --" + args.env + " --path components/card");
    err.showStack = false;
    return Promise.reject(err);
  }

  console.log('Deploying file or folder:', filePath);

  var isDirectory = false;

  try {
    isDirectory = require('fs').statSync(filePath).isDirectory();
  } catch (e) {
    isDirectory = false;
  }

  var glob = isDirectory ? `${filePath}/**/*` : filePath;

  return deploy([glob, ...filesToExclude,  ...hashFileExclude], sftpConfig);
}
exports.ds = ds;

// Task to deploy every file (respecting the exclude list)
async function dall() {
  await build();
  return deploy(["**/*.*", ...filesToExclude], resolveEnv());
}
exports.dall = dall;

// Task to deploy all files in the dist folder except images and fonts + functions/project/deploy/hash.php
async function ddistbuild() {
  await build();
  return deploy(["dist/*.js", "dist/*.css", "functions/project/deploy/hash.php"], resolveEnv());
}
exports.ddistbuild = ddistbuild;

// Task to remove a file or directory
function dremove() {
  var filePath = args.path;
  return remove(filePath, resolveEnv());
}
exports.remove = dremove;

// Task to remove a several hash files
function dremovehash() {
  return cleanOldHash(resolveEnv(), hashFilesToExclude);
}
exports.removehash = dremovehash;