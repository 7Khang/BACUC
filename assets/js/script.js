document.addEventListener('DOMContentLoaded', () => {
    // Hàm lọc nhanh câu hỏi theo từ khóa
    function filterQuestions() {
        const searchInput = document.getElementById('search-input');
        if (!searchInput) return;

        const searchTerm = searchInput.value.toLowerCase();
        const rows = document.querySelectorAll('#question-table tbody tr');

        rows.forEach(row => {
            const questionText = row.getAttribute('data-question') || '';
            const answerText = row.querySelector('td:nth-child(3)')?.innerText.toLowerCase() || '';

            if (questionText.includes(searchTerm) || answerText.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Gắn sự kiện cho ô tìm kiếm
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('input', filterQuestions);
    }

    // Toggle form thêm câu hỏi
    const toggleButton = document.getElementById('toggle-add-question');
    const addQuestionContainer = document.getElementById('add-question-container');
    if (toggleButton && addQuestionContainer) {
        toggleButton.addEventListener('click', () => {
            if (addQuestionContainer.classList.contains('visible')) {
                addQuestionContainer.classList.remove('visible');
                setTimeout(() => {
                    addQuestionContainer.style.display = 'none';
                }, 500);
            } else {
                addQuestionContainer.style.display = 'block';
                setTimeout(() => {
                    addQuestionContainer.classList.add('visible');
                }, 10);
            }
        });
    }

    // Xử lý form thêm câu hỏi qua AJAX
    const form = document.getElementById('add-question-form');
    const tableBody = document.querySelector('#question-table tbody');
    if (form && tableBody) {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const formData = new FormData(form);

            try {
                const response = await fetch('/qa-system/noimon.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                const text = await response.text();
                let data;

                try {
                    data = JSON.parse(text);
                } catch (error) {
                    console.error('Phản hồi không phải là JSON:', text);
                    alert('Có lỗi xảy ra. Vui lòng thử lại.');
                    return;
                }

                if (data.success) {
                    const newRow = document.createElement('tr');
                    newRow.classList.add('question-row');
                    newRow.setAttribute('data-id', data.data.id);
                    newRow.innerHTML = `
                        <td class="serial-number">${tableBody.children.length + 1}</td>
                        <td>${data.data.question}</td>
                        <td>${data.data.answer || 'Chưa có câu trả lời.'}</td>
                        <td class="center-align">${data.data.contributor}</td>
                        ${data.data.status === 'pending' ? `
                            <td class="actions">
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn duyệt câu hỏi này?');">
                                    <input type="hidden" name="approve_id" value="${data.data.id}">
                                    <button type="submit" class="approve-button">Duyệt</button>
                                </form>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa câu hỏi này?');">
                                    <input type="hidden" name="delete_id" value="${data.data.id}">
                                    <button type="submit" class="delete-button">Xóa</button>
                                </form>
                            </td>
                        ` : ''}
                    `;
                    tableBody.appendChild(newRow);
                    alert(data.message);
                    form.reset();
                } else {
                    alert(data.message);
                }
            } catch (error) {
                console.error('Lỗi:', error);
                alert('Đã xảy ra lỗi khi thêm câu hỏi.');
            }
        });
    }
    fetch('/qa-system/noimon.php', { // Hoặc '/qa-system/noimon.php'
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            console.error('Server responded with status:', response.status);
            throw new Error('Network response was not ok');
        }
        return response.text(); // Nhận phản hồi dưới dạng text
    })
    .then(text => {
        console.log('Raw response:', text); // Kiểm tra phản hồi thô
        try {
            const data = JSON.parse(text); // Thử parse JSON
            console.log('Parsed data:', data);
        } catch (error) {
            console.error('Phản hồi không phải là JSON:', text);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
});