<?php
declare(strict_types=1);

namespace App\Libraries;

use App\Models\AffectationShiftModel;
use App\Models\ConfigSystemeModel;
use CodeIgniter\I18n\Time;

/**
 * Calculateur de présences et statuts
 */
class PresenceCalculator
{
    protected ConfigSystemeModel $configModel;
    protected AffectationShiftModel $shiftModel;

    public function __construct()
    {
        $this->configModel = model(ConfigSystemeModel::class);
        $this->shiftModel  = model(AffectationShiftModel::class);
    }

    /**
     * Calcule le statut d'une présence (présent, retard, absent)
     */
    public function calculateStatus(array $presence, ?array $shift = null): string
    {
        $heureArrivee = $presence['heure_pointage'] ?? null;
        $heureDepart  = $presence['heure_sortie'] ?? null;

        if ($shift === null) {
            $shift = $this->getShiftForEmployee($presence['employe_id'], $presence['date_pointage']);
        }

        if (!$shift) {
            // Pas de shift défini, utiliser config par défaut
            $heureDebut = $this->configModel->getValue('heure_debut_pointage_arrivee') ?: '08:00';
            $heureFin   = $this->configModel->getValue('heure_fin_pointage_arrivee') ?: '10:30';
        } else {
            $heureDebut = $shift['heure_debut'];
            $heureFin   = date('H:i', strtotime($shift['heure_debut']) + 2 * 3600); // +2h pour tolérance
        }

        if ($heureArrivee === null) {
            return 'absent';
        }

        $heureArriveeTime = strtotime($heureArrivee);
        $heureDebutTime   = strtotime($heureDebut);
        $heureFinTime     = strtotime($heureFin);

        if ($heureArriveeTime <= $heureFinTime) {
            return $heureArriveeTime <= $heureDebutTime ? 'present' : 'retard';
        }

        return 'absent';
    }

    /**
     * Calcule les minutes de retard
     */
    public function calculateRetardMinutes(array $presence, ?array $shift = null): int
    {
        $heureArrivee = $presence['heure_pointage'] ?? null;

        if ($shift === null) {
            $shift = $this->getShiftForEmployee($presence['employe_id'], $presence['date_pointage']);
        }

        if (!$shift || !$heureArrivee) {
            return 0;
        }

        $heureArriveeTime = strtotime($heureArrivee);
        $heureDebutTime   = strtotime($shift['heure_debut']);

        if ($heureArriveeTime <= $heureDebutTime) {
            return 0;
        }

        return (int) (($heureArriveeTime - $heureDebutTime) / 60);
    }

    /**
     * Récupère le shift affecté à un employé pour une date donnée
     */
    protected function getShiftForEmployee(int $employeId, string $date): ?array
    {
        return $this->shiftModel->getShiftForEmployeeOnDate($employeId, $date);
    }

    /**
     * Marque les absences pour une journée
     */
    public function markAbsencesForDate(string $date): void
    {
        // Logique pour marquer absent ceux qui n'ont pas pointé
        // À implémenter avec les modèles
    }
}