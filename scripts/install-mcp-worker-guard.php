<?php
/** CLI-only, atomic installation; leaves every other must-use plugin alone. */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require dirname(__DIR__, 4) . '/wp-load.php';
$source = dirname(__DIR__) . '/mu-plugins/coda-mcp-worker-guard.php';
$destination = WPMU_PLUGIN_DIR . '/coda-mcp-worker-guard.php';
$contents = file_get_contents($source);
if ($contents === false || strpos($contents, 'Plugin Name: Coda MCP Worker Protection') === false) {
    throw new RuntimeException('Missing or invalid worker protection source');
}
if (!is_dir(WPMU_PLUGIN_DIR) && !wp_mkdir_p(WPMU_PLUGIN_DIR)) {
    throw new RuntimeException('Cannot create must-use plugin directory');
}
if (is_link($destination)) { throw new RuntimeException('Refusing to replace a symlink'); }
if (is_file($destination)) {
    $existing = file_get_contents($destination);
    if ($existing === $contents) { echo "Worker protection already installed\n"; exit; }
    if (strpos($existing, 'Plugin Name: Coda MCP Worker Protection') === false) {
        throw new RuntimeException('Refusing to replace an unrelated plugin');
    }
}
$temporary = tempnam(WPMU_PLUGIN_DIR, '.coda-mcp-');
if ($temporary === false) { throw new RuntimeException('Cannot stage must-use plugin'); }
try {
    if (file_put_contents($temporary, $contents) !== strlen($contents) || !chmod($temporary, 0644)
        || !rename($temporary, $destination)) {
        throw new RuntimeException('Cannot install must-use plugin atomically');
    }
} finally {
    if (file_exists($temporary)) { unlink($temporary); }
}
echo "Installed Coda MCP Worker Protection in the must-use plugin directory\n";
