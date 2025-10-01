<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>회원가입 - Holic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            align-items: center;
            padding-top: 40px;
            padding-bottom: 40px;
            background-color: #f5f5f5;
            min-height: 100vh;
        }
        .form-register {
            width: 100%;
            max-width: 450px;
            padding: 15px;
            margin: auto;
        }
        .card {
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body>
    <main class="form-register">
        <div class="card">
            <div class="card-body p-4">
                <h1 class="h3 mb-4 text-center">관리자 회원가입</h1>

                <form id="registerForm">
                    <div class="mb-3">
                        <label for="username" class="form-label">아이디 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="username" name="username" required>
                        <div class="form-text">영문, 숫자 조합 4-20자</div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">비밀번호 <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="form-text">8자 이상</div>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirm" class="form-label">비밀번호 확인 <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">이름 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="agency_id" class="form-label">대리점 ID</label>
                        <input type="number" class="form-control" id="agency_id" name="agency_id">
                        <div class="form-text">소속 대리점이 있는 경우 입력</div>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">권한</label>
                        <select class="form-select" id="role" name="role">
                            <option value="10">일반 관리자</option>
                            <option value="20">대리점 관리자</option>
                            <option value="99">슈퍼 관리자</option>
                        </select>
                    </div>

                    <button class="w-100 btn btn-lg btn-primary" type="submit">회원가입</button>
                    
                    <div class="text-center mt-3">
                        <a href="/admin/login" class="text-decoration-none">로그인 페이지로</a>
                    </div>
                </form>
            </div>
        </div>
        
        <p class="mt-4 mb-3 text-muted text-center">&copy; 2025</p>
    </main>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        const API_BASE_URL = '<?= base_url('api/v1') ?>';
        
        $('#registerForm').on('submit', function(e) {
            e.preventDefault();
            
            const username = $('#username').val();
            const password = $('#password').val();
            const password_confirm = $('#password_confirm').val();
            const name = $('#name').val();
            const agency_id = $('#agency_id').val();
            const role = $('#role').val();
            
            // 클라이언트 측 유효성 검사
            if (username.length < 4 || username.length > 20) {
                alert('아이디는 4-20자로 입력하세요.');
                return;
            }
            
            if (password.length < 8) {
                alert('비밀번호는 8자 이상 입력하세요.');
                return;
            }
            
            if (password !== password_confirm) {
                alert('비밀번호가 일치하지 않습니다.');
                return;
            }
            
            // API 호출
            $.ajax({
                url: API_BASE_URL + '/admin/register',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    username: username,
                    password: password,
                    password_confirm: password_confirm,
                    name: name,
                    agency_id: agency_id || null,
                    role: role
                }),
                success: function(response) {
                    if (response.status === 'success') {
                        alert('회원가입이 완료되었습니다!');
                        location.href = '/admin/login';
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || '회원가입 실패';
                    alert(message);
                }
            });
        });
    </script>
</body>
</html>