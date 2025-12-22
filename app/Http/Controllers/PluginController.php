<?php
declare(strict_types=1);

namespace App\Http\Controllers;

class PluginController extends Controller
{
    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            return $this->redirect('/login');
        }

        $pluginManager = \Plugins\PluginManager::getInstance();
        $plugins = $pluginManager->getPlugins();

        return $this->view('admin.plugins', [
            'title' => 'Управление плагинами',
            'plugins' => $plugins
        ]);
    }

    public function activate($pluginName)
    {
        if (!isset($_POST['_token']) || $_POST['_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = "Недействительный CSRF токен";
            $this->redirect('/admin/plugins');
            return;
        }

        if (!isset($_SESSION['user_id'])) {
            $_SESSION['flash_error'] = "Требуется авторизация";
            $this->redirect('/login');
            return;
        }

        $pluginManager = \Plugins\PluginManager::getInstance();

        if (!$pluginManager->pluginExists($pluginName)) {
            $_SESSION['flash_error'] = "Плагин {$pluginName} не найден";
            $this->redirect('/admin/plugins');
            return;
        }

        $result = $pluginManager->activatePlugin($pluginName);
        $pluginManager->reloadActivePlugins();

        if ($result) {
            $_SESSION['flash_message'] = "Плагин {$pluginName} успешно активирован";
        } else {
            $_SESSION['flash_error'] = "Не удалось активировать плагин {$pluginName}";
        }

        $this->redirect('/admin/plugins');
    }

    public function deactivate($pluginName)
    {
        if (!isset($_POST['_token']) || $_POST['_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = "Недействительный CSRF токен";
            $this->redirect('/admin/plugins');
            return;
        }

        if (!isset($_SESSION['user_id'])) {
            $_SESSION['flash_error'] = "Требуется авторизация";
            $this->redirect('/login');
            return;
        }

        $pluginManager = \Plugins\PluginManager::getInstance();

        if (!$pluginManager->pluginExists($pluginName)) {
            $_SESSION['flash_error'] = "Плагин {$pluginName} не найден";
            $this->redirect('/admin/plugins');
            return;
        }

        $result = $pluginManager->deactivatePlugin($pluginName);
        $pluginManager->reloadActivePlugins();

        if ($result) {
            $_SESSION['flash_message'] = "Плагин {$pluginName} успешно деактивирован";
        } else {
            $_SESSION['flash_error'] = "Не удалось деактивировать плагин {$pluginName}";
        }

        $this->redirect('/admin/plugins');
    }
}