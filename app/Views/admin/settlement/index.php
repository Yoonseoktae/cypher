<?= $this->include('layouts/header') ?>
<?= $this->include('layouts/sidebar') ?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">정산 관리</h1>
    </div>

    <!-- 필터 -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">대리점 선택</label>
                    <select class="form-select" id="agencyFilter" onchange="loadSettlement()">
                        <!-- JavaScript로 채워짐 -->
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">년도</label>
                    <select class="form-select" id="yearFilter" onchange="loadSettlement()">
                        <!-- JavaScript로 채워짐 -->
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">월</label>
                    <select class="form-select" id="monthFilter" onchange="loadSettlement()">
                        <option value="01">1월</option>
                        <option value="02">2월</option>
                        <option value="03">3월</option>
                        <option value="04">4월</option>
                        <option value="05">5월</option>
                        <option value="06">6월</option>
                        <option value="07">7월</option>
                        <option value="08">8월</option>
                        <option value="09">9월</option>
                        <option value="10">10월</option>
                        <option value="11">11월</option>
                        <option value="12">12월</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- 정산 기간 안내 -->
    <div class="alert alert-info" id="settlementPeriod" style="display:none;">
        <i class="bi bi-calendar-range"></i> 
        <strong>정산 기간:</strong> <span id="periodText"></span>
        <br>
        <i class="bi bi-clock"></i> 
        <strong>스냅샷 생성일:</strong> <span id="snapshotDate"></span>
    </div>

    <!-- 통계 카드 -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body p-3">
                    <div class="text-xs fw-bold text-primary text-uppercase mb-1">총 인원</div>
                    <div class="h5 mb-0 fw-bold" id="totalUsers">-</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body p-3">
                    <div class="text-xs fw-bold text-success text-uppercase mb-1">신규 회원</div>
                    <div class="h5 mb-0 fw-bold" id="newUsers">-</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body p-3">
                    <div class="text-xs fw-bold text-info text-uppercase mb-1">정산 인원</div>
                    <div class="h5 mb-0 fw-bold" id="settlementUsers">-</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body p-3">
                    <div class="text-xs fw-bold text-warning text-uppercase mb-1">정산 금액</div>
                    <div class="h5 mb-0 fw-bold text-danger" id="settlementAmount">-</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 회원 리스트 -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">정산 대상 회원 리스트</h6>
            <button class="btn btn-sm btn-success" onclick="downloadExcel()" id="downloadBtn" style="display:none;">
                <i class="bi bi-download"></i> 엑셀 다운로드
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="px-3">이름</th>
                            <th>전화번호</th>
                            <th>등록일</th>
                            <th>만료일</th>
                            <th class="px-3">상태</th>
                        </tr>
                    </thead>
                    <tbody id="usersTable">
                        <tr>
                            <td colspan="5" class="text-center py-4">대리점을 선택하세요</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?= $this->section('scripts') ?>
<script>
let currentUsers = [];

$(document).ready(function() {
    loadAgencyList();
    initYearFilter();
    
    $('#monthFilter').val(('0' + new Date().getMonth()).slice(-2) || '12');
});

function initYearFilter() {
    const currentYear = new Date().getFullYear();
    for (let i = 0; i < 5; i++) {
        const year = currentYear - i;
        $('#yearFilter').append(`<option value="${year}">${year}년</option>`);
    }
}

function loadAgencyList() {
    api.get('/agency/list')
        .done(function(response) {
            if (response.status === 'success') {
                const agencies = response.data;
                
                $('#agencyFilter').append('<option value="">대리점 선택</option>');
                agencies.forEach(function(agency) {
                    $('#agencyFilter').append(
                        `<option value="${agency.id}">${agency.name}</option>`
                    );
                });
                
                if (agencies.length > 0) {
                    $('#agencyFilter').val(agencies[0].id);
                    loadSettlement();
                }
            }
        });
}

function loadSettlement() {
    const agencyId = $('#agencyFilter').val();
    const year = $('#yearFilter').val();
    const month = $('#monthFilter').val();
    
    if (!agencyId) {
        $('#usersTable').html('<tr><td colspan="5" class="text-center py-4">대리점을 선택하세요</td></tr>');
        return;
    }
    
    // 정산 통계 조회
    api.get('/settlement/snapshot', {
        agency_id: agencyId,
        year: year,
        month: month
    })
    .done(function(response) {
        if (response.status === 'success') {
            const data = response.data;
            
            $('#totalUsers').text(data.total_users || 0);
            $('#newUsers').text(data.new_users || 0);
            $('#settlementUsers').text(data.settlement_users || 0);
            $('#settlementAmount').text((data.settlement_amount || 0).toLocaleString() + '원');
            
            // settlement_period 존재 여부 확인
            if (data.settlement_period) {
                const period = data.settlement_period;
                $('#periodText').text(`${period.start} ~ ${period.end}`);
                $('#settlementPeriod').show();
            } else {
                $('#settlementPeriod').hide();
            }
        }
    });
    
    // 정산 인원 리스트 조회
    api.get('/settlement/users', {
        agency_id: agencyId,
        year: year,
        month: month
    })
    .done(function(response) {
        if (response.status === 'success') {
            currentUsers = response.data;
            renderUsersTable(currentUsers);
            
            if (currentUsers.length > 0) {
                $('#downloadBtn').show();
            } else {
                $('#downloadBtn').hide();
            }
        }
    })
    .fail(handleError);
}

function renderUsersTable(users) {
    let html = '';
    
    if (users.length === 0) {
        html = '<tr><td colspan="5" class="text-center py-4">해당 정산 기간에 등록된 회원이 없습니다.</td></tr>';
    } else {
        users.forEach(function(user) {
            html += `
                <tr>
                    <td class="px-3">${escapeHtml(user.name)}</td>
                    <td>${escapeHtml(user.phone_number)}</td>
                    <td>${user.registration_date}</td>
                    <td>${user.expiry_date}</td>
                    <td class="px-3">${getStatusBadge(user.status)}</td>
                </tr>
            `;
        });
    }
    
    $('#usersTable').html(html);
}

function getStatusBadge(status) {
    const badges = {
        1: '<span class="badge bg-success">신규</span>',
        2: '<span class="badge bg-info">연장</span>',
        3: '<span class="badge bg-danger">정지</span>'
    };
    return badges[status] || '<span class="badge bg-secondary">알수없음</span>';
}

function downloadExcel() {
    const agencyName = $('#agencyFilter option:selected').text();
    const year = $('#yearFilter').val();
    const month = $('#monthFilter').val();
    
    let excelContent = `
        <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
        <head>
            <meta charset="utf-8">
            <xml>
                <x:ExcelWorkbook>
                    <x:ExcelWorksheets>
                        <x:ExcelWorksheet>
                            <x:Name>정산내역</x:Name>
                            <x:WorksheetOptions>
                                <x:DisplayGridlines/>
                            </x:WorksheetOptions>
                        </x:ExcelWorksheet>
                    </x:ExcelWorksheets>
                </x:ExcelWorkbook>
            </xml>
        </head>
        <body>
            <table border="1">
                <thead>
                    <tr>
                        <th>이름</th>
                        <th>전화번호</th>
                        <th>등록일</th>
                        <th>만료일</th>
                        <th>상태</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    currentUsers.forEach(function(user) {
        const statusText = user.status == 1 ? '신규' : user.status == 2 ? '연장' : '정지';
        excelContent += `
            <tr>
                <td>${user.name}</td>
                <td>${user.phone_number}</td>
                <td>${user.registration_date}</td>
                <td>${user.expiry_date}</td>
                <td>${statusText}</td>
            </tr>
        `;
    });
    
    excelContent += `
                </tbody>
            </table>
        </body>
        </html>
    `;
    
    const blob = new Blob(['\ufeff', excelContent], {
        type: 'application/vnd.ms-excel;charset=utf-8;'
    });
    
    const link = document.createElement('a');
    const filename = `${agencyName}_${year}년${month}월_정산내역.xls`;
    
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(link.href);
}
</script>
<?= $this->endSection() ?>

<?= $this->include('layouts/footer') ?>