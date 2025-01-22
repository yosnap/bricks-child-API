<?php
/*
Template Name: Detalle de Vehículo
*/

if (!defined('ABSPATH')) exit;

require_once get_stylesheet_directory() . '/includes/motoraldia-api.php';

// Debug
error_log('Motor al día Template - Iniciando template de detalle');
error_log('REQUEST_URI: ' . $_SERVER['REQUEST_URI']);
error_log('Query vars: ' . print_r($GLOBALS['wp_query']->query_vars, true));

// Obtener el ID del vehículo de la URL
$vehicle_id = get_query_var('vehicle_id');
error_log('Motor al día Template - ID del vehículo: ' . $vehicle_id);

if (!$vehicle_id) {
    error_log('Motor al día Template - No se encontró ID del vehículo');
    wp_redirect(home_url());
    exit;
}

// Obtener los datos del vehículo
$vehicle = get_vehicle_by_id($vehicle_id);
if (!$vehicle) {
    error_log('Motor al día Template - No se encontró el vehículo con ID: ' . $vehicle_id);
    wp_redirect(home_url());
    exit;
}

error_log('Motor al día Template - Vehículo encontrado: ' . print_r($vehicle, true));

// Hacer disponible los datos del vehículo globalmente para los dynamic tags
global $current_vehicle_data;
$current_vehicle_data = $vehicle;

// Establecer el título de la página
add_filter('pre_get_document_title', function() use ($vehicle) {
    return $vehicle['titol-anunci'] . ' - ' . get_bloginfo('name');
});

// Renderizar la plantilla de Bricks
get_header();
if (function_exists('bricks_template')) {
    bricks_template();
} else {
    // Fallback básico si Bricks no está activo
    echo '<div class="vehicle-detail-container">';
    echo '<h1>' . esc_html($vehicle['titol-anunci']) . '</h1>';
    echo '</div>';
}
get_footer();
