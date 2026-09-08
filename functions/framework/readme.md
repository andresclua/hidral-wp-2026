# Terra Framework TEST

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
| [Custom_Search_Gutenberg](#custom_search_gutenberg) | Que bloque Gutenberg usa cada post | No |
| [Custom_Search_Forms](#custom_search_forms) | Que form de Contact Form 7 usa cada pagina | No |
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
    'image_type'              => ['generate_image_tag', 'wp_render_image'],
    'enable_search_modules'   => true,
    'enable_search_gutenberg' => true,
    'enable_search_forms'     => true,
    'enable_vulnerability'    => true,
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

Agrega la pagina **Terra → Search Modules**: matriz de que modulo / hero ACF usa cada pagina publicada.

### Custom_Search_Gutenberg

Mismo reporte que Search Modules pero recorriendo `parse_blocks()`: que bloque custom usa cada post.

```php
new Custom_Search_Gutenberg((object) []);
```

Agrega la pagina **Terra → Search Gutenberg**. Ignora los bloques `core/*` y detecta bloques anidados de forma recursiva.

### Custom_Search_Forms

Reporte de uso de **Contact Form 7**: que form usa cada pagina. Agrega la pagina **Terra → Search Forms**.

```php
new Custom_Search_Forms((object) []);
```

Requiere Contact Form 7: si el plugin no esta activo la clase no registra nada (ni submenu ni estilos), asi que se puede dejar prendida en todos los proyectos sin efecto colateral.

Detecta los forms en todos los lugares donde un proyecto puede referenciarlos:

| Origen | Que busca |
|--------|-----------|
| `post_content` | Shortcode `[contact-form-7 …]`, shortcode legacy `[contact-form 123 "Title"]` y el bloque `contact-form-7/contact-form-selector` |
| `postmeta` (ACF) | Campos que guardan el shortcode como texto (ej. `form_shortcode` dentro de un modulo flexible) y campos post object / relationship que apuntan a un `wpcf7_contact_form` |
| `options` (ACF Options Page) | Lo mismo, y ademas se resuelve a que paginas llega (ver abajo) |
| Templates del tema | Shortcodes escritos a mano en los `.php` del tema (y del padre, si hay child theme), con archivo y linea |
| Bloques reutilizables (`wp_block`) | El shortcode dentro de un patron sincronizado, y **las paginas que lo embeben** (ver abajo) |

La resolucion de la referencia sigue el mismo orden que CF7 (hash → post ID → unit ID legacy → titulo), asi que funciona tanto con los shortcodes nuevos (`id="a1b2c3d"`) como con los viejos (`id="123"`).

La pagina muestra cinco bloques (los que no tienen datos no se renderizan):

1. **Forms** — una fila por form con su shortcode, **las paginas donde se usa** (con link para editar cada una), en cuantos templates aparece, y badge `Not used` para los que no se usan en ningun lado.

   La columna `Pages` lista los nombres directamente si son 10 o menos; arriba de eso se colapsa en un `<details>` con el total, hasta un maximo de 25 links + "`+ N more`". Cada link lleva en el `title` de donde salio el form (lo mismo que el tooltip de la matriz), y los que no son `page` muestran su post type al lado.

   La columna **Site-wide** solo aparece si algun form global **no** se pudo atribuir a paginas concretas (ver el punto 2). Si todo se resolvio, la columna no se renderiza.
2. **Pages** — matriz paginas (filas) x forms (columnas) con ✔️. El tooltip de cada celda dice donde se encontro (`Content`, `Gutenberg block`, `Field: modules_3_form_shortcode`, `Global form via <layout> (modules_3)`, `Reusable block: <titulo>`, …).

   **Forms globales resueltos a paginas.** Un form seteado en una Options Page lo renderiza algun modulo que lee ese campo, y ese vinculo esta en el codigo del tema. El scan junta las dos mitades y las cruza, sin nada hardcodeado del proyecto:

   - que archivo llama a `get_field('<campo>', 'option')` → por ejemplo `flexible/module/contact-cta.php`
   - que layout renderiza ese archivo, leyendo el `switch` del dispatcher de flexibles → `contact_cta`

   Hay una segunda via para los temas donde ese vinculo esta en un mapa PHP y no en un `get_field()` literal (un resolver generico que recibe el nombre del campo por variable no se puede rastrear lexicamente): los campos de options se namespacean con el layout al que pertenecen justamente para no colisionar (`contact_cta_form_shortcode`), asi que se prueba el layout como prefijo del nombre del campo. El layout tiene que existir en el dispatcher y tener instancias no-custom, asi que una coincidencia no atribuye nada.

   Con el layout, las paginas que lo usan son una meta query. Ojo que ACF guarda el Flexible Content como **una lista serializada de layouts en el campo padre** (`modules` = `a:5:{…i:3;s:11:"contact_cta";…}`): no existen rows `*_acf_fc_layout` en la base, eso solo aparece en el array que devuelve `get_field()`. El indice dentro de esa lista es lo que da el prefijo de los subcampos (indice 3 de `modules` → `modules_3_*`).

   Cada instancia del modulo se evalua aparte: si tiene su propio shortcode, es un form custom y ya lo reporto el scan de campos; si tiene un campo con valor `custom` pero vacio, no renderiza nada; el resto cae en el form global y se registra como uso de esa pagina.

   Se lee el `switch` del dispatcher porque los nombres de archivo **no** tienen por que coincidir con los de layout (un `mi-modulo-CTA.php` puede corresponder al layout `mi_modulo_cta`). Cuando no se puede atribuir — ningun modulo lee el campo, o lo lee `header.php` — el form queda en la columna Site-wide.

   **Bloques reutilizables resueltos a paginas.** Un patron sincronizado guarda su contenido una sola vez, en un post `wp_block`, y las paginas que lo usan guardan nada mas que `<!-- wp:block {"ref":123} /-->`. O sea que el shortcode existe en un unico lugar y en las paginas no hay rastro. Como `wp_block` **no es un post type publico**, sin tratarlo aparte un form vivo en 20 paginas se reportaria como `Not used` — el error en la direccion peligrosa, porque alguien lo borra confiando en el badge.

   Por eso `wp_block` se suma a los post types escaneados y despues se siguen las referencias hacia afuera: la fila del bloque se mantiene (es donde vive el shortcode, y un bloque que no se usa en ningun lado tambien es informacion) y ademas se registra cada pagina que lo embebe, con fuente `Reusable block: <titulo>`. El match del `ref` se hace en PHP con un borde de palabra, porque un `LIKE "ref":12` tambien pegaria en `"ref":123`.
3. **Templates** — shortcodes hardcodeados en el tema, con archivo, linea y **a que contenido llega**. Cuentan como uso real: sin esto el form apareceria como `Not used` estando publicado, que es peor que no cubrir el caso.

   La columna `Applies to` resuelve el archivo por la jerarquia de templates de WordPress, porque saber el archivo es media respuesta: un shortcode en `single-{post_type}.php` esta en **todos** los posts publicados de ese post type.

   Segun el scope muestra una cosa u otra. Los **post types** llevan un link a su listado del admin (`edit.php?post_type={post_type}`) con el total al lado — enumerar cientos de posts no le sirve a nadie. Las **paginas** si se enumeran con link a editar cada una, porque son pocas y saber *cual* importa; arriba de 10 se colapsan en un `<details>` (hasta 25 links + "`+ N more`").

   | Archivo | Applies to |
   |---------|------------|
   | Header `Template Name:` | `Page template: <nombre>` + lista de las paginas con ese template asignado (`_wp_page_template`) |
   | `single-{post_type}.php` | Link al listado de ese post type, con el total publicado |
   | `single.php` | Un link por cada post type que **no** tiene su propio `single-{type}.php` |
   | `page-{slug}.php` / `page-{id}.php` | Esa pagina (solo si el archivo no tiene header `Template Name:`, que gana siempre) |
   | `page.php` | Lista de las paginas con template default, excluyendo las que matchea un `page-{slug}.php` |
   | `front-page.php` / `home.php` | La home / la pagina de posts |
   | `archive-{type}.php` | Link al listado del post type, mas link al archive del front si el post type tiene `has_archive` |
   | `taxonomy-*`, `category`, `tag`, `search`, `404`, `index` | Descripcion del scope: no hay un set de posts que enumerar |
   | `header.php` / `footer.php` | Site-wide |
   | Cualquier subdirectorio (partials, modulos flexibles) | `Partial — included from other templates, check manually`: resolverlo implicaria rastrear cada include |

   Si el post type o la pagina no existen, lo dice (`Post type "x" is not registered — template never used`) en lugar de mostrar una lista vacia.
4. **Not verifiable** — llamadas `do_shortcode($var)` donde el shortcode se arma en runtime y no se puede rastrear de donde viene. Se listan con archivo, linea y el codigo, en lugar de adivinar que form renderizan.

   Antes de listar una, se intenta rastrear el argumento: si es una llamada ACF directa (`do_shortcode(get_field('form'))`, `do_shortcode($module['form_shortcode'])`) o una variable que en ese mismo archivo se asigna desde ACF (`get_field`, `get_sub_field`, `$module[`, `$hero[`, `$block[`), **no se lista** — el uso real ya lo reporto el scan de campos, y repetirlo seria ruido. Ruido en esta lista es justamente lo que taparia una llamada verdaderamente irrastreable.

   Por eso el patron estandar de un modulo no aparece: un `do_shortcode($form_shortcode)` cuya variable se asigno unas lineas arriba desde `$module['form_shortcode']` o desde `get_field('<campo>', 'option')` se rastrea sin problema. Lo que si aparece es, por ejemplo, una variable que viene de `get_post_meta()` crudo o de una concatenacion.

   El rastreo es una heuristica lexica dentro del archivo, no analisis de flujo real: no cruza archivos ni sigue variables que pasan por funciones.
5. **Broken references** — shortcodes que apuntan a forms borrados, es decir los lugares donde el front hoy imprime "Contact form not found".

Se escanea solo contenido **publicado**, de los post types publicos mas `wp_block`. El reporte se calcula al abrir la pagina y no guarda nada en base de datos.

El scan de templates saltea `node_modules`, `vendor`, `dist`, `build`, `cache`, `.git` y su propio directorio — los docblocks del scanner estan llenos de shortcodes de ejemplo. Para no ensuciar **Broken references** con ejemplos de comentarios de otros archivos, una referencia rota en un template solo se reporta si el `id` parece real (`^[A-Za-z0-9_-]+$`), asi que placeholders como `id="…"` o `id="%s"` se ignoran.

Se puede apagar por proyecto desde **Terra → System → Modules** (`search_forms`) o con `'enable_search_forms' => false` en `Default_Setup`.

#### Portabilidad

El archivo se pullea igual en todos los proyectos, asi que no asume nada de un tema en particular. Lo que podria ser especifico se deriva en runtime o se valida contra los datos:

| Podria variar | Como se resuelve |
|---------------|------------------|
| Prefijo de las Options Pages de ACF | Se lee de `acf_get_options_pages()`. Una options page con `post_id` custom guarda `{post_id}_{campo}` en vez de `options_{campo}` y se detecta igual |
| Mapeo layout → archivo | Se lee del `switch` del propio tema, exigiendo que el archivo mencione `acf_fc_layout` (asi un `switch` cualquiera que incluya un partial no se confunde con un dispatcher). Soporta `locate_template()` y `get_template_part()` |
| Temas con dispatch dinamico (sin `switch`) | Cae en convenciones de nombre (`mi_layout` ↔ `mi-layout.php`, ambos separadores). Un guess errado no rompe nada: no matchea ninguna fila y no se atribuye. **Los datos validan la convencion** |
| Post types, page templates, archives | Jerarquia de templates de WordPress |
| Nombre del campo flexible (`modules`, `heros`, …) | Nunca se asume: se recorre cualquier meta key |

Para lo que no se puede derivar hay filtros — **ningun proyecto necesita tocar este archivo**:

| Filtro | Para que |
|--------|----------|
| `terra_search_forms_template_roots` | Agregar directorios al scan (ej. un plugin propio que renderiza forms) |
| `terra_search_forms_post_types` | Post types escaneados (por defecto los publicos + `wp_block`) |
| `terra_search_forms_skip_dirs` | Nombres de directorio a saltear |
| `terra_search_forms_option_prefixes` | Prefijos de options si la deteccion via ACF no alcanza |
| `terra_search_forms_layouts_for_file` | Que layouts renderiza un archivo, si el mapeo no es expresable |
| `terra_search_forms_acf_tokens` | Tokens que marcan un valor como proveniente de ACF (ej. otra variable en vez de `$module[`) |
| `terra_search_forms_custom_markers` | Valores del switch que significan "este modulo no usa el form global" |
| `terra_search_forms_list_limit` | Cuantas paginas se listan antes del "`+ N more`" |

Sobre el costo: la atribucion de forms globales hace un `meta_value LIKE` que MySQL no puede indexar, o sea un scan de `postmeta`. Por eso **todos** los layouts candidatos se resuelven en una sola query — sumar guesses de nombre sale gratis — y el paso entero se saltea si no hay ningun form en una Options Page. En un sitio de unos 3.000 posts publicados el reporte completo tarda alrededor de medio segundo y usa unas 25 queries; el orden de magnitud escala con el tamaño de `postmeta`, no con la cantidad de forms.

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
