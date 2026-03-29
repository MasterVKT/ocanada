<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\NotificationService;
use App\Models\UtilisateurModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\I18n\Time;

/**
 * Contrôleur d'authentification
 */
class AuthController extends BaseController
{
    protected UtilisateurModel $userModel;
    protected NotificationService $notificationService;

    public function __construct()
    {
        $this->userModel            = model(UtilisateurModel::class);
        $this->notificationService  = new NotificationService();
    }

    /**
     * Affiche le formulaire de connexion
     */
    public function login(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        if ($this->isLoggedIn()) {
            // user already connected, send to proper dashboard
            return $this->redirectAfterLogin($this->currentUser['role'] ?? '');
        }

        return $this->renderView('auth/login', [
            'title' => lang('Auth.login_title')
        ]);
    }

    /**
     * Traite la connexion
     */
    public function attemptLogin(): RedirectResponse
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $user = $this->userModel->where('email', $email)->first();

        if (!$user || !password_verify($password, $user['mot_de_passe'])) {
            // Journaliser l'échec
            $this->auditLog('ECHEC_CONNEXION', [
                'email' => $email,
                'ip'    => $this->request->getIPAddress()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', lang('Auth.login_error'));
        }

        if ($user['statut'] !== 'actif') {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Compte désactivé.');
        }

        $userId = (int) $user['id'];
        $employeId = isset($user['employe_id']) && $user['employe_id'] !== null ? (int) $user['employe_id'] : null;

        // Vérifier les tentatives de connexion
        if ($this->isAccountLocked($userId)) {
            return redirect()->back()
                ->withInput()
                ->with('error', lang('Auth.login_attempts'));
        }

        // Connexion réussie
        $this->session->set([
            'user_id'    => $userId,
            'user_email' => $user['email'],
            'role'       => $user['role'],       // use simple key to match AuthFilter/loadCurrentUser
            'employe_id' => $employeId,
            'logged_in'  => true,
            'login_time' => time()
        ]);

        // Régénérer la session pour sécurité
        $this->session->regenerate();

        // Mettre à jour dernière connexion
        $this->userModel->update($userId, [
            'derniere_connexion' => date('Y-m-d H:i:s')
        ]);

        // Journaliser la connexion
        $this->auditLog('CONNEXION', [
            'user_id' => $userId,
            'ip'      => $this->request->getIPAddress()
        ]);

        // Rediriger selon le rôle
        return $this->redirectAfterLogin($user['role']);
    }

    /**
     * Déconnexion
     */
    public function logout(): RedirectResponse
    {
        if ($this->isLoggedIn()) {
            $userId = $this->session->get('user_id');

            // Journaliser la déconnexion
            $this->auditLog('DECONNEXION', [
                'user_id' => $userId,
                'ip'      => $this->request->getIPAddress()
            ]);
        }

        $this->session->destroy();

        return redirect()->to('/login')->with('success', lang('Auth.logout_success'));
    }

    /**
     * Affiche le formulaire mot de passe oublié
     */
    public function forgotPassword(): string
    {
        return $this->renderView('auth/forgot_password', [
            'title' => lang('Auth.forgot_title')
        ]);
    }

    /**
     * Traite la demande de réinitialisation
     */
    public function sendResetLink(): RedirectResponse
    {
        $rules = [
            'email' => 'required|valid_email'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $email = $this->request->getPost('email');
        $user  = $this->userModel->where('email', $email)->first();

        if (!$user) {
            return redirect()->back()
                ->withInput()
                ->with('error', lang('Auth.forgot_error'));
        }

        // Générer token de réinitialisation
        $token = bin2hex(random_bytes(32));
        $this->userModel->update($user['id'], [
            'reset_token'      => $token,
            'reset_expires_at' => date('Y-m-d H:i:s', time() + 3600) // 1h
        ]);

        // TODO: Envoyer email avec lien de réinitialisation
        // Pour l'instant, juste un message de succès

        return redirect()->back()->with('success', lang('Auth.forgot_success'));
    }

    /**
     * Affiche le formulaire de réinitialisation
     */
    public function resetPassword(string $token): string
    {
        $user = $this->userModel->where('reset_token', $token)
            ->where('reset_expires_at >', date('Y-m-d H:i:s'))
            ->first();

        if (!$user) {
            return $this->renderView('auth/reset_password', [
                'title'  => lang('Auth.reset_title'),
                'error'  => lang('Auth.reset_error'),
                'valid'  => false
            ]);
        }

        return $this->renderView('auth/reset_password', [
            'title' => lang('Auth.reset_title'),
            'token' => $token,
            'valid' => true
        ]);
    }

    /**
     * Traite la réinitialisation du mot de passe
     */
    public function updatePassword(): RedirectResponse
    {
        $rules = [
            'token'           => 'required',
            'password'        => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $token    = $this->request->getPost('token');
        $password = $this->request->getPost('password');

        $user = $this->userModel->where('reset_token', $token)
            ->where('reset_expires_at >', date('Y-m-d H:i:s'))
            ->first();

        if (!$user) {
            return redirect()->back()->with('error', lang('Auth.reset_error'));
        }

        // Mettre à jour le mot de passe
        $this->userModel->update($user['id'], [
            'mot_de_passe'     => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
            'reset_token'      => null,
            'reset_expires_at' => null
        ]);

        return redirect()->to('/login')->with('success', lang('Auth.reset_success'));
    }

    /**
     * Vérifie si le compte est verrouillé
     */
    protected function isAccountLocked(int $userId): bool
    {
        // TODO: Implémenter la logique de verrouillage après 5 tentatives
        return false;
    }

    /**
     * Redirige après connexion selon le rôle
     */
    protected function redirectAfterLogin(string $role): RedirectResponse
    {
        switch ($role) {
            case 'admin':
                return redirect()->to('/admin/dashboard');
            case 'agent':
                return redirect()->to('/agent/dashboard');
            case 'employe':
            default:
                return redirect()->to('/employe/dashboard');
        }
    }
}