<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class AdminController extends BaseController
{
    public function loginForm()
    {
        return view('admin/login');
    }
}