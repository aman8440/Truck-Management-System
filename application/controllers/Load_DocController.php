<?php
defined('BASEPATH') or exit('No direct script access allowed');

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");

class Load_DocController extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->model('load_DocModel');
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
    $load_docs = $this->load_DocModel->get_load_docs();
    $this->output
      ->set_content_type('application/json')
      ->set_status_header(200)
      ->set_output(json_encode($load_docs));
  }

  public function view($id)
  {
    $this->verify_token();
    $load_doc = $this->load_DocModel->get_load_doc($id);
    if (empty($load_doc)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(404)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Load document not found']));
      return;
    }
    $this->output
      ->set_content_type('application/json')
      ->set_status_header(200)
      ->set_output(json_encode($load_doc));
  }

  public function create()
  {
    $this->verify_token();
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['doc_name']) || empty($data['loads_id'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Document name and Load ID are required']));
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

    $load_doc_data = [
      'doc_name' => $data['doc_name'],
      'loads_id' => $data['loads_id'],
      'created_by' => $admin_name,
      'updated_by' => $admin_name
    ];

    if ($this->load_DocModel->create_load_doc($load_doc_data)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(201)
        ->set_output(json_encode(['status' => 'success', 'message' => 'Load document created successfully']));
    } else {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(500)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Failed to create load document']));
    }
  }

  public function update($id)
  {
    $this->verify_token();
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['doc_name']) || empty($data['loads_id'])) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(400)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Document name and Load ID are required']));
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

    $load_doc_data = [
      'doc_name' => $data['doc_name'],
      'loads_id' => $data['loads_id'],
      'updated_by' => $admin_name
    ];

    if ($this->load_DocModel->update_load_doc($id, $load_doc_data)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(json_encode(['status' => 'success', 'message' => 'Load document updated successfully']));
    } else {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(500)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Failed to update load document']));
    }
  }

  public function delete($id)
  {
    $this->verify_token();
    if ($this->load_DocModel->delete_load_doc($id)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(json_encode(['status' => 'success', 'message' => 'Load document deleted successfully']));
    } else {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(404)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Load document not found']));
    }
  }
}
;
