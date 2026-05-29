<x-default-layout>
    @section('title')
        Daily Planner Dashboard
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('daily-planner.dashboard') }}
    @endsection

    @push('styles')
    <style>
        .hover-elevate:hover {
            transform: translateY(-5px);
            transition: all 0.3s ease;
        }
        .hover-elevate {
            transition: all 0.3s ease;
        }

        .task-done-line {
            text-decoration: line-through;
            opacity: 0.6;
            transition: all 0.3s ease;
        }
        .task-text {
            transition: all 0.3s ease;
        }
    </style>
    @endpush

    <!-- Dynamic Welcome Banner -->
    <div class="card border-0 bg-light-primary mb-5 mb-xl-10 shadow-sm overflow-hidden">
        <div class="card-body p-8 p-lg-12 position-relative">
            <div class="row align-items-center">
                <div class="col-12 col-md-8 position-relative z-index-1">
                    @php
                        $hour = date('H');
                        if ($hour >= 5 && $hour < 11) {
                            $greeting = 'Selamat Pagi';
                            $sub = 'Mari mulai hari dengan penuh semangat dan terencana!';
                        } elseif ($hour >= 11 && $hour < 15) {
                            $greeting = 'Selamat Siang';
                            $sub = 'Tetap fokus dan terhidrasi dengan baik untuk menyelesaikan agenda Anda!';
                        } elseif ($hour >= 15 && $hour < 18) {
                            $greeting = 'Selamat Sore';
                            $sub = 'Hampir menyelesaikan hari kerja, selangkah lagi menuju kesuksesan!';
                        } else {
                            $greeting = 'Selamat Malam';
                            $sub = 'Waktunya evaluasi produktivitas hari ini dan rencanakan hari esok!';
                        }
                    @endphp
                    <h1 class="fw-bolder text-gray-900 fs-2qx lh-1 mb-3">
                        {{ $greeting }}, {{ auth()->user()->name }}! 👋
                    </h1>
                    <p class="text-gray-600 fs-5 fw-semibold mb-6">
                        {{ $sub }}
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="{{ route('daily-planner.activity') }}" class="btn btn-primary btn-sm fw-bold">
                            <i class="ki-outline ki-calendar-8 fs-4 me-1"></i> Buka Kegiatan & Kalender
                        </a>
                        <button type="button" class="btn btn-light-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#kt_modal_quick_add_activity">
                            <i class="ki-outline ki-plus fs-4 me-1"></i> Tambah Kegiatan
                        </button>
                    </div>
                </div>
                <div class="col-12 col-md-4 text-center d-none d-md-block">
                    <div class="text-end">
                        <h2 class="fw-extrabold text-primary fs-3hx mb-0" id="live_time">00:00:00</h2>
                        <span class="text-gray-500 fw-bold fs-6">{{ Carbon\Carbon::today()->translatedFormat('l, d F Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Core Dynamic Metrics -->
    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        <!-- Metric Card 1: Total Tasks -->
        <div class="col-md-6 col-lg-6 col-xl-3">
            <div class="card card-flush h-md-100 hover-elevate border-0 shadow-sm">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ $totalTasks }}</span>
                        <span class="text-gray-500 pt-1 fw-semibold fs-6">Total Kegiatan</span>
                    </div>
                </div>
                <div class="card-body d-flex align-items-end pt-0 pb-6">
                    <div class="d-flex align-items-center flex-column mt-3 w-100">
                        <div class="d-flex justify-content-between w-100 mt-auto mb-2">
                            <span class="fw-bold fs-6 text-muted">Aktivitas Terjadwal</span>
                            <span class="fw-bold fs-6 text-primary"><i class="ki-outline ki-document-text fs-4 text-primary"></i></span>
                        </div>
                        <div class="h-8px w-100 bg-light-primary rounded">
                            <div class="bg-primary rounded h-8px" role="progressbar" style="width: 100%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric Card 2: Not Started -->
        <div class="col-md-6 col-lg-6 col-xl-3">
            <div class="card card-flush h-md-100 hover-elevate border-0 shadow-sm">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <span class="fs-2hx fw-bold text-warning me-2 lh-1 ls-n2">{{ $notStartedTasks }}</span>
                        <span class="text-gray-500 pt-1 fw-semibold fs-6">Belum Mulai</span>
                    </div>
                </div>
                <div class="card-body d-flex align-items-end pt-0 pb-6">
                    <div class="d-flex align-items-center flex-column mt-3 w-100">
                        <div class="d-flex justify-content-between w-100 mt-auto mb-2">
                            <span class="fw-bold fs-6 text-muted">Menunggu Aksi</span>
                            <span class="fw-bold fs-6 text-warning"><i class="ki-outline ki-time fs-4 text-warning"></i></span>
                        </div>
                        <div class="h-8px w-100 bg-light-warning rounded">
                            <div class="bg-warning rounded h-8px" role="progressbar" style="width: {{ $totalTasks > 0 ? ($notStartedTasks / $totalTasks) * 100 : 0 }}%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric Card 3: In Progress -->
        <div class="col-md-6 col-lg-6 col-xl-3">
            <div class="card card-flush h-md-100 hover-elevate border-0 shadow-sm">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <span class="fs-2hx fw-bold text-primary me-2 lh-1 ls-n2">{{ $inProgressTasks }}</span>
                        <span class="text-gray-500 pt-1 fw-semibold fs-6">Sedang Berjalan</span>
                    </div>
                </div>
                <div class="card-body d-flex align-items-end pt-0 pb-6">
                    <div class="d-flex align-items-center flex-column mt-3 w-100">
                        <div class="d-flex justify-content-between w-100 mt-auto mb-2">
                            <span class="fw-bold fs-6 text-muted">Sedang Dikerjakan</span>
                            <span class="fw-bold fs-6 text-primary"><i class="ki-outline ki-loading fs-4 text-primary animate-spin"></i></span>
                        </div>
                        <div class="h-8px w-100 bg-light-primary rounded">
                            <div class="bg-info rounded h-8px" role="progressbar" style="width: {{ $totalTasks > 0 ? ($inProgressTasks / $totalTasks) * 100 : 0 }}%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric Card 4: Progress Hari Ini -->
        <div class="col-md-6 col-lg-6 col-xl-3">
            <div class="card card-flush h-md-100 hover-elevate border-0 shadow-sm bg-light-success">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <span class="fs-2hx fw-bold text-success me-2 lh-1 ls-n2" id="today_completion_text">{{ $todayCompletionRate }}%</span>
                        <span class="text-success-800 pt-1 fw-semibold fs-6">Selesai Hari Ini</span>
                    </div>
                </div>
                <div class="card-body d-flex align-items-end pt-0 pb-6">
                    <div class="d-flex align-items-center flex-column mt-3 w-100">
                        <div class="d-flex justify-content-between w-100 mt-auto mb-2">
                            <span class="fw-bold fs-6 text-success" id="today_ratio_text">{{ $todayDoneCount }} dari {{ $todayTotalCount }} Tugas</span>
                            <span class="fw-bold fs-6 text-success"><i class="ki-outline ki-double-check fs-4 text-success"></i></span>
                        </div>
                        <div class="h-8px w-100 bg-light-success rounded border border-success-200">
                            <div class="bg-success rounded h-8px" id="today_progress_bar" role="progressbar" style="width: {{ $todayCompletionRate }}%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Workspace Layout -->
    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        <!-- Left: Today's Checklist & Weekly Chart (8 Cols) -->
        <div class="col-xl-8">
            <!-- Today's Checklist Card -->
            <div class="card card-flush h-xl-50 mb-5 mb-xl-10 border-0 shadow-sm">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-900 fs-3">Agenda Hari Ini 📝</span>
                        <span class="text-muted mt-1 fw-semibold fs-7">Daftar kegiatan Anda untuk hari ini</span>
                    </h3>
                    <div class="card-toolbar">
                        <button type="button" class="btn btn-sm btn-light-primary fw-bold" data-bs-toggle="modal" data-bs-target="#kt_modal_quick_add_activity">
                            <i class="ki-outline ki-plus fs-5"></i> Tambah Cepat
                        </button>
                    </div>
                </div>
                <div class="card-body pt-2 pb-5 overflow-auto" style="max-height: 380px;">
                    <div class="d-flex flex-column" id="today_checklist_container">
                        @forelse($todayActivities as $act)
                            <div class="d-flex align-items-center mb-5 p-3 rounded bg-hover-light hover-elevate transition-all duration-300" id="checklist_item_{{ $act->id }}">
                                <!-- Checkbox Toggle Status -->
                                <div class="form-check form-check-custom form-check-solid me-4">
                                    <input class="form-check-input h-25px w-25px quick-status-toggle" type="checkbox" value="{{ $act->id }}" data-id="{{ $act->id }}" {{ $act->status === 'done' ? 'checked' : '' }} />
                                </div>
                                <!-- Time Label -->
                                <div class="badge badge-light-primary fw-bold fs-7 p-3 me-4">
                                    <i class="ki-outline ki-time fs-6 me-1 text-primary"></i> {{ $act->formatted_time }}
                                </div>
                                <!-- Activity Title & Custom Badge Status -->
                                <div class="d-flex flex-column flex-grow-1">
                                    <span class="text-gray-800 fw-bold fs-6 task-text {{ $act->status === 'done' ? 'task-done-line' : '' }}" id="task_text_{{ $act->id }}">
                                        {{ $act->activity }}
                                    </span>
                                </div>
                                <!-- Status Badge Display -->
                                <div class="text-end" id="status_badge_container_{{ $act->id }}">
                                    <span class="badge {{ $act->status_badge_class }} fw-bold py-2 px-3">
                                        {{ ucwords($act->status) }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-10">
                                <i class="ki-outline ki-calendar-tick fs-4x text-muted mb-3 d-block"></i>
                                <h4 class="text-gray-700 fw-bold">Belum ada agenda hari ini</h4>
                                <p class="text-muted fs-6">Semua pekerjaan terkendali! Silakan tambahkan kegiatan manual.</p>
                                <button type="button" class="btn btn-sm btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#kt_modal_quick_add_activity">
                                    Mulai Rencana Hari Ini
                                </button>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Weekly Productivity Chart Card -->
            <div class="card card-flush h-xl-50 border-0 shadow-sm">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-900 fs-3">Tren Produktivitas Mingguan 📊</span>
                        <span class="text-muted mt-1 fw-semibold fs-7">Persentase penyelesaian tugas selama 7 hari terakhir</span>
                    </h3>
                </div>
                <div class="card-body pt-0 pb-5">
                    <div id="kt_planner_weekly_chart" style="height: 250px;"></div>
                </div>
            </div>
        </div>

        <!-- Right: Status Breakdown & Next Up Activity (4 Cols) -->
        <div class="col-xl-4">
            <!-- Next Up Activity Highlight -->
            <div class="card card-flush h-xl-50 mb-5 mb-xl-10 border-0 shadow-sm bg-gradient-to-r {{ $nextActivity ? 'bg-light-primary border-start border-primary border-4' : 'bg-light' }}">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-900 fs-4">Kegiatan Berikutnya ⏱️</span>
                        <span class="text-muted mt-1 fw-semibold fs-7">Agenda mendesak berikutnya hari ini</span>
                    </h3>
                </div>
                <div class="card-body pt-0 pb-5">
                    @if($nextActivity)
                        <div class="d-flex flex-column h-100 justify-content-between">
                            <div class="bg-white p-5 rounded border shadow-xs">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="badge badge-light-danger fw-bold fs-8">Up Next</span>
                                    <span class="fw-bolder text-primary fs-5"><i class="ki-outline ki-time fs-5 text-primary"></i> Jam {{ $nextActivity->formatted_time }}</span>
                                </div>
                                <h3 class="fw-extrabold text-gray-900 mb-2">{{ $nextActivity->activity }}</h3>
                                <p class="text-muted fs-7 mb-0">Status saat ini: <span class="badge badge-light-warning fw-bold">{{ ucwords($nextActivity->status) }}</span></p>
                            </div>
                            <div class="mt-5 text-end">
                                <button class="btn btn-sm btn-primary fw-bold quick-status-toggle-btn" data-id="{{ $nextActivity->id }}">
                                    <i class="ki-outline ki-check-circle fs-5 me-1"></i> Tandai Selesai
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-10">
                            <i class="ki-outline ki-coffee fs-3x text-muted mb-2 d-block"></i>
                            <h5 class="text-gray-700 fw-bold mb-1">Santai Sejenak</h5>
                            <p class="text-muted fs-7 mb-0">Tidak ada agenda mendesak yang menunggu saat ini.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Status Breakdown Chart -->
            <div class="card card-flush h-xl-50 border-0 shadow-sm">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-900 fs-4">Distribusi Status Tugas 🎯</span>
                        <span class="text-muted mt-1 fw-semibold fs-7">Breakdown seluruh status kegiatan planner Anda</span>
                    </h3>
                </div>
                <div class="card-body pt-2 pb-5 d-flex flex-column justify-content-center">
                    @if($totalTasks > 0)
                        <div id="kt_planner_status_chart" style="height: 250px;"></div>
                        <div class="mt-2 text-center fs-7 text-muted fw-bold">
                            Total: {{ $totalTasks }} Kegiatan Terlisting
                        </div>
                    @else
                        <div class="text-center py-10">
                            <i class="ki-outline ki-chart-pie-3 fs-3x text-muted mb-2 d-block"></i>
                            <p class="text-muted fs-7 mb-0">Tambahkan kegiatan terlebih dahulu untuk melihat distribusi status visual.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Add Activity Modal -->
    <div class="modal fade" id="kt_modal_quick_add_activity" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-500px">
            <div class="modal-content border-0">
                <form id="kt_modal_quick_add_form">
                    @csrf
                    <div class="modal-header bg-light-primary border-0 py-5">
                        <h2 class="fw-bold text-gray-900 mb-0">Tambah Rencana Cepat 🚀</h2>
                        <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                            <i class="ki-outline ki-cross fs-1"></i>
                        </div>
                    </div>
                    <div class="modal-body py-10 px-lg-17">
                        <!-- Start Datetime Picker -->
                        <div class="fv-row mb-7">
                            <label class="fs-6 fw-semibold required mb-2">Tanggal &amp; Jam Mulai</label>
                            <div class="mb-0">
                                <input class="form-control form-control-solid" placeholder="Pilih tanggal &amp; jam mulai" id="quick_picker_start" name="start_datetime" required />
                            </div>
                        </div>

                        <!-- End Datetime Picker -->
                        <div class="fv-row mb-7">
                            <label class="fs-6 fw-semibold mb-2">Tanggal &amp; Jam Selesai <span class="text-muted fs-7">(opsional)</span></label>
                            <div class="mb-0">
                                <input class="form-control form-control-solid" placeholder="Pilih tanggal &amp; jam selesai" id="quick_picker_end" name="end_datetime" />
                            </div>
                        </div>

                        <!-- Activity Description -->
                        <div class="fv-row mb-7">
                            <label class="fs-6 fw-semibold required mb-2">Kegiatan Apa?</label>
                            <textarea class="form-control form-control-solid" rows="3" name="activity" placeholder="Tuliskan detail rencana kegiatan Anda..." required></textarea>
                        </div>

                        <!-- Status Select -->
                        <div class="fv-row mb-7">
                            <label class="fs-6 fw-semibold required mb-2">Status Awal</label>
                            <select class="form-select form-select-solid" name="status" data-control="select2" data-hide-search="true">
                                <option value="not started" selected>Not Started (Belum Mulai)</option>
                                <option value="in progress">In Progress (Sedang Berjalan)</option>
                                <option value="done">Done (Selesai)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer flex-center border-0 pb-10">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" id="quick_add_submit_btn" class="btn btn-primary">
                            <span class="indicator-label"><i class="ki-outline ki-plus-square fs-5 me-1"></i> Rencanakan!</span>
                            <span class="indicator-progress">Harap tunggu... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Clock Ticker in Header
        function startLiveClock() {
            setInterval(function() {
                var now = new Date();
                var timeString = now.toLocaleTimeString('en-US', { hour12: false });
                $('#live_time').text(timeString);
            }, 1000);
        }

        // ApexCharts - Weekly Trend Chart
        function initWeeklyTrendChart() {
            var element = document.getElementById('kt_planner_weekly_chart');
            if (!element) return;

            var options = {
                series: [{
                    name: 'Rasio Penyelesaian',
                    data: {!! json_encode($weeklyChartData) !!}
                }],
                chart: {
                    type: 'bar',
                    height: 250,
                    toolbar: { show: false },
                    sparkline: { enabled: false }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '45%',
                        borderRadius: 5,
                        dataLabels: { position: 'top' }
                    }
                },
                colors: ['#50CD89'], // Success Green
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return val + "%";
                    },
                    offsetY: -20,
                    style: {
                        fontSize: '11px',
                        colors: ["#3F4254"]
                    }
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: {!! json_encode($weeklyChartCategories) !!},
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: {
                        style: {
                            colors: '#808080',
                            fontSize: '11px'
                        }
                    }
                },
                yaxis: {
                    max: 100,
                    labels: {
                        formatter: function(val) {
                            return val + '%';
                        },
                        style: {
                            colors: '#808080',
                            fontSize: '11px'
                        }
                    }
                },
                fill: {
                    opacity: 1,
                    type: 'solid'
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return val + "% Terselesaikan";
                        }
                    }
                }
            };

            var chart = new ApexCharts(element, options);
            chart.render();
        }

        // ApexCharts - Status Breakdown Donut Chart
        function initStatusBreakdownChart() {
            var element = document.getElementById('kt_planner_status_chart');
            if (!element) return;

            var options = {
                series: [{{ $notStartedTasks }}, {{ $inProgressTasks }}, {{ $doneTasks }}],
                chart: {
                    type: 'donut',
                    height: 250
                },
                labels: ['Belum Mulai', 'Sedang Berjalan', 'Selesai'],
                colors: ['#FFC700', '#009EF7', '#50CD89'], // Warning, Primary, Success
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['#ffffff']
                },
                legend: {
                    position: 'bottom',
                    fontSize: '12px',
                    fontFamily: 'Inter',
                    labels: {
                        colors: '#5E6278'
                    }
                },
                dataLabels: {
                    enabled: false
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: true,
                                name: {
                                    show: true,
                                    fontSize: '14px',
                                    fontFamily: 'Inter',
                                    color: '#A1A5B7',
                                    offsetY: -10
                                },
                                value: {
                                    show: true,
                                    fontSize: '22px',
                                    fontFamily: 'Inter',
                                    color: '#181C32',
                                    fontWeight: '700',
                                    offsetY: 10,
                                    formatter: function (val) {
                                        return val;
                                    }
                                },
                                total: {
                                    show: true,
                                    label: 'Total Rencana',
                                    fontSize: '12px',
                                    color: '#A1A5B7',
                                    fontWeight: '500',
                                    formatter: function (w) {
                                        return w.globals.seriesTotals.reduce(function(a, b) {
                                            return a + b;
                                        }, 0);
                                    }
                                }
                            }
                        }
                    }
                }
            };

            var chart = new ApexCharts(element, options);
            chart.render();
        }

        $(document).ready(function() {
            // Start Clock
            startLiveClock();

            // Initialize Charts
            initWeeklyTrendChart();
            initStatusBreakdownChart();

            // Initialize Start & End Datetime Pickers
            var fpStart = flatpickr("#quick_picker_start", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                defaultDate: new Date(),
                onChange: function(selectedDates) {
                    // Set minimum date for end picker to be same as start
                    if (selectedDates.length > 0) {
                        fpEnd.set('minDate', selectedDates[0]);
                    }
                }
            });

            var fpEnd = flatpickr("#quick_picker_end", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                defaultDate: new Date(new Date().getTime() + 60 * 60 * 1000) // +1 jam
            });

            // Reset pickers when modal is closed
            $('#kt_modal_quick_add_activity').on('hidden.bs.modal', function() {
                fpStart.setDate(new Date());
                fpEnd.setDate(new Date(new Date().getTime() + 60 * 60 * 1000));
            });

            // AJAX: Toggle Status Checkbox
            $('.quick-status-toggle').change(function() {
                var id = $(this).data('id');
                var isChecked = $(this).is(':checked');
                var itemRow = $('#checklist_item_' + id);
                var textEl = $('#task_text_' + id);
                
                // Temporary Visual Polish transition
                if (isChecked) {
                    textEl.addClass('task-done-line');
                } else {
                    textEl.removeClass('task-done-line');
                }

                $.ajax({
                    url: '/daily-planner/activity/' + id + '/toggle',
                    type: 'PATCH',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.success);
                            
                            // Dynamically update status badge
                            var badgeContainer = $('#status_badge_container_' + id);
                            var badgeClass = response.status === 'done' ? 'badge-light-success' : 'badge-light-warning';
                            var labelText = response.status === 'done' ? 'Done' : 'Not Started';
                            
                            badgeContainer.html('<span class="badge ' + badgeClass + ' fw-bold py-2 px-3">' + labelText + '</span>');

                            // Reload page in 1 sec to refresh stats and charts smoothly
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Ada kesalahan saat mengubah status.');
                        // revert checked status
                        $('.quick-status-toggle[data-id="' + id + '"]').prop('checked', !isChecked);
                        if (!isChecked) {
                            textEl.addClass('task-done-line');
                        } else {
                            textEl.removeClass('task-done-line');
                        }
                    }
                });
            });

            // AJAX: Toggle Status Button (Kegiatan Berikutnya)
            $('.quick-status-toggle-btn').click(function() {
                var id = $(this).data('id');
                var btn = $(this);
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm align-middle me-2"></span> Harap tunggu...');

                $.ajax({
                    url: '/daily-planner/activity/' + id + '/toggle',
                    type: 'PATCH',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.success);
                            setTimeout(function() {
                                location.reload();
                            }, 800);
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Ada kesalahan saat mengubah status.');
                        btn.prop('disabled', false).html('<i class="ki-outline ki-check-circle fs-5 me-1"></i> Tandai Selesai');
                    }
                });
            });

            // AJAX: Submit Quick Add Form
            $('#kt_modal_quick_add_form').submit(function(e) {
                e.preventDefault();
                var submitBtn = $('#quick_add_submit_btn');
                submitBtn.attr('data-kt-indicator', 'on').prop('disabled', true);

                $.ajax({
                    url: '{{ route("daily-planner.activity.store") }}',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        submitBtn.removeAttr('data-kt-indicator').prop('disabled', false);
                        if (response.success) {
                            toastr.success(response.success);
                            $('#kt_modal_quick_add_activity').modal('hide');
                            $('#kt_modal_quick_add_form')[0].reset();
                            
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        }
                    },
                    error: function(xhr) {
                        submitBtn.removeAttr('data-kt-indicator').prop('disabled', false);
                        var errors = xhr.responseJSON.errors;
                        if (errors) {
                            $.each(errors, function(key, value) {
                                toastr.error(value[0]);
                            });
                        } else {
                            toastr.error('Terjadi kesalahan saat menambahkan rencana.');
                        }
                    }
                });
            });
        });
    </script>
    @endpush
</x-default-layout>