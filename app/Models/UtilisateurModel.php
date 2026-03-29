<?php
declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class UtilisateurModel extends Model
{
    protected $table          = 'utilisateurs';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps  = false;

    protected $allowedFields  = [
        'email',
        'mot_de_passe',
        'role',
        'statut',
        'employe_id',
        'reset_token',
        'reset_expires_at',
        'date_creation',
        'derniere_connexion',
        'token_reinitialisation',
        'token_expiration',
    ];

    protected $validationRules = [
        'email'        => 'required|valid_email|max_length[255]',
        'mot_de_passe' => 'required|string|max_length[255]',
        'role'         => 'required|in_list[admin,employe,agent]',
        'statut'       => 'required|in_list[actif,inactif]',
    ];

    public function findByEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }

    /**
     * Retourne les identifiants de tous les administrateurs.
     *
     * @return int[]
     */
    public function getAdminIds(): array
    {
        $rows = $this->select('id')
            ->where('role', 'admin')
            ->where('statut', 'actif')
            ->findAll();

        return array_map(static fn (array $row): int => (int) $row['id'], $rows);
    }
}

