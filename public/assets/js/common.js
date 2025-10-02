// public/assets/js/common.js

const API_BASE_URL = document.querySelector('meta[name="api-base-url"]')?.content || '/api/v1';

// 로딩 오버레이 HTML
const loadingOverlay = `
    <div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
        <div class="spinner-border text-light" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
`;

// 페이지 로드 시 로딩 오버레이 추가
$(document).ready(function() {
    $('body').append(loadingOverlay);
});

// 로딩 표시
function showLoading() {
    $('#loadingOverlay').css('display', 'flex');
}

// 로딩 숨김
function hideLoading() {
    $('#loadingOverlay').fadeOut(200);
}

// 공통 에러 처리
function handleError(xhr) {
    hideLoading();
    
    if (xhr.status === 401) {
        alert('로그인이 필요합니다.');
        location.href = '/admin/login';
    } else if (xhr.status === 403) {
        alert('권한이 없습니다.');
    } else if (xhr.responseJSON && xhr.responseJSON.message) {
        alert(xhr.responseJSON.message);
    } else {
        alert('오류가 발생했습니다.');
    }
}

// 공통 API 호출 함수
const api = {
    // GET 요청
    get: function(url, params = {}) {
        showLoading();
        
        return $.ajax({
            url: API_BASE_URL + url,
            method: 'GET',
            data: params
        }).always(function() {
            hideLoading();
        });
    },
    
    // POST 요청
    post: function(url, data = {}) {
        showLoading();
        
        return $.ajax({
            url: API_BASE_URL + url,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data)
        }).always(function() {
            hideLoading();
        });
    },
    
    // PUT 요청
    put: function(url, data = {}) {
        showLoading();
        
        return $.ajax({
            url: API_BASE_URL + url,
            method: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify(data)
        }).always(function() {
            hideLoading();
        });
    },
    
    // DELETE 요청
    delete: function(url) {
        showLoading();
        
        return $.ajax({
            url: API_BASE_URL + url,
            method: 'DELETE'
        }).always(function() {
            hideLoading();
        });
    }
};

// 로그아웃 함수
function logout() {
    if (confirm('로그아웃 하시겠습니까?')) {
        api.post('/admin/logout')
            .done(function(response) {
                alert('로그아웃 되었습니다.');
                location.href = '/admin/login';
            })
            .fail(handleError);
    }
}

// HTML 이스케이프 함수
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}