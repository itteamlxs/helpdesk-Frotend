<?php
// Calcular BASE_PATH dinámicamente
$basePath = str_replace('/public', '', dirname($_SERVER['SCRIPT_NAME']));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?? 'Helpdesk System' ?></title>
    
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?= $basePath ?>/assets/css/custom.css" rel="stylesheet">
    
    <!-- 🆕 PASAR DATOS DEL USUARIO A JAVASCRIPT -->
    <script>
        // Configurar BASE_PATH global
        window.BASE_PATH = '<?= $basePath ?>';
        
        // 🔐 PASAR DATOS DEL USUARIO DESDE PHP
        <?php if (isset($_SESSION['usuario'])): ?>
            window.userId = <?= $_SESSION['usuario']['id'] ?>;
            window.userRole = <?= $_SESSION['usuario']['rol_id'] ?>;
            window.userName = '<?= addslashes($_SESSION['usuario']['nombre']) ?>';
            window.userEmail = '<?= addslashes($_SESSION['usuario']['correo'] ?? '') ?>';
            
            console.log('✅ Usuario cargado desde sesión:', {
                id: window.userId,
                role: window.userRole,
                name: window.userName,
                email: window.userEmail
            });
        <?php else: ?>
            console.warn('⚠️ No hay usuario en sesión - Redirigiendo a login');
            window.userId = null;
            window.userRole = 1; // Default cliente
            window.userName = 'Invitado';
            window.userEmail = '';
            
            // Si no hay sesión y no estamos en login, redirigir
            if (window.location.search.indexOf('ruta=login') === -1) {
                window.location.href = '?ruta=login';
            }
        <?php endif; ?>
        
        // Debug info
        console.log('🔧 BASE_PATH configurado:', window.BASE_PATH);
        console.log('🔐 Permisos de usuario:', {
            role: window.userRole,
            canEdit: window.userRole >= 2,
            canAdmin: window.userRole >= 3
        });
    </script>
</head>
<body>
    <!-- Navbar -->
    <?php include __DIR__ . '/../components/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <?php include __DIR__ . '/../components/sidebar.php'; ?>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <?php include __DIR__ . '/../components/alerts.php'; ?>
                
                <main>
                    <?php 
                    // El contenido específico se incluye aquí
                    if (isset($contenido)) {
                        echo $contenido;
                    }
                    ?>
                </main>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- API Client y App JS con rutas corregidas -->
    <script src="<?= $basePath ?>/assets/js/api.js"></script>
    <script src="<?= $basePath ?>/assets/js/app.js"></script>
    
    <!-- Módulos específicos con rutas corregidas -->
    <?php if (isset($jsModules) && is_array($jsModules)): ?>
        <?php foreach ($jsModules as $module): ?>
            <script src="<?= $basePath ?>/assets/js/modules/<?= $module ?>.js"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- 🔧 DEBUG FINAL - Verificar que todo esté cargado -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Verificar APIs disponibles
            if (typeof window.api !== 'undefined') {
                console.log('✅ API Client cargado correctamente');
            } else {
                console.error('❌ API Client no cargado - revisar ruta');
            }
            
            // Verificar datos de usuario
            if (window.userId && window.userRole) {
                console.log('✅ Datos de usuario disponibles para JavaScript');
                console.log(`🎭 Rol: ${window.userRole} (${window.userRole >= 2 ? 'Técnico/Admin' : 'Cliente'})`);
            } else {
                console.warn('⚠️ Datos de usuario no disponibles');
            }
            
            // Debug de módulos cargados
            const scripts = Array.from(document.scripts).filter(s => s.src.includes('/assets/js/'));
            console.log('📦 Scripts JS cargados:', scripts.map(s => s.src));
        });
    </script>
</body>
</html>