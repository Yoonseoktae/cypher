        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        const API_BASE_URL = '<?= base_url('api/v1') ?>';
        
        // 로그아웃 함수
        function logout() {
            if (confirm('로그아웃 하시겠습니까?')) {
                $.ajax({
                    url: API_BASE_URL + '/admin/logout',
                    method: 'POST',
                    success: function(response) {
                        alert('로그아웃 되었습니다.');
                        location.href = '/admin/login';
                    },
                    error: function() {
                        alert('로그아웃 실패');
                    }
                });
            }
        }

        // 공통 에러 처리
        function handleError(xhr) {
            if (xhr.status === 401) {
                alert('로그인이 필요합니다.');
                location.href = '/admin/login';
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                alert(xhr.responseJSON.message);
            } else {
                alert('오류가 발생했습니다.');
            }
        }
    </script>
    
    <?= $this->renderSection('scripts') ?>
</body>
</html>