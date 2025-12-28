<?php
declare(strict_types=1);

namespace App\Http\Controllers;
use App\Core\View\TemplateEngine;
use App\Core\View\SafeTemplateEngine;
use App\Services\AuthService;
use App\Http\Request;
use App\Core\Session\SessionInterface;

/**
 * Controller class
 *
 * Base controller that extends BaseController and includes
 * session functionality through the HasSession trait.
 *
 * @package App\Http\Controllers
 */
abstract class Controller extends BaseController
{
    use Concerns\HasSession;

    /**
     * Create a new controller instance.
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
        parent::__construct($template, $authService, $request, $session);
    }
}
