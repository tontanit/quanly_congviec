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
        transition: transform 0.1s ease;
    }

    .fc-event:hover {
        transform: scale(1.02);
        z-index: 5;
    }

    .fc-daygrid-event {
        white-space: normal !important;
        display: block !important;
    }

    /* 4. Đánh dấu màu sắc theo loại hình - CODE BỔ SUNG */
    .bg-event-hop {
        background-color: #ef4444 !important;
        border-left: 4px solid #991b1b !important;
    }

    .bg-event-cong-tac {
        background-color: #3b82f6 !important;
        border-left: 4px solid #1e40af !important;
    }

    .bg-event-tiep-khach {
        background-color: #10b981 !important;
        border-left: 4px solid #065f46 !important;
    }

    .bg-event-khac {
        background-color: #64748b !important;
        border-left: 4px solid #334155 !important;
    }

    .event-type-tag {
        font-size: 0.6rem;
        background: rgba(0, 0, 0, 0.2);
        padding: 1px 4px;
        border-radius: 3px;
        margin-right: 5px;
        font-weight: 800;
        color: #fff;
        text-transform: uppercase;
    }

    .event-leader-tag {
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        margin-bottom: 2px;
        display: block;
        color: #fff;
    }

    .event-job-title {
        font-size: 0.85rem;
        font-weight: 500;
        line-height: 1.3;
        display: block;
        color: #fff;
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

    /* 3. CSS cho cụm lọc ngày và xuất file */
    .export-filter-group {
        background: #ffffff;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .export-filter-group .input-group-text {
        background-color: #f8f9fa;
        color: #6c757d;
        font-weight: 600;
        font-size: 0.75rem;
        border: none;
        padding: 0 10px;
    }

    .export-filter-group input[type="date"] {
        border: none;
        font-size: 0.85rem;
        color: #495057;
        padding: 5px;
        width: 135px;
    }

    .export-filter-group .btn-export {
        background: #fff;
        border: none;
        border-left: 1px solid #dee2e6;
        padding: 5px 12px;
        transition: all 0.2s;
    }

    .export-filter-group .btn-export:hover {
        background: #f8f9fa;
    }
</style>

<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-xl-4 col-lg-3">
            <h3 class="fw-bold mb-0 text-primary">
                <i class="fa fa-calendar-check me-2"></i>LỊCH CÔNG TÁC
            </h3>
        </div>

        <div class="col-xl-5 col-lg-6">
            <form action="export_excel.php" method="GET" class="d-flex justify-content-end align-items-center w-100">
                <div class="input-group input-group-sm export-filter-group shadow-sm">
                    <span class="input-group-text">TỪ</span>
                    <input type="date" name="from_date" value="<?php echo date('Y-m-01'); ?>" required>

                    <span class="input-group-text border-start">ĐẾN</span>
                    <input type="date" name="to_date" value="<?php echo date('Y-m-t'); ?>" required>

                    <button type="submit" class="btn-export text-success" title="Xuất Excel">
                        <i class="fa fa-file-excel fa-lg"></i>
                    </button>
                    <button type="submit" formaction="export_word.php" class="btn-export text-primary" title="Xuất Word">
                        <i class="fa fa-file-word fa-lg"></i>
                    </button>
                </div>
            </form>
        </div>

        <div class="col-xl-3 col-lg-3 text-end">
            <?php if ($_SESSION['role'] == 'admin'): ?>
                <a href="trash.php" class="btn btn-outline-secondary btn-sm fw-bold shadow-sm me-1" title="Thùng rác">
                    <i class="fa fa-trash-alt"></i>
                </a>
                <button class="btn btn-primary btn-sm fw-bold shadow-sm" id="btnAddNew">
                    <i class="fa fa-plus-circle me-1"></i> THÊM MỚI
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div id='calendar'></div>
        </div>
        <div class="card-footer bg-white border-top-0 py-3">
            <div class="d-flex flex-wrap gap-4 justify-content-center">
                <div class="d-flex align-items-center"><span class="badge bg-danger me-2" style="width:12px; height:12px; border-radius:3px"></span> <small class="fw-bold">Họp</small></div>
                <div class="d-flex align-items-center"><span class="badge bg-primary me-2" style="width:12px; height:12px; border-radius:3px"></span> <small class="fw-bold">Công tác</small></div>
                <div class="d-flex align-items-center"><span class="badge bg-success me-2" style="width:12px; height:12px; border-radius:3px"></span> <small class="fw-bold">Tiếp khách</small></div>
                <div class="d-flex align-items-center"><span class="badge bg-secondary me-2" style="width:12px; height:12px; border-radius:3px"></span> <small class="fw-bold">Khác</small></div>
            </div>
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
            events: 'get_events.php',

            // CODE BỔ SUNG: Gán class màu sắc dựa trên loại hình
            eventDidMount: function(info) {
                const type = info.event.extendedProps.type;
                if (type === 'Họp') info.el.classList.add('bg-event-hop');
                else if (type === 'Công tác') info.el.classList.add('bg-event-cong-tac');
                else if (type === 'Tiếp khách') info.el.classList.add('bg-event-tiep-khach');
                else info.el.classList.add('bg-event-khac');
            },

            // CODE BỔ SUNG: Cấu trúc nội dung hiển thị (Gồm Leader và Icon)
            eventContent: function(arg) {
                let leader = arg.event.extendedProps.leader || 'N/A';
                let type = arg.event.extendedProps.type || 'Khác';
                let fullTitle = arg.event.title;
                let cleanTitle = fullTitle.includes('] ') ? fullTitle.split('] ').pop() : fullTitle;

                let typeIcon = type === 'Họp' ? '<i class="fa fa-comments me-1"></i>' : '<i class="fa fa-briefcase me-1"></i>';

                let container = document.createElement('div');
                container.innerHTML = `
                    <div class="d-flex align-items-center mb-1" style="overflow:hidden">
                        <span class="event-type-tag">${type}</span>
                        <span class="event-leader-tag mb-0 border-0" style="white-space:nowrap"><i class="fa fa-user-circle me-1"></i>${leader}</span>
                    </div>
                    <span class="event-job-title">${typeIcon}${cleanTitle}</span>
                `;
                return {
                    domNodes: [container]
                };
            },

            eventClick: function(info) {
                <?php if ($_SESSION['role'] == 'admin'): ?>
                    openModalForEdit(info.event);
                <?php else: ?>
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

        $('#btnAddNew').on('click', function() {
            $('#eventForm')[0].reset();
            $('#event_id').val('');
            $('#modalTitle').text('THÊM LỊCH CÔNG TÁC MỚI');
            $('#btnDelete').hide();
            $('#eventModal').modal('show');
        });

        function openModalForEdit(event) {
            $('#event_id').val(event.id);
            let title = event.title.includes('] ') ? event.title.split('] ').pop() : event.title;
            $('input[name="tieu_de"]').val(title);

            if (event.startStr) $('#bat_dau').val(event.startStr.substring(0, 16));
            if (event.endStr) $('#ket_thuc').val(event.endStr.substring(0, 16));

            $('input[name="dia_diem"]').val(event.extendedProps.location);
            $('select[name="loai_lich"]').val(event.extendedProps.type);
            $('textarea[name="noi_dung"]').val(event.extendedProps.description);

            $('#modalTitle').text('CHỈNH SỬA LỊCH CÔNG TÁC');
            $('#btnDelete').show();
            $('#eventModal').modal('show');
        }

        $('#eventForm').on('submit', function(e) {
            e.preventDefault();
            $.post('save_event.php', $(this).serialize(), function(res) {
                try {
                    const data = JSON.parse(res);
                    if (data.status === 'success') {
                        $('#eventModal').modal('hide');
                        calendar.refetchEvents();
                        Swal.fire('Thành công', data.message, 'success');
                    } else {
                        Swal.fire('Lỗi', data.message, 'error');
                    }
                } catch (e) {
                    console.error("Lỗi parse JSON:", res);
                }
            });
        });

        $('#btnDelete').on('click', function() {
            const id = $('#event_id').val();
            Swal.fire({
                title: 'Xác nhận xóa?',
                text: "Lịch sẽ được chuyển vào thùng rác!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Đồng ý xóa'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'delete_event.php',
                        type: 'POST',
                        data: {
                            id: id
                        },
                        success: function(res) {
                            let data = typeof res === 'object' ? res : JSON.parse(res);
                            if (data.status === 'success') {
                                $('#eventModal').modal('hide');
                                $('.modal-backdrop').remove();
                                calendar.refetchEvents();
                                Swal.fire({
                                    title: 'Đã xóa!',
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            } else {
                                Swal.fire('Lỗi', data.message, 'error');
                            }
                        }
                    });
                }
            });
        });
    });
</script>

<?php include '../../includes/footer.php'; ?>