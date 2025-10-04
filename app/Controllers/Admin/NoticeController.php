<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class NoticeController extends BaseController
{
    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/admin/login');
        }
        
        $data = [
            'title' => '공지사항',
            'active_menu' => 'notices'
        ];
        
        return view('admin/notices/index', $data);
    }

    public function create()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/admin/login');
        }
        
        $data = [
            'title' => '공지사항 작성',
            'active_menu' => 'notices',
            'mode' => 'create'
        ];
        
        return view('admin/notices/form', $data);
    }

    public function edit($id)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/admin/login');
        }
        
        $data = [
            'title' => '공지사항 수정',
            'active_menu' => 'notices',
            'mode' => 'edit',
            'notice_id' => $id
        ];
        
        return view('admin/notices/form', $data);
    }
}