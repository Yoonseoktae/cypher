<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="api-base-url" content="<?= base_url('api/v1') ?>">
    <title><?= $title ?? 'Holic' ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
    <nav class="navbar navbar-dark sticky-top bg-dark p-0 shadow">
        <div class="container-fluid">
            <!-- 모바일: 햄버거만 -->
            <button class="navbar-toggler d-md-none" type="button" 
                    data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- PC: 제목 -->
            <a class="navbar-brand d-none d-md-block col-md-3 col-lg-2 me-0 px-3" href="/admin/dashboard">
                <i class="bi bi-shield-lock"></i> Holic Admin
            </a>
             
            <!-- 모바일: 로그아웃만 -->
            <a class="nav-link text-white px-3 d-md-none" href="#" onclick="logout(); return false;">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">