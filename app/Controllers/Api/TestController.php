<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class TestController extends BaseController
{
    public function index()
    {
        //
    }

    public function hello()
    {
        // JSON 응답 헤더 설정
        $this->response->setContentType('application/json');
        
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Hello World from CodeIgniter 4 API!',
            'timestamp' => date('Y-m-d H:i:s'),
            'server_ip' => $_SERVER['SERVER_ADDR'] ?? 'unknown'
        ]);
    }
}
