<?php

namespace App\Models;

use CodeIgniter\Model;

class NoticeModel extends Model
{
    protected $table = 'notices';
    protected $primaryKey = 'id';
    protected $allowedFields = ['agency_id', 'title', 'content', 'is_pinned', 'created_by'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    /**
     * 대리점별 고정 공지사항 조회
     */
    public function getActiveNotice($agencyId)
    {
        return $this->where('agency_id', $agencyId)
                    ->where('is_pinned', 1)
                    ->orderBy('created_at', 'DESC')
                    ->first();
    }
    
    /**
     * 대리점별 공지사항 목록
     */
    public function getNoticesByAgency($agencyId)
    {
        return $this->where('agency_id', $agencyId)
                    ->orderBy('is_pinned', 'DESC')
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }
}