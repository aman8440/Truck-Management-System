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
  $key = 'your_secret_key'; // Change this to your secret key
  $issuedAt = time();
  $expirationTime = $issuedAt + 3600;  // jwt valid for 1 hour from the issued time
  $payload = array(
    'iat' => $issuedAt,
    'exp' => $expirationTime,
    'data' => $data
  );
  return JWT::encode($payload, $key, 'HS256'); // Specify algorithm HS256 here
}

/**
 * Validate and decode a JWT token.
 *
 * @param string $token JWT token to validate and decode.
 * @return array|null Decoded token data or null if validation fails.
 */
function validateToken($token)
{
  $key = new Key('your_secret_key', 'HS256'); // Change this to your secret key
  try {
    $decoded = JWT::decode($token, $key);
    // print_r($decoded->data);
    return (array) $decoded->data;
  } catch (Exception $e) {
    return null;
  }
}
