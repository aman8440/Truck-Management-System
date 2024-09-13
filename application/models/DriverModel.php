<?php
defined('BASEPATH') or exit('No direct script access allowed');
class DriverModel extends CI_Model
{

  public function get_drivers($search = '', $sort = 'dri_name', $order = 'asc', $page = 1, $limit = 5)
  {
    $offset = ($page - 1) * $limit;

    // Main query to get data
    $this->db->select('drivers.*, dispatchers.dis_name');
    $this->db->from('drivers');
    $this->db->join('dispatchers', 'drivers.dispatchers_id = dispatchers.id', 'left');
    $this->db->where('drivers.deleted_at', NULL);

    if (!empty($search)) {
      $this->db->group_start();
      $this->db->like('dri_name', $search);
      $this->db->or_like('dri_email', $search);
      $this->db->or_like('dri_phone', $search);
      $this->db->or_like('license_number', $search);
      $this->db->or_like('license_expiry_date', $search);
      $this->db->or_like('dispatchers.dis_name', $search);
      $this->db->or_like('drivers.status', $search);
      $this->db->group_end();
    }

    $this->db->order_by($sort, $order);
    $this->db->limit($limit, $offset);
    $query = $this->db->get();
    $result['data'] = $query->result();

    // Query to get total number of records
    $this->db->from('drivers');
    $this->db->join('dispatchers', 'drivers.dispatchers_id = dispatchers.id', 'left');
    $this->db->where('drivers.deleted_at', NULL);

    if (!empty($search)) {
      $this->db->group_start();
      $this->db->like('dri_name', $search);
      $this->db->or_like('dri_email', $search);
      $this->db->or_like('dri_phone', $search);
      $this->db->or_like('license_number', $search);
      $this->db->or_like('license_expiry_date', $search);
      $this->db->or_like('dispatchers.dis_name', $search);
      $this->db->or_like('drivers.status', $search);
      $this->db->group_end();
    }

    $result['total'] = $this->db->count_all_results();

    return $result;
  }

  public function get_driver($id = FALSE)
  {
    $this->db->select('drivers.*, dispatchers.dis_name');
    $this->db->from('drivers');
    $this->db->join('dispatchers', 'drivers.dispatchers_id = dispatchers.id', 'left');

    if ($id === FALSE) {
      $this->db->where('drivers.deleted_at', NULL);
      $query = $this->db->get();
      return $query->result_array();
    }

    $this->db->where('drivers.id', $id);
    $this->db->where('drivers.deleted_at', NULL);
    $query = $this->db->get();
    return $query->row_array();
  }
  public function create_drivers($data)
  {
    if ($this->is_unique_drivers_email($data['dri_email']) && $this->is_unique_drivers_phone($data['dri_phone']) && $this->is_unique_drivers_license($data['license_number']) || $this->is_unique_drivers_name($data['dri_name'])) {
      return $this->db->insert('drivers', $data);
    } else {
      return false;
    }
  }

  public function update_drivers($id, $data)
  {
    $drivers = $this->get_driver($id);
    if ($drivers['dri_email'] === $data['dri_email'] || $drivers['dri_phone'] === $data['dri_phone'] || $drivers['license_number'] === $data['license_number'] || $drivers['dri_name'] === $data['dri_name'] || $this->is_unique_drivers_email($data['dri_email']) || $this->is_unique_drivers_phone($data['dri_phone']) || $this->is_unique_drivers_license($data['license_number']) || $this->is_unique_drivers_name($data['dri_name'])) {
      $this->db->where('id', $id);
      return $this->db->update('drivers', $data);
    } else {
      return FALSE;
    }
  }

  public function delete_drivers($id)
  {
    $data = array(
      'deleted_at' => date('Y-m-d H:i:s')
    );
    $this->db->where('id', $id);
    return $this->db->update('drivers', $data);
  }

  public function get_drivers_by_name($name)
  {
    $query = $this->db->get_where('drivers', ['dri_name' => $name]);
    return $query->row();
  }
  public function get_drivers_by_name_load($name, $dis)
  {
    $this->db->where('dispatchers_id', $dis);
    $this->db->where('dri_name', $name);
    $query = $this->db->get('drivers');
    return $query->row();
  }
  public function get_drivers_by_disId($dispatchers_id)
  {
    $query = $this->db->get_where('drivers', ['dispatchers_id' => $dispatchers_id]);
    return $query->row();
  }
  public function is_unique_drivers_email($dri_email)
  {
    $this->db->where('dri_email', $dri_email);
    $query = $this->db->get('drivers');
    return $query->num_rows() == 0;
  }
  public function is_unique_drivers_name($dri_name)
  {
    $this->db->where('dri_name', $dri_name);
    $query = $this->db->get('drivers');
    return $query->num_rows() == 0;
  }
  public function is_unique_drivers_phone($dri_phone)
  {
    $this->db->where('dri_phone', $dri_phone);
    $query = $this->db->get('drivers');
    return $query->num_rows() == 0;
  }
  public function is_unique_drivers_license($license_number)
  {
    $this->db->where('license_number', $license_number);
    $query = $this->db->get('drivers');
    return $query->num_rows() == 0;
  }
}
;
