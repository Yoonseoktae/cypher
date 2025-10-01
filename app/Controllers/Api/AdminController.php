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

    public function register()
    {
        $data = $this->getRequestData();
        
        // 필수 항목 체크
        if (empty($data['username']) || empty($data['password']) || empty($data['name'])) {
            return $this->errorResponse('필수 항목을 입력하세요', 400);
        }
        
        // 비밀번호 확인
        if (empty($data['password_confirm']) || $data['password'] !== $data['password_confirm']) {
            return $this->errorResponse('비밀번호가 일치하지 않습니다', 400);
        }
        
        // 아이디 중복 체크
        $existingAdmin = $this->adminModel->where('username', $data['username'])->first();
        if ($existingAdmin) {
            return $this->errorResponse('이미 사용중인 아이디입니다', 400);
        }
        
        // 관리자 등록
        $insertData = [
            'agency_id' => $data['agency_id'] ?? null,
            'username' => $data['username'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'name' => $data['name'],
            'role' => $data['role'] ?? 'operator',
            'status' => 1
        ];
        
        $adminId = $this->adminModel->insert($insertData);
        
        if ($adminId) {
            return $this->successResponse([
                'admin_id' => $adminId
            ], '회원가입 성공', 201);
        }
        
        return $this->errorResponse('회원가입 실패', 500);
    }
}