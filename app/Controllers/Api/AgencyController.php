<?php

namespace App\Controllers\Api;

use App\Models\AgencyModel;
use App\Models\UserModel;

class AgencyController extends BaseApiController
{
    protected $agencyModel;
    protected $userModel;

    public function __construct()
    {
        $this->agencyModel = new AgencyModel();
        $this->userModel = new UserModel();
    }

    public function show($id = null)
    {
        if (!$id) {
            $id = session()->get('agency_id');
        }

        if (!$id) {
            return $this->errorResponse('로그인이 필요합니다', 401);
        }

        $agency = $this->agencyModel->find($id);

        if (!$agency) {
            return $this->errorResponse('대리점을 찾을 수 없습니다', 404);
        }

        return $this->successResponse($agency);
    }

    public function create()
    {
        $data = $this->getRequestData();

        if (empty($data['agency_code']) || empty($data['name'])) {
            return $this->errorResponse('필수 항목을 입력하세요', 400);
        }

        $data['status'] = 'active';
        $agencyId = $this->agencyModel->insert($data);

        if ($agencyId) {
            return $this->successResponse(['agency_id' => $agencyId], '대리점 생성 성공', 201);
        }

        return $this->errorResponse('대리점 생성 실패', 500);
    }

    public function modify()
    {
        $agencyId = session()->get('agency_id');
        
        if (!$agencyId) {
            return $this->errorResponse('로그인이 필요합니다', 401);
        }

        $data = $this->getRequestData();

        if ($this->agencyModel->update($agencyId, $data)) {
            return $this->successResponse(null, '대리점 정보 수정 성공');
        }

        return $this->errorResponse('대리점 정보 수정 실패', 500);
    }

    /**
     * 대리점 목록 조회 (슈퍼 관리자용)
     * GET /api/v1/agency/list
     */
    public function getList()
    {
        // 슈퍼 관리자만 접근 가능
        $role = session()->get('role');
        if ($role != 99) {
            return $this->fail('접근 권한이 없습니다.', 403);
        }
        
        $agencyModel = new AgencyModel();
        $agencies = $agencyModel->select('id, name')
                                ->where('status', 1)
                                ->orderBy('name', 'ASC')
                                ->findAll();
        
        return $this->respond([
            'status' => 'success',
            'data' => $agencies
        ]);
    }

    public function getDashboardStats()
    {
        $role = session()->get('role');
        $agencyId = session()->get('agency_id');
        
        // GET 파라미터 직접 읽기
        $data = $this->getRequestData();
        $selectedAgencyId = $data['agency_id'];
        
        $userModel = new \App\Models\UserModel();
        
        // 슈퍼 관리자
        if ($role == 99) {
            if (!$selectedAgencyId) {
                return $this->fail('대리점을 선택해주세요.', 400);
            }
            $targetAgencyId = $selectedAgencyId;
        } else {
            // 대리점 관리자는 자기 대리점만
            $targetAgencyId = $agencyId;
        }
        
        // 통계 조회
        $stats = [
            'total_users' => $userModel->where('agency_id', $targetAgencyId)->countAllResults(false),
            'active_users' => $userModel->where('agency_id', $targetAgencyId)->where('status', 1)->countAllResults(),
            'expired_users' => $userModel->getExpiredCount($targetAgencyId),
            'expiring_soon' => $userModel->getExpiringSoonCount($targetAgencyId),
            'this_week_new' => $userModel->getThisWeekNewCount($targetAgencyId),
            'recent_users' => $userModel->getRecentUsers(10, $targetAgencyId)
        ];
        
        return $this->respond([
            'status' => 'success',
            'data' => $stats
        ]);
    }
}