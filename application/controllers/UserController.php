<?php
defined('BASEPATH') or exit('No direct script access allowed');

class UserController extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->model('userModel');
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
    $headers = $this->input->request_headers();
    if (!isset($headers['Authorization'])) {
      $this->output->set_status_header(401);
      echo json_encode(array('status' => false, 'message' => 'Unauthorized access'));
      exit();
    }

    $token = str_replace('Bearer ', '', $headers['Authorization']);
    $decoded_token = validateToken($token);

    if ($decoded_token === 'expired') {
      $this->output->set_status_header(401);
      echo json_encode(array('status' => false, 'message' => 'Token has expired'));
      exit();
    } elseif ($decoded_token === null) {
      $this->output->set_status_header(401);
      echo json_encode(array('status' => false, 'message' => 'Unauthorized access'));
      exit();
    }
    return $decoded_token;
  }
  
  public function get_user_data(){
    $this->verify_token();
    $data = $this->userModel->get_data();
    if($data){
      $this->output
      ->set_content_type('application/json')
      ->set_status_header(200)
      ->set_output(json_encode(['data' => $data, 'message' => 'Data fetch successfully']));
    }
    else{
      $this->output
      ->set_content_type('application/json')
      ->set_status_header(404)
      ->set_output(json_encode(['status' => 'error', 'message' => 'Data Not Found']));
    }
  }
  public function get_user_status(){
    $this->verify_token();
    $data = $this->userModel->get_status_count();
    if($data){
      $this->output
      ->set_content_type('application/json')
      ->set_status_header(200)
      ->set_output(json_encode(['data' => $data, 'message' => 'Data fetch successfully']));
    }
    else{
      $this->output
      ->set_content_type('application/json')
      ->set_status_header(404)
      ->set_output(json_encode(['status' => 'error', 'message' => 'Data Not Found']));
    }
  }

  public function get_data()
  {
    $this->verify_token();
    $dispatchers = $this->userModel->get_dispatche();
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
    $startAt = $this->input->get('project_startat');
    $deadlineAt = $this->input->get('project_deadline');
    $status = $this->input->get('project_status');
    $tech = $this->input->get('project_tech');
    $offset = ($page - 1) * $limit;
    $dispatchers = $this->userModel->get_users($search, $sort, $order, $page, $limit, $startAt, $deadlineAt, $status, $tech);
    $this->output
      ->set_content_type('application/json')
      ->set_status_header(200)
      ->set_output(json_encode(['data' => $dispatchers,'limit'=>$limit, 'offset'=>$offset]));
  }

  public function view($id)
  {
    $this->verify_token();
    $dispatchers = $this->userModel->get_dispatche($id);
    if (empty($dispatchers)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(404)
        ->set_output(json_encode(['status' => 'error', 'message' => 'User not found']));
      return;
    }
    $this->output
      ->set_content_type('application/json')
      ->set_status_header(200)
      ->set_output(json_encode($dispatchers));
  }

  public function get_status(){
    $this->verify_token();
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['project_startat']) || empty($data['project_deadline'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Project start at and project deadline is required']));
      return;
    }
    $status_data= $this->userModel->get_status($data);
    $merged_project_tech = [];
    foreach ($status_data as $project) {
        $technologies = explode(', ', $project['project_status']);
        $merged_project_tech = array_merge($merged_project_tech, $technologies);
    }
    $merged_project_tech = array_unique($merged_project_tech);
    $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(json_encode(['status' => 'success', 'data' => $merged_project_tech]));
  }

  public function get_tech(){
    $this->verify_token();
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['project_status'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Project status is required']));
      return;
    }
    $project_tech= $this->userModel->get_tech($data);
    $merged_project_tech = [];
    foreach ($project_tech as $project) {
        $technologies = explode(', ', $project['project_tech']);
        $merged_project_tech = array_merge($merged_project_tech, $technologies);
    }
    $merged_project_tech = array_unique($merged_project_tech);
    $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(json_encode(['status' => 'success', 'data' => $merged_project_tech]));
  }

  public function create()
  {
    $this->verify_token();
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['project_name']) || empty($data['project_tech']) || empty($data['project_startat']) || empty($data['project_deadline']) || empty($data['project_client']) || empty($data['project_description'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'All fields are required']));
      return;
    }
    if (!preg_match("/^[a-zA-Z-' ]*$/", $data['project_name'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Only letters and white space allowed']));
      return;
    }
    if (is_array($data['project_tech'])) {
      $data['project_tech'] = implode(', ', $data['project_tech']);
    }
    if ($this->userModel->create_listUser($data, $data['created_by'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(201)
        ->set_output(json_encode(['status' => 'success', 'message' => 'Your Data Inserted Successfully']));
    } else {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(409)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Data must be in conflict']));
    }
  }

  public function update($id)
  {
    $this->verify_token();
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['project_name']) || empty($data['project_tech']) || empty($data['project_startat']) || empty($data['project_deadline']) || empty($data['project_client']) || empty($data['project_description'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'All fields are required']));
      return;
    }
    if (!preg_match("/^[a-zA-Z-' ]*$/", $data['project_name'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Only letters and white space allowed']));
      return;
    }
    if (is_array($data['project_tech'])) {
      $data['project_tech'] = implode(', ', $data['project_tech']);
    }
    if ($this->userModel->update_listUser($id, $data, $data['updated_by'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(201)
        ->set_output(json_encode(['status' => 'success', 'message' => 'Your Data Updated Successfully']));
    } else {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(409)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Data must be in conflict during update']));
    }
  }
  public function delete($id)
  {
    $this->verify_token();
    if ($this->userModel->delete_listUser($id)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(json_encode(['status' => 'success', 'message' => 'Your Data Deleted Successfully']));
    } else {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(404)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Project not found']));
    }
  }
}
;
