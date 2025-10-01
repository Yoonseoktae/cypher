<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ApiAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // 세션에서 API 로그인 상태 확인
        if (!session()->get('api_logged_in')) {
            return service('response')
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Authentication required'
                ])
                ->setStatusCode(401);
        }

        // 세션 만료 체크 (선택적)
        $lastActivity = session()->get('last_activity');
        if ($lastActivity && (time() - $lastActivity > 7200)) { // 2시간
            session()->destroy();
            return service('response')
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Session expired'
                ])
                ->setStatusCode(401);
        }

        // 활동 시간 업데이트
        session()->set('last_activity', time());
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // 후처리 필요시
    }
}