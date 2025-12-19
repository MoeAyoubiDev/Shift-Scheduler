<?php
declare(strict_types=1);

require_once __DIR__ . '/../Core/Database.php';

class User
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch() ?: null;
    }
    
    public function create(string $username, string $password, ?string $email = null): int
    {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare(
            "INSERT INTO users (username, password_hash, email) VALUES (?, ?, ?)"
        );
        $stmt->execute([$username, $hash, $email]);
        return (int) $this->db->lastInsertId();
    }
}
