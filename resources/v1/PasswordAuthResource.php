<?php
require_once '../config/database.php';
require_once '../models/PasswordPolicy.php';
require_once '../core/services/PasswordValidator.php';
require_once '../models/ApiToken.php';

class PasswordAuthResource
{
    private $db;
    private const MAX_ATTEMPTS = 5;
    private const LOCK_MINUTES = 15;

    public function __construct() { $this->db = (new Database())->getConnection(); }

    // POST /auth/register
    public function register()
    {
        header("Content-Type: application/json");
        $data = json_decode(file_get_contents("php://input"));

        if (empty($data->username) || empty($data->email) || empty($data->password)) {
            http_response_code(400);
            echo json_encode(["error" => "invalid_request", "message" => "username, email y password son obligatorios"]);
            return;
        }

        $check = $this->db->prepare("SELECT id FROM api_users WHERE username = :u OR email = :e LIMIT 1");
        $check->bindParam(':u', $data->username);
        $check->bindParam(':e', $data->email);
        $check->execute();
        if ($check->fetch()) {
            http_response_code(409);
            echo json_encode(["error" => "user_exists", "message" => "El usuario o correo ya existe"]);
            return;
        }

        $policy = (new PasswordPolicy($this->db))->get();
        $validation = PasswordValidator::validate($data->password, $policy, $data->username);

        if (!$validation['isValid']) {
            http_response_code(422);
            echo json_encode($validation);
            return;
        }

        $hash = password_hash($data->password, PASSWORD_DEFAULT);
        $insert = $this->db->prepare(
            "INSERT INTO api_users (username, email, password_hash) VALUES (:u, :e, :p)"
        );
        $insert->bindParam(':u', $data->username);
        $insert->bindParam(':e', $data->email);
        $insert->bindParam(':p', $hash);
        $insert->execute();
        $id = $this->db->lastInsertId();

        $row = $this->db->prepare("SELECT id, username, email, created_at FROM api_users WHERE id = :id");
        $row->bindParam(':id', $id);
        $row->execute();
        $user = $row->fetch(PDO::FETCH_ASSOC);

        http_response_code(201);
        echo json_encode([
            "id" => (string)$user['id'],
            "username" => $user['username'],
            "email" => $user['email'],
            "createdAt" => str_replace(' ', 'T', $user['created_at']) . 'Z'
        ]);
    }

    // POST /auth/login
    public function login()
    {
        header("Content-Type: application/json");
        $data = json_decode(file_get_contents("php://input"));

        if (empty($data->username) || empty($data->password)) {
            http_response_code(400);
            echo json_encode(["error" => "invalid_request", "message" => "username y password son obligatorios"]);
            return;
        }

        $stmt = $this->db->prepare(
            "SELECT id, password_hash, status, failed_attempts, locked_until FROM api_users WHERE username = :u LIMIT 1"
        );
        $stmt->bindParam(':u', $data->username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $user['locked_until'] && strtotime($user['locked_until']) > time()) {
            http_response_code(423);
            echo json_encode(["error" => "account_locked", "message" => "Cuenta bloqueada temporalmente por múltiples intentos fallidos"]);
            return;
        }

        if (!$user || $user['status'] !== 'ACTIVE' || !password_verify($data->password, $user['password_hash'])) {
            if ($user) {
                $this->registerFailedAttempt($user);
            }
            http_response_code(401);
            echo json_encode(["error" => "invalid_credentials", "message" => "Usuario o contraseña incorrectos"]);
            return;
        }

        $reset = $this->db->prepare("UPDATE api_users SET failed_attempts = 0, locked_until = NULL WHERE id = :id");
        $reset->bindParam(':id', $user['id']);
        $reset->execute();

        $ttlMinutes = 60;
        $result = (new ApiToken($this->db))->generateForUser($user['id'], $ttlMinutes);

        http_response_code(200);
        echo json_encode([
            "accessToken" => $result['token'],
            "tokenType" => "Bearer",
            "expiresIn" => $ttlMinutes * 60
        ]);
    }

    private function registerFailedAttempt(array $user): void
    {
        $attempts = (int)$user['failed_attempts'] + 1;

        if ($attempts >= self::MAX_ATTEMPTS) {
            $lockedUntil = date('Y-m-d H:i:s', strtotime('+' . self::LOCK_MINUTES . ' minutes'));
            $stmt = $this->db->prepare(
                "UPDATE api_users SET failed_attempts = 0, locked_until = :locked WHERE id = :id"
            );
            $stmt->bindParam(':locked', $lockedUntil);
            $stmt->bindParam(':id', $user['id']);
            $stmt->execute();
        } else {
            $stmt = $this->db->prepare("UPDATE api_users SET failed_attempts = :a WHERE id = :id");
            $stmt->bindParam(':a', $attempts);
            $stmt->bindParam(':id', $user['id']);
            $stmt->execute();
        }
    }
}
?>