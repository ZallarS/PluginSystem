<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Панель администратора') ?></title>
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
        .dashboard-widgets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .dashboard-widget {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s;
            overflow: hidden;
        }
        .widget-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        .widget-body {
            padding: 20px;
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
        <a href="/admin/plugins" class="d-block mb-2 text-white text-decoration-none">
            <i class="bi bi-plug"></i> Плагины
        </a>
        <a href="/logout" class="d-block text-white text-decoration-none">
            <i class="bi bi-box-arrow-right"></i> Выход
        </a>
        <a href="/admin/hidden-widgets-page" class="d-block mb-2 text-white text-decoration-none">
            <i class="bi bi-eye-slash"></i> Скрытые виджеты
        </a>
    </div>
</nav>

<!-- Main Content -->
<div class="main-content">
    <h1 class="h3 mb-4"><?= e($title ?? 'Панель администратора') ?></h1>
    <p class="text-muted mb-4"><?= e($message ?? 'Добро пожаловать в панель управления!') ?></p>

    <!-- Widgets Grid -->
    <?php if (!empty($widgetsGrid)): ?>
        <?= $widgetsGrid ?>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            Виджеты не настроены. Добавьте виджеты через систему плагинов.
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>