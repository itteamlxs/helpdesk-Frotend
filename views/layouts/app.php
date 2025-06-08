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
    <link href="/assets/css/custom.css" rel="stylesheet">
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
    
    <!-- API Client -->
    <script src="/assets/js/api.js"></script>
    <script src="/assets/js/app.js"></script>
    
    <!-- Módulos específicos -->
    <?php if (isset($jsModules) && is_array($jsModules)): ?>
        <?php foreach ($jsModules as $module): ?>
            <script src="/assets/js/modules/<?= $module ?>.js"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>