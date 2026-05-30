<x-default-layout>
    @section('title')
        Recurring Activities – Daily Planner
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('daily-planner.recurring-activity') }}
    @endsection

    @push('styles')
        <style>
            /* ─── Color Tokens ──────────────────────────────────────────────── */
            :root {
                --ra-primary: #6366f1;
                --ra-primary-2: #818cf8;
                --ra-success: #10b981;
                --ra-warning: #f59e0b;
                --ra-danger: #ef4444;
                --ra-surface: #ffffff;
                --ra-glass: rgba(255, 255, 255, 0.72);
                --ra-border: rgba(99, 102, 241, 0.12);
            }

            /* ─── Hero Banner ───────────────────────────────────────────────── */
            .ra-hero {
                background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a78bfa 100%);
                border-radius: 20px;
                padding: 2rem 2.5rem;
                position: relative;
                overflow: hidden;
                margin-bottom: 2rem;
                box-shadow: 0 20px 60px rgba(99, 102, 241, .3);
            }

            .ra-hero::before {
                content: '';
                position: absolute;
                top: -60px;
                right: -60px;
                width: 260px;
                height: 260px;
                border-radius: 50%;
                background: rgba(255, 255, 255, .08);
            }

            .ra-hero::after {
                content: '';
                position: absolute;
                bottom: -80px;
                left: 30%;
                width: 200px;
                height: 200px;
                border-radius: 50%;
                background: rgba(255, 255, 255, .06);
            }

            .ra-hero-text {
                position: relative;
                z-index: 1;
            }

            .ra-hero h1 {
                color: #fff;
                font-size: 1.75rem;
                font-weight: 800;
                margin-bottom: .35rem;
            }

            .ra-hero p {
                color: rgba(255, 255, 255, .82);
                font-size: .95rem;
                margin: 0;
            }

            /* ─── Stat Cards ────────────────────────────────────────────────── */
            .ra-stat-card {
                background: var(--ra-glass);
                backdrop-filter: blur(10px);
                border: 1px solid var(--ra-border);
                border-radius: 16px;
                padding: 1.5rem;
                transition: transform .25s, box-shadow .25s;
            }

            .ra-stat-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 12px 40px rgba(99, 102, 241, .15);
            }

            .ra-stat-num {
                font-size: 2.25rem;
                font-weight: 800;
                line-height: 1;
            }

            .ra-stat-lbl {
                font-size: .8rem;
                color: #6b7280;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: .06em;
                margin-top: .35rem;
            }

            .ra-stat-icon {
                width: 48px;
                height: 48px;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.3rem;
            }

            /* ─── Frequency Badge ────────────────────────────────────────────── */
            .freq-badge {
                display: inline-flex;
                align-items: center;
                gap: .35rem;
                padding: .35rem .75rem;
                border-radius: 30px;
                font-size: .75rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .05em;
            }

            .freq-daily {
                background: #ede9fe;
                color: #6d28d9;
            }

            .freq-weekly {
                background: #dbeafe;
                color: #1d4ed8;
            }

            .freq-monthly {
                background: #d1fae5;
                color: #047857;
            }

            /* ─── Next Occurrences Pill ──────────────────────────────────────── */
            .occ-pill {
                display: inline-block;
                background: #f3f4f6;
                border-radius: 20px;
                padding: .2rem .6rem;
                font-size: .72rem;
                color: #374151;
                margin: 2px;
                font-weight: 600;
            }

            /* ─── Active Toggle ──────────────────────────────────────────────── */
            .status-dot {
                width: 10px;
                height: 10px;
                border-radius: 50%;
                display: inline-block;
                margin-right: .4rem;
            }

            .status-dot.active {
                background: #10b981;
                box-shadow: 0 0 0 3px rgba(16, 185, 129, .2);
            }

            .status-dot.inactive {
                background: #9ca3af;
            }

            /* ─── Day Checkbox ───────────────────────────────────────────────── */
            .day-checkbox-group {
                display: flex;
                flex-wrap: wrap;
                gap: .5rem;
            }

            .day-check-label {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 44px;
                height: 44px;
                border: 2px solid #e5e7eb;
                border-radius: 10px;
                cursor: pointer;
                font-weight: 700;
                font-size: .75rem;
                transition: all .2s;
                color: #6b7280;
            }

            .day-check-label:has(input:checked) {
                border-color: var(--ra-primary);
                background: var(--ra-primary);
                color: #fff;
            }

            .day-check-label input {
                display: none;
            }

            /* ─── Hover Animation ────────────────────────────────────────────── */
            .hover-lift {
                transition: transform .25s, box-shadow .25s;
            }

            .hover-lift:hover {
                transform: translateY(-3px);
                box-shadow: 0 8px 24px rgba(0, 0, 0, .08);
            }

            /* ─── Modal Polish ───────────────────────────────────────────────── */
            .ra-modal .modal-header {
                background: linear-gradient(135deg, #6366f1, #8b5cf6);
                border-radius: 16px 16px 0 0;
                border: none;
                padding: 1.5rem 2rem;
            }

            .ra-modal .modal-header h5 {
                color: #fff;
                font-weight: 800;
                font-size: 1.1rem;
            }

            .ra-modal .modal-header .btn-close {
                filter: invert(1) brightness(2);
            }

            .ra-modal .modal-content {
                border-radius: 16px;
                border: none;
                box-shadow: 0 25px 80px rgba(0, 0, 0, .18);
            }

            .ra-modal .modal-body {
                padding: 2rem;
            }

            .ra-modal .form-label {
                font-weight: 600;
                color: #374151;
                font-size: .875rem;
            }

            .ra-modal .form-control,
            .ra-modal .form-select {
                border-radius: 10px;
                border: 1.5px solid #e5e7eb;
                padding: .65rem 1rem;
            }

            .ra-modal .form-control:focus,
            .ra-modal .form-select:focus {
                border-color: var(--ra-primary);
                box-shadow: 0 0 0 3px rgba(99, 102, 241, .12);
            }

            /* ─── Sync Pulse ─────────────────────────────────────────────────── */
            @keyframes pulse {

                0%,
                100% {
                    opacity: 1
                }

                50% {
                    opacity: .5
                }
            }

            .syncing {
                animation: pulse 1s infinite;
                pointer-events: none;
            }

            /* ─── Empty State ────────────────────────────────────────────────── */
            .ra-empty {
                text-align: center;
                padding: 4rem 2rem;
            }

            .ra-empty-icon {
                font-size: 4rem;
                margin-bottom: 1rem;
                opacity: .4;
            }
        </style>
    @endpush

    {{-- ─── Hero Banner ─────────────────────────────────────────────────── --}}
    <div class="ra-hero">
        <div class="ra-hero-text">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h1><i class="ki-outline ki-timer fs-2 me-2"></i>Recurring Activities</h1>
                    <p>Kelola aktivitas berulang Anda — otomatis masuk ke Daily Activity hingga 14 hari ke depan.</p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button id="btn-sync-recurring" class="btn btn-sm fw-bold"
                        style="background:rgba(255,255,255,.18);color:#fff;border:1.5px solid rgba(255,255,255,.4);border-radius:10px;">
                        <i class="ki-outline ki-arrows-loop fs-5 me-1"></i> Sync Sekarang
                    </button>
                    <button data-bs-toggle="modal" data-bs-target="#modalCreateRecurring" class="btn btn-sm fw-bold"
                        style="background:#fff;color:#6366f1;border-radius:10px;">
                        <i class="ki-outline ki-plus fs-5 me-1"></i> Tambah Aktivitas
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── Stat Cards ──────────────────────────────────────────────────── --}}
    @php
        $totalRec = $recurrings->count();
        $activeRec = $recurrings->where('is_active', true)->count();
        $dailyRec = $recurrings->where('frequency', 'daily')->count();
        $weeklyRec = $recurrings->where('frequency', 'weekly')->count();
        $monthlyRec = $recurrings->where('frequency', 'monthly')->count();
    @endphp
    <div class="row g-4 mb-6">
        <div class="col-6 col-md-3">
            <div class="ra-stat-card d-flex align-items-center gap-3 hover-lift">
                <div class="ra-stat-icon" style="background:#ede9fe;">
                    <i class="ki-outline ki-calendar fs-3" style="color:#6d28d9;"></i>
                </div>
                <div>
                    <div class="ra-stat-num" style="color:#6366f1;">{{ $totalRec }}</div>
                    <div class="ra-stat-lbl">Total</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="ra-stat-card d-flex align-items-center gap-3 hover-lift">
                <div class="ra-stat-icon" style="background:#d1fae5;">
                    <i class="ki-outline ki-check-circle fs-3" style="color:#047857;"></i>
                </div>
                <div>
                    <div class="ra-stat-num" style="color:#10b981;">{{ $activeRec }}</div>
                    <div class="ra-stat-lbl">Aktif</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="ra-stat-card d-flex align-items-center gap-3 hover-lift">
                <div class="ra-stat-icon" style="background:#dbeafe;">
                    <i class="ki-outline ki-graph-up fs-3" style="color:#1d4ed8;"></i>
                </div>
                <div>
                    <div class="ra-stat-num" style="color:#2563eb;">{{ $weeklyRec }}</div>
                    <div class="ra-stat-lbl">Mingguan</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="ra-stat-card d-flex align-items-center gap-3 hover-lift">
                <div class="ra-stat-icon" style="background:#fef3c7;">
                    <i class="ki-outline ki-calendar-8 fs-3" style="color:#d97706;"></i>
                </div>
                <div>
                    <div class="ra-stat-num" style="color:#f59e0b;">{{ $monthlyRec }}</div>
                    <div class="ra-stat-lbl">Bulanan</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── Recurring Activities Table ──────────────────────────────────── --}}
    <div class="card card-flush border-0 shadow-sm">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div>
                    <h4 class="fw-bold mb-1" style="color:#1f2937;">Daftar Aktivitas Berulang</h4>
                    <p class="text-muted fs-7 mb-0">Klik edit untuk mengubah jadwal atau nonaktifkan suatu aktivitas.</p>
                </div>
            </div>
            <div class="card-toolbar">
                <div class="position-relative d-flex align-items-center">
                    <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4 text-gray-500"></i>
                    <input type="text" id="ra-search" class="form-control form-control-solid ps-12"
                        placeholder="Cari aktivitas..." style="min-width:220px;border-radius:10px;">
                </div>
            </div>
        </div>

        <div class="card-body pt-0 fs-6">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="ra-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Aktivitas</th>
                        <th>Frekuensi</th>
                        <th>Waktu</th>
                        <th>Durasi</th>
                        <th>Periode</th>
                        <th>Jadwal Berikutnya</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recurrings as $i => $rec)
                        <tr class="ra-row">
                            <td class="text-muted fw-bold fs-7">{{ $i + 1 }}</td>
                            <td>
                                <span class="fw-bold text-gray-800 fs-6">{{ $rec->activity }}</span>
                            </td>
                            <td>
                                @php
                                    $freqClass = match ($rec->frequency) {
                                        'daily' => 'freq-daily',
                                        'weekly' => 'freq-weekly',
                                        'monthly' => 'freq-monthly',
                                        default => 'freq-daily'
                                    };
                                    $freqIcon = match ($rec->frequency) {
                                        'daily' => 'ki-outline ki-sun',
                                        'weekly' => 'ki-outline ki-calendar-8',
                                        'monthly' => 'ki-outline ki-calendar-2',
                                        default => 'ki-outline ki-sun'
                                    };
                                @endphp
                                <span class="freq-badge {{ $freqClass }}">
                                    <i class="{{ $freqIcon }} fs-7"></i>
                                    {{ $rec->formatted_frequency }}
                                </span>
                            </td>
                            <td>
                                <span class="fw-semibold text-gray-700">
                                    <i class="ki-outline ki-time fs-6 me-1 text-muted"></i>
                                    {{ $rec->start_time }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-light-primary fw-bold">{{ $rec->duration_minutes }} mnt</span>
                            </td>
                            <td>
                                <div class="fs-7 text-muted">
                                    <div><i
                                            class="ki-outline ki-arrow-right fs-8 me-1"></i>{{ \Carbon\Carbon::parse($rec->start_date)->format('d M Y') }}
                                    </div>
                                    @if($rec->end_date)
                                        <div><i
                                                class="ki-outline ki-arrow-left fs-8 me-1"></i>{{ \Carbon\Carbon::parse($rec->end_date)->format('d M Y') }}
                                        </div>
                                    @else
                                        <div class="text-success fw-semibold fs-8">Tidak ada batas</div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @php $nextOccs = $rec->next_occurrences; @endphp
                                @if(count($nextOccs) > 0)
                                    @foreach(array_slice((array) $nextOccs, 0, 3) as $occ)
                                        <span class="occ-pill">{{ \Carbon\Carbon::parse($occ)->format('d M') }}</span>
                                    @endforeach
                                    @if(count($nextOccs) > 3)
                                        <span class="occ-pill"
                                            style="background:#e0e7ff;color:#4f46e5;">+{{ count($nextOccs) - 3 }}</span>
                                    @endif
                                @else
                                    <span class="text-muted fs-7">Tidak ada</span>
                                @endif
                            </td>
                            <td>
                                @if($rec->is_active)
                                    <span class="fw-semibold text-success fs-7">
                                        <span class="status-dot active"></span>Aktif
                                    </span>
                                @else
                                    <span class="fw-semibold text-muted fs-7">
                                        <span class="status-dot inactive"></span>Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                <button type="button"
                                    class="btn btn-icon btn-sm btn-light btn-active-color-primary me-1 btn-edit-ra"
                                    data-bs-toggle="tooltip" title="Edit" data-id="{{ $rec->id }}"
                                    data-activity="{{ $rec->activity }}" data-frequency="{{ $rec->frequency }}"
                                    data-day_of_week="{{ json_encode($rec->day_of_week ?? []) }}"
                                    data-day_of_month="{{ $rec->day_of_month }}"
                                    data-start_date="{{ $rec->start_date ? \Carbon\Carbon::parse($rec->start_date)->format('Y-m-d') : '' }}"
                                    data-end_date="{{ $rec->end_date ? \Carbon\Carbon::parse($rec->end_date)->format('Y-m-d') : '' }}"
                                    data-start_time="{{ $rec->start_time }}"
                                    data-duration_minutes="{{ $rec->duration_minutes }}"
                                    data-is_active="{{ $rec->is_active ? '1' : '0' }}">
                                    <i class="ki-outline ki-pencil fs-4"></i>
                                </button>
                                <button type="button"
                                    class="btn btn-icon btn-sm btn-light btn-active-color-danger btn-delete-ra"
                                    data-bs-toggle="tooltip" title="Hapus" data-id="{{ $rec->id }}"
                                    data-activity="{{ $rec->activity }}">
                                    <i class="ki-outline ki-trash fs-4"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr id="ra-empty-row">
                            <td colspan="9">
                                <div class="ra-empty">
                                    <div class="ra-empty-icon">🔁</div>
                                    <h4 class="fw-bold text-gray-700 mb-2">Belum ada aktivitas berulang</h4>
                                    <p class="text-muted mb-4">Buat aktivitas berulang agar otomatis masuk ke daily planner
                                        Anda!</p>
                                    <button data-bs-toggle="modal" data-bs-target="#modalCreateRecurring"
                                        class="btn btn-primary fw-bold">
                                        <i class="ki-outline ki-plus fs-5 me-1"></i> Buat Aktivitas Pertama
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

    {{-- ─── Create Modal ─────────────────────────────────────────────────── --}}
    <div class="modal fade ra-modal" id="modalCreateRecurring" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ki-outline ki-plus-circle fs-4 me-2"
                            style="color:#c4b5fd;"></i>Tambah Aktivitas Berulang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formCreateRA">
                    @csrf
                    <div class="modal-body">
                        @include('pages.daily-planner._recurring-form', ['rec' => null])
                    </div>
                    <div class="modal-footer border-0 pb-6 pt-0">
                        <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary fw-bold" id="btnSubmitCreate">
                            <span class="indicator-label"><i class="ki-outline ki-check fs-5 me-1"></i>Simpan</span>
                            <span class="indicator-progress">Menyimpan... <span
                                    class="spinner-border spinner-border-sm ms-2"></span></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ─── Edit Modal ───────────────────────────────────────────────────── --}}
    <div class="modal fade ra-modal" id="modalEditRecurring" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ki-outline ki-pencil fs-4 me-2" style="color:#c4b5fd;"></i>Edit
                        Aktivitas Berulang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditRA">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_ra_id" name="_ra_id">
                    <div class="modal-body">
                        @include('pages.daily-planner._recurring-form', ['rec' => null, 'editMode' => true])
                    </div>
                    <div class="modal-footer border-0 pb-6 pt-0">
                        <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary fw-bold" id="btnSubmitEdit">
                            <span class="indicator-label"><i class="ki-outline ki-check fs-5 me-1"></i>Simpan
                                Perubahan</span>
                            <span class="indicator-progress">Menyimpan... <span
                                    class="spinner-border spinner-border-sm ms-2"></span></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function () {

                // ── Live search ──────────────────────────────────────────────────
                $('#ra-search').on('keyup', function () {
                    var val = $(this).val().toLowerCase();
                    $('#ra-table tbody .ra-row').each(function () {
                        var text = $(this).text().toLowerCase();
                        $(this).toggle(text.indexOf(val) > -1);
                    });
                });

                // ── Frequency field toggling ─────────────────────────────────────
                function toggleFrequencyFields(formPrefix, freq) {
                    $(formPrefix + ' .weekly-fields').toggle(freq === 'weekly');
                    $(formPrefix + ' .monthly-fields').toggle(freq === 'monthly');
                }

                $('[name="frequency"]').on('change', function () {
                    var prefix = $(this).closest('form').attr('id') === 'formCreateRA' ? '#formCreateRA' : '#formEditRA';
                    toggleFrequencyFields(prefix, $(this).val());
                });

                // Initialise on load
                toggleFrequencyFields('#formCreateRA', $('#formCreateRA [name="frequency"]').val());

                // ── Sync Recurring ──────────────────────────────────────────────
                $('#btn-sync-recurring').on('click', function () {
                    var btn = $(this);
                    btn.addClass('syncing').html('<i class="ki-outline ki-arrows-loop fs-5 me-1"></i> Menyinkronkan...');

                    $.ajax({
                        url: '{{ route("daily-planner.recurring-activity.sync") }}',
                        type: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function (res) {
                            btn.removeClass('syncing').html('<i class="ki-outline ki-arrows-loop fs-5 me-1"></i> Sync Sekarang');
                            toastr.success(res.success || 'Aktivitas berulang berhasil disinkronkan ke daily planner!');
                        },
                        error: function () {
                            btn.removeClass('syncing').html('<i class="ki-outline ki-arrows-loop fs-5 me-1"></i> Sync Sekarang');
                            toastr.error('Gagal melakukan sinkronisasi.');
                        }
                    });
                });

                // ── Create ───────────────────────────────────────────────────────
                $('#formCreateRA').on('submit', function (e) {
                    e.preventDefault();
                    var btn = $('#btnSubmitCreate');
                    btn.attr('data-kt-indicator', 'on').prop('disabled', true);

                    $.ajax({
                        url: '{{ route("daily-planner.recurring-activity.store") }}',
                        type: 'POST',
                        data: $(this).serialize(),
                        success: function (res) {
                            btn.removeAttr('data-kt-indicator').prop('disabled', false);
                            $('#modalCreateRecurring').modal('hide');
                            toastr.success(res.success || 'Aktivitas berulang berhasil dibuat!');
                            setTimeout(() => location.reload(), 800);
                        },
                        error: function (xhr) {
                            btn.removeAttr('data-kt-indicator').prop('disabled', false);
                            var errors = xhr.responseJSON?.errors;
                            if (errors) {
                                $.each(errors, function (k, v) { toastr.error(v[0]); });
                            } else {
                                toastr.error('Terjadi kesalahan, periksa kembali form Anda.');
                            }
                        }
                    });
                });

                // ── Open Edit Modal ──────────────────────────────────────────────
                $(document).on('click', '.btn-edit-ra', function () {
                    var d = $(this).data();
                    var days = typeof d.day_of_week === 'string' ? JSON.parse(d.day_of_week) : (d.day_of_week || []);

                    $('#edit_ra_id').val(d.id);
                    $('#formEditRA [name="activity"]').val(d.activity);
                    $('#formEditRA [name="frequency"]').val(d.frequency).trigger('change');
                    $('#formEditRA [name="day_of_month"]').val(d.day_of_month);
                    $('#formEditRA [name="start_date"]').val(d.start_date);
                    $('#formEditRA [name="end_date"]').val(d.end_date);
                    $('#formEditRA [name="start_time"]').val(d.start_time);
                    $('#formEditRA [name="duration_minutes"]').val(d.duration_minutes);
                    $('#formEditRA [name="is_active"]').val(d.is_active);

                    // Tick the correct day checkboxes
                    $('#formEditRA .day-check-input').each(function () {
                        $(this).prop('checked', days.includes($(this).val()));
                    });

                    toggleFrequencyFields('#formEditRA', d.frequency);
                    $('#modalEditRecurring').modal('show');
                });

                // ── Edit Submit ──────────────────────────────────────────────────
                $('#formEditRA').on('submit', function (e) {
                    e.preventDefault();
                    var btn = $('#btnSubmitEdit');
                    btn.attr('data-kt-indicator', 'on').prop('disabled', true);
                    var id = $('#edit_ra_id').val();

                    $.ajax({
                        url: '/daily-planner/recurring-activity/' + id,
                        type: 'PUT',
                        data: $(this).serialize(),
                        success: function (res) {
                            btn.removeAttr('data-kt-indicator').prop('disabled', false);
                            $('#modalEditRecurring').modal('hide');
                            toastr.success(res.success || 'Aktivitas berulang berhasil diperbarui!');
                            setTimeout(() => location.reload(), 800);
                        },
                        error: function (xhr) {
                            btn.removeAttr('data-kt-indicator').prop('disabled', false);
                            var errors = xhr.responseJSON?.errors;
                            if (errors) {
                                $.each(errors, function (k, v) { toastr.error(v[0]); });
                            } else {
                                toastr.error('Gagal memperbarui aktivitas.');
                            }
                        }
                    });
                });

                // ── Delete ───────────────────────────────────────────────────────
                $(document).on('click', '.btn-delete-ra', function () {
                    var id = $(this).data('id');
                    var activity = $(this).data('activity');

                    Swal.fire({
                        title: 'Hapus Aktivitas Berulang?',
                        html: '<p class="text-muted">Aktivitas <strong>' + activity + '</strong> akan dihapus beserta semua jadwal yang belum dikerjakan.</p>',
                        icon: 'warning',
                        showCancelButton: true,
                        buttonsStyling: false,
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal',
                        customClass: {
                            confirmButton: 'btn btn-danger fw-bold me-2',
                            cancelButton: 'btn btn-light fw-bold'
                        }
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: '/daily-planner/recurring-activity/' + id,
                                type: 'DELETE',
                                data: { _token: '{{ csrf_token() }}' },
                                success: function (res) {
                                    toastr.success(res.success || 'Aktivitas berhasil dihapus!');
                                    setTimeout(() => location.reload(), 800);
                                },
                                error: function () {
                                    toastr.error('Gagal menghapus aktivitas.');
                                }
                            });
                        }
                    });
                });

                // ── Init Tooltips ────────────────────────────────────────────────
                var tooltipEls = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipEls.forEach(function (el) { new bootstrap.Tooltip(el); });
            });
        </script>
    @endpush

</x-default-layout>