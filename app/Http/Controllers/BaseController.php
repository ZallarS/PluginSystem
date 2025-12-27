<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\View\TemplateEngine;
use App\Services\AuthService;
use App\Http\Request;
use App\Core\Session\SessionInterface;

/**
 * BaseController class
 *
 * Abstract base class for all controllers in the application.
 * Provides common functionality like template rendering,
 * JSON responses, redirects, and authentication checks.
 *
 * @package App\Http\Controllers
 */
abstract class BaseController
{
    /**
     * @var TemplateEngine The template engine instance
     */
    protected TemplateEngine $template;

    /**
     * @var AuthService|null The authentication service
     */
    protected ?AuthService $authService;

    /**
     * @var Request The request instance
     */
    protected Request $request;

    /**
     * @var SessionInterface|null The session interface
     */
    protected ?SessionInterface $session;


    /**
     * Create a new base controller instance.
     *
     * @param TemplateEngine $template The template engine
     * @param AuthService|null $authService The authentication service
     * @param Request $request The request object
     * @param SessionInterface|null $session The session interface (optional)
     */
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

    /**
     * Render a view template with the given data.
     *
     * @param string $template The template name/path
     * @param array $data The data to pass to the template
     * @return \App\Http\Response The response with rendered content
     */
    protected function view($template, $data = []): \App\Http\Response
    {
        $content = $this->template->render($template, $data);
        return new \App\Http\Response($content);
    }

    /**
     * Create a JSON response.
     *
     * @param mixed $data The data to encode as JSON
     * @param int $statusCode The HTTP status code
     * @return \App\Http\Response The JSON response
     */
    protected function json($data, $statusCode = 200): \App\Http\Response
    {
        return \App\Http\Response::json($data, $statusCode);
    }

    /**
     * Create a redirect response.
     *
     * @param string $url The URL to redirect to
     * @param int $statusCode The HTTP status code (usually 301 or 302)
     * @return \App\Http\Response The redirect response
     */
    protected function redirect($url, $statusCode = 302): \App\Http\Response
    {
        return \App\Http\Response::redirect($url, $statusCode);
    }

    /**
     * Validate request data against the given rules.
     *
     * Supports simple validation rules like 'required', 'email',
     * and 'min:N' where N is the minimum length.
     *
     * @param array $rules The validation rules
     * @return array The validation errors (empty if no errors)
     */
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

    /**
     * Check if the current user is logged in.
     *
     * @return bool True if user is logged in
     */
    protected function isLoggedIn(): bool
    {
        return $this->authService && $this->authService->isLoggedIn();
    }
}
