
// Hàm tìm kiếm nhanh
document.getElementById('search-input').addEventListener('input', function () {
    const keyword = this.value.trim().toLowerCase(); // Lấy từ khóa và chuẩn hóa
    const rows = document.querySelectorAll('.question-table tbody tr'); // Lấy tất cả các hàng trong bảng
    let hasMatch = false; // Biến kiểm tra xem có câu hỏi nào khớp không

    rows.forEach(row => {
        const questionCell = row.querySelector('td:nth-child(2)'); // Lấy ô chứa câu hỏi (cột thứ 2)
        if (questionCell) {
            const questionText = questionCell.textContent.toLowerCase(); // Nội dung câu hỏi
            if (questionText.includes(keyword)) {
                row.style.display = ''; // Hiển thị hàng nếu khớp
                hasMatch = true; // Đánh dấu có ít nhất một câu hỏi khớp
            } else {
                row.style.display = 'none'; // Ẩn hàng nếu không khớp
            }
        }
    });

    // Hiển thị hoặc ẩn thông báo "Không có kết quả"
    const noResultsMessage = document.getElementById('no-results-message');
    if (!hasMatch) {
        if (!noResultsMessage) {
            // Tạo thông báo nếu chưa tồn tại
            const messageElement = document.createElement('div');
            messageElement.id = 'no-results-message';
            messageElement.style.textAlign = 'center';
            messageElement.style.color = '#d9c893';
            messageElement.style.marginTop = '20px';
            messageElement.style.fontWeight = 'bold';
            messageElement.textContent =
                'KHÔNG CÓ CÂU HỎI NÀO KHỚP VỚI TỪ KHÓA, ĐẠO HỮU HÃY XEM LẠI TỪ KHÓA HOẶC BỔ SUNG CÂU HỎI NẾU CHƯA CÓ';
            document.querySelector('.question-table').insertAdjacentElement('afterend', messageElement);
        }
    } else {
        // Xóa thông báo nếu đã tồn tại
        if (noResultsMessage) {
            noResultsMessage.remove();
        }
    }
});

// Toggle hiển thị form thêm câu hỏi
document.addEventListener('DOMContentLoaded', function () {
    const toggleButton = document.getElementById('toggle-add-question');
    const container = document.getElementById('add-question-container');
    if (toggleButton && container) {
        toggleButton.addEventListener('click', function () {
            if (container.style.display === 'none' || container.style.display === '') {
                container.style.display = 'block'; // Hiển thị form
                container.style.opacity = 1;
            } else {
                container.style.display = 'none'; // Ẩn form
                container.style.opacity = 0;
            }
        });
    } else {
        console.error('Không tìm thấy toggleButton hoặc container!');
    }
});

// Lấy tham chiếu đến các phần tử DOM
const searchInput = document.getElementById('search-input');
const clearButton = document.getElementById('clear-search');
const questionRows = document.querySelectorAll('.question-table tbody tr');
// Lưu trữ danh sách câu hỏi gốc dưới dạng HTML
const originalRowsHTML = Array.from(questionRows).map(row => row.outerHTML);

// Hàm lọc danh sách câu hỏi theo từ khóa
function filterQuestions(query) {
    query = query.toLowerCase().trim();
    // Ẩn/hiện hàng dựa trên từ khóa
    questionRows.forEach(row => {
        const questionText = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        if (questionText.includes(query)) {
            row.style.display = ''; // Hiển thị hàng nếu khớp
        } else {
            row.style.display = 'none'; // Ẩn hàng nếu không khớp
        }
    });
}

// Xử lý sự kiện khi nhập vào ô tìm kiếm
searchInput.addEventListener('input', function () {
    const query = searchInput.value;
    filterQuestions(query);
    // Hiển thị hoặc ẩn nút Clear
    if (query.trim() !== '') {
        clearButton.style.display = 'block';
    } else {
        clearButton.style.display = 'none';
    }
});

// Xử lý sự kiện khi nhấn nút Clear
clearButton.addEventListener('click', function () {
    searchInput.value = ''; // Xóa nội dung trong ô tìm kiếm
    searchInput.focus(); // Focus lại vào ô tìm kiếm
    // Khôi phục danh sách câu hỏi gốc
    questionRows.forEach(row => {
        row.style.display = ''; // Hiển thị tất cả các hàng
    });
    // Ẩn nút Clear
    clearButton.style.display = 'none';
});

// Ban đầu ẩn nút Clear
clearButton.style.display = 'none';

// Xử lý sự kiện khi nhấn nút xóa
document.querySelectorAll('.delete-button').forEach(button => {
    button.addEventListener('click', function (e) {
        e.preventDefault(); // Ngăn chặn hành động mặc định của form
        const deleteId = this.dataset.id; // Lấy ID câu hỏi từ thuộc tính data-id
        if (!confirm('Bạn có chắc chắn muốn xóa câu hỏi này?')) {
            return; // Nếu người dùng hủy, dừng hành động
        }
        // Gửi yêu cầu xóa qua AJAX
        fetch('noimon.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `delete_id=${deleteId}`,
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Hiển thị thông báo thành công
                alert(data.message);
                // Xóa hàng tương ứng khỏi bảng
                const row = document.querySelector(`tr[data-id="${deleteId}"]`);
                if (row) {
                    row.remove();
                    // Cập nhật số thứ tự
                    updateSerialNumbers();
                    // Cập nhật lại danh sách gốc
                    const index = originalRowsHTML.findIndex(html => html.includes(`data-id="${deleteId}"`));
                    if (index !== -1) {
                        originalRowsHTML.splice(index, 1); // Xóa hàng khỏi danh sách gốc
                    }
                }
            } else {
                // Hiển thị thông báo lỗi
                alert('Lỗi: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Đã xảy ra lỗi khi xóa câu hỏi.');
        });
    });
});



// Xử lý sự kiện khi chỉnh sửa nội dung
document.querySelectorAll('.editable').forEach(cell => {
    // Lưu giá trị ban đầu để hoàn tác nếu cần
    cell.dataset.originalValue = cell.textContent.trim();

    cell.addEventListener('blur', function () {
        const field = this.dataset.field; // Lấy trường cần cập nhật (question hoặc answer)
        const row = this.closest('tr'); // Tìm hàng chứa ô đang chỉnh sửa
        const questionId = row.dataset.id; // Lấy ID câu hỏi
        const newValue = this.textContent.trim(); // Lấy giá trị mới

        // Kiểm tra dữ liệu trước khi gửi
        if (!questionId || !field || newValue === '') {
            alert('Vui lòng nhập nội dung.');
            this.textContent = this.dataset.originalValue; // Hoàn tác nếu dữ liệu không hợp lệ
            return;
        }

        // Gửi yêu cầu cập nhật qua AJAX
        fetch('update_question.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id: questionId,
                field: field,
                value: newValue
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {

                this.dataset.originalValue = newValue;
            } else {
                alert('Lỗi: ' + data.message);
                // Hoàn tác thay đổi nếu cập nhật thất bại
                this.textContent = this.dataset.originalValue;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Đã xảy ra lỗi khi cập nhật.');
            // Hoàn tác thay đổi nếu có lỗi
            this.textContent = this.dataset.originalValue;
        });
    });
});

// Xử lý sự kiện khi nhấn nút "Duyệt"
document.querySelectorAll('.approve-button').forEach(button => {
    button.addEventListener('click', function () {
        const approveId = this.dataset.id; // Lấy ID câu hỏi từ thuộc tính data-id

        // Gửi yêu cầu duyệt qua AJAX
        fetch('noimon.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `approve_id=${approveId}`,
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Hiển thị thông báo thành công
                alert(data.message);

                // Cập nhật giao diện người dùng
                const row = document.querySelector(`tr[data-id="${approveId}"]`);
                if (row) {
                    // Ẩn nhãn "Chờ Duyệt"
                    const pendingLabel = row.querySelector('.pending-label');
                    if (pendingLabel) {
                        pendingLabel.remove();
                    }

                    // Ẩn nút "Duyệt"
                    const approveButton = row.querySelector('.approve-button');
                    if (approveButton) {
                        approveButton.remove();
                    }
                }
            } else {
                // Hiển thị thông báo lỗi
                alert('Lỗi: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Đã xảy ra lỗi khi duyệt câu hỏi.');
        });
    });
});

// Hàm cập nhật số thứ tự
function updateSerialNumbers() {
    const rows = document.querySelectorAll('.question-table tbody tr');
    rows.forEach((row, index) => {
        const serialNumberCell = row.querySelector('.serial-number');
        if (serialNumberCell) {
            serialNumberCell.textContent = index + 1; // Cập nhật số thứ tự
        }
    });
}

