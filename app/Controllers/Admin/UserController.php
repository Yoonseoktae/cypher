<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class UserController extends BaseController
{
    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/admin/login');
        }
        
        $data = [
            'title' => '사용자 관리',
            'active_menu' => 'users'
        ];
        
        return view('admin/users/index', $data);
    }
}