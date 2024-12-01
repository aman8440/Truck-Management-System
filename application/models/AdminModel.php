<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AdminModel extends CI_Model
{
  public function get_admin($id)
  {
    $this->db->where('id', $id);
    $query = $this->db->get('Users');
    return $query->row_array();
  }
  public function create_login($data)
  {
    $this->db->where('email', $data['email']);
    $query = $this->db->get('Users');

    if ($query->num_rows() == 1) {
      $user = $query->row();
      if (password_verify($data['password'], $user->password)) {
        return $user;
      } else {
        return false;
      }
    } else {
      return false;
    }
  }
  public function insert_data($postdata)
  {
    if($this->is_unique_user_email($postdata['email']) && $this->is_unique_user_phone($postdata['phone'])){
      $hash_pass = password_hash($postdata['password'], PASSWORD_DEFAULT);
      $post['name'] = $postdata['name'];
      $post['phone'] = $postdata['phone'];
      $post['email'] = $postdata['email'];
      $post['gender'] = $postdata['gender'];
      $post['password'] = $hash_pass;
  
      $q = $this->db->insert('Users', $post);
      if ($q) {
        return true;
      } else {
        log_message('error', 'Database update error: ' . $this->db->last_query());
        return false;
      }
    }
    else{
      if (!$this->is_unique_user_email($postdata['email'])) {
        log_message('info', 'Duplicate email: ' . $postdata['email']);
      }
      if (!$this->is_unique_user_phone($postdata['phone'])) {
          log_message('info', 'Duplicate phone number: ' . $postdata['phone']);
      }
      return false;
    }
  }
  public function get_admin_by_email($email)
  {
    return $this->db->get_where('Users', ['email' => $email])->row();
  }

  public function set_password_reset_token($admin_id, $token)
  {
    $data = [
      'reset_token' => $token,
      'reset_token_created_at' => date('Y-m-d H:i:s')
    ];
    $this->db->update('Users', $data, ['id' => $admin_id]);
  }

  public function get_admin_by_reset_token($token)
  {
    $this->db->where('reset_token', $token);
    $this->db->where('reset_token_created_at >=', date('Y-m-d H:i:s', strtotime('-5 minutes')));
    return $this->db->get('Users')->row();
  }

  public function update_password($admin_id, $new_password)
  {
    $this->db->update('Users', ['password' => $new_password], ['id' => $admin_id]);
  }

  public function clear_password_reset_token($admin_id)
  {
    $data = [
      'reset_token' => null,
      'reset_token_created_at' => null
    ];
    $this->db->update('Users', $data, ['id' => $admin_id]);
  }

  public function is_unique_user_email($email)
  {
    $this->db->where('email', $email);
    $query = $this->db->get('Users');
    return $query->num_rows() == 0;
  }
  public function is_unique_user_phone($phone)
  {
    $this->db->where('phone', $phone);
    $query = $this->db->get('Users');
    return $query->num_rows() == 0;
  }

  public function prf_data($id, $image_name)
  {
    $this->db->where('id', $id);
    $query = $this->db->get('Users');

    if ($query->num_rows() > 0) {
        $this->db->where('id', $id);
        return $this->db->update('Users', ['image_name' => $image_name]);
    } else {
        return $this->db->insert('Users', ['id' => $id, 'image_name' => $image_name]);
    }
  }
  public function get_image_name($id)
  {
    $this->db->select('image_name');
    $this->db->from('Users');
    $this->db->where('id', $id);
    $query = $this->db->get();

    if ($query->num_rows() > 0) {
      return $query->row_array()['image_name'];
    }
    return null;
  }

  public function delete_image($id)
  {
    $this->db->set('image_name', NULL);
    $this->db->where('id', $id);
    return $this->db->update('Users');
  }
};