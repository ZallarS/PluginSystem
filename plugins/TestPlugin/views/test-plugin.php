<?php
// Устанавливаем значения по умолчанию для переменных
$title = $title ?? 'Тестовый плагин';
$message = $message ?? 'Добро пожаловать на демонстрационную страницу плагина!';
$features = $features ?? [];
$plugin_info = $plugin_info ?? [];
$settings = $settings ?? [];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars((string)$title) ?> | Test Plugin</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">

    <style>
        .plugin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 10px 10px;
        }

        .feature-card {
            border-left: 4px solid #667eea;
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .plugin-icon {
            font-size: 3rem;
            color: #667eea;
        }
    </style>
</head>
<body>
<!-- Шапка плагина -->
<div class="plugin-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-2 text-center">
                <i class="bi bi-plugin plugin-icon"></i>
            </div>
            <div class="col-md-10">
                <h1 class="display-4"><?= htmlspecialchars((string)$title) ?></h1>
                <p class="lead"><?= htmlspecialchars((string)$message) ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Основной контент -->
<div class="container">
    <!-- Статистика и информация -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card text-center">
                <h3><i class="bi bi-info-circle text-primary"></i> Информация</h3>
                <p class="mb-0"><strong>Версия:</strong> <?= htmlspecialchars((string)($plugin_info['version'] ?? '1.0.0')) ?></p>
                <p class="mb-0"><strong>Автор:</strong> <?= htmlspecialchars((string)($plugin_info['author'] ?? 'Unknown')) ?></p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card text-center">
                <h3><i class="bi bi-gear text-success"></i> Настройки</h3>
                <p class="mb-0"><strong>Статус:</strong> <span class="badge bg-success">Активен</span></p>
                <p class="mb-0"><strong>Элементов:</strong> <?= htmlspecialchars((string)($settings['max_items'] ?? 10)) ?></p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card text-center">
                <h3><i class="bi bi-bar-chart text-warning"></i> Статистика</h3>
                <p class="mb-0"><strong>Запросов:</strong> 1,234</p>
                <p class="mb-0"><strong>Активность:</strong> Высокая</p>
            </div>
        </div>
    </div>

    <!-- Возможности плагина -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="mb-4"><i class="bi bi-stars"></i> Возможности плагина</h2>
            <div class="row">
                <?php foreach ($features as $index => $feature): ?>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card feature-card h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-primary text-white rounded-circle p-2 me-3">
                                        <?= $index + 1 ?>
                                    </div>
                                    <h5 class="card-title mb-0"><?= htmlspecialchars((string)$feature) ?></h5>
                                </div>
                                <p class="card-text text-muted">
                                    Эта функция демонстрирует возможности плагина и его интеграцию с системой.
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Тестовая форма -->
    <div class="row mb-4">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="bi bi-send"></i> Тестовая форма плагина</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="/test-plugin/save" id="pluginForm">
                        <div class="mb-3">
                            <label for="name" class="form-label">Ваше имя</label>
                            <input type="text" class="form-control" id="name" name="name"
                                   placeholder="Введите ваше имя" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email адрес</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   placeholder="Введите ваш email" required>
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">Сообщение</label>
                            <textarea class="form-control" id="message" name="message"
                                      rows="4" placeholder="Введите ваше сообщение..."></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-send"></i> Отправить данные через плагин
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Кнопки действий -->
    <div class="row mb-4">
        <div class="col-md-12 text-center">
            <div class="btn-group" role="group">
                <a href="/test-plugin/api" class="btn btn-outline-info">
                    <i class="bi bi-code-slash"></i> Тестировать API
                </a>

                <?php if (isset($_SESSION['is_admin'])): ?>
                    <a href="/admin/test-plugin" class="btn btn-outline-warning">
                        <i class="bi bi-gear"></i> Управление плагином
                    </a>
                <?php endif; ?>

                <a href="/admin/plugins" class="btn btn-outline-secondary">
                    <i class="bi bi-plug"></i> Все плагины
                </a>

                <a href="/" class="btn btn-outline-dark">
                    <i class="bi bi-house"></i> На главную
                </a>
            </div>
        </div>
    </div>

    <!-- Информация о системе -->
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-secondary">
                <h5><i class="bi bi-info-circle"></i> Информация о плагине</h5>
                <p>Это демонстрационный плагин, созданный для тестирования системы плагинов MVC.</p>
                <p><strong>Путь к плагину:</strong> <code><?= htmlspecialchars(__DIR__) ?></code></p>
                <p><strong>Версия PHP:</strong> <?= htmlspecialchars(PHP_VERSION) ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Подвал -->
<footer class="footer mt-5 py-3 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5>Тестовый плагин</h5>
                <p class="text-muted">Демонстрационный плагин для системы MVC</p>
            </div>
            <div class="col-md-6 text-end">
                <p class="text-muted">
                    Версия <?= htmlspecialchars((string)($plugin_info['version'] ?? '1.0.0')) ?> |
                    &copy; <?= date('Y') ?> <?= htmlspecialchars((string)($plugin_info['author'] ?? 'MVC System')) ?>
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Обработка формы
    document.getElementById('pluginForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('/test-plugin/save', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Данные успешно отправлены! Проверьте консоль для просмотра ответа.');
                    console.log('Ответ от плагина:', data);
                    this.reset();
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
                alert('Произошла ошибка при отправке данных');
            });
    });
</script>
</body>
</html>