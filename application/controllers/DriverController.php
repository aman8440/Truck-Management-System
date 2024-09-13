<?php
defined('BASEPATH') or exit('No direct script access allowed');

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");

class DriverController extends CI_Controller
{

  public function __construct()
  {
    parent::__construct();
    $this->load->model('driverModel');
    $this->load->model('dispatcherModel');
    $this->load->model('truckModel');
    $this->load->model('trailerModel');
    $this->load->helper('jwt');
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
  public function index()
  {
    $this->verify_token();
    $search = $this->input->get('search');
    $sort = $this->input->get('sort');
    $order = $this->input->get('order');
    $page = $this->input->get('page');
    $limit = $this->input->get('limit');
    $drivers = $this->driverModel->get_drivers($search, $sort, $order, $page, $limit);
    $this->output
      ->set_content_type('application/json')
      ->set_status_header(200)
      ->set_output(json_encode(['data' => $drivers]));
  }
  public function get_data()
  {
    $this->verify_token();
    $drivers = $this->driverModel->get_driver();
    $this->output
      ->set_content_type('application/json')
      ->set_status_header(200)
      ->set_output(json_encode($drivers));
  }

  public function view($id)
  {
    $this->verify_token();
    $drivers = $this->driverModel->get_driver($id);
    if (empty($drivers)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(404)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Driver not found']));
      return;
    }
    $this->output
      ->set_content_type('application/json')
      ->set_status_header(200)
      ->set_output(json_encode($drivers));
  }

  public function create()
  {
    $this->verify_token();
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['dri_name']) || empty($data['dri_email']) || empty($data['dri_phone']) || empty($data['license_number']) || empty($data['license_expiry_date'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'All fields are required']));
      return;
    }
    if (!preg_match("/^[a-zA-Z-' ]*$/", $data['dri_name'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Only letters and white space allowed']));
      return;
    }
    if (!preg_match("/\S+@\S+\.\S+/", $data['dri_email'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Please provide a valid email address']));
      return;
    }
    if (!preg_match("/^[0-9]{10}$/", $data['dri_phone'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Please provide a valid phone number']));
      return;
    }
    if (!preg_match("/^(([A-Z]{2}[0-9]{2})( )|([A-Z]{2}-[0-9]{2}))((19|20)[0-9]{2})[0-9]{7}$/", $data['license_number'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Please provide a valid license number']));
      return;
    }
    $admin_name = $this->session->userdata('admin_name');
    if (empty($admin_name)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(403)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Admin not logged in']));
      return;
    }

    $dispatcher = $this->dispatcherModel->get_dispatcher_by_name($data['dis_name']);
    if (empty($dispatcher)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(404)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Dispatcher not found']));
      return;
    }

    $driver_data = [
      'dri_name' => $data['dri_name'],
      'dri_email' => $data['dri_email'],
      'dri_phone' => $data['dri_phone'],
      'license_number' => $data['license_number'],
      'license_expiry_date' => $data['license_expiry_date'],
      'dispatchers_id' => $dispatcher->id,
      'created_by' => $admin_name,
      'updated_by' => $admin_name,
      'status' => isset($data['status']) ? $data['status'] : 'Available'
    ];

    if ($this->driverModel->create_drivers($driver_data)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(201)
        ->set_output(json_encode(['status' => 'success', 'message' => 'Driver created successfully', 'data' => [$driver_data]]));
    } else {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(409)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Driver name, Driver email, phone number, or license number must be unique', 'data' => []]));
    }
  }

  public function update($id)
  {
    $this->verify_token();
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['dri_name']) || empty($data['dri_email']) || empty($data['dri_phone']) || empty($data['license_number']) || empty($data['license_expiry_date']) || empty($data['dis_name'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'All fields are required']));
      return;
    }
    if (!preg_match("/\S+@\S+\.\S+/", $data['dri_email'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Please provide a valid email address']));
      return;
    }
    if (!preg_match("/^[a-zA-Z-' ]*$/", $data['dri_name'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Only letters and white space allowed']));
      return;
    }
    if (!preg_match("/^[0-9]{10}$/", $data['dri_phone'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Please provide a valid phone number']));
      return;
    }
    if (!preg_match("/^(([A-Z]{2}[0-9]{2})( )|([A-Z]{2}-[0-9]{2}))((19|20)[0-9]{2})[0-9]{7}$/", $data['license_number'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Please provide a valid license number']));
      return;
    }
    $admin_name = $this->session->userdata('admin_name');
    if (empty($admin_name)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(403)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Admin not logged in']));
      return;
    }

    $dispatcher = $this->dispatcherModel->get_dispatcher_by_name($data['dis_name']);
    if (empty($dispatcher)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(404)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Dispatcher not found']));
      return;
    }
    $driver_data = [
      'dri_name' => $data['dri_name'],
      'dri_email' => $data['dri_email'],
      'dri_phone' => $data['dri_phone'],
      'license_number' => $data['license_number'],
      'license_expiry_date' => $data['license_expiry_date'],
      'dispatchers_id' => $dispatcher->id,
      'updated_by' => $admin_name,
      'status' => isset($data['status']) ? $data['status'] : 'Available'
    ];

    if ($this->driverModel->update_drivers($id, $driver_data)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(201)
        ->set_output(json_encode(['status' => 'success', 'message' => 'Your Data Updated Sucessfully', 'data' => [$driver_data]]));
    } else {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(409)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Driver name, Driver Email, Phone Number, License Number must be unique', 'data' => []]));
    }
  }

  public function delete($id)
  {
    $this->verify_token();
    if ($this->driverModel->delete_drivers($id)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(json_encode(['status' => 'success', 'message' => 'Your Data Deleted Sucessfully']));
    } else {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(404)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Driver not found']));
    }
  }
  public function checkUniqueEmail()
  {
    $this->verify_token();
    $data = json_decode(file_get_contents('php://input'), true);
    $is_unique = $this->driverModel->is_unique_drivers_email($data['dri_email']);
    if ($is_unique) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(json_encode(['status' => 'success', 'message' => 'Email are unique', 'email' => $data['dri_email']]));
    } else {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(json_encode(['status' => 'emailError', 'message' => 'Email already exist', 'email' => $data['dri_email']]));
    }
  }
  public function checkUniqueName()
  {
    $this->verify_token();
    $data = json_decode(file_get_contents('php://input'), true);
    $is_unique = $this->driverModel->is_unique_drivers_name($data['dri_name']);
    if ($is_unique) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(json_encode(['status' => 'success', 'message' => 'Name are unique', 'name' => $data['dri_name']]));
    } else {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(json_encode(['status' => 'nameError', 'message' => 'Name already exist', 'name' => $data['dri_name']]));
    }
  }
  public function checkUniquePhone()
  {
    $this->verify_token();
    $data = json_decode(file_get_contents('php://input'), true);
    $is_unique = $this->driverModel->is_unique_drivers_phone($data['dri_phone']);
    if ($is_unique) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(json_encode(['status' => 'success', 'message' => 'Phone Number are unique', 'phone' => $data['dri_phone']]));
    } else {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(json_encode(['status' => 'phoneError', 'message' => 'Phone Number already exist', 'phone' => $data['dri_phone']]));
    }
  }
  public function checkUniqueLicense()
  {
    $this->verify_token();
    $data = json_decode(file_get_contents('php://input'), true);
    $is_unique = $this->driverModel->is_unique_drivers_license($data['license_number']);
    if ($is_unique) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(json_encode(['status' => 'success', 'message' => 'License Number are unique', 'phone' => $data['license_number']]));
    } else {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(json_encode(['status' => 'phoneError', 'message' => 'License Number already exist', 'license' => $data['license_number']]));
    }
  }
}
