<!-- app/Views/admin/notices/index.php -->
<?= $this->include('layouts/header') ?>
<?= $this->include('layouts/sidebar') ?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">공지사항</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button type="button" class="btn btn-primary" onclick="location.href='/admin/notices/create'">
                <i class="bi bi-plus-lg"></i> 공지사항 작성
            </button>
        </div>
    </div>

    <!-- 고정 공지 -->
    <div id="pinnedNoticeArea"></div>

    <!-- 일반 공지 목록 -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="60">번호</th>
                            <th>제목</th>
                            <th width="120">작성일</th>
                            <th width="100">고정</th>
                            <th width="150">관리</th>
                        </tr>
                    </thead>
                    <tbody id="noticesTable">
                        <tr>
                            <td colspan="5" class="text-center">로딩 중...</td>
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

<?= $this->section('scripts') ?>
<script>
    let currentPage = 1;

    $(document).ready(function() {
        loadNotices(1);
    });

    function loadNotices(page) {
        currentPage = page;
        
        api.get('/notices', {
            page: page,
            limit: 20
        })
        .done(function(response) {
            if (response.status === 'success') {
                renderPinnedNotice(response.data.pinned_notice);
                renderNoticesTable(response.data.notices);
                renderPagination(response.data.pagination);
            }
        })
        .fail(handleError);
    }

    function renderPinnedNotice(notice) {
        let html = '';
        
        if (notice) {
            html = `
                <div class="card border-warning mb-3">
                    <div class="card-header bg-warning text-dark">
                        <i class="bi bi-pin-angle-fill"></i> 고정 공지
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">${escapeHtml(notice.title)}</h5>
                        <p class="card-text" style="white-space: pre-wrap;">${escapeHtml(notice.content)}</p>
                        <small class="text-muted">작성일: ${notice.created_at}</small>
                    </div>
                </div>
            `;
        }
        
        $('#pinnedNoticeArea').html(html);
    }

    function renderNoticesTable(notices) {
        let html = '';
        
        if (notices.length === 0) {
            html = '<tr><td colspan="5" class="text-center">등록된 공지사항이 없습니다.</td></tr>';
        } else {
            notices.forEach(function(notice) {
                const pinBtnClass = notice.is_pinned == 1 ? 'btn-warning' : 'btn-outline-secondary';
                const pinIcon = notice.is_pinned == 1 ? 'bi-pin-angle-fill' : 'bi-pin-angle';
                
                html += `
                    <tr>
                        <td>${notice.id}</td>
                        <td>
                            <a href="#" onclick="editNotice(${notice.id}); return false;">
                                ${escapeHtml(notice.title)}
                            </a>
                        </td>
                        <td>${notice.created_at.split(' ')[0]}</td>
                        <td>
                            <button class="btn btn-sm ${pinBtnClass}" onclick="togglePin(${notice.id})" title="고정">
                                <i class="bi ${pinIcon}"></i>
                            </button>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="editNotice(${notice.id})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteNotice(${notice.id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        }
        
        $('#noticesTable').html(html);
    }

    function renderPagination(pagination) {
        let html = '';
        
        for (let i = 1; i <= pagination.total_pages; i++) {
            const active = i === pagination.current_page ? 'active' : '';
            html += `<li class="page-item ${active}">
                        <a class="page-link" href="#" onclick="loadNotices(${i}); return false;">${i}</a>
                     </li>`;
        }
        
        $('#pagination').html(html);
    }

    function togglePin(id) {
        api.post('/notices/' + id + '/toggle-pin')
            .done(function(response) {
                if (response.status === 'success') {
                    loadNotices(currentPage);
                }
            })
            .fail(handleError);
    }

    function viewNotice(id) {
        location.href = `/admin/notices/${id}`;
    }

    function editNotice(id) {
        location.href = `/admin/notices/${id}/edit`;
    }

    function deleteNotice(id) {
        if (confirm('정말 삭제하시겠습니까?')) {
            api.delete('/notices/' + id)
                .done(function(response) {
                    alert('삭제되었습니다.');
                    loadNotices(currentPage);
                })
                .fail(handleError);
        }
    }

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
</script>
<?= $this->endSection() ?>

<?= $this->include('layouts/footer') ?>