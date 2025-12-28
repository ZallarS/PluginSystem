<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Ошибка сервера</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .error-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
        }
        .error-code {
            font-size: 6rem;
            font-weight: bold;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="error-container text-center">
                <div class="error-code">500</div>
                <h1 class="display-4 mb-4">Внутренняя ошибка сервера</h1>
                <p class="lead mb-4">Произошла внутренняя ошибка сервера. Пожалуйста, попробуйте позже или свяжитесь с администратором.</p>

                <div class="mt-5">
                    <a href="/" class="btn btn-primary btn-lg me-3">
                        <i class="bi bi-house-door me-2"></i>На главную
                    </a>
                    <button onclick="window.location.reload()" class="btn btn-outline-primary btn-lg">
                        <i class="bi bi-arrow-clockwise me-2"></i>Обновить страницу
                    </button>
                </div>

                <?php
                // Проверяем, доступна ли информация об ошибке
                if (isset($e) && env('APP_DEBUG', false)):
                    ?>
                    <div class="mt-5 text-start">
                        <div class="alert alert-danger">
                            <h4>Отладочная информация:</h4>
                            <p><strong>Тип:</strong> <?= htmlspecialchars(get_class($e)) ?></p>
                            <p><strong>Сообщение:</strong> <?= htmlspecialchars($e->getMessage()) ?></p>
                            <p><strong>Файл:</strong> <?= htmlspecialchars($e->getFile()) ?> (строка <?= $e->getLine() ?>)</p>

                            <h5 class="mt-3">Трассировка вызовов:</h5>
                            <pre class="bg-dark text-light p-3 rounded mt-3" style="font-size: 12px; max-height: 300px; overflow: auto;">
<?= htmlspecialchars($e->getTraceAsString()) ?>
                                </pre>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>