<?php
declare(strict_types=1);

namespace App\Libraries;

use App\Models\JourFerieModel;
use CodeIgniter\I18n\Time;

/**
 * Calculateur de jours ouvrables selon les règles OHADA et camerounaises
 */
class WorkingDaysCalculator
{
    protected JourFerieModel $holidaysModel;

    public function __construct()
    {
        $this->holidaysModel = model(JourFerieModel::class);
    }

    /**
     * Calcule le nombre de jours ouvrables entre deux dates
     * Exclut les week-ends et jours fériés
     */
    public function calculateWorkingDays(string $startDate, string $endDate): int
    {
        $start = Time::createFromFormat('Y-m-d', $startDate);
        $end   = Time::createFromFormat('Y-m-d', $endDate);

        if ($start->isAfter($end)) {
            return 0;
        }

        $workingDays = 0;
        $current     = $start->clone();

        while ($current->isBefore($end) || $current->isSameDay($end)) {
            if ($this->isWorkingDay($current)) {
                $workingDays++;
            }
            $current = $current->addDays(1);
        }

        return $workingDays;
    }

    /**
     * Vérifie si une date est un jour ouvrable
     */
    public function isWorkingDay(Time $date): bool
    {
        // Exclure les week-ends (samedi et dimanche)
        if ($date->dayOfWeek >= 6) { // 6 = samedi, 0 = dimanche
            return false;
        }

        // Exclure les jours fériés
        $holidays = $this->holidaysModel->getHolidaysForYear((int) $date->format('Y'));
        foreach ($holidays as $holiday) {
            if ($date->format('Y-m-d') === $holiday['date_ferie']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Ajoute un nombre de jours ouvrables à une date
     */
    public function addWorkingDays(string $startDate, int $days): string
    {
        $current = Time::createFromFormat('Y-m-d', $startDate);
        $added   = 0;

        while ($added < $days) {
            $current = $current->addDays(1);
            if ($this->isWorkingDay($current)) {
                $added++;
            }
        }

        return $current->format('Y-m-d');
    }

    /**
     * Calcule la date de fin après un nombre de jours ouvrables
     */
    public function getEndDate(string $startDate, int $workingDays): string
    {
        return $this->addWorkingDays($startDate, $workingDays);
    }
}