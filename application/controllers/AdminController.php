<?php
defined('BASEPATH') or exit('No direct script access allowed');

// header("Access-Control-Allow-Origin: *");
// header('Access-Control-Allow-Credentials: true');
// header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE");
// header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
// header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");

class AdminController extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->library('email');
    $this->load->model('adminModel');
    $this->load->helper('jwt');
  }
  public function view($id)
  {
    $admin = $this->adminModel->get_admin($id);
    if (empty($admin)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(404)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Admin not found']));
      return;
    }
    $this->output
      ->set_content_type('application/json')
      ->set_status_header(200)
      ->set_output(json_encode($admin));
  }
  public function create()
  {
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['admin_email']) || empty($data['admin_password'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Email and Password are required']));
      return;
    }
    $check = $this->adminModel->create_login($data);
    if ($check) {
      $this->session->set_userdata('admin_id', $check->id);
      $this->session->set_userdata('admin_name', $check->admin_name);
      $this->session->set_userdata('admin_email', $check->admin_email);
      $token = generateToken(['admin_name' => $check->admin_name, 'admin_email' => $check->admin_email]);
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(201)
        ->set_output(json_encode(['status' => 'success', 'message' => 'Login Sucessfully', 'token' => $token, 'admin_name' => $check->admin_name]));
    } else {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(401)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Invalid email or password']));
    }
  }

  public function forgot_password()
  {
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['admin_email'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Email is required']));
      return;
    }

    $admin_email = $data['admin_email'];
    $admin = $this->adminModel->get_admin_by_email($admin_email);

    if (!$admin) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(404)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Email not found']));
      return;
    }

    $reset_token = bin2hex(random_bytes(50));
    $this->adminModel->set_password_reset_token($admin->id, $reset_token);

    $this->email->from('no-reply@example.com', 'Trucking App');
    $this->email->to($admin_email);
    $this->email->subject('Password Reset');
    $message = 'Click the following link to reset your password: ';
    $message .= base_url('frontend/#!/reset-password/' . $reset_token);
    $message .= ' This link will expire in 5 Minutes.';
    $this->email->message($message);

    if ($this->email->send()) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(json_encode(['status' => 'success', 'message' => 'Password reset token sent to your email']));
    } else {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(500)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Failed to send email']));
    }
  }
  public function reset_password()
  {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['reset_token']) || empty($data['new_password'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Token and new password are required']));
      return;
    }

    $reset_token = $data['reset_token'];
    $new_password = password_hash($data['new_password'], PASSWORD_DEFAULT);
    $admin = $this->adminModel->get_admin_by_reset_token($reset_token);

    if (!$admin) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Invalid or expired token']));
      return;
    }

    $this->adminModel->update_password($admin->id, $new_password);

    $this->adminModel->clear_password_reset_token($admin->id);

    $this->output
      ->set_content_type('application/json')
      ->set_status_header(200)
      ->set_output(json_encode(['status' => 'success', 'message' => 'Password reset successfully']));
  }
  public function logout()
  {
    if ($this->session->userdata('admin_name')) {
      $this->session->unset_userdata('admin_name');
      $this->session->sess_destroy();
      $this->output
        ->set_status_header(200)
        ->set_output(json_encode(['message' => 'Logout successful']));
    } else {
      $this->output
        ->set_status_header(401)
        ->set_output(json_encode(['message' => 'No active session found']));
    }
  }
};