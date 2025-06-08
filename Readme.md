# Helpdesk Support System (PHP MVC)

Proyecto backend puro en PHP orientado a soporte técnico, con arquitectura MVC modular, controladores desacoplados, seguridad integrada y configuración por entorno (`.env`).

## 📁 Estructura del Proyecto

```
helpdesk/
├── config/
│   ├── env_loader.php
│   └── session_start.php
├── controllers/
│   ├── UsersController.php
│   ├── TicketsController.php
│   ├── CommentsController.php
│   ├── SettingsController.php
│   ├── SLAController.php
│   ├── AuditController.php
│   └── SecurityController.php
├── core/
│   ├── BaseController.php
│   ├── Database.php
│   ├── Logger.php
│   └── Mailer.php
├── public/
│   └── index.php
├── scripts/
│   └── auto_close_tickets.php
├── security/
│   ├── csrf.php
│   └── middleware.php
├── tests/
│   ├── test_usuarios.php
│   ├── test_all_modules.php
│   ├── test_insert_config.php
│   ├── test_insert_tickets_sla_comments.php
│   └── mail_test.php
├── logs/
│   └── app.log
├── .env
└── README.md
```

## 📌 Descripción rápida de carpetas

- **config/** → Archivos de carga de entorno y sesión segura.
- **controllers/** → Lógica REST de cada módulo.
- **core/** → Componentes de infraestructura reutilizables.
- **public/** → Punto de entrada (`index.php`).
- **scripts/** → Automatizaciones tipo cron.
- **security/** → Protección CSRF y control de roles.
- **tests/** → Scripts funcionales para poblar, validar y testear correo.
- **logs/** → Archivo de bitácora de errores y acciones internas.

## 🛠️ Instalación (entorno Fedora + XAMPP)

1. Instala XAMPP (ya completado por el usuario).
2. Coloca el proyecto en `/opt/lampp/htdocs/helpdesk/`.
3. Crea el archivo `.env` en la raíz con los siguientes valores:

   ```env
   DB_HOST=localhost
   DB_NAME=ticketsdb
   DB_USER=root
   DB_PASS=
   SMTP_HOST=smtp.gmail.com
   SMTP_USER=email@gmail
   SMTP_PASS=secret_password
   NOTIFICACIONES_EMAIL=true
   SESSION_NAME=HELPDESK_SESSION
   ```

    ```
    Si usas Gmail genera tu pass secreta desde el siguiente enlace:  https://myaccount.google.com/apppasswords
    ```

4. Crea la base de datos `ticketsdb` y ejecuta el schema inicial desde phpMyAdmin.
5. Accede a la app mediante rutas como:
   - `http://localhost/helpdesk/public/index.php?ruta=usuarios`
6. Asegúrate de crear el directorio de logs:
   ```bash
   mkdir logs
   chmod 775 logs
   ```
7. Instala PHPMailer con Composer si no está:
   ```bash
   composer require phpmailer/phpmailer
   ```
8. Valida el correo con:
   - `http://localhost/helpdesk/tests/mail_test.php`

---

## 🔌 Documentación Técnica de la API REST

### 🔹 Usuarios (`ruta=usuarios`)
- `GET` → Listar todos
- `GET&id={id}` → Ver uno
- `POST` → Crear usuario (JSON: nombre, correo, password, rol_id)
- `PUT&id={id}` → Actualizar usuario
- `DELETE&id={id}` → Eliminar usuario

### 🔹 Tickets (`ruta=tickets`)
- `GET` → Listar todos
- `GET&id={id}` → Ver uno
- `POST` → Crear (JSON: titulo, descripcion, categoria, prioridad, cliente_id)
- `PUT&id={id}` → Actualizar técnico o estado
- `DELETE&id={id}` → Eliminar

### 🔹 Comentarios (`ruta=comments&ticket_id={id}`)
- `GET` → Listar comentarios del ticket
- `POST` → Agregar (JSON: usuario_id, contenido, interno)

### 🔹 Configuración (`ruta=settings`)
- `GET` → Obtener última config
- `PUT` → Guardar nueva config

### 🔹 SLA (`ruta=sla`)
- `GET` → Listar SLA por prioridad
- `PUT` → Guardar arreglo con prioridades y tiempos

### 🔹 Auditoría (`ruta=audit`)
- `GET` → Listar
- `GET&id={id}` → Detalle

### 🔹 Seguridad
- `POST ruta=login` → Login (correo, password)
- `POST ruta=logout` → Logout
- `GET ruta=csrf-token` → Token CSRF actual

---

## 👥 Documentación Funcional para Usuarios y Administradores

### Usuario final (cliente):
- Se registra mediante `/usuarios` con `rol_id = 1` (cliente).
- Puede crear tickets con título, descripción, categoría y prioridad.
- Puede consultar el estado de sus tickets.
- Puede añadir comentarios públicos a sus tickets.

### Técnico:
- Visualiza todos los tickets.
- Añade comentarios internos o públicos.
- Cambia estado del ticket: abierto, en_espera, resuelto, cerrado.
- Tiene `rol_id = 2`.

### Administrador:
- Accede a configuración global (`/settings`).
- Define tiempos SLA por prioridad (`/sla`).
- Consulta logs de auditoría (`/audit`).
- Administra usuarios y roles.
- Tiene `rol_id = 3`.

### Funciones compartidas:
- Todos deben autenticarse vía `/login` y finalizar con `/logout`.
- Pueden obtener token CSRF para protección en formularios (`/csrf-token`).

---

## 🚀 Recomendaciones para Despliegue en Producción

1. **Protege `.env`**
   - Asegúrate de que `.env` esté fuera del alcance público o negado por `.htaccess`.

2. **Cambia `display_errors` a `0`**
   ```php
   ini_set('display_errors', 0);
   error_reporting(0);
   ```

3. **Configura HTTPS obligatorio**
   - Usa redirección a HTTPS vía Apache o Nginx.

4. **Agrega encabezados de seguridad** en `index.php`:
   ```php
   header('X-Frame-Options: DENY');
   header('X-Content-Type-Options: nosniff');
   header('Referrer-Policy: no-referrer');
   ```

5. **Revisar permisos de directorios**
   - `/logs` debe tener `775`, otros `755` o `750`.

6. **Elimina scripts de prueba**
   - Borra `/tests/*` en el entorno de producción.

7. **Activa regeneración de sesiones**
   - Considera regenerar el ID de sesión al iniciar y cerrar sesión.

8. **Backup periódico**
   - Exporta `ticketsdb` y copia `/logs` regularmente.

---

> 📌 Este sistema está optimizado para backend puro y puede conectarse fácilmente a frontend en React, Vue o aplicaciones móviles.

¿Deseas que empaque todo esto como `.zip` para entrega o documentación final exportable?
