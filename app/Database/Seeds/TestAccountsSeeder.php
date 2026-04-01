<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use App\Models\UtilisateurModel;
use CodeIgniter\Database\Seeder;

/**
 * TestAccountsSeeder
 *
 * Crée / réinitialise les comptes de test pour chaque rôle applicatif.
 * Idempotent : peut être relancé plusieurs fois sans doublon ni erreur.
 *
 * Comptes créés / mis à jour :
 *   admin   → admin@ocanada.local    / Admin123!
 *   agent   → agent@ocanada.local    / Agent123!
 *   employe → employe@ocanada.local  / Employe123!   (compte test dédié, lié à EMP-TEST)
 *
 * Les comptes employe existants (anemena, kamdem) voient aussi leur mot de passe réinitialisé :
 *   anemena@gmail.com  / Employe123!
 *   kamdem@gmail.com   / Employe123!
 */
class TestAccountsSeeder extends Seeder
{
    /** Coût bcrypt réduit pour un seeder de dev (ne pas utiliser en prod). */
    private const BCRYPT_COST = 10;

    public function run(): void
    {
        $this->upsertAgent();
        $this->upsertTestEmployee();
        $this->resetAllEmployeePasswords();
        $this->resetAdminPassword();

        echo PHP_EOL
            . '╔══════════════════════════════════════════════════════════╗' . PHP_EOL
            . '║          COMPTES DE TEST — Ô Canada                      ║' . PHP_EOL
            . '╠══════════════════════════════════════════════════════════╣' . PHP_EOL
            . '║ Rôle    │ Email                    │ Mot de passe         ║' . PHP_EOL
            . '╠══════════════════════════════════════════════════════════╣' . PHP_EOL
            . '║ admin   │ admin@ocanada.local       │ Admin123!            ║' . PHP_EOL
            . '║ agent   │ agent@ocanada.local       │ Agent123!            ║' . PHP_EOL
            . '║ employe │ employe@ocanada.local     │ Employe123!          ║' . PHP_EOL
            . '║ employe │ anemena@gmail.com         │ Employe123!          ║' . PHP_EOL
            . '║ employe │ kamdem@gmail.com          │ Employe123!          ║' . PHP_EOL
            . '╚══════════════════════════════════════════════════════════╝' . PHP_EOL
            . PHP_EOL;
    }

    // -------------------------------------------------------------------------

    private function upsertAgent(): void
    {
        /** @var UtilisateurModel $users */
        $users    = model(UtilisateurModel::class);
        $existing = $users->where('email', 'agent@ocanada.local')->first();

        $hash = password_hash('Agent123!', PASSWORD_BCRYPT, ['cost' => self::BCRYPT_COST]);

        if ($existing !== null) {
            $users->update($existing['id'], [
                'mot_de_passe' => $hash,
                'statut'       => 'actif',
            ]);
            return;
        }

        $users->insert([
            'email'             => 'agent@ocanada.local',
            'mot_de_passe'      => $hash,
            'role'              => 'agent',
            'statut'            => 'actif',
            'employe_id'        => null,
            'date_creation'     => date('Y-m-d H:i:s'),
            'derniere_connexion' => null,
        ]);
    }

    private function upsertTestEmployee(): void
    {
        /** @var UtilisateurModel $users */
        $users    = model(UtilisateurModel::class);
        $existing = $users->where('email', 'employe@ocanada.local')->first();

        $hash = password_hash('Employe123!', PASSWORD_BCRYPT, ['cost' => self::BCRYPT_COST]);

        if ($existing !== null) {
            $users->update($existing['id'], [
                'mot_de_passe' => $hash,
                'statut'       => 'actif',
            ]);
            return;
        }

        // Crée d'abord la fiche employé de test si elle n'existe pas.
        $db = $this->db;
        $emp = $db->table('employes')
                  ->where('matricule', 'EMP-TEST-001')
                  ->get()
                  ->getRowArray();

        if ($emp === null) {
            $db->table('employes')->insert([
                'matricule'         => 'EMP-TEST-001',
                'nom'               => 'Test',
                'prenom'            => 'Employe',
                'email'             => 'employe@ocanada.local',
                'telephone'         => '+237 600 000 000',
                'date_naissance'    => '1990-01-01',
                'date_embauche'     => date('Y-m-d'),
                'poste'             => 'Stagiaire test',
                'departement'       => 'Direction',
                'salaire_base'      => 100000,
                'pin_kiosque'       => '0000',
                'adresse'           => 'Douala',
                'ville'             => 'Douala',
                'code_postal'       => '00000',
                'pays'              => 'Cameroun',
                'statut'            => 'actif',
                'date_creation'     => date('Y-m-d H:i:s'),
                'date_modification' => date('Y-m-d H:i:s'),
            ]);
            $empId = $db->insertID();
        } else {
            $empId = $emp['id'];
        }

        $users->insert([
            'email'             => 'employe@ocanada.local',
            'mot_de_passe'      => $hash,
            'role'              => 'employe',
            'statut'            => 'actif',
            'employe_id'        => $empId,
            'date_creation'     => date('Y-m-d H:i:s'),
            'derniere_connexion' => null,
        ]);
    }

    private function resetAllEmployeePasswords(): void
    {
        $db   = $this->db;
        $hash = password_hash('Employe123!', PASSWORD_BCRYPT, ['cost' => self::BCRYPT_COST]);

        foreach (['anemena@gmail.com', 'kamdem@gmail.com'] as $email) {
            $db->table('utilisateurs')
               ->where('email', $email)
               ->update(['mot_de_passe' => $hash, 'statut' => 'actif']);
        }
    }

    private function resetAdminPassword(): void
    {
        $hash = password_hash('Admin123!', PASSWORD_BCRYPT, ['cost' => self::BCRYPT_COST]);

        $this->db->table('utilisateurs')
                 ->where('email', 'admin@ocanada.local')
                 ->update(['mot_de_passe' => $hash]);
    }
}
