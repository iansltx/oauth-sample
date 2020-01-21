<?php

namespace App\Repositories;

use App\Models\User;
use Aura\Sql\ExtendedPdoInterface;

class UserRepository
{
    protected ExtendedPdoInterface $db;

    public function __construct(ExtendedPdoInterface $db)
    {
        $this->db = $db;
    }

    public function getByUsernameAndPassword(?string $username, ?string $password): ?User
    {
        if (!$username || !$password ||
                !($row = $this->db->fetchOne('SELECT * FROM user WHERE username = ?', [$username]))) {
            return null;
        }

        return password_verify($password, $row['password']) ? $this->userFromRow($row) : null;
    }

    public function getById(int $id): User
    {
        if (!($row = $this->db->fetchOne('SELECT * FROM user WHERE id = ?', [$id]))) {
            throw new \InvalidArgumentException('A user with that ID does not exist');
        }

        return $this->userFromRow($row);
    }

    protected function userFromRow(array $row): User
    {
        return new User($row['id'], $row['first_name'], $row['last_name']);
    }
}
