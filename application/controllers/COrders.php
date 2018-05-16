<?php
//~ header('Content-Type: text/json; charset=utf-8;');
defined('BASEPATH') OR exit('No direct script access allowed');

class COrders extends CI_Controller {
	
	public $tables;  // Tablas con relación directa a las ordenes
	
	public $tables2;  // Tablas secundarias

    public function __construct() {
        parent::__construct();
        
        /* Definición de tablas a incluir en el json, incluyendo sus campos de relación a detallar.
         * Cada tabla tiene un arreglo de relaciones a detallar, pero el primer elemento de dicho arreglo es un
         * definidor de tabla, para identificar si la tabla de relación directa o indirecta.
         * */
        $this->tables = array(
			'orders' => array(
				'id_shop_group','id_shop','id_carrier','id_lang','id_customer','id_cart','id_currency',
				'id_address_delivery','id_address_invoice'
			),
			'order_carrier' => array(1,'id_carrier'),
			'order_cart_rule' => array(1,'id_cart_rule'),
			'order_detail' => array(
				'id_warehouse','id_shop','product_id','product_attribute_id','id_customization','id_tax_rules_group'
			),
			'order_history' => array(
				'id_employee','id_order_state'
			),
			'order_invoice' => array(),
			'order_invoice_payment' => array(),
			'order_payment' => array('id_currency'),
			'order_return' => array('id_customer'),
			'order_slip' => array('id_customer')
		);
		
		$this->tables2 = array(
			'order_detail_tax' => array(
				'id_tax'
			),
			'order_invoice_tax' => array(
				'id_tax'
			),
			'order_return_detail' => array('id_order_detail','id_customization'),
			'order_slip_detail' => array('id_order_detail')
		);
		
		$this->tables3 = array(
			'order_slip_detail_tax' => array(
				'id_tax'
			)
		);
        
        // Load database
        $this->load->model('MOrders');
    }

    // Método para cargar los datos de una orden según el id
    public function index($order_id) {
		
		// Consultamos los datos de la orden correspondiente para disponer de los mismos más adelante
		$data_order = $this->MOrders->obtenerById('orders', 'id_order', $order_id);
		
		// Variable que contendrá la nueva data armada que incluirá los datos relacionados de primer nivel
		$new_data = array();
		
		if(count($data_order) > 0){
			
			// Ids de las tablas con relaciones a terceras tablas
			$id_order_detail = array();
			$id_order_invoice = array();
			$id_order_return = array();
			$id_order_slip = array();
		
			$i = 0;
			// CICLO DE RECORRIDO DE TABLAS PRIMARIAS
			foreach($this->tables as $table => $relations){
				
				$field;  // Campo Identificador a usar para filtrar la orden, puede ser el campo 'id_order' o el campo 'reference'.
				
				$value;  // Identificador a usar para filtrar la orden, cuyo valor puede venir del campo 'id_order' o del campo 'reference'.
				
				// Si el campo id_order existe en la tabla actual, usamos dicho campo y su valor como identificadores para filtrar
				if($this->db->field_exists('id_order', $table)){
					$field = 'id_order';
					$value = $order_id;
				}else{
					$field = 'order_reference';
					$value = $data_order[0]->reference;
				}
				
				// Consultamos la tabla correspondiente
				$data = $this->MOrders->obtenerById($table, $field, $value);
				
				// Contador de registros
				$j = 0;
				
				$reg_data = array();
				
				foreach($data as $registro){
					
					// Contador de campos
					$k = 0;
					
					$field_data = array();
					
					foreach($registro as $key => $valor){
						
						// Guardamos los ids de las tablas con relaciones secundarias
						if($table == "order_detail" && $key == "id_order_detail"){
							$id_order_detail[] = $valor;
						}
						if($table == "order_invoice" && $key == "id_order_invoice"){
							$id_order_invoice[] = $valor;
						}
						if($table == "order_return" && $key == "id_order_return"){
							$id_order_return[] = $valor;
						}
						if($table == "order_slip" && $key == "id_order_slip"){
							$id_order_slip[] = $valor;
						}
						
						// Cargamos cada campo-valor del registro
						$field_data[$key] = $valor;
						
						// Si la clave actual está presente en la lista de ids asociativos
						if(in_array($key, $relations)){
							
							// Separamos el nombre de la tabla a consultar
							$table2 = explode("_", $key);
							
							// Armamos el nombre de la tabla a consultar
							if(count($table2) > 3){
								$table2 = $table2[1]."_".$table2[2]."_".$table2[3];
							}else if(count($table2) > 2){
								// Si es una clave de dirección entonces tomamos sólo la segunda palabra y obviamos la tercera
								if($key == "id_address_delivery" || $key == "id_address_invoice"){
									$table2 = $table2[1];
								}
								// Si es una clave con nomenclatura invertida 'xxxx_xxxx_id' tomamos la primera palabra
								else if($table2[2] == "id"){
									$table2 = $table2[0]."_".$table2[1];
								}else{
									$table2 = $table2[1]."_".$table2[2];
								}
							}else if(count($table2) == 2){
								// Si es una clave con nomenclatura invertida 'xxxx_id' tomamos la primera palabra
								if($table2[1] == "id"){
									$table2 = $table2[0];
								}else{
									$table2 = $table2[1];
								}
							}
							
							$clave = $key;
							// Si es una clave de dirección entonces fijamos la misma en 'id_address'
							if($key == "id_address_delivery" || $key == "id_address_invoice"){
								$clave = 'id_address';
							}
							// Si es una clave de producto entonces fijamos la misma en 'id_product'
							else if($key == "product_id"){
								$clave = 'id_product';
							}
							// Si es una clave de atributo de producto entonces fijamos la misma en 'id_product_attribute'|
							else if($key == "product_attribute_id"){
								$clave = 'id_product_attribute';
							}
							
							// Consultamos los detalles de la asociación a la tabla resultante
							$data_detalle = $this->MOrders->obtenerDetalle($table2, $clave, $valor);
							
							// Cargamos un nuevo campo-valor con los detalles del campo asociado
							// En este caso la clave será simplificada y recortada para eliminar el segmento 'id_'
							$new_key = explode("_", $key, 2);
							// Si es una clave con nomenclatura invertida 'xxxx_xxxx_id' tomamos la primera palabra
							if($key == "product_id"){
								$new_key = $new_key[0];
							}
							// Si es una clave con nomenclatura invertida 'xxxx_xxxx_id' tomamos la primera palabra
							else if($key == "product_attribute_id"){
								$new_key = substr($key, 0, -3);
							}
							// Si es una clave con nomenclatura normal 'id_xxxx' tomamos la seguda palabra
							else{
								$new_key = $new_key[1];
							}
							$field_data[$new_key] = $data_detalle;
							
							// foreach($data_detalle as $reg){
								// 
								// foreach($reg as $key_sub => $valor_sub){
									// 
								// }
							// 
							// }
						}
						
					}
					
					$reg_data[] = $field_data;
					
				}
				
				// Convertimos la tabla orders a su singular para dar más sentido al conjunto de datos
				if($table == "orders"){
					$table = "order";
				}
				
				// Creamos un indice por cada tabla y le asignamos los registros correspondientes
				$new_data[$table] = $reg_data;
				
				$i++;
			}
			
			// Ids de las tablas con relaciones a cuartas tablas
			$id_order_slip_detail = array();
			
			// Valores de los ids de las tablas a recorrer
			$values_ids = array(
				"order_detail_tax" => $id_order_detail, 
				"order_invoice_tax" => $id_order_invoice, 
				"order_return_detail" => $id_order_return,
				"order_slip_detail" => $id_order_slip
			);
			
			// Añadimos los ids a consultar en las tablas secundarias
			array_unshift($this->tables2["order_detail_tax"], $id_order_detail);
			array_unshift($this->tables2["order_invoice_tax"], $id_order_invoice);
			array_unshift($this->tables2["order_return_detail"], $id_order_return);
			array_unshift($this->tables2["order_slip_detail"], $id_order_slip);
		
			$i = 0;
			// CICLO DE RECORRIDO DE TABLAS SECUNDARIAS
			foreach($this->tables2 as $table => $relations){
				
				$field;  // Campo Identificador a usar para filtrar la orden.
				
				$value;  // Identificador a usar para filtrar la orden.
				
				// Recorremos la lista de ids a consultar
				foreach($relations[0] as $id_value){
				
					// Construcción del campo identificador y el valor correspondiente
					$field = explode("_", $table);
					$field = "id_".$field[0]."_".$field[1];
					
					$value = $id_value;
					
					// Consultamos la tabla correspondiente
					$data = $this->MOrders->obtenerById($table, $field, $value);
					
					// Contador de registros
					$j = 0;
					
					$reg_data = array();
					
					foreach($data as $registro){
						
						// Contador de campos
						$k = 0;
						
						$field_data = array();
						
						foreach($registro as $key => $valor){
							
							// Guardamos los ids de las tablas con relaciones terciarias
							if($table == "order_slip_detail" && $key == "id_order_slip_detail"){
								$id_order_slip_detail[] = $valor;
							}
							
							// Cargamos cada campo-valor del registro
							$field_data[$key] = $valor;
							
							// Si la clave actual está presente en la lista de ids asociativos
							if(in_array($key, $relations)){
								
								// Separamos el nombre de la tabla a consultar
								$table2 = explode("_", $key);
								
								// Armamos el nombre de la tabla a consultar
								if(count($table2) > 3){
									$table2 = $table2[1]."_".$table2[2]."_".$table2[3];
								}else if(count($table2) > 2){
									// Si es una clave de dirección entonces tomamos sólo la segunda palabra y obviamos la tercera
									if($key == "id_address_delivery" || $key == "id_address_invoice"){
										$table2 = $table2[1];
									}
									// Si es una clave con nomenclatura invertida 'xxxx_xxxx_id' tomamos las dos primeras palabras
									else if($table2[2] == "id"){
										$table2 = $table2[0]."_".$table2[1];
									}else{
										$table2 = $table2[1]."_".$table2[2];
									}
								}else if(count($table2) == 2){
									// Si es una clave con nomenclatura invertida 'xxxx_id' tomamos la primera palabra
									if($table2[1] == "id"){
										$table2 = $table2[0];
									}else{
										$table2 = $table2[1];
									}
								}
								
								$clave = $key;
								// Si es una clave de dirección entonces fijamos la misma en 'id_address'
								if($key == "id_address_delivery" || $key == "id_address_invoice"){
									$clave = 'id_address';
								}
								// Si es una clave de producto entonces fijamos la misma en 'id_product'
								else if($key == "product_id"){
									$clave = 'id_product';
								}
								// Si es una clave de atributo de producto entonces fijamos la misma en 'id_product_attribute'|
								else if($key == "product_attribute_id"){
									$clave = 'id_product_attribute';
								}
								
								// Consultamos los detalles de la asociación a la tabla resultante
								$data_detalle = $this->MOrders->obtenerDetalle($table2, $clave, $valor);
								
								// Cargamos un nuevo campo-valor con los detalles del campo asociado
								// En este caso la clave será simplificada y recortada para eliminar el segmento 'id_'
								$new_key = explode("_", $key, 2);
								// Si es una clave con nomenclatura invertida 'xxxx_xxxx_id' tomamos la primera palabra
								if($key == "product_id"){
									$new_key = $new_key[0];
								}
								// Si es una clave con nomenclatura invertida 'xxxx_xxxx_id' tomamos la primera palabra
								else if($key == "product_attribute_id"){
									$new_key = substr($key, 0, -3);
								}
								// Si es una clave con nomenclatura normal 'id_xxxx' tomamos la seguda palabra
								else{
									$new_key = $new_key[1];
								}
								$field_data[$new_key] = $data_detalle;
								
							}
							
						}
						
						$reg_data[] = $field_data;
						
					}
					
				}
				
				// Creamos un indice por cada tabla y le asignamos los registros correspondientes
				$new_data[$table] = $reg_data;
				
				$i++;
			}
			
			
			//~ Convertimos los datos resultantes a formato JSON
			$jsonencoded = json_encode($new_data, JSON_UNESCAPED_UNICODE);
			echo $jsonencoded;
			
		}
    }
    
	// Generación del reporte de la orden
    function detail_order($order_id)
    {
        // Consultamos los datos de la orden
		$get2 = file_get_contents(base_url()."orders/".$order_id);
		$exchangeRates2 = json_decode($get2, true);
        
        $data['order'] = $exchangeRates2;
        
        $this->load->view('pdf/order_report', $data);
    }

}
