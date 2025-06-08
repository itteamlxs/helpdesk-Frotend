<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cerrando Sesión - Helpdesk System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            min-height: 100vh; 
        }
        .logout-container { 
            min-height: 100vh; 
        }
        .logout-card { 
            border: none; 
            border-radius: 15px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid logout-container d-flex align-items-center justify-content-center">
        <div class="row w-100">
            <div class="col-md-6 col-lg-4 mx-auto">
                <div class="card logout-card">
                    <div class="card-body p-5 text-center">
                        <!-- Loading state -->
                        <div id="loadingState">
                            <div class="spinner-border text-primary mb-4" role="status">
                                <span class="visually-hidden">Cerrando sesión...</span>
                            </div>
                            <h3 class="card-title">Cerrando Sesión</h3>
                            <p class="text-muted">Por favor espera un momento...</p>
                        </div>
                        
                        <!-- Success state -->
                        <div id="successState" class="d-none">
                            <i class="fas fa-check-circle fa-4x text-success mb-4"></i>
                            <h3 class="card-title text-success">Sesión Cerrada</h3>
                            <p class="text-muted">Has cerrado sesión correctamente</p>
                            <p class="text-muted">Redirigiendo al login...</p>
                        </div>
                        
                        <!-- Error state -->
                        <div id="errorState" class="d-none">
                            <i class="fas fa-exclamation-triangle fa-4x text-warning mb-4"></i>
                            <h3 class="card-title text-warning">Error al Cerrar Sesión</h3>
                            <p class="text-muted mb-4">Hubo un problema, pero tu sesión se limpiará localmente</p>
                            <button class="btn btn-primary" onclick="irAlLogin()">
                                <i class="fas fa-sign-in-alt"></i> Ir al Login
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Función para ir al login
        function irAlLogin() {
            window.location.href = '?ruta=login';
        }
        
        // Función principal de logout
        async function ejecutarLogout() {
            try {
                // Intentar logout via API
                const response = await fetch('?api=1&ruta=logout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });
                
                // Mostrar estado de éxito
                document.getElementById('loadingState').classList.add('d-none');
                document.getElementById('successState').classList.remove('d-none');
                
                // Redirigir después de 2 segundos
                setTimeout(() => {
                    irAlLogin();
                }, 2000);
                
            } catch (error) {
                console.error('Error en logout:', error);
                
                // Mostrar estado de error
                document.getElementById('loadingState').classList.add('d-none');
                document.getElementById('errorState').classList.remove('d-none');
                
                // Limpiar sesión local de todas formas
                if (typeof Storage !== "undefined") {
                    sessionStorage.clear();
                    localStorage.removeItem('user_session');
                }
                
                // Auto-redirigir después de 5 segundos en caso de error
                setTimeout(() => {
                    irAlLogin();
                }, 5000);
            }
        }
        
        // Ejecutar logout automáticamente al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            // Pequeño delay para mejor UX
            setTimeout(ejecutarLogout, 500);
        });
        
        // Prevenir que el usuario navegue hacia atrás
        history.pushState(null, null, window.location.href);
        window.addEventListener('popstate', function() {
            history.pushState(null, null, window.location.href);
        });
    </script>
</body>
</html>