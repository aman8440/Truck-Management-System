<?php

use OpenApi\Annotations as OA;


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
  
  /**
   * @OA\Get(
   *     path="/api/project/getProjectCount",
   *     summary="Fetch Project count data",
   *     tags={"ProjectCount"},
   *     security={{"bearerAuth":{}}},
   *     @OA\Response(
   *         response=200,
   *         description="Success - Successful data retrieval",
   *         @OA\JsonContent(ref="#/components/schemas/GetProjectCountResponse")
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Not Found - Data not found",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Data Not Found")
   *         )
   *     ),
   *     @OA\Response(
   *         response=401,
   *         description="Unauthorized - Invalid or missing token",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Authorization token is missing or invalid")
   *         )
   *     ),
   *     @OA\Response(
   *         response=500,
   *         description="Failed -  Internal Server Error",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Internal Server Error")
   *         )
   *     )
   * )
   */
  /**
   * @OA\Schema(
   *     schema="GetProjectCountResponse",
   *     type="object",
   *     required={"data", "message"},
   *     @OA\Property(property="data", type="array", 
   *         @OA\Items(type="object", 
   *             required={
   *                "month_name",
   *                "project_count"
   *             },
   *             @OA\Property(property="month_name", type="string", example="December 2024"),
   *             @OA\Property(property="project_count", type="integer", example=5)
   *         )
   *     ),
   *     @OA\Property(property="message", type="string", example="Data fetch successfully")
   * )
   */
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

  /**
   * @OA\Get(
   *     path="/api/project/getStatusCount",
   *     summary="Fetch Project status data",
   *     tags={"ProjectStatus"},
   *     security={{"bearerAuth":{}}},
   *     @OA\Response(
   *         response=200,
   *         description="Success - Successful data retrieval",
   *         @OA\JsonContent(ref="#/components/schemas/GetProjectStatusResponse")
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Not Found - Data not found",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Data Not Found")
   *         )
   *     ),
   *     @OA\Response(
   *         response=401,
   *         description="Unauthorized - Invalid or missing token",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Authorization token is missing or invalid")
   *         )
   *     ),
   *     @OA\Response(
   *         response=500,
   *         description="Failed - Internal Server Error",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Internal Server Error")
   *         )
   *     )
   * )
   */
  /**
   * @OA\Schema(
   *     schema="GetProjectStatusResponse",
   *     type="object",
   *     required={"data", "message"}, 
   *     @OA\Property(property="data", type="array", 
   *         @OA\Items(type="object", 
   *             required={
   *                "project_status",
   *                "project_count"
   *             },
   *             @OA\Property(property="project_status", type="string", example="Under Planning"),
   *             @OA\Property(property="project_count", type="integer", example=5)
   *         )
   *     ),
   *     @OA\Property(property="message", type="string", example="Data fetch successfully")
   * )
   */
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

  /**
   * @OA\Get(
   *     path="/api/project",
   *     summary="Retrieve all Project data",
   *     tags={"Project Management"},
   *     @OA\Response(
   *         response=200,
   *         description="Success - Successful data retrieval",
   *         @OA\JsonContent(ref="#/components/schemas/GetProjectDataResponse")
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Not Found - No data found",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Data not found")
   *         )
   *     ),
   *     @OA\Response(
   *         response=401,
   *         description="Unauthorized - Invalid or missing token",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Authorization token is missing or invalid")
   *         )
   *     ),
   *     @OA\Response(
   *         response=500,
   *         description="Failed - Internal Server Error",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Internal Server Error")
   *         )
   *     )
   * )
   */
  /**
   * @OA\Schema(
   *     schema="GetProjectDataResponse",
   *     type="array",
   *     @OA\Items(
   *         type="object",
   *         required={
   *             "id",
   *             "project_name", 
   *             "project_tech", 
   *             "project_startat", 
   *             "project_deadline",
   *             "project_lead",
   *             "team_size",
   *             "project_client",
   *             "project_management_tool",
   *             "project_management_url",
   *             "project_description", 
   *             "project_repo_tool",
   *             "project_repo_url",
   *             "project_status",
   *             "created_by",
   *             "updated_by",
   *             "created_at",
   *             "updated_at"
   *         },
   *         @OA\Property(property="id", type="integer", example=1),
   *         @OA\Property(property="project_name", type="string", example="Project A"),
   *         @OA\Property(property="project_tech", type="string", example="PHP, MySQL, Angular"),
   *         @OA\Property(property="project_startat", type="string", format="date", example="2024-01-01"),
   *         @OA\Property(property="project_deadline", type="string", format="date", example="2024-12-31"),
   *         @OA\Property(property="project_lead", type="string", example="John Doe"),
   *         @OA\Property(property="team_size", type="integer", example=5),
   *         @OA\Property(property="project_client", type="string", example="ABC Corp"),
   *         @OA\Property(property="project_management_tool", type="string", example="Jira"),
   *         @OA\Property(property="project_management_url", type="string", example="https://jira.com/project-A"),
   *         @OA\Property(property="project_description", type="string", example="This project aims to..."),
   *         @OA\Property(property="project_repo_tool", type="string", example="GitHub"),
   *         @OA\Property(property="project_repo_url", type="string", example="https://github.com/ABC/project-A"),
   *         @OA\Property(property="project_status", type="string", enum={"Under Planning", "Development Started", "Under Testing", "Deployed on Dev", "Live"}, example="Under Planning"),
   *         @OA\Property(property="created_by", type="string", example="Admin"),
   *         @OA\Property(property="updated_by", type="string", example="Admin"),
   *         @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T12:00:00Z"),
   *         @OA\Property(property="updated_at", type="string", format="date-time", example="2024-06-01T12:00:00Z"),
   *         @OA\Property(property="deleted_at", type="string", format="date-time", example="2024-12-31T12:00:00Z")
   *     )
   * )
   */
  public function get_data()
  {
    $this->verify_token();
    $data = $this->userModel->get_dispatche();
    if($data){
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(json_encode($data));
    }
    else{
      $this->output
      ->set_content_type('application/json')
      ->set_status_header(404)
      ->set_output(json_encode(['status' => 'error', 'message' => 'Data Not Found']));
    }
  }

  /**
   * @OA\Get(
   *     path="/api/project/list",
   *     summary="Retrieve project data with filtering and pagination",
   *     tags={"Project Management"},
   *     @OA\Parameter(
   *         name="search",
   *         in="query",
   *         description="Search term for filtering the project",
   *         @OA\Schema(type="string")
   *     ),
   *     @OA\Parameter(
   *         name="sort",
   *         in="query",
   *         description="Sort the data by a specific field",
   *         @OA\Schema(type="string")
   *     ),
   *     @OA\Parameter(
   *         name="order",
   *         in="query",
   *         description="Sort order (asc or desc)",
   *         @OA\Schema(type="string", enum={"asc", "desc"})
   *     ),
   *     @OA\Parameter(
   *         name="page",
   *         in="query",
   *         description="Page number for pagination",
   *         @OA\Schema(type="integer", example=1)
   *     ),
   *     @OA\Parameter(
   *         name="limit",
   *         in="query",
   *         description="Number of results per page",
   *         @OA\Schema(type="integer", example=10)
   *     ),
   *     @OA\Parameter(
   *         name="project_startat",
   *         in="query",
   *         description="Filter by project start date",
   *         @OA\Schema(type="string", format="date")
   *     ),
   *     @OA\Parameter(
   *         name="project_deadline",
   *         in="query",
   *         description="Filter by project deadline",
   *         @OA\Schema(type="string", format="date")
   *     ),
   *     @OA\Parameter(
   *         name="project_status",
   *         in="query",
   *         description="Filter by project status",
   *         @OA\Schema(type="string", enum={"Under Planning", "Development Started", "Under Testing", "Deployed on Dev", "Live"})
   *     ),
   *     @OA\Parameter(
   *         name="project_tech",
   *         in="query",
   *         description="Filter by project technology",
   *         @OA\Schema(type="string")
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Success - Successful data retrieval with pagination",
   *         @OA\JsonContent(ref="#/components/schemas/GetProjectListDataResponse")
   *     ),
   *     @OA\Response(
   *         response=400,
   *         description="Bad Request - Invalid parameters",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Invalid parameters")
   *         )
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Not Found - No data found",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Data not found")
   *         )
   *     ),
   *     @OA\Response(
   *         response=401,
   *         description="Unauthorized - Invalid or missing token",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Authorization token is missing or invalid")
   *         )
   *     ),
   *     @OA\Response(
   *         response=500,
   *         description="Failed - Internal Server Error",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Internal Server Error")
   *         )
   *     )
   * )
   */
  /**
   * @OA\Schema(
   *     schema="GetProjectListDataResponse",
   *     type="object",
   *     @OA\Property(property="data", type="object",
   *         @OA\Property(property="projects", type="array", items=@OA\Items(type="object",
   *             required={
   *                  "id",
   *                  "project_name", 
   *                  "project_tech", 
   *                  "project_startat", 
   *                  "project_deadline",
   *                  "project_lead",
   *                  "team_size",
   *                  "project_client",
   *                  "project_management_tool",
   *                  "project_management_url",
   *                  "project_description", 
   *                  "project_repo_tool",
   *                  "project_repo_url",
   *                  "project_status",
   *                  "created_by",
   *                  "updated_by",
   *                  "created_at",
   *                  "updated_at"
   *             },
   *             @OA\Property(property="id", type="integer", example=1),
   *             @OA\Property(property="project_name", type="string", example="Project A"),
   *             @OA\Property(property="project_tech", type="string", example="PHP, JavaScript"),
   *             @OA\Property(property="project_startat", type="string", format="date", example="2023-01-01"),
   *             @OA\Property(property="project_deadline", type="string", format="date", example="2023-12-31"),
   *             @OA\Property(property="project_lead", type="string", example="John Doe"),
   *             @OA\Property(property="team_size", type="integer", example=5),
   *             @OA\Property(property="project_client", type="string", example="Client X"),
   *             @OA\Property(property="project_management_tool", type="string", example="Trello"),
   *             @OA\Property(property="project_management_url", type="string", example="https://trello.com/projectX"),
   *             @OA\Property(property="project_description", type="string", example="Project description..."),
   *             @OA\Property(property="project_repo_tool", type="string", example="GitHub"),
   *             @OA\Property(property="project_repo_url", type="string", example="https://github.com/projectX"),
   *             @OA\Property(property="project_status", type="string", example="Under Planning"),
   *             @OA\Property(property="created_by", type="string", example="Admin"),
   *             @OA\Property(property="updated_by", type="string", example="Admin"),
   *             @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T10:00:00Z"),
   *             @OA\Property(property="updated_at", type="string", format="date-time", example="2023-06-01T10:00:00Z"),
   *             @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, example="2023-12-01T10:00:00Z")
   *         )),
   *         required={
   *            "total"
   *         },
   *         @OA\Property(property="total", type="integer", example=100)
   *     ),
   *     required={
   *        "limit",
   *        "offset"
   *     },
   *     @OA\Property(property="limit", type="integer", example=10),
   *     @OA\Property(property="offset", type="integer", example=0)
   * )
   */
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
    $data = $this->userModel->get_users($search, $sort, $order, $page, $limit, $startAt, $deadlineAt, $status, $tech);
    if($data){
      $this->output
      ->set_content_type('application/json')
      ->set_status_header(200)
      ->set_output(json_encode(['data' => $data,'limit'=>$limit, 'offset'=>$offset]));
    }
    else{
      $this->output
      ->set_content_type('application/json')
      ->set_status_header(404)
      ->set_output(json_encode(['status' => 'error', 'message' => 'Data Not Found']));
    }
  }

  /**
   * @OA\Get(
   *     path="/api/project/details/{id}",
   *     summary="Retrieve project data by ID",
   *     tags={"Project Management"},
   *     @OA\Parameter(
   *         name="id",
   *         in="path",
   *         description="ID of the project to retrieve",
   *         required=true,
   *         @OA\Schema(type="integer", example=1)
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Success - Successful retrieval of project data",
   *         @OA\JsonContent(ref="#/components/schemas/GetProjectDataByIdResponse")
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Not Found - Data not found",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Project not found")
   *         )
   *     ),
   *     @OA\Response(
   *         response=400,
   *         description="Bad Request - Invalid parameters",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Invalid parameters")
   *         )
   *     ),
   *     @OA\Response(
   *         response=500,
   *         description="Failed - Internal Server Error",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Internal Server Error")
   *         )
   *     )
   * )
   */
  /**
   * @OA\Schema(
   *     schema="GetProjectDataByIdResponse",
   *     type="object",
  *      required={
   *           "id",
   *           "project_name", 
   *           "project_tech", 
   *           "project_startat", 
   *           "project_deadline",
   *           "project_lead",
   *           "team_size",
   *           "project_client",
   *           "project_management_tool",
   *           "project_management_url",
   *           "project_description", 
   *           "project_repo_tool",
   *           "project_repo_url",
   *           "project_status",
   *           "created_by",
   *           "updated_by",
   *           "created_at",
   *           "updated_at"
   *      },
   *      @OA\Property(property="id", type="integer", example=1),
   *      @OA\Property(property="project_name", type="string", example="Project A"),
   *      @OA\Property(property="project_tech", type="string", example="PHP, JavaScript"),
   *      @OA\Property(property="project_startat", type="string", format="date", example="2023-01-01"),
   *      @OA\Property(property="project_deadline", type="string", format="date", example="2023-12-31"),
   *      @OA\Property(property="project_lead", type="string", example="John Doe"),
   *      @OA\Property(property="team_size", type="integer", example=5),
   *      @OA\Property(property="project_client", type="string", example="Client X"),
   *      @OA\Property(property="project_management_tool", type="string", example="Trello"),
   *      @OA\Property(property="project_management_url", type="string", example="https://trello.com/projectX"),
   *      @OA\Property(property="project_description", type="string", example="Project description..."),
   *      @OA\Property(property="project_repo_tool", type="string", example="GitHub"),
   *      @OA\Property(property="project_repo_url", type="string", example="https://github.com/projectX"),
   *      @OA\Property(property="project_status", type="string", example="Under Planning"),
   *      @OA\Property(property="created_by", type="string", example="Admin"),
   *      @OA\Property(property="updated_by", type="string", example="Admin"),
   *      @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T10:00:00Z"),
   *      @OA\Property(property="updated_at", type="string", format="date-time", example="2023-06-01T10:00:00Z"),
   *      @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, example="2023-12-01T10:00:00Z")
   * )
   */
  public function view($id)
  {
    $this->verify_token();
    $data = $this->userModel->get_dispatche($id);
    if (empty($data)) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(404)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Project not found']));
      return;
    }
    $this->output
      ->set_content_type('application/json')
      ->set_status_header(200)
      ->set_output(json_encode($data));
  }

  /**
   * @OA\Post(
   *     path="/api/project/status",
   *     summary="Retrieve project status data based on start and end dates",
   *     tags={"Project Management"},
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(ref="#/components/schemas/GetStatusRequest")
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Successful retrieval of merged project tech statuses",
   *         @OA\JsonContent(ref="#/components/schemas/GetStatusResponse")
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Not Found - Data not found",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Project not found")
   *         )
   *     ),
   *     @OA\Response(
   *         response=400,
   *         description="Bad Request - Missing project_startat or project_deadline",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Project start at and project deadline is required")
   *         )
   *     ),
   *     @OA\Response(
   *         response=500,
   *         description="Internal Server Error",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Something went wrong")
   *         )
   *     )
   * )
   */
  /**
   * @OA\Schema(
   *     schema="GetStatusRequest",
   *     type="object",
   *     required={"project_startat", "project_deadline"},
   *     @OA\Property(property="project_startat", type="string", format="date", example="2023-01-01"),
   *     @OA\Property(property="project_deadline", type="string", format="date", example="2023-12-31")
   * ),
   * @OA\Schema(
   *     schema="GetStatusResponse",
   *     type="object",
   *     @OA\Property(property="status", type="string", example="success"),
   *     @OA\Property(
   *         property="data",
   *         type="array",
   *         @OA\Items(type="string", example="Under Planning")
   *     )
   * )
   */
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

  /**
   * @OA\Post(
   *     path="/api/project/tech",
   *     summary="Retrieve unique technologies based on project status",
   *     tags={"Project Management"},
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(ref="#/components/schemas/GetTechRequest")
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Successful retrieval of unique technologies",
   *         @OA\JsonContent(ref="#/components/schemas/GetTechResponse")
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Not Found - Data not found",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Project not found")
   *         )
   *     ),
   *     @OA\Response(
   *         response=400,
   *         description="Bad Request - Missing project_status",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Project status is required")
   *         )
   *     ),
   *     @OA\Response(
   *         response=500,
   *         description="Internal Server Error",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Something went wrong")
   *         )
   *     )
   * )
   */
  /**
   * @OA\Schema(
   *     schema="GetTechRequest",
   *     type="object",
   *     required={"project_status"},
   *     @OA\Property(property="project_status", type="string", example="Under Planning")
   * ),
   * @OA\Schema(
   *     schema="GetTechResponse",
   *     type="object",
   *     required={"status", "data"},
   *     @OA\Property(property="status", type="string", example="success"),
   *     @OA\Property(
   *         property="data",
   *         type="array",
   *         description="Array of technologies, must include at least one item",
   *         minItems=1,
   *         @OA\Items(type="string", example="Node.js")
   *     )
   * )
   */
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

  /**
   * @OA\Post(
   *     path="/api/project/create",
   *     summary="Create a new project",
   *     tags={"Project Management"},
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(ref="#/components/schemas/CreateProjectRequest")
   *     ),
   *     @OA\Response(
   *         response=201,
   *         description="Created - Project created successfully",
   *         @OA\JsonContent(ref="#/components/schemas/CreateProjectResponse")
   *     ),
   *     @OA\Response(
   *         response=400,
   *         description="Bad Request - Validation errors",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="All fields are required")
   *         )
   *     ),
   *     @OA\Response(
   *         response=409,
   *         description="Conflict - Data insertion failed",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Data must be in conflict")
   *         )
   *     ),
   *     @OA\Response(
   *         response=500,
   *         description="Internal Server Error",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Something went wrong")
   *         )
   *     ),
   *     security={{
   *        "bearerAuth": {}
   *     }}
   * )
   */
  /**
   * @OA\Schema(
   *     schema="CreateProjectRequest",
   *     type="object",
   *     required={
   *         "project_name", 
   *         "project_tech", 
   *         "project_startat", 
   *         "project_deadline",
   *         "project_lead",
   *         "team_size",
   *         "project_client",
   *         "project_management_tool",
   *         "project_management_url",
   *         "project_description", 
   *         "project_repo_tool",
   *         "project_repo_url",
   *         "project_status",
   *         "created_by",
   *         "updated_by"
   *     },
   *     @OA\Property(property="project_name", type="string", example="New Project"),
   *     @OA\Property(property="project_tech", type="array", @OA\Items(type="string"), example={"Angular", "Node.js"}),
   *     @OA\Property(property="project_startat", type="string", format="date", example="2024-01-01"),
   *     @OA\Property(property="project_deadline", type="string", format="date", example="2024-06-01"),
   *     @OA\Property(property="project_lead", type="string", example="Adam"),
   *     @OA\Property(property="team_size", type="integer", example=2),
   *     @OA\Property(property="project_client", type="string", example="TechCorp"),
   *     @OA\Property(property="project_management_tool", type="string", example="Trello"),
   *     @OA\Property(property="project_management_url", type="string", example="https://trello.com/projectX"),
   *     @OA\Property(property="project_description", type="string", example="This is a description of the project."),
   *     @OA\Property(property="project_repo_tool", type="string", example="GitHub"),
   *     @OA\Property(property="project_repo_url", type="string", example="https://github.com/projectX"),
   *     @OA\Property(property="project_status", type="string", example="Under Planning"),
   *     @OA\Property(property="created_by", type="string", example="xxx"),
   *     @OA\Property(property="updated_by", type="string", example="xxx")
   * ),
   * @OA\Schema(
   *     schema="CreateProjectResponse",
   *     type="object",
   *     required= {"status", "message"},
   *     @OA\Property(property="status", type="string", example="success"),
   *     @OA\Property(property="message", type="string", example="Your Data Inserted Successfully")
   * )
   */
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

  /**
   * @OA\Put(
   *     path="/api/project/update/{id}",
   *     summary="Update an existing project",
   *     tags={"Project Management"},
   *     @OA\Parameter(
   *         name="id",
   *         in="path",
   *         required=true,
   *         description="The ID of the project to update",
   *         @OA\Schema(type="integer", example=1)
   *     ),
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(ref="#/components/schemas/UpdateProjectRequest")
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Created - Project updated successfully",
   *         @OA\JsonContent(ref="#/components/schemas/UpdateProjectResponse")
   *     ),
   *     @OA\Response(
   *         response=400,
   *         description="Bad Request - Validation errors",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="All fields are required")
   *         )
   *     ),
   *     @OA\Response(
   *         response=409,
   *         description="Conflict - Update failed",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Data must be in conflict during update")
   *         )
   *     ),
   *     @OA\Response(
   *         response=500,
   *         description="Internal Server Error",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Something went wrong")
   *         )
   *     ),
   *     security={{
   *        "bearerAuth": {}
   *     }}
   * )
   */
  /**
   * @OA\Schema(
   *     schema="UpdateProjectRequest",
   *     type="object",
   *     required={
   *         "project_name", 
   *         "project_tech", 
   *         "project_startat", 
   *         "project_deadline",
   *         "project_lead",
   *         "team_size",
   *         "project_client",
   *         "project_management_tool",
   *         "project_management_url",
   *         "project_description", 
   *         "project_repo_tool",
   *         "project_repo_url",
   *         "project_status",
   *         "updated_by"
   *     },
   *     @OA\Property(property="project_name", type="string", example="New Project"),
   *     @OA\Property(property="project_tech", type="array", @OA\Items(type="string"), example={"Angular", "Node.js"}),
   *     @OA\Property(property="project_startat", type="string", format="date", example="2024-01-01"),
   *     @OA\Property(property="project_deadline", type="string", format="date", example="2024-06-01"),
   *     @OA\Property(property="project_lead", type="string", example="Adam"),
   *     @OA\Property(property="team_size", type="integer", example=2),
   *     @OA\Property(property="project_client", type="string", example="TechCorp"),
   *     @OA\Property(property="project_management_tool", type="string", example="Trello"),
   *     @OA\Property(property="project_management_url", type="string", example="https://trello.com/projectX"),
   *     @OA\Property(property="project_description", type="string", example="This is a description of the project."),
   *     @OA\Property(property="project_repo_tool", type="string", example="GitHub"),
   *     @OA\Property(property="project_repo_url", type="string", example="https://github.com/projectX"),
   *     @OA\Property(property="project_status", type="string", example="Under Planning"),
   *     @OA\Property(property="updated_by", type="string", example="xxx")
   * ),
   * @OA\Schema(
   *     schema="UpdateProjectResponse",
   *     type="object",
   *     required= {"status", "message"},
   *     @OA\Property(property="status", type="string", example="success"),
   *     @OA\Property(property="message", type="string", example="Your Data updated Successfully")
   * )
   */
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
        ->set_status_header(200)
        ->set_output(json_encode(['status' => 'success', 'message' => 'Your Data Updated Successfully']));
    } else {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(409)
        ->set_output(json_encode(['status' => 'error', 'message' => 'Data must be in conflict during update']));
    }
  }

  /**
   * @OA\Delete(
   *     path="/api/project/delete/{id}",
   *     summary="Delete a project",
   *     tags={"Project Management"},
   *     @OA\Parameter(
   *         name="id",
   *         in="path",
   *         required=true,
   *         description="The ID of the project to delete",
   *         @OA\Schema(type="integer", example=1)
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Project deleted successfully",
   *         @OA\JsonContent(ref="#/components/schemas/DeleteProjectResponse")
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Project not found",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Project not found")
   *         )
   *     ),
   *     @OA\Response(
   *         response=500,
   *         description="Internal Server Error",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Something went wrong")
   *         )
   *     ),
   *     security={{
   *        "bearerAuth": {}
   *     }}
   * )
   */
  /**
   * @OA\Schema(
   *     schema="DeleteProjectResponse",
   *     type="object",
   *     required= {"status", "message"},
   *     @OA\Property(property="status", type="string", example="success"),
   *     @OA\Property(property="message", type="string", example="Your Data Deleted Successfully")
   * )
   */
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
