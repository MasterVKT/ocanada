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
            ->groupStart()
            ->where('documents_rh.employe_id IS NULL', null, false)
            ->orWhere('documents_rh.employe_id', (int) $employeId)
            ->groupEnd();

        $documents = $builder->orderBy('documents_rh.date_creation', 'DESC')->get()->getResult('array');

        return $this->renderView('employe/documents/index', [
            'title'     => 'Mes documents',
            'documents' => $documents,
        ]);
    }

    /**
     * Téléchargement d'un document (doit être accessible à l'utilisateur)
     */
    public function download(int $id): ResponseInterface
    {
        $document = $this->documentModel->find($id);
        if (! $document) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $employeId = $this->currentUser['employe_id'] ?? null;
        if ($document['employe_id'] !== null && $document['employe_id'] !== $employeId) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $filePath = $this->documentModel->ensureDownloadableFile($document);
        if ($filePath === null || ! is_file($filePath)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $downloadName = $this->documentModel->getDownloadFilename($document, $filePath);

        return $this->response->download($filePath, null)->setFileName($downloadName);
    }
}
