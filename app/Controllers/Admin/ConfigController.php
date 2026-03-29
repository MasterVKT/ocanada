<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ConfigSystemeModel;
use App\Models\JourFerieModel;
use CodeIgniter\HTTP\RedirectResponse;

class ConfigController extends BaseController
{
    protected ConfigSystemeModel $configModel;
    protected JourFerieModel $jourFerieModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request,
                                   \CodeIgniter\HTTP\ResponseInterface $response,
                                   \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->configModel = model(ConfigSystemeModel::class);
        $this->jourFerieModel = model(JourFerieModel::class);
    }

    public function index(): string
    {
        $year = (int) ($this->request->getGet('annee') ?? date('Y'));
        if ($year < 2000 || $year > 2100) {
            $year = (int) date('Y');
        }

        $configs = $this->configModel->orderBy('cle', 'ASC')->findAll();
        $configMap = [];
        foreach ($configs as $config) {
            $configMap[(string) $config['cle']] = $config;
        }

        $holidays = $this->jourFerieModel
            ->where('annee', $year)
            ->orderBy('date_ferie', 'ASC')
            ->findAll();

        return $this->renderView('admin/config/index', [
            'title' => 'Configuration systeme',
            'year' => $year,
            'configMap' => $configMap,
            'configs' => $configs,
            'holidays' => $holidays,
        ]);
    }

    public function update(): RedirectResponse
    {
        $payload = [
            'ip_kiosque_autorisees' => trim((string) $this->request->getPost('ip_kiosque_autorisees')),
            'heure_debut_travail' => trim((string) $this->request->getPost('heure_debut_travail')),
            'heure_fin_travail' => trim((string) $this->request->getPost('heure_fin_travail')),
            'jours_ouvrables' => trim((string) $this->request->getPost('jours_ouvrables')),
            'anthropic_api_key' => trim((string) $this->request->getPost('anthropic_api_key')),
        ];

        if ($payload['heure_debut_travail'] !== '' && ! preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $payload['heure_debut_travail'])) {
            return redirect()->back()->withInput()->with('error', 'Heure de debut invalide (HH:MM).');
        }

        if ($payload['heure_fin_travail'] !== '' && ! preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $payload['heure_fin_travail'])) {
            return redirect()->back()->withInput()->with('error', 'Heure de fin invalide (HH:MM).');
        }

        if (! $this->isValidIpList($payload['ip_kiosque_autorisees'])) {
            return redirect()->back()->withInput()->with('error', 'Liste IP kiosque invalide. Utiliser des IPv4/IPv6 separees par des virgules.');
        }

        if (! $this->isValidWorkingDays($payload['jours_ouvrables'])) {
            return redirect()->back()->withInput()->with('error', 'Jours ouvrables invalides. Utiliser une liste unique parmi 1,2,3,4,5,6,7.');
        }

        if ($payload['heure_debut_travail'] !== '' && $payload['heure_fin_travail'] !== '' && $payload['heure_debut_travail'] >= $payload['heure_fin_travail']) {
            return redirect()->back()->withInput()->with('error', 'L heure de fin doit etre posterieure a l heure de debut.');
        }

        foreach ($payload as $key => $value) {
            if ($key === 'anthropic_api_key' && $value === '') {
                continue;
            }

            $existing = $this->configModel->find($key);
            $data = [
                'cle' => $key,
                'valeur' => $value,
                'description' => $existing['description'] ?? null,
            ];

            if ($existing === null) {
                $this->configModel->insert($data);
            } else {
                $this->configModel->update($key, $data);
            }
        }

        $this->auditLog('CONFIG_MAJ', 'Mise a jour des parametres systeme.');

        return redirect()->to('/admin/configuration')->with('success', 'Configuration mise a jour avec succes.');
    }

    public function addHoliday(): RedirectResponse
    {
        $rules = [
            'date_ferie' => 'required|valid_date',
            'designation' => 'required|max_length[100]',
            'type' => 'required|in_list[fixe,variable]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Jour ferie invalide.');
        }

        $date = (string) $this->request->getPost('date_ferie');
        $designation = trim((string) $this->request->getPost('designation'));
        $type = (string) $this->request->getPost('type');
        $annee = (int) date('Y', strtotime($date));

        $this->jourFerieModel->insert([
            'date_ferie' => $date,
            'designation' => $designation,
            'type' => $type,
            'annee' => $annee,
        ]);

        $this->auditLog('AJOUT_JOUR_FERIE', sprintf('Ajout du jour ferie %s (%s)', $date, $designation));

        return redirect()->to('/admin/configuration?annee=' . $annee)->with('success', 'Jour ferie ajoute.');
    }

    public function updateHoliday(int $id): RedirectResponse
    {
        $holiday = $this->jourFerieModel->find($id);
        if ($holiday === null) {
            return redirect()->back()->with('error', 'Jour ferie introuvable.');
        }

        $rules = [
            'date_ferie' => 'required|valid_date',
            'designation' => 'required|max_length[100]',
            'type' => 'required|in_list[fixe,variable]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Modification du jour ferie invalide.');
        }

        $date = (string) $this->request->getPost('date_ferie');
        $designation = trim((string) $this->request->getPost('designation'));
        $type = (string) $this->request->getPost('type');
        $annee = (int) date('Y', strtotime($date));

        $duplicate = $this->jourFerieModel
            ->where('date_ferie', $date)
            ->where('id !=', $id)
            ->first();

        if ($duplicate !== null) {
            return redirect()->back()->withInput()->with('error', 'Un jour ferie existe deja pour cette date.');
        }

        $before = [
            'date_ferie' => (string) $holiday['date_ferie'],
            'designation' => (string) $holiday['designation'],
            'type' => (string) $holiday['type'],
            'annee' => (int) $holiday['annee'],
        ];

        $after = [
            'date_ferie' => $date,
            'designation' => $designation,
            'type' => $type,
            'annee' => $annee,
        ];

        $this->jourFerieModel->update($id, $after);

        $this->auditLog('MAJ_JOUR_FERIE', sprintf('Modification du jour ferie %s', $designation), $before, $after);

        return redirect()->to('/admin/configuration?annee=' . $annee)->with('success', 'Jour ferie mis a jour.');
    }

    public function deleteHoliday(int $id): RedirectResponse
    {
        $holiday = $this->jourFerieModel->find($id);
        if ($holiday === null) {
            return redirect()->back()->with('error', 'Jour ferie introuvable.');
        }

        $this->jourFerieModel->delete($id);

        $this->auditLog('SUPPRESSION_JOUR_FERIE', sprintf('Suppression du jour ferie %s (%s)', (string) $holiday['date_ferie'], (string) $holiday['designation']));

        return redirect()->to('/admin/configuration?annee=' . (int) $holiday['annee'])->with('success', 'Jour ferie supprime.');
    }

    private function isValidIpList(string $rawValue): bool
    {
        if ($rawValue === '') {
            return true;
        }

        $items = array_filter(array_map('trim', explode(',', $rawValue)), static fn(string $item): bool => $item !== '');

        foreach ($items as $item) {
            if (filter_var($item, FILTER_VALIDATE_IP) === false) {
                return false;
            }
        }

        return true;
    }

    private function isValidWorkingDays(string $rawValue): bool
    {
        if ($rawValue === '') {
            return true;
        }

        $items = array_filter(array_map('trim', explode(',', $rawValue)), static fn(string $item): bool => $item !== '');

        if ($items === []) {
            return false;
        }

        $unique = array_unique($items);
        if (count($unique) !== count($items)) {
            return false;
        }

        foreach ($items as $item) {
            if (! in_array($item, ['1', '2', '3', '4', '5', '6', '7'], true)) {
                return false;
            }
        }

        return true;
    }
}
