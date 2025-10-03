<?= $this->include('layouts/header') ?>
<?= $this->include('layouts/sidebar') ?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">대시보드</h1>
        
        <?php if (session()->get('role') == 99): ?>
        <div class="mt-2 mt-md-0">
            <select class="form-select form-select-sm" id="agencyFilter" onchange="loadDashboardStats()">
            </select>
        </div>
        <?php endif; ?>
    </div>

    <!-- 통계 카드 -->
    <div class="row g-3">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                전체 사용자
                            </div>
                            <div class="h5 mb-0 fw-bold" id="totalUsers">-</div>
                        </div>
                        <div>
                            <i class="bi bi-people fs-2 text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">
                                활성 사용자
                            </div>
                            <div class="h5 mb-0 fw-bold" id="activeUsers">-</div>
                        </div>
                        <div>
                            <i class="bi bi-check-circle fs-2 text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs fw-bold text-info text-uppercase mb-1">
                                이번주 신규
                            </div>
                            <div class="h5 mb-0 fw-bold" id="thisWeekNew">-</div>
                        </div>
                        <div>
                            <i class="bi bi-person-plus fs-2 text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">
                                만료 임박
                            </div>
                            <div class="h5 mb-0 fw-bold" id="expiringSoon">-</div>
                        </div>
                        <div>
                            <i class="bi bi-exclamation-triangle fs-2 text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 최근 등록 회원 -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">최근 등록 회원</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="px-3">이름</th>
                                    <th class="d-none d-md-table-cell">전화번호</th>
                                    <th class="d-none d-lg-table-cell">등록일</th>
                                    <th class="d-none d-lg-table-cell">만료일</th>
                                    <th class="px-3">상태</th>
                                </tr>
                            </thead>
                            <tbody id="recentUsersTable">
                                <tr>
                                    <td colspan="5" class="text-center py-4">로딩 중...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    <?php if (session()->get('role') == 99): ?>
    loadAgencyList();
    <?php else: ?>
    loadDashboardStats();
    <?php endif; ?>
});

function loadAgencyList() {
    api.get('/agency/list')
        .done(function(response) {
            if (response.status === 'success') {
                const agencies = response.data;
                
                if (agencies.length === 0) {
                    alert('등록된 대리점이 없습니다.');
                    return;
                }
                
                // 대리점 옵션 추가
                agencies.forEach(function(agency) {
                    $('#agencyFilter').append(
                        `<option value="${agency.id}">${agency.name}</option>`
                    );
                });
                
                // 첫 번째 대리점 자동 선택 및 로드
                $('#agencyFilter').val(agencies[0].id);
                loadDashboardStats();
            }
        })
        .fail(function() {
            alert('대리점 목록을 불러오는데 실패했습니다.');
        });
}

function loadDashboardStats() {
    const agencyId = $('#agencyFilter').val();
    
    api.get('/agency/dashboard/stats', { agency_id: agencyId })
        .done(function(response) {
            if (response.status === 'success') {
                const stats = response.data;
                $('#totalUsers').text(stats.total_users);
                $('#activeUsers').text(stats.active_users);
                $('#thisWeekNew').text(stats.this_week_new);
                $('#expiringSoon').text(stats.expiring_soon);
                renderRecentUsers(stats.recent_users);
            }
        })
        .fail(handleError);
}

function renderRecentUsers(users) {
    let html = '';
    
    if (users.length === 0) {
        html = '<tr><td colspan="5" class="text-center py-4">등록된 회원이 없습니다.</td></tr>';
    } else {
        users.forEach(function(user) {
            html += `
                <tr>
                    <td class="px-3">${escapeHtml(user.name)}</td>
                    <td class="d-none d-md-table-cell">${escapeHtml(user.phone_number)}</td>
                    <td class="d-none d-lg-table-cell">${user.registration_date}</td>
                    <td class="d-none d-lg-table-cell">${user.expiry_date}</td>
                    <td class="px-3">${getStatusBadge(user.status)}</td>
                </tr>
            `;
        });
    }
    
    $('#recentUsersTable').html(html);
}

function getStatusBadge(status) {
    const badges = {
        1: '<span class="badge bg-success">사용가능</span>',
        2: '<span class="badge bg-warning">테스터</span>',
        3: '<span class="badge bg-danger">사용불가</span>'
    };
    return badges[status] || '<span class="badge bg-secondary">알수없음</span>';
}
</script>
<?= $this->endSection() ?>

<?= $this->include('layouts/footer') ?>