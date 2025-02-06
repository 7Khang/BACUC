document.addEventListener('DOMContentLoaded', () => {
    // Hàm lọc nhanh câu hỏi theo từ khóa
    function filterQuestions() {
        const searchInput = document.getElementById('search-input');
        if (!searchInput) return; // Kiểm tra xem ô tìm kiếm có tồn tại không

        const searchValue = searchInput.value.toLowerCase();
        console.log("Từ khóa tìm kiếm:", searchValue);

        const rows = document.querySelectorAll('#question-table tbody tr');
        console.log("Số hàng trong bảng:", rows.length);

        rows.forEach(row => {
            const questionText = row.getAttribute('data-question');
            if (questionText && questionText.includes(searchValue)) {
                row.style.display = ''; // Hiển thị hàng nếu phù hợp
            } else {
                row.style.display = 'none'; // Ẩn hàng nếu không phù hợp
            }
        });
    }

    // Gắn sự kiện cho ô tìm kiếm
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('input', filterQuestions);
    }

    // Xử lý hamburger menu
    const hamburger = document.querySelector('.hamburger');
    const menu = document.querySelector('.menu ul');

    if (hamburger && menu) {
        hamburger.addEventListener('click', () => {
            menu.classList.toggle('active');
        });
    }

    // Xác nhận trước khi xóa
    const deleteLinks = document.querySelectorAll('a[href*="delete_question.php"]');
    deleteLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            if (!confirm('Bạn có chắc chắn muốn xóa câu hỏi này không?')) {
                e.preventDefault(); // Ngăn chặn hành động xóa nếu người dùng hủy
            }
        });
    });
});
document.addEventListener('DOMContentLoaded', () => {
    const toggleButton = document.getElementById('toggle-add-question');
    const addQuestionContainer = document.getElementById('add-question-container');

    toggleButton.addEventListener('click', () => {
        if (addQuestionContainer.style.display === 'none' || addQuestionContainer.style.display === '') {
            addQuestionContainer.style.display = 'block';
        } else {
            addQuestionContainer.style.display = 'none';
        }
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const toggleButton = document.getElementById('toggle-add-question');
    const addQuestionContainer = document.getElementById('add-question-container');

    toggleButton.addEventListener('click', () => {
        if (addQuestionContainer.classList.contains('visible')) {
            addQuestionContainer.classList.remove('visible');
            setTimeout(() => {
                addQuestionContainer.style.display = 'none';
            }, 500); // Thời gian chờ để hoàn thành hiệu ứng mờ dần
        } else {
            addQuestionContainer.style.display = 'block';
            setTimeout(() => {
                addQuestionContainer.classList.add('visible');
            }, 10); // Đợi DOM cập nhật trước khi thêm class
        }
    });

    // Kiểm tra dữ liệu trước khi gửi
    document.querySelector('.add-question-form').addEventListener('submit', function (e) {
        const question = document.getElementById('question').value.trim();
        const answer = document.getElementById('answer').value.trim();
    
        if (!question || !answer) {
            e.preventDefault(); // Ngăn chặn gửi form nếu dữ liệu trống
            alert("Vui lòng nhập đầy đủ câu hỏi và đáp án.");
        }
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const editableCells = document.querySelectorAll(".editable");

    editableCells.forEach(cell => {
        cell.addEventListener("blur", function () {
            const questionId = cell.closest(".question-row").getAttribute("data-id");
            const field = cell.getAttribute("data-field");
            const newValue = cell.textContent.trim();

            if (newValue) {
                fetch("update_question.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        id: questionId,
                        field: field,
                        value: newValue
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Cập nhật thành công!");
                    } else {
                        alert("Lỗi khi cập nhật: " + data.error);
                    }
                })
                .catch(error => {
                    console.error("Lỗi:", error);
                    alert("Lỗi khi cập nhật.");
                });
            } else {
                alert("Vui lòng nhập nội dung.");
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const editableCells = document.querySelectorAll('.editable');

    editableCells.forEach(cell => {
        cell.addEventListener('blur', async () => {
            console.log('Nội dung ô:', cell.innerText); // Debug: Kiểm tra nội dung ô
        });
    });
});
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('search-input');
    const rows = document.querySelectorAll('#question-table tbody tr');

    searchInput.addEventListener('input', () => {
        const searchTerm = searchInput.value.toLowerCase();

        rows.forEach(row => {
            const questionCell = row.querySelector('td:first-child');
            const answerCell = row.querySelector('td:nth-child(2)');

            if (questionCell && answerCell) {
                const questionText = questionCell.innerText.toLowerCase();
                const answerText = answerCell.innerText.toLowerCase();

                if (questionText.includes(searchTerm) || answerText.includes(searchTerm)) {
                    row.style.display = ''; // Hiển thị dòng
                } else {
                    row.style.display = 'none'; // Ẩn dòng
                }
            }
        });
    });
});




