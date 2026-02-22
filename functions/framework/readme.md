# Terra Framework

Framework PHP modular para WordPress con autoloading PSR-4.

## Instalacion

```bash
cd tu-tema/functions/
git clone git@github.com:terra-hq/terra-framework.git framework
```

Agregar a `.gitignore` del proyecto principal:
```
functions/framework/
```

## Estructura

```
framework/
├── classes/              # Clases PHP con autoload
│   ├── index.php         # Autoloader + class map
│   ├── Admin_Controller.php
│   ├── AJAX_Request.php
│   ├── Call_Cronjob.php
│   ├── Clean_Wp.php
│   ├── Custom_API_Endpoint.php
│   ├── Custom_Blocks.php
│   ├── Custom_Post_Type.php
│   ├── Custom_Search_Modules.php
│   ├── Custom_Taxonomy.php
│   ├── Default_Blocks.php
│   ├── Default_Files.php
│   ├── Default_Setup.php
│   ├── Google_Search_Console.php
│   ├── Grammar.php
│   ├── Images.php
│   ├── Mail_To.php
│   ├── Manage_Columns.php
│   ├── Redirect_Stage_Urls.php
│   ├── Security.php
│   ├── System_Warning.php
│   ├── Terra_Lighthouse.php
│   ├── Terra_URL_Health_Check.php
│   ├── WP_Functionality.php
│   └── WP_Vulnerability_Checker.php
│
├── includes/             # Archivos auxiliares de clases
│   ├── google_search_console/
│   ├── lighthouse/
│   ├── search_modules/
│   ├── system_warning/
│   └── url_health_check/
│
├── blocks/               # Templates ACF blocks
│
└── utilities/            # Funciones helper globales
```

## Requisitos

- WordPress 5.3+
- PHP 7.4+
- ACF Pro (para bloques y opciones)

---

## Clases Disponibles

### Indice rapido

| Clase | Descripcion | Auto-Init |
|-------|-------------|-----------|
| [Default_Setup](#default_setup) | Bootstrap principal del framework | No |
| [Default_Files](#default_files) | Carga archivos de deploy | No |
| [Clean_Wp](#clean_wp) | Optimiza WordPress, expone JS globals | Si |
| [Security](#security) | Hardening de WordPress | Si |
| [WP_Functionality](#wp_functionality) | Utilidades y extensiones WP | Si |
| [Custom_Post_Type](#custom_post_type) | Registrar CPTs con extensiones Terra | No |
| [Custom_Taxonomy](#custom_taxonomy) | Registrar taxonomias | No |
| [Manage_Columns](#manage_columns) | Columnas custom en admin | No |
| [Admin_Controller](#admin_controller) | Controlar interfaz admin | No |
| [AJAX_Request](#ajax_request) | Handlers AJAX con seguridad | No |
| [Custom_API_Endpoint](#custom_api_endpoint) | Endpoints REST API | No |
| [Custom_Blocks](#custom_blocks--default_blocks) | Bloques ACF custom | No |
| [Default_Blocks](#custom_blocks--default_blocks) | Bloques ACF del framework | No |
| [Images](#images) | Imagenes responsive con lazy loading | No |
| [Grammar](#grammar) | Validacion gramatical via Spling API | No |
| [Mail_To](#mail_to) | Envio de emails HTML | No |
| [Call_Cronjob](#call_cronjob) | Tareas cron programadas | No |
| [System_Warning](#system_warning) | Dashboard de monitoreo | No |
| [Terra_Lighthouse](#terra_lighthouse) | Performance monitoring | No |
| [Terra_URL_Health_Check](#terra_url_health_check) | Health check de URLs | No |
| [Google_Search_Console](#google_search_console) | Integracion GSC | No |
| [Custom_Search_Modules](#custom_search_modules) | Admin de busqueda | No |
| [Redirect_Stage_Urls](#redirect_stage_urls) | Redirects en staging | No |
| [WP_Vulnerability_Checker](#wp_vulnerability_checker) | Seguridad avanzada | No |

---

## Core

### Default_Setup

Clase principal que inicializa todos los componentes del framework. Punto de entrada del tema.

```php
new Default_Setup([
    'image_sizes' => [
        ['name' => 'tablets', 'w' => 810, 'h' => 9999, 'crop' => false],
        ['name' => 'mobile',  'w' => 580, 'h' => 9999, 'crop' => false],
    ],
    'image_type'             => ['generate_image_tag', 'wp_render_image'],
    'enable_search_modules'  => true,
    'enable_vulnerability'   => true,
]);
```

Al llamar `init()`, instancia automaticamente: Security, Clean_Wp, Images, WP_Functionality y las features opcionales.

### Default_Files

Carga los archivos de deploy en orden: local-variable, hash, enqueues.

```php
new Default_Files([
    'local_variable' => 'functions/project/deploy/local-variable.php',
    'hash'           => 'functions/project/deploy/hash.php',
    'enqueues'       => 'functions/project/deploy/enqueues.php',
]);
```

### Clean_Wp

Optimiza WordPress eliminando scripts y estilos innecesarios. Se instancia automaticamente desde Default_Setup.

Funcionalidades:
- Elimina jQuery Migrate, wp-embed, block library CSS
- Agrega async/defer a scripts
- Expone variables globales JS (`window.base_wp_api`)
- Configura opciones de TinyMCE
- Crea panel de ACF General Options

Variables JS expuestas:
```javascript
window.base_wp_api.ajax_url
window.base_wp_api.root_url
window.base_wp_api.theme_url
window.base_wp_api.current_page_ID
window.base_wp_api.nonces.loadmore_posts
```

### Security

Hardening basico de WordPress. Se instancia automaticamente desde Default_Setup.

Funcionalidades:
- Elimina roles innecesarios (wpseo_manager, wpseo_editor, subscriber, author, contributor)
- Limpia wp_head (version WP, RSD, WLW, emojis, feed links)
- Mensajes de login genericos
- Elimina generator meta tag

### WP_Functionality

Extiende WordPress con utilidades. Se instancia automaticamente desde Default_Setup.

Funcionalidades:
- Habilita uploads SVG, WebP y JSON
- Helper `get_target_link($target, $text)` para links externos
- Etiqueta "Home" para page-home.php en admin
- Excluye posts protegidos por password de queries por defecto
- Helper `get_page_id_by_title($title)`

---

## Content Types

### Custom_Post_Type

Registra Custom Post Types con extensiones Terra.

```php
new Custom_Post_Type((object) [
    'post_type'     => 'team',
    'singular_name' => 'Team Member',
    'plural_name'   => 'Team',
    'args' => [
        'menu_icon'   => 'dashicons-groups',
        'has_archive' => true,
        'supports'    => ['title', 'thumbnail', 'editor'],
        'rewrite'     => ['slug' => 'team', 'with_front' => false],

        // Extensiones Terra
        'terra_hide_permalink'      => true,
        'terra_hide_preview_button' => true,
        'terra_hide_seo_columns'    => true,
        'terra_redirect'            => '/team-page',
        'terra_manage_columns' => [
            'job_title' => [
                'label'     => 'Job Title',
                'reference' => 'acf',
            ],
        ],
    ],
]);
```

**Extensiones Terra disponibles:**

| Extension | Tipo | Descripcion |
|-----------|------|-------------|
| `terra_hide_permalink` | bool | Ocultar permalink en editor |
| `terra_hide_preview_button` | bool | Ocultar boton preview |
| `terra_hide_seo_columns` | bool | Ocultar columnas SEO en listado |
| `terra_redirect` | string | Redirect al ver el single del CPT |
| `terra_manage_columns` | array | Columnas custom en listado admin |

### Custom_Taxonomy

Registra taxonomias personalizadas.

```php
new Custom_Taxonomy((object) [
    'taxonomy'      => 'department',
    'object_type'   => ['team'],
    'singular_name' => 'Department',
    'plural_name'   => 'Departments',
    'args' => [
        'hierarchical' => true,
        'rewrite'      => ['slug' => 'department'],
    ],
]);
```

### Manage_Columns

Agrega columnas custom al listado de posts en admin. Soporta campos ACF y featured images.

```php
new Manage_Columns((object) [
    'post_type' => 'team',
    'columns' => [
        'job_title' => [
            'label'     => 'Job Title',
            'reference' => 'acf',       // Valor de campo ACF
        ],
        'featured_image' => [
            'label'     => 'Image',
            'reference' => 'wp',        // Featured image thumbnail
        ],
    ],
]);
```

---

## Admin

### Admin_Controller

Controla la interfaz de administracion por template, post type, post ID o condicion custom.

```php
// Por template
new Admin_Controller((object) [
    'identifier'    => 'page-home.php',
    'match_type'    => 'template',
    'hide_elements' => ['excerpt', 'editor', 'comments', 'author'],
]);

// Por post type
new Admin_Controller((object) [
    'identifier'    => 'team',
    'match_type'    => 'post_type',
    'hide_elements' => ['excerpt', 'thumbnail'],
    'redirect'      => '/wp-admin/edit.php?post_type=team',
]);

// Por condicion custom
new Admin_Controller((object) [
    'identifier' => 'custom_check',
    'match_type' => 'condition',
    'condition'  => function() { return is_user_logged_in(); },
    'hide_elements' => ['custom_fields'],
]);
```

**Elementos ocultables:** `excerpt`, `thumbnail`, `editor`, `custom_fields`, `comments`, `slug`, `author`, `revisions`, `page_attributes`, `categories`, `tags`

**Match types:** `template`, `post_type`, `post_id`, `condition`

---

## AJAX & API

### AJAX_Request

Handlers AJAX con seguridad integrada: nonce, sanitizacion, capability checks.

```php
new AJAX_Request((object) [
    'action'       => 'submit_form',
    'callback'     => 'handle_form',
    'public'       => true,           // Permitir no logueados
    'verify_nonce' => true,           // Verificar nonce
    'method'       => 'POST',         // Solo POST
    'capability'   => 'edit_posts',   // Requerir capability
    'required'     => ['email'],      // Campos requeridos
    'sanitize'     => [               // Sanitizacion automatica
        'email'   => 'email',
        'message' => 'textarea',
        'page'    => 'int',
        'ids'     => 'array_int',
    ],
]);

function handle_form($data) {
    // $data ya viene sanitizado
    if (!is_email($data['email'])) {
        AJAX_Request::send_error('invalid_email', 'Email invalido', 400);
    }

    AJAX_Request::send_success(['id' => $new_id], 'Guardado');
}
```

**Tipos de sanitizacion:**

| Tipo | Funcion |
|------|---------|
| `int`, `integer` | `intval()` |
| `float`, `number` | `floatval()` |
| `bool`, `boolean` | `filter_var(..., FILTER_VALIDATE_BOOLEAN)` |
| `email` | `sanitize_email()` |
| `url` | `esc_url_raw()` |
| `text`, `string` | `sanitize_text_field()` |
| `textarea` | `sanitize_textarea_field()` |
| `html` | `wp_kses_post()` |
| `key`, `slug` | `sanitize_key()` |
| `filename`, `file` | `sanitize_file_name()` |
| `array_int` | Array de integers |
| `array_text` | Array de texto sanitizado |
| `raw`, `none` | Sin sanitizacion |
| `callable` | Funcion custom |

**Metodos de respuesta:**

```php
AJAX_Request::send_success(['data' => $value], 'Mensaje');
AJAX_Request::send_error('error_code', 'Mensaje de error', 400);
AJAX_Request::send_paginated($html, $has_more, $page, $total);
```

### Custom_API_Endpoint

Endpoints REST API personalizados.

```php
new Custom_API_Endpoint((object) [
    'namespace' => 'theme/v1',
    'route'     => '/posts',
    'methods'   => 'GET',
    'callback'  => 'get_posts_handler',
    'args' => [
        'page' => [
            'default'           => 1,
            'sanitize_callback' => 'absint',
        ],
        'q' => [
            'sanitize_callback' => 'sanitize_text_field',
        ],
    ],
]);
```

---

## Bloques

### Custom_Blocks / Default_Blocks

Bloques ACF Gutenberg. Custom_Blocks para bloques del proyecto, Default_Blocks para bloques del framework.

```php
// En config/custom-blocks_config.php
return [
    [
        'block_name'    => 'testimonial',
        'singular_name' => 'Testimonial',
        'icon'          => 'format-quote',
        'keywords'      => 'quote, testimonial',
        'fields' => [
            [
                'name'  => 'quote',
                'label' => 'Quote',
                'type'  => 'textarea',
            ],
            [
                'name'  => 'author',
                'label' => 'Author',
                'type'  => 'text',
            ],
            [
                'name'          => 'image',
                'label'         => 'Image',
                'type'          => 'image',
                'return_format' => 'array',
            ],
        ],
    ],
];
```

Template en `blocks/testimonial.php`:
```php
<div class="c--testimonial">
    <blockquote><?php echo esc_html($data['quote']); ?></blockquote>
    <cite><?php echo esc_html($data['author']); ?></cite>
</div>
```

---

## Media

### Images

Imagenes responsive con lazy loading y aspect ratio automatico.

```php
// Configuracion de sizes
new Images(
    [
        ['name' => 'tablets', 'w' => 810, 'h' => 9999, 'crop' => false],
        ['name' => 'mobile',  'w' => 580, 'h' => 9999, 'crop' => false],
    ],
    ['generate_image_tag', 'wp_render_image']
);

// Usar en templates
render_wp_image([
    'image'           => $image,   // ACF array, ID, o URL
    'sizes'           => 'large',  // large, medium, small, o media query
    'class'           => 'my-image',
    'isLazy'          => true,
    'showAspectRatio' => true,
    'decoding'        => 'async',
    'fetchPriority'   => 'auto',
]);
```

---

## Comunicacion

### Mail_To

Utilidad simple para envio de emails HTML via `wp_mail`.

```php
new Mail_To((object) [
    'email'   => 'admin@example.com',
    'subject' => 'Notificacion',
    'message' => '<p>Contenido HTML del email</p>',
]);
```

### Grammar

Validacion gramatical y ortografica en posts publicados y taxonomias via Spling API.

```php
new Grammar([
    'post_types'    => ['post', 'page'],
    'taxonomies'    => ['category'],
    'notify_emails' => ['admin@example.com'],
    'language'      => 'en',
]);
```

**Requiere:** constante `SPLING_API_KEY` definida.

Funcionalidades:
- Hook automatico en publicacion de posts
- Genera reporte via Spling API
- Notifica por email con link al reporte
- Metodo manual: `Grammar::check_url($url, $emails)`

---

## Tareas Programadas

### Call_Cronjob

Programa tareas cron en WordPress.

```php
new Call_Cronjob((object) [
    'cronName'     => 'daily_cleanup',
    'interval'     => 86400,  // segundos
    'functionName' => 'my_cleanup_function',
]);

function my_cleanup_function() {
    // Tarea programada...
}
```

---

## Monitoreo

### System_Warning

Dashboard de administracion con herramientas de monitoreo. Solo activo en produccion.

```php
new System_Warning([
    'recipients'                    => ['admin@example.com'],
    'lighthouse_enabled'            => true,
    'google_search_console_enabled' => true,
    'url_health_checked_enabled'    => true,
    'mail_to_enabled'               => true,
    'mail_to_config' => [
        'email'   => 'alerts@example.com',
        'subject' => 'Site Alert',
        'message' => 'Check the site.',
    ],
]);
```

### Terra_Lighthouse

Monitoreo de performance via Google Lighthouse con reportes periodicos.

```php
new Terra_Lighthouse((object) [
    'interval' => 86400,   // segundos entre checks
    'email'    => 'admin@example.com',
    'url'      => 'https://example.com',
]);
```

- Cron job automatico para checks periodicos
- Reportes en submenu admin bajo System Warning
- Requiere archivos en `includes/lighthouse/`

### Terra_URL_Health_Check

Monitoreo de salud de URLs del sitio (404s, 500s, timeouts).

```php
new Terra_URL_Health_Check((object) [
    'interval' => 86400,   // segundos entre checks
    'email'    => 'admin@example.com',
    'url'      => 'https://example.com',
]);
```

- Cron job automatico
- Reportes por email y en admin
- Requiere archivos en `includes/url_health_check/`

### Google_Search_Console

Integracion de datos de Google Search Console en el admin.

```php
new Google_Search_Console([]);
```

Agrega submenu bajo System Warning con datos de GSC.

### Custom_Search_Modules

Panel de administracion para gestion de modulos de busqueda.

```php
new Custom_Search_Modules((object) []);
```

---

## Seguridad Avanzada

### WP_Vulnerability_Checker

Hardening avanzado configurable por feature flags.

```php
new WP_Vulnerability_Checker([
    'restrict_users_endpoint'       => true,  // Bloquear /wp-json/wp/v2/users
    'enforce_strong_passwords'      => true,  // Minimo 16 caracteres
    'cors_protect_rest_api'         => true,  // Solo mismo origen
    'remove_wp_version_headers'     => true,  // Ocultar version WP
    'generic_rest_errors'           => true,  // Errores genericos REST
    'redirect_author_archives'      => true,  // Redirigir /author/
    'shorten_password_reset_expiry' => true,  // Reset expira en 1h
]);
```

---

## Staging

### Redirect_Stage_Urls

Redirige URLs no definidas en staging a home (301). Solo activo en WP Engine staging.

```php
new Redirect_Stage_Urls([
    'pages'      => [12, 45, 67],       // IDs de paginas permitidas
    'single'     => ['post', 'team'],   // Post types permitidos
    'taxonomies' => ['category'],       // Taxonomias permitidas
]);
```

---

## Utilities

Funciones helper globales disponibles en `framework/utilities/`:

```php
// Verificar si es produccion
if (is_production_url()) { }

// Generar nonce para AJAX
$nonce = terra_ajax_nonce('my_action');

// LoadMore button
terra_loadmore_button([
    'container' => 'posts-grid',
    'template'  => 'card-a',
    'post_type' => 'post',
    'per_page'  => 6,
    'label'     => 'Load More',
]);

// Debug en consola del browser
debug_to_console($data);

// Detectar robots/bots
$is_bot = detect_robot_callback();

// Obtener emails de opciones ACF
$emails = get_recipient_emails();

// URL a embed
$embed = url_to_embed($url);

// API helper
$response = terra_api_request($url, $args);
```

---

## Configuracion del Proyecto

El framework se configura desde `functions/project/config/`:

| Archivo | Proposito |
|---------|-----------|
| `index.php` | Combina todas las configs |
| `default_config.php` | Image sizes, opciones generales |
| `post-types_config.php` | Custom Post Types |
| `taxonomy_config.php` | Taxonomias |
| `ajax_config.php` | Handlers AJAX |
| `endpoint_config.php` | REST API endpoints |
| `admin-controller_config.php` | Control de interfaz admin |
| `custom-blocks_config.php` | Bloques ACF custom |
| `default-blocks_config.php` | Bloques ACF del framework |

---

## Licencia

Terra HQ
