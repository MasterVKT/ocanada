<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\PresenceCalculator;
use App\Models\EmployeModel;
use App\Models\PresenceModel;

/**
 * Contrôleur du mode kiosque
 */
class KiosqueController extends BaseController
{
    protected EmployeModel $employeModel;
    protected PresenceModel $presenceModel;
    protected PresenceCalculator $calculator;

    public function __construct()
    {
        $this->employeModel   = model(EmployeModel::class);
        $this->presenceModel  = model(PresenceModel::class);
        $this->calculator     = new PresenceCalculator();
    }

    /**
     * Affichage du kiosque
     */
    public function index(): string
    {
        return $this->renderView('kiosque/index', [
            'title' => 'Kiosque de pointage'
        ]);
    }

    /**
     * Pointage arrivée
     */
    public function pointageArrivee(): \CodeIgniter\HTTP\ResponseInterface
    {
        $employeId = $this->request->getPost('employe_id');
        $pin = $this->request->getPost('pin');

        // Vérifier l'employé
        $employe = $this->employeModel->find($employeId);

        if (!$employe || !$this->employeModel->hasValidPin($employeId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Employé non trouvé ou PIN invalide.'
            ])->setStatusCode(404);
        }

        // Vérifier le PIN
        if ($employe['pin_kiosque'] !== $pin) {
            $this->auditLog('ECHEC_PIN_KIOSQUE', [
                'employe_id' => $employeId,
                'ip'         => $this->request->getIPAddress()
            ]);

            return $this->response->setJSON([
                'success' => false,
                'message' => 'PIN incorrect.'
            ])->setStatusCode(401);
        }

        // Enregistrer l'arrivée
        $heureArrivee = date('H:i');
        if (!$this->presenceModel->pointageArrivee($employeId, $heureArrivee)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Impossible d\'enregistrer l\'arrivée.'
            ])->setStatusCode(500);
        }

        // Journaliser
        $this->auditLog('POINTAGE_ARRIVEE', [
            'employe_id' => $employeId,
            'heure'      => $heureArrivee,
            'ip'         => $this->request->getIPAddress()
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Arrivée enregistrée',
            'employe' => $employe['prenom'] . ' ' . $employe['nom'],
            'heure' => $heureArrivee
        ]);
    }

    /**
     * Pointage départ
     */
    public function pointageDepart(): \CodeIgniter\HTTP\ResponseInterface
    {
        $employeId = $this->request->getPost('employe_id');
        $pin = $this->request->getPost('pin');

        // Vérifier l'employé
        $employe = $this->employeModel->find($employeId);

        if (!$employe || $employe['pin_kiosque'] !== $pin) {
            $this->auditLog('ECHEC_PIN_KIOSQUE', [
                'employe_id' => $employeId,
                'ip'         => $this->request->getIPAddress()
            ]);

            return $this->response->setJSON([
                'success' => false,
                'message' => 'PIN incorrect.'
            ])->setStatusCode(401);
        }

        // Enregistrer le départ
        $heureDepart = date('H:i');
        if (!$this->presenceModel->pointageDepart($employeId, $heureDepart)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Impossible d\'enregistrer le départ.'
            ])->setStatusCode(500);
        }

        // Journaliser
        $this->auditLog('POINTAGE_DEPART', [
            'employe_id' => $employeId,
            'heure'      => $heureDepart,
            'ip'         => $this->request->getIPAddress()
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Départ enregistré',
            'employe' => $employe['prenom'] . ' ' . $employe['nom'],
            'heure' => $heureDepart
        ]);
    }

    /**
     * Recherche d'employé (AJAX)
     */
    public function searchEmployee(): \CodeIgniter\HTTP\ResponseInterface
    {
        $search = $this->request->getGet('q');

        if (strlen($search) < 2) {
            return $this->response->setJSON([
                'results' => []
            ]);
        }

        $employes = $this->employeModel->db->table('employes')
            ->where('statut', 'actif')
            ->groupStart()
            ->like('matricule', $search)
            ->orLike('prenom', $search)
            ->orLike('nom', $search)
            ->groupEnd()
            ->select('id, prenom, nom, matricule')
            ->limit(10)
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'results' => $employes
        ]);
    }
}