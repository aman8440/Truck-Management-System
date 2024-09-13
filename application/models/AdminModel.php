<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AdminModel extends CI_Model
{
  public function get_admin($id)
  {
    $this->db->where('id', $id);
    $query = $this->db->get('admin');
    return $query->row_array();
  }
  public function create_login($data)
  {
    $this->db->where('admin_email', $data['admin_email']);
    $query = $this->db->get('admin');

    if ($query->num_rows() == 1) {
      $user = $query->row();
      if (password_verify($data['admin_password'], $user->admin_password)) {
        return $user;
      } else {
        return false;
      }
    } else {
      return false;
    }
  }
  public function get_admin_by_email($email)
  {
    return $this->db->get_where('admin', ['admin_email' => $email])->row();
  }

  public function set_password_reset_token($admin_id, $token)
  {
    $data = [
      'reset_token' => $token,
      'reset_token_created_at' => date('Y-m-d H:i:s')
    ];
    $this->db->update('admin', $data, ['id' => $admin_id]);
  }

  public function get_admin_by_reset_token($token)
  {
    $this->db->where('reset_token', $token);
    $this->db->where('reset_token_created_at >=', date('Y-m-d H:i:s', strtotime('-5 minutes')));
    return $this->db->get('admin')->row();
  }

  public function update_password($admin_id, $new_password)
  {
    $this->db->update('admin', ['admin_password' => $new_password], ['id' => $admin_id]);
  }

  public function clear_password_reset_token($admin_id)
  {
    $data = [
      'reset_token' => null,
      'reset_token_created_at' => null
    ];
    $this->db->update('admin', $data, ['id' => $admin_id]);
  }
};