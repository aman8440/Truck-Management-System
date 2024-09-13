<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Load_DocModel extends CI_Model
{
    public function get_load_docs($id = FALSE)
    {
      if ($id === FALSE) {
        $this->db->where('deleted_at', NULL);
        $query = $this->db->get('load_doc');
        return $query->result_array();
      }
  
      $this->db->where('id', $id);
      $this->db->where('deleted_at', NULL);
      $query = $this->db->get('load_doc');
      return $query->row_array();
    }

    public function create_load_doc($data)
    {
        return $this->db->insert('load_doc', $data);
    }

    public function update_load_doc($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('load_doc', $data);
    }

    public function delete_load_doc($id)
    {
      $data = array(
        'deleted_at' => date('Y-m-d H:i:s')
      );
      $this->db->where('id', $id);
      return $this->db->update('load_doc', $data);
    }
};