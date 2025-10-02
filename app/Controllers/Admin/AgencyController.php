<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class AgencyController extends BaseController
{
    public function info()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/admin/login');
        }
        
        $data = [
            'title' => '대리점 정보',
            'active_menu' => 'agency'
        ];
        
        return view('admin/agency/info', $data);
    }

    public function create()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/admin/login');
        }
        
        $data = [
            'title' => '대리점 등록',
            'active_menu' => 'agency'
        ];
        
        return view('admin/agency/create', $data);
    }
}