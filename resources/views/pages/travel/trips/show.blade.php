<x-default-layout>
    @section('title', 'Project Plan: ' . $trip->title)

    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <!--begin::Content container-->
        <div id="kt_app_content_container" class="app-container container-xxl">
            <!--begin::Navbar-->
            <div class="card mb-6 mb-xl-9 shadow-sm" style="border-radius: 20px; overflow: hidden; border: none;">
                <div class="card-body pt-9 pb-0">
                    <!--begin::Details-->
                    <div class="d-flex flex-wrap flex-sm-nowrap mb-6">
                        <!--begin::Image-->
                        <div class="me-7 mb-4">
                            <div class="symbol symbol-100px symbol-circle position-relative">
                                <div class="symbol-label" style="background: url('{{ $trip->getCoverUrl(asset('assets/media/icon-travel/jog.JPG')) }}') center/cover;"></div>
                                <div class="position-absolute translate-middle bottom-0 start-100 mb-6 bg-success rounded-circle border border-4 border-body h-20px w-20px"></div>
                            </div>
                        </div>
                        <!--end::Image-->
                        <!--begin::Wrapper-->
                        <div class="flex-grow-1">
                            <!--begin::Head-->
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <!--begin::Details-->
                                <div class="d-flex flex-column">
                                    <!--begin::Status-->
                                    <div class="d-flex align-items-center mb-1">
                                        <a href="#" class="text-gray-800 text-hover-primary fs-2 fw-bold me-3">{{ $trip->title }}</a>
                                        @php
                                            $statusClass = [
                                                'ongoing' => 'success',
                                                'planned' => 'primary',
                                                'completed' => 'info',
                                                'cancelled' => 'danger'
                                            ][$trip->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge badge-light-{{ $statusClass }} fw-bold ms-2 fs-8 py-1 px-3">{{ ucfirst($trip->status) }}</span>
                                    </div>
                                    <!--end::Status-->
                                    <!--begin::Description-->
                                    <div class="d-flex flex-wrap fw-semibold mb-4 fs-6 text-gray-500">
                                        <span class="d-flex align-items-center me-5">
                                            <i class="ki-outline ki-geolocation fs-4 me-1 text-primary"></i>{{ $trip->destination }}
                                        </span>
                                        <span class="d-flex align-items-center me-5">
                                            <i class="ki-outline ki-calendar fs-4 me-1 text-primary"></i>{{ $trip->start_date }} - {{ $trip->end_date }}
                                        </span>
                                    </div>
                                    <!--end::Description-->
                                </div>
                                <!--end::Details-->
                                <!--begin::Actions-->
                                <div class="d-flex mb-4">
                                    <a href="{{ route('travel.trips.edit', $trip->id) }}" class="btn btn-sm btn-bg-light btn-active-color-primary me-3">Edit Details</a>
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add_itinerary">Add Activity</button>
                                </div>
                                <!--end::Actions-->
                            </div>
                            <!--end::Head-->
                            <!--begin::Info-->
                            <div class="d-flex flex-wrap justify-content-start">
                                <!--begin::Stats-->
                                <div class="d-flex flex-wrap">
                                    <!--begin::Stat-->
                                    <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="fs-4 fw-bold text-gray-900">{{ $trip->currency }} {{ number_format($trip->total_budget, 0) }}</div>
                                        </div>
                                        <div class="fw-semibold fs-6 text-gray-500">Total Budget</div>
                                    </div>
                                    <!--end::Stat-->
                                    @php
                                        $allocated = $trip->budgets->sum('amount');
                                        $spent = $trip->expenses->sum('amount');
                                        $remaining = $trip->total_budget - $spent;
                                        $unallocated = $trip->total_budget - $allocated;
                                        $percent = $trip->total_budget > 0 ? min(($spent / $trip->total_budget) * 100, 100) : 0;
                                    @endphp
                                    <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="fs-4 fw-bold text-primary">{{ $trip->currency }} {{ number_format($allocated, 0) }}</div>
                                        </div>
                                        <div class="fw-semibold fs-6 text-gray-500">Allocated</div>
                                    </div>
                                    <!--begin::Stat-->
                                    <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="fs-4 fw-bold text-danger">{{ $trip->currency }} {{ number_format($spent, 0) }}</div>
                                        </div>
                                        <div class="fw-semibold fs-6 text-gray-500">Actual Spent</div>
                                    </div>
                                    <!--end::Stat-->
                                    <!--begin::Stat-->
                                    <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="fs-4 fw-bold text-{{ $remaining < 0 ? 'danger' : 'success' }}">{{ $trip->currency }} {{ number_format($remaining, 0) }}</div>
                                        </div>
                                        <div class="fw-semibold fs-6 text-gray-500">Remaining Plan</div>
                                    </div>
                                    <!--end::Stat-->
                                </div>
                                <!--end::Stats-->
                            </div>
                            <!--end::Info-->
                        </div>
                        <!--end::Wrapper-->
                    </div>
                    <!--end::Details-->
                    <div class="separator mt-3"></div>
                    <!--begin::Navs-->
                <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold">
                    <!--begin::Nav item-->
                    <li class="nav-item mt-2">
                        <a class="nav-link text-active-primary ms-0 me-10 py-5 active" data-bs-toggle="tab" href="#kt_trip_overview">Overview</a>
                    </li>
                    <li class="nav-item mt-2">
                        <a class="nav-link text-active-primary ms-0 me-10 py-5" data-bs-toggle="tab" href="#kt_trip_itinerary">Itinerary</a>
                    </li>
                    <li class="nav-item mt-2">
                        <a class="nav-link text-active-primary ms-0 me-10 py-5" data-bs-toggle="tab" href="#kt_trip_budget">Budgets</a>
                    </li>
                    <li class="nav-item mt-2">
                        <a class="nav-link text-active-primary ms-0 me-10 py-5" data-bs-toggle="tab" href="#kt_trip_expenses">Expenses</a>
                    </li>
                </ul>
                <!--begin::Navs-->
            </div>
        </div>
        <!--end::Navbar-->

        <!--begin::Alert Control Guide-->
        <div class="alert alert-dismissible bg-light-info d-flex align-items-center p-5 mb-10 border border-info border-dashed" style="border-radius: 15px;">
            <i class="ki-duotone ki-delivery-logistic fs-2qx text-info me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span><span class="path7"></span></i>
            <div class="d-flex justify-content-between align-items-center w-100 pe-0 pe-sm-10">
                <div class="d-flex flex-column">
                    <h4 class="mb-1 text-info">Mission Command: Managing your Journey</h4>
                    <span>Master the three pillars: Itinerary planning, Budget allocation, and Expense tracking.</span>
                </div>
                <button class="btn btn-info btn-sm fw-bold shadow-sm text-white" data-bs-toggle="modal" data-bs-target="#kt_modal_command_center">
                    Command Protocol
                </button>
            </div>
            <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto" data-bs-dismiss="alert">
                <i class="ki-duotone ki-cross fs-1 text-info"><span class="path1"></span><span class="path2"></span></i>
            </button>
        </div>
        <!--end::Alert Control Guide-->

        <!--begin::Modal Command Guide-->
        <div class="modal fade" id="kt_modal_command_center" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered mw-700px">
                <div class="modal-content border-0" style="border-radius: 20px;">
                    <div class="modal-header border-0 pb-0 shadow-sm px-10 py-5">
                        <h2 class="fw-bold">Mission Control Manual 📟</h2>
                        <div class="btn btn-icon btn-sm btn-active-icon-info" data-bs-dismiss="modal">
                            <i class="ki-duotone ki-cross fs-1"></i>
                        </div>
                    </div>
                    <div class="modal-body py-10 px-10">
                        <div class="row g-7">
                            <div class="col-md-4">
                                <div class="card h-100 bg-light-primary border border-primary border-dashed p-5 text-center" style="border-radius: 15px;">
                                    <i class="ki-outline ki-calendar-8 fs-3x text-primary mb-3"></i>
                                    <h5 class="fw-bold text-gray-800">1. Itinerary</h5>
                                    <p class="fs-7 text-muted mb-0">Rencanakan agenda harian. Sistem akan menghitung Day Number otomatis.</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card h-100 bg-light-success border border-success border-dashed p-5 text-center" style="border-radius: 15px;">
                                    <i class="ki-outline ki-wallet fs-3x text-success mb-3"></i>
                                    <h5 class="fw-bold text-gray-800">2. Budgeting</h5>
                                    <p class="fs-7 text-muted mb-0">Kunci porsi anggaran di tiap kategori agar tidak boros.</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card h-100 bg-light-danger border border-danger border-dashed p-5 text-center" style="border-radius: 15px;">
                                    <i class="ki-outline ki-receipt-item fs-3x text-danger mb-3"></i>
                                    <h5 class="fw-bold text-gray-800">3. Expenses</h5>
                                    <p class="fs-7 text-muted mb-0">Catat setiap struk belanja agar saldo riil tetap terkendali.</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-8 mt-10 rounded shadow-sm bg-light-info text-dark" style="border-left: 5px solid #009ef7;">
                            <h4 class="fw-bold mb-3">The Logic 🧠</h4>
                            <p class="fs-6 mb-0">
                                <b>Remaining Plan</b> adalah sisa uang dari <b>Total Budget</b> awal dikurangi <b>Actual Spent</b> riil.<br>
                                Sedangkan alokasi di tab <b>Budgets</b> hanyalah "pembatas" agar Anda tidak melebihi rencana awal di kategori tertentu.
                            </p>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 flex-center">
                        <button type="button" class="btn btn-info fw-bold min-w-150px text-white" data-bs-dismiss="modal">Copy that!</button>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Modal Command Guide-->
            <!--begin::Tab Content-->
            <div class="tab-content" id="myTabContent">
                <!--begin::Overview Tab-->
                <div class="tab-pane fade show active" id="kt_trip_overview" role="tabpanel">
                    <div class="row g-6 g-xl-9">
                        <!--begin::Column-->
                        <div class="col-lg-12">
                            <div class="card card-flush shadow-sm" style="border-radius: 20px;">
                                <div class="card-header pt-7">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bold text-gray-900 fs-3">Quick Timeline</span>
                                        <span class="text-muted mt-1 fw-semibold fs-6">Activities for this expedition</span>
                                    </h3>
                                </div>
                                <div class="card-body pt-5">
                                    <div class="timeline-label">
                                        @forelse($trip->itineraries->sortBy('date') as $item)
                                            <div class="timeline-item">
                                                <div class="timeline-label fw-bold text-gray-800 fs-6">{{ $item->time ? substr($item->time, 0, 5) : 'Day '.$item->day_number }}</div>
                                                <div class="timeline-badge">
                                                    <i class="fa fa-genderless text-{{ $item->cost_estimate > 0 ? 'warning' : 'primary' }} fs-1"></i>
                                                </div>
                                                <div class="timeline-content fw-semibold text-gray-800 ps-3">
                                                    {{ $item->activity }}
                                                    <span class="text-muted fw-semibold d-block fs-7">{{ $item->location }}</span>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-10">
                                                <i class="ki-outline ki-clipboard fs-3x text-gray-300 mb-5"></i>
                                                <p class="text-muted">No activities recorded. Your board is waiting for plans!</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!--begin::Itinerary Tab-->
                <div class="tab-pane fade" id="kt_trip_itinerary" role="tabpanel">
                    @foreach($trip->itineraries->groupBy('day_number')->sortKeys() as $day => $items)
                        <div class="card card-flush shadow-sm mb-6" style="border-radius: 20px;">
                            <div class="card-header pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold text-gray-900 fs-4">DAY {{ $day }}</span>
                                    <span class="text-muted mt-1 fw-semibold fs-7"><i class="ki-outline ki-calendar fs-7"></i> {{ $items->first()->date }}</span>
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                        <thead>
                                            <tr class="fw-bold text-muted">
                                                <th class="min-w-80px">Time</th>
                                                <th class="min-w-200px">Activity & Venue</th>
                                                <th class="min-w-100px">Estimations</th>
                                                <th class="min-w-100px text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($items->sortBy('time') as $activity)
                                                <tr>
                                                    <td><span class="badge badge-light fw-bold">{{ $activity->time ? substr($activity->time, 0, 5) : '--:--' }}</span></td>
                                                    <td>
                                                        <div class="d-flex flex-column">
                                                            <span class="text-gray-900 fw-bold fs-6">{{ $activity->activity }}</span>
                                                            <span class="text-muted fw-semibold fs-7"><i class="ki-outline ki-geolocation fs-8"></i> {{ $activity->location }}</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="text-gray-900 fw-bold">{{ $trip->currency }} {{ number_format($activity->cost_estimate, 0) }}</span>
                                                    </td>
                                                    <td class="text-end">
                                                        <form action="{{ route('travel.itineraries.destroy', $activity->id) }}" method="POST" class="delete-form">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="button" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm swal-delete-btn" data-title="Remove activity: {{ $activity->activity }}?">
                                                                <i class="ki-outline ki-trash fs-3"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!--begin::Budget Tab-->
                <div class="tab-pane fade" id="kt_trip_budget" role="tabpanel">
                    <div class="row g-6 g-xl-9">
                        <div class="col-lg-12">
                            <div class="card card-flush shadow-sm" style="border-radius: 20px;">
                                <div class="card-header pt-7">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bold text-gray-900 fs-3">Strategic Budget Allocations</span>
                                        <span class="text-muted mt-1 fw-semibold fs-6">Pecah total budget Anda ke kategori tertentu untuk memonitor rencana pengeluaran.</span>
                                    </h3>
                                    <div class="card-toolbar">
                                        <button class="btn btn-sm btn-light-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add_budget">
                                            <i class="ki-outline ki-plus fs-2"></i> Add Allocation
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table align-middle table-row-dashed fs-6 gy-5">
                                            <thead>
                                                <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                                    <th class="min-w-200px">Category</th>
                                                    <th class="text-end min-w-100px">Amount Allocated</th>
                                                </tr>
                                            </thead>
                                            <tbody class="text-gray-600 fw-semibold">
                                                @forelse($trip->budgets as $budget)
                                                    <tr>
                                                        <td class="text-gray-900 fw-bold fs-6">
                                                            <div class="d-flex align-items-center">
                                                                <span class="bullet bullet-vertical h-40px bg-primary me-5"></span>
                                                                {{ $budget->category }}
                                                            </div>
                                                        </td>
                                                        <td class="text-end">
                                                            <div class="d-flex align-items-center justify-content-end">
                                                                <span class="text-gray-900 fw-bold fs-6 me-3">{{ $trip->currency }} {{ number_format($budget->amount, 0) }}</span>
                                                                <form action="{{ route('travel.budgets.destroy', $budget->id) }}" method="POST" class="delete-form">
                                                                    @csrf @method('DELETE')
                                                                    <button type="button" class="btn btn-icon btn-active-color-danger btn-sm swal-delete-btn" data-title="Delete budget allocation for {{ $budget->category }}?">
                                                                        <i class="ki-outline ki-trash fs-4"></i>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="2" class="text-center py-10">
                                                            <span class="text-muted">No allocations yet. Strategize your budget now!</span>
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!--begin::Expenses Tab-->
                <div class="tab-pane fade" id="kt_trip_expenses" role="tabpanel">   
                    <div class="row g-6 g-xl-9">
                        <div class="col-lg-12">
                            <div class="card card-flush shadow-sm" style="border-radius: 20px;">
                                <div class="card-header pt-7">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bold text-gray-900 fs-3">Real-time Expense Feed</span>
                                        <span class="text-muted mt-1 fw-semibold fs-6">Catat pengeluaran riil setiap harinya untuk mengontrol sisa saldo perjalanan.</span>
                                    </h3>
                                    <div class="card-toolbar">
                                        <button class="btn btn-sm btn-light-success" data-bs-toggle="modal" data-bs-target="#kt_modal_add_expense">
                                            <i class="ki-outline ki-plus fs-2"></i> New Record
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body pt-5">
                                    @forelse($trip->expenses->sortByDesc('date') as $expense)
                                        <div class="d-flex flex-stack mb-5 p-4 bg-light-{{ $expense->amount > 0 ? 'danger' : 'success' }} rounded border border-dashed border-gray-300">
                                            <div class="d-flex align-items-center">
                                                <div class="symbol symbol-40px me-4">
                                                    <span class="symbol-label bg-white"><i class="ki-outline ki-finance fs-2 text-primary"></i></span>
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-900 fw-bold fs-6">{{ $expense->description }}</span>
                                                    <span class="text-muted fw-semibold fs-7">{{ $expense->date }} • {{ $expense->category }}</span>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <div class="text-danger fw-bold fs-6 me-3">- {{ $trip->currency }} {{ number_format($expense->amount, 0) }}</div>
                                                <form action="{{ route('travel.expenses.destroy', $expense->id) }}" method="POST" class="delete-form">
                                                    @csrf @method('DELETE')
                                                    <button type="button" class="btn btn-icon btn-active-color-danger btn-sm swal-delete-btn" data-title="Delete expense record: {{ $expense->description }}?">
                                                        <i class="ki-outline ki-trash fs-4"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center py-10">
                                            <span class="text-muted">No expenses recorded yet. Keep track of your coins!</span>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Tab Content-->
        </div>
    </div>

    @include('pages.travel.trips.partials.modals')

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            flatpickr(".kt_flatpickr_datetime", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                time_24hr: true,
                allowInput: true,
                defaultHour: 0,
                defaultMinute: 0,
            });

            flatpickr(".kt_flatpickr_date", {
                dateFormat: "Y-m-d",
                allowInput: true,
            });

            // Handle session flash messages with SweetAlert
            @if(session('success'))
                Swal.fire({
                    text: "{{ session('success') }}",
                    icon: "success",
                    buttonsStyling: false,
                    confirmButtonText: "Great!",
                    customClass: { confirmButton: "btn btn-primary" }
                });
            @endif

            @if(session('warning'))
                Swal.fire({
                    text: "{{ session('warning') }}",
                    icon: "warning",
                    buttonsStyling: false,
                    confirmButtonText: "Understood",
                    customClass: { confirmButton: "btn btn-warning" }
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    text: "{{ session('error') }}",
                    icon: "error",
                    buttonsStyling: false,
                    confirmButtonText: "Change Plan",
                    customClass: { confirmButton: "btn btn-danger" }
                });
            @endif

            @if($errors->any())
                Swal.fire({
                    html: "{!! implode('<br>', $errors->all()) !!}",
                    icon: "error",
                    buttonsStyling: false,
                    confirmButtonText: "Got it",
                    customClass: { confirmButton: "btn btn-danger" }
                });
            @endif

            // Handle Deletion with SweetAlert2
            $(document).on('click', '.swal-delete-btn', function(e) {
                e.preventDefault();
                let form = $(this).closest('form');
                let title = $(this).data('title') || 'Are you sure you want to delete this?';

                Swal.fire({
                    text: title,
                    icon: "warning",
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: "Yes, delete!",
                    cancelButtonText: "No, cancel",
                    customClass: {
                        confirmButton: "btn fw-bold btn-danger",
                        cancelButton: "btn fw-bold btn-active-light-primary"
                    }
                }).then(function (result) {
                    if (result.value) {
                        form.submit();
                    }
                });
            });

        });
    </script>
    @endpush
</x-default-layout>
