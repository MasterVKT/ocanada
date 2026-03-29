<?php
declare(strict_types=1);

namespace App\Controllers\Employe;

use App\Controllers\BaseController;
use App\Models\DocumentRHModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Affiche les documents accessibles à l'employé
 */
class DocumentsController extends BaseController
{
    protected DocumentRHModel $documentModel;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);

        $this->documentModel = model(DocumentRHModel::class);
    }

    /**
     * Liste des documents RH pour l'utilisateur connecté
     */
    public function index(): string
    {
        $employeId = $this->currentUser['employe_id'] ?? null;

        $builder = $this->documentModel->builder();
        $builder->select('documents_rh.*')
            ->where('(documents_rh.employe_id IS NULL OR documents_rh.employe_id = ' . (int) $employeId . ')');

        $documents = $builder->orderBy('documents_rh.date_creation', 'DESC')->get()->getResult('array');

        return $this->renderView('employe/documents/index', [
            'title'     => 'Mes documents',
            'documents' => $documents,
        ]);
    }

    /**
     * Téléchargement d'un document (doit être accessible à l'utilisateur)
     */
    public function download(int $id)
    {
        $document = $this->documentModel->find($id);
        if (! $document) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $employeId = $this->currentUser['employe_id'] ?? null;
        if ($document['employe_id'] !== null && $document['employe_id'] !== $employeId) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $filePath = $this->documentModel->getFilePath($document['fichier']);
        if (! is_file($filePath)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $extension = pathinfo($document['fichier'], PATHINFO_EXTENSION);
        $downloadName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $document['titre']);
        $downloadName = substr($downloadName, 0, 200) ?: 'document';
        $downloadName .= '.' . $extension;

        return $this->response->download($filePath, null)->setFileName($downloadName);
    }
}
