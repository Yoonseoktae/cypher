<?php

namespace App\Models;

use CodeIgniter\Model;

class AppLogModel extends Model
{
    protected $table = 'app_logs';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'phone_number',
        'app_version',
        'app_service',
        'content',
        'created_at'
    ];
    
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';

    /**
     * 로그 생성
     */
    public function createLog($data)
    {
        return $this->insert($data);
    }

    /**
     * 배치 삽입
     */
    public function createBatch($logs)
    {
        return $this->insertBatch($logs);
    }

    /**
     * 핸드폰번호별 로그 조회
     */
    public function getByPhone($phoneNumber, $limit = 100, $offset = 0)
    {
        return $this->where('phone_number', $phoneNumber)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit, $offset)
                    ->findAll();
    }

    /**
     * 서비스타입별 로그 조회
     */
    public function getByService($appService, $limit = 100, $offset = 0)
    {
        return $this->where('app_service', $appService)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit, $offset)
                    ->findAll();
    }

    /**
     * 핸드폰번호 + 서비스별 로그 조회
     */
    public function getByPhoneAndService($phoneNumber, $appService, $limit = 100, $offset = 0)
    {
        return $this->where('phone_number', $phoneNumber)
                    ->where('app_service', $appService)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit, $offset)
                    ->findAll();
    }

    /**
     * 기간별 로그 조회
     */
    public function getByDateRange($startDate, $endDate, $phoneNumber = null, $limit = 100, $offset = 0)
    {
        $builder = $this->where('created_at >=', $startDate)
                        ->where('created_at <=', $endDate);
        
        if ($phoneNumber) {
            $builder->where('phone_number', $phoneNumber);
        }
        
        return $builder->orderBy('created_at', 'DESC')
                       ->limit($limit, $offset)
                       ->findAll();
    }

    /**
     * 로그 내용 전문 검색 (FULLTEXT)
     */
    public function searchContent($keyword, $phoneNumber = null, $limit = 100, $offset = 0)
    {
        $builder = $this->db->table($this->table);
    
        // FULLTEXT 검색 - 올바른 방법
        $builder->select('*, MATCH(content) AGAINST("' . $this->db->escapeString($keyword) . '" IN NATURAL LANGUAGE MODE) as relevance', false);
        $builder->where('MATCH(content) AGAINST("' . $this->db->escapeString($keyword) . '" IN NATURAL LANGUAGE MODE)', null, false);
        
        if ($phoneNumber) {
            $builder->where('phone_number', $phoneNumber);
        }
        
        return $builder->orderBy('relevance', 'DESC')
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit, $offset)
                    ->get()
                    ->getResultArray();
    }

    /**
     * 로그 내용 LIKE 검색 (FULLTEXT가 안되는 경우)
     */
    public function searchContentLike($keyword, $phoneNumber = null, $limit = 100, $offset = 0)
    {
        $builder = $this->like('content', $keyword);
        
        if ($phoneNumber) {
            $builder->where('phone_number', $phoneNumber);
        }
        
        return $builder->orderBy('created_at', 'DESC')
                       ->limit($limit, $offset)
                       ->findAll();
    }

    /**
     * 30일 이상 지난 로그 삭제
     */
    public function deleteOldLogs($days = 30)
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        return $this->where('created_at <', $cutoffDate)->delete();
    }

    /**
     * 핸드폰번호별 로그 통계
     */
    public function getPhoneStats($phoneNumber, $days = 7)
    {
        $startDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        return $this->select('app_service, COUNT(*) as count')
                    ->where('phone_number', $phoneNumber)
                    ->where('created_at >=', $startDate)
                    ->groupBy('app_service')
                    ->findAll();
    }

    /**
     * 앱 버전별 통계
     */
    public function getVersionStats($days = 7)
    {
        $startDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        return $this->select('app_version, COUNT(*) as count')
                    ->where('created_at >=', $startDate)
                    ->groupBy('app_version')
                    ->orderBy('count', 'DESC')
                    ->findAll();
    }
}