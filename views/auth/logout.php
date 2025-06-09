<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cerrando Sesión - Helpdesk System</title>
    
    <!-- Tailwind CSS para diseño minimalista -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome para iconos -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        
        /* Custom animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }
        
        .spinner {
            border: 3px solid #e5e7eb;
            border-top: 3px solid #3b82f6;
            border-radius: 50%;
            width: 48px;
            height: 48px;
            animation: spin 1s linear infinite;
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        /* States transitions */
        .state-transition {
            transition: all 0.5s ease-in-out;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <!-- Container principal -->
    <div class="w-full max-w-md">
        <!-- Card principal -->
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden fade-in">
            <!-- Header con logo -->
            <div class="px-8 pt-8 pb-4 text-center">
                <!-- Logo -->
                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl mx-auto flex items-center justify-center mb-4">
                    <i class="fas fa-headset text-white text-2xl"></i>
                </div>
            </div>
            
            <!-- Content área -->
            <div class="px-8 pb-12">
                
                <!-- Loading State -->
                <div id="loadingState" class="text-center state-transition">
                    <div class="spinner mx-auto mb-6"></div>
                    <h3 class="text-2xl font-semibold text-gray-900 mb-3">Cerrando Sesión</h3>
                    <p class="text-gray-500 text-sm">Por favor espera un momento...</p>
                    <div class="mt-4">
                        <div class="flex justify-center space-x-1">
                            <div class="w-2 h-2 bg-blue-400 rounded-full pulse" style="animation-delay: 0s;"></div>
                            <div class="w-2 h-2 bg-blue-400 rounded-full pulse" style="animation-delay: 0.2s;"></div>
                            <div class="w-2 h-2 bg-blue-400 rounded-full pulse" style="animation-delay: 0.4s;"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Success State -->
                <div id="successState" class="text-center state-transition hidden">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-check-circle text-green-500 text-4xl"></i>
                    </div>
                    <h3 class="text-2xl font-semibold text-green-700 mb-3">Sesión Cerrada</h3>
                    <p class="text-gray-600 mb-2">Has cerrado sesión correctamente</p>
                    <p class="text-gray-500 text-sm">Redirigiendo al login...</p>
                    
                    <!-- Progress bar -->
                    <div class="w-full bg-gray-200 rounded-full h-1 mt-6">
                        <div id="progressBar" class="bg-green-500 h-1 rounded-full transition-all duration-2000 ease-out" style="width: 0%"></div>
                    </div>
                </div>
                
                <!-- Error State -->
                <div id="errorState" class="text-center state-transition hidden">
                    <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-exclamation-triangle text-yellow-500 text-4xl"></i>
                    </div>
                    <h3 class="text-2xl font-semibold text-yellow-700 mb-3">Error al Cerrar Sesión</h3>
                    <p class="text-gray-600 mb-6">Hubo un problema, pero tu sesión se limpiará localmente</p>
                    
                    <button 
                        onclick="irAlLogin()" 
                        class="w-full bg-gray-900 hover:bg-gray-800 text-white font-medium py-3 px-4 rounded-lg transition-all duration-200 flex items-center justify-center space-x-2"
                    >
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Ir al Login</span>
                    </button>
                </div>
                
            </div>
        </div>
        
        <!-- Footer -->
        <div class="text-center mt-8">
            <p class="text-gray-400 text-sm">Helpdesk System © 2025</p>
        </div>
    </div>

    <script>
        // ================================================================================================
        // 🚀 JAVASCRIPT REFACTORIZADO - MANTIENE LÓGICA ORIGINAL + NUEVO DISEÑO
        // ================================================================================================
        
        // Función para ir al login - MANTIENE LÓGICA ORIGINAL
        function irAlLogin() {
            window.location.href = '?ruta=login';
        }
        
        // Función para mostrar estado
        function showState(stateId) {
            const states = ['loadingState', 'successState', 'errorState'];
            
            states.forEach(id => {
                const element = document.getElementById(id);
                if (id === stateId) {
                    element.classList.remove('hidden');
                    // Fade in effect
                    setTimeout(() => {
                        element.style.opacity = '1';
                        element.style.transform = 'translateY(0)';
                    }, 50);
                } else {
                    element.classList.add('hidden');
                }
            });
        }
        
        // Función para animar progress bar
        function animateProgressBar() {
            const progressBar = document.getElementById('progressBar');
            let width = 0;
            const interval = setInterval(() => {
                width += 50; // 2 segundos / 40 pasos = 50ms por paso
                progressBar.style.width = width + '%';
                
                if (width >= 100) {
                    clearInterval(interval);
                }
            }, 50);
        }
        
        // ================================================================================================
        // 🔥 FUNCIÓN PRINCIPAL DE LOGOUT - MANTIENE LÓGICA ORIGINAL EXACTA
        // ================================================================================================
        
        async function ejecutarLogout() {
            try {
                // MISMA LLAMADA API QUE EL LOGOUT ORIGINAL
                const response = await fetch('?api=1&ruta=logout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });
                
                // Mostrar estado de éxito
                showState('successState');
                animateProgressBar();
                
                // MISMA REDIRECCIÓN QUE EL LOGOUT ORIGINAL
                setTimeout(() => {
                    irAlLogin();
                }, 2000);
                
            } catch (error) {
                console.error('Error en logout:', error);
                
                // Mostrar estado de error
                showState('errorState');
                
                // MISMA LÓGICA DE LIMPIEZA QUE EL LOGOUT ORIGINAL
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
        
        // ================================================================================================
        // 🔄 INICIALIZACIÓN - MANTIENE LÓGICA ORIGINAL
        // ================================================================================================
        
        // Ejecutar logout automáticamente al cargar la página - LÓGICA ORIGINAL
        document.addEventListener('DOMContentLoaded', function() {
            // Pequeño delay para mejor UX - MANTIENE EL DELAY ORIGINAL
            setTimeout(ejecutarLogout, 500);
        });
        
        // Prevenir que el usuario navegue hacia atrás - LÓGICA ORIGINAL
        history.pushState(null, null, window.location.href);
        window.addEventListener('popstate', function() {
            history.pushState(null, null, window.location.href);
        });
        
        // Efectos adicionales para mejor UX
        document.addEventListener('DOMContentLoaded', function() {
            // Precargar estados para transiciones suaves
            const states = document.querySelectorAll('[id$="State"]');
            states.forEach(state => {
                state.style.opacity = '0';
                state.style.transform = 'translateY(10px)';
                state.style.transition = 'all 0.3s ease-out';
            });
            
            // Mostrar loading state inicial
            document.getElementById('loadingState').style.opacity = '1';
            document.getElementById('loadingState').style.transform = 'translateY(0)';
        });
    </script>
</body>
</html>