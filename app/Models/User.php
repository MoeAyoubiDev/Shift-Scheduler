<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseModel.php';

class User extends BaseModel
{
    protected string $table = 'users';
    protected array $fillable = [
        'username',
        'password_hash',
        'email',
        'is_active',
        'created_at',
        'updated_at',
    ];

    public function findByUsername(string $username): ?array
    {
        return $this->firstWhere('username', $username);
    }

    public function createWithPassword(string $username, string $password, ?string $email = null): int
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        return $this->create([
            'username' => $username,
            'password_hash' => $hash,
            'email' => $email,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
