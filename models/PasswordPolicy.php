<?php
class PasswordPolicy
{
    private $conn;
    public function __construct($db) { $this->conn = $db; }

    public function get(): array
    {
        $stmt = $this->conn->prepare("SELECT * FROM password_policy WHERE id = 1 LIMIT 1");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            "minLength" => (int)$row['min_length'],
            "maxLength" => (int)$row['max_length'],
            "requireUppercase" => (bool)$row['require_uppercase'],
            "requireLowercase" => (bool)$row['require_lowercase'],
            "requireNumbers" => (bool)$row['require_numbers'],
            "requireSymbols" => (bool)$row['require_symbols'],
            "disallowCommonPasswords" => (bool)$row['disallow_common_passwords'],
            "disallowUsernameInPassword" => (bool)$row['disallow_username_in_password'],
            "disallowSequentialCharacters" => (bool)$row['disallow_sequential_characters'],
            "passwordHistoryLimit" => (int)$row['password_history_limit'],
            "expirationDays" => (int)$row['expiration_days'],
        ];
    }
}
?>