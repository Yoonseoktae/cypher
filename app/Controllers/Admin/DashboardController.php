<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index()
    {
        // 세션 체크
        if (!session()->get('logged_in')) {
            return redirect()->to('/admin/login');
        }
        
        $data = [
            'title' => '대시보드',
            'active_menu' => 'dashboard'
        ];
        
        return view('admin/dashboard', $data);
    }
}