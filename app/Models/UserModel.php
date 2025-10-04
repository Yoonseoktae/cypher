<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'agency_id',
        'name',
        'phone_number',
        'is_franchise',
        'location',
        'app_version',
        'app_service',
        'status',
        'login_at',
        'registration_date',
        'expiry_date'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * 만료된 회원 수 조회
     */
    public function getExpiredCount($agencyId = null)
    {
        $builder = $this->where('expiry_date <', date('Y-m-d'));
        
        if ($agencyId) {
            $builder->where('agency_id', $agencyId);
        }
        
        return $builder->countAllResults();
    }

    /**
     * 만료 임박 회원 수 조회 (7일 이내)
     */
    public function getExpiringSoonCount($agencyId = null)
    {
        $today = date('Y-m-d');
        $sevenDaysLater = date('Y-m-d', strtotime('+7 days'));
        
        $builder = $this->where('expiry_date >=', $today)
                        ->where('expiry_date <=', $sevenDaysLater);
        
        if ($agencyId) {
            $builder->where('agency_id', $agencyId);
        }
        
        return $builder->countAllResults();
    }

    /**
     * 이번주 신규 회원 수
     */
    public function getThisWeekNewCount($agencyId = null)
    {
        $startOfWeek = date('Y-m-d', strtotime('monday this week'));
        
        $builder = $this->where('registration_date >=', $startOfWeek);
        
        if ($agencyId) {
            $builder->where('agency_id', $agencyId);
        }
        
        return $builder->countAllResults();
    }

    /**
     * 최근 등록 회원 리스트
     */
    public function getRecentUsers($limit = 10, $agencyId = null)
    {
        $builder = $this->orderBy('registration_date', 'DESC');
        
        if ($agencyId) {
            $builder->where('agency_id', $agencyId);
        }
        
        return $builder->limit($limit)->findAll();
    }

    /**
     * 전화번호 + 대리점 중복 체크
     */
    public function isDuplicatePhone($phoneNumber, $agencyId, $excludeUserId = null)
    {
        $builder = $this->where('phone_number', $phoneNumber)
                        ->where('agency_id', $agencyId);
        
        if ($excludeUserId) {
            $builder->where('id !=', $excludeUserId);
        }
        
        return $builder->countAllResults() > 0;
    }
}