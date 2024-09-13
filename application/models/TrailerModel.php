<?php
class TrailerModel extends CI_Model
{

  public function get_trailers($search = '', $sort = 'trailer_number', $order = 'asc', $page = 1, $limit = 5)
  {
    $offset = ($page - 1) * $limit;
    $this->db->select('trailers.*, truck.truck_number');
    $this->db->from('trailers');
    $this->db->join('truck', 'trailers.truck_id = truck.id', 'left');
    $this->db->where('trailers.deleted_at', NULL);
    if (!empty($search)) {
      $this->db->group_start();
      $this->db->like('trailer_number', $search);
      $this->db->or_like('trailers.model', $search);
      $this->db->or_like('trailers.capacity', $search);
      $this->db->or_like('trailers.registration_date', $search);
      $this->db->or_like('trailer_type', $search);
      $this->db->or_like('trailers.status', $search);
      $this->db->or_like('truck.truck_number', $search);
      $this->db->group_end();
    }

    $this->db->order_by($sort, $order);
    $this->db->limit($limit, $offset);
    $query = $this->db->get();
    $result['data'] = $query->result();

    // Query to get total number of records
    $this->db->from('trailers');
    $this->db->join('truck', 'trailers.truck_id = truck.id', 'left');
    $this->db->where('trailers.deleted_at', NULL);

    if (!empty($search)) {
      $this->db->group_start();
      $this->db->like('trailer_number', $search);
      $this->db->or_like('trailers.model', $search);
      $this->db->or_like('trailers.capacity', $search);
      $this->db->or_like('trailers.registration_date', $search);
      $this->db->or_like('trailer_type', $search);
      $this->db->or_like('trailers.status', $search);
      $this->db->or_like('truck.truck_number', $search);
      $this->db->group_end();
    }

    $result['total'] = $this->db->count_all_results();

    return $result;

  }
  public function get_traile($id= FALSE)
  {
    $this->db->select('trailers.*, truck.truck_number');
    $this->db->from('trailers');
    $this->db->join('truck', 'trailers.truck_id = truck.id', 'left');

    if ($id === FALSE) {
      $this->db->where('trailers.deleted_at', NULL);
      $query = $this->db->get();
      return $query->result_array();
    }

    $this->db->where('trailers.id', $id);
    $this->db->where('trailers.deleted_at', NULL);
    $query = $this->db->get();
    return $query->row_array();
  }
  public function create_trailers($data)
  {
    if ($this->is_unique_trailers_number($data['trailer_number'])) {
      return $this->db->insert('trailers', $data);
    } else {
      return false;
    }
  }

  public function update_trailers($id, $data)
  {
    $trailers = $this->get_traile($id);
    if ($trailers['trailer_number'] === $data['trailer_number'] || $this->is_unique_trailers_number($data['trailer_number'])) {
      $this->db->where('id', $id);
      return $this->db->update('trailers', $data);
    } else {
      return FALSE;
    }
  }

  public function delete_trailers($id)
  {
    $data = array(
      'deleted_at' => date('Y-m-d H:i:s')
    );
    $this->db->where('id', $id);
    return $this->db->update('trailers', $data);
  }
  public function get_trailer_by_number($trailers_number, $id)
  {
    $this->db->where('truck_id', $id);
    $this->db->where('trailer_number', $trailers_number);
    $query = $this->db->get('truck');
    return $query->row();
  }
  public function is_unique_trailers_number($trailers_number)
  {
    $this->db->where('trailer_number', $trailers_number);
    $query = $this->db->get('trailers');
    return $query->num_rows() == 0;
  }
}
;
