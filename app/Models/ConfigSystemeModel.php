<?php
declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class ConfigSystemeModel extends Model
{
    protected $table          = 'config_systeme';
    protected $primaryKey     = 'cle';
    protected $returnType     = 'array';
    protected $useAutoIncrement = false;
    protected $useSoftDeletes = false;
    protected $useTimestamps  = false;

    protected $allowedFields  = [
        'cle',
        'valeur',
        'description',
    ];

    protected $validationRules = [
        'cle'    => 'required|string|max_length[100]',
        'valeur' => 'permit_empty|string',
    ];

    public function get(string $key, ?string $default = null): ?string
    {
        $row = $this->find($key);

        if ($row === null) {
            return $default;
        }

        return $row['valeur'];
    }
}

