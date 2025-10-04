<?= $this->include('layouts/header') ?>
<?= $this->include('layouts/sidebar') ?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">대리점 관리</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button type="button" class="btn btn-primary" onclick="location.href='/admin/agency/create'">
                <i class="bi bi-plus-lg"></i> 대리점 등록
            </button>
        </div>
    </div>

    <!-- 검색 필터 -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <input type="text" class="form-control" id="searchKeyword" placeholder="대리점명 검색">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" onclick="loadAgencies()">검색</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 대리점 목록 테이블 -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>대리점 코드</th>
                            <th>대리점명</th>
                            <th>총 가입자</th>
                            <th>이번주 가입자</th>
                            <th>관리</th>
                        </tr>
                    </thead>
                    <tbody id="agenciesTable">
                        <tr>
                            <td colspan="4" class="text-center">로딩 중...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- 수정 모달 -->
<div class="modal fade" id="editAgencyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">대리점명 수정</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit_agency_id">

                <div class="mb-3">
                    <label class="form-label">대리점명</label>
                    <input type="text" class="form-control" id="edit_agency_name">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                <button type="button" class="btn btn-primary" onclick="updateAgency()">저장</button>
            </div>
        </div>
    </div>
</div>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    loadAgencies();
});

function loadAgencies() {
    const search = $('#searchKeyword').val();
    
    api.get('/agency/list', { search: search })
        .done(function(response) {
            if (response.status === 'success') {
                renderAgenciesTable(response.data);
            }
        })
        .fail(handleError);
}

function renderAgenciesTable(agencies) {
    let html = '';
    
    if (agencies.length === 0) {
        html = '<tr><td colspan="4" class="text-center">등록된 대리점이 없습니다.</td></tr>';
    } else {
        agencies.forEach(function(agency) {
            html += `
                <tr>
                    <td>${escapeHtml(agency.id)}</td>
                    <td>${escapeHtml(agency.name)}</td>
                    <td>${agency.total_users}</td>
                    <td>${agency.this_week_new}</td>
                    <td>
                        <button class="btn btn-sm btn-warning" onclick="openEditModal(${agency.id}, '${escapeHtml(agency.name)}')">
                            <i class="bi bi-pencil"></i> 수정
                        </button>
                    </td>
                </tr>
            `;
        });
    }
    
    $('#agenciesTable').html(html);
}

function openEditModal(agencyId, agencyName) {
    $('#edit_agency_id').val(agencyId);
    $('.edit_agency_id').val(agencyId);
    $('#edit_agency_name').val(agencyName);
    
    new bootstrap.Modal(document.getElementById('editAgencyModal')).show();
}

function updateAgency() {
    const data = {
        id: $('#edit_agency_id').val(),
        name: $('#edit_agency_name').val().trim()
    };
    
    if (!data.name) {
        alert('대리점명을 입력하세요.');
        return;
    }
    
    if (!confirm('수정하시겠습니까?')) return;
    
    api.put('/agency', data)
        .done(function(response) {
            if (response.status === 'success') {
                alert('수정되었습니다.');
                bootstrap.Modal.getInstance(document.getElementById('editAgencyModal')).hide();
                loadAgencies();
            }
        })
        .fail(handleError);
}
</script>
<?= $this->endSection() ?>

<?= $this->include('layouts/footer') ?>