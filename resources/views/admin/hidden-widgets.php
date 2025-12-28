<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Скрытые виджеты</title>
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
        .widget-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        .widget-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.12);
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
        <a href="/admin/hidden-widgets" class="d-block mb-2 text-white text-decoration-none fw-bold">
            <i class="bi bi-eye-slash"></i> Скрытые виджеты
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
            <h1 class="h3 mb-0">Скрытые виджеты</h1>
            <p class="text-muted mb-0">Виджеты, которые были скрыты с дашборда</p>
        </div>
        <div>
            <a href="/admin" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left"></i> Назад к дашборду
            </a>
        </div>
    </div>

    <div id="hiddenWidgetsContainer">
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            Загрузка списка скрытых виджетов...
        </div>
    </div>

    <div class="mt-5">
        <div class="alert alert-light">
            <h5><i class="bi bi-lightbulb me-2"></i>Справка</h5>
            <p class="mb-0">Здесь отображаются виджеты, которые были скрыты с основного дашборда. Вы можете восстановить их, нажав кнопку "Показать".</p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Загружаем список скрытых виджетов через AJAX
    document.addEventListener('DOMContentLoaded', function() {
        fetch('/admin/hidden-widgets', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('hiddenWidgetsContainer');

                if (data.success && data.hidden_widgets && data.hidden_widgets.length > 0) {
                    let html = '<div class="row">';

                    data.hidden_widgets.forEach(widget => {
                        html += `
                            <div class="col-md-6 mb-4">
                                <div class="widget-card p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="mb-1">
                                                <i class="bi ${widget.icon} me-2"></i>
                                                ${widget.title}
                                            </h5>
                                            <small class="text-muted">${widget.description}</small>
                                        </div>
                                        <span class="badge bg-secondary">Скрыт</span>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <small><strong>Размер:</strong> ${widget.size}</small>
                                        </div>
                                        <div class="col-6">
                                            <small><strong>Источник:</strong> ${widget.source}</small>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-muted">ID: ${widget.id}</small>
                                        </div>

                                        <div>
                                            <button class="btn btn-sm btn-outline-success show-widget-btn"
                                                    data-widget-id="${widget.id}">
                                                <i class="bi bi-eye"></i> Показать
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });

                    html += '</div>';
                    container.innerHTML = html;

                    // Добавляем обработчики для кнопок показа
                    document.querySelectorAll('.show-widget-btn').forEach(button => {
                        button.addEventListener('click', function() {
                            const widgetId = this.dataset.widgetId;
                            showWidget(widgetId);
                        });
                    });
                } else {
                    container.innerHTML = `
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i>
                            Нет скрытых виджетов. Все виджеты отображаются на дашборде.
                        </div>
                    `;
                }
            })
            .catch(error => {
                document.getElementById('hiddenWidgetsContainer').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Ошибка при загрузке списка виджетов. Попробуйте обновить страницу.
                    </div>
                `;
            });
    });

    function showWidget(widgetId) {
        const formData = new FormData();
        formData.append('widget_id', widgetId);
        formData.append('action', 'show_widget');
        formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');

        fetch('/admin/toggle-widget', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Виджет восстановлен! Он снова появится на дашборде.');
                    window.location.reload();
                } else {
                    alert('Ошибка при восстановлении виджета: ' + (data.error || 'Неизвестная ошибка'));
                }
            })
            .catch(error => {
                alert('Ошибка сети при восстановлении виджета');
            });
    }
</script>
</body>
</html>