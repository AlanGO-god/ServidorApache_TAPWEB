<?php
require_once '../config/database.php';
require_once '../models/ApiUser.php';
require_once '../models/ApiToken.php';
require_once '../core/Middleware/AuthMiddleware.php';

class AuthResource
{
    private $db;
    public function __construct() { $this->db = (new Database())->getConnection(); }

    // POST /api/v2/login
    public function login()
    {
        header("Content-Type: application/json");
        $data = json_decode(file_get_contents("php://input"));

        if (empty($data->username) || empty($data->password)) {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos"]);
            return;
        }

        $user = (new ApiUser($this->db))->findActiveByUsername($data->username);

        // mensaje genérico: no revela si falló usuario o password
        if (!$user || $user['status'] !== 'ACTIVE' || !password_verify($data->password, $user['password_hash'])) {
            http_response_code(401);
            echo json_encode(["error" => "invalid_credentials", "message" => "Usuario o contraseña incorrectos"]);
            return;
        }

        $result = (new ApiToken($this->db))->generateForUser($user['id'], 60);

        http_response_code(200);
        echo json_encode([
            "access_token" => $result['token'],
            "token_type" => "Bearer",
            "expires_at" => $result['expires_at']
        ]);
    }

    // POST /api/v2/logout (protegido)
    public function logout()
    {
        header("Content-Type: application/json");
        $token = AuthMiddleware::getBearerToken();
        (new ApiToken($this->db))->revoke($token);
        http_response_code(200);
        echo json_encode(["message" => "Sesión cerrada correctamente"]);
    }

    // GET /api/v2/me (protegido)
    public function me()
    {
        header("Content-Type: application/json");
        http_response_code(200);
        echo json_encode(AuthMiddleware::$user);
    }
}