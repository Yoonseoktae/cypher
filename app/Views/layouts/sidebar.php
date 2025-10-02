<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="sidebar-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= $active_menu == 'dashboard' ? 'active' : '' ?>" href="/admin/dashboard">
                    <i class="bi bi-house-door"></i> 대시보드
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $active_menu == 'users' ? 'active' : '' ?>" href="/admin/users">
                    <i class="bi bi-people"></i> 사용자 관리
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $active_menu == 'agency' ? 'active' : '' ?>" href="/admin/agency/info">
                    <i class="bi bi-building"></i> 대리점 정보
                </a>
            </li>
        </ul>
    </div>
</nav>