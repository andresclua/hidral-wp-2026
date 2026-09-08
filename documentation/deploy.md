# deployment

As you can see, there is an sftpConfig.js file. The idea is for all file upload operations to go through this file. Currently, it contains all these functions.

All tasks have been rewritten to simplify the process. The list of tasks is as follows:
- `gulp ddisthash --dev|stage|production` - Run `npm run build` and deploy all files from the `dist` directory and the `hash.php` file.
- `gulp ddistbuild --dev|stage|production` - Run `npm run build` and deploy all generated CSS and JS files from the `dist` directory, together with the `hash.php` file.
- `gulp dfm --dev|stage|production` - Deploy all flexible modules and heros.
- `gulp dfmconfig --dev|stage|production` - Deploy the configuration files for all flexible modules and heros.
- `gulp dc --dev|stage|production` - Deploy all components.
- `gulp dall --dev|stage|production` - Run `npm run build` and deploy all project files, including the `hash.php` file.
- `gulp dphp --dev|stage|production` - Deploy all PHP files, excluding the `hash.php` file and files in `filesToExclude`.
- `gulp ds --dev|stage|production --path footer.php || --path folder` - Deploy a single file or directory.
- `gulp remove --dev|stage|production --path footer.php || --path folder` - Remove a specified file or directory.
- `gulp removehash --dev|stage|production` - Remove old hashed files from the `dist` directory, keeping the current and previous hash values. Files listed in `hashFilesToExclude` in `config/sftpConfig.js` are never removed.

It's important that any files to be excluded from uploads are specified in the `filesToExclude` constant. Hash file will be added in `hashFileExclude` and files that don't want to be removed when removing last two hashes must be added in `hashFilesToExclude`.

``` js
  const filesToExclude = [
    "!functions/project/deploy/local-variable.php",
    "!public/**/*", // Exclude public folder and everything inside
    "!config/**/*", // Exclude config folder and everything inside
    "!node_modules/**/*", // Exclude node_modules folder and everything inside
    "!src/**/*", // Exclude src folder and everything inside
    "!.env.production", // Exclude .env.production file
    "!.env.virtual", // Exclude .env.virtual file
    "!gulpfile.js", // Exclude gulpfile.js
    "!package-lock.json", // Exclude package-lock.json file
    "!package.json", // Exclude package.json file
    "!readme.md", // Exclude readme.md file
    "!vite.config.js", // Exclude vite.config.js file
    "!documentation/**/*", // Exclude documentation folder and everything inside
  ];

    const hashFileExclude = [
    "!functions/project/deploy/hash.php",
  ];

  const hashFilesToExclude = [
    "form.js",
  ];
```

