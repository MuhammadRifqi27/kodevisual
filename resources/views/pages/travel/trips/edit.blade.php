<x-default-layout>
    @section('title', 'Edit Expedition: ' . $trip->title)

    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-xxl">
            <!--begin::Card-->
            <div class="card shadow-sm border-0" style="border-radius: 20px;">
                <!--begin::Card Header-->
                <div class="card-header border-0 pt-10">
                    <div class="card-title d-flex flex-column">
                        <div class="d-flex align-items-center mb-2">
                            <span class="symbol symbol-45px me-3">
                                <span class="symbol-label bg-light-warning">
                                    <i class="ki-outline ki-pencil fs-2x text-warning"></i>
                                </span>
                            </span>
                            <h1 class="fw-bold text-gray-900 m-0">Edit Expedition Project</h1>
                        </div>
                        <span class="text-muted fw-semibold fs-6">Update your journey details and project parameters.</span>
                    </div>
                </div>
                <!--end::Card Header-->

                <!--begin::Card Body-->
                <div class="card-body py-10">
                    <form action="{{ route('travel.trips.update', $trip->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!--begin::Cover Image Upload-->
                        <div class="row g-9 mb-8">
                            <div class="col-12">
                                <label class="fs-6 fw-semibold mb-3 d-block">Trip Cover Image <span class="text-muted fs-7">(leave empty to keep current)</span></label>
                                <div class="d-flex align-items-center">
                                    @php
                                        $currentCover = $trip->getCoverUrl(asset('assets/media/icon-travel/jog.JPG'));
                                    @endphp
                                    <div class="image-input image-input-outline" data-kt-image-input="true" style="background-image: url('{{ $currentCover }}')">
                                        <div class="image-input-wrapper w-125px h-125px" style="background-image: url('{{ $currentCover }}')"></div>
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
                                        <div class="fw-semibold text-gray-600 fs-6">Update the cover photo for your expedition.</div>
                                        <div class="text-muted fs-7 mt-1">PNG, JPG, GIF, WEBP up to 2MB</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Cover Image Upload-->

                        <!--begin::Input group-->
                        <div class="row g-9 mb-8">
                            <div class="col-md-6 fv-row">
                                <label class="required fs-6 fw-semibold mb-2">Expedition Title</label>
                                <div class="position-relative d-flex align-items-center">
                                    <i class="ki-outline ki-note-2 fs-3 position-absolute ms-4 text-gray-500"></i>
                                    <input type="text" class="form-control form-control-solid ps-12" name="title" value="{{ $trip->title }}" required />
                                </div>
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="required fs-6 fw-semibold mb-2">Status</label>
                                <select class="form-select form-select-solid" name="status" data-control="select2" data-placeholder="Select status" data-minimum-results-for-search="0">
                                    <option value="draft" {{ $trip->status == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="planned" {{ $trip->status == 'planned' ? 'selected' : '' }}>Planned</option>
                                    <option value="ongoing" {{ $trip->status == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                                    <option value="completed" {{ $trip->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ $trip->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                        </div>
                        <!--end::Input group-->

                        <!--begin::Input group-->
                        <div class="row g-9 mb-8">
                            <div class="col-md-12 fv-row">
                                <label class="required fs-6 fw-semibold mb-2">Destination City / Country</label>
                                <div class="position-relative d-flex align-items-center">
                                    <i class="ki-outline ki-geolocation fs-3 position-absolute ms-4 text-gray-500"></i>
                                    <input type="text" class="form-control form-control-solid ps-12" name="destination" value="{{ $trip->destination }}" required />
                                </div>
                            </div>
                        </div>
                        <!--end::Input group-->

                        <!--begin::Input group-->
                        <div class="row g-9 mb-8">
                            <div class="col-md-6 fv-row">
                                <label class="required fs-6 fw-semibold mb-2">Start Date</label>
                                <div class="position-relative d-flex align-items-center">
                                    <i class="ki-outline ki-calendar-8 fs-3 position-absolute ms-4 text-gray-500"></i>
                                    <input type="text" class="form-control form-control-solid ps-12 kt_flatpickr_date" name="start_date" value="{{ $trip->start_date }}" required />
                                </div>
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="required fs-6 fw-semibold mb-2">Return Date</label>
                                <div class="position-relative d-flex align-items-center">
                                    <i class="ki-outline ki-calendar-tick fs-3 position-absolute ms-4 text-gray-500"></i>
                                    <input type="text" class="form-control form-control-solid ps-12 kt_flatpickr_date" name="end_date" value="{{ $trip->end_date }}" required />
                                </div>
                            </div>
                        </div>
                        <!--end::Input group-->

                        <!--begin::Input group-->
                        <div class="row g-9 mb-10">
                            <div class="col-md-6 fv-row">
                                <label class="required fs-6 fw-semibold mb-2">Financial Allocation</label>
                                <div class="input-group input-group-solid">
                                    <span class="input-group-text"><i class="ki-outline ki-wallet fs-3"></i></span>
                                    <input type="number" class="form-control" name="total_budget" value="{{ $trip->total_budget }}" required />
                                </div>
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="required fs-6 fw-semibold mb-2">Currency</label>
                                <select class="form-select form-select-solid" name="currency" data-control="select2" data-placeholder="Select currency" data-minimum-results-for-search="0">
                                    <option value="IDR" {{ $trip->currency == 'IDR' ? 'selected' : '' }}>IDR - Indonesian Rupiah</option>
                                    <option value="USD" {{ $trip->currency == 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                                    <option value="JPY" {{ $trip->currency == 'JPY' ? 'selected' : '' }}>JPY - Japanese Yen</option>
                                    <option value="SGD" {{ $trip->currency == 'SGD' ? 'selected' : '' }}>SGD - Singapore Dollar</option>
                                </select>
                            </div>
                        </div>
                        <!--end::Input group-->

                        <!--begin::Actions-->
                        <div class="text-center pt-5">
                            <a href="{{ route('travel.trips.index') }}" class="btn btn-light me-3">Cancel</a>
                            <button type="submit" class="btn btn-warning px-10">
                                <span class="indicator-label">Update Expedition</span>
                            </button>
                        </div>
                        <!--end::Actions-->
                    </form>
                </div>
                <!--end::Card Body-->
            </div>
            <!--end::Card-->
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
