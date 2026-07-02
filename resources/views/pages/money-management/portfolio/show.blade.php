<x-default-layout>
    @section('title')
        {{ $portfolio->account_name }} Details
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('money-management.portfolio.show', $portfolio->id) }}
    @endsection

    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        <!-- Dashboard Card -->
        <div class="col-xl-12">
            <div class="card card-flush h-xl-100">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-800">{{ $portfolio->account_name }}</span>
                        <span class="text-gray-400 mt-1 fw-semibold fs-6">Provider: {{ $portfolio->investment->name ?? 'Other' }}</span>
                    </h3>
                    <div class="card-toolbar">
                        <div class="d-flex align-items-center">
                            <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2">Rp {{ number_format($balance, 0, ',', '.') }}</span>
                            <span class="badge badge-light-success fs-base">Current Balance</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--begin::Card-->
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3 class="fw-bold m-0">Transaction History</h3>
            </div>
            <div class="card-toolbar">
                <button type="button" class="btn btn-primary" id="btn_add_transaction">
                    <i class="ki-duotone ki-plus fs-2"></i> Add Transaction
                </button>
            </div>
        </div>
        <div class="card-body">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="table_transactions">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-50px">No</th>
                        <th class="min-w-100px">Date</th>
                        <th class="min-w-100px">Type</th>
                        <th class="min-w-100px">{{ optional($portfolio->investment)->type === 'stock' ? 'Emiten' : 'Asset' }}</th>
                        @if(optional($portfolio->investment)->type === 'stock')
                        <th class="min-w-80px">Lot</th>
                        @endif
                        <th class="min-w-150px">Amount</th>
                        <th class="min-w-200px">Description</th>
                        <th class="text-end min-w-100px">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold"></tbody>
            </table>
        </div>
    </div>
    <!--end::Card-->

    <!-- BEGIN::Modal Transaction -->
    <div class="modal fade" id="modal_transaction" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold" id="modal_transaction_title">Add Transaction</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <form id="form_transaction" class="form">
                    @csrf
                    <input type="hidden" name="id" id="trx_id">
                    <input type="hidden" name="finance_investment_id" value="{{ $portfolio->id }}">
                    
                    <div class="modal-body py-10 px-lg-17">
                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Date</label>
                            <input type="date" class="form-control form-control-solid" name="date" id="trx_date" required />
                        </div>

                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Type</label>
                            <select class="form-select form-select-solid" name="type" id="trx_type" data-control="select2" data-hide-search="true" required>
                                <option value="deposit">Deposit (Top Up)</option>
                                <option value="withdrawal">Withdrawal (Tarik)</option>
                                <option value="profit">Profit (Bunga/Keep)</option>
                                <option value="loss">Loss (Rugi/Biaya)</option>
                            </select>
                        </div>

                        @if(in_array(optional($portfolio->investment)->type, ['crypto', 'stock']))
                        <div class="fv-row mb-7">
                            <label class="fs-6 fw-semibold mb-2">{{ $portfolio->investment->type === 'stock' ? 'Emiten' : 'Asset' }}</label>
                            <input type="text" class="form-control form-control-solid" name="asset" id="trx_asset" placeholder="{{ $portfolio->investment->type === 'stock' ? 'e.g. PRDL' : 'e.g. BITCOIN' }}" />
                        </div>
                        @endif

                        @if(optional($portfolio->investment)->type === 'stock')
                        <div class="fv-row mb-7">
                            <label class="fs-6 fw-semibold mb-2">Lot</label>
                            <input type="number" min="0" step="1" class="form-control form-control-solid" name="lot" id="trx_lot" placeholder="e.g. 5" />
                        </div>
                        @endif

                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Amount</label>
                            <input type="number" class="form-control form-control-solid" placeholder="0" name="amount" id="trx_amount" required min="0" />
                        </div>

                        <div class="fv-row mb-7">
                            <label class="fs-6 fw-semibold mb-2">Description</label>
                            <textarea class="form-control form-control-solid" rows="3" name="description" id="trx_description" placeholder="Optional description"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer flex-center">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">Submit</span>
                            <span class="indicator-progress">Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- END::Modal Transaction -->

    @push('scripts')
    <script>
        $(document).ready(function() {
            let table = $('#table_transactions').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('money-management.portfolio.transactions.datatable', $portfolio->id) }}",
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'date', name: 'date'},
                    {data: 'type', name: 'type'},
                    {data: 'asset', name: 'asset'},
                    @if(optional($portfolio->investment)->type === 'stock')
                    {data: 'lot', name: 'lot'},
                    @endif
                    {data: 'amount', name: 'amount'},
                    {data: 'description', name: 'description'},
                    {data: 'action', name: 'action', orderable: false, searchable: false, className: "text-end"},
                ]
            });

            $('#btn_add_transaction').click(function() {
                $('#form_transaction')[0].reset();
                $('#trx_id').val('');
                $('#trx_type').val('deposit').trigger('change');
                
                let today = new Date().toISOString().split('T')[0];
                $('#trx_date').val(today);

                $('#modal_transaction_title').text('Add Transaction');
                $('#modal_transaction').modal('show');
            });

            // Edit Transaction
            $(document).on('click', '.edit-trx-btn', function() {
                let id = $(this).data('id');
                let date = $(this).data('date');
                let type = $(this).data('type');
                let amount = $(this).data('amount');
                let asset = $(this).data('asset');
                let lot = $(this).data('lot');
                let desc = $(this).data('description');

                $('#trx_id').val(id);
                $('#trx_date').val(date);
                $('#trx_type').val(type).trigger('change');
                $('#trx_amount').val(amount);
                $('#trx_asset').val(asset);
                $('#trx_lot').val(lot);
                $('#trx_description').val(desc);

                $('#modal_transaction_title').text('Edit Transaction');
                $('#modal_transaction').modal('show');
            });

            // Submit Form
            $('#form_transaction').submit(function(e) {
                e.preventDefault();
                let formData = new FormData(this);
                let id = $('#trx_id').val();
                let url = id ? 
                    "{{ route('money-management.portfolio.transactions.update', ':id') }}".replace(':id', id) : 
                    "{{ route('money-management.portfolio.transactions.store') }}";
                
                if(id) formData.append('_method', 'PUT');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        $('#modal_transaction').modal('hide');
                        table.draw();
                        // Optional: Reload page to update balance header, or update via JS
                        // location.reload(); 
                        Swal.fire({ 
                            text: response.success, 
                            icon: "success", 
                            buttonsStyling: false, 
                            confirmButtonText: "Ok!", 
                            customClass: { confirmButton: "btn btn-primary" } 
                        }).then((result) => {
                             location.reload(); // Simple way to update balance card
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({ text: "Error saving data", icon: "error", buttonsStyling: false, confirmButtonText: "Ok!", customClass: { confirmButton: "btn btn-primary" } });
                    }
                });
            });

            // Delete Transaction
            $(document).on('click', '.delete-trx-btn', function() {
                let id = $(this).data('id');
                Swal.fire({
                    text: "Are you sure you want to delete this transaction?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, delete!",
                    customClass: { confirmButton: "btn btn-danger", cancelButton: "btn btn-active-light" }
                }).then(function(result) {
                    if (result.value) {
                        $.ajax({
                            url: "{{ route('money-management.portfolio.transactions.destroy', ':id') }}".replace(':id', id),
                            type: "DELETE",
                            data: { _token: "{{ csrf_token() }}" },
                            success: function(response) {
                                table.draw();
                                Swal.fire({ 
                                    text: response.success, 
                                    icon: "success", 
                                    buttonsStyling: false, 
                                    confirmButtonText: "Ok!", 
                                    customClass: { confirmButton: "btn btn-primary" } 
                                }).then((result) => {
                                     location.reload(); 
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
    @endpush
</x-default-layout>
