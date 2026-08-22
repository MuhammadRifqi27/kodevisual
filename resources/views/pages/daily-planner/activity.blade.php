<x-default-layout>
    @section('title')
        Manage Daily Activities
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('daily-planner.activity') }}
    @endsection

    @push('styles')
    <style>
        .fc-event {
            cursor: pointer;
            padding: 2px 5px;
            font-weight: 600;
        }
        .hover-elevate {
            transition: all 0.3s ease;
        }
        .hover-elevate:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        /* Ensure flatpickr input fills full width when static:true is used */
        .flatpickr-wrapper {
            width: 100%;
        }
    </style>
    @endpush

    <!-- Main Card Container -->
    <div class="card card-flush border-0 shadow-sm">
        <!-- Card Header with Navigation Tabs -->
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <!-- Dual-Tab Navigation Buttons -->
                <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link text-active-primary py-3 active" data-bs-toggle="tab" href="#kt_planner_tab_list" role="tab">
                            <i class="ki-outline ki-row-horizontal fs-4 me-2"></i> Daftar Kegiatan
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link text-active-primary py-3" data-bs-toggle="tab" href="#kt_planner_tab_calendar" role="tab" id="kt_calendar_tab_trigger">
                            <i class="ki-outline ki-calendar-8 fs-4 me-2"></i> Tampilan Kalender
                        </a>
                    </li>
                </ul>
            </div>
            <!-- Card Toolbar: Actions -->
            <div class="card-toolbar">
                <button type="button" class="btn btn-primary fw-bold" id="btn_add_new_activity">
                    <i class="ki-outline ki-plus fs-4 me-1"></i> Rencana Baru
                </button>
            </div>
        </div>

        <!-- Card Body containing Tab Contents -->
        <div class="card-body pt-0 fs-6">
            <div class="tab-content">
                
                <!-- Tab 1: Listing View -->
                <div class="tab-pane fade show active" id="kt_planner_tab_list" role="tabpanel">
                    
                    <!-- Search & Filter Row -->
                    <div class="row g-5 align-items-center mb-6 pt-3">
                        <!-- Realtime Search -->
                        <div class="col-md-4">
                            <div class="position-relative d-flex align-items-center">
                                <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4 text-gray-500"></i>
                                <input type="text" id="planner_search_input" class="form-control form-control-solid ps-12" placeholder="Cari kegiatan harian..." />
                            </div>
                        </div>
                        <!-- Status Filter -->
                        <div class="col-md-3">
                            <select id="planner_status_filter" class="form-select form-select-solid" data-control="select2" data-hide-search="true">
                                <option value="" selected>Semua Status</option>
                                <option value="not started">Not Started</option>
                                <option value="in progress">In Progress</option>
                                <option value="done">Done</option>
                            </select>
                        </div>
                        <!-- Date Filter -->
                        <div class="col-md-3">
                            <div class="position-relative d-flex align-items-center">
                                <i class="ki-outline ki-calendar fs-3 position-absolute ms-4 text-gray-500"></i>
                                <input type="text" id="planner_date_filter" class="form-control form-control-solid ps-12" placeholder="Filter Tanggal" />
                            </div>
                        </div>
                        <!-- Reset Button -->
                        <div class="col-md-2 text-end">
                            <button type="button" class="btn btn-light-primary btn-sm w-100 fw-bold" id="btn_reset_filters">
                                <i class="ki-outline ki-arrows-loop fs-5 me-1"></i> Reset
                            </button>
                        </div>
                    </div>

                    <!-- Activities Table -->
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_planner_activities_table">
                            <thead>
                                <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                    <th>Mulai</th>
                                    <th>Selesai</th>
                                    <th>Kegiatan</th>
                                    <th>Status</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="fw-semibold text-gray-600">
                                @forelse($activities as $act)
                                    <tr class="activity-row hover-elevate transition-all duration-300" 
                                        data-id="{{ $act->id }}"
                                        data-activity="{{ $act->activity }}"
                                        data-status="{{ $act->status }}"
                                        data-start="{{ $act->start_datetime ? \Carbon\Carbon::parse($act->start_datetime)->format('Y-m-d H:i') : '' }}"
                                        data-end="{{ $act->end_datetime ? \Carbon\Carbon::parse($act->end_datetime)->format('Y-m-d H:i') : '' }}">
                                        
                                        <!-- Start Datetime -->
                                        <td class="fw-bold text-gray-900">
                                            @if($act->start_datetime)
                                                <div>{{ \Carbon\Carbon::parse($act->start_datetime)->translatedFormat('D, d M Y') }}</div>
                                                <small class="text-muted">{{ \Carbon\Carbon::parse($act->start_datetime)->format('H:i') }}</small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>

                                        <!-- End Datetime -->
                                        <td>
                                            @if($act->end_datetime)
                                                <span class="badge badge-light-info fs-7 px-3 py-2">
                                                    <i class="ki-outline ki-time fs-7 text-info me-1"></i>
                                                    {{ \Carbon\Carbon::parse($act->end_datetime)->format('H:i') }}
                                                </span>
                                                <div><small class="text-muted">{{ \Carbon\Carbon::parse($act->end_datetime)->translatedFormat('d M Y') }}</small></div>
                                            @else
                                                <span class="text-muted fs-7">-</span>
                                            @endif
                                        </td>
                                        
                                        <!-- Activity Description -->
                                        <td class="fw-bold text-gray-800 fs-5 min-w-150px">
                                            {{ $act->activity }}
                                        </td>
                                        
                                        <!-- Status Badge -->
                                        <td>
                                            <span class="badge {{ $act->status_badge_class }} fw-extrabold px-3 py-2">
                                                {{ ucwords($act->status) }}
                                            </span>
                                        </td>
                                        
                                        <!-- Action Buttons -->
                                        <td class="text-end">
                                            <button type="button" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1 btn-edit-activity" data-bs-toggle="tooltip" title="Edit Rencana">
                                                <i class="ki-outline ki-pencil fs-3"></i>
                                            </button>
                                            <button type="button" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm btn-delete-activity" data-bs-toggle="tooltip" title="Hapus Rencana">
                                                <i class="ki-outline ki-trash fs-3"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-10">
                                            <div class="text-center py-5">
                                                <i class="ki-outline ki-notepad fs-3x text-muted mb-3 d-block"></i>
                                                <h4 class="text-gray-700 fw-bold">Belum ada kegiatan terdaftar</h4>
                                                <p class="text-muted fs-6">Mulailah membuat rencana kegiatan harian Anda sekarang!</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab 2: Calendar View -->
                <div class="tab-pane fade" id="kt_planner_tab_calendar" role="tabpanel">
                    <div class="p-3 bg-light-primary rounded border border-primary-200 mb-6 d-flex align-items-center">
                        <i class="ki-outline ki-information fs-2 text-primary me-3"></i>
                        <span class="fs-7 text-primary-800 fw-semibold">
                            <strong>Interaksi Kalender:</strong> Klik sel tanggal kosong untuk menjadwalkan kegiatan baru secara otomatis. Klik pada event untuk melihat detail kegiatan, mengubah status, melakukan edit, atau menghapus rencana.
                        </span>
                    </div>
                    <!-- Calendar Element -->
                    <div id="kt_calendar_app"></div>
                </div>

            </div>
        </div>
    </div>

    <!-- Shared Add & Edit Activity Modal -->
    <div class="modal fade" id="kt_modal_activity_form" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-550px">
            <div class="modal-content border-0 shadow">
                <form id="planner_crud_form">
                    @csrf
                    <!-- Hidden field for Edit ID -->
                    <input type="hidden" name="activity_id" id="form_activity_id" value="" />

                    <div class="modal-header bg-light-primary border-0 py-5">
                        <h2 class="fw-bold text-gray-900 mb-0" id="form_modal_title">Rencanakan Kegiatan Baru</h2>
                        <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                            <i class="ki-outline ki-cross fs-1"></i>
                        </div>
                    </div>
                    <div class="modal-body py-10 px-lg-17">
                        
                        <!-- Start Datetime Picker -->
                        <div class="fv-row mb-7">
                            <label class="fs-6 fw-semibold required mb-2">Tanggal &amp; Jam Mulai</label>
                            <div class="mb-0 position-relative">
                                <input class="form-control form-control-solid" placeholder="Pilih tanggal &amp; jam mulai" id="form_picker_start" name="start_datetime" required />
                            </div>
                        </div>

                        <!-- End Datetime Picker -->
                        <div class="fv-row mb-7">
                            <label class="fs-6 fw-semibold mb-2">Tanggal &amp; Jam Selesai <span class="text-muted fs-7">(opsional)</span></label>
                            <div class="mb-0 position-relative">
                                <input class="form-control form-control-solid" placeholder="Pilih tanggal &amp; jam selesai" id="form_picker_end" name="end_datetime" />
                            </div>
                        </div>

                        <!-- Activity Description Input -->
                        <div class="fv-row mb-7">
                            <label class="fs-6 fw-semibold required mb-2">Apa Kegiatannya?</label>
                            <textarea class="form-control form-control-solid" rows="3" name="activity" id="form_input_activity" placeholder="Rapatkan koordinasi, olahraga sore, beli buku, dsb..." required></textarea>
                        </div>

                        <!-- Status Picker Dropdown -->
                        <div class="fv-row mb-7">
                            <label class="fs-6 fw-semibold required mb-2">Status Kegiatan</label>
                            <select class="form-select form-select-solid" name="status" id="form_select_status">
                                <option value="not started">Not Started (Belum Mulai)</option>
                                <option value="in progress">In Progress (Sedang Berjalan)</option>
                                <option value="done">Done (Selesai)</option>
                            </select>
                        </div>

                    </div>
                    <div class="modal-footer flex-center border-0 pb-10">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" id="form_submit_btn" class="btn btn-primary">
                            <span class="indicator-label"><i class="ki-outline ki-check fs-5 me-1"></i> Simpan</span>
                            <span class="indicator-progress">Harap tunggu... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Calendar Event Details Modal -->
    <div class="modal fade" id="kt_modal_event_details" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-500px">
            <div class="modal-content border-0">
                <div class="modal-header bg-light-primary border-0 py-5">
                    <h2 class="fw-bold text-gray-900 mb-0">Detail Kegiatan Planner</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-outline ki-cross fs-1"></i>
                    </div>
                </div>
                <div class="modal-body py-8 px-8 px-lg-12">
                    
                    <div class="d-flex align-items-center mb-6">
                        <div class="symbol symbol-45px me-4">
                            <span class="symbol-label bg-light-primary">
                                <i class="ki-outline ki-notepad fs-1 text-primary"></i>
                            </span>
                        </div>
                        <div class="d-flex flex-column">
                            <h3 class="fw-extrabold text-gray-900 mb-1" id="detail_activity_title">Event Title</h3>
                            <span class="text-muted fw-semibold fs-7" id="detail_activity_datetime">Sunday, 30 May 2026 - 14:00</span>
                        </div>
                    </div>

                    <div class="separator separator-dashed my-5"></div>

                    <div class="d-flex flex-stack mb-5">
                        <span class="fw-bold text-gray-600 fs-6">Status Kegiatan:</span>
                        <div id="detail_badge_container">
                            <span class="badge badge-light-warning fw-extrabold px-3 py-2" id="detail_activity_status">Not Started</span>
                        </div>
                    </div>

                    <div class="d-flex flex-stack">
                        <span class="fw-bold text-gray-600 fs-6">Ubah Status Cepat:</span>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-xs btn-light-warning fw-bold btn-quick-status" data-status="not started">Not Started</button>
                            <button type="button" class="btn btn-xs btn-light-primary fw-bold btn-quick-status" data-status="in progress">In Progress</button>
                            <button type="button" class="btn btn-xs btn-light-success fw-bold btn-quick-status" data-status="done">Done</button>
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-0 pb-10 flex-center">
                    <button type="button" class="btn btn-sm btn-light me-3" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-sm btn-primary btn-edit-calendar-event me-3">
                        <i class="ki-outline ki-pencil fs-5 me-1"></i> Edit
                    </button>
                    <button type="button" class="btn btn-sm btn-danger btn-delete-calendar-event">
                        <i class="ki-outline ki-trash fs-5 me-1"></i> Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        var calendar = null;
        var currentClickedEventData = null; // Store event details when clicked

        // Initialize Flatpickr Datetime Pickers for CRUD Modal
        var fpFormStart = flatpickr("#form_picker_start", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            defaultDate: new Date(),
            allowInput: true,
            static: true,
            time_24hr: true
        });

        var fpFormEnd = flatpickr("#form_picker_end", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            defaultDate: new Date(new Date().getTime() + 60 * 60 * 1000),
            allowInput: true,
            static: true,
            time_24hr: true
        });

        // Initialize Flatpickr for Date Filter in Table
        var filterDatePicker = flatpickr("#planner_date_filter", {
            dateFormat: "Y-m-d",
            onClose: function(selectedDates, dateStr, instance) {
                applyTableFilters();
            }
        });

        // Client-side Interactive Filter & Search
        function applyTableFilters() {
            var searchVal = $('#planner_search_input').val().toLowerCase();
            var statusVal = $('#planner_status_filter').val();
            var dateVal = $('#planner_date_filter').val();

            $('#kt_planner_activities_table tbody tr.activity-row').each(function() {
                var row = $(this);
                var startStr = (row.data('start') || '').toString().toLowerCase();
                var textMatch = row.data('activity').toString().toLowerCase().indexOf(searchVal) > -1 || startStr.indexOf(searchVal) > -1;
                var statusMatch = statusVal === '' || row.data('status') === statusVal;
                var dateMatch = dateVal === '' || startStr.indexOf(dateVal) > -1;

                if (textMatch && statusMatch && dateMatch) {
                    row.show();
                } else {
                    row.hide();
                }
            });
        }

        // Initialize FullCalendar App
        function initFullCalendar() {
            var calendarEl = document.getElementById('kt_calendar_app');
            if (!calendarEl) return;

            calendar = new FullCalendar.Calendar(calendarEl, {
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                initialView: 'dayGridMonth',
                locale: 'id',
                navLinks: true,
                selectable: true,
                selectMirror: true,
                events: '{{ route("daily-planner.events") }}', // JSON Feed
                
                // Select slot -> Trigger Add Modal
                select: function(arg) {
                    var selectedDate = arg.startStr.split('T')[0];
                    openFormModalForCreate(selectedDate);
                    calendar.unselect();
                },

                // Click event -> Trigger Event Details Modal
                eventClick: function(arg) {
                    var event = arg.event;
                    var props = event.extendedProps;

                    currentClickedEventData = {
                        id: event.id,
                        activity: props.activity,
                        status: props.status,
                        start_datetime: props.raw_start,
                        end_datetime: props.raw_end,
                        title: event.title
                    };

                    // Populate Event Details Modal
                    $('#detail_activity_title').text(currentClickedEventData.activity);

                    // Format DateTime nicely
                    var rawStart = new Date(currentClickedEventData.start_datetime);
                    var dtStr = rawStart.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
                        + ' - ' + rawStart.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                    $('#detail_activity_datetime').text(dtStr);

                    // Badge status
                    var bClass = 'badge-light-warning';
                    if (currentClickedEventData.status === 'in progress') bClass = 'badge-light-primary';
                    if (currentClickedEventData.status === 'done') bClass = 'badge-light-success';

                    $('#detail_badge_container').html('<span class="badge ' + bClass + ' fw-extrabold px-3 py-2" id="detail_activity_status">' + currentClickedEventData.status.toUpperCase() + '</span>');

                    $('#kt_modal_event_details').modal('show');
                }
            });

            calendar.render();
        }

        // Helper: Open Modal for Create
        function openFormModalForCreate(prefilledDate) {
            $('#planner_crud_form')[0].reset();
            $('#form_activity_id').val('');
            $('#form_modal_title').text('Rencanakan Kegiatan Baru 🚀');

            var now = new Date();
            var startDefault = prefilledDate
                ? new Date(prefilledDate + 'T' + now.toTimeString().slice(0, 5))
                : now;
            var endDefault = new Date(startDefault.getTime() + 60 * 60 * 1000);

            fpFormStart.setDate(startDefault);
            fpFormEnd.setDate(endDefault);

            $('#kt_modal_activity_form').modal('show');
        }

        // Helper: Open Modal for Edit
        function openFormModalForEdit(id, activity, status, startDatetime, endDatetime) {
            $('#form_activity_id').val(id);
            $('#form_input_activity').val(activity);
            $('#form_select_status').val(status).trigger('change');

            fpFormStart.setDate(startDatetime || new Date());
            fpFormEnd.setDate(endDatetime || new Date(new Date().getTime() + 60 * 60 * 1000));

            $('#form_modal_title').text('Edit Rencana Kegiatan ✏️');
            $('#kt_modal_activity_form').modal('show');
        }

        $(document).ready(function() {
            
            // Register Events for Filters
            $('#planner_search_input').keyup(function() {
                applyTableFilters();
            });

            $('#planner_status_filter').change(function() {
                applyTableFilters();
            });

            $('#btn_reset_filters').click(function() {
                $('#planner_search_input').val('');
                $('#planner_status_filter').val('').trigger('change');
                filterDatePicker.clear();
                applyTableFilters();
                toastr.info('Filter berhasil direset.');
            });

            // Action: Create New Click
            $('#btn_add_new_activity').click(function() {
                openFormModalForCreate();
            });

            // Action: Edit Row Click
            $('.btn-edit-activity').click(function() {
                var row = $(this).closest('tr');
                openFormModalForEdit(
                    row.data('id'),
                    row.data('activity'),
                    row.data('status'),
                    row.data('start'),
                    row.data('end')
                );
            });

            // Action: Delete Row Click
            $('.btn-delete-activity').click(function() {
                var row = $(this).closest('tr');
                var id = row.data('id');
                var activityText = row.data('activity');

                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: 'Kegiatan "' + activityText + '" akan dihapus secara permanen!',
                    icon: 'warning',
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                    customClass: {
                        confirmButton: 'btn btn-danger me-2',
                        cancelButton: 'btn btn-light'
                    }
                }).then(function(result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/daily-planner/activity/' + id,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    toastr.success(response.success);
                                    row.fadeOut(500, function() {
                                        row.remove();
                                        location.reload();
                                    });
                                }
                            },
                            error: function(xhr) {
                                toastr.error('Gagal menghapus kegiatan harian.');
                            }
                        });
                    }
                });
            });

            // Tab Switching -> Render Calendar dynamically (required by FullCalendar inside modals/tabs)
            $('#kt_calendar_tab_trigger').on('shown.bs.tab', function() {
                if (calendar === null) {
                    initFullCalendar();
                } else {
                    calendar.updateSize();
                    calendar.refetchEvents();
                }
            });

            // Action: Edit Event from Calendar Details Modal
            $('.btn-edit-calendar-event').click(function() {
                if (!currentClickedEventData) return;
                $('#kt_modal_event_details').modal('hide');
                openFormModalForEdit(
                    currentClickedEventData.id,
                    currentClickedEventData.activity,
                    currentClickedEventData.status,
                    currentClickedEventData.start_datetime,
                    currentClickedEventData.end_datetime
                );
            });

            // Action: Delete Event from Calendar Details Modal
            $('.btn-delete-calendar-event').click(function() {
                if (!currentClickedEventData) return;
                var id = currentClickedEventData.id;
                var text = currentClickedEventData.activity;

                Swal.fire({
                    title: 'Hapus rencana kegiatan?',
                    text: 'Agenda "' + text + '" akan dihapus secara permanen!',
                    icon: 'warning',
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    customClass: {
                        confirmButton: 'btn btn-danger me-2',
                        cancelButton: 'btn btn-light'
                    }
                }).then(function(result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/daily-planner/activity/' + id,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                $('#kt_modal_event_details').modal('hide');
                                if (response.success) {
                                    toastr.success(response.success);
                                    if (calendar) calendar.refetchEvents();
                                    setTimeout(function() {
                                        location.reload();
                                    }, 800);
                                }
                            },
                            error: function(xhr) {
                                toastr.error('Gagal menghapus agenda dari kalender.');
                            }
                        });
                    }
                });
            });

            // Action: Quick Status Update from Calendar Details Modal
            $('.btn-quick-status').click(function() {
                if (!currentClickedEventData) return;
                var status = $(this).data('status');
                var id = currentClickedEventData.id;

                $.ajax({
                    url: '/daily-planner/activity/' + id,
                    type: 'PUT',
                    data: {
                        _token: '{{ csrf_token() }}',
                        start_datetime: currentClickedEventData.start_datetime,
                        end_datetime: currentClickedEventData.end_datetime,
                        activity: currentClickedEventData.activity,
                        status: status
                    },
                    success: function(response) {
                        $('#kt_modal_event_details').modal('hide');
                        toastr.success('Status kegiatan berhasil diperbarui!');
                        if (calendar) calendar.refetchEvents();
                        setTimeout(function() {
                            location.reload();
                        }, 800);
                    },
                    error: function(xhr) {
                        toastr.error('Gagal memperbarui status kegiatan.');
                    }
                });
            });

            // Submit CRUD Form (Create / Edit) via AJAX
            $('#planner_crud_form').submit(function(e) {
                e.preventDefault();
                var submitBtn = $('#form_submit_btn');
                submitBtn.attr('data-kt-indicator', 'on').prop('disabled', true);

                var id = $('#form_activity_id').val();
                var isEdit = id !== '';
                var url = isEdit ? '/daily-planner/activity/' + id : '{{ route("daily-planner.activity.store") }}';
                var method = isEdit ? 'PUT' : 'POST';

                $.ajax({
                    url: url,
                    type: method,
                    data: $(this).serialize(),
                    success: function(response) {
                        submitBtn.removeAttr('data-kt-indicator').prop('disabled', false);
                        $('#kt_modal_activity_form').modal('hide');
                        $('#planner_crud_form')[0].reset();
                        
                        toastr.success(response.success);
                        if (calendar) calendar.refetchEvents();
                        
                        setTimeout(function() {
                            location.reload();
                        }, 800);
                    },
                    error: function(xhr) {
                        submitBtn.removeAttr('data-kt-indicator').prop('disabled', false);
                        var errors = xhr.responseJSON.errors;
                        if (errors) {
                            $.each(errors, function(key, value) {
                                toastr.error(value[0]);
                            });
                        } else {
                            toastr.error('Terjadi kesalahan saat menyimpan rencana.');
                        }
                    }
                });
            });

        });
    </script>
    @endpush
</x-default-layout>
