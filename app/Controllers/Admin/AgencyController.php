<?php

namespace App\Controllers\Admin;

class AgencyController extends BaseController
{
    public function info()
    {
        $auth = $this->checkAuth();
        if ($auth !== true) return $auth;
        
        $roleCheck = $this->checkRole(99);
        if ($roleCheck !== true) return $roleCheck;
        
        return view('admin/agency/info', [
            'title' => '대리점 정보',
            'active_menu' => 'agency'
        ]);
    }
    
    public function create()
    {
        $auth = $this->checkAuth();
        if ($auth !== true) return $auth;
        
        $roleCheck = $this->checkRole(99);
        if ($roleCheck !== true) return $roleCheck;
        
        return view('admin/agency/create', [
            'title' => '대리점 등록',
            'active_menu' => 'agency'
        ]);
    }
}