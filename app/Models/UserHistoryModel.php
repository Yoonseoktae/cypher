<?php

namespace App\Models;

use CodeIgniter\Model;

class UserHistoryModel extends Model
{
    protected $table = 'user_history';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    
    protected $allowedFields = [
        'user_id',
        'admin_id',
        'action',
        'field',
        'before_value',
        'after_value',
        'ip_address'
    ];
    
    protected $useTimestamps = false;
    protected $createdField = 'created_at';
    protected $updatedField = null; // update 시간 없음
}