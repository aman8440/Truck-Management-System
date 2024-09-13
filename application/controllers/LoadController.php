<?php
defined('BASEPATH') or exit('No direct script access allowed');

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");

class LoadController extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->model('loadModel');
    $this->load->model('dispatcherModel');
    $this->load->model('driverModel');
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
    $loads = $this->loadModel->get_loads();
    $this->output
      ->set_content_type('application/json')
      ->set_status_header(200)
      ->set_output(json_encode($loads));
  }

  public function view($id)
  {
    $this->verify_token();
    $loads = $this->loadModel->get_loads($id);
    if (empty($loads)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(404)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Loads not found']));
      return;
    }
    $this->output
      ->set_content_type('application/json')
      ->set_status_header(200)
      ->set_output(json_encode($loads));
  }

  public function create()
  {
    $this->verify_token();
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['dis_name']) || empty($data['dri_name']) || empty($data['truck_number']) || empty($data['trailer_number']) || empty($data['shipper_name']) || empty($data['shipper_address']) || empty($data['shipper_city']) || empty($data['shipper_state']) || empty($data['shipper_country']) || empty($data['shipper_zipcode']) || empty($data['pickup_date']) || empty($data['pickup_time']) || empty($data['consignee_name']) || empty($data['consignee_address']) || empty($data['consignee_city']) || empty($data['consignee_state']) || empty($data['consignee_country']) || empty($data['consignee_zipcode']) || empty($data['dropoff_date']) || empty($data['dropoff_time']) || empty($data['load_commodity']) || empty($data['load_quantity']) || empty($data['load_weight']) || empty($data['load_miles']) || empty($data['load_deadmiles']) || empty($data['amount']) || empty($data['work_order_ref_number']) || empty($data['driver_notes'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'All fields are required']));
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
    $this->check($data['shipper_name']);
    $this->check($data['shipper_city']);
    $this->check($data['shipper_state']);
    $this->check($data['shipper_country']);
    $this->check($data['consignee_name']);
    $this->check($data['consignee_city']);
    $this->check($data['consignee_state']);
    $this->check($data['consignee_country']);

    $dispatcher = $this->dispatcherModel->get_dispatcher_by_name($data['dis_name']);
    if (empty($dispatcher)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(404)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Dispatcher not found']));
      return;
    }
    $dis_dri= $this->dispatcherModel->get_dispatchers($dispatcher->id);

    $dis= $this->driverModel->get_drivers_by_disId($dis_dri);
    $driver = $this->driverModel->get_drivers_by_name_load($data['dri_name'], $dis);


    if (empty($driver)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(404)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Driver not found']));
      return;
    }
    $truck = $this->truckModel->get_truck_by_number_loads($data['truck_number'], $driver->id);
    if (empty($truck)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(404)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Truck not found']));
      return;
    }
    $trailer = $this->trailerModel->get_trailer_by_number($data['trailer_number'], $truck->id);
    if (empty($trailer)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(404)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Trailer not found']));
      return;
    }
    $load_data = [
      'dispatchers_id' => $dispatcher->id,
      'drivers_id' => $driver->id,
      'truck_id' => $truck->id,
      'trailers_id' => $trailer->id,
      'shipper_name' => $data['shipper_name'],
      'shipper_address' => $data['shipper_address'],
      'shipper_city' => $data['shipper_city'],
      'shipper_state' => $data['shipper_state'],
      'shipper_country' => $data['shipper_country'],
      'shipper_zipcode' => $data['shipper_zipcode'],
      'pickup_date' => $data['pickup_date'],
      'pickup_time' => $data['pickup_time'],
      'consignee_name' => $data['consignee_name'],
      'consignee_address' => $data['consignee_address'],
      'consignee_city' => $data['consignee_city'],
      'consignee_state' => $data['consignee_state'],
      'consignee_country' => $data['consignee_country'],
      'consignee_zipcode' => $data['consignee_zipcode'],
      'dropoff_date' => $data['dropoff_date'],
      'dropoff_time' => $data['dropoff_time'],
      'load_commodity' => $data['load_commodity'],
      'load_quantity' => $data['load_quantity'],
      'load_weight' => $data['load_weight'],
      'load_miles' => $data['load_miles'],
      'load_deadmiles' => $data['load_deadmiles'],
      'amount' => $data['amount'],
      'work_order_ref_number' => $data['work_order_ref_number'],
      'driver_notes' => $data['driver_notes'],
      'created_by' => $admin_name,
      'updated_by' => $admin_name
    ];

    if ($this->loadModel->create_loads($load_data)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(201)
        ->set_output(json_encode(['status' => 'success', 'message' => 'Your Data Inserted Sucessfully']));
    } else {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(409)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Origin address and destination address must be different']));
    }
  }

  public function update($id)
  {
    $this->verify_token();
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['dis_name']) || empty($data['dri_name']) || empty($data['truck_number']) || empty($data['trailer_number']) || empty($data['shipper_name']) || empty($data['shipper_address']) || empty($data['shipper_city']) || empty($data['shipper_state']) || empty($data['shipper_country']) || empty($data['shipper_zipcode']) || empty($data['pickup_date']) || empty($data['pickup_time']) || empty($data['consignee_name']) || empty($data['consignee_address']) || empty($data['consignee_city']) || empty($data['consignee_state']) || empty($data['consignee_country']) || empty($data['consignee_zipcode']) || empty($data['dropoff_date']) || empty($data['dropoff_time']) || empty($data['load_commodity']) || empty($data['load_quantity']) || empty($data['load_weight']) || empty($data['load_miles']) || empty($data['load_deadmiles']) || empty($data['amount']) || empty($data['work_order_ref_number']) || empty($data['driver_notes'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'All fields are required']));
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
    $this->check($data['shipper_name']);
    $this->check($data['shipper_city']);
    $this->check($data['shipper_state']);
    $this->check($data['shipper_country']);
    $this->check($data['consignee_name']);
    $this->check($data['consignee_city']);
    $this->check($data['consignee_state']);
    $this->check($data['consignee_country']);

    $dispatcher = $this->dispatcherModel->get_dispatcher_by_name($data['dis_name']);
    if (empty($dispatcher)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(404)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Dispatcher not found']));
      return;
    }
    $dis_dri= $this->dispatcherModel->get_dispatchers($dispatcher->id);

    $dis= $this->driverModel->get_drivers_by_disId($dis_dri);
    $driver = $this->driverModel->get_drivers_by_name_load($data['dri_name'], $dis);


    if (empty($driver)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(404)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Driver not found']));
      return;
    }
    $truck = $this->truckModel->get_truck_by_number_loads($data['truck_number'], $driver->id);
    if (empty($truck)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(404)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Truck not found']));
      return;
    }
    $trailer = $this->trailerModel->get_trailer_by_number($data['trailer_number'], $truck->id);
    if (empty($trailer)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(404)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Trailer not found']));
      return;
    }
    $load_data = [
      'dispatchers_id' => $dispatcher->id,
      'drivers_id' => $driver->id,
      'truck_id' => $truck->id,
      'trailers_id' => $trailer->id,
      'shipper_name' => $data['shipper_name'],
      'shipper_address' => $data['shipper_address'],
      'shipper_city' => $data['shipper_city'],
      'shipper_state' => $data['shipper_state'],
      'shipper_country' => $data['shipper_country'],
      'shipper_zipcode' => $data['shipper_zipcode'],
      'pickup_date' => $data['pickup_date'],
      'pickup_time' => $data['pickup_time'],
      'consignee_name' => $data['consignee_name'],
      'consignee_address' => $data['consignee_address'],
      'consignee_city' => $data['consignee_city'],
      'consignee_state' => $data['consignee_state'],
      'consignee_country' => $data['consignee_country'],
      'consignee_zipcode' => $data['consignee_zipcode'],
      'dropoff_date' => $data['dropoff_date'],
      'dropoff_time' => $data['dropoff_time'],
      'load_commodity' => $data['load_commodity'],
      'load_quantity' => $data['load_quantity'],
      'load_weight' => $data['load_weight'],
      'load_miles' => $data['load_miles'],
      'load_deadmiles' => $data['load_deadmiles'],
      'amount' => $data['amount'],
      'work_order_ref_number' => $data['work_order_ref_number'],
      'driver_notes' => $data['driver_notes'],
      'created_by' => $admin_name,
      'updated_by' => $admin_name
    ];
    if ($this->loadModel->update_loads($load_data)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(201)
        ->set_output(json_encode(['status' => 'success', 'message' => 'Your Data Updated Sucessfully']));
    } else {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(409)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Origin address and destination address must be different']));
    }
  }

  public function delete($id)
  {
    $this->verify_token();
    if ($this->loadModel->delete_loads($id)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(json_encode(['status' => 'success', 'message' => 'Your Data Deleted Sucessfully']));
    } else {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(404)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Loads not found']));
    }
  }
  private function check($data){
    if (!preg_match("/^[a-zA-Z-' ]*$/", $data)) {
       $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Only letters and white space allowed']));
      exit;
    }
  }
};
