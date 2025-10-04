<?= $this->include('layouts/header') ?>
<?= $this->include('layouts/sidebar') ?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">대리점 등록</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button type="button" class="btn btn-secondary" onclick="location.href='/admin/agency'">
                <i class="bi bi-arrow-left"></i> 목록으로
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form id="agencyForm">
                        <div class="mb-3">
                            <label for="agency_name" class="form-label">대리점명 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="agency_name" required>
                        </div>

                        <div class="mb-3">
                            <label for="phone_number" class="form-label">전화번호 <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="phone_number" placeholder="01012345678" required>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 대리점 코드는 자동으로 생성됩니다.
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-lg"></i> 등록하기
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    $('#agencyForm').on('submit', function(e) {
        e.preventDefault();
        
        const data = {
            name: $('#agency_name').val().trim(),
            number: $('#phone_number').val().trim()
        };
        
        if (!data.name) {
            alert('대리점명을 입력하세요.');
            return;
        }
        
        if (!data.number) {
            alert('전화번호를 입력하세요.');
            return;
        }
        
        api.post('/agency', data)
            .done(function(response) {
                if (response.status === 'success') {
                    alert('대리점이 등록되었습니다.');
                    location.href = '/admin/agency';
                }
            })
            .fail(handleError);
    });
});
</script>
<?= $this->endSection() ?>

<?= $this->include('layouts/footer') ?>