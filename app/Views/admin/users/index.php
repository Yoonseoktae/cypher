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
                        <option value="1">사용가능</option>
                        <option value="2">테스터</option>
                        <option value="3">사용불가</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="serviceFilter">
                        <option value="">전체 서비스</option>
                        <option value="normal">일반</option>
                        <option value="venti">벤티</option>
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
                            <th>서비스타입</th>
                            <th>가입날짜</th>
                            <th>유효기간</th>
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
            
            <nav>
                <ul class="pagination justify-content-center" id="pagination"></ul>
            </nav>
        </div>
    </div>
</main>

<!-- 사용자 수정 모달 -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">사용자 정보 수정</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>이름:</strong> <span id="modal_name"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>전화번호:</strong> <span id="modal_phone"></span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>서비스타입:</strong> <span id="modal_service"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>작동방식:</strong> <span id="modal_franchise"></span>
                    </div>
                </div>

                <hr>

                <!-- 기본 정보 수정 -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">기본 정보 수정</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">이름</label>
                            <input type="text" class="form-control" id="edit_name">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">전화번호</label>
                            <input type="text" class="form-control" id="edit_phone">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">서비스타입</label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="edit_app_service" id="edit_normalService" value="normal">
                                    <label class="form-check-label" for="edit_normalService">일반</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="edit_app_service" id="edit_vantiService" value="venti">
                                    <label class="form-check-label" for="edit_vantiService">벤티</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3" id="edit_franchiseSection">
                            <label class="form-label">작동방식</label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="edit_is_franchise" id="edit_nonFranchise" value="0">
                                    <label class="form-check-label" for="edit_nonFranchise">비가맹</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="edit_is_franchise" id="edit_franchise" value="1">
                                    <label class="form-check-label" for="edit_franchise">가맹</label>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-primary btn-sm w-100" onclick="updateUserInfo()">정보 수정</button>
                    </div>
                </div>

                <!-- 가입일 수정 -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">가입일 수정</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-8">
                                <input type="date" class="form-control" id="modal_signup_date">
                            </div>
                            <div class="col-md-4">
                                <button class="btn btn-primary btn-sm w-100" onclick="updateSignupDate()">수정</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 상태 변경 -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">상태 변경</h6>
                    </div>
                    <div class="card-body">
                        <select class="form-select" id="modal_status">
                            <option value="1">사용가능</option>
                            <option value="2">테스터</option>
                            <option value="3">사용불가</option>
                        </select>
                        <button class="btn btn-primary btn-sm mt-2 w-100" onclick="updateStatus()">상태 변경</button>
                    </div>
                </div>

                <!-- 유효기간 수정 -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">유효기간 수정</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-8">
                                <input type="date" class="form-control" id="modal_expiry_date">
                            </div>
                            <div class="col-md-4">
                                <button class="btn btn-primary btn-sm w-100" onclick="updateExpiryDate()">수정</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 로그 다운로드 -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">사용자 로그 이력</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-2">최근 7일간의 로그를 다운로드합니다.</p>
                        <button class="btn btn-success btn-sm w-100" onclick="downloadLogs()">
                            <i class="bi bi-download"></i> 로그 다운로드 (TXT)
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
            </div>
        </div>
    </div>
</div>

<?= $this->section('scripts') ?>
<script>
    let currentPage = 1;
    let currentUserId = null;

    $(document).ready(function() {
        loadUsers(1);
    });

    function loadUsers(page = 1) {
        currentPage = page;
        const search = $('#searchInput').val();
        const status = $('#statusFilter').val();
        const service = $('#serviceFilter').val(); // 추가
        
        api.get('/users', {
            page: page,
            limit: 20,
            search: search,
            status: status,
            app_service: service // 추가
        })
        .done(function(response) {
            if (response.status === 'success') {
                const data = response.data;
                renderUsersTable(data.users);
                renderPagination(data.pagination);
            }
        })
        .fail(handleError);
    }

    function renderUsersTable(users) {
        let html = '';
        
        if (users.length === 0) {
            html = '<tr><td colspan="8" class="text-center py-4">등록된 사용자가 없습니다.</td></tr>';
        } else {
            users.forEach(function(user) {
                html += `
                    <tr>
                        <td>${user.id}</td>
                        <td>${escapeHtml(user.name)}</td>
                        <td>${formatPhoneNumber(user.phone_number)}</td>
                        <td>${getServiceTypeBadge(user.app_service)}</td>
                        <td>${user.signup_date || '-'}</td>
                        <td>${user.expiry_date ? user.expiry_date.split(' ')[0] : '-'}</td>
                        <td>${getStatusBadge(user.status)}</td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="openEditModal(${user.id})">
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

    function getServiceTypeBadge(appService) {
        const badges = {
            'normal': '<span class="badge bg-primary">일반</span>',
            'venti': '<span class="badge bg-info">벤티</span>'
        };
        return badges[appService] || '<span class="badge bg-secondary">' + (appService || '-') + '</span>';
    }

    function formatPhoneNumber(phone) {
        if (!phone) return '-';
        // 010-1234-5678 형식으로 변환
        if (phone.length === 11) {
            return phone.replace(/(\d{3})(\d{4})(\d{4})/, '$1-$2-$3');
        } else if (phone.length === 10) {
            return phone.replace(/(\d{3})(\d{3})(\d{4})/, '$1-$2-$3');
        }
        return phone;
    }

    function getStatusBadge(status) {
        const badges = {
            1: '<span class="badge bg-success">사용가능</span>',
            2: '<span class="badge bg-warning">테스터</span>',
            3: '<span class="badge bg-danger">사용불가</span>'
        };
        return badges[status] || '<span class="badge bg-secondary">알수없음</span>';
    }

    function getStatusBadge(status) {
        const badges = {
            1: '<span class="badge bg-success">사용가능</span>',
            2: '<span class="badge bg-warning">테스터</span>',
            3: '<span class="badge bg-danger">사용불가</span>'
        };
        return badges[status] || '<span class="badge bg-secondary">알수없음</span>';
    }

    function openEditModal(userId) {
        currentUserId = userId;
        
        api.get('/users/' + userId)
            .done(function(response) {
                if (response.status === 'success') {
                    const user = response.data;
                    
                    $('#modal_name').text(user.name);
                    $('#modal_phone').text(formatPhoneNumber(user.phone_number));
                    $('#modal_service').text(user.app_service == 'normal' ? '일반' : user.app_service == 'venti' ? '벤티' : '-');
                    $('#modal_franchise').text(user.is_franchise == 1 ? '가맹' : user.is_franchise == 0 ? '비가맹' : '-');
                    $('#modal_status').val(user.status);
                    
                    const expiryDate = user.expiry_date ? user.expiry_date.split(' ')[0] : '';
                    $('#modal_expiry_date').val(expiryDate);
                    
                    const signupDate = user.signup_date || '';
                    $('#modal_signup_date').val(signupDate);
                    
                    $('#edit_name').val(user.name);
                    $('#edit_phone').val(user.phone_number);
                    $(`input[name="edit_app_service"][value="${user.app_service}"]`).prop('checked', true);
                    
                    if (user.app_service == 'normal' && user.is_franchise !== null) {
                        $(`input[name="edit_is_franchise"][value="${user.is_franchise}"]`).prop('checked', true);
                        $('#edit_franchiseSection').show();
                    } else {
                        $('#edit_franchiseSection').hide();
                    }
                    
                    new bootstrap.Modal(document.getElementById('editUserModal')).show();
                }
            })
            .fail(handleError);
    }

    // 서비스타입 변경 시 작동방식 표시/숨김
    $(document).on('change', 'input[name="edit_app_service"]', function() {
        if ($(this).val() == 'normal') {
            $('#edit_franchiseSection').show();
        } else {
            $('#edit_franchiseSection').hide();
        }
    });

    function updateUserInfo() {
        const appService = $('input[name="edit_app_service"]:checked').val();
        
        const data = {
            name: $('#edit_name').val().trim(),
            phone_number: $('#edit_phone').val().trim(),
            app_service: appService
        };
        
        if (appService == 'normal') {
            data.is_franchise = $('input[name="edit_is_franchise"]:checked').val();
        }
        
        if (!confirm('정보를 수정하시겠습니까?')) return;
        
        api.put('/users/' + currentUserId, data)
            .done(function(response) {
                if (response.status === 'success') {
                    alert('수정되었습니다.');
                    loadUsers(currentPage);
                    
                    $('#editUserModal').modal('hide');
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open').css('padding-right', '');
                    
                    setTimeout(function() {
                        openEditModal(currentUserId);
                    }, 400);
                }
            })
            .fail(function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.messages) {
                    alert(xhr.responseJSON.messages.error);
                } else {
                    handleError(xhr);
                }
            });
    }

    function updateSignupDate() {
        const signupDate = $('#modal_signup_date').val();
        
        if (!signupDate) {
            alert('가입일을 선택하세요.');
            return;
        }
        
        if (!confirm('가입일을 변경하시겠습니까?')) return;
        
        api.put('/users/' + currentUserId, {
            signup_date: signupDate
        })
        .done(function(response) {
            if (response.status === 'success') {
                alert('가입일이 변경되었습니다.');
                loadUsers(currentPage);
                
                $('#editUserModal').modal('hide');
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css('padding-right', '');
                
                setTimeout(function() {
                    openEditModal(currentUserId);
                }, 400);
            }
        })
        .fail(handleError);
    }

    function updateStatus() {
        const newStatus = $('#modal_status').val();
        
        if (!confirm('상태를 변경하시겠습니까?')) return;
        
        api.post('/users/' + currentUserId + '/status', {
            status: newStatus
        })
        .done(function(response) {
            if (response.status === 'success') {
                alert('상태가 변경되었습니다.');
                loadUsers(currentPage);
                
                $('#editUserModal').modal('hide');
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css('padding-right', '');
                
                setTimeout(function() {
                    openEditModal(currentUserId);
                }, 400);
            }
        })
        .fail(handleError);
    }

    function updateExpiryDate() {
        const expiryDate = $('#modal_expiry_date').val();
        
        if (!expiryDate) {
            alert('유효기간을 선택하세요.');
            return;
        }
        
        if (!confirm('유효기간을 변경하시겠습니까?')) return;
        
        api.post('/users/' + currentUserId + '/extend', {
            expiry_date: expiryDate
        })
        .done(function(response) {
            if (response.status === 'success') {
                alert('유효기간이 변경되었습니다.');
                loadUsers(currentPage);
                
                // 기존 모달과 백드롭 완전히 제거
                $('#editUserModal').modal('hide');
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css('padding-right', '');
                
                // 재오픈
                setTimeout(function() {
                    openEditModal(currentUserId);
                }, 400);
            }
        })
        .fail(handleError);
    }

    function downloadLogs() {
        const phoneNumber = $('#modal_phone').text();
        const userName = $('#modal_name').text();
        
        api.get('/logs/download/' + currentUserId + '/' + phoneNumber)
            .done(function(response) {
                if (response.status === 'success') {
                    if (!response.data) {
                        alert('최근 7일간 로그 이력이 없습니다.');
                        return;
                    }
                    
                    const blob = new Blob([response.data], { 
                        type: 'text/plain;charset=utf-8;' 
                    });
                    const link = document.createElement('a');
                    const filename = `${userName}_${phoneNumber}_최근7일_로그.txt`;
                    
                    link.href = URL.createObjectURL(blob);
                    link.download = filename;
                    link.style.display = 'none';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    URL.revokeObjectURL(link.href);
                }
            })
            .fail(handleError);
    }

    function deleteUser(id) {
        if (confirm('정말 삭제하시겠습니까?')) {
            api.delete('/users/' + id)
                .done(function(response) {
                    alert('삭제되었습니다.');
                    loadUsers(currentPage);
                })
                .fail(handleError);
        }
    }
</script>
<?= $this->endSection() ?>

<?= $this->include('layouts/footer') ?>