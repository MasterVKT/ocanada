<?php
declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

/**
 * Représente les documents RH (contrats, attestations, etc.)
 */
class DocumentRHModel extends Model
{
    protected $table            = 'documents_rh';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'employe_id',
        'titre',
        'type',
        'fichier',
        'description',
        'uploadé_par',
        'date_creation',
        'date_modification',
    ];

    protected $useTimestamps = false;

    /**
     * Répertoire de stockage des fichiers (public)
     */
    public function getUploadDir(): string
    {
        return rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'documents';
    }

    /**
     * Chemin complet pour un fichier enregistré
     */
    public function getFilePath(string $filename): string
    {
        return $this->getUploadDir() . DIRECTORY_SEPARATOR . $filename;
    }

    /**
     * Ensure the upload directory exists and is writable.
     */
    public function ensureUploadDirectory(): void
    {
        $dir = $this->getUploadDir();
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    /**
     * Supprime un document et son fichier associé.
     */
    public function deleteWithFile(int $id): bool
    {
        $document = $this->find($id);
        if (! $document) {
            return false;
        }

        $filePath = $this->getFilePath($document['fichier']);
        if (is_file($filePath)) {
            @unlink($filePath);
        }

        return (bool) $this->delete($id);
    }
}
