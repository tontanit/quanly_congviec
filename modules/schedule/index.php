<?php
require_once '../../config/database.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

include '../../includes/header.php';
?>

<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>

<style>
    :root {
        --fc-border-color: #eee;
        --fc-daygrid-event-dot-width: 8px;
    }

    #calendar {
        background: #fff;
        padding: 20px;
        border-radius: 15px;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    /* Hiển thị tiêu đề sự kiện đầy đủ, không bị cắt bớt */
    .fc-event-title {
        font-weight: 600 !important;
        white-space: normal !important;
        /* Cho phép xuống dòng */
        font-size: 0.85rem !important;
    }

    .fc-event {
        cursor: pointer;
        padding: 2px 4px;
        border: none !important;
        margin-bottom: 2px;
    }

    .fc-toolbar-title {
        font-size: 1.25rem !important;
        font-weight: bold;
        color: #0d6efd;
        text-transform: uppercase;
    }

    /* Tùy chỉnh màu sắc các nút trên toolbar */
    .fc-button-primary {
        background-color: #fff !important;
        border-color: #dee2e6 !important;
        color: #444 !important;
        text-transform: capitalize !important;
    }

    .fc-button-active {
        background-color: #0d6efd !important;
        border-color: #0d6efd !important;
        color: #fff !important;
    }
</style>

<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h3 class="fw-bold mb-0 text-uppercase">
                <i class="fa fa-calendar-alt text-primary me-2"></i>Lịch công tác Lãnh đạo
            </h3>
        </div>
        <div class="col-md-6 text-end">
            <?php if ($_SESSION['role'] == 'admin'): ?>
                <a href="trash.php" class="btn btn-outline-danger shadow-sm fw-bold me-2">
                    <i class="fa fa-trash-alt me-2"></i>THÙNG RÁC
                </a>
                <button class="btn btn-primary shadow-sm fw-bold" id="btnAddNew">
                    <i class="fa fa-plus me-2"></i>THÊM LỊCH MỚI
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div id='calendar'></div>
        </div>
    </div>
</div>

<div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="modalTitle">SOẠN THẢO LỊCH CÔNG TÁC</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="eventForm">
                <input type="hidden" name="event_id" id="event_id">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold small">Nội dung công việc</label>
                            <input type="text" name="tieu_de" class="form-control form-control-lg" placeholder="Nhập tiêu đề cuộc họp, công tác..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Bắt đầu</label>
                            <input type="datetime-local" name="bat_dau" id="bat_dau" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Kết thúc</label>
                            <input type="datetime-local" name="ket_thuc" id="ket_thuc" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Địa điểm</label>
                            <input type="text" name="dia_diem" class="form-control" placeholder="Phòng họp, địa phương..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Loại hình</label>
                            <select name="loai_lich" class="form-select">
                                <option value="Họp">Họp nội bộ</option>
                                <option value="Công tác">Đi công tác</option>
                                <option value="Tiếp khách">Tiếp khách</option>
                                <option value="Khác">Khác</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold small">Ghi chú (Thành phần, nội dung chi tiết)</label>
                            <textarea name="noi_dung" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light d-flex justify-content-between">
                    <div>
                        <button type="button" id="btnDelete" class="btn btn-danger fw-bold" style="display:none;">
                            <i class="fa fa-trash-alt me-2"></i>XÓA LỊCH
                        </button>
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">ĐÓNG</button>
                        <button type="submit" class="btn btn-primary fw-bold px-4">LƯU THÔNG TIN</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendar');

        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'vi',
            timeZone: 'Asia/Ho_Chi_Minh',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },
            buttonText: {
                today: 'Hôm nay',
                month: 'Tháng',
                week: 'Tuần',
                list: 'Danh sách'
            },
            events: 'get_events.php', // Tự động gọi file này để lấy JSON

            // Xử lý hiển thị nội dung mượt mà
            eventDidMount: function(info) {
                // Thêm tooltip cơ bản của trình duyệt khi rê chuột vào
                info.el.title = info.event.title + " tại " + info.event.extendedProps.location;
            },

            eventClick: function(info) {
                <?php if ($_SESSION['role'] == 'admin'): ?>
                    openModalForEdit(info.event);
                <?php else: ?>
                    // Xem chi tiết (Cho nhân viên)
                    Swal.fire({
                        title: `<span class="text-primary">${info.event.title}</span>`,
                        html: `<div class="text-start border-top pt-2">
                               <p><b>📍 Địa điểm:</b> ${info.event.extendedProps.location}</p>
                               <p><b>⏰ Thời gian:</b> ${info.event.start.toLocaleString('vi-VN')}</p>
                               <p><b>📝 Chi tiết:</b> ${info.event.extendedProps.description || 'Không có'}</p>
                               </div>`,
                        icon: 'info'
                    });
                <?php endif; ?>
            }
        });

        calendar.render();

        // 1. Mở modal thêm mới
        $('#btnAddNew').on('click', function() {
            $('#eventForm')[0].reset();
            $('#event_id').val('');
            $('#modalTitle').text('THÊM LỊCH CÔNG TÁC MỚI');
            $('#btnDelete').hide();
            $('#eventModal').modal('show');
        });

        // 2. Mở modal chỉnh sửa
        function openModalForEdit(event) {
            $('#event_id').val(event.id);
            // Lấy lại tiêu đề gốc (loại bỏ phần [Tên] nếu có)
            let title = event.title;
            if (title.includes('] ')) title = title.split('] ').pop();

            $('input[name="tieu_de"]').val(title);
            $('#bat_dau').val(formatDateTime(event.start));
            $('#ket_thuc').val(formatDateTime(event.end));
            $('input[name="dia_diem"]').val(event.extendedProps.location);
            $('select[name="loai_lich"]').val(event.extendedProps.type);
            $('textarea[name="noi_dung"]').val(event.extendedProps.description);

            $('#modalTitle').text('CHỈNH SỬA LỊCH CÔNG TÁC');
            $('#btnDelete').show();
            $('#eventModal').modal('show');
        }

        // 3. Xử lý AJAX Lưu
        $('#eventForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: 'save_event.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    const data = JSON.parse(res);
                    if (data.status === 'success') {
                        $('#eventModal').modal('hide');
                        calendar.refetchEvents(); // Tải lại các chấm sự kiện
                        Swal.fire('Thành công', data.message, 'success');
                    } else {
                        Swal.fire('Lỗi', data.message, 'error');
                    }
                }
            });
        });

        // 4. Xử lý AJAX Xóa tạm thời
        $('#btnDelete').on('click', function() {
            const id = $('#event_id').val();
            Swal.fire({
                title: 'Xóa lịch trình?',
                text: "Lịch sẽ được chuyển vào thùng rác!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Đồng ý xóa'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('delete_event.php', {
                        id: id
                    }, function(res) {
                        const data = JSON.parse(res);
                        if (data.status === 'success') {
                            $('#eventModal').modal('hide');
                            calendar.refetchEvents();
                            Swal.fire('Đã xóa', data.message, 'success');
                        }
                    });
                }
            });
        });

        // Helper: Định dạng thời gian cho input datetime-local
        function formatDateTime(date) {
            if (!date) return '';
            const d = new Date(date);
            d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
            return d.toISOString().slice(0, 16);
        }
    });
</script>

<?php include '../../includes/footer.php'; ?>