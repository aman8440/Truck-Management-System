<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Truck Management API",
 *     version="1.0.0",
 *     description="API documentation for the Truck Management system."
 * )
 * 
 * @OA\Server(
 *     url="http://localhost/truck_management/",
 *     description="Local server"
 * )
 * @A\Paths()
 */
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
  /**
   * @OA\Post(
   *     path="/verify_token",
   *     summary="Verify JWT Token",
   *     tags={"Authentication"},
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(ref="#/components/schemas/TokenVerifyRequest")
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Token is valid",
   *         @OA\JsonContent(ref="#/components/schemas/TokenVerifyResponse")
   *     ),
   *     @OA\Response(
   *         response=400,
   *         description="Token is missing",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="boolean", example=false),
   *             @OA\Property(property="message", type="string", example="Token is missing")
   *         )
   *     ),
   *     @OA\Response(
   *         response=401,
   *         description="Invalid token",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="boolean", example=false),
   *             @OA\Property(property="message", type="string", example="Invalid token")
   *         )
   *     )
   * )
   */
  /**
   * @OA\Schema(
   *     schema="TokenVerifyRequest",
   *     type="object",
   *     @OA\Property(property="token", type="string", description="JWT Token to verify")
   * ),
   * @OA\Schema(
   *     schema="TokenVerifyResponse",
   *     type="object",
   *     @OA\Property(property="status", type="boolean"),
   *     @OA\Property(property="message", type="string"),
   *     @OA\Property(property="data", type="object")
   * )
   */
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
  /**
   * @OA\Get(
   *     path="/me",
   *     summary="View Admin Profile",
   *     tags={"Authentication"},
   *     security={{"bearerAuth":{}}},
   *     @OA\Response(
   *         response=200,
   *         description="Successful retrieval of admin profile",
   *         @OA\JsonContent(ref="#/components/schemas/GetAdminResponse")
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
   *         response=400,
   *         description="Bad Request - Missing data",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="ID is missing in token payload")
   *         )
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Not Found - Admin",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Admin not found")
   *         )
   *     )
   * )
   */
  /**
   * @OA\Schema(
   *     schema="GetAdminResponse",
   *     type="object",
   *     @OA\Property(property="status", type="string", example="success"),
   *     @OA\Property(
   *         property="admin",
   *         type="object",
   *         @OA\Property(property="id", type="integer", example=1),
   *         @OA\Property(property="name", type="string", example="Admin Name"),
   *         @OA\Property(property="email", type="string", example="admin@example.com"),
   *         @OA\Property(property="phone", type="string", example="1234567890")
   *     )
   * )
   */
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

  /**
   * @OA\Post(
   *     path="/api/login",
   *     summary="Login API",
   *     description="Authenticate a user and return a JWT token.",
   *     tags={"Authentication"},
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(ref="#/components/schemas/AdminLoginRequest")
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Login Successful",
   *         @OA\JsonContent(ref="#/components/schemas/AdminLoginResponse")
   *     ),
   *     @OA\Response(
   *         response=400,
   *         description="Bad Request - Missing email or password",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Email and Password are required")
   *         )
   *     ),
   *     @OA\Response(
   *         response=401,
   *         description="Unauthorized - Invalid email or password",
   *         @OA\JsonContent(
   *             type="object",
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Invalid email or password")
   *         )
   *     )
   * )
   */
  /**
   * @OA\Schema(
   *     schema="AdminLoginRequest",
   *     type="object",
   *     @OA\Property(property="email", type="string", format="email", example="user@example.com"),
   *     @OA\Property(property="password", type="string", format="password", example="password123")
   * ),
   * @OA\Schema(
   *     schema="AdminLoginResponse",
   *     type="object",
   *     @OA\Property(property="status", type="string", example="success"),
   *     @OA\Property(property="message", type="string", example="Login Successfully"),
   *     @OA\Property(property="token", type="string", example="eyJhbGciOiJIUzI1NiIsInR...")
   * )
   */
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
  /**
   * @OA\Post(
   *     path="/api/signup",
   *     summary="Create Admin User",
   *     tags={"Authentication"},
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(ref="#/components/schemas/CreateAdminRequest")
   *     ),
   *     @OA\Response(
   *         response=201,
   *         description="Created - User created successfully",
   *         @OA\JsonContent(ref="#/components/schemas/CreateAdminResponse")
   *     ),
   *     @OA\Response(
   *         response=400,
   *         description="Bad Request - Validation Error",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Missing required fields or invalid input format")
   *         )
   *     ),
   *     @OA\Response(
   *         response=409,
   *         description="Conflict - User already exists",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Failed to create user. Email or phone may already be registered.")
   *         )
   *     )
   * )
   */
  /**
   * @OA\Schema(
   *     schema="CreateAdminRequest",
   *     type="object",
   *     required={"name", "phone", "email", "gender", "password"},
   *     @OA\Property(property="name", type="string"),
   *     @OA\Property(property="phone", type="string"),
   *     @OA\Property(property="email", type="string", format="email"),
   *     @OA\Property(property="gender", type="string"),
   *     @OA\Property(property="password", type="string", format="password")
   * ),
   * @OA\Schema(
   *     schema="CreateAdminResponse",
   *     type="object",
   *     @OA\Property(property="status", type="string", example="success"),
   *     @OA\Property(property="message", type="string", example="User created successfully")
   * )
   */
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

  /**
   * @OA\Post(
   *     path="/forgot_password",
   *     summary="Initiate Password Reset",
   *     tags={"Authentication"},
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(ref="#/components/schemas/ForgetPassRequest")
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Success - Password reset token sent",
   *         @OA\JsonContent(ref="#/components/schemas/ForgetPassResponse")
   *     ),
   *     @OA\Response(
   *         response=400,
   *         description="Bad Request - Missing email",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Email is required")
   *         )
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Not Found - Email not found",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Email not found")
   *         )
   *     ),
   *     @OA\Response(
   *         response=500,
   *         description="Failed - Email sending failed",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Failed to send email")
   *         )
   *     )
   * )
   */
  /**
   * @OA\Schema(
   *     schema="ForgetPassRequest",
   *     type="object",
   *     required={"email"},
   *     @OA\Property(property="email", type="string", format="email")
   * ),
   * @OA\Schema(
   *     schema="ForgetPassResponse",
   *     type="object",
   *     required={"status", "message"},
   *     @OA\Property(property="status", type="string", example="success"),
   *     @OA\Property(property="message", type="string", example="Password reset token sent to your email")
   * )
   */
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

  /**
   * @OA\Get(
   *     path="/verify-token",
   *     summary="Verify Password Reset Token",
   *     tags={"Authentication"},
   *     @OA\Parameter(
   *         name="reset_token",
   *         in="query",
   *         required=true,
   *         @OA\Schema(type="string")
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Success - Token is valid",
   *         @OA\JsonContent(ref="#/components/schemas/TokenResetResponse")
   *     ),
   *     @OA\Response(
   *         response=401,
   *         description="Unauthorized - Invalid or expired token",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Invalid or expired token")
   *         )
   *     ),
   *     @OA\Response(
   *         response=400,
   *         description="Bad Request - Token is required",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Token is required")
   *         )
   *     )
   * )
   */
  /**
   * @OA\Schema(
   *     schema="TokenResetResponse",
   *     type="object",
   *     required={"status", "message"},
   *     @OA\Property(property="status", type="string", example="success"),
   *     @OA\Property(property="message", type="string", example="Token is valid")
   * )
   */
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
            ->set_status_header(401)
            ->set_output(json_encode(['status' => 'error', 'message' => 'Invalid or expired token']));
        return;
    }

    $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(json_encode(['status' => 'success', 'message' => 'Token is valid']));
  }

  /**
   * @OA\Post(
   *     path="/reset_password",
   *     summary="Reset Password",
   *     tags={"Authentication"},
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(ref="#/components/schemas/ResetPassRequest")
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Success - Password reset successfully",
   *         @OA\JsonContent(ref="#/components/schemas/ResetPassResponse")
   *     ),
   *     @OA\Response(
   *         response=400,
   *         description="Bad Request - Invalid token or missing password",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Token and new password are required")
   *         )
   *     ),
   *     @OA\Response(
   *         response=401,
   *         description="Unauthorized - Invalid or expired token",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Invalid or expired token")
   *         )
   *     )
   * )
   */
  /**
   * @OA\Schema(
   *     schema="ResetPassRequest",
   *     type="object",
   *     required={"reset_token", "password"},
   *     @OA\Property(property="reset_token", type="string"),
   *     @OA\Property(property="password", type="string", format="password")
   * ),
   * @OA\Schema(
   *     schema="ResetPassResponse",
   *     type="object",
   *     required={"status", "message"},
   *     @OA\Property(property="status", type="string", example="success"),
   *     @OA\Property(property="message", type="string", example="Password reset successfully")
   * )
   */
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
        ->set_status_header(401)
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

  /**
   * @OA\Post(
   *     path="/api/logout",
   *     summary="Admin Logout",
   *     tags={"Authentication"},
   *     @OA\Response(
   *         response=200,
   *         description="Success - Logout successful",
   *         @OA\JsonContent(ref="#/components/schemas/LogoutResponse")
   *     ),
   *     @OA\Response(
   *         response=401,
   *         description="Unauthorized - No active session",
   *         @OA\JsonContent(
   *             @OA\Property(property="message", type="string", example="No active session found")
   *         )
   *     )
   * )
   */
  /**
   * @OA\Schema(
   *     schema="LogoutResponse",
   *     type="object",
   *     required={"status", "message"},
   *     @OA\Property(property="message", type="string", example="Logout successful")
   * )
   */
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

  /**
   * @OA\Post(
   *     path="/me/profile",
   *     summary="Upload Profile Image",
   *     tags={"File Management"},
   *     security={{"bearerAuth":{}}},
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\MediaType(
   *             mediaType="multipart/form-data",
   *             @OA\Schema(ref="#/components/schemas/ProfileImageUploadRequest")
   *         )
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Success - Image uploaded successfully",
   *         @OA\JsonContent(ref="#/components/schemas/UploadSuccessResponse")
   *     ),
   *     @OA\Response(
   *         response=400,
   *         description="Bad Request - File or User ID missing",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string")
   *         )
   *     ),
   *     @OA\Response(
   *         response=401,
   *         description="Unauthorized",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Authorization token is missing or invalid")
   *         )
   *     ),
   *     @OA\Response(
   *         response=500,
   *         description="Failed - Failed to save image data. Please try again.",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Failed to save image data. Please try again.")
   *         )
   *     )
   * )
   */
  /**
   * @OA\Schema(
   *     schema="ProfileImageUploadRequest",
   *     type="object",
   *     required={"id", "file"},
   *     @OA\Property(property="id", type="integer"),
   *     @OA\Property(property="file", type="string", format="binary")
   * ),
   * @OA\Schema(
   *     schema="UploadSuccessResponse",
   *     type="object",
   *     required={"status", "message", "image_name"},
   *     @OA\Property(property="status", type="string", example="success"),
   *     @OA\Property(property="message", type="string", example="Image uploaded successfully."),
   *     @OA\Property(property="image_name", type="string")
   * )
   */
  public function api_upload_image()
  {
    $authHeader = $this->input->get_request_header('Authorization', true);
    if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
      return $this->respond(401, 'Authorization token is missing or invalid.');
    }
    $token = $matches[1];
    try {
      $decodedToken = verifyToken($token);
    } catch (Exception $e) {
      return $this->respond(401, 'Invalid or expired token.');
    }
    $user_id = $this->input->post('id');
    if (empty($_FILES['file']['name'])) {
      return $this->respond(400, 'File is missing');
    }
    if (empty($user_id)) {
      return $this->respond(400, 'User ID is required.');
    }

    $user_id = (int)$user_id;
    $config['upload_path'] = 'assets/images/uploads';
    $config['allowed_types'] = 'jpg|png|jpeg|webp';
    $config['max_size'] = 500; 
    $this->load->library('upload', $config);

    if (!$this->upload->do_upload('file')) {
        $error = $this->upload->display_errors('', '');
        return $this->respond(400, $error);
    }

    $upload_data = $this->upload->data();
    $unique_id = uniqid();
    $extension = pathinfo($upload_data['file_name'], PATHINFO_EXTENSION);
    $unique_image_name = $unique_id . '.' . $extension;

    $new_file_path = $config['upload_path'] . '/' . $unique_image_name;
    rename($upload_data['full_path'], $new_file_path);

    $check = $this->adminModel->prf_data($user_id, $unique_image_name);

    if ($check) {
      $this->output
        ->set_content_type('application/json')
        ->set_status_header(200)
        ->set_output(json_encode(['status' => 'success', 'message' => 'Image uploaded successfully.', 'image_name' => $unique_image_name]));
    } else {
      return $this->respond(500, 'Failed to save image data. Please try again.');
    } 
  }

  /**
   * @OA\Post(
   *     path="/me/profile/delete",
   *     summary="Delete Profile Image",
   *     tags={"File Management"},
   *     security={{"bearerAuth":{}}},
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(ref="#/components/schemas/DeleteImageRequest")
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Success - Image uploaded successfully",
   *         @OA\JsonContent(ref="#/components/schemas/DeleteImageResponse")
   *     ),
   *     @OA\Response(
   *         response=400,
   *         description="Bad Request - User ID missing",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="User ID is required")
   *         )
   *     ),
   *     @OA\Response(
   *         response=401,
   *         description="Unauthorized",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Authorization token is missing or invalid")
   *         )
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Not Found - No image found",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="No image found for the provided user ID")
   *         )
   *     ),
   *     @OA\Response(
   *         response=500,
   *         description="Failed - Failed to delete image",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Failed to delete image. Please try again")
   *         )
   *     )
   * )
   */
  /**
   * @OA\Schema(
   *     schema="DeleteImageRequest",
   *     type="object",
   *     required={"id"},
   *     @OA\Property(property="id", type="integer", example=123, description="User ID of the image owner")
   * ),
   * @OA\Schema(
   *     schema="DeleteImageResponse",
   *     type="object",
   *     required={"status", "message"},
   *     @OA\Property(property="status", type="string", example="success"),
   *     @OA\Property(property="message", type="string", example="Image uploaded successfully."),
   *     @OA\Property(property="image_name", type="string", example="profile123.jpg")
   * )
   */
  public function api_delete_image()
  {
    $authHeader = $this->input->get_request_header('Authorization', true);
    if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
      return $this->respond(401, 'Authorization token is missing or invalid.');
    }

    $token = $matches[1];
    try {
      $decodedToken = verifyToken($token);
    } catch (Exception $e) {
      return $this->respond(401, 'Invalid or expired token.');
    }
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['id'])) {
      return $this->respond(400, 'User ID is required.');
    }

    $userId = (int)$data['id'];

    $imageName = $this->adminModel->get_image_name($userId);
    if (!$imageName) {
      return $this->respond(404, 'No image found for the provided user ID.');
    }
    if ($this->adminModel->delete_image($userId)) {
      $filePath = 'assets/images/uploads/' . $imageName;
      if (file_exists($filePath) && !unlink($filePath)) {
        return $this->respond(500, 'Image record deleted, but failed to delete the file.');
      }
      return $this->respond(200, 'Image deleted successfully.');
    }
    return $this->respond(500, 'Failed to delete image. Please try again.');
  }
  
  /**
   * Utility function to send a JSON response with a specific HTTP status code.
   *
   * @param int $statusCode HTTP status code.
   * @param string $message Response message.
   * @return void
   */
  private function respond($statusCode, $message)
  {
    $this->output
        ->set_content_type('application/json')
        ->set_status_header($statusCode)
        ->set_output(json_encode(['status' => $statusCode === 200 || $statusCode === 201 ? 'success' : 'error', 'message' => $message]));
  }
  /**
    * @OA\SecurityScheme(
    *     securityScheme="bearerAuth",
    *     type="http",
    *     scheme="bearer",
    *     bearerFormat="JWT"
    * )
    */
};