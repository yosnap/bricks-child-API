<?php
if (!defined('ABSPATH')) exit; // Seguridad

function motoraldia_display_vehicles_loop($number_of_items = -1) {
    // Obtener los datos de la API
    $data = get_vehicles_data();
    
    if (empty($data['vehicles'])) {
        echo '<p>No hay vehículos disponibles</p>';
        return;
    }

    $vehicles = $data['vehicles'];
    if ($number_of_items > 0) {
        $vehicles = array_slice($vehicles, 0, $number_of_items);
    }

    echo '<div class="vehicles-grid">';
    
    foreach ($vehicles as $vehicle) {
        // Variables principales
        $title = esc_html($vehicle['titol-anunci']);
        $price = number_format($vehicle['preu'], 0, ',', '.') . ' €';
        $image_url = esc_url($vehicle['imatge-destacada-url']);
        $year = esc_html($vehicle['any']);
        $seller_type = esc_html($vehicle['venedor']);
        $mileage = number_format($vehicle['quilometratge'], 0, ',', '.') . ' km';
        
        // Generar URL de detalle
        $detail_url = home_url("/vehicle-detail/{$vehicle['id']}/");
        
        // HTML del vehículo
        ?>
        <div class="vehicle-card">
            <div class="vehicle-image">
                <a href="<?php echo $detail_url; ?>">
                    <img src="<?php echo $image_url; ?>" alt="<?php echo $title; ?>">
                </a>
            </div>
            <div class="vehicle-info">
                <h3 class="vehicle-title">
                    <a href="<?php echo $detail_url; ?>"><?php echo $title; ?></a>
                </h3>
                <div class="vehicle-meta">
                    <span class="year"><?php echo $year; ?></span>
                    <span class="mileage"><?php echo $mileage; ?></span>
                </div>
                <div class="vehicle-price"><?php echo $price; ?></div>
                <div class="vehicle-seller"><?php echo ucfirst($seller_type); ?></div>
                <a href="<?php echo $detail_url; ?>" class="vehicle-button">Ver detalles</a>
            </div>
        </div>
        <?php
    }
    
    echo '</div>';
}

// Agregar estilos CSS
add_action('wp_head', function() {
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
        
        .vehicle-image {
            position: relative;
            overflow: hidden;
        }
        
        .vehicle-image img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .vehicle-image:hover img {
            transform: scale(1.05);
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
            transition: color 0.2s;
        }
        
        .vehicle-title a:hover {
            color: #3498db;
        }
        
        .vehicle-meta {
            display: flex;
            gap: 15px;
            margin-bottom: 10px;
            color: #666;
            font-size: 0.9em;
        }
        
        .vehicle-price {
            font-size: 1.4em;
            font-weight: bold;
            color: #2c3e50;
            margin: 10px 0;
        }
        
        .vehicle-seller {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 10px;
        }
        
        .vehicle-button {
            display: inline-block;
            padding: 8px 20px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: all 0.2s;
            width: 100%;
            text-align: center;
            box-sizing: border-box;
        }
        
        .vehicle-button:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
    </style>
    <?php
});
