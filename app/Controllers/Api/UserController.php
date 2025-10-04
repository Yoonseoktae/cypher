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

    public function index()
    {
        $data = $this->getRequestData();
        $agencyId = session()->get('agency_id');
        
        $page = (int)($data['page'] ?? 1);
        $limit = (int)($data['limit'] ?? 20);
        $search = $data['search'] ?? '';
        $status = $data['status'] ?? '';
        $appService = $data['app_service'] ?? ''; // 추가
        
        $userModel = new \App\Models\UserModel();
        
        $builder = $userModel->where('agency_id', $agencyId);
        
        if (!empty($search)) {
            $builder->groupStart()
                    ->like('name', $search)
                    ->orLike('phone_number', $search)
                    ->groupEnd();
        }
        
        if ($status !== '') {
            $builder->where('status', $status);
        }
        
        if ($appService !== '') {
            $builder->where('app_service', $appService);
        }
        
        $total = $builder->countAllResults(false);
        
        $users = $builder->orderBy('registration_date', 'DESC')
                        ->limit($limit, ($page - 1) * $limit)
                        ->findAll();
        
        return $this->respond([
            'status' => 'success',
            'data' => [
                'users' => $users,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => ceil($total / $limit),
                    'total_records' => $total,
                    'limit' => $limit
                ]
            ]
        ]);
    }
    
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

        if (in_array($user['status'], ['2'])) {
            $statusMessage = [
                3 => '사용불가'
            ];
            return $this->errorResponse('사용 불가능한 계정입니다. 상태: ' . ($statusMessage[$user['status']] ?? '알 수 없음'), 403);
        }

        if (!$user['expiry_date']) {
            return $this->errorResponse('만료일이 지정되지 않았습니다', 400);
        }

        if ($user['expiry_date'] !== null) {
            $expiryDate = date('Y-m-d', strtotime($user['expiry_date']));
            $today = date('Y-m-d');
            
            if ($expiryDate < $today) {
                return $this->errorResponse('만료된 계정입니다', 403);
            }
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
            'login_at' => date('Y-m-d H:i:s')
        ];
        
        if ($appVersion) {
            $updateData['app_version'] = $appVersion;
        }

        $this->userModel->update($user['id'], $updateData);

        // 해당 대리점의 고정 공지사항 조회
        $noticeModel = new \App\Models\NoticeModel();
        $notice = $noticeModel->getActiveNotice($user['agency_id']);

        $responseData = [
            'user_id' => $user['id'],
            'name' => $user['name'],
            'phone_number' => $user['phone_number'],
            'status' => $user['status'],
            'expiry_date' => date('Y-m-d', strtotime($user['expiry_date'])),
            'notice' => [
                'title' => $notice['title'] ?? '',
                'content' => $notice['content'] ?? ''
            ]
        ];

        if ($user['app_service'] == 'normal') {
            $responseData['is_franchise'] = $user['is_franchise'];
        }

        return $this->successResponse($responseData, '로그인 성공');
    }

    public function logout()
    {
        session()->destroy();
        return $this->successResponse(null, '로그아웃 성공');
    }

    public function detail($id)
    {
        $agencyId = session()->get('agency_id');
        
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($id);
        
        if (!$user || $user['agency_id'] != $agencyId) {
            return $this->fail('사용자를 찾을 수 없습니다.', 404);
        }
        
        return $this->respond([
            'status' => 'success',
            'data' => $user
        ]);
    }

    public function create()
    {
        $data = $this->getRequestData();
        $agencyId = session()->get('agency_id');
        
        if (empty($data['name']) || empty($data['phone_number'])) {
            return $this->fail('이름과 전화번호를 입력하세요.', 400);
        }
        
        // 전화번호에서 숫자만 추출
        $phoneNumber = preg_replace('/[^0-9]/', '', $data['phone_number']);
        
        if (strlen($phoneNumber) < 10 || strlen($phoneNumber) > 11) {
            return $this->fail('올바른 전화번호를 입력하세요.', 400);
        }
        
        $userModel = new \App\Models\UserModel();
        
        // 전체 대리점에서 전화번호 중복 체크
        if ($userModel->isPhoneExists($phoneNumber)) {
            return $this->fail('다른 대리점에 가입된 번호입니다.', 400);
        }
        
        $insertData = [
            'agency_id' => $agencyId,
            'name' => $data['name'],
            'phone_number' => $phoneNumber, // 숫자만 저장
            'app_service' => $data['app_service'] ?? 'normal',
            'signup_date' => $data['signup_date'] ?? null,
            'is_franchise' => $data['is_franchise'] ?? null,
            'registration_date' => date('Y-m-d'),
            'expiry_date' => null,
            'status' => $data['status'] ?? 1
        ];
        
        if ($userModel->insert($insertData)) {
            return $this->respondCreated([
                'status' => 'success',
                'message' => '사용자가 등록되었습니다.'
            ]);
        }
        
        return $this->fail('등록에 실패했습니다.', 500);
    }

    public function modify($id)
    {
        $data = $this->getRequestData();
        $agencyId = session()->get('agency_id');
        
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($id);
        
        if (!$user || $user['agency_id'] != $agencyId) {
            return $this->fail('사용자를 찾을 수 없습니다.', 404);
        }
        
        $updateData = [];
        
        // 전화번호 변경 시
        if (isset($data['phone_number'])) {
            // 숫자만 추출
            $phoneNumber = preg_replace('/[^0-9]/', '', $data['phone_number']);
            
            if (strlen($phoneNumber) < 10 || strlen($phoneNumber) > 11) {
                return $this->fail('올바른 전화번호를 입력하세요.', 400);
            }
            
            // 전화번호가 변경되었을 때만 중복 체크
            if ($phoneNumber != $user['phone_number']) {
                // 전체 대리점에서 중복 체크
                if ($userModel->isPhoneExists($phoneNumber, $id)) {
                    return $this->fail('다른 대리점에 가입된 번호입니다.', 400);
                }
            }
            
            $updateData['phone_number'] = $phoneNumber;
        }
        
        if (isset($data['name'])) $updateData['name'] = $data['name'];
        if (isset($data['app_service'])) {
            $updateData['app_service'] = $data['app_service'];
            // 벤티로 변경 시 is_franchise null
            if ($data['app_service'] == 'venti') {
                $updateData['is_franchise'] = null;
            }
        }
        if (isset($data['signup_date'])) $updateData['signup_date'] = $data['signup_date'];
        if (isset($data['is_franchise'])) $updateData['is_franchise'] = $data['is_franchise'];
        
        if ($userModel->update($id, $updateData)) {
            return $this->respond([
                'status' => 'success',
                'message' => '수정되었습니다.'
            ]);
        }
        
        return $this->fail('수정에 실패했습니다.', 500);
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

    /**
     * 사용자 유효기간 연장/수정
     * POST /api/v1/users/{id}/extend
     */
    public function extend($id)
    {
        $data = $this->getRequestData();
        $agencyId = session()->get('agency_id');
        
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($id);
        
        if (!$user || $user['agency_id'] != $agencyId) {
            return $this->fail('사용자를 찾을 수 없습니다.', 404);
        }
        
        if (!isset($data['expiry_date'])) {
            return $this->fail('유효기간 값이 필요합니다.', 400);
        }
        
        if ($userModel->update($id, ['expiry_date' => $data['expiry_date']])) {
            return $this->respond([
                'status' => 'success',
                'message' => '유효기간이 변경되었습니다.'
            ]);
        }
        
        return $this->fail('유효기간 변경에 실패했습니다.', 500);
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

    // /**
    //  * 사용자 앱 로그 이력 조회
    //  * GET /api/v1/users/{id}/logs
    //  */
    // public function getLogs($id)
    // {
    //     $userModel = new \App\Models\UserModel();
    //     $user = $userModel->find($id);
        
    //     if (!$user) {
    //         return $this->fail('사용자를 찾을 수 없습니다.', 404);
    //     }
        
    //     // 사용자의 전화번호로 로그 조회
    //     $logModel = new \App\Models\AppLogModel();
        
    //     $limit = $this->request->getGet('limit') ?? 1000;
        
    //     $builder = $logModel->where('phone_number', $user['phone_number'])
    //                         ->orderBy('created_at', 'DESC');
        
    //     if ($startDate) {
    //         $builder->where('created_at >=', $startDate);
    //     }
    //     if ($endDate) {
    //         $builder->where('created_at <=', $endDate . ' 23:59:59');
    //     }
        
    //     $logs = $builder->limit($limit)->findAll();
        
    //     return $this->respond([
    //         'status' => 'success',
    //         'data' => $logs,
    //         'count' => count($logs),
    //         'user' => [
    //             'id' => $user['id'],
    //             'name' => $user['name'],
    //             'phone_number' => $user['phone_number']
    //         ]
    //     ]);
    // }

    /**
     * 사용자 상태 변경
     * POST /api/v1/users/{id}/status
     */
    public function changeStatus($id)
    {
        $data = $this->getRequestData();
        $agencyId = session()->get('agency_id');
        
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($id);
        
        if (!$user || $user['agency_id'] != $agencyId) {
            return $this->fail('사용자를 찾을 수 없습니다.', 404);
        }
        
        if (!isset($data['status'])) {
            return $this->fail('상태 값이 필요합니다.', 400);
        }
        
        if ($userModel->update($id, ['status' => $data['status']])) {
            return $this->respond([
                'status' => 'success',
                'message' => '상태가 변경되었습니다.'
            ]);
        }
        
        return $this->fail('상태 변경에 실패했습니다.', 500);
    }


}