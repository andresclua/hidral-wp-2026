const devSFTPConfig = {
    host: "wppunkydev.sftp.wpengine.com",
    user: "wppunkydev-terra",
    port: "2222",
    pass: "yYt>q;,x!X7",
    remotePath: "/wp-content/themes/wp-punky-theme"
  };
  
  const stageSFTPConfig = {
    host: "example.sftp.wpengine.com",
    user: "example-terra",
    port: "2222",
    pass: "y$3@@&Wd",
    remotePath: "/wp-content/themes/your-theme"
  };
  
  const prodSFTPConfig = {
    host: "example.sftp.wpengine.com",
    user: "example-terra",
    port: "2222",
    pass: "y$3@@&Wd",
    remotePath: "/wp-content/themes/your-theme"
  };

  const filesToExclude = [
    "!functions/project/hash.php",
    "!functions/project/local-variable.php",
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
    "!*.zip", // Exclude all zip files
    "!*.tgz", // Exclude all tgz files
  ];
  
  module.exports = {
    devSFTPConfig,
    stageSFTPConfig,
    prodSFTPConfig,
    filesToExclude
  };
