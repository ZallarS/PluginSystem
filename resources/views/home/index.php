<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'MVC System') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .feature-card {
            text-align: center;
            padding: 30px 20px;
            background: white;
            border-radius: 10px;
            margin-bottom: 20px;
            height: 100%;
            transition: all 0.3s;
        }
        .feature-card:hover {
            background: #f8f9fa;
        }
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #667eea;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card p-5 mb-5">
                <div class="text-center mb-5">
                    <h1 class="display-4 fw-bold text-dark mb-3"><?= e($title ?? 'MVC System') ?></h1>
                    <p class="lead text-muted mb-4"><?= e($message ?? 'Добро пожаловать в нашу MVC систему с плагинами!') ?></p>

                    <div class="row mt-5">
                        <div class="col-md-6 mb-4">
                            <a href="/admin" class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-speedometer2 me-2"></i>Панель администратора
                            </a>
                        </div>
                        <div class="col-md-6 mb-4">
                            <a href="/login" class="btn btn-outline-primary btn-lg w-100">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Вход в систему
                            </a>
                        </div>
                    </div>
                </div>

                <h3 class="text-center mb-4 text-dark">Возможности системы</h3>
                <div class="row">
                    <?php
                    $features = $features ?? [
                            'Модульная архитектура MVC' => 'bi-diagram-3',
                            'Поддержка плагинов' => 'bi-plug',
                            'Система виджетов' => 'bi-grid-3x3-gap',
                            'Современный интерфейс' => 'bi-palette',
                            'Безопасная аутентификация' => 'bi-shield-check',
                            'Гибкая маршрутизация' => 'bi-signpost-split',
                            'База данных' => 'bi-database',
                            'RESTful API' => 'bi-code-slash'
                        ];

                    foreach ($features as $name => $icon):
                        ?>
                        <div class="col-md-3 col-sm-6 mb-4">
                            <div class="feature-card">
                                <div class="feature-icon">
                                    <i class="bi <?= e($icon) ?>"></i>
                                </div>
                                <h5 class="fw-bold"><?= e($name) ?></h5>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="text-center mt-5">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Система успешно запущена и работает!
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>