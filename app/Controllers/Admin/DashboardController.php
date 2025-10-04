<?php

namespace App\Controllers\Admin;

class DashboardController extends BaseController
{
    public function index()
    {
        $auth = $this->checkAuth();
        if ($auth !== true) return $auth;
        
        $role = session()->get('role');
        
        if ($role == 99) {
            return redirect()->to('/admin/settlement');
        }
        
        $roleCheck = $this->checkRole(1);
        if ($roleCheck !== true) return $roleCheck;
        
        return view('admin/dashboard', [
            'title' => '대시보드',
            'active_menu' => 'dashboard',
            'role' => $role
        ]);
    }
}