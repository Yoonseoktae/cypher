<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>로그인</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            align-items: center;
            padding-top: 40px;
            padding-bottom: 40px;
            background-color: #f5f5f5;
            height: 100vh;
        }
        .form-signin {
            width: 100%;
            max-width: 330px;
            padding: 15px;
            margin: auto;
        }
        .form-signin .form-floating:focus-within {
            z-index: 2;
        }
    </style>
</head>
<body>
    <main class="form-signin">
        <form id="loginForm">
            <h1 class="h3 mb-3 fw-normal text-center">Holic</h1>

            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="username" name="username" placeholder="아이디" required>
                <label for="username">아이디</label>
            </div>
            
            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="password" name="password" placeholder="비밀번호" required>
                <label for="password">비밀번호</label>
            </div>

            <button class="w-100 btn btn-lg btn-primary" type="submit">로그인</button>
            
            <p class="mt-5 mb-3 text-muted text-center">&copy; 2025</p>
        </form>
    </main>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        const API_BASE_URL = '<?= base_url('api/v1') ?>';
        
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();
            
            const username = $('#username').val();
            const password = $('#password').val();
            
            $.ajax({
                url: API_BASE_URL + '/admin/login',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    username: username,
                    password: password
                }),
                success: function(response) {
                    if (response.status === 'success') {
                        // alert('로그인 성공!');
                        location.href = '/admin/dashboard';
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || '로그인 실패';
                    alert(message);
                }
            });
        });
    </script>
</body>
</html>