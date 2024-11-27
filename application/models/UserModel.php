<?php
defined('BASEPATH') or exit('No direct script access allowed');
class UserModel extends CI_Model
{

  public function get_users($search = '', $sort = 'project_name', $order = 'asc', $page = 1, $limit = 5)
  {
    $offset = ($page - 1) * $limit;
    $this->db->where('deleted_at', NULL);
    if (!empty($search)) {
      $this->db->group_start();
      $this->db->like('project_name', $search);
      $this->db->or_like('project_tech', $search);
      $this->db->or_like('project_startat', $search);
      $this->db->or_like('project_deadline', $search);
      $this->db->or_like('project_lead', $search);
      $this->db->or_like('team_size', $search);
      $this->db->or_like('project_client', $search);
      $this->db->or_like('project_management_tool', $search);
      $this->db->or_like('project_management_url', $search);
      $this->db->or_like('project_description', $search);
      $this->db->or_like('project_repo_tool', $search);
      $this->db->or_like('project_repo_url', $search);
      $this->db->or_like('project_status', $search);
      $this->db->group_end();
    }
    $this->db->order_by($sort, $order);
    $this->db->limit($limit, $offset);

    $query = $this->db->get('project_management');
    $result['data'] = $query->result();

    if (!empty($search)) {
      $this->db->group_start();
      $this->db->like('project_name', $search);
      $this->db->or_like('project_tech', $search);
      $this->db->or_like('project_startat', $search);
      $this->db->or_like('project_deadline', $search);
      $this->db->or_like('project_lead', $search);
      $this->db->or_like('team_size', $search);
      $this->db->or_like('project_client', $search);
      $this->db->or_like('project_management_tool', $search);
      $this->db->or_like('project_management_url', $search);
      $this->db->or_like('project_description', $search);
      $this->db->or_like('project_repo_tool', $search);
      $this->db->or_like('project_repo_url', $search);
      $this->db->or_like('project_status', $search);
      $this->db->group_end();
    }
    $result['total'] = $this->db->count_all_results('project_management');

    return $result;
  }
  public function get_dispatche($id = FALSE)
  {
    if ($id === FALSE) {
      $this->db->where('deleted_at', NULL);
      $query = $this->db->get('project_management');
      return $query->result_array();
    }

    $this->db->where('id', $id);
    $this->db->where('deleted_at', NULL);
    $query = $this->db->get('project_management');
    return $query->row_array();
  }
  public function create_listUser($data, $admin_name)
  {
    if (is_array($data['project_tech'])) {
      $data['project_tech'] = json_encode($data['project_tech']);
    }
    $data['created_by'] = $admin_name;
    $data['updated_by'] = $admin_name;
    return $this->db->insert('project_management', $data);
  }

  public function update_listUser($id, $data, $admin_name)
  {
    $data['updated_by'] = $admin_name;
    $this->db->where('id', $id);
    return $this->db->update('project_management', $data);
  }

  public function delete_listUser($id)
  {
    $data = array(
      'deleted_at' => date('Y-m-d H:i:s')
    );
    $this->db->where('id', $id);
    return $this->db->update('project_management', $data);
  }
  public function get_listUser_by_name($name)
  {
    $query = $this->db->get_where('project_management', ['project_name' => $name]);
    return $query->row();
  }
}
;
