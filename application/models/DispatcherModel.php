<?php
defined('BASEPATH') or exit('No direct script access allowed');
class DispatcherModel extends CI_Model
{

  public function get_dispatchers($search = '', $sort = 'dis_name', $order = 'asc', $page = 1, $limit = 5)
  {
    $offset = ($page - 1) * $limit;
    $this->db->where('deleted_at', NULL);
    if (!empty($search)) {
      $this->db->group_start();
      $this->db->like('dis_name', $search);
      $this->db->or_like('dis_email', $search);
      $this->db->or_like('dis_phone', $search);
      $this->db->or_like('status', $search);
      $this->db->group_end();
    }
    $this->db->order_by($sort, $order);
    $this->db->limit($limit, $offset);

    $query = $this->db->get('dispatchers');
    $result['data'] = $query->result();

    if (!empty($search)) {
      $this->db->group_start();
      $this->db->like('dis_name', $search);
      $this->db->or_like('dis_email', $search);
      $this->db->or_like('dis_phone', $search);
      $this->db->or_like('status', $search);
      $this->db->group_end();
    }
    $result['total'] = $this->db->count_all_results('dispatchers');

    return $result;
  }
  public function get_dispatche($id = FALSE)
  {
    if ($id === FALSE) {
      $this->db->where('deleted_at', NULL);
      $query = $this->db->get('dispatchers');
      return $query->result_array();
    }

    $this->db->where('id', $id);
    $this->db->where('deleted_at', NULL);
    $query = $this->db->get('dispatchers');
    return $query->row_array();
  }
  public function create_dispatchers($data, $admin_name)
  {
    if ($this->is_unique_dispatchers_email($data['dis_email']) && $this->is_unique_dispatchers_phone($data['dis_phone']) || $this->is_unique_dispatchers_name($data['dis_name'])) {
      $data['created_by'] = $admin_name;
      $data['updated_by'] = $admin_name;
      return $this->db->insert('dispatchers', $data);
    } else {
      return false;
    }
  }

  public function update_dispatchers($id, $data, $admin_name)
  {
    $dispatchers = $this->get_dispatche($id);
    if ($dispatchers['dis_email'] === $data['dis_email'] || $dispatchers['dis_name'] === $data['dis_name'] || $dispatchers['dis_phone'] === $data['dis_phone'] || $this->is_unique_dispatchers_email($data['dis_email']) || $this->is_unique_dispatchers_phone($data['dis_phone']) || $this->is_unique_dispatchers_name($data['dis_name'])) {
      $data['updated_by'] = $admin_name;
      $this->db->where('id', $id);
      return $this->db->update('dispatchers', $data);
    } else {
      return FALSE;
    }
  }

  public function delete_dispatchers($id)
  {
    $data = array(
      'deleted_at' => date('Y-m-d H:i:s')
    );
    $this->db->where('id', $id);
    return $this->db->update('dispatchers', $data);
  }
  public function is_unique_dispatchers_email($dri_email)
  {
    $this->db->where('dis_email', $dri_email);
    $query = $this->db->get('dispatchers');
    return $query->num_rows() == 0;
  }
  public function is_unique_dispatchers_phone($dri_phone)
  {
    $this->db->where('dis_phone', $dri_phone);
    $query = $this->db->get('dispatchers');
    return $query->num_rows() == 0;
  }
  public function get_dispatcher_by_name($name)
  {
    $query = $this->db->get_where('dispatchers', ['dis_name' => $name]);
    return $query->row();
  }
  public function is_unique_dispatchers_name($dis_name)
  {
    $this->db->where('dis_name', $dis_name);
    $query = $this->db->get('dispatchers');
    return $query->num_rows() == 0;
  }
}
;
