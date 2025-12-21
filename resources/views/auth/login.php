<?php
// Устанавливаем значения по умолчанию для переменных
$error = $error ?? null;
$errors = $errors ?? [];
$csrf_token = $csrf_token ?? ($_SESSION['csrf_token'] ?? '');
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в систему | MVC System</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 40px;
        }
        .login-container {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
        }
        .login-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px 10px 0 0;
            padding: 20px;
            text-align: center;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 12px;
            font-weight: 600;
        }
        .btn-login:hover {
            opacity: 0.9;
            color: white;
        }
    </style>
</head>
<body>
<div class="login-container">
    <div class="card login-card">
        <div class="login-header">
            <h4><i class="bi bi-box-arrow-in-right"></i> Вход в систему</h4>
            <p class="mb-0">Панель администратора MVC</p>
        </div>

        <div class="card-body p-4">
            <!-- Уведомления -->
            <?php if (isset($_SESSION['flash_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    <?= htmlspecialchars($_SESSION['flash_message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['flash_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($_SESSION['flash_error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>

            <!-- Ошибка формы -->
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/login">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrf_token) ?>">

                <div class="mb-3">
                    <label for="username" class="form-label">Имя пользователя</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text"
                               class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
                               id="username"
                               name="username"
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                               required>
                        <?php if (isset($errors['username'])): ?>
                            <div class="invalid-feedback">
                                <?= htmlspecialchars($errors['username']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Пароль</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password"
                               class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                               id="password"
                               name="password"
                               required>
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback">
                                <?= htmlspecialchars($errors['password']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="d-grid gap-2 mb-3">
                    <button type="submit" class="btn btn-login">
                        <i class="bi bi-box-arrow-in-right"></i> Войти
                    </button>
                </div>

                <?php if (env('APP_ENV', 'production') !== 'production'): ?>
                    <div class="d-grid gap-2">
                        <a href="/quick-login" class="btn btn-outline-secondary">
                            <i class="bi bi-lightning"></i> Быстрый вход (только для теста)
                        </a>
                    </div>
                <?php endif; ?>
            </form>

            <hr class="my-4">

            <div class="text-center">
                <p class="text-muted small mb-2">
                    <i class="bi bi-info-circle"></i> Для тестирования используйте:
                </p>
                <p class="mb-0">
                    <strong>Логин:</strong> <?= htmlspecialchars(env('ADMIN_USERNAME', 'admin')) ?> |
                    <strong>Пароль:</strong> <?= htmlspecialchars(env('ADMIN_PASSWORD', 'admin')) ?>
                </p>
            </div>
        </div>

        <div class="card-footer text-center bg-transparent">
            <a href="/" class="text-decoration-none">
                <i class="bi bi-arrow-left"></i> Вернуться на главную
            </a>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Автоматическое скрытие алертов через 5 секунд
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                bsAlert.close();
            });
        }, 5000);
    });
</script>
</body>
</html>