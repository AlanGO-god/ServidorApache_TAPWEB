<?php
class ApiUser
{
    private $conn;
    public function __construct($db) { $this->conn = $db; }

    public function findActiveByUsername(string $username): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT id, username, email, password_hash, status FROM api_users WHERE username = :u LIMIT 1"
        );
        $stmt->bindParam(':u', $username);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}