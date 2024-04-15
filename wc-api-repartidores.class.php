<?php
require_once plugin_dir_path( __FILE__ ) . 'wc-api-repartidores.conf.php';

class WC_REST_Repartidores_Controller {
	protected $namespace = 'wc/v3';
	protected $rest_base = '/repartidores';
	protected $rest_areas = '/repartidores/areas';
	
	public function register_routes() {
		register_rest_route(
			$this->namespace, 
			'/' . $this->rest_base,
			array(
				array(
					'methods' => 'GET',
					'callback' => array( $this, 'get_repartidores'),
					'permission_callback' => '__return_true', 
				),
				array(
					'methods' => 'POST',
					'callback' => array( $this, 'add_repartidor'),
					'permission_callback' => '__return_true', 
				),
			)
		);
		register_rest_route(
			$this->namespace, 
			'/' . $this->rest_areas,
			array(
				'methods' => 'GET',
				'callback' => array( $this, 'get_areas'),
				'permission_callback' => '__return_true', 
			)
		);
	}
	
	public function get_areas() {
		global $wpdb;
		$areas = [];
		$tabla_areas = $wpdb->prefix . TABLA_REPARTIDORES_AREAS;
		$query = "SELECT * FROM $tabla_areas;";
		$results = $wpdb->get_results($query);
		if($results) {
			foreach ( $results as $row ) {
				$areas[] = [
					'id' => $row->id,
					'area' => $row->area,
					'descripcion' => $row->descripcion
				];
			}
		}
		$json_response = json_encode($repartidores);
		header('Content-Type: application/json');
		return $areas;
	}
	
	public function get_repartidores() {
		global $wpdb;
		$repartidores = [];
		$tabla_areas = $wpdb->prefix . TABLA_REPARTIDORES_AREAS;
		$tabla_repartidores = $wpdb->prefix . TABLA_REPARTIDORES;
		$query = "SELECT r.id, r.nombre, r.telefono, a.area FROM $tabla_repartidores r LEFT JOIN $tabla_areas a ON r.area_id = a.id;";
		$results = $wpdb->get_results($query);
		if($results) {
			foreach ( $results as $row ) {
				$repartidores[] = [
					'id' => $row->id,
					'nombre' => $row->nombre,
					'area' => $row->area
				];
			}
		}
		$json_response = json_encode($repartidores);
		header('Content-Type: application/json');
		return $repartidores;
	}
	
	public function add_repartidor( $request ) {
		global $wpdb;
		$params = $request->get_params();
		if( !isset( $params['nombre'] ) || empty( $params['nombre'] )) {
			return new WP_Error( 'missing_parameter', 'El parámetro "nombre" es requerido.', array( 'status' => 400 ) );
		}
		$result = $wpdb->insert(
			$wpdb->prefix . TABLA_REPARTIDORES,
			array(
				'nombre' => $params['nombre'],
				'telefono' => $params['telefono']
			)
		);
		if(!$result){
			return new WP_Error( 'error', 'No se pudo agregar el repartidor.', array( 'status' => 500 ) );
		}
	}
}
