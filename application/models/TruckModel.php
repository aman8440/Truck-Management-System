<?php
class TruckModel extends CI_Model
{

  public function get_trucks($search = '', $sort = 'truck_number', $order = 'asc', $page = 1, $limit = 5)
  {
    $offset = ($page - 1) * $limit;

    $this->db->select('truck.*, drivers.dri_name');
    $this->db->from('truck');
    $this->db->join('drivers', 'truck.drivers_id = drivers.id', 'left');
    $this->db->where('truck.deleted_at', NULL);

    if (!empty($search)) {
      $this->db->group_start();
      $this->db->like('truck_number', $search);
      $this->db->or_like('model', $search);
      $this->db->or_like('capacity', $search);
      $this->db->or_like('truck_milege', $search);
      $this->db->or_like('registration_date', $search);
      $this->db->or_like('truck.status', $search);
      $this->db->or_like('drivers.dri_name', $search);
      $this->db->group_end();
    }

    $this->db->order_by($sort, $order);
    $this->db->limit($limit, $offset);
    $query = $this->db->get();
    $result['data'] = $query->result();

    // Query to get total number of records
    $this->db->from('truck');
    $this->db->join('drivers', 'truck.drivers_id = drivers.id', 'left');
    $this->db->where('truck.deleted_at', NULL);

    if (!empty($search)) {
      $this->db->group_start();
      $this->db->like('truck_number', $search);
      $this->db->or_like('model', $search);
      $this->db->or_like('capacity', $search);
      $this->db->or_like('truck_milege', $search);
      $this->db->or_like('registration_date', $search);
      $this->db->or_like('truck.status', $search);
      $this->db->or_like('drivers.dri_name', $search);
      $this->db->group_end();
    }

    $result['total'] = $this->db->count_all_results();

    return $result;
  }
  public function get_truc($id = FALSE)
  {
    $this->db->select('truck.*, drivers.dri_name');
    $this->db->from('truck');
    $this->db->join('drivers', 'truck.drivers_id = drivers.id', 'left');

    if ($id === FALSE) {
      $this->db->where('truck.deleted_at', NULL);
      $query = $this->db->get();
      return $query->result_array();
    }

    $this->db->where('truck.id', $id);
    $this->db->where('truck.deleted_at', NULL);
    $query = $this->db->get();
    return $query->row_array();
  }

  public function create_trucks($data)
  {
    if ($this->is_unique_truck_number($data['truck_number'])) {
      return $this->db->insert('truck', $data);
    } else {
      return false;
    }
  }

  public function update_trucks($id, $data)
  {
    $truck = $this->get_truc($id);
    if ($truck['truck_number'] === $data['truck_number'] || $this->is_unique_truck_number($data['truck_number'])) {
      $this->db->where('id', $id);
      return $this->db->update('truck', $data);
    } else {
      return FALSE;
    }
  }

  public function delete_trucks($id)
  {
    $data = array(
      'deleted_at' => date('Y-m-d H:i:s')
    );
    $this->db->where('id', $id);
    return $this->db->update('truck', $data);
  }
  public function get_truck_by_number($truck_number)
  {
    $query = $this->db->get_where('truck', ['truck_number' => $truck_number]);
    return $query->row();
  }
  public function get_truck_by_number_loads($truck_number, $id)
  {
    $this->db->where('drivers_id', $id);
    $this->db->where('truck_number', $truck_number);
    $query = $this->db->get('truck');
    return $query->row();
  }
  public function is_unique_truck_number($truck_number)
  {
    $this->db->where('truck_number', $truck_number);
    $query = $this->db->get('truck');
    return $query->num_rows() == 0;
  }
}
