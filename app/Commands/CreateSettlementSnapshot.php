<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CreateSettlementSnapshot extends BaseCommand
{
    protected $group = 'Settlement';
    protected $name = 'settlement:snapshot';
    protected $description = '매월 20일 정산 스냅샷 생성';

    public function run(array $params)
    {
        $agencyModel = new \App\Models\AgencyModel();
        $userModel = new \App\Models\UserModel();
        $snapshotModel = new \App\Models\SettlementSnapshotModel();
        
        // 현재 시점 (20일 23:59:59 가정)
        $snapshotDate = date('Y-m-d H:i:s');
        $year = date('Y');
        $month = date('m');
        
        // 정산 기간: 전월 21일 00:00:00 ~ 당월 20일 23:59:59
        $currentDay = date('d');
        
        if ($currentDay >= 21) {
            // 21일 이후: 당월 21일 ~ 다음달 20일
            $startDate = date('Y-m-21 00:00:00');
            $endDate = date('Y-m-20 23:59:59', strtotime('+1 month'));
        } else {
            // 20일 이전: 전월 21일 ~ 당월 20일
            $startDate = date('Y-m-21 00:00:00', strtotime('-1 month'));
            $endDate = date('Y-m-20 23:59:59');
        }
        
        CLI::write("정산 스냅샷 생성: {$year}년 {$month}월", 'green');
        CLI::write("정산 기간: {$startDate} ~ {$endDate}", 'yellow');
        
        $agencies = $agencyModel->where('status', 1)->findAll();
        
        foreach ($agencies as $agency) {
            // 스냅샷 시점 기준 유효한 회원
            $totalUsers = $userModel->where('agency_id', $agency['id'])
                                    ->where('expiry_date >=', $snapshotDate)
                                    ->countAllResults();
            
            // 정산 기간 내 신규 회원
            $newUsers = $userModel->where('agency_id', $agency['id'])
                                  ->where('registration_date >=', $startDate)
                                  ->where('registration_date <=', $endDate)
                                  ->where('expiry_date >=', $snapshotDate)
                                  ->where('status', 1)
                                  ->countAllResults();
            
            // 정산 기간 내 연장 회원
            $extendedUsers = $userModel->where('agency_id', $agency['id'])
                                       ->where('registration_date >=', $startDate)
                                       ->where('registration_date <=', $endDate)
                                       ->where('expiry_date >=', $snapshotDate)
                                       ->where('status', 2)
                                       ->countAllResults();
            
            $settlementAmount = $totalUsers * 70000;
            
            $existing = $snapshotModel->getSnapshot($agency['id'], $year, $month);
            
            $data = [
                'agency_id' => $agency['id'],
                'year' => $year,
                'month' => $month,
                'total_users' => $totalUsers,
                'new_users' => $newUsers,
                'extended_users' => $extendedUsers,
                'settlement_amount' => $settlementAmount,
                'snapshot_date' => $snapshotDate
            ];
            
            if ($existing) {
                $snapshotModel->update($existing['id'], $data);
                CLI::write("  - {$agency['name']}: 업데이트 (유효: {$totalUsers}명, 금액: " . number_format($settlementAmount) . "원)", 'yellow');
            } else {
                $snapshotModel->insert($data);
                CLI::write("  - {$agency['name']}: 생성 (유효: {$totalUsers}명, 금액: " . number_format($settlementAmount) . "원)", 'blue');
            }
        }
        
        CLI::write("정산 스냅샷 생성 완료!", 'green');
    }
}