<?php
defined('BASEPATH') or exit('No direct script access allowed');

class DispatcherController extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->model('dispatcherModel');
    $this->load->model('driverModel');
    $this->load->helper('jwt');
    $this->set_headers();
  }

  private function set_headers()
  {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding, Authorization");
  }

  private function verify_token()
  {
    $headers = $this->input->request_headers();
    if (!isset($headers['Authorization'])) {
      $this->output->set_status_header(401);
      echo json_encode(array('status' => false, 'message' => 'Unauthorized access'));
      exit();
    }

    $token = str_replace('Bearer ', '', $headers['Authorization']);
    $decoded_token = validateToken($token);

    if ($decoded_token === null) {
      $this->output->set_status_header(401);
      echo json_encode(array('status' => false, 'message' => 'Unauthorized access'));
      exit();
    }

    return $decoded_token;
  }

  public function get_data()
  {
    $this->verify_token();
    $dispatchers = $this->dispatcherModel->get_dispatche();
    $this->output
      ->set_content_type('application/json')
      ->set_status_header(200)
      ->set_output(json_encode($dispatchers));
  }
  public function index()
  {
    $this->verify_token();
    $search = $this->input->get('search');
    $sort = $this->input->get('sort');
    $order = $this->input->get('order');
    $page = $this->input->get('page');
    $limit = $this->input->get('limit');
    $offset = ($page - 1) * $limit;
    $dispatchers = $this->dispatcherModel->get_dispatchers($search, $sort, $order, $page, $limit);
    $this->output
      ->set_content_type('application/json')
      ->set_status_header(200)
      ->set_output(json_encode(['data' => $dispatchers,'limit'=>$limit, 'offset'=>$offset]));
  }

  public function view($id)
  {
    $this->verify_token();
    $dispatchers = $this->dispatcherModel->get_dispatche($id);
    if (empty($dispatchers)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(404)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Dispatcher not found']));
      return;
    }
    $this->output
      ->set_content_type('application/json')
      ->set_status_header(200)
      ->set_output(json_encode($dispatchers));
  }

  public function create()
  {
    $this->verify_token();
    $data = json_decode(file_get_contents('php://input'), true);
    $admin_name = $this->session->userdata('admin_name');
    if (empty($admin_name)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(403)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Admin not logged in']));
      return;
    }
    if (empty($data['dis_name']) || empty($data['dis_email']) || empty($data['dis_phone'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Dispatcher Name, Email, and Phone Number are required']));
      return;
    }
    if (!preg_match("/^[a-zA-Z-' ]*$/", $data['dis_name'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Only letters and white space allowed']));
      return;
    }
    if (!preg_match("/\S+@\S+\.\S+/", $data['dis_email'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Please provide a valid email address']));
      return;
    }
    if (!preg_match("/^[0-9]{10}$/", $data['dis_phone'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Please provide a valid phone number']));
      return;
    }
    if ($this->dispatcherModel->create_dispatchers($data, $admin_name)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(json_encode(['status' => 'success', 'message' => 'Your Data Inserted Successfully']));
    } else {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(409)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Dispatcher Name, Dispatcher Email, Phone Number must be unique']));
    }
  }

  public function update($id)
  {
    $this->verify_token();
    $data = json_decode(file_get_contents('php://input'), true);
    $admin_name = $this->session->userdata('admin_name');
    if (empty($admin_name)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(403)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Admin not logged in']));
      return;
    }
    if (empty($data['dis_name']) || empty($data['dis_email']) || empty($data['dis_phone'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Dispatchers Name, Email, Phone Number are required']));
      return;
    }
    if (!preg_match("/^[a-zA-Z-' ]*$/", $data['dis_name'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Only letters and white space allowed']));
      return;
    }
    if (!preg_match("/\S+@\S+\.\S+/", $data['dis_email'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Please provide a valid email address']));
      return;
    }
    if (!preg_match("/^[0-9]{10}$/", $data['dis_phone'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Please provide a valid phone number']));
      return;
    }
    if ($this->dispatcherModel->update_dispatchers($id, $data, $admin_name)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(201)
        ->set_output(json_encode(['status' => 'success', 'message' => 'Your Data Updated Successfully']));
    } else {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(409)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Dispatcher Name, Dispatchers Email, Phone Number must be unique']));
    }
  }

  public function delete($id)
  {
    $this->verify_token();
    if ($this->dispatcherModel->delete_dispatchers($id)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(json_encode(['status' => 'success', 'message' => 'Your Data Deleted Successfully']));
    } else {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(404)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Dispatcher not found']));
    }
  }
  public function checkUniqueEmail()
  {
    $this->verify_token();
    $data = json_decode(file_get_contents('php://input'), true);
    $is_unique = $this->dispatcherModel->is_unique_dispatchers_email($data['dis_email']);
    if ($is_unique) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(json_encode(['status' => 'success', 'message' => 'Email are unique', 'email' => $data['dis_email']]));
    } else {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(json_encode(['status' => 'emailError', 'message' => 'Email already exist', 'email' => $data['dis_email']]));
    }
  }
  public function checkUniqueName()
  {
    $this->verify_token();
    $data = json_decode(file_get_contents('php://input'), true);
    $is_unique = $this->dispatcherModel->is_unique_dispatchers_name($data['dis_name']);
    if ($is_unique) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(json_encode(['status' => 'success', 'message' => 'Name are unique', 'name' => $data['dis_name']]));
    } else {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(json_encode(['status' => 'nameError', 'message' => 'Name already exist', 'name' => $data['dis_name']]));
    }
  }
  public function checkUniquePhone()
  {
    $this->verify_token();
    $data = json_decode(file_get_contents('php://input'), true);
    $is_unique = $this->dispatcherModel->is_unique_dispatchers_phone($data['dis_phone']);
    if ($is_unique) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(json_encode(['status' => 'success', 'message' => 'Phone Number are unique', 'phone' => $data['dis_phone']]));
    } else {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(json_encode(['status' => 'phoneError', 'message' => 'Phone Number already exist', 'phone' => $data['dis_phone']]));
    }
  }
}
;
