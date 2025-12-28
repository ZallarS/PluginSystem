<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\View\TemplateEngine;
use App\Services\AuthService;
use App\Http\Request;
use App\Core\Session\SessionInterface;

abstract class BaseController
{
    protected TemplateEngine $template;
    protected ?AuthService $authService;
    protected Request $request;
    protected ?SessionInterface $session;

    public function __construct(
        TemplateEngine $template,
        ?AuthService $authService,
        Request $request,
        ?SessionInterface $session = null
    ) {
        $this->template = $template;
        $this->authService = $authService;
        $this->request = $request;
        $this->session = $session;
    }

    protected function view($template, $data = []): \App\Http\Response
    {
        $content = $this->template->render($template, $data);
        return new \App\Http\Response($content);
    }

    protected function json($data, $statusCode = 200): \App\Http\Response
    {
        return \App\Http\Response::json($data, $statusCode);
    }

    protected function redirect($url, $statusCode = 302): \App\Http\Response
    {
        return \App\Http\Response::redirect($url, $statusCode);
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

    protected function isLoggedIn(): bool
    {
        return $this->authService && $this->authService->isLoggedIn();
    }
}