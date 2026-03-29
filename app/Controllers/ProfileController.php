<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\EmployeModel;
use App\Models\UtilisateurModel;

/**
 * Contrôleur de gestion du profil utilisateur
 */
class ProfileController extends BaseController
{
    protected UtilisateurModel $userModel;
    protected EmployeModel $employeModel;

    public function __construct()
    {
        $this->userModel    = model(UtilisateurModel::class);
        $this->employeModel = model(EmployeModel::class);
    }

    /**
     * Affichage du profil
     */
    public function index(): string
    {
        $user = $this->userModel->find($this->session->get('user_id'));
        $employe = null;

        if ($user['employe_id']) {
            $employe = $this->employeModel->find($user['employe_id']);
        }

        return $this->renderView('profile/index', [
            'title'   => 'Mon profil',
            'user'    => $user,
            'employe' => $employe,
        ]);
    }

    /**
     * Mise à jour du mot de passe
     */
    public function updatePassword(): \CodeIgniter\HTTP\RedirectResponse
    {
        $rules = [
            'current_password' => 'required',
            'new_password'     => 'required|min_length[8]',
            'confirm_password' => 'required|matches[new_password]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $userId = $this->session->get('user_id');
        $user = $this->userModel->find($userId);

        $currentPassword = $this->request->getPost('current_password');
        $newPassword = $this->request->getPost('new_password');

        if (!password_verify($currentPassword, $user['mot_de_passe'])) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Mot de passe actuel incorrect.');
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);

        if ($this->userModel->update($userId, ['mot_de_passe' => $hashedPassword])) {
            $this->auditLog('CHANGEMENT_MOT_DE_PASSE', [
                'user_id' => $userId,
            ]);

            return redirect()->back()
                ->with('success', 'Mot de passe modifié avec succès.');
        }

        return redirect()->back()
            ->with('error', 'Erreur lors de la modification du mot de passe.');
    }

    /**
     * Mise à jour du PIN kiosque
     */
    public function updatePin(): \CodeIgniter\HTTP\RedirectResponse
    {
        $rules = [
            'pin' => 'required|exact_length[4]|numeric',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $userId = $this->session->get('user_id');
        $user = $this->userModel->find($userId);

        if (!$user['employe_id']) {
            return redirect()->back()
                ->with('error', 'Aucun employé associé à ce compte.');
        }

        $pin = $this->request->getPost('pin');

        if ($this->employeModel->updatePin($user['employe_id'], $pin)) {
            $this->auditLog('CHANGEMENT_PIN_KIOSQUE', [
                'user_id'    => $userId,
                'employe_id' => $user['employe_id'],
            ]);

            return redirect()->back()
                ->with('success', 'PIN modifié avec succès.');
        }

        return redirect()->back()
            ->with('error', 'Erreur lors de la modification du PIN.');
    }
}