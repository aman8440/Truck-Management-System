<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AdminController extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->library('email');
    $this->load->library('form_validation');
    $this->load->model('adminModel');
    $this->load->helper('jwt');
    $this->set_headers();
  }
  private function set_headers()
  {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding, Authorization");
  }

  public function verify_token()
  {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['token'])) {
      $this->output
           ->set_status_header(400)
           ->set_content_type('application/json')
           ->set_output(json_encode(array(
             'status' => false,
             'message' => 'Token is missing'
           )));
      return;
    }
    $token = str_replace('Bearer ', '', $input['token']);
    $decoded_token = validateToken($token);
    if ($decoded_token === null) {
      $this->output
           ->set_status_header(401)
           ->set_content_type('application/json')
           ->set_output(json_encode(array(
             'status' => false,
             'message' => 'Invalid token'
           )));
      return;
    }
    $this->output
         ->set_content_type('application/json')
         ->set_status_header(200)
         ->set_output(json_encode(array(
           'status' => true,
           'message' => 'Token is valid',
           'data' => $decoded_token
         )));
  }
  public function view()
  {
    $authHeader = $this->input->get_request_header('Authorization', true);
    if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $this->output
            ->set_content_type('application/json')
            ->set_status_header(401)
            ->set_output(json_encode(['status' => 'error', 'message' => 'Authorization token is missing or invalid']));
        return;
    }

    $token = $matches[1];
    try {
      $decodedToken = verifyToken($token);
    } catch (Exception $e) {
        $this->output
            ->set_content_type('application/json')
            ->set_status_header(401)
            ->set_output(json_encode(['status' => 'error', 'message' => 'Invalid or expired token']));
        return;
    }
    if (empty($decodedToken->data->id)) {
        $this->output
            ->set_content_type('application/json')
            ->set_status_header(400)
            ->set_output(json_encode(['status' => 'error', 'message' => 'ID is missing in token payload']));
        return;
    }

    $id = $decodedToken->data->id;
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
        ->set_output(json_encode(['status' => 'success', 'admin' => $admin]));
  }
  public function login()
  {
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['email']) || empty($data['password'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Email and Password are required']));
      return;
    }
    $check = $this->adminModel->create_login($data);
    if ($check) {
      $this->session->set_userdata('id', $check->id);
      $this->session->set_userdata('fname', $check->fname);
      $token = generateToken(['id' => $check->id]);
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(json_encode(['status' => 'success', 'message' => 'Login Sucessfully', 'token' => $token]));
    } else {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(401)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Invalid email or password']));
    }
  }

  public function create() {
    $this->form_validation->set_rules($this->config->item('signup'));
    if ($this->form_validation->run() == FALSE) {
      $response = [
          'status' => 'error',
          'message' => validation_errors()
      ];
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode($response));
    } else {
      $postdata = [
        'name' => $this->input->post('name', TRUE),
        'phone' => $this->input->post('phone', TRUE),
        'email' => $this->input->post('email', TRUE),
        'gender' => $this->input->post('gender', TRUE),
        'password' => $this->input->post('password', TRUE)
      ];
      if ($this->adminModel->insert_data($postdata)) {
        $response = [
          'status' => 'success',
          'message' => 'User created successfully'
        ];
        $this->output
          ->set_content_type('application/json')
          ->set_status_header(201)
          ->set_output(json_encode($response));
      } else {
        $response = [
          'status' => 'error',
          'message' => 'Failed to create user. Email or phone may already be registered.'
        ];
        $this->output
          ->set_content_type('application/json')
          ->set_status_header(409)
          ->set_output(json_encode($response));
      }
    }
  }

  public function forgot_password()
  {
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['email'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Email is required']));
      return;
    }

    $email = $data['email'];
    $admin = $this->adminModel->get_admin_by_email($email);

    if (!$admin) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(404)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Email not found']));
      return;
    }

    $reset_token = bin2hex(random_bytes(32));
    $this->adminModel->set_password_reset_token($admin->id, $reset_token);

    $this->email->from('no-reply@example.com', 'Trucking App');
    $this->email->to($email);
    $this->email->subject('Password Reset');
    $message = 'Click the following link to reset your password: ';
    // $message .= base_url('frontend/#!/reset-password/' . $reset_token);
    $message .= 'http://localhost:5173/reset-password/' .$reset_token;
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
  public function verify_reset_token()
{
    $reset_token = $this->input->get('reset_token');
    if (empty($reset_token)) {
        $this->output
            ->set_content_type('application/json')
            ->set_status_header(400)
            ->set_output(json_encode(['status' => 'error', 'message' => 'Token is required']));
        return;
    }
    $admin = $this->adminModel->get_admin_by_reset_token($reset_token);

    if (!$admin) {
        $this->output
            ->set_content_type('application/json')
            ->set_status_header(400)
            ->set_output(json_encode(['status' => 'error', 'message' => 'Invalid or expired token']));
        return;
    }

    $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(json_encode(['status' => 'success', 'message' => 'Token is valid']));
  }
  public function reset_password()
  {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['reset_token']) || empty($data['password'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Token and new password are required']));
      return;
    }

    $reset_token = $data['reset_token'];
    $new_password = password_hash($data['password'], PASSWORD_DEFAULT);
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
      ->set_status_header(201)
      ->set_output(json_encode(['status' => 'success', 'message' => 'Password reset successfully']));
  }
  public function logout()
  {
    if ($this->session->userdata('fname') && $this->session->userdata('lname')) {
        $this->session->unset_userdata('fname');
        $this->session->unset_userdata('lname');
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

  public function api_upload_image()
  {
    $user_id = $this->input->post('id');
    if (empty($user_id)) {
        $this->output
            ->set_content_type('application/json')
            ->set_status_header(400)
            ->set_output(json_encode(['status' => 'error', 'message' => 'User ID is required.']));
        return;
    }

    $user_id = (int)$user_id;
    $config['upload_path'] = 'assets/images/uploads';
    $config['allowed_types'] = 'jpg|png|jpeg|webp';
    $config['max_size'] = 500; 
    $this->load->library('upload', $config);

    if (!$this->upload->do_upload('file')) {
        $error = $this->upload->display_errors('', '');
        echo json_encode(['status' => 'error', 'message' => $error]);
        return;
    }

    $upload_data = $this->upload->data();
    $unique_id = uniqid();
    $extension = pathinfo($upload_data['file_name'], PATHINFO_EXTENSION);
    $unique_image_name = $unique_id . '.' . $extension;

    $new_file_path = $config['upload_path'] . '/' . $unique_image_name;
    rename($upload_data['full_path'], $new_file_path);

    $check = $this->adminModel->prf_data($user_id, $unique_image_name);

    if ($check) {
        echo json_encode(['status' => 'success', 'message' => 'Image uploaded successfully.', 'image_name' => $unique_image_name]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save image data. Please try again.']);
    } 
  }

  public function api_delete_image()
  {
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['id'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'User ID is required.']));
      return;
    }
    $user_id= (int)$data['id'];
    $image_name = $this->adminModel->get_image_name($user_id);

    if (!$image_name) {
      echo json_encode(['status' => 'error', 'message' => 'No image found for the provided user ID.']);
      return;
    }
    $delete = $this->adminModel->delete_image($user_id);

    if ($delete) {
        $file_path = 'assets/images/uploads' . $image_name;
        if (file_exists($file_path)) {
          unlink($file_path);
        }
        echo json_encode(['status' => 'success', 'message' => 'Image deleted successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete image. Please try again.']);
    }
  }
};