<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class MProducts extends CI_Model {
	
	private $db_psadmin;

    public function __construct() {

        parent::__construct();
        $this->load->database();
        //~ $this->db_psadmin = $this->load->database('psadmin', TRUE);  // Indicamos que use la base de datos 'psadmin' en vez de 'm32018'
    }

    //Public method to obtain the orders
    public function obtener() {
        $query = $this->db->get('attribute_product');
        //~ $query = $db_psadmin->get('attribute_product');

        if ($query->num_rows() > 0)
            return $query->result();
        else
            return $query->result();
    }

    // Método para consultar los atributos asociados a un prodcuto dado
    public function obtenerTelas($table, $field, $value) {
		
		$select = "a_g_l.id_attribute_group, a_g_l.public_name, a_l.id_attribute, a_l.name";
		$query = $this->db->select($select);
		$query = $this->db->from($table);
		$query = $this->db->join('product p', 'p.id_product=p_a.id_product');
		$query = $this->db->join('product_attribute_combination p_a_c', 'p_a_c.id_product_attribute=p_a.id_product_attribute');
		$query = $this->db->join('attribute a', 'a.id_attribute=p_a_c.id_attribute');
		$query = $this->db->join('attribute_group_lang a_g_l', 'a_g_l.id_attribute_group=a.id_attribute_group');
		$query = $this->db->join('attribute_lang a_l', 'a_l.id_attribute=a.id_attribute');
        $query = $this->db->where($field, $value);
        $query = $this->db->where('a_l.id_lang', 1);
        $query = $this->db->group_by(array('a.id_attribute_group', 'a_l.name'));
        $query = $this->db->order_by('a_l.id_attribute');
        $query = $this->db->get();
        
        //~ echo $this->db->last_query();

        if ($query->num_rows() > 0)
            return $query->result();
        else
            return $query->result();
    }

    // Método para consultar los atributos asociados a un prodcuto dado
    public function obtenerCombinaciones() {
		
		$select = "p.id_product, p_l.name as product_name, a_l.id_attribute, a_l.name as attribute_name, p_a.id_product_attribute";
		$query = $this->db->select($select);
		$query = $this->db->from('product p');
		$query = $this->db->join('product_lang p_l', 'p_l.id_product=p.id_product');
		$query = $this->db->join('product_attribute p_a', 'p_a.id_product=p.id_product');
		$query = $this->db->join('product_attribute_combination p_a_c', 'p_a_c.id_product_attribute=p_a.id_product_attribute');
		$query = $this->db->join('attribute a', 'a.id_attribute=p_a_c.id_attribute');
		$query = $this->db->join('attribute_group_lang a_g_l', 'a_g_l.id_attribute_group=a.id_attribute_group');
		$query = $this->db->join('attribute_lang a_l', 'a_l.id_attribute=p_a_c.id_attribute');
        $query = $this->db->group_by(array('p_l.name', 'a_l.name'));
        $query = $this->db->order_by('p.id_product, a_l.id_attribute');
        $query = $this->db->get();
        
        //~ echo $this->db->last_query();

        if ($query->num_rows() > 0)
            return $query->result();
        else
            return $query->result();
    }

    // Método para consultar los atributos asociados a un prodcuto dado
    public function obtenerAtributos($table, $field, $value) {
		
		$select = "a_g_l.id_attribute_group, a_g_l.public_name, a_l.id_attribute, a_l.name";
		$query = $this->db->select($select);
		$query = $this->db->from($table);
		$query = $this->db->join('attribute_group_lang a_g_l', 'a_p.id_attribute_group=a_g_l.id_attribute_group');
		$query = $this->db->join('attribute_lang a_l', 'a_p.id_attribute_lang=a_l.id_attribute');
        $query = $this->db->where($field, $value);
        $query = $this->db->group_by(array('a_p.id_attribute_group', 'a_l.name'));
        $query = $this->db->order_by('a_l.id_attribute');
        $query = $this->db->get();
		//~ $query = $this->db_psadmin->select($select);
		//~ $query = $this->db_psadmin->from($table);
		//~ $query = $this->db_psadmin->join('attribute_group_lang a_g_l', 'a_p.id_attribute_group=a_g_l.id_attribute_group');
		//~ $query = $this->db_psadmin->join('attribute_lang a_l', 'a_p.id_attribute_lang=a_l.id_attribute');
        //~ $query = $this->db_psadmin->where($field, $value);
        //~ $query = $this->db_psadmin->group_by(array('a_p.id_attribute_group', 'a_l.name'));
        //~ $query = $this->db_psadmin->order_by('a_l.id_attribute');
        //~ $query = $this->db_psadmin->get();
        
        //~ echo $this->db->last_query();

        if ($query->num_rows() > 0)
            return $query->result();
        else
            return $query->result();
    }
    
    // Método para consultar los datos básicos de un producto.
    public function obtenerById($table, $field, $value){
		
		$select = "p.id_product, p_l.name, p.reference";
		$query = $this->db->select($select);
		$query = $this->db->from($table);
		$query = $this->db->join('product_lang p_l', 'p.id_product=p_l.id_product');
        $query = $this->db->where($field, $value);
        $query = $this->db->where('p_l.id_lang', 1);
        $query = $this->db->order_by('p.id_product');
        $query = $this->db->get();
        
        //~ echo $this->db->last_query();
        
        return $query->result();
        
	}

}

?>
