<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Управление плагинами') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: #f8f9fa;
            min-height: 100vh;
        }
        .sidebar {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            min-height: 100vh;
            position: fixed;
            width: 250px;
        }
        .main-content {
            margin-left: 250px;
            padding: 30px;
        }
        .plugin-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        .plugin-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.12);
        }
        .plugin-card.active {
            border-left: 5px solid #28a745;
        }
        .plugin-card.inactive {
            border-left: 5px solid #6c757d;
        }
        .plugin-actions {
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body>
<!-- Sidebar -->
<nav class="sidebar">
    <div class="sidebar-header p-3">
        <h4><i class="bi bi-speedometer2 me-2"></i>Панель управления</h4>
        <small class="text-muted">MVC System Admin</small>
    </div>

    <div class="sidebar-menu p-3">
        <a href="/admin" class="d-block mb-2 text-white text-decoration-none">
            <i class="bi bi-speedometer2"></i> Дашборд
        </a>
        <a href="/admin/plugins" class="d-block mb-2 text-white text-decoration-none fw-bold">
            <i class="bi bi-plug"></i> Плагины
        </a>
        <a href="/logout" class="d-block text-white text-decoration-none">
            <i class="bi bi-box-arrow-right"></i> Выход
        </a>
    </div>
</nav>

<!-- Main Content -->
<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><?= e($title ?? 'Управление плагинами') ?></h1>
            <p class="text-muted mb-0">Управление установленными плагинами системы</p>
        </div>
        <div>
            <button class="btn btn-outline-primary" onclick="window.location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Обновить
            </button>
        </div>
    </div>

    <?php if (empty($plugins)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            Плагины не найдены. Установите плагины в директорию <code>/plugins</code>.
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($plugins as $pluginName => $plugin): ?>
                <div class="col-md-6 mb-4">
                    <div class="plugin-card <?= $plugin['active'] ? 'active' : 'inactive' ?> p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="mb-1">
                                    <i class="bi bi-plugin me-2"></i>
                                    <?= e($plugin['name'] ?? $pluginName) ?>
                                </h5>
                                <small class="text-muted">Версия: <?= e($plugin['version'] ?? '1.0.0') ?></small>
                            </div>
                            <span class="badge <?= $plugin['active'] ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= $plugin['active'] ? 'Активен' : 'Не активен' ?>
                                </span>
                        </div>

                        <?php if (!empty($plugin['description'])): ?>
                            <p class="mb-3"><?= e($plugin['description']) ?></p>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <?php if (!empty($plugin['author'])): ?>
                                    <small class="text-muted">
                                        Автор: <?= e($plugin['author']) ?>
                                    </small>
                                <?php endif; ?>
                            </div>

                            <div class="plugin-actions">
                                <?php if ($plugin['active']): ?>
                                    <form action="/admin/plugins/deactivate/<?= e($pluginName) ?>" method="POST" class="d-inline">
                                        <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-power"></i> Деактивировать
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form action="/admin/plugins/activate/<?= e($pluginName) ?>" method="POST" class="d-inline">
                                        <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-plug"></i> Активировать
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="mt-5">
        <div class="alert alert-light">
            <h5><i class="bi bi-question-circle me-2"></i>Как добавить плагины?</h5>
            <p class="mb-2">1. Создайте директорию плагина в <code>/plugins</code></p>
            <p class="mb-2">2. Создайте файл <code>plugin.json</code> с описанием плагина</p>
            <p class="mb-0">3. Создайте главный класс плагина с методом <code>init()</code></p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Подтверждение действий с плагинами
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const action = this.querySelector('button').textContent.trim();
            if (!confirm(`Вы уверены, что хотите ${action} этот плагин?`)) {
                e.preventDefault();
            }
        });
    });
</script>
</body>
</html>