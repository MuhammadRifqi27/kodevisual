<x-default-layout>
    @section('title', 'Budget Planning')

    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-xxl">
            <!--begin::Card-->
            <div class="card shadow-sm border-0" style="border-radius: 20px;">
                <div class="card-header border-0 pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-900 fs-3">Global Budget Strategy</span>
                        <span class="text-muted mt-1 fw-semibold fs-6">Manage all your travel allocations across missions</span>
                    </h3>
                </div>
                <div class="card-body">
                    <!--begin::Alert Budget Guide-->
                    <div class="alert alert-dismissible bg-light-info d-flex align-items-center p-5 mb-10 border border-info border-dashed" style="border-radius: 15px;">
                        <i class="ki-duotone ki-delivery-logistic fs-2hx text-info me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                        <div class="d-flex flex-column pe-0 pe-sm-10">
                            <h4 class="mb-1 text-info">Financial War Room: Global Strategy</h4>
                            <span>Manage and review all your travel allocations in one place.</span>
                        </div>
                        <button class="btn btn-info btn-sm ms-auto fw-bold text-white shadow-sm" data-bs-toggle="modal" data-bs-target="#kt_modal_budget_guide">
                            Tactical Guide
                        </button>
                    </div>
                    <!--end::Alert Budget Guide-->

                    <!--begin::Modal Budget Guide-->
                    <div class="modal fade" id="kt_modal_budget_guide" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered mw-500px">
                            <div class="modal-content border-0" style="border-radius: 20px;">
                                <div class="modal-header border-0 pb-0 shadow-sm px-10 py-5">
                                    <h2 class="fw-bold">Global Budgeting 🛡️</h2>
                                    <div class="btn btn-icon btn-sm btn-active-icon-info" data-bs-dismiss="modal">
                                        <i class="ki-duotone ki-cross fs-1"></i>
                                    </div>
                                </div>
                                <div class="modal-body py-10 px-10">
                                    <p class="fs-6 text-gray-700 mb-5">Halaman ini mengumpulkan semua alokasi anggaran dari berbagai proyek perjalanan aktif Anda.</p>
                                    <ul class="list-unstyled">
                                        <li class="d-flex align-items-center mb-5">
                                            <i class="ki-outline ki-abstract-26 fs-2 text-info me-3"></i>
                                            <span class="fs-7">Gunakan tabel ini untuk melihat total dana yang 'dikunci' per kategori.</span>
                                        </li>
                                        <li class="d-flex align-items-center mb-5">
                                            <i class="ki-outline ki-message-edit fs-2 text-info me-3"></i>
                                            <span class="fs-7">Klik nama proyek untuk mengedit alokasi langsung di pusat kendalinya.</span>
                                        </li>
                                    </ul>
                                </div>
                                <div class="modal-footer border-0 pt-0 flex-center">
                                    <button type="button" class="btn btn-info fw-bold text-white shadow-sm" data-bs-dismiss="modal">Copy that!</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Modal Budget Guide-->

                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th class="ps-4 min-w-200px rounded-start">Project / Mission</th>
                                    <th class="min-w-150px">Category</th>
                                    <th class="min-w-150px text-end">Allocation</th>
                                    <th class="min-w-150px text-end pe-4 rounded-end">Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($trips as $trip)
                                    @foreach($trip->budgets as $budget)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="d-flex justify-content-start flex-column">
                                                        <a href="{{ route('travel.trips.show', $trip->id) }}" class="text-gray-900 fw-bold text-hover-primary mb-1 fs-6">{{ $trip->title }}</a>
                                                        <span class="text-muted fw-semibold d-block fs-7">{{ $trip->destination }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-light-info fw-bold">{{ $budget->category }}</span>
                                            </td>
                                            <td class="text-end">
                                                <span class="text-gray-900 fw-bold fs-6">{{ $trip->currency }} {{ number_format($budget->amount, 0) }}</span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <span class="text-muted fs-7">{{ $budget->notes ?: '-' }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-default-layout>
