<?php
declare(strict_types=1);

namespace App\Http\Controllers;

class PluginController extends Controller
{
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

        // ИСПРАВЛЕНО: Добавлена закрывающая круглая скобка
        if (!$pluginManager->pluginExists($pluginName)) {
            // Используем session flash
            try {
                $session = app(\App\Core\Session\SessionManager::class);
                $session->flash('flash_error', "Плагин {$pluginName} не найден");
            } catch (\Exception $e) {
                $_SESSION['flash_error'] = "Плагин {$pluginName} не найден";
            }

            return $this->redirect('/admin/plugins');
        }

        $result = $pluginManager->activatePlugin($pluginName);
        $pluginManager->reloadActivePlugins();

        if ($result) {
            try {
                $session = app(\App\Core\Session\SessionManager::class);
                $session->flash('flash_message', "Плагин {$pluginName} успешно активирован");
            } catch (\Exception $e) {
                $_SESSION['flash_message'] = "Плагин {$pluginName} успешно активирован";
            }
        } else {
            try {
                $session = app(\App\Core\Session\SessionManager::class);
                $session->flash('flash_error', "Не удалось активировать плагин {$pluginName}");
            } catch (\Exception $e) {
                $_SESSION['flash_error'] = "Не удалось активировать плагин {$pluginName}";
            }
        }

        return $this->redirect('/admin/plugins');
    }

    public function deactivate($pluginName)
    {
        // Проверка аутентификации и CSRF теперь в middleware
        $pluginManager = \Plugins\PluginManager::getInstance();

        // ИСПРАВЛЕНО: Добавлена закрывающая круглая скобка
        if (!$pluginManager->pluginExists($pluginName)) {
            try {
                $session = app(\App\Core\Session\SessionManager::class);
                $session->flash('flash_error', "Плагин {$pluginName} не найден");
            } catch (\Exception $e) {
                $_SESSION['flash_error'] = "Плагин {$pluginName} не найден";
            }

            return $this->redirect('/admin/plugins');
        }

        $result = $pluginManager->deactivatePlugin($pluginName);
        $pluginManager->reloadActivePlugins();

        if ($result) {
            try {
                $session = app(\App\Core\Session\SessionManager::class);
                $session->flash('flash_message', "Плагин {$pluginName} успешно деактивирован");
            } catch (\Exception $e) {
                $_SESSION['flash_message'] = "Плагин {$pluginName} успешно деактивирован";
            }
        } else {
            try {
                $session = app(\App\Core\Session\SessionManager::class);
                $session->flash('flash_error', "Не удалось деактивировать плагин {$pluginName}");
            } catch (\Exception $e) {
                $_SESSION['flash_error'] = "Не удалось деактивировать плагин {$pluginName}";
            }
        }

        return $this->redirect('/admin/plugins');
    }
}