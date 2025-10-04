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
                            <input type="tel" class="form-control" id="phone_number" placeholder="01012345678" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">서비스타입 <span class="text-danger">*</span></label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="app_service" id="normalService" value="normal" checked>
                                    <label class="form-check-label" for="normalService">일반</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="app_service" id="vantiService" value="venti">
                                    <label class="form-check-label" for="vantiService">벤티</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="signup_date" class="form-label">가입날짜</label>
                            <input type="date" class="form-control" id="signup_date">
                        </div>

                        <div class="mb-3" id="franchiseSection">
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
    // 서비스타입에 따라 작동방식 표시/숨김
    $('input[name="app_service"]').on('change', function() {
        if ($(this).val() == 'normal') {
            $('#franchiseSection').show();
        } else { // venti
            $('#franchiseSection').hide();
        }
    });

    $('#userForm').on('submit', function(e) {
        e.preventDefault();
        
        const appService = $('input[name="app_service"]:checked').val();
        
        const data = {
            name: $('#name').val().trim(),
            phone_number: $('#phone_number').val().trim(),
            app_service: appService,
            signup_date: $('#signup_date').val(),
            status: $('#status').val()
        };
        
        // normal일 때만 is_franchise 추가
        if (appService == 'normal') {
            data.is_franchise = $('input[name="is_franchise"]:checked').val();
        }

        if (!data.name || !data.phone_number) {
            alert('필수 항목을 입력하세요.');
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