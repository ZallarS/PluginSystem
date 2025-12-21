<?php
// Устанавливаем значения по умолчанию для переменных
$error = $error ?? null;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в систему | MVC System</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">

    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-card {
            max-width: 400px;
            margin: 100px auto;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="login-card">
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white text-center">
                <h4 class="mb-0"><i class="bi bi-box-arrow-in-right"></i> Вход в систему</h4>
            </div>

            <div class="card-body">
                <!-- Уведомления -->
                <?php include __DIR__ . '/../components/notifications.php'; ?>

                <!-- Ошибка формы -->
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i> <?= htmlspecialchars((string)$error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/login">
                    <div class="mb-3">
                        <label for="username" class="form-label">Имя пользователя</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control" id="username" name="username"
                                   value="<?= htmlspecialchars((string)($_POST['username'] ?? '')) ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Пароль</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-right"></i> Войти
                        </button>

                        <a href="/quick-login" class="btn btn-outline-secondary">
                            <i class="bi bi-lightning"></i> Быстрый вход (для теста)
                        </a>
                    </div>
                </form>

                <hr class="my-4">

                <div class="text-center">
                    <p class="text-muted small">
                        <i class="bi bi-info-circle"></i> Для тестирования используйте:<br>
                        <strong>Логин:</strong> admin | <strong>Пароль:</strong> admin
                    </p>
                </div>
            </div>

            <div class="card-footer text-center">
                <a href="/" class="text-decoration-none">
                    <i class="bi bi-arrow-left"></i> Вернуться на главную
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>