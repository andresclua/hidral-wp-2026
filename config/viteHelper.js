import path, { resolve } from "path";
import { promises as fs } from "fs";
import fsSync from "fs"; // <- añadido

export function generateRandomHash(length) {
    let result = "";
    const characters = "abcdefghijklmnopqrstuvwxyz0123456789";
    for (let i = 0; i < length; i++) {
        result += characters.charAt(Math.floor(Math.random() * characters.length));
    }
    return result;
}

export function updateHash(hash) {
    console.log(`Updating hash in hash.php file with new hash: ${hash}`);

    const hashFileRoute = resolve(process.cwd(), "functions/project/hash.php");

    if (fsSync.existsSync(hashFileRoute)) {
        const content = fsSync.readFileSync(hashFileRoute, "utf-8");

        const updatedContent = content.replace(
            /define\(\s*['"]hash['"]\s*,\s*['"][a-zA-Z0-9]*['"]\s*\);/,
            `define('hash', '${hash}');`
        );

        fsSync.writeFileSync(hashFileRoute, updatedContent, "utf-8");
    } else {
        console.log("Hash file not found:", hashFileRoute);
    }
}

export function generateAndUpdateHash(length = 3) {
    const hash = generateRandomHash(length);
    updateHash(hash);
    return hash;
}

export function removeFilesPlugin() {
    return {
        name: "remove-files",
        closeBundle: async () => {
            const distPath = resolve(__dirname, "dist");
            const files = await fs.readdir(distPath);

            const imageFiles = files.filter((file) =>
                [".jpg", ".png", ".gif", ".webp", ".svg"].some((ext) =>
                    file.endsWith(ext)
                )
            );

            const deleteFilesPromises = imageFiles.map((file) =>
                fs.unlink(resolve(distPath, file))
            );

            const deleteFoldersPromises = ["assets", "fonts"].map(async (folder) => {
                const folderPath = resolve(distPath, folder);
                try {
                    await fs.rm(folderPath, { recursive: true });
                } catch (err) {
                    if (err.code !== "ENOENT") throw err;
                }
            });

            await Promise.all([...deleteFilesPromises, ...deleteFoldersPromises]);
        },
    };
}
