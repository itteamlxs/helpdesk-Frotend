<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Helpdesk System</title>
    
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
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }
        
        .shake {
            animation: shake 0.5s ease-in-out;
        }
        
        /* Loading spinner */
        .spinner {
            border: 2px solid #ffffff;
            border-top: 2px solid transparent;
            border-radius: 50%;
            width: 16px;
            height: 16px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Hover effects */
        .input-focus:focus {
            transform: translateY(-1px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .btn-hover:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <!-- Container principal -->
    <div class="w-full max-w-md">
        <!-- Card principal -->
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden fade-in">
            <!-- Header con espacio para logo -->
            <div class="px-8 pt-12 pb-8 text-center">
                <!-- Espacio para logo -->
                <div class="mb-6">
                    <!-- Logo placeholder - reemplazar con tu logo -->
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl mx-auto flex items-center justify-center mb-4">
                        <i class="fas fa-headset text-white text-2xl"></i>
                    </div>
                    <!-- Alternativamente, usar imagen:
                    <img src="/path/to/logo.png" alt="Logo" class="w-16 h-16 mx-auto mb-4 rounded-2xl">
                    -->
                </div>
                
                <!-- Título minimalista -->
                <h1 class="text-3xl font-semibold text-gray-900 mb-2 tracking-tight">Helpdesk</h1>
                <p class="text-gray-500 text-sm font-medium">Please enter your username and password to login.</p>
            </div>
            
            <!-- Formulario -->
            <div class="px-8 pb-12">
                <!-- Container para alertas -->
                <div id="alertContainer" class="mb-6"></div>
                
                <!-- Formulario de login -->
                <form id="loginForm" class="space-y-6">
                    <!-- Campo Username -->
                    <div>
                        <label for="correo" class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                        <input 
                            type="email" 
                            id="correo" 
                            name="correo" 
                            class="input-focus w-full px-4 py-3 border border-gray-200 rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                            placeholder="Enter your username"
                            required
                            autocomplete="email"
                        >
                    </div>
                    
                    <!-- Campo Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="input-focus w-full px-4 py-3 border border-gray-200 rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                            placeholder="Enter your password"
                            required
                            autocomplete="current-password"
                        >
                    </div>
                    
                    <!-- Botón de login -->
                    <button 
                        type="submit" 
                        id="loginButton"
                        class="btn-hover w-full bg-gray-900 hover:bg-gray-800 text-white font-medium py-3 px-4 rounded-lg transition-all duration-200 flex items-center justify-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span id="buttonText">Login</span>
                        <div id="loadingSpinner" class="spinner hidden"></div>
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Footer opcional -->
        <div class="text-center mt-8">
            <p class="text-gray-400 text-sm">Helpdesk System © 2025</p>
        </div>
    </div>

    <script>
        // ================================================================================================
        // 🚀 JAVASCRIPT REFACTORIZADO - MANTIENE LÓGICA ORIGINAL + NUEVO DISEÑO
        // ================================================================================================
        
        // Sistema de alertas mejorado
        function showAlert(message, type = 'info') {
            const alertContainer = document.getElementById('alertContainer');
            
            // Limpiar alertas previas
            alertContainer.innerHTML = '';
            
            // Mapeo de tipos a clases Tailwind
            const typeClasses = {
                'success': 'bg-green-50 border-green-200 text-green-800',
                'danger': 'bg-red-50 border-red-200 text-red-800',
                'error': 'bg-red-50 border-red-200 text-red-800',
                'warning': 'bg-yellow-50 border-yellow-200 text-yellow-800',
                'info': 'bg-blue-50 border-blue-200 text-blue-800'
            };
            
            const alertClasses = typeClasses[type] || typeClasses['info'];
            
            const alertHTML = `
                <div class="border rounded-lg p-4 ${alertClasses} fade-in">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-${getIconForType(type)} text-sm"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium">${message}</p>
                        </div>
                        <div class="ml-auto pl-3">
                            <button onclick="this.parentElement.parentElement.parentElement.remove()" class="inline-flex text-sm">
                                <i class="fas fa-times hover:opacity-75"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            alertContainer.innerHTML = alertHTML;
            
            // Auto-remove para errores después de 5 segundos
            if (type === 'error' || type === 'danger') {
                setTimeout(() => {
                    const alert = alertContainer.querySelector('.border');
                    if (alert) {
                        alert.style.opacity = '0';
                        alert.style.transform = 'translateY(-10px)';
                        setTimeout(() => alert.remove(), 300);
                    }
                }, 5000);
            }
        }
        
        function getIconForType(type) {
            const icons = {
                'success': 'check-circle',
                'danger': 'exclamation-triangle',
                'error': 'exclamation-triangle',
                'warning': 'exclamation-circle',
                'info': 'info-circle'
            };
            return icons[type] || 'info-circle';
        }
        
        // Función para mostrar/ocultar loading
        function setLoading(isLoading) {
            const button = document.getElementById('loginButton');
            const buttonText = document.getElementById('buttonText');
            const spinner = document.getElementById('loadingSpinner');
            
            button.disabled = isLoading;
            
            if (isLoading) {
                buttonText.classList.add('hidden');
                spinner.classList.remove('hidden');
                button.classList.add('opacity-75');
            } else {
                buttonText.classList.remove('hidden');
                spinner.classList.add('hidden');
                button.classList.remove('opacity-75');
            }
        }
        
        // Función para animación de error
        function showErrorAnimation() {
            const form = document.getElementById('loginForm');
            form.classList.add('shake');
            setTimeout(() => {
                form.classList.remove('shake');
            }, 500);
        }
        
        // ================================================================================================
        // 🔥 LÓGICA ORIGINAL DEL LOGIN - SIN CAMBIOS EN LA API
        // ================================================================================================
        
        // Handle login form - MANTIENE LA LÓGICA ORIGINAL EXACTA
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Limpiar alertas previas
            document.getElementById('alertContainer').innerHTML = '';
            
            // Mostrar loading
            setLoading(true);
            
            const formData = new FormData(this);
            const data = {
                correo: formData.get('correo'),
                password: formData.get('password')
            };
            
            try {
                // MISMA LLAMADA API QUE EL LOGIN ORIGINAL
                const response = await fetch('?api=1&ruta=login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (response.ok) {
                    // Éxito - cambiar botón a verde
                    const button = document.getElementById('loginButton');
                    button.classList.remove('bg-gray-900', 'hover:bg-gray-800');
                    button.classList.add('bg-green-600');
                    document.getElementById('buttonText').textContent = 'Success!';
                    
                    showAlert('Login exitoso, redirigiendo...', 'success');
                    
                    // MISMA REDIRECCIÓN QUE EL LOGIN ORIGINAL
                    setTimeout(() => {
                        window.location.href = '?ruta=dashboard';
                    }, 1000);
                } else {
                    // Error - mostrar animación y resetear
                    setLoading(false);
                    showErrorAnimation();
                    showAlert(result.error || 'Error en el login', 'error');
                    
                    // Focus en el campo email para reintento
                    document.getElementById('correo').focus();
                }
            } catch (error) {
                // Error de conexión
                setLoading(false);
                showErrorAnimation();
                showAlert('Error de conexión', 'error');
                console.error('Login error:', error);
                
                // Focus en el campo email para reintento
                document.getElementById('correo').focus();
            }
        });
        
        // Validación en tiempo real
        document.getElementById('correo').addEventListener('input', function() {
            if (this.value && !this.validity.valid) {
                this.classList.add('border-red-300', 'ring-red-500');
                this.classList.remove('border-gray-200');
            } else {
                this.classList.remove('border-red-300', 'ring-red-500');
                this.classList.add('border-gray-200');
            }
        });
        
        document.getElementById('password').addEventListener('input', function() {
            if (this.value.length > 0 && this.value.length < 3) {
                this.classList.add('border-red-300', 'ring-red-500');
                this.classList.remove('border-gray-200');
            } else {
                this.classList.remove('border-red-300', 'ring-red-500');
                this.classList.add('border-gray-200');
            }
        });
        
        // Focus automático en el primer campo
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('correo').focus();
        });
        
        // Prevenir navegación hacia atrás si ya está logueado
        if (window.history && window.history.pushState) {
            window.history.pushState('forward', null, window.location.href);
            window.addEventListener('popstate', function() {
                window.history.pushState('forward', null, window.location.href);
            });
        }
    </script>
</body>
</html>