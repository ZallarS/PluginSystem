<?php
// Устанавливаем значения по умолчанию для переменных
$title = $title ?? 'Управление плагинами';
$plugins = $plugins ?? [];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars((string)$title) ?> | MVC System</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">

    <style>
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
        }

        .sidebar-sticky {
            position: relative;
            top: 0;
            height: calc(100vh - 48px);
            padding-top: .5rem;
            overflow-x: hidden;
            overflow-y: auto;
        }

        .sidebar .nav-link {
            font-weight: 500;
            color: #333;
            padding: 0.5rem 1rem;
        }

        .sidebar .nav-link.active {
            color: #0d6efd;
        }

        .sidebar .nav-link:hover {
            color: #0d6efd;
            background-color: rgba(13, 110, 253, 0.1);
        }

        .sidebar-heading {
            font-size: .75rem;
            text-transform: uppercase;
            padding: 0.5rem 1rem;
            color: #6c757d;
        }

        main {
            padding-top: 1.5rem;
        }

        .plugin-card {
            border-left: 4px solid #0d6efd;
            transition: all 0.3s ease;
        }

        .plugin-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .plugin-status {
            font-size: 0.75rem;
            padding: 2px 8px;
            border-radius: 10px;
        }

        .plugin-status.active {
            background-color: #d1e7dd;
            color: #0f5132;
        }

        .plugin-status.inactive {
            background-color: #f8d7da;
            color: #842029;
        }

        .badge-version {
            font-size: 0.7rem;
            background-color: #6c757d;
        }
    </style>
</head>
<body>
<!-- Навбар -->
<nav class="navbar navbar-dark bg-dark fixed-top navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand" href="/admin">
            <i class="bi bi-gear"></i> MVC Admin
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/admin">
                        <i class="bi bi-speedometer2"></i> Панель управления
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="/admin/plugins">
                        <i class="bi bi-plug"></i> Плагины
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> <?= htmlspecialchars((string)($_SESSION['username'] ?? 'Администратор')) ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/"><i class="bi bi-house"></i> На сайт</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="/logout"><i class="bi bi-box-arrow-right"></i> Выйти</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Основной контент -->
<div class="container-fluid" style="margin-top: 70px;">
    <div class="row">
        <!-- Боковая панель -->
        <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
            <div class="sidebar-sticky pt-3">
                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                    <span>Управление</span>
                </h6>

                <ul class="nav flex-column mb-2">
                    <li class="nav-item">
                        <a class="nav-link" href="/admin">
                            <i class="bi bi-speedometer2"></i> Дашборд
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="/admin/plugins">
                            <i class="bi bi-plug"></i> Плагины
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="bi bi-palette"></i> Темы
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="bi bi-shield-check"></i> Безопасность
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Основной контент -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <!-- Уведомления -->
            <?php include __DIR__ . '/../components/notifications.php'; ?>

            <!-- Контент управления плагинами -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="bi bi-plug"></i> <?= htmlspecialchars((string)$title) ?>
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#installModal">
                        <i class="bi bi-download"></i> Установить плагин
                    </button>
                </div>
            </div>

            <!-- Список плагинов -->
            <?php if (!empty($plugins)): ?>
                <div class="row">
                    <?php foreach ($plugins as $pluginName => $pluginData): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card plugin-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h5 class="card-title mb-0">
                                            <?= htmlspecialchars((string)$pluginName) ?>
                                        </h5>
                                        <span class="badge <?= $pluginData['active'] ? 'bg-success' : 'bg-secondary' ?> plugin-status">
                                                <?= $pluginData['active'] ? 'Активен' : 'Неактивен' ?>
                                            </span>
                                    </div>

                                    <p class="card-text text-muted small">
                                        <?= htmlspecialchars((string)($pluginData['config']['description'] ?? 'Описание отсутствует')) ?>
                                    </p>

                                    <div class="mb-3">
                                        <?php if (isset($pluginData['config']['version'])): ?>
                                            <span class="badge badge-version">v<?= htmlspecialchars((string)$pluginData['config']['version']) ?></span>
                                        <?php endif; ?>

                                        <?php if (isset($pluginData['config']['author'])): ?>
                                            <span class="text-muted small">
                                                    <i class="bi bi-person"></i> <?= htmlspecialchars((string)$pluginData['config']['author']) ?>
                                                </span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="card-footer bg-transparent">
                                    <div class="d-flex justify-content-between">
                                        <?php if ($pluginData['active']): ?>
                                            <form method="POST" action="/admin/plugins/deactivate/<?= htmlspecialchars((string)$pluginName) ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Деактивировать плагин <?= htmlspecialchars((string)$pluginName) ?>?')">
                                                    <i class="bi bi-power"></i> Деактивировать
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" action="/admin/plugins/activate/<?= htmlspecialchars((string)$pluginName) ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-success">
                                                    <i class="bi bi-power"></i> Активировать
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <button class="btn btn-sm btn-outline-info"
                                                data-bs-toggle="modal"
                                                data-bs-target="#detailsModal"
                                                data-plugin-name="<?= htmlspecialchars((string)$pluginName) ?>"
                                                data-plugin-config='<?= htmlspecialchars((string)json_encode($pluginData['config'] ?? [])) ?>'>
                                            <i class="bi bi-info-circle"></i> Подробнее
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Информационный блок без автоскрытия -->
                <div class="alert alert-info">
                    <div class="text-center">
                        <i class="bi bi-plug display-4 d-block mb-3"></i>
                        <h4>Плагины не найдены</h4>
                        <p>В системе еще не установлено ни одного плагина.</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#installModal">
                            <i class="bi bi-download"></i> Установить первый плагин
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<!-- Модальное окно для установки плагина -->
<div class="modal fade" id="installModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-download"></i> Установка плагина</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Установка плагинов находится в разработке
                </div>
                <p>В будущих версиях будет реализована установка плагинов через:</p>
                <ul>
                    <li>Загрузку ZIP-архива</li>
                    <li>Установку по URL</li>
                    <li>Магазин плагинов</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно с подробностями плагина -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pluginDetailsTitle">Подробности плагина</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="pluginDetailsContent">
                <!-- Заполняется JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Показать детали плагина
    const detailsModal = document.getElementById('detailsModal');
    if (detailsModal) {
        detailsModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const pluginName = button.getAttribute('data-plugin-name');
            const pluginConfig = JSON.parse(button.getAttribute('data-plugin-config'));

            document.getElementById('pluginDetailsTitle').textContent = 'Плагин: ' + pluginName;

            let html = `
                    <div class="mb-3">
                        <strong>Название:</strong><br>
                        ${pluginConfig.name || pluginName}
                    </div>

                    <div class="mb-3">
                        <strong>Описание:</strong><br>
                        ${pluginConfig.description || 'Отсутствует'}
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3">
                                <strong>Версия:</strong><br>
                                ${pluginConfig.version || 'Не указана'}
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <strong>Автор:</strong><br>
                                ${pluginConfig.author || 'Не указан'}
                            </div>
                        </div>
                    </div>
                `;

            if (pluginConfig.requires) {
                html += `
                        <div class="mb-3">
                            <strong>Требования:</strong><br>
                            <ul class="mb-0">
                    `;

                for (const [key, value] of Object.entries(pluginConfig.requires)) {
                    html += `<li><code>${key}: ${value}</code></li>`;
                }

                html += `
                            </ul>
                        </div>
                    `;
            }

            document.getElementById('pluginDetailsContent').innerHTML = html;
        });
    }
</script>
</body>
</html>