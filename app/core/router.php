<?php
function route(string $method, string $path, callable $handler): void {
  static $routes = [];
  $routes[] = [$method, $path, $handler];
  $GLOBALS['__routes'] = $routes;
}

function dispatch(): void {
  $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
  $method = $_SERVER['REQUEST_METHOD'];

  $base = (require __DIR__ . '/../config/config.php')['app']['base_url'];
  if ($base && str_starts_with($uri, $base)) $uri = substr($uri, strlen($base)) ?: '/';

  foreach (($GLOBALS['__routes'] ?? []) as [$m, $p, $h]) {
    if ($m === $method && $p === $uri) { $h(); return; }
  }
  http_response_code(404);
  echo "404 Not Found";
}