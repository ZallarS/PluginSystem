<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Session\SessionInterface;

/**
 * PluginController class
 *
 * Handles plugin management including activation,
 * deactivation, and listing of plugins.
 *
 * @package App\Http\Controllers
 */
class PluginController extends Controller
{

    use Concerns\HasSession;

    /**
     * @var \Plugins\PluginManager The plugin manager instance
     */
    private \Plugins\PluginManager $pluginManager;


    /**
     * Create a new plugin controller instance.
     *
     * @param \App\Core\View\TemplateEngine $template The template engine
     * @param \App\Services\AuthService|null $authService The authentication service
     * @param \App\Http\Request $request The request object
     * @param SessionInterface|null $session The session interface (optional)
     * @param \Plugins\PluginManager|null $pluginManager The plugin manager (optional, will throw exception if null)
     * @throws \Exception If plugin manager is not provided
     */
    public function __construct(
        \App\Core\View\TemplateEngine $template,
        ?\App\Services\AuthService $authService,
        \App\Http\Request $request,
        ?SessionInterface $session = null,
        ?\Plugins\PluginManager $pluginManager = null
    ) {
        parent::__construct($template, $authService, $request, $session);
        $this->pluginManager = $pluginManager ?? throw new \Exception('PluginManager должен быть внедрен через DI');
    }


    /**
     * Display the plugin management page.
     *
     * Shows a list of all available plugins with their
     * status and management options.
     *
     * @return \App\Http\Response The response with the plugins view
     */
    public function index()
    {
        // Проверка аутентификации теперь в middleware
        $plugins = $this->pluginManager->getPlugins();

        return $this->view('admin.plugins', [
            'title' => 'Управление плагинами',
            'plugins' => $plugins
        ]);
    }

    /**
     * Activate a plugin.
     *
     * Activates the specified plugin and redirects back
     * to the plugins management page.
     *
     * @param string $pluginName The name of the plugin to activate
     * @return \App\Http\Response The response with the redirect
     */
    public function activate($pluginName)
    {
        // Проверка аутентификации и CSRF теперь в middleware
        $pluginManager = $this->pluginManager;

        if (!$pluginManager->pluginExists($pluginName)) {
            $this->flashMessage("Плагин {$pluginName} не найден", 'error');
            return $this->redirect('/admin/plugins');
        }

        $result = $pluginManager->activatePlugin($pluginName);
        $pluginManager->reloadActivePlugins();

        if ($result) {
            $this->flashMessage("Плагин {$pluginName} успешно активирован", 'success');
        } else {
            $this->flashMessage("Не удалось активировать плагин {$pluginName}", 'error');
        }

        return $this->redirect('/admin/plugins');
    }

    /**
     * Deactivate a plugin.
     *
     * Deactivates the specified plugin and redirects back
     * to the plugins management page.
     *
     * @param string $pluginName The name of the plugin to deactivate
     * @return \App\Http\Response The response with the redirect
     */
    public function deactivate($pluginName)
    {
        // Проверка аутентификации и CSRF теперь в middleware
        $pluginManager = $this->pluginManager;

        if (!$pluginManager->pluginExists($pluginName)) {
            $this->flashMessage("Плагин {$pluginName} не найден", 'error');
            return $this->redirect('/admin/plugins');
        }

        $result = $pluginManager->deactivatePlugin($pluginName);
        $pluginManager->reloadActivePlugins();

        if ($result) {
            $this->flashMessage("Плагин {$pluginName} успешно деактивирован", 'success');
        } else {
            $this->flashMessage("Не удалось деактивировать плагин {$pluginName}", 'error');
        }

        return $this->redirect('/admin/plugins');
    }
}
