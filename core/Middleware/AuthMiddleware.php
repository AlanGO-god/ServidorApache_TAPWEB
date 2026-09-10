<?php
require_once '../config/database.php';

class AuthMiddleware
{
    public static ?array $user = null;

    public static function handle(): bool
    {
        $token = self::getBearerToken();

        if (!$token) {
            self::unauthorized();
            return false;
        }

        $database = new Database();
        $db = $database->getConnection();

        $query = "SELECT t.expires_at, t.revoked, u.id AS user_id, u.username, u.email, u.status
                  FROM api_tokens t
                  INNER JOIN api_users u ON u.id = t.user_id
                  WHERE t.token = :token LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $expirado = !$row || strtotime($row['expires_at']) < time();
        if (!$row || (int)$row['revoked'] === 1 || $expirado || $row['status'] !== 'ACTIVE') {
            self::unauthorized();
            return false;
        }

        self::$user = ['id' => $row['user_id'], 'username' => $row['username'], 'email' => $row['email']];
        return true;
    }

    public static function getBearerToken(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null;

        if (!$header && function_exists('apache_request_headers')) {
            foreach (apache_request_headers() as $name => $value) {
                if (strcasecmp($name, 'Authorization') === 0) { $header = $value; break; }
            }
        }

        if ($header && preg_match('/Bearer\s+(.*)$/i', $header, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    private static function unauthorized(): void
    {
        http_response_code(401);
        header("Content-Type: application/json");
        echo json_encode(["error" => "unauthorized", "message" => "Token inválido, expirado o no proporcionado"]);
    }
}