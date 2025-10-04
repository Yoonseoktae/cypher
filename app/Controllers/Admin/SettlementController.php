<?php

namespace App\Controllers\Admin;

class SettlementController extends BaseController
{
    public function index()
    {
        $auth = $this->checkAuth();
        if ($auth !== true) return $auth;
        
        $roleCheck = $this->checkRole(99);
        if ($roleCheck !== true) return $roleCheck;
        
        return view('admin/settlement/index', [
            'title' => '정산 관리',
            'active_menu' => 'settlement'
        ]);
    }
}