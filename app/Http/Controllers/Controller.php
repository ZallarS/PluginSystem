<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\View\TemplateEngine;

abstract class Controller
{
    protected TemplateEngine $template;

    public function __construct()
    {
        $this->template = new TemplateEngine();
    }

    protected function view($template, $data = [])
    {
        return $this->template->render($template, $data);
    }

    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function redirect($url, $statusCode = 302)
    {
        header("Location: {$url}", true, $statusCode);
        exit;
    }

    protected function isLoggedIn()
    {
        return isset($_SESSION['user_id']) && isset($_SESSION['is_admin']);
    }

    protected function requireLogin()
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('/login');
            return false;
        }
        return true;
    }
    protected function validateCsrfToken()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return true;
        }

        $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

        if (empty($token) || !isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            if ($this->isJsonRequest()) {
                return $this->json(['error' => 'Invalid CSRF token'], 403);
            } else {
                $_SESSION['flash_error'] = 'Недействительный CSRF токен';
                $this->redirect('/');
                return false;
            }
        }

        return true;
    }

    protected function isJsonRequest()
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return strpos($accept, 'application/json') !== false ||
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }
}