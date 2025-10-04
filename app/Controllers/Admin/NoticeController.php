<?php

namespace App\Controllers\Admin;

class NoticeController extends BaseController
{
    public function index()
    {
        $auth = $this->checkAuth();
        if ($auth !== true) return $auth;
        
        // 대리점 관리자만 접근 가능
        $roleCheck = $this->checkRole([1, 99]); // 대리점 관리자(1), 슈퍼관리자(99)
        if ($roleCheck !== true) return $roleCheck;
        
        return view('admin/notices/index', [
            'title' => '공지사항 관리',
            'active_menu' => 'notices'
        ]);
    }
    
    public function create()
    {
        $auth = $this->checkAuth();
        if ($auth !== true) return $auth;
        
        $roleCheck = $this->checkRole([1, 99]);
        if ($roleCheck !== true) return $roleCheck;
        
        return view('admin/notices/create', [
            'title' => '공지사항 등록',
            'active_menu' => 'notices'
        ]);
    }
}