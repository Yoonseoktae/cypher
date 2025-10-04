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
        $db = \Config\Database::connect();
        
        $snapshotDate = date('Y-m-d H:i:s');
        $year = date('Y');
        $month = date('m');
        
        // 정산 기간: 전월 21일 00:00:00 ~ 당월 20일 23:59:59
        $currentDay = date('d');
        
        if ($currentDay >= 21) {
            $startDate = date('Y-m-21 00:00:00');
            $endDate = date('Y-m-20 23:59:59', strtotime('+1 month'));
        } else {
            $startDate = date('Y-m-21 00:00:00', strtotime('-1 month'));
            $endDate = date('Y-m-20 23:59:59');
        }
        
        CLI::write("정산 스냅샷 생성: {$year}년 {$month}월", 'green');
        CLI::write("정산 기간: {$startDate} ~ {$endDate}", 'yellow');
        CLI::write("스냅샷 시점: {$snapshotDate}", 'cyan');
        
        $agencies = $agencyModel->where('status', 1)->findAll();
        
        foreach ($agencies as $agency) {
            // 전체 유효 회원 (스냅샷 시점 기준)
            $totalUsers = $userModel->where('agency_id', $agency['id'])
                                    ->where('expiry_date >=', $snapshotDate)
                                    ->countAllResults();
            
            // 정산 대상 회원 조회 (정산 기간 내 등록 + 스냅샷 시점 유효)
            $settlementUsers = $userModel->where('agency_id', $agency['id'])
                                        ->where('registration_date >=', $startDate)
                                        ->where('registration_date <=', $endDate)
                                        ->where('expiry_date >=', $snapshotDate)
                                        ->select('id, name, phone_number, registration_date, expiry_date, status')
                                        ->findAll();
            
            $newUsers = count($settlementUsers);
            $settlementAmount = $newUsers * 70000;
            
            $existing = $snapshotModel->getSnapshot($agency['id'], $year, $month);
            
            $data = [
                'agency_id' => $agency['id'],
                'year' => $year,
                'month' => $month,
                'total_users' => $totalUsers,
                'new_users' => $newUsers,
                'extended_users' => 0,
                'settlement_amount' => $settlementAmount,
                'snapshot_date' => $snapshotDate
            ];
            
            if ($existing) {
                $snapshotId = $existing['id'];
                $snapshotModel->update($snapshotId, $data);
                CLI::write("  - {$agency['name']}: 업데이트", 'yellow');
            } else {
                $snapshotId = $snapshotModel->insert($data);
                CLI::write("  - {$agency['name']}: 생성", 'blue');
            }
            
            // 기존 회원 리스트 삭제 후 재저장
            $db->table('settlement_users')->where('snapshot_id', $snapshotId)->delete();
            
            if (count($settlementUsers) > 0) {
                foreach ($settlementUsers as $user) {
                    $db->table('settlement_users')->insert([
                        'snapshot_id' => $snapshotId,
                        'user_id' => $user['id'],
                        'name' => $user['name'],
                        'phone_number' => $user['phone_number'],
                        'registration_date' => $user['registration_date'],
                        'expiry_date' => $user['expiry_date'],
                        'status' => $user['status']
                    ]);
                }
            }
            
            CLI::write("    전체유효: {$totalUsers}명, 정산대상: {$newUsers}명, 정산액: " . number_format($settlementAmount) . "원", 'green');
            
            // 디버깅: 정산 대상 회원 출력
            if ($newUsers > 0) {
                CLI::write("    정산 대상 회원:", 'white');
                foreach ($settlementUsers as $user) {
                    CLI::write("      - {$user['name']} ({$user['phone_number']}) 등록: {$user['registration_date']}, 만료: {$user['expiry_date']}", 'white');
                }
            }
        }
        
        CLI::write("\n정산 스냅샷 생성 완료!", 'green');
    }

}