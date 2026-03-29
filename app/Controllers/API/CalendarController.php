<?php
declare(strict_types=1);

namespace App\Controllers\API;

use App\Models\JourFerieModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;

class CalendarController extends Controller
{
    use ResponseTrait;

    public function holidays()
    {
        $year = (int) ($this->request->getGet('year') ?? date('Y'));
        if ($year < 2000 || $year > 2100) {
            $year = (int) date('Y');
        }

        $model = model(JourFerieModel::class);
        $rows = $model->where('annee', $year)->orderBy('date_ferie', 'ASC')->findAll();

        $holidays = array_map(static fn(array $row): array => [
            'id' => (int) $row['id'],
            'date' => (string) $row['date_ferie'],
            'name' => (string) $row['designation'],
            'type' => (string) $row['type'],
        ], $rows);

        return $this->respond([
            'success' => true,
            'year' => $year,
            'holidays' => $holidays,
        ]);
    }
}
