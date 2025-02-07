document.addEventListener('DOMContentLoaded', function () {
    const questionList = document.getElementById('question-list');

    // Hàm tải danh sách câu hỏi
    function loadQuestions() {
        fetch('noimon.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    renderQuestions(data.data); // Hiển thị danh sách câu hỏi
                } else {
                    console.error('Lỗi khi tải danh sách câu hỏi:', data.message);
                }
            })
            .catch(error => console.error('Error:', error));
    }

    // Hàm hiển thị danh sách câu hỏi lên bảng
    function renderQuestions(questions) {
        questionList.innerHTML = ''; // Xóa dữ liệu cũ
        if (questions.length === 0) {
            questionList.innerHTML = '<tr><td colspan="5">Không có câu hỏi nào hiện tại.</td></tr>';
            return;
        }

        questions.forEach((question, index) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${index + 1}</td>
                <td 
                    contenteditable="${isAdmin() ? 'true' : 'false'}" 
                    class="editable" 
                    data-id="${question.id}" 
                    data-field="question"
                >${question.question}</td>
                <td 
                    contenteditable="${isAdmin() ? 'true' : 'false'}" 
                    class="editable" 
                    data-id="${question.id}" 
                    data-field="answer"
                >${question.answer || 'Chưa có câu trả lời'}</td>
                <td class="center-align">${question.contributor}</td>
                <td>
                    ${question.status === 'pending' ? '<span class="pending-label">(Chờ Duyệt)</span>' : ''}
                    ${isAdmin() ? `
                        <button class="approve-button" data-id="${question.id}">Duyệt</button>
                        <button class="delete-button" data-id="${question.id}">Xóa</button>
                    ` : ''}
                </td>
            `;
            questionList.appendChild(row);
        });

        // Thêm sự kiện cho các ô chỉnh sửa và nút hành động
        addEventListeners();
    }

    // Hàm kiểm tra quyền admin
    function isAdmin() {
        return sessionStorage.getItem('role') === 'admin';
    }

    // Hàm thêm sự kiện cho các ô chỉnh sửa và nút hành động
    function addEventListeners() {
        // Sử dụng event delegation để xử lý sự kiện
        document.addEventListener('click', function (event) {
            const target = event.target;

            // Xử lý nút Duyệt
            if (target.classList.contains('approve-button')) {
                const id = target.getAttribute('data-id');
                approveQuestion(id);
            }

            // Xử lý nút Xóa
            if (target.classList.contains('delete-button')) {
                const id = target.getAttribute('data-id');
                deleteQuestion(id);
            }
        });

        document.addEventListener('blur', function (event) {
            const target = event.target;

            // Xử lý chỉnh sửa nội dung
            if (target.classList.contains('editable')) {
                const id = target.getAttribute('data-id');
                const field = target.getAttribute('data-field');
                const value = target.textContent.trim();
                if (value !== target.dataset.originalValue) {
                    updateQuestion(id, field, value);
                }
            }
        }, true);

        // Lưu giá trị ban đầu để so sánh khi blur
        document.querySelectorAll('.editable').forEach(cell => {
            cell.dataset.originalValue = cell.textContent.trim();
        });
    }

    // Hàm duyệt câu hỏi
    function approveQuestion(id) {
        fetch('noimon.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `approve_id=${id}`
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('Câu hỏi đã được duyệt thành công!');
                loadQuestions(); // Tải lại danh sách câu hỏi
            } else {
                alert('Lỗi: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // Hàm xóa câu hỏi
    function deleteQuestion(id) {
        if (!confirm('Bạn có chắc chắn muốn xóa câu hỏi này?')) return;

        fetch('noimon.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `delete_id=${id}`
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('Câu hỏi đã bị xóa thành công!');
                loadQuestions(); // Tải lại danh sách câu hỏi
            } else {
                alert('Lỗi: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // Hàm cập nhật câu hỏi hoặc đáp án
    function updateQuestion(id, field, value) {
        fetch('noimon.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `update_field=true&id=${id}&field=${field}&value=${encodeURIComponent(value)}`
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('Cập nhật thành công!');
            } else {
                alert('Lỗi: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // Sự kiện tìm kiếm
    document.getElementById('search-input').addEventListener('input', function () {
        const keyword = this.value.trim();
        if (keyword.length > 0) {
            fetch(`search.php?keyword=${encodeURIComponent(keyword)}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    updateQuestionTable(data);
                })
                .catch(error => console.error('Lỗi tìm kiếm:', error));
        } else {
            fetchAllQuestions();
        }
    });

    // Hàm lấy toàn bộ câu hỏi
    function fetchAllQuestions() {
        fetch('fetch_questions.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                updateQuestionTable(data);
            })
            .catch(error => console.error('Lỗi tải câu hỏi:', error));
    }

    // Hàm cập nhật bảng câu hỏi
    function updateQuestionTable(questions) {
        const tableBody = document.querySelector('.question-table tbody');
        tableBody.innerHTML = ''; // Xóa nội dung cũ

        questions.forEach((question, index) => {
            const row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${question.question}</td>
                    <td>${question.answer || 'Chưa có câu trả lời.'}</td>
                    <td class="center-align">${question.contributor}</td>
                    ${isAdmin() ? `<td>${question.status === 'pending' ? '(Chờ Duyệt)' : ''}</td>` : ''}
                </tr>
            `;
            tableBody.insertAdjacentHTML('beforeend', row);
        });
    }

    // Tải danh sách câu hỏi khi trang được tải
    loadQuestions();
});