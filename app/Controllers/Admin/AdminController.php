<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class AdminController extends BaseController
{
    public function loginForm()
    {
        return view('admin/login');
    }

    public function registerForm()
    {
        $data = [
            'title' => '관리자 회원가입'
        ];
        return view('admin/register', $data);
    }
}