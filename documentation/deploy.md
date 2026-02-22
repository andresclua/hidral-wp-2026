# deployment

As you can see, there is an sftpConfig.js file. The idea is for all file upload operations to go through this file. Currently, it contains all these functions.

All tasks have been rewritten to simplify the process. The list of tasks is as follows:
- `gulp ddist --dev|stage|production` - Upload all files.
- `gulp ddisthash --dev|stage|production` - Upload dist and hash directories.
- `gulp ddistscripts --dev|stage|production` - Upload dist (only JS and CSS) and hash.
- `gulp dfm --dev|stage|production` - Deploy all flexible modules.
- `gulp dall --dev|stage|production` - Deploy all files.
- `gulp dphp --dev|stage|production` - Deploy all php files.
- `gulp ds --dev|stage|production  --path footer.php || --path folder ` - Deploy single file or directory.
- `gulp remove --dev|stage|production --path footer.php` - Remove a specified file or folder.

It's important that any files to be excluded from uploads are specified in the `filesToExclude` constant.

``` js
  const filesToExclude = [
    "!functions/project/hash.php",
    "!functions/project/local/local-variable.php",
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
```

