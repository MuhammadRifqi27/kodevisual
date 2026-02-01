<x-default-layout>
    @section('title')
        Transfers
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('money-management.transfers') }}
    @endsection

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    <input type="text" id="transfer_search"
                        class="form-control form-control-solid w-250px ps-12" placeholder="Search Transfers..." />
                </div>
            </div>
            <div class="card-toolbar">
                <button type="button" class="btn btn-primary" id="btn_add_transfer">
                    <i class="ki-duotone ki-plus fs-2"></i> New Transfer
                </button>
            </div>
        </div>
        <div class="card-body">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="table_transfers">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-50px">No</th>
                        <th class="min-w-100px">Date</th>
                        <th class="min-w-150px">From</th>
                        <th class="min-w-150px">To</th>
                        <th class="min-w-150px">Amount</th>
                        <th class="min-w-200px">Description</th>
                        <th class="text-end min-w-100px">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold"></tbody>
            </table>
        </div>
    </div>

    <!-- BEGIN::Modal Transfer -->
    <div class="modal fade" id="modal_transfer" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold">Record Internal Transfer</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <form id="form_transfer" class="form">
                    @csrf
                    <div class="modal-body py-10 px-lg-17">
                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Date</label>
                            <input type="date" class="form-control form-control-solid" name="date" value="{{ date('Y-m-d') }}" required />
                        </div>

                        <div class="row g-9 mb-7">
                            <div class="col-md-6 fv-row">
                                <label class="required fs-6 fw-semibold mb-2">From Account</label>
                                <select class="form-select form-select-solid" name="from_account_id" data-control="select2" data-dropdown-parent="#modal_transfer" data-placeholder="Source Account" required>
                                    <option></option>
                                    @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="required fs-6 fw-semibold mb-2">To Account</label>
                                <select class="form-select form-select-solid" name="to_account_id" data-control="select2" data-dropdown-parent="#modal_transfer" data-placeholder="Destination Account" required>
                                    <option></option>
                                    @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Amount (Rp)</label>
                            <input type="number" step="0.01" class="form-control form-control-solid" name="amount" placeholder="e.g. 1000000" required />
                        </div>

                        <div class="fv-row mb-7">
                            <label class="fs-6 fw-semibold mb-2">Description / Note</label>
                            <textarea class="form-control form-control-solid" name="description" rows="2" placeholder="Optional notes..."></textarea>
                        </div>

                        <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-6">
                            <i class="ki-duotone ki-information-5 fs-2tx text-primary me-4">
                                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                            </i>
                            <div class="d-flex flex-stack flex-grow-1">
                                <div class="fw-semibold">
                                    <div class="fs-6 text-gray-700">Transfer transactions do not affect your Monthly Income or Expense reports. They only shift balances between accounts.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer flex-center">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="btn_submit_transfer">
                            <span class="indicator-label">Record Transfer</span>
                            <span class="indicator-progress">Processing... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- BEGIN::Modal Transfer Receipt -->
    <div class="modal fade" id="modal_receipt" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-500px">
            <div class="modal-content" id="receipt_container">
                <!-- Content loaded via AJAX -->
                <div class="p-20 text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            let table = $('#table_transfers').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('money-management.transfers.datatable') }}",
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'date', name: 'date'},
                    {data: 'from', name: 'from'},
                    {data: 'to', name: 'to'},
                    {data: 'amount', name: 'amount'},
                    {data: 'description', name: 'description'},
                    {
                        data: 'action', 
                        name: 'action', 
                        orderable: false, 
                        searchable: false, 
                        className: "text-end",
                        render: function(data, type, row) {
                            return `
                                <button data-id="${row.id}" class="btn btn-icon btn-active-light-primary w-30px h-30px view-receipt-btn me-2" title="View Receipt">
                                    <i class="ki-duotone ki-file-down fs-3"><span class="path1"></span><span class="path2"></span></i>
                                </button>
                                ${data}
                            `;
                        }
                    },
                ]
            });

            function showReceipt(id) {
                $('#modal_receipt').modal('show');
                $('#receipt_container').html('<div class="p-20 text-center"><div class="spinner-border text-primary" role="status"></div></div>');
                
                $.get("{{ route('money-management.transfers.receipt', ':id') }}".replace(':id', id), function(html) {
                    $('#receipt_container').html(html);
                });
            }

            $(document).on('click', '.view-receipt-btn', function() {
                showReceipt($(this).data('id'));
            });

            $('#transfer_search').keyup(function(){
                table.search($(this).val()).draw();
            });

            $('#btn_add_transfer').click(function() {
                $('#form_transfer')[0].reset();
                $('#form_transfer select').val('').trigger('change');
                $('#modal_transfer').modal('show');
            });

            $('#form_transfer').submit(function(e) {
                e.preventDefault();
                let btn = $('#btn_submit_transfer');
                btn.attr('data-kt-indicator', 'on').prop('disabled', true);

                $.ajax({
                    url: "{{ route('money-management.transfers.store') }}",
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        btn.removeAttr('data-kt-indicator').prop('disabled', false);
                        $('#modal_transfer').modal('hide');
                        table.ajax.reload();
                        
                        Swal.fire({ 
                            text: response.success, 
                            icon: "success", 
                            showCancelButton: true,
                            confirmButtonText: "Print Receipt",
                            cancelButtonText: "Close",
                            customClass: { 
                                confirmButton: "btn btn-primary",
                                cancelButton: "btn btn-light"
                            } 
                        }).then((result) => {
                            if (result.isConfirmed) {
                                showReceipt(response.transaction_id);
                            }
                        });
                    },
                    error: function(xhr) {
                        btn.removeAttr('data-kt-indicator').prop('disabled', false);
                        let msg = xhr.responseJSON ? xhr.responseJSON.message : "Error recording transfer";
                        Swal.fire({ text: msg, icon: "error", buttonsStyling: false, confirmButtonText: "Ok!", customClass: { confirmButton: "btn btn-primary" } });
                    }
                });
            });

            $(document).on('click', '.delete-transfer-btn', function() {
                let id = $(this).data('id');
                Swal.fire({
                    text: "Are you sure you want to delete this transfer? This will revert balances for both involved accounts.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, delete!",
                    customClass: { confirmButton: "btn btn-danger", cancelButton: "btn btn-active-light" }
                }).then(function(result) {
                    if (result.value) {
                        $.ajax({
                            url: "{{ route('money-management.transfers.destroy', ':id') }}".replace(':id', id),
                            type: "DELETE",
                            data: { _token: "{{ csrf_token() }}" },
                            success: function(response) {
                                table.ajax.reload();
                                Swal.fire({ text: response.success, icon: "success", buttonsStyling: false, confirmButtonText: "Ok!", customClass: { confirmButton: "btn btn-primary" } });
                            }
                        });
                    }
                });
            });
        });
    </script>
    @endpush
</x-default-layout>
