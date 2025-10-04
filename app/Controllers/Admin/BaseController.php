<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController as AppBaseController;

class BaseController extends AppBaseController
{
    protected $session;
    
    public function __construct()
    {
        $this->session = session();
    }
    
    protected function checkAuth()
    {
        if (!$this->session->get('logged_in')) {
            return redirect()->to('/admin/login');
        }

        return true;
    }
    
    protected function checkRole($requiredRole)
    {
        $userRole = $this->session->get('role');
        
        if ($userRole == 99) {
            return true;
        }
        
        if (is_array($requiredRole)) {
            if (in_array($userRole, $requiredRole)) {
                return true;
            }
        } else {
            if ($userRole == $requiredRole) {
                return true;
            }
        }
        
        return redirect()->to('/admin/dashboard')->with('error', '접근 권한이 없습니다.');
    }
}