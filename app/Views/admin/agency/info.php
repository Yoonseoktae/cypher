<?= $this->include('layouts/header') ?>
<?= $this->include('layouts/sidebar') ?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">대리점 정보</h1>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">기본 정보</h5>
                </div>
                <div class="card-body">
                    <form id="agencyUpdateForm">
                        <div class="mb-3">
                            <label for="code" class="form-label">대리점 코드</label>
                            <input type="text" class="form-control" id="code" name="code" readonly>
                            <div class="form-text">대리점 코드는 변경할 수 없습니다.</div>
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">대리점명 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>

                        <div class="mb-3">
                            <label for="number" class="form-label">연락처</label>
                            <input type="text" class="form-control" id="number" name="number" placeholder="02-1234-5678">
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">주소</label>
                            <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">상태</label>
                            <select class="form-select" id="status" name="status">
                                <option value="0">비활성</option>
                                <option value="1">활성</option>
                            </select>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">저장하기</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">통계</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>총 기사 수</span>
                        <strong id="stat_total">-</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>활성 기사</span>
                        <strong id="stat_active">-</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>이번주 신규</span>
                        <strong id="stat_weekly">-</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>만료 임박</span>
                        <strong id="stat_expiring">-</strong>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="card-title">생성일</h6>
                    <p class="card-text" id="created_at">-</p>
                    
                    <h6 class="card-title">최종 수정일</h6>
                    <p class="card-text" id="updated_at">-</p>
                </div>
            </div>
        </div>
    </div>
</main>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        loadAgencyInfo();
        loadDashboardStats();
    });

    function loadAgencyInfo() {
        $.ajax({
            url: API_BASE_URL + '/agency/<?= session()->get('agency_id') ?>',
            method: 'GET',
            success: function(response) {
                if (response.status === 'success') {
                    const agency = response.data;
                    $('#code').val(agency.code);
                    $('#name').val(agency.name);
                    $('#number').val(agency.number);
                    $('#address').val(agency.address);
                    $('#status').val(agency.status);
                    $('#created_at').text(agency.created_at);
                    $('#updated_at').text(agency.updated_at);
                }
            },
            error: handleError
        });
    }

    function loadDashboardStats() {
        $.ajax({
            url: API_BASE_URL + '/agency/dashboard/stats',
            method: 'GET',
            success: function(response) {
                if (response.status === 'success') {
                    $('#stat_total').text(response.data.total_drivers);
                    $('#stat_active').text(response.data.active_drivers);
                    $('#stat_weekly').text(response.data.weekly_new);
                    $('#stat_expiring').text(response.data.expiring_soon);
                }
            },
            error: handleError
        });
    }

    $('#agencyUpdateForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            name: $('#name').val(),
            number: $('#number').val(),
            address: $('#address').val(),
            status: $('#status').val()
        };
        
        $.ajax({
            url: API_BASE_URL + '/agency',
            method: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function(response) {
                if (response.status === 'success') {
                    alert('대리점 정보가 수정되었습니다.');
                    loadAgencyInfo();
                }
            },
            error: handleError
        });
    });
</script>
<?= $this->endSection() ?>

<?= $this->include('layouts/footer') ?>