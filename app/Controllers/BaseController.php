<?php

namespace App\Controllers;

use Core\TemplateEngine;

abstract class BaseController
{
    protected $template;

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
}