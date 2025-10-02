<?php
// app/Controllers/Api/NoticeController.php

namespace App\Controllers\Api;

use App\Models\NoticeModel;

class NoticeController extends BaseApiController
{
    protected $noticeModel;

    public function __construct()
    {
        $this->noticeModel = new NoticeModel();
    }

    // 공지사항 목록
    public function index()
    {
        $page = (int) ($_GET['page'] ?? 1);
        $limit = (int) ($_GET['limit'] ?? 20);

        // 고정 공지와 일반 공지 분리
        $pinnedNotice = $this->noticeModel
            ->where('is_pinned', 1)
            ->orderBy('created_at', 'DESC')
            ->first();

        $builder = $this->noticeModel->where('is_pinned', 0);
        $total = $builder->countAllResults(false);
        $notices = $builder->orderBy('created_at', 'DESC')
            ->paginate($limit, 'default', $page);
            
        return $this->successResponse([
            'pinned_notice' => $pinnedNotice,
            'notices' => $notices,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($total / $limit),
                'total_items' => $total,
                'per_page' => $limit
            ]
        ]);
    }

    // 공지사항 상세
    public function show($id = null)
    {
        $notice = $this->noticeModel->find($id);

        if (!$notice) {
            return $this->errorResponse('공지사항을 찾을 수 없습니다', 404);
        }

        return $this->successResponse($notice);
    }

    // 공지사항 등록
    public function create()
    {
        $adminId = session()->get('admin_id');
        
        if (!$adminId) {
            return $this->errorResponse('로그인이 필요합니다', 401);
        }

        $data = $this->getRequestData();

        if (empty($data['title']) || empty($data['content'])) {
            return $this->errorResponse('제목과 내용을 입력하세요', 400);
        }

        $insertData = [
            'title' => $data['title'],
            'content' => $data['content'],
            'is_pinned' => $data['is_pinned'] ?? 0,
            'created_by' => $adminId
        ];

        // 고정 공지로 등록하는 경우 기존 고정 해제
        if ($insertData['is_pinned'] == 1) {
            $this->noticeModel->where('is_pinned', 1)->set('is_pinned', 0)->update();
        }

        $noticeId = $this->noticeModel->insert($insertData);

        if ($noticeId) {
            return $this->successResponse(['notice_id' => $noticeId], '공지사항이 등록되었습니다', 201);
        }

        return $this->errorResponse('공지사항 등록 실패', 500);
    }

    // 공지사항 수정
    public function update($id = null)
    {
        $adminId = session()->get('admin_id');
        
        if (!$adminId) {
            return $this->errorResponse('로그인이 필요합니다', 401);
        }

        $notice = $this->noticeModel->find($id);

        if (!$notice) {
            return $this->errorResponse('공지사항을 찾을 수 없습니다', 404);
        }

        $data = $this->getRequestData();

        if (empty($data['title']) || empty($data['content'])) {
            return $this->errorResponse('제목과 내용을 입력하세요', 400);
        }

        $updateData = [
            'title' => $data['title'],
            'content' => $data['content'],
            'is_pinned' => $data['is_pinned'] ?? 0
        ];

        // 고정 공지로 변경하는 경우 기존 고정 해제
        if ($updateData['is_pinned'] == 1 && $notice['is_pinned'] == 0) {
            $this->noticeModel->where('is_pinned', 1)->set('is_pinned', 0)->update();
        }

        if ($this->noticeModel->update($id, $updateData)) {
            return $this->successResponse(null, '공지사항이 수정되었습니다');
        }

        return $this->errorResponse('공지사항 수정 실패', 500);
    }

    // 공지사항 삭제
    public function delete($id = null)
    {
        $adminId = session()->get('admin_id');
        
        if (!$adminId) {
            return $this->errorResponse('로그인이 필요합니다', 401);
        }

        $notice = $this->noticeModel->find($id);

        if (!$notice) {
            return $this->errorResponse('공지사항을 찾을 수 없습니다', 404);
        }

        if ($this->noticeModel->delete($id)) {
            return $this->successResponse(null, '공지사항이 삭제되었습니다');
        }

        return $this->errorResponse('공지사항 삭제 실패', 500);
    }

    // 고정 공지 토글
    public function togglePin($id = null)
    {
        $adminId = session()->get('admin_id');
        
        if (!$adminId) {
            return $this->errorResponse('로그인이 필요합니다', 401);
        }

        $notice = $this->noticeModel->find($id);

        if (!$notice) {
            return $this->errorResponse('공지사항을 찾을 수 없습니다', 404);
        }

        $newPinStatus = $notice['is_pinned'] == 1 ? 0 : 1;

        // 고정으로 변경하는 경우 기존 고정 해제
        if ($newPinStatus == 1) {
            $this->noticeModel->where('is_pinned', 1)->set('is_pinned', 0)->update();
        }

        if ($this->noticeModel->update($id, ['is_pinned' => $newPinStatus])) {
            return $this->successResponse([
                'is_pinned' => $newPinStatus
            ], $newPinStatus == 1 ? '고정되었습니다' : '고정 해제되었습니다');
        }

        return $this->errorResponse('처리 실패', 500);
    }
}