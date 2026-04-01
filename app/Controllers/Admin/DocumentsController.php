<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\DocumentRHModel;
use App\Models\EmployeModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface as HttpResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Gestion des documents RH (admin)
 */
class DocumentsController extends BaseController
{
    protected DocumentRHModel $documentModel;
    protected EmployeModel $employeModel;

    public function initController(RequestInterface $request, HttpResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);

        $this->documentModel = model(DocumentRHModel::class);
        $this->employeModel   = model(EmployeModel::class);

        // Ensure the upload folder exists
        $this->documentModel->ensureUploadDirectory();
    }

    /**
     * Liste des documents
     */
    public function index(): string
    {
        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 15;

        $search = trim((string) $this->request->getGet('search'));
        $type = trim((string) $this->request->getGet('type'));
        $employeId = $this->request->getGet('employe_id');

        $builder = $this->documentModel->builder();
        $builder->select('documents_rh.*, employes.nom AS employe_nom, employes.prenom AS employe_prenom')
            ->join('employes', 'employes.id = documents_rh.employe_id', 'left');

        $uploaderColumn = $this->documentModel->getUploaderColumn();
        if ($uploaderColumn !== null) {
            $builder->select('utilisateurs.email AS uploader_email')
                ->join('utilisateurs', 'utilisateurs.id = documents_rh.' . $uploaderColumn, 'left');
        } else {
            $builder->select("'' AS uploader_email", false);
        }

        if ($search !== '') {
            $builder->groupStart()
                ->like('documents_rh.titre', $search)
                ->orLike('documents_rh.type', $search)
                ->orLike('documents_rh.description', $search)
                ->groupEnd();
        }

        if ($type !== '') {
            $builder->like('documents_rh.type', $type);
        }

        if ($employeId !== null && $employeId !== '') {
            $builder->where('documents_rh.employe_id', (int) $employeId);
        }

        $total = $builder->countAllResults(false);
        $documents = $builder
            ->orderBy('documents_rh.date_creation', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()
            ->getResult('array');

        $employees = $this->employeModel->findAll();

        return $this->renderView('admin/documents/index', [
            'title'      => 'Documents RH',
            'documents'  => $documents,
            'employees'  => $employees,
            'filters'    => [
                'search'     => $search,
                'type'       => $type,
                'employe_id' => $employeId,
            ],
            'pager'      => [
                'currentPage' => $page,
                'perPage'     => $perPage,
                'total'       => $total,
                'lastPage'    => (int) ceil($total / $perPage),
            ],
        ]);
    }

    /**
     * Formulaire d'ajout
     */
    public function create(): string
    {
        $employees = $this->employeModel->findAll();

        return $this->renderView('admin/documents/create', [
            'title'     => 'Ajouter un document',
            'employees' => $employees,
        ]);
    }

    /**
     * Enregistrement du document
     */
    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $rules = [
            'titre'       => 'required|min_length[3]|max_length[255]',
            'type'        => 'required|min_length[2]|max_length[50]',
            'fichier'     => 'uploaded[fichier]|max_size[fichier,5120]|ext_in[fichier,pdf,jpg,jpeg,png]',
            'description' => 'permit_empty|max_length[1000]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $file = $this->request->getFile('fichier');
        if (! $file->isValid()) {
            return redirect()->back()->withInput()->with('error', 'Fichier invalide.');
        }

        $newName = $file->getRandomName();
        $file->move($this->documentModel->getUploadDir(), $newName);

        $data = [
            'titre'        => $this->request->getPost('titre'),
            'type'         => $this->request->getPost('type'),
            'fichier'      => $newName,
            'chemin_fichier' => 'uploads/documents/' . $newName,
            'description'  => $this->request->getPost('description'),
            'uploadé_par'  => $this->currentUser['user_id'] ?? null,
            'uploade_par'  => $this->currentUser['user_id'] ?? null,
            'uploaded_by'  => $this->currentUser['user_id'] ?? null,
            'date_creation' => date('Y-m-d H:i:s'),
            'date_upload'  => date('Y-m-d H:i:s'),
            'nom_original' => $file->getClientName(),
            'taille_octets' => $file->getSize(),
        ];

        $employeId = $this->request->getPost('employe_id');
        if ($employeId !== null && $employeId !== '') {
            $data['employe_id'] = (int) $employeId;
        }

        $this->documentModel->insert($this->documentModel->filterToExistingColumns($data));

        return redirect()->to('/admin/documents')->with('success', 'Document ajouté avec succès.');
    }

    /**
     * Formulaire d'édition d'un document
     */
    public function edit(int $id): string
    {
        $document = $this->documentModel->find($id);
        if (! $document) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $employees = $this->employeModel->findAll();

        return $this->renderView('admin/documents/edit', [
            'title'    => 'Modifier le document',
            'document' => $document,
            'employees' => $employees,
        ]);
    }

    /**
     * Mise à jour d'un document
     */
    public function update(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $document = $this->documentModel->find($id);
        if (! $document) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $rules = [
            'titre'       => 'required|min_length[3]|max_length[255]',
            'type'        => 'required|min_length[2]|max_length[50]',
            'description' => 'permit_empty|max_length[1000]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'titre'           => $this->request->getPost('titre'),
            'type'            => $this->request->getPost('type'),
            'description'     => $this->request->getPost('description'),
            'date_modification' => date('Y-m-d H:i:s'),
        ];

        $employeId = $this->request->getPost('employe_id');
        $data['employe_id'] = ($employeId !== null && $employeId !== '') ? (int) $employeId : null;

        $file = $this->request->getFile('fichier');
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            // Delete old file
            $oldPath = $this->documentModel->getFilePath($document['fichier']);
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }

            $newName = $file->getRandomName();
            $file->move($this->documentModel->getUploadDir(), $newName);
            $data['fichier'] = $newName;
            $data['chemin_fichier'] = 'uploads/documents/' . $newName;
            $data['nom_original'] = $file->getClientName();
            $data['taille_octets'] = $file->getSize();
        }

        $this->documentModel->update($id, $this->documentModel->filterToExistingColumns($data));

        return redirect()->to('/admin/documents')->with('success', 'Document mis à jour avec succès.');
    }

    /**
     * Téléchargement du document
     */
    public function download(int $id): ResponseInterface
    {
        $document = $this->documentModel->find($id);
        if (! $document) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $filePath = $this->documentModel->ensureDownloadableFile($document);
        if ($filePath === null || ! is_file($filePath)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $downloadName = $this->documentModel->getDownloadFilename($document, $filePath);

        return $this->response->download($filePath, null)->setFileName($downloadName);
    }

    /**
     * Suppression d'un document
     */
    public function delete(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        if (! $this->documentModel->deleteWithFile($id)) {
            return redirect()->back()->with('error', 'Impossible de supprimer le document.');
        }

        return redirect()->to('/admin/documents')->with('success', 'Document supprimé avec succès.');
    }
}
