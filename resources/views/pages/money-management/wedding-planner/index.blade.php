<x-default-layout>
    @section('title')
        Wedding Planner
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('money-management.wedding-planner') }}
    @endsection
    
    @php
        $targetAmount = $plan->target_amount;
        $targetYears = $plan->target_years;
        $currentYear = date('Y');
        $targetYear = $currentYear + $targetYears;
        
        $totalEstimated = $items->sum('estimated_amount');
        
        // Progress based on Manual Savings
        $totalSaved = $totalSavedManual; 
        $progressPercent = $targetAmount > 0 ? min(($totalSaved / $targetAmount) * 100, 100) : 0;

        // Estimation Logic (Server Side Initial)
        $monthlySaving = $targetAmount / ($targetYears * 12);
        
        // Scenario 1: Using Manual Savings
        $remainingManual = max($targetAmount - $totalSavedManual, 0);
        $monthsNeededManual = $monthlySaving > 0 ? ceil($remainingManual / $monthlySaving) : 0;
        $dateManual = now()->addMonths($monthsNeededManual);

        // Scenario 2: Using BNI Portfolio
        $remainingBNI = max($targetAmount - $bniBalance, 0);
        $monthsNeededBNI = $monthlySaving > 0 ? ceil($remainingBNI / $monthlySaving) : 0;
        $dateBNI = now()->addMonths($monthsNeededBNI);
    @endphp

    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
        
        <!-- Header Section -->
        <div class="d-flex flex-column flex-column-fluid">
            <div class="mb-5 mb-lg-10">
                <div class="d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <h1 class="page-heading d-flex  fw-bold fs-3 flex-column justify-content-center my-0">Wedding Planning Dashboard</h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item ">
                                <a href="{{ route('dashboard') }}" class=" text-hover-primary">Home</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-primary w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item ">Money Management</li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-primary w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item ">Wedding Planner</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Overview Row -->
        <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
            <!-- Progress Card -->
            <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 mb-md-5 mb-xl-10">
                <div class="card card-flush h-md-50 mb-5 mb-xl-10" style="background: linear-gradient(112.14deg, #FF80AB 0%, #F50057 100%)">
                    <div class="card-header pt-5">
                        <div class="card-title d-flex flex-column">
                            <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ round($progressPercent) }}%</span>
                            <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Overall Progress (Manual)</span>
                        </div>
                    </div>
                    <div class="card-body d-flex align-items-end pt-0">
                        <div class="d-flex align-items-center flex-column mt-3 w-100">
                            <div class="d-flex justify-content-between w-100 mt-auto mb-2">
                                <span class="fw-boldest text-white fs-6">Rp {{ number_format($totalSaved, 0, ',', '.') }}</span>
                                <span class="fw-boldest text-white fs-6">Target: Rp {{ number_format($targetAmount / 1000000, 0) }}jt</span>
                            </div>
                            <div class="h-8px mx-3 w-100 bg-white bg-opacity-50 rounded">
                                <div class="bg-white rounded h-8px" role="progressbar" style="width: {{ $progressPercent }}%;" aria-valuenow="{{ $progressPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card card-flush h-md-50 mb-5 mb-xl-10">
                    <div class="card-header pt-5">
                        <div class="card-title d-flex flex-column">
                            <span class="fs-2hx fw-bold  me-2 lh-1 ls-n2 simulation_result_monthly_display">Rp {{ number_format($targetAmount / ($targetYears * 12), 0, ',', '.') }}</span>
                            <span class=" opacity-75 pt-1 fw-semibold fs-6">Target Menabung / Bulan</span>
                        </div>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-end pe-0">
                        <span class="fs-6 fw-bolder  d-block mb-2">Target Menikah: <span class="text-primary" id="display_target_year">{{ $targetYear }}</span> (<span id="display_timeline_years">{{ $targetYears }}</span> Tahun Lagi)</span>
                        <div class="symbol-group symbol-hover" id="symbol_group_years">
                            @for($i = 1; $i <= min($targetYears, 4); $i++)
                                <div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip" title="Year {{ $currentYear + $i }}">
                                    <span class="symbol-label bg-{{ ['primary', 'success', 'warning', 'danger'][$i-1] }} text-white fw-bold">{{ substr($currentYear + $i, -2) }}</span>
                                </div>
                            @endfor
                            @if($targetYears > 4)
                                <div class="symbol symbol-35px symbol-circle">
                                    <span class="symbol-label bg-light-dark  fw-bold">+{{ $targetYears - 4 }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Savings Simulator -->
            <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-9 mb-md-5 mb-xl-10">
                <div class="card card-flush h-lg-100">
                    <div class="card-header pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold ">Wedding Savings Simulator & Estimation</span>
                            <span class=" opacity-50 mt-1 fw-semibold fs-6">Sesuaikan target dan lihat estimasi tanggal tercapai</span>
                        </h3>
                    </div>
                    <div class="card-body pt-10">
                        <div class="row g-10">
                            <div class="col-lg-6">
                                <div class="mb-10">
                                    <label class="form-label fw-bold fs-6 mb-5">Target Tabungan (IDR)</label>
                                    <div class="input-group input-group-solid mb-5">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" id="input_target_amount" class="form-control form-control-solid" value="{{ $targetAmount }}" step="1000000" />
                                    </div>
                                </div>
                                <div class="mb-10">
                                    <div class="d-flex flex-stack mb-5">
                                        <label class="form-label fw-bold fs-6 mb-0">Jangka Waktu</label>
                                        <span class="badge badge-light-primary fs-7 fw-bold" id="badge_timeline_years">{{ $targetYears }} Tahun</span>
                                    </div>
                                    <input type="range" class="form-range" id="input_timeline_range" min="1" max="10" step="1" value="{{ $targetYears }}">
                                </div>
                                
                                <div class="separator separator-dashed my-5"></div>
                                
                                <div class="d-flex flex-stack bg-light-info rounded p-5">
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-info fs-6">Portofolio Bank BNI</span>
                                        <span class=" opacity-75 fs-7">Saldo saat ini digunakan sebagai dasar</span>
                                    </div>
                                    <div class="text-end">
                                        <span class="fw-boldest  fs-5">Rp {{ number_format($bniBalance, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-6">
                                <div class="bg-light-primary rounded border border-primary border-dashed p-6 h-100 d-flex flex-column justify-content-center">
                                    <div class="mb-6 text-center">
                                        <span class="fs-4  opacity-75 d-block">Tabungan per Bulan:</span>
                                        <span class="fs-2hx fw-boldest  simulation_result_monthly_display">Rp {{ number_format($targetAmount / ($targetYears * 12), 0, ',', '.') }}</span>
                                    </div>
                                    
                                    <div class="row g-5 mb-5">
                                        <div class="col-6">
                                            <div class="bg-light-primary border border-2 border-primary border-dashed rounded p-4 text-center">
                                                <span class="fs-8 fw-bold  opacity-75 d-block mb-1">Estimasi (BNI)</span>
                                                <span class="fs-6 fw-boldest text-primary d-block" id="est_date_bni">{{ $dateBNI->format('d M Y') }}</span>
                                                <span class="fs-9  opacity-50" id="est_months_bni">{{ $monthsNeededBNI }} Bulan lagi</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="bg-light-primary border border-2 border-primary border-dashed rounded p-4 text-center">
                                                <span class="fs-8 fw-bold  opacity-75 d-block mb-1">Estimasi (Manual)</span>
                                                <span class="fs-6 fw-boldest text-success d-block" id="est_date_manual">{{ $dateManual->format('d M Y') }}</span>
                                                <span class="fs-9  opacity-50" id="est_months_manual">{{ $monthsNeededManual }} Bulan lagi</span>
                                            </div>
                                        </div>
                                    </div>

                                    <button class="btn btn-primary mt-auto" id="btn_save_simulation">
                                        <span class="indicator-label">Simpan Sebagai Target</span>
                                        <span class="indicator-progress">Please wait... 
                                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Savings History Section -->
        <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
            <div class="col-xl-12">
                <div class="card card-flush h-xl-100">
                    <div class="card-header pt-7">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold ">Wedding Savings History (Manual CRUD)</span>
                            <span class=" opacity-50 mt-1 fw-semibold fs-6">Catat setoran tabungan nikah Anda di sini secara khusus</span>
                        </h3>
                        <div class="card-toolbar">
                            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modal_add_savings">
                                <i class="ki-duotone ki-plus fs-2 text-white"></i>Tambah Setoran
                            </button>
                        </div>
                    </div>
                    <div class="card-body pt-6">
                        <div class="table-responsive">
                            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                <thead>
                                    <tr class="fw-bold  opacity-75 bg-light">
                                        <th class="ps-4 min-w-150px rounded-start">Tanggal</th>
                                        <th class="min-w-200px">Keterangan</th>
                                        <th class="min-w-150px">Nominal</th>
                                        <th class="min-w-100px text-end pe-4 rounded-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($savings as $saving)
                                    <tr>
                                        <td class="ps-4">
                                            <span class=" fw-bold d-block fs-6">{{ \Carbon\Carbon::parse($saving->date)->format('d M Y') }}</span>
                                        </td>
                                        <td>
                                            <span class=" fw-semibold d-block fs-6">{{ $saving->description ?: '-' }}</span>
                                        </td>
                                        <td>
                                            <span class="text-success fw-boldest d-block fs-6">Rp {{ number_format($saving->amount, 0, ',', '.') }}</span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm delete-savings-btn" data-id="{{ $saving->id }}">
                                                <i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center  opacity-50 py-10">Belum ada riwayat setoran manual.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Budget Breakdown Section -->
        <div class="row g-5 g-xl-10">
            <div class="col-xl-8">
                <div class="card card-flush h-xl-100">
                    <div class="card-header pt-7">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold ">Wedding Cost Breakdown (Real Data)</span>
                            <span class=" opacity-50 mt-1 fw-semibold fs-6">Data tabel ini sudah tersimpan di database</span>
                        </h3>
                        <div class="card-toolbar">
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal_add_item">
                                <i class="ki-duotone ki-plus fs-2 text-white"></i>Tambah Item
                            </button>
                        </div>
                    </div>
                    <div class="card-body pt-6">
                        <div class="table-responsive">
                            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                <thead>
                                    <tr class="fw-bold  opacity-75 bg-light">
                                        <th class="ps-4 min-w-200px rounded-start">Kategori / Item</th>
                                        <th class="min-w-100px">Porsi (%)</th>
                                        <th class="min-w-125px">Estimasi Biaya</th>
                                        <th class="min-w-150px">Status</th>
                                        <th class="min-w-100px text-end pe-4 rounded-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($items as $item)
                                    <tr>
                                        <td class="ps-4">
                                            <span class="text-gray-800 fw-bold d-block fs-6">{{ $item->name }}</span>
                                            @if($item->notes) <span class=" opacity-50 fs-7">{{ $item->notes }}</span> @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-light-secondary fw-bold">{{ round(($item->estimated_amount / ($targetAmount ?: 1)) * 100) }}%</span>
                                        </td>
                                        <td>
                                            <span class="text-gray-800 fw-bold d-block fs-6">Rp {{ number_format($item->estimated_amount, 0, ',', '.') }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $color = 'primary';
                                                if($item->status == 'paid') $color = 'success';
                                                if($item->status == 'cancelled') $color = 'danger';
                                            @endphp
                                            <span class="badge badge-light-{{ $color }} fs-7 fw-bold">{{ ucfirst($item->status) }}</span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm delete-item-btn" data-id="{{ $item->id }}">
                                                <i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center  opacity-50 py-10">Belum ada item rencana. Klik Tambah Item.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-4">
                <div class="card card-flush h-xl-100">
                    <div class="card-header pt-7">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold ">Tips Perencanaan</span>
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-8">
                            <span class="bullet bullet-vertical h-40px bg-success me-5"></span>
                            <div class="flex-grow-1">
                                <a href="#" class="text-gray-800 text-hover-primary fw-bold fs-6">Dana Darurat Wedding</a>
                                <span class=" opacity-75 fw-semibold d-block">Sisihkan 5-10% dari total budget untuk biaya tak terduga.</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-8">
                            <span class="bullet bullet-vertical h-40px bg-primary me-5"></span>
                            <div class="flex-grow-1">
                                <a href="#" class="text-gray-800 text-hover-primary fw-bold fs-6">Utamakan Venue & Catering</a>
                                <span class=" opacity-75 fw-semibold d-block">Biasanya memakan porsi 40-50% dari total pengeluaran.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Add Item -->
<div class="modal fade" id="modal_add_item" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="form_add_item" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Item Rencana</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-5">
                    <label class="form-label fw-bold">Nama Item / Kategori</label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: Catering 500 Pax" required />
                </div>
                <div class="mb-5">
                    <label class="form-label fw-bold">Estimasi Biaya (Rp)</label>
                    <input type="number" name="estimated_amount" class="form-control" placeholder="0" required />
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" id="btn_submit_item">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Add Savings -->
<div class="modal fade" id="modal_add_savings" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="form_add_savings" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Catat Setoran Tabungan Nikah</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-5">
                    <label class="form-label fw-bold">Tanggal</label>
                    <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required />
                </div>
                <div class="mb-5">
                    <label class="form-label fw-bold">Nominal (Rp)</label>
                    <input type="number" name="amount" class="form-control" placeholder="Contoh: 2000000" required />
                </div>
                <div class="mb-5">
                    <label class="form-label fw-bold">Keterangan</label>
                    <input type="text" name="description" class="form-control" placeholder="Contoh: Tabungan April 2024" />
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success" id="btn_submit_savings">Simpan Setoran</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputTarget = document.getElementById('input_target_amount');
        const inputTimeline = document.getElementById('input_timeline_range');
        const bniBalance = {{ $bniBalance }};
        const manualSaved = {{ $totalSavedManual }};

        function formatRupiah(amount) {
            return 'Rp ' + Math.ceil(amount).toLocaleString('id-ID');
        }

        function formatDate(date) {
            const months = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
            return date.getDate() + ' ' + months[date.getMonth()] + ' ' + date.getFullYear();
        }

        function updateSimulation() {
            const target = parseFloat(inputTarget.value) || 0;
            const years = parseInt(inputTimeline.value) || 1;
            const months = years * 12;

            const monthly = Math.ceil(target / months);
            
            // Update all monthly displays
            document.querySelectorAll('.simulation_result_monthly_display').forEach(el => {
                el.textContent = formatRupiah(monthly);
            });
            
            // Estimations Logic
            if (monthly > 0) {
                // BNI Scenario
                const remBNI = Math.max(target - bniBalance, 0);
                const monthsBNI = Math.ceil(remBNI / monthly);
                const dateBNI = new Date();
                dateBNI.setMonth(dateBNI.getMonth() + monthsBNI);
                
                document.getElementById('est_date_bni').textContent = formatDate(dateBNI);
                document.getElementById('est_months_bni').textContent = monthsBNI + ' Bulan lagi';

                // Manual Scenario
                const remManual = Math.max(target - manualSaved, 0);
                const monthsManual = Math.ceil(remManual / monthly);
                const dateManual = new Date();
                dateManual.setMonth(dateManual.getMonth() + monthsManual);
                
                document.getElementById('est_date_manual').textContent = formatDate(dateManual);
                document.getElementById('est_months_manual').textContent = monthsManual + ' Bulan lagi';
            }

            // Update Target Year and Timeline display
            const currentYear = new Date().getFullYear();
            const targetYear = currentYear + years;
            
            document.getElementById('display_target_year').textContent = targetYear;
            document.getElementById('display_timeline_years').textContent = years;
            document.getElementById('badge_timeline_years').textContent = years + ' Tahun';

            // Update Symbols dynamically
            const symbolGroup = document.getElementById('symbol_group_years');
            if (symbolGroup) {
                symbolGroup.innerHTML = '';
                const colors = ['primary', 'success', 'warning', 'danger'];
                for (let i = 1; i <= Math.min(years, 4); i++) {
                    const yearLabel = (currentYear + i).toString().slice(-2);
                    const color = colors[i - 1] || 'primary';
                    symbolGroup.innerHTML += `
                        <div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip" title="Year ${currentYear + i}">
                            <span class="symbol-label bg-light-${color} text-${color} fw-bold">${yearLabel}</span>
                        </div>
                    `;
                }
                if (years > 4) {
                    symbolGroup.innerHTML += `
                        <div class="symbol symbol-35px symbol-circle">
                            <span class="symbol-label bg-light-dark  fw-bold">+${years - 4}</span>
                        </div>
                    `;
                }
            }
        }

        // Initial from PHP
        inputTarget.value = {{ $plan->target_amount }};
        inputTimeline.value = {{ $plan->target_years }};

        inputTarget.addEventListener('input', updateSimulation);
        inputTimeline.addEventListener('input', updateSimulation);

        // Save Target functionality
        const btnSave = document.getElementById('btn_save_simulation');
        btnSave.addEventListener('click', function() {
            btnSave.setAttribute('data-kt-indicator', 'on');
            axios.post('{{ route('money-management.wedding-planner.store') }}', {
                target_amount: inputTarget.value,
                target_years: inputTimeline.value
            })
            .then(res => {
                Swal.fire({ text: res.data.success, icon: "success", buttonsStyling: false, confirmButtonText: "Ok!", customClass: { confirmButton: "btn btn-primary" } })
                .then(() => location.reload());
            })
            .finally(() => btnSave.removeAttribute('data-kt-indicator'));
        });

        // Add Savings functionality
        const formAddSavings = document.getElementById('form_add_savings');
        formAddSavings.addEventListener('submit', function(e) {
            e.preventDefault();
            const btnSubmit = document.getElementById('btn_submit_savings');
            btnSubmit.setAttribute('disabled', 'disabled');
            
            const formData = new FormData(formAddSavings);
            axios.post('{{ route('money-management.wedding-planner.savings.store') }}', {
                amount: formData.get('amount'),
                date: formData.get('date'),
                description: formData.get('description')
            })
            .then(res => location.reload())
            .catch(err => {
                alert(err.response.data.message || 'Error saving transaction');
                btnSubmit.removeAttribute('disabled');
            });
        });

        // Delete Savings functionality
        document.querySelectorAll('.delete-savings-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                Swal.fire({
                    title: "Hapus transaksi ini?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Ya, Hapus!",
                    customClass: { confirmButton: "btn btn-danger", cancelButton: "btn btn-light" }
                }).then((result) => {
                    if (result.isConfirmed) {
                        axios.delete('{{ url('money-management/wedding-planner/savings') }}/' + id)
                        .then(() => location.reload());
                    }
                });
            });
        });

        // Delete Item functionality (Existing)
        document.querySelectorAll('.delete-item-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                Swal.fire({ title: "Hapus item ini?", icon: "warning", showCancelButton: true, confirmButtonText: "Ya, Hapus!", customClass: { confirmButton: "btn btn-danger", cancelButton: "btn btn-light" } })
                .then((result) => {
                    if (result.isConfirmed) {
                        axios.delete('{{ url('money-management/wedding-planner/item') }}/' + id)
                        .then(() => location.reload());
                    }
                });
            });
        });
        
        // Add Item functionality (Existing)
        const formAddItem = document.getElementById('form_add_item');
        formAddItem.addEventListener('submit', function(e) {
            e.preventDefault();
            const btnSubmit = document.getElementById('btn_submit_item');
            btnSubmit.setAttribute('disabled', 'disabled');
            const formData = new FormData(formAddItem);
            axios.post('{{ route('money-management.wedding-planner.item.store') }}', {
                name: formData.get('name'),
                estimated_amount: formData.get('estimated_amount')
            })
            .then(res => location.reload());
        });
    });
</script>
@endpush

</x-default-layout>