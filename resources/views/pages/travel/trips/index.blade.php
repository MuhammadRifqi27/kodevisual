<x-default-layout>
    @section('title', 'Expedition Projects')

    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-xxl">
            <!--begin::Toolbar-->
            <div class="d-flex flex-stack mb-9">
                <div class="d-flex flex-column">
                    <h1 class="text-gray-900 fw-bold my-1 fs-1">My Expeditions</h1>
                    <ul class="breadcrumb breadcrumb-dot fw-semibold fs-base my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('travel.dashboard') }}" class="text-muted text-hover-primary">Home</a>
                        </li>
                        <li class="breadcrumb-item text-gray-900">Expeditions</li>
                    </ul>
                </div>
                <div class="d-flex align-items-center gap-2 gap-lg-3">
                    <a href="{{ route('travel.trips.create') }}" class="btn btn-primary fw-bold">
                        <i class="ki-outline ki-plus-circle fs-2 me-1"></i> New Project
                    </a>
                </div>
            </div>
            <!--end::Toolbar-->

            <!--begin::Alert Index Guide-->
            <div class="alert alert-dismissible bg-light-primary d-flex align-items-center p-5 mb-10 border border-primary border-dashed" style="border-radius: 15px;">
                <i class="ki-duotone ki-map fs-2hx text-primary me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                <div class="d-flex flex-column pe-0 pe-sm-10">
                    <h4 class="mb-1 text-primary">Expedition Archives: Manage your Quests</h4>
                    <span>View, edit, or launch new travel projects from your central command.</span>
                </div>
                <button class="btn btn-primary btn-sm ms-auto fw-bold" data-bs-toggle="modal" data-bs-target="#kt_modal_index_guide">
                    Archives Protocol
                </button>
                <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto" data-bs-dismiss="alert">
                    <i class="ki-duotone ki-cross fs-1 text-primary"><span class="path1"></span><span class="path2"></span></i>
                </button>
            </div>
            <!--end::Alert Index Guide-->

            <!--begin::Modal Index Guide-->
            <div class="modal fade" id="kt_modal_index_guide" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered mw-600px">
                    <div class="modal-content border-0" style="border-radius: 20px;">
                        <div class="modal-header border-0 pb-0 shadow-sm px-10 py-5">
                            <h2 class="fw-bold">Archive Management 📚</h2>
                            <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                                <i class="ki-duotone ki-cross fs-1"></i>
                            </div>
                        </div>
                        <div class="modal-body py-10 px-10">
                            <ul class="list-unstyled">
                                <li class="d-flex align-items-center mb-7">
                                    <i class="ki-outline ki-plus-square fs-2qx text-success me-4"></i>
                                    <div>
                                        <span class="text-gray-800 fw-bold fs-6 d-block">Create New Project</span>
                                        <span class="text-muted fs-7">Gunakan tombol 'New Project' untuk mendaftarkan petualangan masa depan.</span>
                                    </div>
                                </li>
                                <li class="d-flex align-items-center mb-7">
                                    <i class="ki-outline ki-eye fs-2qx text-primary me-4"></i>
                                    <div>
                                        <span class="text-gray-800 fw-bold fs-6 d-block">Manage Journey</span>
                                        <span class="text-muted fs-7">Klik 'Manage' pada kartu trip untuk masuk ke ruang kendali itinerary dan budget.</span>
                                    </div>
                                </li>
                                <li class="d-flex align-items-center mb-7">
                                    <i class="ki-outline ki-pencil fs-2qx text-warning me-4"></i>
                                    <div>
                                        <span class="text-gray-800 fw-bold fs-6 d-block">Edit Project</span>
                                        <span class="text-muted fs-7">Gunakan ikon pensil untuk mengedit detail project.</span>
                                    </div>
                                </li>
                                <li class="d-flex align-items-center">
                                    <i class="ki-outline ki-trash fs-2qx text-danger me-4"></i>
                                    <div>
                                        <span class="text-gray-800 fw-bold fs-6 d-block">Delete Project</span>
                                        <span class="text-muted fs-7">Gunakan ikon tempat sampah untuk menghapus project.</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="modal-footer border-0 pt-0 flex-center">
                            <button type="button" class="btn btn-primary fw-bold min-w-150px" data-bs-dismiss="modal">Understood!</button>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Modal Index Guide-->

            <!--begin::Row-->
            <div class="row g-6 g-xl-9">
                @forelse($trips as $trip)
                    <div class="col-md-6 col-xl-4">
                        <!--begin::Card-->
                        <div class="card border-0 shadow-sm h-100" style="border-radius: 20px;">
                            <div class="card-body p-9">
                                <div class="d-flex flex-stack mb-5">
                                    <div class="symbol symbol-70px symbol-circle">
                                        <div class="symbol-label" style="background: url('{{ $trip->getCoverUrl(asset('assets/media/icon-travel/jog.JPG')) }}') center/cover;"></div>
                                    </div>
                                    @php
                                        $statusClass = [
                                            'ongoing' => 'success',
                                            'planned' => 'primary',
                                            'completed' => 'info',
                                            'cancelled' => 'danger'
                                        ][$trip->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge badge-light-{{ $statusClass }} fw-bold px-4 py-3">{{ ucfirst($trip->status) }}</span>
                                </div>

                                <div class="mb-5">
                                    <a href="{{ route('travel.trips.show', $trip->id) }}" class="fs-2 fw-bold text-gray-900 text-hover-primary">{{ $trip->title }}</a>
                                    <div class="fw-semibold text-gray-500 mt-1"><i class="ki-outline ki-geolocation fs-5 me-1"></i> {{ $trip->destination }}</div>
                                </div>

                                <div class="d-flex flex-stack flex-wrap mb-5">
                                    <div class="d-flex align-items-center me-3 mb-3">
                                        <i class="ki-outline ki-calendar fs-2 text-gray-400 me-2"></i>
                                        <div class="fw-bold text-gray-700">{{ $trip->start_date }}</div>
                                    </div>
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="ki-outline ki-wallet fs-2 text-gray-400 me-2"></i>
                                        <div class="fw-bold text-gray-700">{{ $trip->currency }} {{ number_format($trip->total_budget, 0) }}</div>
                                    </div>
                                </div>

                                @php
                                    $spent = $trip->expenses->sum('amount');
                                    $percent = $trip->total_budget > 0 ? min(($spent / $trip->total_budget) * 100, 100) : 0;
                                @endphp
                                <div class="d-flex align-items-center flex-column mt-3">
                                    <div class="d-flex justify-content-between w-100 mt-auto mb-2">
                                        <span class="fw-bold fs-6 text-gray-500">Budget Progress</span>
                                        <span class="fw-bold fs-6">{{ number_format($percent, 0) }}%</span>
                                    </div>
                                    <div class="h-8px mx-3 w-100 bg-light-{{ $percent > 90 ? 'danger' : 'primary' }} rounded">
                                        <div class="bg-{{ $percent > 90 ? 'danger' : 'primary' }} rounded h-8px" role="progressbar" style="width: {{ $percent }}%;" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>

                                <div class="separator separator-dashed border-gray-300 my-8"></div>

                                <div class="d-flex flex-stack">
                                    <div class="symbol-group symbol-hover">
                                        <div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip" title="Itinerary Items">
                                            <span class="symbol-label bg-light-info text-info fw-bold">{{ $trip->itineraries->count() }}</span>
                                        </div>
                                        <div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip" title="Expenses Tracked">
                                            <span class="symbol-label bg-light-warning text-warning fw-bold">{{ $trip->expenses->count() }}</span>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('travel.trips.show', $trip->id) }}" class="btn btn-sm btn-light-primary fw-bold">Manage</a>
                                        <a href="{{ route('travel.trips.edit', $trip->id) }}" class="btn btn-sm btn-icon btn-bg-light btn-active-color-warning" data-bs-toggle="tooltip" title="Edit Trip">
                                            <i class="ki-outline ki-pencil fs-2"></i>
                                        </a>
                                        <form action="{{ route('travel.trips.destroy', $trip->id) }}" method="POST" class="delete-form">
                                            @csrf @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-icon btn-bg-light btn-active-color-danger swal-delete-btn" data-title="Archive expedition: {{ $trip->title }}?" data-bs-toggle="tooltip" title="Delete Trip">
                                                <i class="ki-outline ki-trash fs-2"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Card-->
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                            <div class="card-body p-20 text-center">
                                <img src="{{ asset('assets/media/illustrations/sigma-1/17.png') }}" class="h-200px mb-10" alt="" />
                                <h3 class="fs-2x fw-bold text-gray-900 mb-5">No expeditions found</h3>
                                <p class="text-gray-500 fs-4 mb-10">The world is a book and those who do not travel read only one page.</p>
                                <a href="{{ route('travel.trips.create') }}" class="btn btn-primary px-8 py-4 fw-bold">Start Your First Chapter</a>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
            <!--end::Row-->
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Handle session flash messages with SweetAlert
            @if(session('success'))
                Swal.fire({
                    text: "{{ session('success') }}",
                    icon: "success",
                    buttonsStyling: false,
                    confirmButtonText: "Ok!",
                    customClass: { confirmButton: "btn btn-primary" }
                });
            @endif

            @if(session('warning'))
                Swal.fire({
                    text: "{{ session('warning') }}",
                    icon: "warning",
                    buttonsStyling: false,
                    confirmButtonText: "Ok!",
                    customClass: { confirmButton: "btn btn-warning" }
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    text: "{{ session('error') }}",
                    icon: "error",
                    buttonsStyling: false,
                    confirmButtonText: "Ok!",
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
                let title = $(this).data('title') || 'Are you sure?';

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
