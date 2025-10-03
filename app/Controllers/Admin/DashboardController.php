<?php

namespace App\Controllers\Admin;

class DashboardController extends BaseController
{
    public function index()
    {
        $auth = $this->checkAuth();
        if ($auth !== true) return $auth;
        
        return view('admin/dashboard', [
            'title' => '대시보드',
            'active_menu' => 'dashboard'
        ]);
    }
}