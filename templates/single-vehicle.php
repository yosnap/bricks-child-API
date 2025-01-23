<?php
if (!defined('ABSPATH')) exit;

require_once get_stylesheet_directory() . '/includes/motoraldia-api.php';

// Obtener el slug del vehículo de la URL
$vehicle_slug = get_query_var('vehicle_slug');
if (!$vehicle_slug) {
    wp_redirect(home_url());
    exit;
}

// Obtener los datos del vehículo usando el slug
$vehicle = get_vehicle_by_slug($vehicle_slug);
if (!$vehicle) {
    wp_redirect(home_url());
    exit;
}

// Establecer el título de la página
add_filter('pre_get_document_title', function() use ($vehicle) {
    return esc_html($vehicle['titol-anunci']) . ' - ' . get_bloginfo('name');
});

// Establecer metadata para SEO
add_action('wp_head', function() use ($vehicle) {
    ?>
    <meta name="description" content="<?php echo esc_attr(wp_trim_words($vehicle['descripcio-anunci'], 20)); ?>">
    <meta property="og:title" content="<?php echo esc_attr($vehicle['titol-anunci']); ?>">
    <meta property="og:description" content="<?php echo esc_attr(wp_trim_words($vehicle['descripcio-anunci'], 20)); ?>">
    <?php if (!empty($vehicle['imatge-destacada-url'])): ?>
    <meta property="og:image" content="<?php echo esc_url($vehicle['imatge-destacada-url']); ?>">
    <?php endif; ?>
    <?php
});

get_header();
?>

<div class="vehicle-detail-container">
    <div class="vehicle-header">
        <div class="breadcrumbs">
            <a href="<?php echo home_url(); ?>">Inicio</a> &gt; 
            <a href="<?php echo home_url('/vehiculos/'); ?>">Vehículos</a> &gt; 
            <span><?php echo esc_html($vehicle['titol-anunci']); ?></span>
        </div>
        <h1><?php echo esc_html($vehicle['titol-anunci']); ?></h1>
    </div>

    <div class="vehicle-main">
        <div class="vehicle-gallery">
            <?php if (!empty($vehicle['imatge-destacada-url'])): ?>
                <div class="featured-image">
                    <img src="<?php echo esc_url($vehicle['imatge-destacada-url']); ?>" 
                         alt="<?php echo esc_attr($vehicle['titol-anunci']); ?>">
                </div>
            <?php endif; ?>

            <?php if (!empty($vehicle['galeria-vehicle-urls'])): ?>
                <div class="gallery-grid">
                    <?php foreach ($vehicle['galeria-vehicle-urls'] as $image_url): ?>
                        <div class="gallery-item">
                            <img src="<?php echo esc_url($image_url); ?>" 
                                 alt="Imagen de <?php echo esc_attr($vehicle['titol-anunci']); ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="vehicle-info">
            <div class="price-box">
                <span class="price"><?php echo number_format($vehicle['preu'], 0, ',', '.'); ?> €</span>
            </div>

            <div class="main-features">
                <?php if (!empty($vehicle['quilometratge'])): ?>
                    <div class="feature">
                        <span class="icon">🚗</span>
                        <span class="value"><?php echo number_format($vehicle['quilometratge'], 0, ',', '.'); ?> km</span>
                    </div>
                <?php endif; ?>
                <?php if (!empty($vehicle['any'])): ?>
                    <div class="feature">
                        <span class="icon">📅</span>
                        <span class="value"><?php echo esc_html($vehicle['any']); ?></span>
                    </div>
                <?php endif; ?>
                <?php if (!empty($vehicle['tipus-combustible'])): ?>
                    <div class="feature">
                        <span class="icon">⛽</span>
                        <span class="value"><?php echo esc_html($vehicle['tipus-combustible']); ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="specifications">
                <h2>Especificaciones</h2>
                <div class="specs-grid">
                    <?php
                    $specs = [
                        'marca' => ['label' => 'Marca', 'icon' => '🏢'],
                        'model' => ['label' => 'Modelo', 'icon' => '🚘'],
                        'potencia-cv' => ['label' => 'Potencia', 'icon' => '⚡'],
                        'canvi' => ['label' => 'Cambio', 'icon' => '⚙️'],
                        'places' => ['label' => 'Plazas', 'icon' => '👥'],
                        'portes' => ['label' => 'Puertas', 'icon' => '🚪'],
                        'color' => ['label' => 'Color', 'icon' => '🎨']
                    ];

                    foreach ($specs as $key => $spec):
                        if (!empty($vehicle[$key])):
                    ?>
                        <div class="spec-item">
                            <span class="spec-icon"><?php echo $spec['icon']; ?></span>
                            <span class="spec-label"><?php echo $spec['label']; ?></span>
                            <span class="spec-value"><?php echo esc_html($vehicle[$key]); ?></span>
                        </div>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </div>
            </div>

            <?php if (!empty($vehicle['descripcio-anunci'])): ?>
                <div class="description">
                    <h2>Descripción</h2>
                    <div class="description-content">
                        <?php echo nl2br(esc_html($vehicle['descripcio-anunci'])); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .vehicle-detail-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .vehicle-header {
        margin-bottom: 30px;
    }

    .breadcrumbs {
        margin-bottom: 15px;
        color: #666;
    }

    .breadcrumbs a {
        color: #3498db;
        text-decoration: none;
    }

    .vehicle-main {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 30px;
    }

    .vehicle-gallery {
        position: relative;
    }

    .featured-image img {
        width: 100%;
        height: auto;
        border-radius: 8px;
    }

    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 10px;
        margin-top: 20px;
    }

    .gallery-item img {
        width: 100%;
        height: 80px;
        object-fit: cover;
        border-radius: 4px;
        cursor: pointer;
    }

    .vehicle-info {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .price-box {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 4px;
        margin-bottom: 20px;
    }

    .price {
        font-size: 2em;
        font-weight: bold;
        color: #2c3e50;
    }

    .main-features {
        display: flex;
        justify-content: space-between;
        margin-bottom: 30px;
    }

    .feature {
        text-align: center;
    }

    .feature .icon {
        font-size: 1.5em;
        display: block;
        margin-bottom: 5px;
    }

    .specifications {
        margin-bottom: 30px;
    }

    .specs-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
    }

    .spec-item {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 10px;
        align-items: center;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 4px;
    }

    .spec-icon {
        font-size: 1.2em;
    }

    .spec-label {
        color: #666;
    }

    .spec-value {
        font-weight: bold;
    }

    .description {
        margin-top: 30px;
    }

    .description-content {
        line-height: 1.6;
        color: #444;
    }

    @media (max-width: 768px) {
        .vehicle-main {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php
get_footer();
