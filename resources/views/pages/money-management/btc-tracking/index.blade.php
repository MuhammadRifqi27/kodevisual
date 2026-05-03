<x-default-layout>
    @section('title')
        Bitcoin Journey Tracking
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('money-management.btc-tracking') }}
    @endsection

    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        <!-- Summary Card -->
        <div class="col-md-4">
            <div class="card card-flush h-md-100 shadow-sm border-0 bg-gradient-primary text-white" style="background: linear-gradient(135deg, #f7931a 0%, #ffab40 100%);">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">Rp {{ number_format($totalBtcValue, 0, ',', '.') }}</span>
                        <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Total Bitcoin Portfolio Value</span>
                    </div>
                </div>
                <div class="card-body d-flex align-items-end pt-0">
                    <div class="d-flex align-items-center flex-column mt-3 w-100">
                        <div class="d-flex justify-content-between w-100 mt-auto mb-2">
                            <span class="fw-boldest fs-6 text-white">{{ $btcPortfolios->count() }} Accounts Registered</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-flush h-md-100 shadow-sm border-0">
                <div class="card-header pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-800">My Bitcoin Accounts</span>
                        <span class="text-gray-400 mt-1 fw-semibold fs-7">Overview of all BTC related portfolios</span>
                    </h3>
                </div>
                <div class="card-body pt-2">
                    <div class="row">
                        @foreach($btcPortfolios as $portfolio)
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center bg-light-warning rounded p-5">
                                <i class="ki-duotone ki-bitcoin fs-1 text-warning me-5">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                                <div class="flex-grow-1">
                                    <a href="{{ route('money-management.portfolio.show', $portfolio->id) }}" class="text-gray-800 text-hover-primary fw-bold fs-6">{{ $portfolio->account_name }}</a>
                                    <span class="text-muted fw-semibold d-block">{{ $portfolio->investment->name }}</span>
                                </div>
                                <div class="text-end">
                                    <span class="text-gray-800 fw-boldest d-block">Rp {{ number_format($portfolio->balance, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
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
                    <input type="text" id="btc_search" class="form-control form-control-solid w-250px ps-12" placeholder="Search Bitcoin History..." />
                </div>
            </div>
            <div class="card-toolbar">
                <div class="d-flex justify-content-end">
                    <a href="{{ route('money-management.transfers.index') }}" class="btn btn-light-primary me-3">
                        <i class="ki-duotone ki-arrows-loop fs-2"><span class="path1"></span><span class="path2"></span></i>
                        New Internal Transfer
                    </a>
                    <a href="{{ route('money-management.transactions.index') }}" class="btn btn-primary">
                        <i class="ki-duotone ki-plus fs-2"></i> Add Transaction
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="table_btc_tracking">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-50px">No</th>
                        <th class="min-w-125px">Date</th>
                        <th class="min-w-150px">Account</th>
                        <th class="min-w-200px">Activity</th>
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
            let table = $('#table_btc_tracking').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('money-management.btc-tracking.datatable') }}",
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'date', name: 'date'},
                    {data: 'portfolio_name', name: 'portfolio_name'},
                    {data: 'description_display', name: 'description_display', orderable: false},
                    {data: 'amount', name: 'amount', searchable: false},
                    {data: 'description', name: 'description'},
                    {
                        data: 'action', 
                        name: 'action', 
                        orderable: false, 
                        searchable: false, 
                        className: "text-end",
                        render: function(data, type, row) {
                            return '<button data-id="'+row.id+'" class="btn btn-icon btn-active-light-danger w-30px h-30px delete-btc-trx-btn" title="Remove from Tracking"><i class="ki-duotone ki-trash fs-3"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i></button>';
                        }
                    },
                ],
                order: [[1, 'desc']]
            });

            $('#btc_search').keyup(function(){
                table.search($(this).val()).draw();
            });

            $(document).on('click', '.delete-btc-trx-btn', function() {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Remove transaction?',
                    text: "This will only remove the transaction entry. It won't delete the record if it's a transfer (to maintain balance).",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, remove!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('money-management/btc-tracking') }}/" + id,
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
