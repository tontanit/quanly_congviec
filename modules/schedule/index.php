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
    /* 1. Tối ưu hiển thị sự kiện trên ô lịch */
    .fc-event {
        cursor: pointer;
        border: none !important;
        margin-bottom: 2px !important;
        padding: 3px 5px !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    /* Ép nội dung tự xuống dòng, không bị ẩn bớt */
    .fc-daygrid-event {
        white-space: normal !important;
        display: block !important;
    }

    /* Tùy chỉnh vùng chứa nội dung bên trong sự kiện */
    .event-leader-tag {
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        margin-bottom: 2px;
        display: block;
    }

    .event-job-title {
        font-size: 0.85rem;
        font-weight: 500;
        line-height: 1.3;
        display: block;
    }

    /* 2. Cấu hình chung cho lịch */
    #calendar {
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        min-height: 700px;
    }

    .fc-toolbar-title {
        font-size: 1.4rem !important;
        color: #0d6efd;
        font-weight: 800 !important;
    }
</style>

<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h3 class="fw-bold mb-0"><i class="fa fa-calendar-check text-primary me-2"></i>LỊCH CÔNG TÁC</h3>
        </div>
        <div class="col-md-6 text-end">
            <?php if ($_SESSION['role'] == 'admin'): ?>
                <a href="trash.php" class="btn btn-outline-secondary shadow-sm fw-bold me-2">
                    <i class="fa fa-trash-alt me-1"></i> THÙNG RÁC
                </a>
                <button class="btn btn-primary shadow-sm fw-bold" id="btnAddNew">
                    <i class="fa fa-plus-circle me-1"></i> THÊM LỊCH MỚI
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div id='calendar'></div>
        </div>
    </div>
</div>

<div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="modalTitle">THÔNG TIN LỊCH CÔNG TÁC</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="eventForm">
                <input type="hidden" name="event_id" id="event_id">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Nội dung công việc</label>
                            <input type="text" name="tieu_de" class="form-control form-control-lg" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Bắt đầu</label>
                            <input type="datetime-local" name="bat_dau" id="bat_dau" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Kết thúc</label>
                            <input type="datetime-local" name="ket_thuc" id="ket_thuc" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Địa điểm</label>
                            <input type="text" name="dia_diem" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Loại hình</label>
                            <select name="loai_lich" class="form-select">
                                <option value="Họp">Họp</option>
                                <option value="Công tác">Công tác</option>
                                <option value="Tiếp khách">Tiếp khách</option>
                                <option value="Khác">Khác</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Chi tiết / Thành phần tham dự</label>
                            <textarea name="noi_dung" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light d-flex justify-content-between">
                    <div>
                        <button type="button" id="btnDelete" class="btn btn-outline-danger fw-bold" style="display:none;">
                            <i class="fa fa-trash-alt me-1"></i> XÓA LỊCH
                        </button>
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ĐÓNG</button>
                        <button type="submit" class="btn btn-primary fw-bold px-4">LƯU THÔNG TIN</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function() {

        const USER_ROLE = "<?= $_SESSION['role'] ?>";
        const calendarEl = document.getElementById('calendar');
        const modal = new bootstrap.Modal(document.getElementById('eventModal'));

        /* =========================
           1. KHỞI TẠO CALENDAR
        ========================== */

        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'vi',
            timeZone: 'Asia/Ho_Chi_Minh',
            height: 'auto',
            navLinks: true,
            selectable: USER_ROLE === 'admin',

            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },

            events: {
                url: 'get_events.php',
                failure: function() {
                    Swal.fire('Lỗi', 'Không thể tải dữ liệu lịch.', 'error');
                }
            },

            /* =========================
               2. HIỂN THỊ SỰ KIỆN
            ========================== */
            eventContent: function(arg) {
                const leader = arg.event.extendedProps.leader ?? 'N/A';
                const fullTitle = arg.event.title;
                const cleanTitle = fullTitle.includes('] ') ?
                    fullTitle.split('] ').pop() :
                    fullTitle;

                const container = document.createElement('div');
                container.innerHTML = `
                <span class="event-leader-tag">
                    <i class="fa fa-user-circle me-1"></i>${leader}
                </span>
                <span class="event-job-title">${cleanTitle}</span>
            `;

                return {
                    domNodes: [container]
                };
            },

            /* =========================
               3. CLICK EVENT
            ========================== */
            eventClick: function(info) {
                if (USER_ROLE === 'admin') {
                    openEditModal(info.event);
                } else {
                    viewOnlyPopup(info.event);
                }
            }
        });

        calendar.render();


        /* =========================
           4. MODAL FUNCTIONS
        ========================== */

        function openEditModal(event) {

            $('#event_id').val(event.id);

            const title = event.title.includes('] ') ?
                event.title.split('] ').pop() :
                event.title;

            $('input[name="tieu_de"]').val(title);
            $('#bat_dau').val(formatDateTime(event.start));
            $('#ket_thuc').val(formatDateTime(event.end ?? event.start));
            $('input[name="dia_diem"]').val(event.extendedProps.location ?? '');
            $('select[name="loai_lich"]').val(event.extendedProps.type ?? '');
            $('textarea[name="noi_dung"]').val(event.extendedProps.description ?? '');

            $('#modalTitle').text('CHỈNH SỬA LỊCH CÔNG TÁC');
            $('#btnDelete').show();

            modal.show();
        }

        function viewOnlyPopup(event) {
            Swal.fire({
                title: `<span class="text-primary">${event.title}</span>`,
                html: `
                <div class="text-start border-top pt-2">
                    <p><b>📍 Địa điểm:</b> ${event.extendedProps.location ?? ''}</p>
                    <p><b>⏰ Thời gian:</b> ${event.start.toLocaleString('vi-VN')}</p>
                    <p><b>📝 Chi tiết:</b> ${event.extendedProps.description ?? 'Không có'}</p>
                </div>
            `,
                icon: 'info'
            });
        }


        /* =========================
           5. THÊM MỚI
        ========================== */

        $('#btnAddNew').on('click', function() {
            resetForm();
            $('#modalTitle').text('THÊM LỊCH CÔNG TÁC MỚI');
            $('#btnDelete').hide();
            modal.show();
        });


        /* =========================
           6. SAVE EVENT
        ========================== */

        $('#eventForm').on('submit', function(e) {
            e.preventDefault();

            const btn = $(this).find('button[type="submit"]');
            btn.prop('disabled', true).text('Đang lưu...');

            $.post('save_event.php', $(this).serialize())
                .done(function(res) {

                    let data;
                    try {
                        data = typeof res === 'object' ? res : JSON.parse(res);
                    } catch {
                        Swal.fire('Lỗi', 'Phản hồi không hợp lệ.', 'error');
                        return;
                    }

                    if (data.status === 'success') {
                        modal.hide();
                        calendar.refetchEvents();
                        Swal.fire('Thành công', data.message, 'success');
                    } else {
                        Swal.fire('Lỗi', data.message, 'error');
                    }
                })
                .fail(function() {
                    Swal.fire('Lỗi', 'Không thể kết nối máy chủ.', 'error');
                })
                .always(function() {
                    btn.prop('disabled', false).text('LƯU THÔNG TIN');
                });
        });


        /* =========================
           7. DELETE EVENT
        ========================== */

        $('#btnDelete').on('click', function() {

            const id = $('#event_id').val();

            Swal.fire({
                title: 'Xác nhận xóa?',
                text: "Lịch sẽ được chuyển vào thùng rác!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Đồng ý xóa',
                cancelButtonText: 'Hủy'
            }).then((result) => {

                if (!result.isConfirmed) return;

                $.post('delete_event.php', {
                        id: id
                    })
                    .done(function(res) {

                        let data = typeof res === 'object' ? res : JSON.parse(res);

                        if (data.status === 'success') {
                            modal.hide();
                            resetForm();
                            calendar.refetchEvents();

                            Swal.fire({
                                title: 'Đã xóa!',
                                text: data.message,
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Lỗi', data.message, 'error');
                        }
                    })
                    .fail(function() {
                        Swal.fire('Lỗi', 'Không thể kết nối máy chủ', 'error');
                    });
            });
        });


        /* =========================
           8. UTILITIES
        ========================== */

        function resetForm() {
            $('#eventForm')[0].reset();
            $('#event_id').val('');
        }

        function formatDateTime(date) {
            if (!date) return '';
            const d = new Date(date);
            d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
            return d.toISOString().slice(0, 16);
        }

    });
</script>


<?php include '../../includes/footer.php'; ?>