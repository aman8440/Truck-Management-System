<?php
defined('BASEPATH') or exit('No direct script access allowed');

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");

class TruckController extends CI_Controller
{

  public function __construct()
  {
    parent::__construct();
    $this->load->model('truckModel');
    $this->load->model('driverModel');
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
    $trucks = $this->truckModel->get_trucks($search, $sort, $order, $page, $limit);
    $this->output
      ->set_content_type('application/json')
      ->set_status_header(200)
      ->set_output(json_encode($trucks));
  }
  public function get_data()
  {
    $this->verify_token();
    $trucks = $this->truckModel->get_truc();
    $this->output
      ->set_content_type('application/json')
      ->set_status_header(200)
      ->set_output(json_encode($trucks));
  }

  public function view($id)
  {
    $this->verify_token();
    $truck = $this->truckModel->get_truc($id);
    if (empty($truck)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(404)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Truck not found']));
      return;
    }
    $this->output
      ->set_content_type('application/json')
      ->set_status_header(200)
      ->set_output(json_encode($truck));
  }

  public function create()
  {
    $this->verify_token();
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['truck_number']) || empty($data['model']) || empty($data['capacity']) || empty($data['registration_date']) || empty($data['status'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'All fields are required']));
      return;
    }
    if (!preg_match("/^[A-Z0-9-]*$/", $data['truck_number'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Invalid truck number format']));
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

    $driver = $this->driverModel->get_drivers_by_name($data['dri_name']);
    if (empty($driver)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(404)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Driver not found']));
      return;
    }

    $truck_data = [
      'truck_number' => $data['truck_number'],
      'model' => $data['model'],
      'capacity' => $data['capacity'],
      'truck_milege' => $data['truck_milege'],
      'registration_date' => $data['registration_date'],
      'status' => isset($data['status']) ? $data['status'] : 'Available',
      'drivers_id' => $driver->id,
      'created_by' => $admin_name,
      'updated_by' => $admin_name,
    ];

    if ($this->truckModel->create_trucks($truck_data)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(201)
        ->set_output(json_encode(['status' => 'success', 'message' => 'Your Data Inserted Sucessfully', 'data' => [$truck_data]]));
    } else {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(409)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Truck Number must be unique', 'data' => [$truck_data]]));
    }
  }

  public function update($id)
  {
    $this->verify_token();
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['truck_number']) || empty($data['model']) || empty($data['capacity']) || empty($data['registration_date']) || empty($data['truck_milege'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'All fields are required']));
      return;
    }
    if (!preg_match("/^[A-Z0-9-]*$/", $data['truck_number'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Invalid truck number format']));
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

    $driver = $this->driverModel->get_drivers_by_name($data['dri_name']);
    if (empty($driver)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(404)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Driver not found']));
      return;
    }

    $truck_data = [
      'truck_number' => $data['truck_number'],
      'model' => $data['model'],
      'capacity' => $data['capacity'],
      'truck_milege' => $data['truck_milege'],
      'registration_date' => $data['registration_date'],
      'status' => isset($data['status']) ? $data['status'] : 'Available',
      'drivers_id' => $driver->id,
      'updated_by' => $admin_name,
    ];
    if ($this->truckModel->update_trucks($id, $truck_data)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(201)
        ->set_output(json_encode(['status' => 'success', 'message' => 'Your Data Updated Sucessfully', 'data' => [$truck_data]]));
    } else {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(409)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Truck Number must be unique', 'data' => [$truck_data]]));
    }
  }

  public function delete($id)
  {
    $this->verify_token();
    if ($this->truckModel->delete_trucks($id)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(json_encode(['status' => 'success', 'message' => 'Your Data Deleted Sucessfully']));
    } else {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(404)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Trucks not found']));
    }
  }

  public function checkUniqueTruck()
  {
    $this->verify_token();
    $data = json_decode(file_get_contents('php://input'), true);
    $is_unique = $this->truckModel->is_unique_truck_number($data['truck_number']);
    if ($is_unique) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(json_encode(['status' => 'success', 'message' => 'Truck Number are unique', 'truck' => $data['truck_number']]));
    } else {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(json_encode(['status' => 'truckError', 'message' => 'Truck Number already exist', 'truck' => $data['truck_number']]));
    }
  }
}
;
