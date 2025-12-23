<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Session\SessionInterface;

class PluginController extends Controller
{

    use Concerns\HasSession;

    public function __construct(
        \App\Core\View\TemplateEngine $template,
        ?\App\Services\AuthService $authService,
        \App\Http\Request $request,
        ?SessionInterface $session = null
    ) {
        parent::__construct($template, $authService, $request, $session);
    }


    public function index()
    {
        // Проверка аутентификации теперь в middleware
        $pluginManager = \Plugins\PluginManager::getInstance();
        $plugins = $pluginManager->getPlugins();

        return $this->view('admin.plugins', [
            'title' => 'Управление плагинами',
            'plugins' => $plugins
        ]);
    }

    public function activate($pluginName)
    {
        // Проверка аутентификации и CSRF теперь в middleware
        $pluginManager = \Plugins\PluginManager::getInstance();

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

    public function deactivate($pluginName)
    {
        // Проверка аутентификации и CSRF теперь в middleware
        $pluginManager = \Plugins\PluginManager::getInstance();

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