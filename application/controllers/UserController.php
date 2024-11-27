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
    $offset = ($page - 1) * $limit;
    $dispatchers = $this->userModel->get_users($search, $sort, $order, $page, $limit);
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

  public function create()
  {
    $this->verify_token();
    $data = json_decode(file_get_contents('php://input'), true);
    if (is_array($data['project_tech'])) {
      $data['project_tech'] = json_encode($data['project_tech']);
      if (json_last_error() !== JSON_ERROR_NONE) {
        $this->output
            ->set_content_type('application/json')
            ->set_status_header(400)
            ->set_output(json_encode(['status' => 'error', 'message' => 'Invalid JSON data for project_tech']));
        return;
    }
    } else {
      $this->output
          ->set_content_type('application/json')
          ->set_status_header(400)
          ->set_output(json_encode(['status' => 'error', 'message' => 'Project tech must be an array']));
      return;
    }
    $encoded_tech = json_encode($data['project_tech']);
    echo $encoded_tech;
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
