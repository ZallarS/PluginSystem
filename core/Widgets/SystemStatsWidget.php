<?php

namespace Core\Widgets\Widgets;

use Core\Widgets\AbstractWidget;

class SystemStatsWidget extends AbstractWidget
{
    public function __construct()
    {
        parent::__construct([
            'id' => 'system_stats',
            'title' => 'Статистика системы',
            'description' => 'Основные показатели системы',
            'icon' => 'bi-speedometer2',
            'size' => 'medium',
        ]);
    }

    public function render(): string
    {
        return '
        <div class="row">
            <div class="col-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="bi bi-plug"></i>
                    </div>
                    <div class="stat-info">
                        <h5>0</h5>
                        <small>Плагинов</small>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="bi bi-person"></i>
                    </div>
                    <div class="stat-info">
                        <h5>1</h5>
                        <small>Пользователей</small>
                    </div>
                </div>
            </div>
        </div>';
    }
}