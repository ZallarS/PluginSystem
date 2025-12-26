<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\View\TemplateEngine;
use App\Services\AuthService;
use App\Http\Request;
use App\Core\Session\SessionInterface;

abstract class Controller extends BaseController
{
    use Concerns\HasSession;

    public function __construct(
        TemplateEngine $template,
        ?AuthService $authService,
        Request $request,
        ?SessionInterface $session = null
    ) {
        parent::__construct($template, $authService, $request, $session);
    }
}