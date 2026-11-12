<?php
// Standalone behavior test: deliberately loads no theme or WordPress bootstrap.
define('ABSPATH', __DIR__);
$hooks = [];
function add_filter($name, $callback, $priority = 10, $args = 1) {
    global $hooks;
    $hooks[$name] = [$callback, $priority, $args];
}
class WP_REST_Response {
    public function __construct(public $data, public $status, public $headers) {}
}
class GuardTestRequest {
    public function __construct(private $method, private $route) {}
    public function get_method() { return $this->method; }
    public function get_route() { return $this->route; }
}
function check($condition, $message) {
    if (!$condition) { throw new RuntimeException($message); }
}
require __DIR__ . '/../mu-plugins/coda-mcp-worker-guard.php';
check($hooks['rest_dispatch_request'][1] === PHP_INT_MAX, 'Guard must run at final dispatch priority');
foreach (['/mcp/v1/http', '/mcp/v1/http/'] as $route) {
    $response = coda_mcp_guard_dispatch(null, new GuardTestRequest('GET', $route));
    check($response->status === 405 && $response->headers['Cache-Control'] === 'no-store', 'GET stream must close without caching');
}
foreach (['POST', 'DELETE', 'OPTIONS'] as $method) {
    check(coda_mcp_guard_dispatch(null, new GuardTestRequest($method, '/mcp/v1/http')) === null, 'Non-GET dispatch must be unchanged');
}
check(coda_mcp_guard_dispatch(null, new GuardTestRequest('GET', '/haunted-tech/v1/book-modal/demo')) === null, 'Other REST routes must be unchanged');
$prior = new stdClass();
check(coda_mcp_guard_dispatch($prior, new GuardTestRequest('GET', '/mcp/v1/http')) === $prior, 'Prior responses must be preserved');
check(coda_mcp_guard_stream_limit(180) === 5 && coda_mcp_guard_stream_limit(3) === 3, 'Fallback timeout must be bounded');
echo "Theme-independent worker protection tests passed\n";
