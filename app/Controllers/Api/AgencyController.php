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

        if (empty($data['agency_code']) || empty($data['agency_name'])) {
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

    public function getDashboardStats()
    {
        $agencyId = session()->get('agency_id');

        if (!$agencyId) {
            return $this->errorResponse('로그인이 필요합니다', 401);
        }

        $totalDrivers = $this->userModel->where('agency_id', $agencyId)->countAllResults();
        
        $activeDrivers = $this->userModel
            ->where('agency_id', $agencyId)
            ->where('status', '사용가능')
            ->countAllResults();

        $weeklyNew = $this->userModel
            ->where('agency_id', $agencyId)
            ->where('registration_date >=', date('Y-m-d', strtotime('monday this week')))
            ->countAllResults();

        $expiringSoon = $this->userModel
            ->where('agency_id', $agencyId)
            ->where('expiry_date <=', date('Y-m-d', strtotime('+7 days')))
            ->where('expiry_date >=', date('Y-m-d'))
            ->countAllResults();

        return $this->successResponse([
            'total_drivers' => $totalDrivers,
            'active_drivers' => $activeDrivers,
            'weekly_new' => $weeklyNew,
            'expiring_soon' => $expiringSoon
        ]);
    }
}