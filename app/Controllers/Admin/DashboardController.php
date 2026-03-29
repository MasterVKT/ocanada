<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EmployeModel;
use App\Models\PresenceModel;

/**
 * Tableau de bord administrateur
 */
class DashboardController extends BaseController
{
    protected EmployeModel $employeModel;
    protected PresenceModel $presenceModel;

    public function __construct()
    {
        $this->employeModel  = model(EmployeModel::class);
        $this->presenceModel = model(PresenceModel::class);
    }

    /**
     * Affichage du tableau de bord
     */
    public function index(): string
    {
        $today = date('Y-m-d');
        $currentMonth = date('Y-m');

        // Statistiques générales
        $totalEmployes = $this->employeModel->where('statut', 'actif')->countAllResults();
        $nouveauxCeMois = $this->employeModel
            ->where('statut', 'actif')
            ->where('DATE(date_embauche) >=', $currentMonth . '-01')
            ->countAllResults();

        // Présences aujourd'hui
        $presencesToday = $this->presenceModel->getByDate($today);
        $presentsToday = count(array_filter($presencesToday, fn($p) => $p['statut'] === 'present'));
        $retardsToday = count(array_filter($presencesToday, fn($p) => $p['statut'] === 'retard'));
        $absentsToday = $totalEmployes - $presentsToday - $retardsToday;

        // Données pour graphiques (simplifié)
        $presenceData = [
            'labels' => ['Présents', 'Retards', 'Absents'],
            'data' => [$presentsToday, $retardsToday, $absentsToday],
            'colors' => ['#28a745', '#ffc107', '#dc3545']
        ];

        return $this->renderView('admin/dashboard', [
            'title' => 'Tableau de bord administrateur',
            'stats' => [
                'total_employes' => $totalEmployes,
                'nouveaux_ce_mois' => $nouveauxCeMois,
                'presents_aujourdhui' => $presentsToday,
                'retards_aujourdhui' => $retardsToday,
                'absents_aujourdhui' => $absentsToday,
            ],
            'presenceData' => $presenceData,
        ]);
    }
}