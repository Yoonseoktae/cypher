<?php

namespace App\Controllers\Api;

use CodeIgniter\API\ResponseTrait;

class LogController extends BaseApiController
{
    use ResponseTrait;

    private $logPath = WRITEPATH . 'logs/app/'; // writable/logs/app/
    private $maxLogDays = 30; // 보관 기간 (일)

    public function __construct()
    {
        // 로그 디렉토리 생성
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }

    /**
     * 로그 저장 API
     * POST /api/v1/logs
     */
    public function create()
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
     * 배치로 여러 로그 저장
     * POST /api/v1/logs/batch
     */
    public function batch()
    {
        
        $data = $this->getRequestData();

        if (!isset($data['logs']) || !is_array($data['logs'])) {
            return $this->fail('logs 배열이 필요합니다.', 400);
        }

        $saved = 0;
        $failed = 0;

        foreach ($data['logs'] as $log) {
            if (!isset($log['log_data'])) {
                $failed++;
                continue;
            }

            $userId = $log['user_id'] ?? 'unknown';
            $logType = $log['log_type'] ?? 'general';
            $logData = $log['log_data'];
            
            $date = date('Y-m-d');
            $filename = "{$date}_{$logType}.log";
            $filepath = $this->logPath . $filename;

            $timestamp = date('Y-m-d H:i:s');
            $logEntry = "[{$timestamp}] [USER:{$userId}] {$logData}" . PHP_EOL;

            if (file_put_contents($filepath, $logEntry, FILE_APPEND | LOCK_EX) !== false) {
                $saved++;
            } else {
                $failed++;
            }
        }

        $this->cleanOldLogs();

        return $this->respond([
            'status' => 'success',
            'saved' => $saved,
            'failed' => $failed
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
}