<?php
defined('BASEPATH') or exit('No direct script access allowed');

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

/**
 * Generate a JWT token with given data payload.
 *
 * @param array $data Data payload to be included in the JWT.
 * @return string JWT token string.
 */
function generateToken($data)
{
  $key = 'your_secret_key';
  $issuedAt = time();
  $expirationTime = $issuedAt + 24 * 3600;  // jwt valid for 24 hour from the issued time
  $payload = array(
    'iat' => $issuedAt,
    'exp' => $expirationTime,
    'data' => $data
  );
  return JWT::encode($payload, $key, 'HS256');
}

/**
 * Validate and decode a JWT token.
 *
 * @param string $token JWT token to validate and decode.
 * @return array|null Decoded token data or null if validation fails.
 */
function validateToken($token)
{
  $key = new Key('your_secret_key', 'HS256');
  try {
    $decoded = JWT::decode($token, $key);
    // print_r($decoded->data);
    return (array) $decoded->data;
  } catch (\Firebase\JWT\ExpiredException $e) {
    return 'expired';
  }catch (Exception $e) {
    return null;
  }
}
function verifyToken($token)
{
  $secretKey = 'your_secret_key';
  return JWT::decode($token, new Key($secretKey, 'HS256'));
}