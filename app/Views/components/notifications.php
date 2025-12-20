<?php
// Проверяем, есть ли флеш-сообщения
$hasFlashMessage = isset($_SESSION['flash_message']) && !empty(trim($_SESSION['flash_message']));
$hasFlashError = isset($_SESSION['flash_error']) && !empty(trim($_SESSION['flash_error']));
?>

<?php if ($hasFlashMessage || $hasFlashError): ?>
    <div class="notifications-container">
        <?php if ($hasFlashMessage): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                <?= htmlspecialchars((string)$_SESSION['flash_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Закрыть"></button>
            </div>
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>

        <?php if ($hasFlashError): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?= htmlspecialchars((string)$_SESSION['flash_error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Закрыть"></button>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>
    </div>

    <script>
        // Автоматическое скрытие flash-сообщений через 5 секунд
        document.addEventListener('DOMContentLoaded', function() {
            const flashAlerts = document.querySelectorAll('.notifications-container .alert');

            flashAlerts.forEach(alert => {
                setTimeout(() => {
                    if (alert && alert.parentNode) {
                        const bsAlert = bootstrap.Alert.getInstance(alert) || new bootstrap.Alert(alert);
                        bsAlert.close();
                    }
                }, 5000);
            });
        });
    </script>
<?php endif; ?>