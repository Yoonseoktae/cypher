<?php

namespace App\Controllers\Api;

use CodeIgniter\API\ResponseTrait;
use App\Models\AppLogModel;

class LogController extends BaseApiController
{
    use ResponseTrait;

    private $logModel;

    private $logPath = WRITEPATH . 'logs/app/'; // writable/logs/app/
    private $maxLogDays = 30; // 보관 기간 (일)

    public function __construct()
    {
        $this->logModel = new AppLogModel();

        // // 로그 디렉토리 생성
        // if (!is_dir($this->logPath)) {
        //     mkdir($this->logPath, 0755, true);
        // }
    }

    /**
     * 로그 저장 API
     * POST /api/v1/logs
     */
    public function create()
    {
        $data = $this->getRequestData();

        if (!isset($data['phone_number']) || empty($data['phone_number'])) {
            return $this->fail('핸드폰 번호가 필요합니다.', 400);
        }

        $data = [
            'phone_number' => $data['phone_number'],
            'app_version' => $data['app_version'] ?? null,
            'app_service' => $data['app_service'] ?? null,
            'content' => $data['content'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ];

        if ($this->logModel->createLog($data)) {
            return $this->respondCreated([
                'status' => 'success',
                'message' => '로그가 저장되었습니다.'
            ]);
        }

        return $this->fail('로그 저장에 실패했습니다.', 500);
    }

    /**
     * 로그 저장 API
     * POST /api/v1/logs
     */
    public function create_shell()
    {
        $data = $this->getRequestData();

        // 필수 필드 검증
        if (!isset($data['content']) || empty($data['content'])) {
            return $this->fail('로그 데이터가 필요합니다.', 400);
        }

        // 선택적 필드
        $phoneNumber = $data['phone_number'] ?? null;
        $appVersion = $data['app_version'] ?? null;
        $appService = $data['app_service'] ?? null;
        
        $logData = $data['content'];

        // 날짜별 파일명 생성
        $date = date('Y-m-d');
        $filename = "{$date}_{$appService}.log";
        $filepath = $this->logPath . $filename;

        // 로그 내용 포맷
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] [USER:{$phoneNumber}] [APPVER:{$appVersion}] {$logData}" . PHP_EOL;

        // 파일에 추가
        if (file_put_contents($filepath, $logEntry, FILE_APPEND | LOCK_EX) === false) {
            return $this->fail('로그 저장에 실패했습니다.', 500);
        }

        // 오래된 로그 삭제
        $this->cleanOldLogs();

        return $this->respondCreated([
            'status' => 'success',
            'message' => '로그가 저장되었습니다.',
            'file' => $filename
        ]);
    }

    /**
     * 30일 이상 지난 로그 파일 삭제
     */
    private function cleanOldLogs()
    {
        $files = glob($this->logPath . '*.log');
        $cutoffTime = strtotime("-{$this->maxLogDays} days");

        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $cutoffTime) {
                unlink($file);
            }
        }
    }

    /**
     * 로그 파일 목록 조회 (관리자용)
     * GET /api/v1/logs/files
     */
    public function files()
    {
        $files = glob($this->logPath . '*.log');
        $fileList = [];

        foreach ($files as $file) {
            $fileList[] = [
                'filename' => basename($file),
                'size' => filesize($file),
                'modified' => date('Y-m-d H:i:s', filemtime($file))
            ];
        }

        return $this->respond([
            'status' => 'success',
            'files' => $fileList,
            'total' => count($fileList)
        ]);
    }

    /**
     * 핸드폰번호별 로그 조회
     * GET /api/v1/logs/phone/{phone_number}
     */
    public function getByPhone($phoneNumber)
    {
        $data = $this->getRequestData();
        $limit = $data['limit'] ?? 100;
        $offset = $data['offset'] ?? 0;
        $service = $data['service'];

        if ($service) {
            $logs = $this->logModel->getByPhoneAndService($phoneNumber, $service, $limit, $offset);
        } else {
            $logs = $this->logModel->getByPhone($phoneNumber, $limit, $offset);
        }

        return $this->respond([
            'status' => 'success',
            'data' => $logs,
            'count' => count($logs)
        ]);
    }

    /**
     * 로그 내용 검색
     * GET /api/v1/logs/search
     */
    public function search()
    {
        $data = $this->getRequestData();
        $keyword = $data['keyword'];
        $phoneNumber = $data['phone_number'];
        $limit = $data['limit'] ?? 100;
        $offset = $data['offset'] ?? 0;

        if (!$keyword) {
            return $this->fail('검색 키워드가 필요합니다.', 400);
        }

        // FULLTEXT 검색 사용
        $logs = $this->logModel->searchContent($keyword, $phoneNumber, $limit, $offset);

        return $this->respond([
            'status' => 'success',
            'keyword' => $keyword,
            'data' => $logs,
            'count' => count($logs)
        ]);
    }

    /**
     * 핸드폰번호별 통계
     * GET /api/v1/logs/phone/{phone_number}/stats
     */
    public function getPhoneStats($phoneNumber)
    {
        $data = $this->getRequestData();
        $days = $data['days'] ?? 7;
        $stats = $this->logModel->getPhoneStats($phoneNumber, $days);

        return $this->respond([
            'status' => 'success',
            'phone_number' => $phoneNumber,
            'period' => "{$days}일",
            'stats' => $stats
        ]);
    }

    /**
     * 앱 버전별 통계
     * GET /api/v1/logs/stats/version
     */
    public function getVersionStats()
    {
        $data = $this->getRequestData();
        $days = $data['days'] ?? 7;
        $stats = $this->logModel->getVersionStats($days);

        return $this->respond([
            'status' => 'success',
            'period' => "{$days}일",
            'stats' => $stats
        ]);
    }

    /**
     * 특정 사용자의 로그 조회 (이력 다운로드용)
     * GET /api/v1/logs/user/{phone_number}
     */
    public function getUserLogs($phoneNumber)
    {
        $data = $this->getRequestData();
        $limit = $data['limit'] ?? 10000;
        
        // 기본값: 최근 1주일
        $startDate = $data['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
        $endDate = $data['end_date'] ?? date('Y-m-d');
        
        $logs = $this->logModel
                    ->where('phone_number', $phoneNumber)
                    ->where('created_at >=', $startDate . ' 00:00:00')
                    ->where('created_at <=', $endDate . ' 23:59:59')
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
        
        return $this->respond([
            'status' => 'success',
            'data' => $logs,
            'count' => count($logs)
        ]);
    }
}