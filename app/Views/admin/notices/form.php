<!-- app/Views/admin/notices/form.php -->
<?= $this->include('layouts/header') ?>
<?= $this->include('layouts/sidebar') ?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><?= $mode == 'edit' ? '공지사항 수정' : '공지사항 작성' ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button type="button" class="btn btn-secondary" onclick="location.href='/admin/notices'">
                <i class="bi bi-arrow-left"></i> 목록으로
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form id="noticeForm">
                <div class="mb-3">
                    <label for="title" class="form-label">제목 <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="title" name="title" required>
                </div>

                <div class="mb-3">
                    <label for="content" class="form-label">내용 <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="content" name="content" rows="15" required style="font-family: monospace;"></textarea>
                    <div class="form-text">텍스트 그대로 저장됩니다. 줄바꿈도 그대로 표시됩니다.</div>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_pinned" name="is_pinned" value="1">
                        <label class="form-check-label" for="is_pinned">
                            <i class="bi bi-pin-angle-fill"></i> 고정 공지로 설정 (체크 시 다른 고정 공지는 자동 해제됩니다)
                        </label>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <?= $mode == 'edit' ? '수정하기' : '등록하기' ?>
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="location.href='/admin/notices'">취소</button>
                </div>
            </form>
        </div>
    </div>
</main>

<?= $this->section('scripts') ?>
<script>
    const mode = '<?= $mode ?>';
    const noticeId = <?= $notice_id ?? 'null' ?>;

    $(document).ready(function() {
        if (mode === 'edit' && noticeId) {
            loadNotice();
        }
    });

    function loadNotice() {
        api.get('/notices/' + noticeId)
            .done(function(response) {
                if (response.status === 'success') {
                    const notice = response.data;
                    $('#title').val(notice.title);
                    $('#content').val(notice.content);
                    $('#is_pinned').prop('checked', notice.is_pinned == 1);
                }
            })
            .fail(handleError);
    }

    $('#noticeForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            title: $('#title').val(),
            content: $('#content').val(),
            is_pinned: $('#is_pinned').is(':checked') ? 1 : 0
        };
        
        const promise = mode === 'edit' 
            ? api.put('/notices/' + noticeId, formData)
            : api.post('/notices', formData);
        
        promise
            .done(function(response) {
                if (response.status === 'success') {
                    alert(mode === 'edit' ? '수정되었습니다.' : '등록되었습니다.');
                    location.href = '/admin/notices';
                }
            })
            .fail(handleError);
    });
</script>
<?= $this->endSection() ?>

<?= $this->include('layouts/footer') ?>