<?php
declare(strict_types=1);

namespace App\Controllers\Agent;

use App\Controllers\BaseController;

/**
 * Tableau de bord agent
 */
class DashboardController extends BaseController
{
    /**
     * Affichage du tableau de bord (redirige vers vue temps réel)
     */
    public function index(): \CodeIgniter\HTTP\RedirectResponse
    {
        return redirect()->to(site_url('shared/realtime'));
    }
}