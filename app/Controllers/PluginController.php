<?php

namespace App\Controllers;

class PluginController extends BaseController
{
    public function index()
    {
        // ВРЕМЕННО ОТКЛЮЧАЕМ ПРОВЕРКУ
        // if (!isset($_SESSION['user_id'])) {
        //     $this->redirect('/login');
        // }

        $pluginManager = \Plugins\PluginManager::getInstance();
        $plugins = $pluginManager->getPlugins();

        echo $this->view('admin.plugins', [
            'title' => 'Управление плагинами',
            'plugins' => $plugins
        ]);
    }

    public function activate($pluginName)
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }

        $pluginManager = \Plugins\PluginManager::getInstance();
        $result = $pluginManager->activatePlugin($pluginName);

        if ($result) {
            $_SESSION['flash_message'] = "Плагин {$pluginName} успешно активирован";
        } else {
            $_SESSION['flash_error'] = "Не удалось активировать плагин {$pluginName}";
        }

        $this->redirect('/admin/plugins');
    }

    public function deactivate($pluginName)
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }

        $pluginManager = \Plugins\PluginManager::getInstance();
        $result = $pluginManager->deactivatePlugin($pluginName);

        if ($result) {
            $_SESSION['flash_message'] = "Плагин {$pluginName} успешно деактивирован";
        } else {
            $_SESSION['flash_error'] = "Не удалось деактивировать плагин {$pluginName}";
        }

        $this->redirect('/admin/plugins');
    }
}