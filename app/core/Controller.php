<?php
/**
 * Controller dasar: render view dengan layout, JSON, helper input.
 */
abstract class Controller
{
    /**
     * Render views/pages/{$view}.php ke dalam views/layouts/{$layout}.php
     * Variabel di $data tersedia di view. Opsi khusus: pageTitle, pageCss[], pageJs[].
     */
    protected function render(string $view, array $data = [], string $layout = 'main'): void
    {
        $data += ['pageTitle' => '', 'pageCss' => [], 'pageJs' => []];
        $authUser = Auth::user();
        extract($data, EXTR_SKIP);

        header('Cache-Control: no-store, max-age=0');
        ob_start();
        require VIEW_PATH . '/pages/' . $view . '.php';
        $content = ob_get_clean();
        require VIEW_PATH . '/layouts/' . $layout . '.php';
    }

    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function isAjax(): bool
    {
        return Router::isAjax();
    }

    /** Redirect balik ke path internal yang aman */
    protected function back(string $fallback = '/dashboard'): void
    {
        $to = input('kembali', $fallback);
        if ($to === '' || $to[0] !== '/' || strpos($to, '//') === 0 || strpos($to, '\\') !== false) {
            $to = $fallback;
        }
        redirect($to);
    }
}
