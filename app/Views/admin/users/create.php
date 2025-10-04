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
                    <form id="userForm">
                        <div class="mb-3">
                            <label for="name" class="form-label">이름 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" required>
                        </div>

                        <div class="mb-3">
                            <label for="phone_number" class="form-label">전화번호 <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="phone_number" placeholder="010-1234-5678" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">작동방식 <span class="text-danger">*</span></label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_franchise" id="nonFranchise" value="0" checked>
                                    <label class="form-check-label" for="nonFranchise">비가맹</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_franchise" id="franchise" value="1">
                                    <label class="form-check-label" for="franchise">가맹</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">상태 <span class="text-danger">*</span></label>
                            <select class="form-select" id="status">
                                <option value="1" selected>사용가능</option>
                                <option value="2">테스터</option>
                                <option value="3">사용불가</option>
                            </select>
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
    $('#userForm').on('submit', function(e) {
        e.preventDefault();
        
        const data = {
            name: $('#name').val().trim(),
            phone_number: $('#phone_number').val().trim(),
            is_franchise: $('input[name="is_franchise"]:checked').val(),
            status: $('#status').val()
        };
        
        if (!data.name || !data.phone_number) {
            alert('모든 필수 항목을 입력하세요.');
            return;
        }
        
        api.post('/users', data)
            .done(function(response) {
                if (response.status === 'success') {
                    alert('사용자가 등록되었습니다.');
                    location.href = '/admin/users';
                }
            })
            .fail(function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.messages) {
                    alert(xhr.responseJSON.messages.error);
                } else {
                    handleError(xhr);
                }
            });
    });
});
</script>
<?= $this->endSection() ?>

<?= $this->include('layouts/footer') ?>