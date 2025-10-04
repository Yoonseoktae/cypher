<?php

namespace App\Controllers\Admin;

class UserController extends BaseController
{
    public function index()
    {
        $auth = $this->checkAuth();
        if ($auth !== true) return $auth;
        
        $roleCheck = $this->checkRole(1);
        if ($roleCheck !== true) return $roleCheck;
        
        return view('admin/users/index', [
            'title' => '사용자 관리',
            'active_menu' => 'users'
        ]);
    }
    
    public function create()
    {
        $auth = $this->checkAuth();
        if ($auth !== true) return $auth;
        
        $roleCheck = $this->checkRole(1);
        if ($roleCheck !== true) return $roleCheck;
        
        return view('admin/users/create', [
            'title' => '사용자 등록',
            'active_menu' => 'users'
        ]);
    }
}