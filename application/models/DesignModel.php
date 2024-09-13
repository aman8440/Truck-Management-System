<?php
defined('BASEPATH') or exit('No direct script access allowed');

class DesignModel extends CI_Model
{
  public function insert_data($postdata)
  {
    $hash_pass = password_hash($postdata['password'], PASSWORD_DEFAULT);
    $post['username'] = $postdata['username'];
    $post['fname'] = $postdata['fname'];
    $post['lname'] = $postdata['lname'];
    $post['email'] = $postdata['email'];
    $post['semail'] = $postdata['semail'];
    $post['timelag'] = $postdata['timelag'];
    $post['register'] = $postdata['register'];
    $post['zeroknow'] = $postdata['zeroknow'];
    $post['phone'] = $postdata['phone'];
    $post['country'] = $postdata['country'];
    $post['is_admin'] = $postdata['is_admin'];
    $post['password'] = $hash_pass;

    $q = $this->db->insert('data_record', $post);
    if ($q) {
      return true;
    } else {
      log_message('error', 'Database update error: ' . $this->db->last_query());
      return false;
    }
  }
  public function updateData($postdata)
  {
    if (!isset($postdata['id'])) {
      return false;
    }
    $post = [
      'username' => $postdata['username'],
      'fname' => $postdata['fname'],
      'lname' => $postdata['lname'],
      'email' => $postdata['email'],
      'semail' => $postdata['semail'],
      'timelag' => $postdata['timelag'],
      'register' => $postdata['register'],
      'zeroknow' => $postdata['zeroknow'],
      'phone' => $postdata['phone'],
      'country' => $postdata['country'],
      'payment' => $postdata['payment']
    ];

    $this->db->where('id', $postdata['id']);
    $q = $this->db->update('data_record', $post);
    if ($q) {
      return true;
    } else {
      log_message('error', 'Database update error: ' . $this->db->last_query());
      return false;
    }
  }
  public function login($postdata)
  {
    $this->db->where('username', $postdata['username']);
    $query = $this->db->get('data_record');

    if ($query->num_rows() == 1) {
      $user = $query->row();
      if (password_verify($postdata['password'], $user->password)) {
        return $user;
      } else {
        return false;
      }
    } else {
      return false;
    }
  }
  public function check_unique_email($email)
  {
    $query = $this->db->query("SELECT * from data_record WHERE email='$email'");
    $email = $query->result();

    if (count($email) > 0) {
      return false;
    } else {
      return true;
    }
  }
  public function check_unique_semail($email)
  {
    $query = $this->db->query("SELECT * from data_record WHERE semail='$email'");
    $email = $query->result();

    if (count($email) > 0) {
      return false;
    } else {
      return true;
    }
  }

  public function check_unique_phone($phone)
  {
    $query = $this->db->query("SELECT * from data_record WHERE phone='$phone'");
    $phone = $query->result();

    if (count($phone) > 0) {
      return false;
    } else {
      return true;
    }
  }
  public function check_unique_username($username)
  {
    $query = $this->db->query("SELECT * from data_record WHERE username='$username'");
    $username = $query->result();

    if (count($username) > 0) {
      return false;
    } else {
      return true;
    }
  }
  public function all_data($id = '')
  {
    if ($id != '') {
      $q = $this->db->where('id', $id)->get('data_record');
      if ($q->num_rows()) {
        return $q->row();
      } else {
        return false;
      }
    } else {
      $q = $this->db->order_by('id', 'asc')->get('data_record');
      if ($q->num_rows()) {
        return $q->result();
      } else {
        return false;
      }
    }
  }
  public function get_username_by_session($user_id)
  {
    $this->db->select('username');
    $this->db->from('data_record');
    $this->db->where('id', $user_id);
    $query = $this->db->get();

    if ($query->num_rows() > 0) {
      return $query->row_array();
    } else {
      return null;
    }
  }

  public function deleteData($id)
  {
    $q = $this->db->where('id', $id)->delete('data_record');
    if ($q) {
      return true;
    } else {
      log_message('error', 'Database update error: ' . $this->db->last_query());
      return false;
    }
  }

  public function get_count($search = '')
  {
    if ($search) {
      $this->db->like('id', $search);
      $this->db->or_like('username', $search);
      $this->db->or_like('fname', $search);
      $this->db->or_like('lname', $search);
      $this->db->or_like('email', $search);
      $this->db->or_like('semail', $search);
      $this->db->or_like('timelag', $search);
      $this->db->or_like('register', $search);
      $this->db->or_like('zeroknow', $search);
      $this->db->or_like('phone', $search);
      $this->db->or_like('is_admin', $search);
    }
    return $this->db->count_all_results('data_record');
  }

  
  public function count_users($search = '')
  {
    $this->db->like('username', $search);
    $this->db->or_like('fname', $search);
    $this->db->or_like('lname', $search);
    $this->db->or_like('email', $search);
    $this->db->or_like('phone', $search);
    $this->db->or_like('country', $search);

    return $this->db->count_all_results('data_record');
  }
  public function fetch_users($limit, $offset, $search = '', $sort_by = 'username', $sort_order = 'asc')
  {
    $this->db->like('username', $search);
    $this->db->or_like('fname', $search);
    $this->db->or_like('lname', $search);
    $this->db->or_like('email', $search);
    $this->db->or_like('phone', $search);
    $this->db->or_like('country', $search);

    $this->db->order_by($sort_by, $sort_order);
    $query = $this->db->get('data_record', $limit, $offset);

    if ($query->num_rows() > 0) {
      return $query->result_array();
    }
    return false;
  }
  public function save_token($email, $token, $expiry_time)
  {
    $data = array(
      'email' => $email,
      'token' => $token,
      'expiry_time' => $expiry_time
    );
    $this->db->insert('password_resets', $data);
  }

  public function email_exists($email)
  {
    $this->db->where('email', $email);
    $query = $this->db->get('data_record');

    if ($query->num_rows() > 0) {
      return TRUE;
    } else {
      return FALSE;
    }
  }
  public function get_email_by_id($id) {
    $id = (int)$id;
    $this->db->where('id', $id);
    $query = $this->db->get('data_record');
    if ($query) {
      log_message('debug', 'Query executed successfully: ' . $this->db->last_query());
    } else {
      log_message('error', 'Query execution failed: ' . $this->db->last_query());
    }
    if ($query->num_rows() > 0) {
      return $query->row()->email;
    } else {
      return false;
    }
  }

  public function prf_data($email, $image_name)
  {
    $this->db->where('email', $email);
    $query = $this->db->get('image_user');

    if ($query->num_rows() > 0) {
      $this->db->where('email', $email);
      $result = $this->db->update('image_user', array('image' => $image_name));
    } else {
      $result = $this->db->insert('image_user', array('email' => $email, 'image' => $image_name));
    }
    return $result;
  }
  public function get_image_name($email) {
    $this->db->select('image');
    $this->db->from('image_user');
    $this->db->where('email', $email);
    $query = $this->db->get();
    if ($query->num_rows() > 0) {
      return $query->row()->image;
    }
    return false;
  }

  public function delete_image($email) {
    $this->db->where('email', $email);
    return $this->db->delete('image_user');
  }
};