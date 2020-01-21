<?php

namespace App;

use App\Models\User;
use App\Repositories\UserRepository;

class Session
{
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        session_start();
    }

    public function getUser(): ?User
    {
        return $_SESSION['userId'] ? $this->userRepository->getById($_SESSION['userId']) : null;
    }

    public function isLoggedIn()
    {
        return $_SESSION['userId'] !== null;
    }

    public function logOut()
    {
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();
    }

    public function setUser(User $user)
    {
        $_SESSION['userId'] = $user->getId();
    }
}
