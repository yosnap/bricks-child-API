<?php 
/**
 * Register/enqueue custom scripts and styles
 */
add_action( 'wp_enqueue_scripts', function() {
	// Enqueue your files on the canvas & frontend, not the builder panel. Otherwise custom CSS might affect builder)
	if ( ! bricks_is_builder_main() ) {
		wp_enqueue_style( 'bricks-child', get_stylesheet_uri(), ['bricks-frontend'], filemtime( get_stylesheet_directory() . '/style.css' ) );
	}
} );

// Evitar acceso directo al archivo
if (!defined('ABSPATH')) exit;

// Incluir archivos necesarios
require_once get_stylesheet_directory() . '/includes/motoraldia-api.php';
require_once get_stylesheet_directory() . '/includes/motoraldia-query.php';
require_once get_stylesheet_directory() . '/includes/motoraldia-tags.php';
require_once get_stylesheet_directory() . '/includes/motoraldia-rewrite.php';
require_once get_stylesheet_directory() . '/includes/motoraldia-cpt-simulator.php';

// Función para mostrar la página de administración de vehículos
function motoraldia_display_vehicles_page() {
    // Obtener los datos de la API
    $data = get_vehicles_data();
    if (empty($data['vehicles'])) {
        echo '<div class="notice notice-warning"><p>No se encontraron vehículos.</p></div>';
        return;
    }

    // Búsqueda
    $search = isset($_GET['vehicle_search']) ? sanitize_text_field($_GET['vehicle_search']) : '';
    
    // Elementos por página
    $per_page_options = [10, 20, 50, 100];
    $per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 20;
    $custom_per_page = isset($_GET['custom_per_page']) ? intval($_GET['custom_per_page']) : '';
    
    // Si hay un valor personalizado, usarlo
    if ($custom_per_page > 0) {
        $per_page = $custom_per_page;
    } elseif (!in_array($per_page, $per_page_options)) {
        $per_page = 20;
    }

    // Filtrar vehículos si hay búsqueda
    $filtered_vehicles = $data['vehicles'];
    if (!empty($search)) {
        $filtered_vehicles = array_filter($data['vehicles'], function($vehicle) use ($search) {
            return stripos($vehicle['titol-anunci'], $search) !== false;
        });
    }

    // Paginación
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $total_items = count($filtered_vehicles);
    $total_pages = ceil($total_items / $per_page);
    
    // Obtener solo los vehículos de la página actual
    $offset = ($current_page - 1) * $per_page;
    $vehicles_page = array_slice($filtered_vehicles, $offset, $per_page);

    // Añadir estilos CSS inline
    echo '<style>
        .vehicle-thumb { 
            max-width: 80px;
            width: 80px;
            height: 60px;
            object-fit: cover;
            display: block;
        }
        .vehicle-image-col {
            width: 90px;
        }
        .widefat td { 
            vertical-align: middle; 
        }
        .widefat tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .widefat tr:hover {
            background-color: #f1f3f4;
        }
        .search-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 1em 0;
            padding: 1em;
            background: #fff;
            border: 1px solid #ccd0d4;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }
        .search-form {
            display: flex;
            gap: 10px;
            align-items: center;
            flex: 1;
        }
        .per-page-form {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .per-page-form input[type="number"] {
            width: 70px;
        }
        .search-form input[type="text"] {
            flex: 1;
            max-width: 300px;
        }
        .tablenav {
            margin: 1em 0;
        }
        .tablenav-pages {
            float: right;
        }
        .tablenav-pages a,
        .tablenav-pages span {
            display: inline-block;
            padding: 4px 8px;
            background: #fff;
            border: 1px solid #ccd0d4;
            text-decoration: none;
            margin: 0 2px;
        }
        .tablenav-pages .current {
            background: #0073aa;
            color: #fff;
            border-color: #0073aa;
        }
    </style>';

    echo '<div class="wrap">';
    echo '<h1>Vehículos de Motoraldia</h1>';

    // Formulario de búsqueda y opciones
    echo '<div class="search-box">';
    echo '<form method="get" class="search-form">';
    echo '<input type="hidden" name="page" value="motoraldia_vehicles">';
    if (isset($_GET['per_page'])) {
        echo '<input type="hidden" name="per_page" value="' . esc_attr($per_page) . '">';
    }
    echo '<input type="text" name="vehicle_search" value="' . esc_attr($search) . '" placeholder="Buscar por título...">';
    echo '<input type="submit" class="button" value="Buscar">';
    if (!empty($search)) {
        $clear_url = add_query_arg(['page' => 'motoraldia_vehicles', 'per_page' => $per_page], admin_url('admin.php'));
        echo ' <a href="' . esc_url($clear_url) . '" class="button">Limpiar</a>';
    }
    echo '</form>';

    // Selector de elementos por página
    echo '<form method="get" class="per-page-form">';
    echo '<input type="hidden" name="page" value="motoraldia_vehicles">';
    if (!empty($search)) {
        echo '<input type="hidden" name="vehicle_search" value="' . esc_attr($search) . '">';
    }
    echo '<label>Mostrar: ';
    echo '<select name="per_page" onchange="this.form.submit()" id="per_page_select">';
    foreach ($per_page_options as $option) {
        $selected = ($per_page == $option && empty($custom_per_page)) ? ' selected' : '';
        echo '<option value="' . $option . '"' . $selected . '>' . $option . ' elementos</option>';
    }
    if (!in_array($per_page, $per_page_options)) {
        echo '<option value="' . $per_page . '" selected>' . $per_page . ' elementos</option>';
    }
    echo '</select>';
    echo '</label>';
    echo '<span class="per-page-custom">';
    echo '<label>o especificar: ';
    echo '<input type="number" name="custom_per_page" value="' . ($custom_per_page ?: '') . '" 
             min="1" max="500" placeholder="Cantidad" 
             onchange="document.getElementById(\'per_page_select\').value=\'20\'">';
    echo '</label>';
    echo '<input type="submit" class="button" value="Aplicar">';
    echo '</span>';
    echo '</form>';
    echo '</div>';

    // Tabla de vehículos
    echo '<table class="widefat fixed" cellspacing="0">';
    echo '<thead><tr><th>ID</th><th>Imagen</th><th>Título</th><th>Tipo</th><th>Precio</th><th>Acciones</th></tr></thead>';
    echo '<tbody>';

    foreach ($vehicles_page as $vehicle) {
        $details_url = add_query_arg(['page' => 'motoraldia_vehicle_details', 'vehicle_id' => $vehicle['id']], admin_url('admin.php'));
        echo '<tr>';
        echo '<td>' . esc_html($vehicle['id']) . '</td>';
        echo '<td>';
        if (!empty($vehicle['imatge-destacada-url'])) {
            echo '<img src="' . esc_url($vehicle['imatge-destacada-url']) . '" 
                       alt="' . esc_attr($vehicle['titol-anunci']) . '" 
                       class="vehicle-thumb" />';
        } else {
            echo 'Sin imagen';
        }
        echo '</td>';
        echo '<td>' . esc_html($vehicle['titol-anunci']) . '</td>';
        echo '<td>' . esc_html($vehicle['tipus-de-vehicle'] ?? 'No especificado') . '</td>';
        echo '<td>' . esc_html(number_format($vehicle['preu'], 0, ',', '.') . ' €') . '</td>';
        echo '<td><a href="' . esc_url($details_url) . '" class="button">Ver Detalles</a></td>';
        echo '</tr>';
    }

    echo '</tbody></table>';

    // Paginación
    if ($total_pages > 1) {
        echo '<div class="tablenav">';
        echo '<div class="tablenav-pages">';
        
        // Primera página
        if ($current_page > 1) {
            echo '<a href="' . esc_url(add_query_arg(['paged' => 1, 'vehicle_search' => $search, 'per_page' => $per_page])) . '">&laquo;</a>';
        }
        
        // Página anterior
        if ($current_page > 1) {
            echo '<a href="' . esc_url(add_query_arg(['paged' => $current_page - 1, 'vehicle_search' => $search, 'per_page' => $per_page])) . '">&lsaquo;</a>';
        }
        
        // Números de página
        for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++) {
            if ($i == $current_page) {
                echo '<span class="current">' . $i . '</span>';
            } else {
                echo '<a href="' . esc_url(add_query_arg(['paged' => $i, 'vehicle_search' => $search, 'per_page' => $per_page])) . '">' . $i . '</a>';
            }
        }
        
        // Página siguiente
        if ($current_page < $total_pages) {
            echo '<a href="' . esc_url(add_query_arg(['paged' => $current_page + 1, 'vehicle_search' => $search, 'per_page' => $per_page])) . '">&rsaquo;</a>';
        }
        
        // Última página
        if ($current_page < $total_pages) {
            echo '<a href="' . esc_url(add_query_arg(['paged' => $total_pages, 'vehicle_search' => $search, 'per_page' => $per_page])) . '">&raquo;</a>';
        }

        echo ' <span class="displaying-num">' . $total_items . ' elementos</span>';
        echo '</div>';
        echo '</div>';
    }
}

// Mostrar los detalles del vehículo
function motoraldia_display_vehicle_details() {
    $vehicle_id = isset($_GET['vehicle_id']) ? $_GET['vehicle_id'] : null;
    if ($vehicle_id === null) {
        wp_die('ID de vehículo no especificado');
    }

    $data = get_vehicles_data();
    if (empty($data['vehicles'])) {
        wp_die('No se encontraron datos de vehículos');
    }

    // Encontrar el vehículo por ID
    $vehicle = null;
    foreach ($data['vehicles'] as $v) {
        if (($v['id'] ?? '') == $vehicle_id) {
            $vehicle = $v;
            break;
        }
    }

    if (!$vehicle) {
        wp_die('Vehículo no encontrado');
    }

    // Estilos para la página de detalles
    echo '<style>
        .vehicle-details-wrap {
            max-width: 1200px;
            margin: 20px;
            background: #fff;
            padding: 20px;
            border: 1px solid #ccd0d4;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }
        .vehicle-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ccd0d4;
            padding-bottom: 20px;
        }
        .vehicle-image {
            max-width: 100%;
            height: auto;
            margin-bottom: 20px;
        }
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .gallery-item {
            position: relative;
            padding-top: 75%; /* Aspecto 4:3 */
            overflow: hidden;
            background: #f8f9fa;
            border: 1px solid #e2e4e7;
            border-radius: 4px;
        }
        .gallery-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .vehicle-meta {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .meta-box {
            background: #f8f9fa;
            padding: 15px;
            border: 1px solid #e2e4e7;
            border-radius: 4px;
        }
        .meta-box h3 {
            margin-top: 0;
            border-bottom: 1px solid #e2e4e7;
            padding-bottom: 10px;
        }
        .meta-item {
            margin-bottom: 10px;
        }
        .meta-label {
            font-weight: bold;
            color: #1d2327;
        }
    </style>';

    echo '<div class="wrap vehicle-details-wrap">';
    
    // Cabecera con botón de regreso
    echo '<div class="vehicle-header">';
    echo '<h1>' . esc_html($vehicle['titol-anunci']) . '</h1>';
    echo '<a href="' . admin_url('admin.php?page=motoraldia_vehicles') . '" class="button">← Volver a la lista</a>';
    echo '</div>';

    // Imagen destacada
    if (!empty($vehicle['imatge-destacada-url'])) {
        echo '<img src="' . esc_url($vehicle['imatge-destacada-url']) . '" 
                   alt="' . esc_attr($vehicle['titol-anunci']) . '" 
                   class="vehicle-image" />';
    }

    // Galería de imágenes
    if (!empty($vehicle['galeria-vehicle-urls'])) {
        echo '<div class="vehicle-gallery">';
        echo '<h3>Galería de imágenes</h3>';
        echo '<div class="gallery-grid">';
        foreach ($vehicle['galeria-vehicle-urls'] as $image_url) {
            echo '<div class="gallery-item">';
            echo '<img src="' . esc_url($image_url) . '" 
                       alt="Imagen de galería" 
                       class="gallery-image" />';
            echo '</div>';
        }
        echo '</div>';
        echo '</div>';
    }

    // Información principal
    echo '<div class="meta-box">';
    echo '<h3>Información Principal</h3>';
    echo '<div class="meta-item"><span class="meta-label">ID:</span> ' . esc_html($vehicle['id'] ?? '') . '</div>';
    echo '<div class="meta-item"><span class="meta-label">Tipo:</span> ' . esc_html($vehicle['tipus-de-vehicle'] ?? 'No especificado') . '</div>';
    echo '<div class="meta-item"><span class="meta-label">Precio:</span> ' . esc_html(number_format($vehicle['preu'], 0, ',', '.') . ' €') . '</div>';
    echo '</div>';

    // Detalles técnicos
    echo '<div class="vehicle-meta">';
    
    // Agrupar campos relacionados
    $groups = [
        'Características Técnicas' => [
            'marca' => 'Marca',
            'model' => 'Modelo',
            'quilometratge' => 'Kilometraje',
            'potencia-cv' => 'Potencia',
            'tipus-combustible' => 'Combustible',
            'canvi' => 'Cambio'
        ],
        'Detalles Adicionales' => [
            'color' => 'Color',
            'any' => 'Año',
            'places' => 'Plazas',
            'portes' => 'Puertas'
        ]
    ];

    foreach ($groups as $group_name => $fields) {
        echo '<div class="meta-box">';
        echo '<h3>' . esc_html($group_name) . '</h3>';
        foreach ($fields as $key => $label) {
            if (isset($vehicle[$key])) {
                $value = $vehicle[$key];
                // Formatear valores específicos
                switch ($key) {
                    case 'quilometratge':
                        $value = number_format($value, 0, ',', '.') . ' km';
                        break;
                    case 'potencia-cv':
                        $value .= ' CV';
                        break;
                }
                echo '<div class="meta-item">';
                echo '<span class="meta-label">' . esc_html($label) . ':</span> ';
                echo esc_html($value);
                echo '</div>';
            }
        }
        echo '</div>';
    }

    echo '</div>'; // Cierre de vehicle-meta

    echo '</div>'; // Cierre de wrap
}

// Agregar el menú de administración
add_action('admin_menu', function() {
    add_menu_page('Vehículos', 'Vehículos', 'manage_options', 'motoraldia_vehicles', 'motoraldia_display_vehicles_page');
    add_submenu_page('motoraldia_vehicles', 'Detalles del Vehículo', 'Detalles del Vehículo', 'manage_options', 'motoraldia_vehicle_details', 'motoraldia_display_vehicle_details');
});

/**
 * Register custom elements
 */
add_action( 'init', function() {
  $element_files = [
    __DIR__ . '/elements/title.php',
  ];

  foreach ( $element_files as $file ) {
    \Bricks\Elements::register_element( $file );
  }
}, 11 );

/**
 * Add text strings to builder
 */
add_filter( 'bricks/builder/i18n', function( $i18n ) {
  // For element category 'custom'
  $i18n['custom'] = esc_html__( 'Custom', 'bricks' );

  return $i18n;
} );

// Shortcode para mostrar el loop de vehículos
add_shortcode('motoraldia_vehicles', function($atts) {
    $atts = shortcode_atts([
        'items' => -1 // -1 significa todos los vehículos
    ], $atts);
    
    ob_start();
    motoraldia_display_vehicles_loop($atts['items']);
    return ob_get_clean();
});

// Debug de datos de vehículos en la consola
add_action('wp_footer', function() {
    if (is_admin()) return;
    
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof bricksData !== 'undefined' && bricksData.queryLoopData) {
            console.log('Bricks Query Loop Data:', bricksData.queryLoopData);
        }
    });
    </script>
    <?php
});

// Sobreescribir traducciones de Bricks
add_filter('bricks/frontend/i18n', function($i18n) {
    $i18n['loadMore'] = 'Cargar más vehículos';
    $i18n['loadMoreError'] = 'Error al cargar más vehículos.';
    $i18n['noMorePosts'] = 'No hay más vehículos disponibles.';
    return $i18n;
});
