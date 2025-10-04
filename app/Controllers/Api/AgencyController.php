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

    /**
     * 대리점 등록
     * POST /api/v1/agency
     */
    public function create()
    {
        $data = $this->getRequestData();
        
        // 유효성 검사
        if (empty($data['name'])) {
            return $this->errorResponse('대리점명을 입력하세요.', 400);
        }
        
        if (empty($data['number'])) {
            return $this->errorResponse('전화번호를 입력하세요.', 400);
        }
        
        $agencyModel = new \App\Models\AgencyModel();
        
        // 대리점 코드 자동 생성 (예: AG + 타임스탬프)
        $agencyCode = 'AG' . date('YmdHis');
        
        $insertData = [
            'name' => $data['name'],
            'number' => $data['number'],
            'status' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        if ($agencyModel->insert($insertData)) {
            return $this->respondCreated([
                'status' => 'success',
                'message' => '대리점이 등록되었습니다.'
            ]);
        }
        
        return $this->errorResponse('대리점 등록에 실패했습니다.', 500);
    }

    /**
     * 대리점 수정
     * PUT /api/v1/agency
     */
    public function modify()
    {
        $data = $this->getRequestData();
        
        if (empty($data['id'])) {
            return $this->fail('대리점 ID가 필요합니다.', 400);
        }
        
        if (empty($data['name'])) {
            return $this->fail('대리점명을 입력하세요.', 400);
        }
        
        $agencyModel = new \App\Models\AgencyModel();
        
        $updateData = [
            'name' => $data['name']
        ];
        
        if ($agencyModel->update($data['id'], $updateData)) {
            return $this->respond([
                'status' => 'success',
                'message' => '수정되었습니다.'
            ]);
        }
        
        return $this->fail('수정에 실패했습니다.', 500);
    }

    /**
     * 대리점 목록 조회 (통계 포함)
     * GET /api/v1/agency/list
     */
    public function getList()
    {
        $role = session()->get('role');
        if ($role != 99) {
            return $this->fail('접근 권한이 없습니다.', 403);
        }
        
        $data = $this->getRequestData();
        $search = $data['search'] ?? null;
        
        $agencyModel = new \App\Models\AgencyModel();
        $userModel = new \App\Models\UserModel();
        
        $builder = $agencyModel->select('id, name, number, status, created_at')
                            ->orderBy('created_at', 'DESC');
        
        if ($search) {
            $builder->groupStart()
                    ->like('name', $search)
                    ->orLike('number', $search)
                    ->groupEnd();
        }
        
        $agencies = $builder->findAll();
        
        // 각 대리점별 통계 추가
        foreach ($agencies as &$agency) {
            $agency['total_users'] = $userModel->where('agency_id', $agency['id'])->countAllResults();
            $agency['this_week_new'] = $userModel->getThisWeekNewCount($agency['id']);
        }
        
        return $this->respond([
            'status' => 'success',
            'data' => $agencies
        ]);
    }

    public function getDashboardStats()
    {
        $role = session()->get('role');
        $agencyId = session()->get('agency_id'); // 여기서 에러
        
        // GET 파라미터 읽기
        $data = $this->getRequestData();
        $selectedAgencyId = $data['agency_id'] ?? null;
        
        $userModel = new \App\Models\UserModel();
        
        // 슈퍼 관리자
        if ($role == 99) {
            if (!$selectedAgencyId) {
                return $this->errorResponse('대리점을 선택해주세요.', 400);
            }
            $targetAgencyId = $selectedAgencyId;
        } else {
            // 대리점 관리자는 자기 대리점만
            if (!$agencyId) {
                return $this->errorResponse('세션에 대리점 정보가 없습니다.', 401);
            }
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