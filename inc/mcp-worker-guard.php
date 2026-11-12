<?php
/** Protect the small shared PHP pool from idle MCP notification streams. */
if (!defined('ABSPATH')) { exit; }

// MCP Streamable HTTP permits 405 when optional standalone GET SSE is not
// offered. Three idle streams exhausted this host's three live PHP workers.
// POST tool requests, DELETE session cleanup, and authentication stay intact.
// This hook runs after route permission checks, immediately before the callback.
add_filter('rest_dispatch_request', function ($result, $request) {
    if ($result !== null || $request->get_method() !== 'GET'
        || rtrim($request->get_route(), '/') !== '/mcp/v1/http') {
        return $result;
    }

    return new WP_REST_Response([
        'code' => 'mcp_standalone_stream_unavailable',
        'message' => 'Standalone notification streams are unavailable. Use POST for MCP requests.',
    ], 405, [
        'Allow' => 'POST, DELETE, OPTIONS',
        'Cache-Control' => 'no-store',
    ]);
}, PHP_INT_MAX, 2);

// Also bound optional token-URL streams without exposing or matching tokens.
add_filter('mwai_mcp_stream_max_time', function ($seconds) {
    return min((int) $seconds, 5);
});
