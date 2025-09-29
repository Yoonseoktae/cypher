<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class BaseApiController extends ResourceController
{
    protected $format = 'json';
    
    protected function getRequestData()
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $data = $_POST;
        }
        
        return $data ?: [];
    }

    protected function successResponse($data = null, $message = 'Success', $code = 200)
    {
        return $this->respond([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function errorResponse($message = 'Error', $code = 400)
    {
        return $this->respond([
            'status' => 'error',
            'message' => $message
        ], $code);
    }
}