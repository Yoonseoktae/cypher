<?php 
$session = session();
$role = $session->get('role');
?>

<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <?php if ($role == 1): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $active_menu == 'dashboard' ? 'active' : '' ?>" 
                    href="/admin/dashboard">
                        <i class="bi bi-speedometer2"></i> 대시보드
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $active_menu == 'users' ? 'active' : '' ?>" 
                    href="/admin/users">
                        <i class="bi bi-people"></i> 사용자 관리
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $active_menu == 'notices' ? 'active' : '' ?>" 
                    href="/admin/notices">
                        <i class="bi bi-megaphone"></i> 공지사항
                    </a>
                </li>
            <?php endif; ?>
            
            <?php if ($role == 99): ?>
            <li class="nav-item">
                <a class="nav-link <?= ($active_menu ?? '') === 'settlement' ? 'active' : '' ?>" 
                   href="/admin/settlement">
                    <i class="bi bi-calculator"></i> 정산 관리
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($active_menu ?? '') === 'agency' ? 'active' : '' ?>" 
                href="/admin/agency">
                    <i class="bi bi-building"></i> 대리점 관리
                </a>
            </li>
            <?php endif; ?>
            
            <hr>
            <li class="nav-item">
                <a class="nav-link" href="#" onclick="logout(); return false;">
                    <i class="bi bi-box-arrow-right"></i> 로그아웃
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="https://firebasestorage.googleapis.com/v0/b/callproject-b0de6.firebasestorage.app/o/taxi%2Fholic_1.3.apk?alt=media&token=51808723-d9d3-4871-9525-d5490567491e">
                    <i class="bi bi-cloud-arrow-down-fill"></i> 어플 다운로드
                </a>
            </li>
        </ul>
    </div>
</nav>