<?php
defined('BASEPATH') or exit('No direct script access allowed');
class LoadModel extends CI_Model
{
  public function get_loads($id = FALSE)
  {
    if ($id === FALSE) {
      $this->db->where('deleted_at', NULL);
      $query = $this->db->get('loads');
      return $query->result_array();
    }

    $this->db->where('id', $id);
    $this->db->where('deleted_at', NULL);
    $query = $this->db->get('loads');
    return $query->row_array();
  }

  public function create_loads($data)
  {
    if($data['shipper_address'] !== $data['consignee_address']){
      return $this->db->insert('loads', $data);
    }
    else{
      return false;
    }
  }

  public function update_loads($id, $data)
  {
    if($data['shipper_address'] !== $data['consignee_address']){
      $this->db->where('id', $id);
      return $this->db->update('loads', $data);
    }
    else{
      return false;
    }
  }

  public function delete_loads($id)
  {
    $data = array(
      'deleted_at' => date('Y-m-d H:i:s')
    );
    $this->db->where('id', $id);
    return $this->db->update('loads', $data);
  }
};
