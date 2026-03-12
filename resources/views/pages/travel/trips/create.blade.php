<x-default-layout>
    @section('title', 'Setup New Expedition')

    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-xxl">
            <!--begin::Card-->
            <div class="card shadow-sm border-0" style="border-radius: 20px;">
                <!--begin::Card Header-->
                <div class="card-header border-0 pt-10">
                    <div class="card-title d-flex flex-column">
                        <div class="d-flex align-items-center mb-2">
                            <span class="symbol symbol-45px me-3">
                                <span class="symbol-label bg-light-primary">
                                    <i class="ki-outline ki-map fs-2x text-primary"></i>
                                </span>
                            </span>
                            <h1 class="fw-bold text-gray-900 m-0">Setup New Expedition</h1>
                        </div>
                        <span class="text-muted fw-semibold fs-6">Initialize your journey project and set your goals.</span>
                    </div>
                </div>
                <!--end::Card Header-->

                <!--begin::Card Body-->
                <div class="card-body py-10">
                    <form action="{{ route('travel.trips.store') }}" method="POST" id="kt_travel_trip_create_form" enctype="multipart/form-data">
                        @csrf
                        
                        <!--begin::Alert Setup Guide-->
                        <div class="alert alert-dismissible bg-light-primary d-flex align-items-center p-5 mb-10 border border-primary border-dashed" style="border-radius: 15px;">
                            <i class="ki-duotone ki-design-frame fs-2hx text-primary me-4"><span class="path1"></span><span class="path2"></span></i>
                            <div class="d-flex flex-column pe-0 pe-sm-10">
                                <h4 class="mb-1 text-primary">Pre-Flight Briefing: Setting Up</h4>
                                <span>Learn how to properly initialize your travel project for better tracking.</span>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm ms-auto fw-bold" data-bs-toggle="modal" data-bs-target="#kt_modal_setup_guide">
                                Setup Guide
                            </button>
                        </div>
                        <!--end::Alert Setup Guide-->

                        <!--begin::Modal Setup Guide-->
                        <div class="modal fade" id="kt_modal_setup_guide" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered mw-500px">
                                <div class="modal-content border-0" style="border-radius: 20px;">
                                    <div class="modal-header border-0 pb-0 shadow-sm px-10 py-5">
                                        <h2 class="fw-bold">Expedition Setup 🚀</h2>
                                        <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                                            <i class="ki-duotone ki-cross fs-1"></i>
                                        </div>
                                    </div>
                                    <div class="modal-body py-10 px-10">
                                        <div class="mb-5">
                                            <h5 class="fw-bold text-gray-800 mb-3">1. Destinasi & Judul</h5>
                                            <p class="fs-7 text-muted">Berikan nama project yang unik (misal: "Japan Sakura Tour 2024") agar mudah dibedakan di dashboard.</p>
                                        </div>
                                        <div class="mb-5">
                                            <h5 class="fw-bold text-gray-800 mb-3">2. Rentang Waktu</h5>
                                            <p class="fs-7 text-muted">Pastikan tanggal mulai dan berakhir akurat. Sistem itinerary akan membatasi aktivitas Anda di dalam rentang waktu ini.</p>
                                        </div>
                                        <div>
                                            <h5 class="fw-bold text-gray-800 mb-3">3. Total Budget</h5>
                                            <p class="fs-7 text-muted">Masukkan estimasi dana maksimal. Angka ini akan menjadi batas atas (ceiling) saat Anda memecah budget ke kategori-kategori nantinya.</p>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0 pt-0 flex-center">
                                        <button type="button" class="btn btn-primary fw-bold min-w-150px" data-bs-dismiss="modal">Launch Ready!</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Modal Setup Guide-->

                        <!--begin::Cover Image Upload-->
                        <div class="row g-9 mb-8">
                            <div class="col-12">
                                <label class="fs-6 fw-semibold mb-3 d-block">Trip Cover Image <span class="text-muted fs-7">(optional)</span></label>
                                <div class="d-flex align-items-center">
                                    <div class="image-input image-input-outline image-input-empty" data-kt-image-input="true" style="background-image: url('{{ asset('assets/media/icon-travel/jog.JPG') }}')">
                                        <div class="image-input-wrapper w-125px h-125px" style="background-image: url('{{ asset('assets/media/icon-travel/jog.JPG') }}')"></div>
                                        <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Change cover photo">
                                            <i class="ki-outline ki-pencil fs-7"></i>
                                            <input type="file" name="cover_image" accept=".png, .jpg, .jpeg, .gif, .webp" />
                                            <input type="hidden" name="cover_image_remove" />
                                        </label>
                                        <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="Cancel">
                                            <i class="ki-outline ki-cross fs-2"></i>
                                        </span>
                                        <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="Remove">
                                            <i class="ki-outline ki-trash fs-2"></i>
                                        </span>
                                    </div>
                                    <div class="ms-6">
                                        <div class="fw-semibold text-gray-600 fs-6">Set a unique cover for your expedition.</div>
                                        <div class="text-muted fs-7 mt-1">PNG, JPG, GIF, WEBP up to 2MB</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Cover Image Upload-->

                        <!--begin::Input group-->
                        <div class="row g-9 mb-8">
                            <!--begin::Col-->
                            <div class="col-md-6 fv-row">
                                <label class="required fs-6 fw-semibold mb-2">Expedition Title</label>
                                <div class="position-relative d-flex align-items-center">
                                    <i class="ki-outline ki-note-2 fs-3 position-absolute ms-4 text-gray-500"></i>
                                    <input type="text" class="form-control form-control-solid ps-12" placeholder="e.g. Winter in Switzerland" name="title" required />
                                </div>
                            </div>
                            <!--end::Col-->
                            <!--begin::Col-->
                            <div class="col-md-6 fv-row">
                                <label class="required fs-6 fw-semibold mb-2">Destination City / Country</label>
                                <div class="position-relative d-flex align-items-center">
                                    <i class="ki-outline ki-geolocation fs-3 position-absolute ms-4 text-gray-500"></i>
                                    <input type="text" class="form-control form-control-solid ps-12" placeholder="e.g. Zurich, Switzerland" name="destination" required />
                                </div>
                            </div>
                            <!--end::Col-->
                        </div>
                        <!--end::Input group-->

                        <!--begin::Input group-->
                        <div class="row g-9 mb-8">
                            <!--begin::Col-->
                            <div class="col-md-6 fv-row">
                                <label class="required fs-6 fw-semibold mb-2">Start Date</label>
                                <div class="position-relative d-flex align-items-center">
                                    <i class="ki-outline ki-calendar-8 fs-3 position-absolute ms-4 text-gray-500"></i>
                                    <input type="text" class="form-control form-control-solid ps-12 kt_flatpickr_date" name="start_date" placeholder="Select date" required />
                                </div>
                            </div>
                            <!--end::Col-->
                            <!--begin::Col-->
                            <div class="col-md-6 fv-row">
                                <label class="required fs-6 fw-semibold mb-2">Estimated Return Date</label>
                                <div class="position-relative d-flex align-items-center">
                                    <i class="ki-outline ki-calendar-tick fs-3 position-absolute ms-4 text-gray-500"></i>
                                    <input type="text" class="form-control form-control-solid ps-12 kt_flatpickr_date" name="end_date" placeholder="Select date" required />
                                </div>
                            </div>
                            <!--end::Col-->
                        </div>
                        <!--end::Input group-->

                        <!--begin::Input group-->
                        <div class="row g-9 mb-10">
                            <!--begin::Col-->
                            <div class="col-md-6 fv-row">
                                <label class="required fs-6 fw-semibold mb-2">Financial Allocation (Budget)</label>
                                <div class="input-group input-group-solid">
                                    <span class="input-group-text"><i class="ki-outline ki-wallet fs-3"></i></span>
                                    <input type="number" class="form-control" placeholder="0" name="total_budget" required />
                                </div>
                            </div>
                            <!--end::Col-->
                            <!--begin::Col-->
                            <div class="col-md-6 fv-row">
                                <label class="required fs-6 fw-semibold mb-2">Primary Currency</label>
                                <select class="form-select form-select-solid" name="currency" data-control="select2" data-placeholder="Select currency" data-minimum-results-for-search="0">
                                    <option value="IDR" selected>IDR - Indonesian Rupiah</option>
                                    <option value="USD">USD - US Dollar</option>
                                    <option value="JPY">JPY - Japanese Yen</option>
                                    <option value="SGD">SGD - Singapore Dollar</option>
                                    <option value="EUR">EUR - Euro</option>
                                </select>
                            </div>
                            <!--end::Col-->
                        </div>
                        <!--end::Input group-->

                        <!--begin::Actions-->
                        <div class="text-center pt-5">
                            <a href="{{ route('travel.trips.index') }}" class="btn btn-light me-3">Discard</a>
                            <button type="submit" class="btn btn-primary px-10">
                                <span class="indicator-label">Launch Project</span>
                                <span class="indicator-progress">Please wait... 
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>
                        <!--end::Actions-->
                    </form>
                </div>
                <!--end::Card Body-->
            </div>
            <!--end::Card-->

            <!--begin::Illustration-->
            <div class="text-center mt-10">
                <img src="{{ asset('assets/media/illustrations/sigma-1/2.png') }}" class="h-150px" alt="" />
            </div>
            <!--end::Illustration-->
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.kt_flatpickr_date').forEach(function(el) {
                flatpickr(el, {
                    dateFormat: "Y-m-d",
                });
            });
        });
    </script>
    @endpush
</x-default-layout>
