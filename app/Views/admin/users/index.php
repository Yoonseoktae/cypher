<?= $this->include('layouts/header') ?>
<?= $this->include('layouts/sidebar') ?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">사용자 관리</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button type="button" class="btn btn-primary" onclick="location.href='/admin/users/create'">
                <i class="bi bi-plus-lg"></i> 사용자 등록
            </button>
        </div>
    </div>

    <!-- 검색 필터 -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" id="searchKeyword" placeholder="이름, 전화번호 검색">
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="statusFilter">
                        <option value="">전체 상태</option>
                        <option value="0">비인증</option>
                        <option value="1">인증</option>
                        <option value="2">중지</option>
                        <option value="3">밴</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" onclick="loadUsers(1)">검색</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 사용자 목록 테이블 -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>이름</th>
                            <th>전화번호</th>
                            <th>위치</th>
                            <th>등록일</th>
                            <th>만료일</th>
                            <th>상태</th>
                            <th>관리</th>
                        </tr>
                    </thead>
                    <tbody id="usersTable">
                        <tr>
                            <td colspan="8" class="text-center">로딩 중...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- 페이지네이션 -->
            <nav>
                <ul class="pagination justify-content-center" id="pagination"></ul>
            </nav>
        </div>
    </div>
</main>

<?= $this->section('scripts') ?>
<script>
    let currentPage = 1;

    $(document).ready(function() {
        loadUsers(1);
    });

    function loadUsers(page) {
        currentPage = page;
        const search = $('#searchKeyword').val();
        const status = $('#statusFilter').val();
        
        $.ajax({
            url: API_BASE_URL + '/users',
            method: 'GET',
            data: {
                page: page,
                limit: 20,
                search: search,
                status: status
            },
            success: function(response) {
                if (response.status === 'success') {
                    renderUsersTable(response.data.users);
                    renderPagination(response.data.pagination);
                }
            },
            error: handleError
        });
    }

    function renderUsersTable(users) {
        let html = '';
        
        if (users.length === 0) {
            html = '<tr><td colspan="8" class="text-center">등록된 사용자가 없습니다.</td></tr>';
        } else {
            users.forEach(function(user) {
                html += `
                    <tr>
                        <td>${user.id}</td>
                        <td>${user.name}</td>
                        <td>${user.phone_number}</td>
                        <td>${user.location || '-'}</td>
                        <td>${user.registration_date}</td>
                        <td>${user.expiry_date}</td>
                        <td>${getStatusBadge(user.status)}</td>
                        <td>
                            <button class="btn btn-sm btn-info" onclick="viewUser(${user.id})">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-warning" onclick="editUser(${user.id})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteUser(${user.id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        }
        
        $('#usersTable').html(html);
    }

    function renderPagination(pagination) {
        let html = '';
        
        for (let i = 1; i <= pagination.total_pages; i++) {
            const active = i === pagination.current_page ? 'active' : '';
            html += `<li class="page-item ${active}">
                        <a class="page-link" href="#" onclick="loadUsers(${i}); return false;">${i}</a>
                     </li>`;
        }
        
        $('#pagination').html(html);
    }

    function getStatusBadge(status) {
        const badges = {
            0: '<span class="badge bg-secondary">비인증</span>',
            1: '<span class="badge bg-success">인증</span>',
            2: '<span class="badge bg-warning">중지</span>',
            3: '<span class="badge bg-danger">밴</span>'
        };
        return badges[status] || '<span class="badge bg-secondary">알수없음</span>';
    }

    function viewUser(id) {
        location.href = `/admin/users/${id}`;
    }

    function editUser(id) {
        location.href = `/admin/users/${id}/edit`;
    }

    function deleteUser(id) {
        if (confirm('정말 삭제하시겠습니까?')) {
            $.ajax({
                url: API_BASE_URL + '/users/' + id,
                method: 'DELETE',
                success: function(response) {
                    alert('삭제되었습니다.');
                    loadUsers(currentPage);
                },
                error: handleError
            });
        }
    }
</script>
<?= $this->endSection() ?>

<?= $this->include('layouts/footer') ?>