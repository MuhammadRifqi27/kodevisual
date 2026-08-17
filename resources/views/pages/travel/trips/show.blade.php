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
                                    <a href="{{ route('travel.trips.export_full', $trip->id) }}" class="btn btn-sm btn-light-success me-3">
                                        <i class="ki-outline ki-file-down fs-4 me-1"></i>Export Full Trip (Excel)
                                    </a>
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
                    <li class="nav-item mt-2">
                        <a class="nav-link text-active-primary ms-0 me-10 py-5" data-bs-toggle="tab" href="#kt_trip_packing">Packing List</a>
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
                    <div class="d-flex justify-content-between align-items-center mb-6">
                        <h4 class="text-gray-900 fw-bold m-0">Daily Schedule</h4>
                        <a href="{{ route('travel.trips.export_itinerary', $trip->id) }}" class="btn btn-sm btn-light-success">
                            <i class="ki-outline ki-file-down fs-4 me-1"></i>Export Itinerary (Excel)
                        </a>
                    </div>

                    @foreach($trip->itineraries->groupBy('day_number')->sortKeys() as $day => $items)
                        <div class="card card-flush shadow-sm mb-6" style="border-radius: 20px;">
                            <div class="card-header pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold text-gray-900 fs-4">DAY {{ $day }}</span>
                                    <span class="text-muted mt-1 fw-semibold fs-7"><i class="ki-outline ki-calendar fs-7"></i> {{ Carbon\Carbon::parse($items->first()->date)->locale('id')->translatedFormat('l, d F Y') }}</span>
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
                                                            @if($activity->location)
                                                                <span class="text-muted fw-semibold fs-7"><i class="ki-outline ki-geolocation fs-8"></i> {{ $activity->location }}</span>
                                                            @endif
                                                            @if($activity->description)
                                                                <span class="text-gray-600 fs-7 mt-1" style="white-space: pre-line; font-style: italic;">{{ $activity->description }}</span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex flex-column">
                                                            <span class="text-gray-900 fw-bold">{{ $trip->currency }} {{ number_format($activity->cost_estimate, 0) }}</span>
                                                            <span class="text-muted fs-7">
                                                                @if($activity->cost_type === 'per_person')
                                                                    ({{ $trip->currency }} {{ number_format($activity->cost_per_person, 0) }}/person)
                                                                @else
                                                                    ({{ $trip->currency }} {{ number_format($activity->cost_per_person, 0) }}/person)
                                                                @endif
                                                            </span>
                                                            @php $activityPersons = $activity->number_of_persons ?? $trip->number_of_persons; @endphp
                                                            <span class="badge badge-light-{{ $activityPersons != $trip->number_of_persons ? 'danger' : 'secondary' }} fs-8 mt-1">
                                                                <i class="ki-outline ki-people fs-8 me-1"></i>{{ $activityPersons }} pax
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td class="text-end">
                                                        <div class="d-flex justify-content-end gap-2">
                                                            <button type="button" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm edit-itinerary-btn"
                                                                data-id="{{ $activity->id }}"
                                                                data-datetime="{{ $activity->date }} {{ $activity->time ? substr($activity->time, 0, 5) : '00:00' }}"
                                                                data-activity="{{ $activity->activity }}"
                                                                data-description="{{ $activity->description }}"
                                                                data-location="{{ $activity->location }}"
                                                                data-cost-type="{{ $activity->cost_type }}"
                                                                data-cost-estimate="{{ $activity->cost_estimate }}"
                                                                data-cost-per-person="{{ $activity->cost_per_person }}"
                                                                data-number-of-persons="{{ $activity->number_of_persons ?? $trip->number_of_persons }}"
                                                                data-update-url="{{ route('travel.itineraries.update', $activity->id) }}">
                                                                <i class="ki-outline ki-pencil fs-3"></i>
                                                            </button>
                                                            <form action="{{ route('travel.itineraries.destroy', $activity->id) }}" method="POST" class="delete-form m-0">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="button" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm swal-delete-btn" data-title="Remove activity: {{ $activity->activity }}?">
                                                                        <i class="ki-outline ki-trash fs-3"></i>
                                                                    </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <!-- NEW Price Per Person Summary Table Card -->
                    <div class="card card-flush shadow-sm mt-8" style="border-radius: 20px;">
                        <div class="card-header pt-7">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-900 fs-3">Itinerary Price Per Person Summary</span>
                                <span class="text-muted mt-1 fw-semibold fs-6">Consolidated budget analysis for {{ $trip->number_of_persons }} person(s)</span>
                            </h3>
                            <div class="card-toolbar">
                                <span class="badge badge-light-primary fs-7 fw-bold px-3 py-2">
                                    <i class="ki-outline ki-profile-user fs-6 text-primary me-1"></i> {{ $trip->number_of_persons }} Pax
                                </span>
                            </div>
                        </div>
                        <div class="card-body pt-5">
                            <div class="table-responsive">
                                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                    <thead>
                                        <tr class="fw-bold text-muted text-uppercase fs-7">
                                            <th class="min-w-100px">Day / Date</th>
                                            <th class="min-w-200px">Activity & Venue</th>
                                            <th class="min-w-150px text-end">Total Cost</th>
                                            <th class="min-w-150px text-end">Price Per Person</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php 
                                            $totalItineraryCost = 0; 
                                            $totalItineraryCostPerPerson = 0;
                                        @endphp
                                        @forelse($trip->itineraries->sortBy(['day_number', 'time']) as $activity)
                                            @php 
                                                $totalItineraryCost += $activity->cost_estimate; 
                                                $totalItineraryCostPerPerson += $activity->cost_per_person;
                                            @endphp
                                            <tr>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span class="text-gray-900 fw-bold fs-6">Day {{ $activity->day_number }}</span>
                                                        <span class="text-muted fw-semibold fs-7">{{ $activity->date }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span class="text-gray-900 fw-bold fs-6">{{ $activity->activity }}</span>
                                                        <span class="text-muted fw-semibold fs-7 mb-1">
                                                            @if($activity->location)
                                                                <i class="ki-outline ki-geolocation fs-8"></i> {{ $activity->location }}
                                                            @else
                                                                <span class="text-gray-400">No Location</span>
                                                            @endif
                                                        </span>
                                                        @if($activity->description)
                                                            <span class="text-gray-600 fs-7" style="white-space: pre-line; font-style: italic;">{{ $activity->description }}</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="text-end">
                                                    <span class="text-gray-900 fw-bold">{{ $trip->currency }} {{ number_format($activity->cost_estimate, 0) }}</span>
                                                </td>
                                                <td class="text-end">
                                                    <span class="text-gray-900 fw-bold">{{ $trip->currency }} {{ number_format($activity->cost_per_person, 0) }}</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-10 text-muted">
                                                    No activities recorded to summarize.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    @if($trip->itineraries->count() > 0)
                                        <tfoot>
                                            <tr class="fw-bold fs-6 text-gray-900 bg-light-primary rounded">
                                                <td colspan="2" class="ps-5 py-4">Total Cost Summary</td>
                                                <td class="text-end py-4 text-primary fw-bolder">{{ $trip->currency }} {{ number_format($totalItineraryCost, 0) }}</td>
                                                <td class="text-end pe-5 py-4 text-success fw-bolder">{{ $trip->currency }} {{ number_format($totalItineraryCostPerPerson, 0) }}</td>
                                            </tr>
                                        </tfoot>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>
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
                                                    <th class="min-w-200px">Notes</th>
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
                                                        <td class="text-gray-900 fw-bold fs-6">
                                                            <div class="d-flex align-items-center">
                                                                {{ $budget->notes }}
                                                            </div>
                                                        </td>
                                                        <td class="text-end">
                                                            <div class="d-flex align-items-center justify-content-end">
                                                                <span class="text-gray-900 fw-bold fs-6 me-3">{{ $trip->currency }} {{ number_format($budget->amount, 0) }}</span>
                                                                <button type="button" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm edit-budget-btn me-2"
                                                                    data-id="{{ $budget->id }}"
                                                                    data-category="{{ $budget->category }}"
                                                                    data-amount="{{ $budget->amount }}"
                                                                    data-notes="{{ $budget->notes }}"
                                                                    data-update-url="{{ route('travel.budgets.update', $budget->id) }}">
                                                                    <i class="ki-outline ki-pencil fs-3"></i>
                                                                </button>
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

                <!--begin::Packing List Tab-->
                <div class="tab-pane fade" id="kt_trip_packing" role="tabpanel">
                    <div class="row g-6 g-xl-9">
                        <div class="col-lg-12">
                            <div class="card card-flush shadow-sm" style="border-radius: 20px;">
                                <div class="card-header pt-7">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bold text-gray-900 fs-3">Packing Checklist</span>
                                        <span class="text-muted mt-1 fw-semibold fs-6">Daftar barang bawaan yang perlu disiapkan sebelum berangkat.</span>
                                    </h3>
                                    <div class="card-toolbar">
                                        <button class="btn btn-sm btn-light-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add_packing_item">
                                            <i class="ki-outline ki-plus fs-2"></i> Add Item
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @php
                                        $packingTotal = $trip->packingItems->count();
                                        $packingDone = $trip->packingItems->where('is_packed', true)->count();
                                        $packingPercent = $packingTotal > 0 ? round(($packingDone / $packingTotal) * 100) : 0;
                                    @endphp
                                    @if($packingTotal > 0)
                                        <div class="d-flex align-items-center mb-6">
                                            <div class="flex-grow-1 me-4">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="fw-semibold text-gray-600 fs-7">Progress Packing</span>
                                                    <span class="fw-bold text-gray-800 fs-7">{{ $packingDone }} / {{ $packingTotal }} ({{ $packingPercent }}%)</span>
                                                </div>
                                                <div class="progress h-6px">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $packingPercent }}%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="table-responsive">
                                        <table class="table align-middle table-row-dashed fs-6 gy-5">
                                            <thead>
                                                <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                                    <th class="w-25px">Packed</th>
                                                    <th class="min-w-150px">Category</th>
                                                    <th class="min-w-200px">Item</th>
                                                    <th class="text-center min-w-75px">Qty</th>
                                                    <th class="min-w-200px">Notes</th>
                                                    <th class="text-end min-w-100px">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody class="text-gray-600 fw-semibold">
                                                @forelse($trip->packingItems->sortBy('category') as $item)
                                                    <tr>
                                                        <td>
                                                            <div class="form-check form-check-custom form-check-solid">
                                                                <input class="form-check-input packing-item-checkbox" type="checkbox"
                                                                    data-toggle-url="{{ route('travel.packing-items.toggle', $item->id) }}"
                                                                    {{ $item->is_packed ? 'checked' : '' }} />
                                                            </div>
                                                        </td>
                                                        <td class="{{ $item->is_packed ? 'text-muted text-decoration-line-through' : 'text-gray-900 fw-bold' }} fs-6 packing-item-category">
                                                            {{ $item->category ?: '-' }}
                                                        </td>
                                                        <td class="{{ $item->is_packed ? 'text-muted text-decoration-line-through' : 'text-gray-900 fw-bold' }} fs-6 packing-item-name">
                                                            {{ $item->item_name }}
                                                        </td>
                                                        <td class="text-center packing-item-qty">{{ $item->quantity }}</td>
                                                        <td class="packing-item-notes">{{ $item->notes }}</td>
                                                        <td class="text-end">
                                                            <div class="d-flex justify-content-end gap-2">
                                                                <button type="button" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm edit-packing-btn"
                                                                    data-id="{{ $item->id }}"
                                                                    data-category="{{ $item->category }}"
                                                                    data-item-name="{{ $item->item_name }}"
                                                                    data-quantity="{{ $item->quantity }}"
                                                                    data-notes="{{ $item->notes }}"
                                                                    data-update-url="{{ route('travel.packing-items.update', $item->id) }}">
                                                                    <i class="ki-outline ki-pencil fs-3"></i>
                                                                </button>
                                                                <form action="{{ route('travel.packing-items.destroy', $item->id) }}" method="POST" class="delete-form m-0">
                                                                    @csrf @method('DELETE')
                                                                    <button type="button" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm swal-delete-btn" data-title="Remove packing item: {{ $item->item_name }}?">
                                                                        <i class="ki-outline ki-trash fs-3"></i>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center py-10">
                                                            <span class="text-muted">Belum ada barang di checklist. Mulai siapkan bawaanmu!</span>
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

            // Pricing logic for Add & Edit Itinerary Modals
            function initPricingLogic(modalId) {
                let modal = document.getElementById(modalId);
                if (!modal) return;

                let defaultPersons = parseInt(modal.getAttribute('data-persons')) || 1;
                let costTypeSelect = modal.querySelector('select[name="cost_type"]');
                let costEstimateInput = modal.querySelector('input[name="cost_estimate"]');
                let costPerPersonInput = modal.querySelector('input[name="cost_per_person"]');
                let personsInput = modal.querySelector('input[name="number_of_persons"]');

                function getPersons() {
                    let val = parseInt(personsInput ? personsInput.value : defaultPersons);
                    return (val && val > 0) ? val : 1;
                }

                function updateFields() {
                    let costType = costTypeSelect.value;
                    let persons = getPersons();
                    if (costType === 'total') {
                        costEstimateInput.removeAttribute('readonly');
                        costEstimateInput.classList.remove('bg-light');
                        costPerPersonInput.setAttribute('readonly', 'readonly');
                        costPerPersonInput.classList.add('bg-light');

                        let total = parseFloat(costEstimateInput.value) || 0;
                        costPerPersonInput.value = (total / persons).toFixed(0);
                    } else {
                        costPerPersonInput.removeAttribute('readonly');
                        costPerPersonInput.classList.remove('bg-light');
                        costEstimateInput.setAttribute('readonly', 'readonly');
                        costEstimateInput.classList.add('bg-light');

                        let perPerson = parseFloat(costPerPersonInput.value) || 0;
                        costEstimateInput.value = (perPerson * persons).toFixed(0);
                    }
                }

                costTypeSelect.addEventListener('change', updateFields);

                costEstimateInput.addEventListener('input', function() {
                    if (costTypeSelect.value === 'total') {
                        let total = parseFloat(costEstimateInput.value) || 0;
                        costPerPersonInput.value = (total / getPersons()).toFixed(0);
                    }
                });

                costPerPersonInput.addEventListener('input', function() {
                    if (costTypeSelect.value === 'per_person') {
                        let perPerson = parseFloat(costPerPersonInput.value) || 0;
                        costEstimateInput.value = (perPerson * getPersons()).toFixed(0);
                    }
                });

                if (personsInput) {
                    personsInput.addEventListener('input', updateFields);
                }

                // Expose updateFields function to window for manual triggers
                if (modalId === 'kt_modal_edit_itinerary') {
                    window.updateEditPricing = updateFields;
                }

                // Initial run
                updateFields();
            }

            initPricingLogic('kt_modal_add_itinerary');
            initPricingLogic('kt_modal_edit_itinerary');

            // Handle Edit Itinerary Button Click
            $(document).on('click', '.edit-itinerary-btn', function() {
                let btn = $(this);
                let modal = $('#kt_modal_edit_itinerary');
                let form = $('#edit_itinerary_form');
                
                // Set form action
                form.attr('action', btn.data('update-url'));
                
                // Fill in inputs
                modal.find('#edit_datetime').val(btn.data('datetime'));
                // Trigger flatpickr if initialized
                let fp = document.querySelector("#edit_datetime")._flatpickr;
                if (fp) {
                    fp.setDate(btn.data('datetime'));
                }

                modal.find('#edit_activity').val(btn.data('activity'));
                modal.find('#edit_description').val(btn.data('description'));
                modal.find('#edit_location').val(btn.data('location'));
                modal.find('#edit_cost_type').val(btn.data('cost-type'));
                modal.find('#edit_number_of_persons').val(btn.data('number-of-persons'));
                modal.find('#edit_cost_estimate').val(btn.data('cost-estimate'));
                modal.find('#edit_cost_per_person').val(btn.data('cost-per-person'));

                // Trigger update fields after population
                if (typeof window.updateEditPricing === 'function') {
                    window.updateEditPricing();
                }
                
                // Show modal
                modal.modal('show');
            });

            // Handle Edit Budget Button Click
            $(document).on('click', '.edit-budget-btn', function() {
                let btn = $(this);
                let modal = $('#kt_modal_edit_budget');
                let form = $('#edit_budget_form');

                // Set form action
                form.attr('action', btn.data('update-url'));

                // Fill in inputs
                modal.find('#edit_budget_category').val(btn.data('category')).trigger('change');
                modal.find('#edit_budget_amount').val(btn.data('amount'));
                modal.find('#edit_budget_notes').val(btn.data('notes'));

                // Show modal
                modal.modal('show');
            });

            // Handle Edit Packing Item Button Click
            $(document).on('click', '.edit-packing-btn', function() {
                let btn = $(this);
                let modal = $('#kt_modal_edit_packing_item');
                let form = $('#edit_packing_item_form');

                // Set form action
                form.attr('action', btn.data('update-url'));

                // Fill in inputs
                modal.find('#edit_packing_category').val(btn.data('category')).trigger('change');
                modal.find('#edit_packing_item_name').val(btn.data('item-name'));
                modal.find('#edit_packing_quantity').val(btn.data('quantity'));
                modal.find('#edit_packing_notes').val(btn.data('notes'));

                // Show modal
                modal.modal('show');
            });

            // Handle Packing Item Checkbox Toggle
            $(document).on('change', '.packing-item-checkbox', function() {
                let checkbox = $(this);
                let row = checkbox.closest('tr');

                $.ajax({
                    url: checkbox.data('toggle-url'),
                    type: 'PATCH',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(res) {
                        let packed = res.is_packed;
                        row.find('.packing-item-category, .packing-item-name').toggleClass('text-muted text-decoration-line-through', packed);
                        row.find('.packing-item-category, .packing-item-name').toggleClass('text-gray-900 fw-bold', !packed);
                    },
                    error: function() {
                        checkbox.prop('checked', !checkbox.prop('checked'));
                        Swal.fire({
                            text: "Gagal memperbarui status barang. Coba lagi.",
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "OK",
                            customClass: { confirmButton: "btn btn-danger" }
                        });
                    }
                });
            });

        });
    </script>
    @endpush
</x-default-layout>
