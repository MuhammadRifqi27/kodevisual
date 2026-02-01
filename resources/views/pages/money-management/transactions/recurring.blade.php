<x-default-layout>
    @section('title')
        Recurring Transactions
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('money-management.recurring') }}
    @endsection

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    <input type="text" id="recurring_search" class="form-control form-control-solid w-250px ps-12" placeholder="Search recurring..." />
                </div>
            </div>
            <div class="card-toolbar">
                <button type="button" class="btn btn-primary" id="btn_add_recurring">
                    <i class="ki-duotone ki-plus fs-2"></i> New Recurring
                </button>
            </div>
        </div>
        <div class="card-body">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="table_recurring">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-50px">No</th>
                        <th class="min-w-100px">Name</th>
                        <th class="min-w-100px">Frequency</th>
                        <th class="min-w-100px">Account</th>
                        <th class="min-w-100px">Amount</th>
                        <th class="min-w-100px">Next Date</th>
                        <th class="min-w-50px">Status</th>
                        <th class="text-end min-w-100px">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold"></tbody>
            </table>
        </div>
    </div>

    <!-- Modal Recurring -->
    <div class="modal fade" id="modal_recurring" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold">Setup Recurring Transaction</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <form id="form_recurring" class="form">
                    @csrf
                    <div class="modal-body py-10 px-lg-17">
                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Automation Name</label>
                            <input type="text" class="form-control form-control-solid" name="name" placeholder="e.g. Monthly Netflix Subscription" required />
                        </div>

                        <div class="row g-9 mb-7">
                            <div class="col-md-6 fv-row">
                                <label class="required fs-6 fw-semibold mb-2">Transaction Type</label>
                                <select class="form-select form-select-solid" name="type" required>
                                    <option value="expense">Expense (-)</option>
                                    <option value="income">Income (+)</option>
                                </select>
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="required fs-6 fw-semibold mb-2">Frequency</label>
                                <select class="form-select form-select-solid" name="frequency" required>
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly" selected>Monthly</option>
                                    <option value="yearly">Yearly</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-9 mb-7">
                            <div class="col-md-6 fv-row">
                                <label class="required fs-6 fw-semibold mb-2">Category</label>
                                <select class="form-select form-select-solid" name="finance_category_id" data-control="select2" data-dropdown-parent="#modal_recurring" required>
                                    <option></option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }} ({{ ucfirst($cat->type) }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="required fs-6 fw-semibold mb-2">Account</label>
                                <select class="form-select form-select-solid" name="finance_investment_id" data-control="select2" data-dropdown-parent="#modal_recurring" required>
                                    <option></option>
                                    @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->account_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row g-9 mb-7">
                            <div class="col-md-6 fv-row">
                                <label class="required fs-6 fw-semibold mb-2">Amount</label>
                                <input type="number" step="0.01" class="form-control form-control-solid" name="amount" required />
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="required fs-6 fw-semibold mb-2">Start Date</label>
                                <input type="date" class="form-control form-control-solid" name="start_date" value="{{ date('Y-m-d') }}" required />
                            </div>
                        </div>
                        
                        <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-6">
                            <i class="ki-duotone ki-information-5 fs-2tx text-warning me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                            <div class="d-flex flex-stack flex-grow-1">
                                <div class="fw-semibold text-gray-700 fs-7">
                                    Recurring transactions will be automatically generated as actual transaction records based on the frequency selected.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer flex-center">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="btn_submit_recurring">Save Automation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            let table = $('#table_recurring').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('money-management.recurring.datatable') }}",
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'name', name: 'name'},
                    {data: 'frequency', name: 'frequency'},
                    {data: 'portfolio.account_name', name: 'portfolio.account_name'},
                    {data: 'amount', name: 'amount'},
                    {data: 'next_date', name: 'next_date'},
                    {data: 'is_active', name: 'is_active'},
                    {data: 'action', name: 'action', orderable: false, searchable: false, className: "text-end"},
                ]
            });

            $('#btn_add_recurring').click(function() {
                $('#form_recurring')[0].reset();
                $('#form_recurring select').val('').trigger('change');
                $('#modal_recurring').modal('show');
            });

            $('#form_recurring').submit(function(e) {
                e.preventDefault();
                $.post("{{ route('money-management.recurring.store') }}", $(this).serialize(), function(res) {
                    $('#modal_recurring').modal('hide');
                    table.ajax.reload();
                    Swal.fire({ text: res.success, icon: "success" });
                });
            });

            $(document).on('click', '.delete-recurring-btn', function() {
                let id = $(this).data('id');
                Swal.fire({
                    text: "Delete this automation?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, delete it!"
                }).then(function(result) {
                    if (result.value) {
                        $.ajax({
                            url: "{{ route('money-management.recurring.destroy', ':id') }}".replace(':id', id),
                            type: "DELETE",
                            data: { _token: "{{ csrf_token() }}" },
                            success: function(res) {
                                table.ajax.reload();
                                Swal.fire({ text: res.success, icon: "success" });
                            }
                        });
                    }
                });
            });
        });
    </script>
    @endpush
</x-default-layout>
