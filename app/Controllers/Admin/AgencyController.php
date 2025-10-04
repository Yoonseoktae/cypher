<?php

namespace App\Controllers\Admin;

class AgencyController extends BaseController
{
    public function index()
    {
        $auth = $this->checkAuth();
        if ($auth !== true) return $auth;
        
        $roleCheck = $this->checkRole(99);
        if ($roleCheck !== true) return $roleCheck;
        
        return view('admin/agency/index', [
            'title' => '대리점 관리',
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