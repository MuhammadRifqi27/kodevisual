<x-default-layout>
    @section('title', 'Travel Dashboard')

    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-xxl">
            <!--begin::Hero Section-->
            <div class="card border-0 mb-5 mb-xl-10 shadow-sm" style="background: linear-gradient(135deg, #009ef7 0%, #00d2ff 100%); border-radius: 20px; overflow: hidden;">
                <div class="card-body p-12 position-relative">
                    <div class="position-absolute top-0 end-0 opacity-10 mt-n10 me-n10">
                        <i class="ki-outline ki-map fs-5x rotate-n-15"></i>
                    </div>
                    <div class="d-flex flex-stack flex-wrap position-relative">
                        <div class="me-5">
                            <h1 class="text-white fw-bold fs-2qx mb-3">Voyage Planner Dashboard 🌍</h1>
                            <p class="text-white opacity-75 fs-4 mb-0">Crafting unforgettable journeys, one destination at a time.</p>
                        </div>
                        <div class="d-flex my-2">
                            <a href="{{ route('travel.trips.create') }}" class="btn btn-dark fw-bold px-8 py-4 shadow-lg text-white" style="border-radius: 12px;">
                                <i class="ki-outline ki-plus-circle fs-2 me-2"></i> Plan a New Journey
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Hero Section-->

            <!--begin::Alert Guide-->
            <div class="alert alert-dismissible bg-light-primary d-flex align-items-center p-5 mb-10 border border-primary border-dashed" style="border-radius: 15px;">
                <i class="ki-duotone ki-shield-tick fs-2hx text-primary me-4"><span class="path1"></span><span class="path2"></span></i>
                <div class="d-flex flex-column pe-0 pe-sm-10">
                    <h4 class="mb-1 text-primary">Mission Intel: How to use Travel Planner</h4>
                    <span>Master the art of journey planning and expense tracking in 3 simple steps.</span>
                </div>
                <button class="btn btn-primary btn-sm ms-auto fw-bold" data-bs-toggle="modal" data-bs-target="#kt_modal_guide">
                    Operation Guide
                </button>
                <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto" data-bs-dismiss="alert">
                    <i class="ki-duotone ki-cross fs-1 text-primary"><span class="path1"></span><span class="path2"></span></i>
                </button>
            </div>
            <!--end::Alert Guide-->

            <!--begin::Modal Guide-->
            <div class="modal fade" id="kt_modal_guide" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered mw-650px">
                    <div class="modal-content border-0" style="border-radius: 20px;">
                        <div class="modal-header border-0 pb-0">
                            <h2 class="fw-bold">Voyage Planner Protocol 🛰️</h2>
                            <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                                <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                            </div>
                        </div>
                        <div class="modal-body py-10 px-lg-17">
                            <div class="d-flex flex-column mb-10">
                                <div class="d-flex align-items-center mb-5">
                                    <div class="symbol symbol-40px me-5">
                                        <span class="symbol-label bg-light-success text-success fw-bold">1</span>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-900 fw-bold fs-6">Deploy Expedition</span>
                                        <span class="text-muted fw-semibold fs-7">Klik 'Plan a New Journey' untuk mendaftarkan destinasi dan budget maksimal Anda.</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mb-5">
                                    <div class="symbol symbol-40px me-5">
                                        <span class="symbol-label bg-light-primary text-primary fw-bold">2</span>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-900 fw-bold fs-6">Strategize & Allocate</span>
                                        <span class="text-muted fw-semibold fs-7">Masuk ke detail trip, susun aktivitas (Itinerary) dan pecah budget Anda ke kategori tertentu.</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-40px me-5">
                                        <span class="symbol-label bg-light-danger text-danger fw-bold">3</span>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-900 fw-bold fs-6">Track Logistics</span>
                                        <span class="text-muted fw-semibold fs-7">Catat setiap pengeluaran riil di lapangan untuk memantau sisa uang Anda secara real-time.</span>
                                    </div>
                                </div>
                            </div>
                            <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-6">
                                <i class="ki-outline ki-information-5 fs-2tx text-warning me-4"></i>
                                <div class="d-flex flex-stack flex-grow-1">
                                    <div class="fw-semibold">
                                        <h4 class="text-gray-900 fw-bold">Pro Tip!</h4>
                                        <div class="fs-6 text-gray-700">Gunakan tab 'Budgets' dan 'Expenses' di detail trip untuk perbandingan akurat antara rencana dan realita keuangan Anda.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0 flex-center">
                            <button type="button" class="btn btn-primary fw-bold min-w-150px" data-bs-dismiss="modal">Got it, Commander!</button>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Modal Guide-->

            <!--begin::Stats-->
            <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
                <div class="col-md-4">
                    <div class="card h-md-100 card-flush shadow-sm" style="border-radius: 20px;">
                        <div class="card-header pt-7">
                            <div class="card-title d-flex flex-column">
                                <div class="symbol symbol-45px mb-5">
                                    <span class="symbol-label bg-light-primary">
                                        <i class="ki-outline ki-airplane fs-2x text-primary"></i>
                                    </span>
                                </div>
                                <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ $trips->count() }}</span>
                                <span class="text-gray-500 pt-1 fw-semibold fs-6">Adventure Projects</span>
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column justify-content-end pe-0 pt-0">
                            <span class="fs-6 fw-semibold text-gray-500">Your lifelong journey summary</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-md-100 card-flush shadow-sm" style="border-radius: 20px;">
                        <div class="card-header pt-7">
                            <div class="card-title d-flex flex-column">
                                <div class="symbol symbol-45px mb-5">
                                    <span class="symbol-label bg-light-danger">
                                        <i class="ki-outline ki-wallet fs-2x text-danger"></i>
                                    </span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="fs-4 fw-semibold text-gray-500 me-2">IDR</span>
                                    <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ number_format($totalExpenses, 0) }}</span>
                                </div>
                                <span class="text-gray-500 pt-1 fw-semibold fs-6">Global Investment in Memories</span>
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column justify-content-end pe-0 pt-0">
                            <span class="fs-6 fw-semibold text-gray-500">Total spent across travel history</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-md-100 card-flush shadow-sm" style="border-radius: 20px;">
                        <div class="card-header pt-7">
                            <div class="card-title d-flex flex-column">
                                <div class="symbol symbol-45px mb-5">
                                    <span class="symbol-label bg-light-success">
                                        <i class="ki-outline ki-calendar-tick fs-2x text-success"></i>
                                    </span>
                                </div>
                                <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ $upcomingTripsCount }}</span>
                                <span class="text-gray-500 pt-1 fw-semibold fs-6">Upcoming Expeditions</span>
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column justify-content-end pe-0 pt-0">
                            <span class="fs-6 fw-semibold text-gray-500">Exciting plans on your horizon</span>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Stats-->

            <!--begin::Projects Section-->
            <div class="card card-flush shadow-sm" style="border-radius: 20px;">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-900 fs-3">Recent Projects</span>
                        <span class="text-gray-500 mt-1 fw-semibold fs-6">Manage and explore your latest itineraries</span>
                    </h3>
                    <div class="card-toolbar">
                        <a href="{{ route('travel.trips.index') }}" class="btn btn-sm btn-light">View All Projects</a>
                    </div>
                </div>
                <div class="card-body pt-6">
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th class="ps-4 min-w-300px rounded-start">Project / Destination</th>
                                    <th class="min-w-150px">Timeline</th>
                                    <th class="min-w-150px">Financials</th>
                                    <th class="min-w-100px">Status</th>
                                    <th class="min-w-100px text-end rounded-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($trips->take(5) as $trip)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="symbol symbol-50px me-5">
                                                    <div class="symbol-label" style="background: url('{{ $trip->getCoverUrl(asset('assets/media/icon-travel/jog.JPG')) }}') center/cover;"></div>
                                                </div>
                                                <div class="d-flex justify-content-start flex-column">
                                                    <a href="{{ route('travel.trips.show', $trip->id) }}" class="text-gray-900 fw-bold text-hover-primary mb-1 fs-6">{{ $trip->title }}</a>
                                                    <span class="text-muted fw-semibold text-muted d-block fs-7"><i class="ki-outline ki-geolocation fs-8"></i> {{ $trip->destination }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column w-100 me-2">
                                                <div class="d-flex flex-stack mb-2">
                                                    <span class="text-muted me-2 fs-7 fw-bold">{{ $trip->start_date }}</span>
                                                </div>
                                                @php
                                                    $now = now();
                                                    $start = \Carbon\Carbon::parse($trip->start_date);
                                                    $end = \Carbon\Carbon::parse($trip->end_date);
                                                    $totalDays = $start->diffInDays($end) ?: 1;
                                                    $elapsed = $start->diffInDays($now);
                                                    $prog = $now < $start ? 0 : ($now > $end ? 100 : ($elapsed / $totalDays) * 100);
                                                @endphp
                                                <div class="progress h-6px w-100">
                                                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $prog }}%" aria-valuenow="{{ $prog }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-gray-900 fw-bold d-block fs-6">{{ $trip->currency }} {{ number_format($trip->total_budget, 0) }}</span>
                                            <span class="text-muted fw-semibold fs-7">Allocation</span>
                                        </td>
                                        <td>
                                            @php
                                                $badgeClass = [
                                                    'planned' => 'light-primary',
                                                    'ongoing' => 'light-success',
                                                    'completed' => 'light-info',
                                                    'cancelled' => 'light-danger',
                                                    'draft' => 'light-warning'
                                                ][$trip->status] ?? 'light-secondary';
                                            @endphp
                                            <span class="badge badge-{{ $badgeClass }} fw-bold px-4 py-3">{{ ucfirst($trip->status) }}</span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('travel.trips.show', $trip->id) }}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1">
                                                <i class="ki-outline ki-eye fs-3"></i>
                                            </a>
                                            <a href="{{ route('travel.trips.edit', $trip->id) }}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1">
                                                <i class="ki-outline ki-pencil fs-3"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">
                                            <div class="text-center py-10">
                                                <p class="text-muted fs-5">No recent activity. Ready to plan your next escape?</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!--end::Projects Section-->
        </div>
    </div>
</x-default-layout>
