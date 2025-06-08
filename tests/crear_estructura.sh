#!/bin/bash

# Crear directorios
mkdir -p views/layouts \
         views/auth \
         views/dashboard \
         views/usuarios \
         views/tickets \
         views/settings \
         views/components \
         assets/css \
         assets/js/modules

# Crear archivos con comentarios
# Layouts
echo "<?php // Layout principal con Bootstrap ?>" > views/layouts/app.php
echo "<?php // Layout para login ?>" > views/layouts/auth.php

# Auth
echo "<?php // Formulario de login ?>" > views/auth/login.php
echo "<?php // Confirmación logout ?>" > views/auth/logout.php

# Dashboard
echo "<?php // Dashboard principal ?>" > views/dashboard/index.php

# Usuarios
echo "<?php // Lista de usuarios ?>" > views/usuarios/index.php
echo "<?php // Crear usuario ?>" > views/usuarios/create.php
echo "<?php // Editar usuario ?>" > views/usuarios/edit.php

# Tickets
echo "<?php // Lista de tickets ?>" > views/tickets/index.php
echo "<?php // Crear ticket ?>" > views/tickets/create.php
echo "<?php // Ver ticket + comentarios ?>" > views/tickets/show.php
echo "<?php // Editar ticket ?>" > views/tickets/edit.php

# Settings
echo "<?php // Configuración sistema ?>" > views/settings/index.php

# Components
echo "<?php // Barra de navegación ?>" > views/components/navbar.php
echo "<?php // Menú lateral ?>" > views/components/sidebar.php
echo "<?php // Alertas y mensajes ?>" > views/components/alerts.php

# CSS
echo "/* Estilos personalizados mínimos */" > assets/css/custom.css

# JS
echo "// Lógica general" > assets/js/app.js
echo "// Cliente API REST" > assets/js/api.js

# JS Módulos
echo "// JS específico usuarios" > assets/js/modules/usuarios.js
echo "// JS específico tickets" > assets/js/modules/tickets.js
echo "// JS autenticación" > assets/js/modules/auth.js

echo "Estructura de archivos y directorios creada exitosamente. ✅"
