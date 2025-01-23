<?php 
/**
 * Register/enqueue custom scripts and styles
 */
add_action('wp_enqueue_scripts', function() {
    if (!bricks_is_builder_main()) {
        wp_enqueue_style('bricks-child', get_stylesheet_uri(), ['bricks-frontend'], filemtime(get_stylesheet_directory() . '/style.css'));
    }
});

// Evitar acceso directo al archivo
if (!defined('ABSPATH')) exit;

// Incluir archivos necesarios
require_once get_stylesheet_directory() . '/includes/motoraldia-api.php';
require_once get_stylesheet_directory() . '/includes/motoraldia-query.php';
require_once get_stylesheet_directory() . '/includes/motoraldia-rewrite.php';
require_once get_stylesheet_directory() . '/includes/motoraldia-api-as-cpt.php';

// Cargar los tags después de que Bricks esté listo
add_action('init', function() {
    if (class_exists('Bricks\Database')) {
        require_once get_stylesheet_directory() . '/includes/motoraldia-tags.php';
    }
}, 20); // Cambiar a prioridad 20 para asegurar que Bricks está cargado

/**
 * Register custom elements
 */
add_action('init', function() {
    $element_files = [
        __DIR__ . '/elements/title.php',
    ];

    foreach ($element_files as $file) {
        \Bricks\Elements::register_element($file);
    }
}, 11);

/**
 * Add text strings to builder
 */
add_filter('bricks/builder/i18n', function($i18n) {
    $i18n['custom'] = esc_html__('Custom', 'bricks');
    return $i18n;
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

// Función para mostrar el loop de vehículos
function motoraldia_display_vehicles_loop($limit = -1) {
    $data = get_vehicles_data();
    if (empty($data['vehicles'])) {
        echo '<p>No se encontraron vehículos.</p>';
        return;
    }

    $vehicles = $data['vehicles'];
    
    // Limitar el número de vehículos si se especifica
    if ($limit > 0) {
        $vehicles = array_slice($vehicles, 0, $limit);
    }

    echo '<div class="vehicles-grid">';
    foreach ($vehicles as $vehicle) {
        // Generar URL de detalle con el slug
        $detail_url = get_vehicle_permalink($vehicle);
        
        echo '<div class="vehicle-card">';
        if (!empty($vehicle['imatge-destacada-url'])) {
            echo '<div class="vehicle-image">';
            echo '<a href="' . esc_url($detail_url) . '">';
            echo '<img src="' . esc_url($vehicle['imatge-destacada-url']) . '" 
                       alt="' . esc_attr($vehicle['titol-anunci']) . '" />';
            echo '</a>';
            echo '</div>';
        }
        
        echo '<div class="vehicle-info">';
        echo '<h3 class="vehicle-title">';
        echo '<a href="' . esc_url($detail_url) . '">' . esc_html($vehicle['titol-anunci']) . '</a>';
        echo '</h3>';
        echo '<p class="vehicle-price">' . number_format($vehicle['preu'], 0, ',', '.') . ' €</p>';
        
        echo '<div class="vehicle-meta">';
        if (!empty($vehicle['any'])) {
            echo '<span class="year">' . esc_html($vehicle['any']) . '</span>';
        }
        if (!empty($vehicle['quilometratge'])) {
            echo '<span class="mileage">' . number_format($vehicle['quilometratge'], 0, ',', '.') . ' km</span>';
        }
        echo '</div>';
        
        echo '<a href="' . esc_url($detail_url) . '" class="vehicle-button">Ver detalles</a>';
        echo '</div>'; // .vehicle-info
        echo '</div>'; // .vehicle-card
    }
    echo '</div>';

    // Agregar estilos inline
    ?>
    <style>
        .vehicles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        .vehicle-card {
            border: 1px solid #eee;
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .vehicle-card:hover {
            transform: translateY(-5px);
        }
        .vehicle-image img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .vehicle-info {
            padding: 15px;
        }
        .vehicle-title {
            margin: 0 0 10px;
            font-size: 1.2em;
        }
        .vehicle-title a {
            color: #333;
            text-decoration: none;
        }
        .vehicle-price {
            font-size: 1.3em;
            font-weight: bold;
            color: #2c3e50;
            margin: 10px 0;
        }
        .vehicle-meta {
            display: flex;
            gap: 15px;
            color: #666;
            margin: 10px 0;
        }
        .vehicle-button {
            display: inline-block;
            width: 100%;
            padding: 10px;
            background: #3498db;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
            transition: background 0.3s;
        }
        .vehicle-button:hover {
            background: #2980b9;
        }
    </style>
    <?php
}

// Registrar el shortcode
add_shortcode('motoraldia_vehicles', function($atts) {
    $atts = shortcode_atts([
        'items' => -1 // -1 significa todos los vehículos
    ], $atts);
    
    ob_start();
    motoraldia_display_vehicles_loop($atts['items']);
    return ob_get_clean();
});

// Asegurar que los datos del vehículo estén disponibles globalmente
add_action('template_redirect', function() {
    if (get_query_var('vehicle_slug')) {
        global $current_post_vehicle;
        $current_post_vehicle = get_current_vehicle_data();
    }
});
