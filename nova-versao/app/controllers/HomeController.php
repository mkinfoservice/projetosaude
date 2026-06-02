<?php
/**
 * app/controllers/HomeController.php
 * Landing page pública e página 404
 *
 * Concessionária Inteligente Bem
 */

declare(strict_types=1);

class HomeController
{
    public function index(): void
    {
        // Busca operadoras ativas (tolerante a falha de BD — mostra lista vazia se banco não estiver pronto)
        $operadoras = [];
        try {
            $pdo  = db();
            $stmt = $pdo->query("SELECT id, nome, slug, logo_url, descricao FROM operadoras WHERE ativo = 1 ORDER BY nome");
            $operadoras = $stmt->fetchAll();
        } catch (Throwable) {
            // Banco não conectado ou tabela inexistente — landing page exibe sem operadoras
        }

        // Gera CSRF token
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path'     => '/',
                'secure'   => APP_ENV === 'production',
                'httponly'  => true,
                'samesite' => 'Strict',
            ]);
            session_start();
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
        }

        $csrf_token    = $_SESSION['csrf_token'];
        $is_logged_in  = !empty($_SESSION['logged_in']);
        $user_type     = $_SESSION['user_type'] ?? null;
        $user_perfil   = $_SESSION['user_data']['perfil'] ?? null;
        $page_title    = 'Concessionária Inteligente Bem';
        $current_page  = 'home';

        // Captura a view em buffer
        ob_start();
        include APP_PATH . '/views/pages/home.php';
        $content = ob_get_clean();

        // Renderiza o layout
        include APP_PATH . '/views/layouts/main.php';
    }

    public function notFound(): void
    {
        http_response_code(404);

        $page_title   = 'Página não encontrada';
        $current_page = '';
        $is_logged_in = !empty($_SESSION['logged_in'] ?? false);
        $user_type    = $_SESSION['user_type'] ?? null;
        $csrf_token   = $_SESSION['csrf_token'] ?? '';

        ob_start(); ?>
        <div style="
            min-height: 60vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 4rem 1.5rem;
        ">
            <div style="font-size: 5rem; margin-bottom: 1rem;">🔍</div>
            <h1 style="font-size: 3rem; color: var(--navy); margin-bottom: 0.5rem;">404</h1>
            <p style="font-size: 1.2rem; color: var(--gray-500); margin-bottom: 2rem;">
                Página não encontrada.
            </p>
            <a href="/" class="btn btn-primary btn-lg">Voltar ao Início</a>
        </div>
        <?php
        $content = ob_get_clean();
        include APP_PATH . '/views/layouts/main.php';
    }
}
