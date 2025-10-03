<?php

namespace App\Models;

use CodeIgniter\Model;

class AgencyModel extends Model
{
    protected $table = 'agency';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    
    protected $allowedFields = [
        'code',
        'name',
        'number',
        'address',
        'status'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getAgencyList()
    {
        return $this->select('id, name')
                    ->where('status', 1)
                    ->orderBy('name', 'ASC')
                    ->findAll();
    }
}