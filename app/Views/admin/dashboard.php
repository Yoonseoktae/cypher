<?= $this->include('layouts/header') ?>
<?= $this->include('layouts/sidebar') ?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">대시보드</h1>
    </div>

    <!-- 통계 카드 -->
    <div class="row" id="statsCards">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">총 기사 수</h5>
                    <h2 class="mb-0" id="totalDrivers">-</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">활성 기사</h5>
                    <h2 class="mb-0" id="activeDrivers">-</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">이번주 신규</h5>
                    <h2 class="mb-0" id="weeklyNew">-</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">만료 임박</h5>
                    <h2 class="mb-0" id="expiringSoon">-</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- 최근 등록 사용자 -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">최근 등록 사용자</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>이름</th>
                            <th>전화번호</th>
                            <th>등록일</th>
                            <th>만료일</th>
                            <th>상태</th>
                        </tr>
                    </thead>
                    <tbody id="recentUsers">
                        <tr>
                            <td colspan="6" class="text-center">로딩 중...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        loadDashboardStats();
        loadRecentUsers();
    });

    // 대시보드 통계 로드
    function loadDashboardStats() {
        $.ajax({
            url: API_BASE_URL + '/agency/dashboard/stats',
            method: 'GET',
            success: function(response) {
                if (response.status === 'success') {
                    $('#totalDrivers').text(response.data.total_drivers);
                    $('#activeDrivers').text(response.data.active_drivers);
                    $('#weeklyNew').text(response.data.weekly_new);
                    $('#expiringSoon').text(response.data.expiring_soon);
                }
            },
            error: handleError
        });
    }

    // 최근 사용자 로드
    function loadRecentUsers() {
        $.ajax({
            url: API_BASE_URL + '/users?limit=10',
            method: 'GET',
            success: function(response) {
                if (response.status === 'success') {
                    const users = response.data.users;
                    let html = '';
                    
                    if (users.length === 0) {
                        html = '<tr><td colspan="6" class="text-center">등록된 사용자가 없습니다.</td></tr>';
                    } else {
                        users.forEach(function(user) {
                            const statusBadge = getStatusBadge(user.status);
                            html += `
                                <tr>
                                    <td>${user.id}</td>
                                    <td>${user.name}</td>
                                    <td>${user.phone_number}</td>
                                    <td>${user.registration_date}</td>
                                    <td>${user.expiry_date}</td>
                                    <td>${statusBadge}</td>
                                </tr>
                            `;
                        });
                    }
                    
                    $('#recentUsers').html(html);
                }
            },
            error: handleError
        });
    }

    // 상태 뱃지
    function getStatusBadge(status) {
        const badges = {
            0: '<span class="badge bg-secondary">비인증</span>',
            1: '<span class="badge bg-success">인증</span>',
            2: '<span class="badge bg-warning">중지</span>',
            3: '<span class="badge bg-danger">밴</span>'
        };
        return badges[status] || '<span class="badge bg-secondary">알수없음</span>';
    }
</script>
<?= $this->endSection() ?>

<?= $this->include('layouts/footer') ?>