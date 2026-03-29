<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        // Redirect users straight into the application.
        // If they're logged in, take them to their dashboard;
        // otherwise send them to the login page.
        if (! empty($this->currentUser['user_id'])) {
            // use same logic as AuthController for role-based redirect
            return $this->redirectAfterLogin($this->currentUser['role'] ?? '');
        }

        return redirect()->to('login');
    }
}
