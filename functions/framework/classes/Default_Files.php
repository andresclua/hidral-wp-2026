<?php
/**
 * Class Default_Files
 *
 * Handles loading of theme deployment files including local variables,
 * hash files for cache busting, and asset enqueues.
 *
 * Files loaded (in order):
 * 1. local-variable.php - Environment-specific variables
 * 2. hash.php - Build hash for cache busting
 * 3. enqueues.php - Script and style registration
 *
 * @package TerraFramework
 * @since 1.0.0
 *
 * @param array $config Configuration options
 * @param string $config['local_variable_file'] Path to local variables file
 * @param string $config['hash_file']           Path to hash file
 * @param string $config['enqueues_file']       Path to enqueues file
 *
 * @example
 * new Default_Files([
 *     'local_variable' => 'functions/project/deploy/local-variable.php',
 *     'hash' => 'functions/project/deploy/hash.php',
 *     'enqueues' => 'functions/project/deploy/enqueues.php',
 * ]);
 */
class Default_Files {

  /** @var array */
  protected $config = [];

  /** @var array */
  protected $defaults = [
    'local_variable_file' => 'functions/project/deploy/local-variable.php',
    'hash_file'           => 'functions/project/deploy/hash.php',
    'enqueues_file'       => 'functions/project/deploy/enqueues.php',
  ];

  public function __construct(array $config = []) {
    $this->config = $config;
    $this->load();
  }

  protected function load(): void {

    // 1) Local variables (optional) + pass $local_variable into the file scope
    $this->require_theme_file($this->get_path('local_variable_file'), false);

    // 2) ALWAYS load hash first
    $this->require_theme_file($this->get_path('hash_file'), false);

    // 3) ALWAYS load enqueues after hash
    $this->require_theme_file($this->get_path('enqueues_file'), false);
  }

  protected function get_path(string $key): string {
    $override = $this->config[$key] ?? null;
    if (is_string($override) && $override !== '') {
      return $override;
    }
    return $this->defaults[$key];
  }

  /**
   * Require a theme file by relative path, optionally injecting variables into scope.
   *
   * @param string $relative_path Relative to THEME_PATH
   * @param bool   $optional      If true, missing file won't fatal
   * @param array  $vars          Variables to extract into the required file scope
   */
  protected function require_theme_file(string $relative_path, bool $optional = false, array $vars = []): void {
    $path = rtrim(THEME_PATH, '/') . '/' . ltrim($relative_path, '/');

    if ($optional && !file_exists($path)) {
      return;
    }

    if (!empty($vars)) {
      extract($vars, EXTR_SKIP); // makes $local_variable available inside the required file
    }

    require_once $path;
  }
}
