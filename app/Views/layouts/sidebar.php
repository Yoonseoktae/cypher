<?php 
$session = session();
$role = $session->get('role');
?>

<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= ($active_menu ?? '') === 'dashboard' ? 'active' : '' ?>" 
                   href="/admin/dashboard">
                    <i class="bi bi-speedometer2"></i> 대시보드
                </a>
            </li>
            
            <?php if ($role == 10): ?>
            <li class="nav-item">
                <a class="nav-link <?= ($active_menu ?? '') === 'users' ? 'active' : '' ?>" 
                   href="/admin/users">
                    <i class="bi bi-people"></i> 사용자 관리
                </a>
            </li>
            <?php endif; ?>
            
            <?php if ($role == 99): ?>
            <li class="nav-item">
                <a class="nav-link <?= ($active_menu ?? '') === 'agency' ? 'active' : '' ?>" 
                   href="/admin/agency/info">
                    <i class="bi bi-building"></i> 대리점 관리
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($active_menu ?? '') === 'notices' ? 'active' : '' ?>" 
                   href="/admin/notices">
                    <i class="bi bi-megaphone"></i> 공지사항
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($active_menu ?? '') === 'settlement' ? 'active' : '' ?>" 
                   href="/admin/settlement">
                    <i class="bi bi-calculator"></i> 정산 관리
                </a>
            </li>
            <?php endif; ?>
            
            <hr>
            <li class="nav-item">
                <a class="nav-link" href="#" onclick="logout(); return false;">
                    <i class="bi bi-box-arrow-right"></i> 로그아웃
                </a>
            </li>
        </ul>
    </div>
</nav>