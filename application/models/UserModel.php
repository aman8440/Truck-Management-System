<?php
defined('BASEPATH') or exit('No direct script access allowed');
class UserModel extends CI_Model
{

  public function get_users($search = '', $sort = 'id', $order = 'asc', $page = 1, $limit = 10, $startAt = '', $deadlineAt = '', $status = '', $tech = '')
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
    if (!empty($startAt)) {
        $this->db->where('project_startat >=', $startAt);
    }
    if (!empty($deadlineAt)) {
        $this->db->where('project_deadline <=', $deadlineAt);
    }
    if (!empty($status)) {
        $this->db->where('project_status', $status);
    }
    if (!empty($tech)) {
      $techList = explode(',', $tech);
      $this->db->group_start();
      foreach ($techList as $t) {
        $this->db->or_like('project_tech', $t);
      }
      $this->db->group_end(); 
    }

    $this->db->order_by($sort, $order);
    $this->db->limit($limit, $offset);
    $query = $this->db->get('project_management');
    $result['data'] = $query->result();
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
    if (!empty($startAt)) {
      $this->db->where('project_startat >=', $startAt);
    }
    if (!empty($deadlineAt)) {
      $this->db->where('project_deadline <=', $deadlineAt);
    }

    if (!empty($status)) {
      $this->db->where('project_status', $status);
    }

    if (!empty($tech)) {
      $techList = explode(',', $tech);
      $this->db->group_start();
      foreach ($techList as $t) {
        $this->db->or_like('project_tech', $t);
      }
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

  public function get_status($data){
    $start_date = $data['project_startat'];
    $end_date = $data['project_deadline'];

    $this->db->select('project_status');
    $this->db->from('project_management');
    $this->db->where('project_startat >=', $start_date);
    $this->db->where('project_deadline <=', $end_date);
    $query = $this->db->get();
    return $query->result_array();
  }

  public function get_tech($data){
    $status = $data['project_status'];

    $this->db->select('project_tech');
    $this->db->from('project_management');
    $this->db->where('project_status', $status);
    $query = $this->db->get();
    return $query->result_array();
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
