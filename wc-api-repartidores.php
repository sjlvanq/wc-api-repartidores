<?php
/*
Plugin Name: WC API Repartidores
Description: WC API Repartidores
Version: dev-0.1
Author: Silvano Emanuel Roques
Author URI: https://dev.lode.uno/
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require plugin_dir_path( __FILE__ ) . 'wc-api-repartidores.conf.php';

add_action( 'woocommerce_init', 'wcrepartidores_woocommerce_init' );
register_activation_hook(__FILE__, 'wcrepartidores_table_create');

function wcrepartidores_woocommerce_init(){
	add_action('rest_api_init', function(){
		include_once( plugin_dir_path( __FILE__ ) . 'wc-api-repartidores.class.php' );
	});
	add_filter( 'woocommerce_rest_api_get_rest_namespaces', function($controllers){
		$controllers['wc/v3']['repartidores'] = 'WC_REST_Repartidores_Controller';
		return $controllers;
	});
}

function wcrepartidores_table_create() {
    global $wpdb;
    $tabla_repartidores = $wpdb->prefix . TABLA_REPARTIDORES_REPARTIDORES;
    $tabla_areas = $wpdb->prefix . TABLA_REPARTIDORES_AREAS;
    
    $sql_repartidores = "CREATE TABLE IF NOT EXISTS $tabla_repartidores 
			(
                id INT NOT NULL AUTO_INCREMENT,
                area_id INT,
                nombre VARCHAR(50),
                telefono VARCHAR(15),
                PRIMARY KEY (id),
                FOREIGN KEY (area_id) REFERENCES $tabla_areas(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            
	$sql_areas = "CREATE TABLE IF NOT EXISTS $tabla_areas 
			(
                id INT NOT NULL AUTO_INCREMENT,
                area VARCHAR(35),
                descripcion VARCHAR(250),
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            
    // Ejecutar las consultas
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_areas);
    dbDelta($sql_repartidores);
}

