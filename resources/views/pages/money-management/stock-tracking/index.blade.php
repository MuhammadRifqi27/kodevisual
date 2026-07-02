<x-default-layout>
    @section('title')
        Stocks Journey Tracking
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('money-management.stock-tracking') }}
    @endsection

    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        <!-- Summary Card -->
        <div class="col-md-4">
            <div class="card card-flush h-md-100 shadow-sm border-0 bg-gradient-primary text-white" style="background: linear-gradient(135deg, #1b84ff 0%, #4fc3f7 100%);">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">Rp {{ number_format($totalStockValue, 0, ',', '.') }}</span>
                        <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Total Stocks Portfolio Value</span>
                    </div>
                </div>
                <div class="card-body d-flex align-items-end pt-0">
                    <div class="d-flex align-items-center flex-column mt-3 w-100">
                        <div class="d-flex justify-content-between w-100 mt-auto mb-2">
                            <span class="fw-boldest fs-6 text-white">{{ $stockPortfolios->count() }} Broker Accounts Registered</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-flush h-md-100 shadow-sm border-0">
                <div class="card-header pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-800">My Emiten</span>
                        <span class="text-gray-400 mt-1 fw-semibold fs-7">Balance breakdown per emiten</span>
                    </h3>
                </div>
                <div class="card-body pt-2">
                    <div class="row">
                        @forelse($emitenBalances as $item)
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center bg-light-info rounded p-5">
                                <i class="ki-duotone ki-chart-line-up-2 fs-1 text-info me-5">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                                <div class="flex-grow-1">
                                    <span class="text-gray-800 fw-bold fs-6">{{ $item['asset'] }}</span>
                                    @if($item['lot'] !== null)
                                    <span class="text-muted fw-semibold d-block fs-7">{{ $item['lot'] }} Lot</span>
                                    @endif
                                </div>
                                <div class="text-end">
                                    <span class="text-gray-800 fw-boldest d-block">Rp {{ number_format($item['balance'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12 text-muted">No stock/emiten activity yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-flush shadow-sm">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    <input type="text" id="stock_search" class="form-control form-control-solid w-250px ps-12" placeholder="Search Stocks History..." />
                </div>
            </div>
            <div class="card-toolbar">
                <div class="d-flex justify-content-end">
                    <a href="{{ route('money-management.portfolio.index') }}" class="btn btn-primary">
                        <i class="ki-duotone ki-plus fs-2"></i> Manage in Portfolio
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="table_stock_tracking">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-50px">No</th>
                        <th class="min-w-125px">Date</th>
                        <th class="min-w-150px">Account</th>
                        <th class="min-w-100px">Emiten</th>
                        <th class="min-w-80px">Lot</th>
                        <th class="min-w-100px">Type</th>
                        <th class="min-w-150px">Value (IDR)</th>
                        <th class="min-w-150px">Description</th>
                        <th class="text-end min-w-70px">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold"></tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            let table = $('#table_stock_tracking').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('money-management.stock-tracking.datatable') }}",
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'date', name: 'date'},
                    {data: 'portfolio_name', name: 'portfolio_name'},
                    {data: 'asset', name: 'asset'},
                    {data: 'lot', name: 'lot'},
                    {data: 'type', name: 'type'},
                    {data: 'amount', name: 'amount', searchable: false},
                    {data: 'description', name: 'description'},
                    {data: 'action', name: 'action', orderable: false, searchable: false, className: "text-end"},
                ],
                order: [[1, 'asc']]
            });

            $('#stock_search').keyup(function(){
                table.search($(this).val()).draw();
            });

            $(document).on('click', '.delete-stock-trx-btn', function() {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Remove transaction?',
                    text: "This will remove the transaction entry from this portfolio's history.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, remove!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('money-management/stock-tracking') }}/" + id,
                            method: "DELETE",
                            data: { _token: "{{ csrf_token() }}" },
                            success: function(res) {
                                Swal.fire('Removed!', res.success, 'success');
                                table.ajax.reload();
                            }
                        });
                    }
                });
            });
        });
    </script>
    @endpush
</x-default-layout>
