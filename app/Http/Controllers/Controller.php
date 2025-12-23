<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\View\TemplateEngine;
use App\Services\AuthService;
use App\Http\Request;
use App\Http\Response;

abstract class Controller
{
    protected TemplateEngine $template;
    protected AuthService $authService;
    protected Request $request;

    public function __construct(
        TemplateEngine $template,
        AuthService $authService,
        Request $request
    ) {
        $this->template = $template;
        $this->authService = $authService;
        $this->request = $request;
    }

    private function getAuthServiceFromContainer(): ?AuthService
    {
        try {
            $app = \App\Core\Application::getInstance();
            if ($app && method_exists($app, 'getContainer')) {
                $container = $app->getContainer();
                if ($container && $container->has(AuthService::class)) {
                    return $container->get(AuthService::class);
                }
            }
        } catch (\Exception $e) {
            if (env('APP_DEBUG', false)) {
                error_log("Controller: Error getting AuthService: " . $e->getMessage());
            }
        }

        return null;
    }

    protected function view($template, $data = []): Response
    {
        $content = $this->template->render($template, $data);
        return new Response($content);
    }

    protected function json($data, $statusCode = 200): Response
    {
        return Response::json($data, $statusCode);
    }

    protected function redirect($url, $statusCode = 302): Response
    {
        return Response::redirect($url, $statusCode);
    }

    protected function validate(array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $rule) {
            $value = $this->request->input($field);

            if ($rule === 'required' && empty($value)) {
                $errors[$field] = "Поле {$field} обязательно для заполнения";
            }

            if ($rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = "Неверный формат email";
            }

            if (strpos($rule, 'min:') === 0) {
                $minLength = (int) substr($rule, 4);
                if (strlen($value) < $minLength) {
                    $errors[$field] = "Минимальная длина {$minLength} символов";
                }
            }
        }

        return $errors;
    }
}