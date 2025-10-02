<?= $this->include('layouts/header') ?>
<?= $this->include('layouts/sidebar') ?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">대리점 등록</h1>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form id="agencyCreateForm">
                        <div class="mb-3">
                            <label for="code" class="form-label">대리점 코드 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="code" name="code" placeholder="AG001" required>
                            <div class="form-text">영문 대문자와 숫자 조합 (예: AG001)</div>
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">대리점명 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="서울택시" required>
                        </div>

                        <div class="mb-3">
                            <label for="number" class="form-label">연락처</label>
                            <input type="text" class="form-control" id="number" name="number" placeholder="02-1234-5678">
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">주소</label>
                            <textarea class="form-control" id="address" name="address" rows="3" placeholder="서울시 강남구"></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">등록하기</button>
                            <button type="button" class="btn btn-secondary" onclick="history.back()">취소</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body">
                    <h5 class="card-title">등록 안내</h5>
                    <ul class="small">
                        <li>대리점 코드는 중복될 수 없습니다.</li>
                        <li>대리점 코드는 등록 후 변경할 수 없습니다.</li>
                        <li>등록 즉시 활성 상태로 생성됩니다.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</main>

<?= $this->section('scripts') ?>
<script>
    $('#agencyCreateForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            code: $('#code').val(),
            name: $('#name').val(),
            number: $('#number').val(),
            address: $('#address').val()
        };
        
        $.ajax({
            url: API_BASE_URL + '/agency',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function(response) {
                if (response.status === 'success') {
                    alert('대리점이 등록되었습니다.');
                    location.href = '/admin/agency/list';
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || '등록 실패';
                alert(message);
            }
        });
    });
</script>
<?= $this->endSection() ?>

<?= $this->include('layouts/footer') ?>