<?php
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require dirname(__DIR__, 4) . '/wp-load.php';
echo 'Active template: ' . get_option('template') . PHP_EOL;
echo 'Guard included: ' . (in_array(dirname(__DIR__) . '/inc/mcp-worker-guard.php', get_included_files(), true) ? 'yes' : 'no') . PHP_EOL;
$request = new WP_REST_Request('GET', '/mcp/v1/http');
$anonymous = rest_do_request($request);
echo 'Anonymous MCP status: ' . $anonymous->get_status() . PHP_EOL;
if (!in_array($anonymous->get_status(), [401, 403], true)) { exit(1); }
$admins = get_users(['role' => 'administrator', 'number' => 1, 'fields' => 'ID']);
if (!$admins) { throw new RuntimeException('No administrator available for CLI-only dispatch check'); }
wp_set_current_user((int) $admins[0]);
$response = rest_do_request($request);
echo 'MCP GET status: ' . $response->get_status() . PHP_EOL;
echo 'AI Engine active: ' . (in_array('ai-engine/ai-engine.php', get_option('active_plugins'), true) ? 'yes' : 'no') . PHP_EOL;
if ($response->get_status() !== 405) { exit(1); }
$post = new WP_REST_Request('POST', '/mcp/v1/http');
$post->set_header('Content-Type', 'application/json');
$post->set_body(wp_json_encode([
    'jsonrpc' => '2.0', 'id' => 1, 'method' => 'initialize',
    'params' => ['protocolVersion' => '2025-06-18', 'capabilities' => [],
        'clientInfo' => ['name' => 'deployment-check', 'version' => '1.0']],
]));
$post_response = rest_do_request($post);
$data = $post_response->get_data();
echo 'MCP POST initialize status: ' . $post_response->get_status() . PHP_EOL;
if ($post_response->get_status() !== 200 || !isset($data['result'])) { exit(1); }
wp_set_current_user(0);
