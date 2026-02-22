import { defineConfig } from "vite";
import liveReload from "vite-plugin-live-reload";
const { resolve } = require("path");
import path from "path";
import { promises as fs } from "fs";
// import vue from "@vitejs/plugin-vue";
import { config } from "dotenv";
import { generateRandomHash, removeFilesPlugin } from "./config/viteHelper";
import stylelint from 'vite-plugin-stylelint';

config({ path: resolve(__dirname, `.env.${process.env.NODE_ENV}`) });

console.log(
    `Launching Wordpress Vite under ${process.env.NODE_ENV}! - by Andrés Clua `
);
console.log("Wordpress Path:", process.env.VITE_WP_PATH);

// if we build files lets add a hash to them
if (process.env.NODE_ENV === "production") {
    var hash = generateRandomHash(3); // Generar un hash de 3 caracteres
}

if (process.env.NODE_ENV === "local") {
    var hash = generateRandomHash(3); // Generar un hash de 3 caracteres
}

export default defineConfig({
    plugins: [
        // vue(),
        liveReload(__dirname + "/**/*.php"),
        process.env.NODE_ENV === "virtual" ? removeFilesPlugin() : null,
        stylelint({
        dev: true,
        build: false,
        emitError: true,
        emitWarning: true,
        lintOnStart: true,
        include: ['src/scss/**/*.scss'],
        exclude: ['node_modules/**'],
      }),
    ],

    // config
    root: "",
    base: process.env.VITE_BASE,
    /*
   logevel could be 'info' to see all the logs.
  */
    logLevel: process.env.VITE_LOG_LEVEL,
    css: {
        preprocessorOptions: {
            scss: {
                additionalData: `
                    @use "@scss/paths.scss" as *;
                    $base-path: "${process.env.VITE_WP_PATH}";
            `,
            },
        },
    },

    build: {
        // output dir for production build
        outDir: resolve(__dirname, "./dist"),
        emptyOutDir: true,

        // esbuild target
        target: "es2018",

        // our entry
        rollupOptions: {
            input: {
                Project: resolve(__dirname + "/src/js/Project.js"),
                Appbackend: resolve(
                    __dirname + "/src/js/vite_additional_input/Appbackend.js"
                ),
                ProjectStyles: resolve(__dirname + "/src/js/ProjectStyles.js"),
            },

            output: {
                // Manual chunks are needed because we have two entrypoints and Main is imported dynamically in Project.js
                manualChunks(id) {
                    if (id.includes("Main.js")) return "main";
                },
                entryFileNames: `[name].${hash}.js`,
                chunkFileNames: `[name].${hash}.js`,
                assetFileNames: `[name].${hash}.[ext]`,
            },
        },
        // minifying switch
        minify: true,
        write: true,
    },

    server: {
        // required to load scripts from custom host
        cors: true,
        // we need a strict port to match on PHP side
        // change freely, but update in your functions.php to match the same port
        strictPort: true,
        port: 9090,
        // serve over http
        https: false,
        hmr: {
            host: "localhost",
        },
    },

    resolve: {
        alias: {
            // vue: "vue/dist/vue.esm-bundler.js",
            "@scss": path.resolve(__dirname, "./src/scss"),
            "@scssFoundation": path.resolve(
                __dirname,
                "./src/scss/framework/foundation"
            ),
            "@scssUtilities": path.resolve(
                __dirname,
                "./src/scss/framework/utilities"
            ),
            "@scssComponents": path.resolve(
                __dirname,
                "./src/scss/framework/components"
            ),
            "@js": path.resolve(__dirname, "./src/js"),
            "@jsModules": path.resolve(__dirname, "./src/js/modules"),
            "@jsMotion": path.resolve(__dirname, "./src/js/motion"),
            "@jsUtilities": path.resolve(__dirname, "./src/js/utilities"),
            "@jsHandler": path.resolve(__dirname, "./src/js/handler"),
            "@vuejs": path.resolve(__dirname, "./src/js/vue"),
        },
    },
});
