<?php
defined('BASEPATH') or exit('No direct script access allowed');

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");

class TrailerController extends CI_Controller
{

  public function __construct()
  {
    parent::__construct();
    $this->load->model('trailerModel');
    $this->load->model('truckModel');
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
    $trailers = $this->trailerModel->get_trailers($search, $sort, $order, $page, $limit);
    $this->output
      ->set_content_type('application/json')
      ->set_status_header(200)
      ->set_output(json_encode($trailers));
  }
  public function get_data()
  {
    $this->verify_token();
    $trailers = $this->trailerModel->get_traile();
    $this->output
      ->set_content_type('application/json')
      ->set_status_header(200)
      ->set_output(json_encode($trailers));
  }

  public function view($id)
  {
    $this->verify_token();
    $trailers = $this->trailerModel->get_traile($id);
    if (empty($trailers)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(404)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Trailer not found']));
      return;
    }
    $this->output
      ->set_content_type('application/json')
      ->set_status_header(200)
      ->set_output(json_encode($trailers));
  }

  public function create()
  {
    $this->verify_token();
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['trailer_number']) || empty($data['model']) || empty($data['capacity']) || empty($data['registration_date']) || empty($data['trailer_type'])) {
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
        ->set_output(json_encode(['status' => 'error', 'message' => 'Invalid trailer number format']));
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

    $truck = $this->truckModel->get_truck_by_number($data['truck_number']);
    if (empty($truck)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(404)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Truck not found']));
      return;
    }
    $trailer_data = [
      'trailer_number' => $data['trailer_number'],
      'model' => $data['model'],
      'capacity' => $data['capacity'],
      'registration_date' => $data['registration_date'],
      'trailer_type' => $data['trailer_type'],
      'status' => isset($data['status']) ? $data['status'] : 'Available',
      'truck_id' => $truck->id,
      'created_by' => $admin_name,
      'updated_by' => $admin_name
    ];
    if ($this->trailerModel->create_trailers($trailer_data)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(201)
        ->set_output(json_encode(['status' => 'success', 'message' => 'Your Data Inserted Sucessfully', 'data' => [$trailer_data]]));
    } else {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(409)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Trailer Number must be unique', 'data' => [$trailer_data]]));
    }
  }

  public function update($id)
  {
    $this->verify_token();
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['trailer_number']) || empty($data['model']) || empty($data['capacity']) || empty($data['registration_date']) || empty($data['trailer_type'])) {
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
        ->set_output(json_encode(['status' => 'error', 'message' => 'Invalid trailer number format']));
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

    $truck = $this->truckModel->get_truck_by_number($data['truck_number']);
    if (empty($truck)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(404)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Truck not found']));
      return;
    }
    $trailer_data = [
      'trailer_number' => $data['trailer_number'],
      'model' => $data['model'],
      'capacity' => $data['capacity'],
      'registration_date' => $data['registration_date'],
      'trailer_type' => $data['trailer_type'],
      'status' => isset($data['status']) ? $data['status'] : 'Available',
      'truck_id' => $truck->id,
      'updated_by' => $admin_name
    ];
    if ($this->trailerModel->update_trailers($id, $trailer_data)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(201)
        ->set_output(json_encode(['status' => 'success', 'message' => 'Your Data Updated Sucessfully', 'data' => [$trailer_data]]));
    } else {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(409)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Trailer Number must be unique', 'data' => [$trailer_data]]));
    }
  }

  public function delete($id)
  {
    $this->verify_token();
    if($this->trailerModel->delete_trailers($id)){
      $this->output
      ->set_content_type('application/json')
      ->set_status_header(200)
      ->set_output(json_encode(['status' => 'success', 'message' => 'Your Data Deleted Sucessfully']));
    }
    else{ 
      $this->output
      ->set_content_type('application/json')
      ->set_status_header(404)
      ->set_output(json_encode(['status' => 'error', 'message' => 'Trailer not found']));
    }
  }
  public function checkUniqueTrailer()
  {
    $this->verify_token();
    $data = json_decode(file_get_contents('php://input'), true);
    $is_unique = $this->trailerModel->is_unique_trailers_number($data['trailer_number']);
    if ($is_unique) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(json_encode(['status' => 'success', 'message' => 'Trailer Number are unique', 'trailer' => $data['trailer_number']]));
    } else {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(json_encode(['status' => 'trailerError', 'message' => 'Trailer Number already exist', 'trailer' => $data['trailer_number']]));
    }
  }
}
