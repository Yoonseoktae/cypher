<?php

namespace App\Controllers\Api;

class SettlementController extends BaseApiController
{
    /**
     * 정산 스냅샷 데이터 조회
     * GET /api/v1/settlement/snapshot
     */
    public function getSnapshot()
    {
        $role = session()->get('role');
        if ($role != 99) {
            return $this->fail('접근 권한이 없습니다.', 403);
        }
        
        $data = $this->getRequestData();
        $agencyId = $data['agency_id'] ?? null;
        $year = $data['year'] ?? date('Y');
        $month = $data['month'] ?? date('m');
        
        if (!$agencyId) {
            return $this->fail('대리점을 선택해주세요.', 400);
        }
        
        $snapshotModel = new \App\Models\SettlementSnapshotModel();
        $snapshot = $snapshotModel->getSnapshot($agencyId, $year, $month);
        
        if (!$snapshot) {
            return $this->respond([
                'status' => 'success',
                'data' => [
                    'total_users' => 0,
                    'new_users' => 0,
                    'settlement_users' => 0,
                    'settlement_amount' => 0,
                    'has_snapshot' => false,
                    'message' => '해당 월의 정산 데이터가 없습니다. 매월 20일에 자동 생성됩니다.'
                ]
            ]);
        }
        
        // settlement_users 테이블에서 실제 인원 카운트
        $db = \Config\Database::connect();
        $actualCount = $db->table('settlement_users')
                          ->where('snapshot_id', $snapshot['id'])
                          ->countAllResults();
        
        $settlementStart = date('Y-m-21 00:00:00', strtotime("{$year}-{$month}-01 -1 month"));
        $settlementEnd = date('Y-m-20 23:59:59', strtotime("{$year}-{$month}-01"));
        
        return $this->respond([
            'status' => 'success',
            'data' => [
                'total_users' => $snapshot['total_users'],
                'new_users' => $actualCount, // 실제 저장된 회원 수
                'settlement_users' => $actualCount,
                'settlement_amount' => $actualCount * 70000, // 실제 인원 기준 재계산
                'snapshot_date' => $snapshot['snapshot_date'],
                'settlement_period' => [
                    'start' => $settlementStart,
                    'end' => $settlementEnd
                ],
                'has_snapshot' => true
            ]
        ]);
    }

    /**
     * 정산 대상 회원 리스트
     * GET /api/v1/settlement/users
     */
    public function getUsers()
    {
        $role = session()->get('role');
        if ($role != 99) {
            return $this->fail('접근 권한이 없습니다.', 403);
        }
        
        $data = $this->getRequestData();
        $agencyId = $data['agency_id'] ?? null;
        $year = $data['year'] ?? date('Y');
        $month = $data['month'] ?? date('m');
        
        if (!$agencyId) {
            return $this->fail('대리점을 선택해주세요.', 400);
        }
        
        $snapshotModel = new \App\Models\SettlementSnapshotModel();
        $snapshot = $snapshotModel->getSnapshot($agencyId, $year, $month);
        
        if (!$snapshot) {
            return $this->respond([
                'status' => 'success',
                'data' => []
            ]);
        }
        
        $db = \Config\Database::connect();
        $users = $db->table('settlement_users')
                    ->where('snapshot_id', $snapshot['id'])
                    ->orderBy('registration_date', 'DESC')
                    ->get()
                    ->getResultArray();
        
        return $this->respond([
            'status' => 'success',
            'data' => $users
        ]);
    }
}