<?php

namespace App\Controllers\Api;

use App\Models\AdminModel;

class AdminController extends BaseApiController
{
    protected $adminModel;

    public function __construct()
    {
        $this->adminModel = new AdminModel();
    }

    public function login()
    {
        $data = $this->getRequestData();
        $username = $data['username'] ?? null;
        $password = $data['password'] ?? null;

        // 간단한 체크
        if (!$username || !$password) {
            return $this->errorResponse('아이디와 비밀번호를 입력하세요', 400);
        }

        $admin = $this->adminModel->where('username', $username)->first();

        if (!$admin || !password_verify($password, $admin['password'])) {
            return $this->errorResponse('아이디 또는 비밀번호가 올바르지 않습니다', 401);
        }

        if ($admin['status'] != 1) {
            return $this->errorResponse('비활성화된 계정입니다', 403);
        }

        // 세션 저장
        session()->set([
            'admin_id' => $admin['id'],
            'agency_id' => $admin['agency_id'],
            'username' => $admin['username'],
            'role' => $admin['role'],
            'user_type' => 'admin',
            'logged_in' => true
        ]);

        // login_at 업데이트
        $this->adminModel->update($admin['id'], [
            'login_at' => time()
        ]);

        return $this->successResponse([
            'admin_id' => $admin['id'],
            'username' => $admin['username'],
            'name' => $admin['name'],
            'role' => $admin['role'],
            'agency_id' => $admin['agency_id']
        ], '로그인 성공');
    }

    public function logout()
    {
        session()->destroy();
        return $this->successResponse(null, '로그아웃 성공');
    }
}