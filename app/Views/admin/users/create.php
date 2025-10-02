<?= $this->include('layouts/header') ?>
<?= $this->include('layouts/sidebar') ?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">사용자 등록</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button type="button" class="btn btn-secondary" onclick="location.href='/admin/users'">
                <i class="bi bi-arrow-left"></i> 목록으로
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form id="userCreateForm">
                        <div class="mb-3">
                            <label for="name" class="form-label">이름 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>

                        <div class="mb-3">
                            <label for="phone_number" class="form-label">전화번호 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="phone_number" name="phone_number" placeholder="010-1234-5678" required>
                        </div>

                        <div class="mb-3">
                            <label for="location" class="form-label">위치</label>
                            <input type="text" class="form-control" id="location" name="location" placeholder="서울">
                        </div>

                        <div class="mb-3">
                            <label for="expiry_date" class="form-label">만료일 <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="expiry_date" name="expiry_date" required>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">상태</label>
                            <select class="form-select" id="status" name="status">
                                <option value="0">비인증</option>
                                <option value="1" selected>인증</option>
                                <option value="2">중지</option>
                                <option value="3">밴</option>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">등록하기</button>
                            <button type="button" class="btn btn-secondary" onclick="location.href='/admin/users'">취소</button>
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
                        <li>이름과 전화번호는 필수 입력 항목입니다.</li>
                        <li>전화번호는 중복될 수 없습니다.</li>
                        <li>만료일은 사용자가 앱을 사용할 수 있는 기한입니다.</li>
                        <li>기본 상태는 '인증'으로 설정됩니다.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</main>

<?= $this->section('scripts') ?>
<script>
    // 오늘 날짜의 1년 후를 기본값으로 설정
    $(document).ready(function() {
        const today = new Date();
        const oneYearLater = new Date(today.setFullYear(today.getFullYear() + 1));
        const dateString = oneYearLater.toISOString().split('T')[0];
        $('#expiry_date').val(dateString);
    });

    $('#userCreateForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            name: $('#name').val(),
            phone_number: $('#phone_number').val(),
            location: $('#location').val(),
            expiry_date: $('#expiry_date').val(),
            status: $('#status').val()
        };
        
        $.ajax({
            url: API_BASE_URL + '/users',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function(response) {
                if (response.status === 'success') {
                    alert('사용자가 등록되었습니다.');
                    location.href = '/admin/users';
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