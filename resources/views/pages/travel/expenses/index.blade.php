<x-default-layout>
    @section('title', 'Expense Tracking')

    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-xxl">
            <!--begin::Card-->
            <div class="card shadow-sm border-0" style="border-radius: 20px;">
                <div class="card-header border-0 pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-900 fs-3">Real-time Expense Feed</span>
                        <span class="text-muted mt-1 fw-semibold fs-6">Every cent tracked across your global adventures</span>
                    </h3>
                </div>
                <div class="card-body">
                    <!--begin::Alert Expense Guide-->
                    <div class="alert alert-dismissible bg-light-danger d-flex align-items-center p-5 mb-10 border border-danger border-dashed" style="border-radius: 15px;">
                        <i class="ki-duotone ki-finance fs-2hx text-danger me-4"><span class="path1"></span><span class="path2"></span></i>
                        <div class="d-flex flex-column pe-0 pe-sm-10">
                            <h4 class="mb-1 text-danger">Intelligence Feed: Global Expenses</h4>
                            <span>Real-time tracking of every single cent spent across your missions.</span>
                        </div>
                        <button class="btn btn-danger btn-sm ms-auto fw-bold text-white shadow-sm" data-bs-toggle="modal" data-bs-target="#kt_modal_expense_guide">
                            Analysis Protocol
                        </button>
                    </div>
                    <!--end::Alert Expense Guide-->

                    <!--begin::Modal Expense Guide-->
                    <div class="modal fade" id="kt_modal_expense_guide" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered mw-500px">
                            <div class="modal-content border-0" style="border-radius: 20px;">
                                <div class="modal-header border-0 pb-0 shadow-sm px-10 py-5">
                                    <h2 class="fw-bold">Global Expense Feed 📉</h2>
                                    <div class="btn btn-icon btn-sm btn-active-icon-danger" data-bs-dismiss="modal">
                                        <i class="ki-duotone ki-cross fs-1"></i>
                                    </div>
                                </div>
                                <div class="modal-body py-10 px-10">
                                    <p class="fs-6 text-gray-700 mb-5">Halaman ini adalah log terpadu untuk semua transaksi pengeluaran dari seluruh proyek perjalanan Anda.</p>
                                    <ul class="list-unstyled">
                                        <li class="d-flex align-items-center mb-5">
                                            <i class="ki-outline ki-receipt-item fs-2 text-danger me-3"></i>
                                            <span class="fs-7">Lihat detail item pengeluaran, kategori, dan project asalnya dalam satu tampilan.</span>
                                        </li>
                                        <li class="d-flex align-items-center mb-5">
                                            <i class="ki-outline ki-eye fs-2 text-danger me-3"></i>
                                            <span class="fs-7">Klik nama proyek untuk melihat rekapitulasi budget vs realita di project tersebut.</span>
                                        </li>
                                    </ul>
                                </div>
                                <div class="modal-footer border-0 pt-0 flex-center">
                                    <button type="button" class="btn btn-danger fw-bold text-white shadow-sm" data-bs-dismiss="modal">Copy that!</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Modal Expense Guide-->

                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th class="ps-4 min-w-150px rounded-start">Date</th>
                                    <th class="min-w-200px">Description / Project</th>
                                    <th class="min-w-150px">Category</th>
                                    <th class="min-w-150px text-end pe-4 rounded-end">Amount Paid</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expenses as $expense)
                                    <tr>
                                        <td class="ps-4">
                                            <span class="text-gray-700 fw-bold">{{ $expense->date }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="text-gray-900 fw-bold fs-6">{{ $expense->description }}</span>
                                                <a href="{{ route('travel.trips.show', $expense->trip->id) }}" class="text-muted text-hover-primary fw-semibold fs-7">{{ $expense->trip->title }}</a>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-light-danger fw-bold">{{ $expense->category }}</span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <span class="text-danger fw-bold fs-6">- {{ $expense->trip->currency }} {{ number_format($expense->amount, 0) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-10">No expenses recorded yet. Be careful with those coins!</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-default-layout>
