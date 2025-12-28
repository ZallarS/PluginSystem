<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Страница не найдена</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .error-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 40px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="error-container text-center">
                <h1 class="display-1 fw-bold text-primary">404</h1>
                <h2 class="mb-4">Страница не найдена</h2>
                <p class="lead mb-4">Запрашиваемая страница не существует или была перемещена.</p>

                <div class="mt-5">
                    <a href="/" class="btn btn-primary btn-lg me-3">
                        На главную
                    </a>
                    <a href="javascript:history.back()" class="btn btn-outline-primary btn-lg">
                        Вернуться назад
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>