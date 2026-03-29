<?php
declare(strict_types=1);

namespace App\Controllers\Employe;

use App\Controllers\BaseController;
use App\Models\PresenceModel;
use App\Models\SoldeCongeModel;

/**
 * Tableau de bord employé
 */
class DashboardController extends BaseController
{
    protected PresenceModel $presenceModel;
    protected SoldeCongeModel $soldeModel;

    public function __construct()
    {
        $this->presenceModel = model(PresenceModel::class);
        $this->soldeModel    = model(SoldeCongeModel::class);
    }

    /**
     * Affichage du tableau de bord
     */
    public function index(): string
    {
        $employeId = $this->session->get('employe_id');
        $currentMonth = date('Y-m');

        // Présences du mois
        $presencesMois = $this->presenceModel->getByEmploye($employeId, $currentMonth . '-01', $currentMonth . '-31');
        $presents = count(array_filter($presencesMois, fn($p) => $p['statut'] === 'present'));
        $retards = count(array_filter($presencesMois, fn($p) => $p['statut'] === 'retard'));
        $absents = count(array_filter($presencesMois, fn($p) => $p['statut'] === 'absent'));

        // Solde de congé
        $soldeConge = $this->soldeModel->getCurrentSolde($employeId);

        // Présence aujourd'hui
        $today = date('Y-m-d');
        $presenceToday = $this->presenceModel->where('employe_id', $employeId)
            ->where('date_pointage', $today)
            ->first();

        return $this->renderView('employe/dashboard', [
            'title' => 'Mon tableau de bord',
            'stats' => [
                'presents_ce_mois' => $presents,
                'retards_ce_mois' => $retards,
                'absents_ce_mois' => $absents,
                'solde_conge' => $soldeConge,
                'presence_today' => $presenceToday,
            ],
        ]);
    }
}