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
				array(
					'methods' => 'PUT',
					'callback' => array( $this, 'edit_repartidor'),
					'permission_callback' => '__return_true',
				),
				array(
					'methods' => 'DELETE',
					'callback' => array( $this, 'del_repartidor'),
					'permission_callback' => '__return_true', 
				),
			)
		);
		register_rest_route(
			$this->namespace, 
			'/' . $this->rest_areas,
			array(
				array(
					'methods' => 'GET',
					'callback' => array( $this, 'get_areas'),
					'permission_callback' => '__return_true', 
				),
				array(
					'methods' => 'POST',
					'callback' => array( $this, 'add_area'),
					'permission_callback' => '__return_true', 
				),
				array(
					'methods' => 'PUT',
					'callback' => array( $this, 'edit_area'),
					'permission_callback' => '__return_true',
				),
				array(
					'methods' => 'DELETE',
					'callback' => array( $this, 'del_area'),
					'permission_callback' => '__return_true', 
				),
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
		$resp = new WP_REST_Response($areas, 200);
		$resp->header('Content-Type', 'application/json');
		$resp->header('X-WP-Total', count($areas));
		return $resp;
	}
	
	public function add_area( $request ) {
		global $wpdb;
		$params = $request->get_params();
		if ( ! isset( $params['area'] ) || ! preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚüÜ\s]+$/', $params['area'] )) {
			return new WP_Error( 'invalid_parameter', 'El nombre de área ha sido omitido o contiene caracteres inválidos.', array( 'status' => 400 ) ); }
		if ( ! preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚüÜ\s]*$/', $params['descripcion'] )) {
			return new WP_Error( 'invalid_parameter', 'La descripción del área contiene caracteres inválidos.', array( 'status' => 400 ) ); }
		$result = $wpdb->insert(
			$wpdb->prefix . TABLA_REPARTIDORES_AREAS,
			array(
				'area' => $params['area'],
				'descripcion' => $params['descripcion']
			)
		);
		if(!$result){
			return new WP_Error( 'error', 'No se pudo agregar el área.', array( 'status' => 500 ) );
		}
	}
	
	public function edit_area( $request ) {
		global $wpdb;
		$params = $request->get_params();
		
		if( ! isset( $params['id'] ) || ! ctype_digit( $params['id'] )) {
			return new WP_Error( 'invalid_parameter', 'El parámetro ID ha sido omitido o es inválido.', array( 'status' => 400 ) ); }
		if ( ! isset( $params['area'] ) || ! preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚüÜ\s]+$/', $params['area'] )) {
			return new WP_Error( 'invalid_parameter', 'El nombre de área ha sido omitido o contiene caracteres inválidos.', array( 'status' => 400 ) ); }
		if ( ! preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚüÜ\s]*$/', $params['descripcion'] )) {
			return new WP_Error( 'invalid_parameter', 'La descripción del área contiene caracteres inválidos.', array( 'status' => 400 ) ); }
		
		$result = $wpdb->update(
			$wpdb->prefix . TABLA_REPARTIDORES_AREAS,
			array(
				'area' => $params['area'],
				'descripcion' => $params['descripcion'],
			),
			array( 'id' => $params['id'] )
		);
		if(!$result){
			return new WP_Error( 'error', 'No se pudo actualizar el área.', array( 'status' => 500 ) );
		}
	}
	
	public function del_area( $request ) {
		global $wpdb;
		$params = $request->get_params();
		if( ! isset( $params['id'] ) || ! ctype_digit( $params['id'] )) {
			return new WP_Error( 'invalid_parameter', 'Parámetro ID faltante o inválido.', array( 'status' => 400 ) );
		}
		$result = $wpdb->delete(
			$wpdb->prefix . TABLA_REPARTIDORES_AREAS,
			array( 'id' => $params['id'] ),
			array( '%d' )
		);
		if(!$result){
			return new WP_Error( 'error', 'No se pudo eliminar el área.', array( 'status' => 500 ) );
		}
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
					'telefono' => $row->telefono,
					'area' => $row->area
				];
			}
		}
		$resp = new WP_REST_Response($repartidores, 200);
		$resp->header('Content-Type', 'application/json');
		$resp->header('X-WP-Total', count($repartidores));
		return $resp;
	}
	
	public function add_repartidor( $request ) {
		global $wpdb;
		$params = $request->get_params();
		
		if ( ! isset( $params['nombre'] ) || ! preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚüÜ\s]+$/', $params['nombre'] )) {
			return new WP_Error( 'invalid_parameter', 'El nombre del repartidor ha sido omitido o contiene caracteres inválidos.', array( 'status' => 400 ) ); }
		if ( ! preg_match('/^[0-9+.-()\s]*$/', $params['telefono'] )) {
			return new WP_Error( 'invalid_parameter', 'El número telefónico contiene caracteres inválidos.', array( 'status' => 400 ) ); }
		
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
	
	public function edit_repartidor( $request ) {
		global $wpdb;
		$params = $request->get_params();
		
		if( ! isset( $params['id'] ) || ! ctype_digit( $params['id'] )) {
			return new WP_Error( 'invalid_parameter', 'El parámetro ID ha sido omitido o es inválido.', array( 'status' => 400 ) ); }
		if ( ! isset( $params['nombre'] ) || ! preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚüÜ\s]+$/', $params['nombre'] )) {
			return new WP_Error( 'invalid_parameter', 'El nombre del repartidor ha sido omitido o contiene caracteres inválidos.', array( 'status' => 400 ) ); }
		if ( ! preg_match('/^[0-9+.-()\s]*$/', $params['telefono'] ?? '' )) {
			return new WP_Error( 'invalid_parameter', 'El número telefónico contiene caracteres inválidos.', array( 'status' => 400 ) ); }
		if( isset( $params['area_id'] ) && ! ctype_digit( $params['area_id'] )) {
			return new WP_Error( 'invalid_parameter', 'El parámetro "area_id" es inválido.', array( 'status' => 400 ) ); }
		
		$result = $wpdb->update(
			$wpdb->prefix . TABLA_REPARTIDORES,
			array(
				'nombre' => $params['nombre'],
				'telefono' => $params['telefono'],
				'area_id' => $params['area_id'],
			),
			array( 'id' => $params['id'] )
		);
		if(!$result){
			return new WP_Error( 'error', 'No se pudieron actualizar los datos del repartidor.', array( 'status' => 500 ) );
		}
	}
	
	public function del_repartidor( $request ) {
		global $wpdb;
		$params = $request->get_params();
		if( ! isset( $params['id'] ) || ! ctype_digit( $params['id'] )) {
			return new WP_Error( 'invalid_parameter', 'Parámetro ID faltante o inválido.', array( 'status' => 400 ) );
		}
		$result = $wpdb->delete(
			$wpdb->prefix . TABLA_REPARTIDORES,
			array( 'id' => $params['id'] ),
			array( '%d' )
		);
		if(!$result){
			return new WP_Error( 'error', 'No se pudo eliminar el repartidor.', array( 'status' => 500 ) );
		}
	}

}
