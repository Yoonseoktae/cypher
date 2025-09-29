<?php

namespace App\Controllers\Api;

use App\Models\UserModel;
use App\Models\UserHistoryModel;

class UserController extends BaseApiController
{
    protected $userModel;
    protected $historyModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->historyModel = new UserHistoryModel();
    }

    // ============ 사용자 로그인 (전화번호만) ============
    
    public function login()
    {
        $data = $this->getRequestData();
        $phoneNumber = $data['phone_number'] ?? null;
        $appVersion = $data['app_version'] ?? null;
        $appService = $data['app_service'] ?? null;

        if (!$appVersion) {
            return $this->errorResponse('앱버전이 확인되지 않습니다', 400);
        }

        if (!$appService) {
            return $this->errorResponse('서비스가 확인되지 않습니다', 400);
        }

        if (!$phoneNumber) {
            return $this->errorResponse('전화번호를 입력하세요', 400);
        }

        $user = $this->userModel
                    ->where('phone_number', $phoneNumber)
                    ->where('app_service', $appService)
                    ->first();

        if (!$user) {
            return $this->errorResponse('계정을 찾을 수 없습니다', 404);
        }

        if ($user['status'] != 1) {
            $statusMessage = [
                0 => '계정 미인증',
                2 => '계정 중지',
                3 => '계정 밴'
            ];
            return $this->errorResponse('사용 불가능한 계정입니다. 상태: ' . ($statusMessage[$user['status']] ?? '알 수 없음'), 403);
        }

        if (strtotime($user['expiry_date']) < time()) {
            return $this->errorResponse('만료된 계정입니다', 403);
        }

        // 세션 저장
        session()->set([
            'user_id' => $user['id'],
            'agency_id' => $user['agency_id'],
            'user_type' => 'driver',
            'logged_in' => true
        ]);

        // login_at과 app_version 업데이트
        $updateData = [
            'login_at' => date('Y-m-d H:i:s') // DATETIME 형식
        ];
        
        if ($appVersion) {
            $updateData['app_version'] = $appVersion;
        }

        $this->userModel->update($user['id'], $updateData);

        return $this->successResponse([
            'user_id' => $user['id'],
            'name' => $user['name'],
            'phone_number' => $user['phone_number'],
            'status' => $user['status'],
            'expiry_date' => $user['expiry_date']
        ], '로그인 성공');
    }

    public function logout()
    {
        session()->destroy();
        return $this->successResponse(null, '로그아웃 성공');
    }

    // ============ 사용자 본인 프로필 ============
    
    public function getProfile()
    {
        $userId = session()->get('user_id');
        
        if (!$userId) {
            return $this->errorResponse('로그인이 필요합니다', 401);
        }

        $user = $this->userModel->find($userId);

        if (!$user) {
            return $this->errorResponse('사용자를 찾을 수 없습니다', 404);
        }

        return $this->successResponse($user);
    }

    public function updateProfile()
    {
        $userId = session()->get('user_id');
        
        if (!$userId) {
            return $this->errorResponse('로그인이 필요합니다', 401);
        }

        $data = $this->getRequestData();

        if ($this->userModel->update($userId, $data)) {
            return $this->successResponse(null, '프로필 업데이트 성공');
        }

        return $this->errorResponse('프로필 업데이트 실패', 500);
    }

    // ============ 관리자용 사용자 관리 ============
    
    public function index()
    {
        $agencyId = session()->get('agency_id');
        
        if (!$agencyId) {
            return $this->errorResponse('로그인이 필요합니다', 401);
        }
        
        $page = (int) ($_GET['page'] ?? 1);
        $limit = (int) ($_GET['limit'] ?? 20);
        $search = $_GET['search'] ?? null;
        $status = $_GET['status'] ?? null;

        $builder = $this->userModel->where('agency_id', $agencyId);

        if ($search) {
            $builder->groupStart()
                ->like('name', $search)
                ->orLike('phone_number', $search)
                ->orLike('user_code', $search)
                ->groupEnd();
        }

        if ($status) {
            $builder->where('status', $status);
        }

        $total = $builder->countAllResults(false);
        $users = $builder->orderBy('created_at', 'DESC')
            ->paginate($limit, 'default', $page);

        return $this->successResponse([
            'users' => $users,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($total / $limit),
                'total_items' => $total,
                'per_page' => $limit
            ]
        ]);
    }

    public function show($id = null)
    {
        $agencyId = session()->get('agency_id');
        
        if (!$agencyId) {
            return $this->errorResponse('로그인이 필요합니다', 401);
        }

        $user = $this->userModel
            ->where('agency_id', $agencyId)
            ->find($id);

        if (!$user) {
            return $this->errorResponse('사용자를 찾을 수 없습니다', 404);
        }

        return $this->successResponse($user);
    }

    public function create()
    {
        // $agencyId = session()->get('agency_id');
        $agencyId = 1;

        if (!$agencyId) {
            return $this->errorResponse('로그인이 필요합니다', 401);
        }

        $data = $this->getRequestData();

        // 간단한 체크
        if (empty($data['name']) || empty($data['phone_number'])) {
            return $this->errorResponse('필수 항목을 입력하세요', 400);
        }

        $data['agency_id'] = $agencyId;
        $data['registration_date'] = date('Y-m-d');
        $data['status'] = 0;

        $userId = $this->userModel->insert($data);

        if ($userId) {
            // 가입 로그
            $this->historyModel->insert([
                'user_id' => $userId,
                'admin_id' => session()->get('admin_id'),
                'action' => 'register',
                'field' => NULL,
                'before_value' => NULL,
                'after_value' => json_encode([
                    'name' => $data['name'],
                    'phone_number' => $data['phone_number'],
                    'expiry_date' => $data['expiry_date'] ?? null
                ]),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
            ]);

            return $this->respondCreated([
                'status' => 'success',
                'message' => '사용자 생성 성공',
                'data' => ['user_id' => $userId]
            ]);
        }

        return $this->errorResponse('사용자 생성 실패', 500);
    }

    public function update($id = null)
    {
        $agencyId = session()->get('agency_id');
        
        if (!$agencyId) {
            return $this->errorResponse('로그인이 필요합니다', 401);
        }

        $user = $this->userModel
            ->where('agency_id', $agencyId)
            ->find($id);

        if (!$user) {
            return $this->errorResponse('사용자를 찾을 수 없습니다', 404);
        }

        $data = $this->getRequestData();

        // 변경 로그 기록
        foreach ($data as $field => $newValue) {
            if (isset($user[$field]) && $user[$field] != $newValue) {
                $this->historyModel->insert([
                    'user_id' => $id,
                    'admin_id' => session()->get('admin_id'),
                    'action' => 'update',
                    'field' => $field,
                    'before_value' => $user[$field],
                    'after_value' => $newValue,
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
                ]);
            }
        }

        if ($this->userModel->update($id, $data)) {
            return $this->successResponse(null, '사용자 수정 성공');
        }

        return $this->errorResponse('사용자 수정 실패', 500);
    }

    public function delete($id = null)
    {
        $agencyId = session()->get('agency_id');
        
        if (!$agencyId) {
            return $this->errorResponse('로그인이 필요합니다', 401);
        }

        $user = $this->userModel
            ->where('agency_id', $agencyId)
            ->find($id);

        if (!$user) {
            return $this->errorResponse('사용자를 찾을 수 없습니다', 404);
        }

        if ($this->userModel->delete($id)) {
            return $this->successResponse(null, '사용자 삭제 성공');
        }

        return $this->errorResponse('사용자 삭제 실패', 500);
    }

    public function extend($id = null)
    {
        $agencyId = session()->get('agency_id');
        
        if (!$agencyId) {
            return $this->errorResponse('로그인이 필요합니다', 401);
        }

        $user = $this->userModel
            ->where('agency_id', $agencyId)
            ->find($id);

        if (!$user) {
            return $this->errorResponse('사용자를 찾을 수 없습니다', 404);
        }

        $data = $this->getRequestData();
        $newExpiryDate = $data['expiry_date'] ?? null;

        if (!$newExpiryDate) {
            return $this->errorResponse('만료일을 입력하세요', 400);
        }

        if ($this->userModel->update($id, ['expiry_date' => $newExpiryDate])) {
            $this->historyModel->insert([
                'user_id' => $id,
                'action_type' => 'renewal',
                'previous_expiry_date' => $user['expiry_date'],
                'new_expiry_date' => $newExpiryDate,
                'created_by' => session()->get('admin_id')
            ]);

            return $this->successResponse(null, '기간 연장 성공');
        }

        return $this->errorResponse('기간 연장 실패', 500);
    }

    public function updateStatus($id = null)
    {
        $agencyId = session()->get('agency_id');
        
        if (!$agencyId) {
            return $this->errorResponse('로그인이 필요합니다', 401);
        }

        $user = $this->userModel
            ->where('agency_id', $agencyId)
            ->find($id);

        if (!$user) {
            return $this->errorResponse('사용자를 찾을 수 없습니다', 404);
        }

        $data = $this->getRequestData();
        $newStatus = $data['status'] ?? null;

        if (!$newStatus) {
            return $this->errorResponse('상태를 입력하세요', 400);
        }

        if ($this->userModel->update($id, ['status' => $newStatus])) {
            $this->historyModel->insert([
                'user_id' => $id,
                'action_type' => 'status_change',
                'previous_status' => $user['status'],
                'new_status' => $newStatus,
                'created_by' => session()->get('admin_id')
            ]);

            return $this->successResponse(null, '상태 변경 성공');
        }

        return $this->errorResponse('상태 변경 실패', 500);
    }

    public function getHistory($id = null)
    {
        $agencyId = session()->get('agency_id');
        
        if (!$agencyId) {
            return $this->errorResponse('로그인이 필요합니다', 401);
        }

        $user = $this->userModel
            ->where('agency_id', $agencyId)
            ->find($id);

        if (!$user) {
            return $this->errorResponse('사용자를 찾을 수 없습니다', 404);
        }

        $history = $this->historyModel
            ->where('user_id', $id)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return $this->successResponse($history);
    }
}