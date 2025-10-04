<?php

namespace App\Models;

use CodeIgniter\Model;

class SettlementSnapshotModel extends Model
{
    protected $table = 'settlement_snapshots';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'agency_id',
        'year',
        'month',
        'total_users',
        'new_users',
        'extended_users',
        'settlement_amount',
        'snapshot_date',
        'created_at'
    ];
    
    protected $useTimestamps = false;

    /**
     * 특정 월 스냅샷 조회
     */
    public function getSnapshot($agencyId, $year, $month)
    {
        return $this->where('agency_id', $agencyId)
                    ->where('year', $year)
                    ->where('month', $month)
                    ->first();
    }

    /**
     * 대리점별 스냅샷 목록
     */
    public function getAgencySnapshots($agencyId)
    {
        return $this->where('agency_id', $agencyId)
                    ->orderBy('year', 'DESC')
                    ->orderBy('month', 'DESC')
                    ->findAll();
    }
}