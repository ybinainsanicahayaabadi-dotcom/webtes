<?php
/**
 * Router sederhana: mencocokkan METHOD + path dengan config/routes.php.
 */
final class Router
{
    public static function isAjax(): bool
    {
        return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
            || strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;
    }

    /** Path request relatif terhadap folder aplikasi, mis. "/dashboard" */
    public static function path(): string
    {
        $uri = rawurldecode((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
        $script = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/');

        $candidates = [$script];
        if ($script !== '') {
            $parent = rtrim(str_replace('\\', '/', dirname($script)), '/');
            $candidates[] = $parent === '.' ? '' : $parent;   // bila diakses lewat .htaccess root
        }
        foreach ($candidates as $base) {
            if ($base !== '' && ($uri === $base || strpos($uri, $base . '/') === 0)) {
                $uri = substr($uri, strlen($base));
                break;
            }
        }
        $path = '/' . trim($uri, '/');
        return $path === '/index.php' ? '/' : $path;
    }

    public static function dispatch(array $routes): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if ($method === 'HEAD') {
            $method = 'GET';
        }
        $path = self::path();

        try {
            foreach ($routes as [$m, $pattern, $handler, $access]) {
                if ($m !== $method) {
                    continue;
                }
                $regex = '#^' . preg_replace('#\{[a-z_]+\}#', '([0-9]+)', $pattern) . '$#';
                if (!preg_match($regex, $path, $match)) {
                    continue;
                }
                array_shift($match);
                Guard::handle($access, $path, $method);

                [$class, $action] = explode('@', $handler);
                (new $class())->$action(...array_map('intval', $match));
                return;
            }
            self::abort(404);
        } catch (Throwable $e) {
            app_log($e::class . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
            self::abort(500, APP_DEBUG ? $e->getMessage() : '');
        }
    }

    public static function abort(int $code, string $message = ''): void
    {
        (new ErrorController())->show($code, $message);
        exit;
    }
}
