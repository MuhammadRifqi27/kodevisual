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
                        <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">Rp {{ number_format($totalEquity, 0, ',', '.') }}</span>
                        <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Total Equity</span>
                    </div>
                </div>
                <div class="card-body d-flex align-items-end pt-0">
                    <div class="d-flex align-items-center flex-column mt-3 w-100">
                        <div class="d-flex justify-content-between w-100 mb-2">
                            <span class="text-white opacity-75 fw-semibold fs-7">Trading Balance</span>
                            <span class="fw-boldest fs-6 text-white">Rp {{ number_format($tradingBalance, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between w-100 mb-2">
                            <span class="text-white opacity-75 fw-semibold fs-7">Open (pending IPO)</span>
                            <span class="fw-boldest fs-6 text-white">Rp {{ number_format($openAmount, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between w-100 mb-2">
                            <span class="text-white opacity-75 fw-semibold fs-7">Invested</span>
                            <span class="fw-boldest fs-6 text-white">Rp {{ number_format($investedTotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between w-100 mb-2">
                            <span class="text-white opacity-75 fw-semibold fs-7">P&amp;L</span>
                            <span class="fw-boldest fs-6 {{ $totalPnl > 0 ? 'text-light-success' : ($totalPnl < 0 ? 'text-danger' : 'text-white') }}">{{ $totalPnl >= 0 ? '+' : '-' }} Rp {{ number_format(abs($totalPnl), 0, ',', '.') }}</span>
                        </div>
                        <div class="separator separator-dashed border-white opacity-25 w-100 mb-3"></div>
                        <div class="d-flex justify-content-between w-100">
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
                                    <span class="text-muted fw-semibold d-block fs-8">
                                        Harga: {{ $item['current_price'] !== null ? 'Rp '.number_format($item['current_price'], 0, ',', '.') : '-' }}
                                        <button type="button" class="btn btn-icon btn-sm btn-active-color-primary w-15px h-15px edit-price-btn" data-asset="{{ $item['asset'] }}" data-price="{{ $item['current_price'] }}" title="Update Price">
                                            <i class="ki-duotone ki-pencil fs-7"><span class="path1"></span><span class="path2"></span></i>
                                        </button>
                                    </span>
                                </div>
                                <div class="text-end">
                                    <span class="text-gray-800 fw-boldest d-block">Rp {{ number_format($item['market_value'], 0, ',', '.') }}</span>
                                    <span class="fw-semibold d-block fs-8 {{ $item['pnl'] > 0 ? 'text-success' : ($item['pnl'] < 0 ? 'text-danger' : 'text-muted') }}">
                                        {{ $item['pnl'] >= 0 ? '+' : '-' }} Rp {{ number_format(abs($item['pnl']), 0, ',', '.') }}
                                    </span>
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

    <!-- Modal Update Current Price -->
    <div class="modal fade" id="modal_price" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-400px">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold">Update Current Price</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <div class="modal-body py-10 px-lg-17">
                    <form id="form_price">
                        @csrf
                        <div class="fv-row mb-7">
                            <label class="fs-6 fw-semibold mb-2">Emiten</label>
                            <input type="text" id="price_asset" name="asset" class="form-control form-control-solid" readonly />
                        </div>
                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Current Price / Share</label>
                            <input type="number" step="0.01" min="0.01" id="price_current_price" name="current_price" class="form-control form-control-solid" required />
                        </div>
                        <div class="text-center pt-5">
                            <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <span class="indicator-label">Save</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Buy/Sell Emiten -->
    <div class="modal fade" id="modal_trade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-500px">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold">Buy / Sell Emiten</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <div class="modal-body py-10 px-lg-17">
                    <form id="form_trade">
                        @csrf
                        <div class="fv-row mb-7">
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="type" id="trade_type_buy" value="buy" checked>
                                <label class="btn btn-outline btn-outline-success btn-active-light-success w-50" for="trade_type_buy">Buy</label>

                                <input type="radio" class="btn-check" name="type" id="trade_type_sell" value="sell">
                                <label class="btn btn-outline btn-outline-danger btn-active-light-danger w-50" for="trade_type_sell">Sell</label>
                            </div>
                        </div>
                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Broker Account</label>
                            <select name="finance_investment_id" id="trade_finance_investment_id" class="form-select form-select-solid" data-control="select2" data-dropdown-parent="#modal_trade" data-placeholder="Select Stock Broker" data-minimum-results-for-search="0" required>
                                <option></option>
                                @foreach($stockPortfolios as $p)
                                    <option value="{{ $p->id }}">{{ $p->account_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Emiten</label>
                            <input type="text" name="asset" id="trade_asset" class="form-control form-control-solid" placeholder="e.g. PRDL" required />
                        </div>
                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Trade Date</label>
                            <input type="date" name="trade_date" id="trade_date" class="form-control form-control-solid" required />
                        </div>
                        <div class="row g-9 mb-7">
                            <div class="col-md-6 fv-row">
                                <label class="required fs-6 fw-semibold mb-2">Price / Share</label>
                                <input type="number" step="0.01" min="0.01" name="price_per_share" id="trade_price_per_share" class="form-control form-control-solid" placeholder="e.g. 1000" required />
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="required fs-6 fw-semibold mb-2">Lot</label>
                                <input type="number" step="1" min="1" name="lot" id="trade_lot" class="form-control form-control-solid" placeholder="e.g. 5" required />
                            </div>
                        </div>
                        <div class="fv-row mb-7">
                            <label class="fs-6 fw-semibold mb-2">Description</label>
                            <textarea name="description" id="trade_description" class="form-control form-control-solid" rows="2"></textarea>
                        </div>
                        <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-6">
                            <div class="fs-7 text-gray-700" id="trade_amount_preview">-</div>
                        </div>
                        <div class="text-center pt-10">
                            <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Discard</button>
                            <button type="submit" class="btn btn-primary">
                                <span class="indicator-label">Submit</span>
                            </button>
                        </div>
                    </form>
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
                    <button type="button" class="btn btn-light-success me-3" id="btn_add_trade">
                        <i class="ki-duotone ki-arrow-up-down fs-2"><span class="path1"></span><span class="path2"></span></i>
                        Buy / Sell Emiten
                    </button>
                    <a href="{{ route('money-management.ipo.index') }}" class="btn btn-light-primary me-3">
                        <i class="ki-duotone ki-crown fs-2"><span class="path1"></span><span class="path2"></span></i>
                        IPO Orders
                    </a>
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
                ]
            });

            $(document).on('click', '.edit-price-btn', function() {
                $('#price_asset').val($(this).data('asset'));
                $('#price_current_price').val($(this).data('price') || '');
                $('#modal_price').modal('show');
            });

            $('#form_price').submit(function(e) {
                e.preventDefault();
                let btn = $(this).find('button[type="submit"]');
                btn.attr('data-kt-indicator', 'on').prop('disabled', true);

                $.ajax({
                    url: "{{ route('money-management.stock-tracking.price') }}",
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        Swal.fire({ text: res.success, icon: "success" }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(err) {
                        Swal.fire({ text: err.responseJSON.error || err.responseJSON.message || "Error occurred", icon: "error" });
                        btn.removeAttr('data-kt-indicator').prop('disabled', false);
                    }
                });
            });

            $('#stock_search').keyup(function(){
                table.search($(this).val()).draw();
            });

            $('#btn_add_trade').click(function() {
                $('#form_trade')[0].reset();
                $('#trade_finance_investment_id').val('').trigger('change');
                $('#trade_date').val(new Date().toISOString().split('T')[0]);
                $('#trade_amount_preview').text('-');
                $('#modal_trade').modal('show');
            });

            function recalculateTradeAmount() {
                let lot = parseFloat($('#trade_lot').val()) || 0;
                let price = parseFloat($('#trade_price_per_share').val()) || 0;
                if (lot > 0 && price > 0) {
                    let amount = lot * 100 * price;
                    $('#trade_amount_preview').text(lot + ' lot x 100 x Rp ' + price.toLocaleString('id-ID') + ' = Rp ' + amount.toLocaleString('id-ID'));
                } else {
                    $('#trade_amount_preview').text('-');
                }
            }

            $('#trade_lot, #trade_price_per_share').on('input', recalculateTradeAmount);

            $('#form_trade').submit(function(e) {
                e.preventDefault();
                let btn = $(this).find('button[type="submit"]');
                btn.attr('data-kt-indicator', 'on').prop('disabled', true);

                $.ajax({
                    url: "{{ route('money-management.stock-tracking.trade.store') }}",
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        Swal.fire({ text: res.success, icon: "success" }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(err) {
                        Swal.fire({ text: err.responseJSON.error || err.responseJSON.message || "Error occurred", icon: "error" });
                        btn.removeAttr('data-kt-indicator').prop('disabled', false);
                    }
                });
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
