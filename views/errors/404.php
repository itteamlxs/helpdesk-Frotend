<?php 
$titulo = 'Página No Encontrada';
ob_start(); 
?>

<div class="container-fluid d-flex align-items-center justify-content-center" style="min-height: 60vh;">
    <div class="text-center">
        <div class="error-content">
            <i class="fas fa-exclamation-triangle fa-5x text-warning mb-4"></i>
            <h1 class="display-4 fw-bold">404</h1>
            <h2 class="h4 mb-3">Página No Encontrada</h2>
            <p class="text-muted mb-4">
                La página que buscas no existe o ha sido movida.
            </p>
            <div>
                <a href="?ruta=dashboard" class="btn btn-primary me-2">
                    <i class="fas fa-home"></i> Ir al Dashboard
                </a>
                <button onclick="history.back()" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </button>
            </div>
        </div>
    </div>
</div>

<?php 
$contenido = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>