<?php
class ApiToken
{
    private $conn;
    public function __construct($db) { $this->conn = $db; }

    public function generateForUser(int $userId, int $ttlMinutes = 60): array
    {
        // Invalidar tokens anteriores del usuario
        $revoke = $this->conn->prepare("UPDATE api_tokens SET revoked = TRUE WHERE user_id = :uid AND revoked = FALSE");
        $revoke->bindParam(':uid', $userId);
        $revoke->execute();

        // Token aleatorio, no predecible, 32 bytes
        $token = bin2hex(random_bytes(32)); // 64 caracteres hex
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$ttlMinutes} minutes"));

        $insert = $this->conn->prepare(
            "INSERT INTO api_tokens (user_id, token, expires_at) VALUES (:uid, :token, :exp)"
        );
        $insert->bindParam(':uid', $userId);
        $insert->bindParam(':token', $token);
        $insert->bindParam(':exp', $expiresAt);
        $insert->execute();

        return ['token' => $token, 'expires_at' => $expiresAt];
    }

    public function revoke(string $token): bool
    {
        $stmt = $this->conn->prepare("UPDATE api_tokens SET revoked = TRUE WHERE token = :token");
        $stmt->bindParam(':token', $token);
        return $stmt->execute();
    }
}