<x-default-layout>
    @section('title')
        Transactions
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('money-management.transactions') }}
    @endsection

    <!--begin::Card-->
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    <input type="text" id="transaction_search"
                        class="form-control form-control-solid w-250px ps-12" placeholder="Search Transactions..." />
                </div>
            </div>
            <div class="card-toolbar">
                <div class="d-flex justify-content-end me-2">
                    <!-- Filter Type -->
                    <select id="filter_type" class="form-select form-select-solid me-2" data-control="select2" data-hide-search="true">
                        <option value="all">All Types</option>
                        <option value="income">Income</option>
                        <option value="expense">Expense</option>
                    </select>
                </div>
                
                <button type="button" class="btn btn-sm btn-primary" id="btn_add_transaction">
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
                        <th class="min-w-150px">Category</th>
                        <th class="min-w-150px">Account/Portfolio</th>
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
                    
                    <div class="modal-body py-10 px-lg-17">
                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Date</label>
                            <input type="date" class="form-control form-control-solid" name="date" id="trx_date" required />
                        </div>

                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Type</label>
                            <select class="form-select form-select-solid" name="type" id="trx_type" data-control="select2" data-hide-search="true" required>
                                <option value="income">Income</option>
                                <option value="expense" selected>Expense</option>
                            </select>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="fs-6 fw-semibold mb-2">Source/Destination Portofolio</label>
                            <select class="form-select form-select-solid" name="investment_id" id="trx_investment" data-control="select2" data-placeholder="Select Portfolio (Optional)">
                                <option></option>
                                @foreach($investments as $inv)
                                    <option value="{{ $inv->id }}">{{ $inv->name }}</option>
                                @endforeach
                            </select>
                            <div class="text-muted fs-7">Uang akan bertambah/berkurang dari portofolio yang dipilih.</div>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Category</label>
                            <select class="form-select form-select-solid" name="category_id" id="trx_category" data-control="select2" data-placeholder="Select Category" required>
                                <option></option>
                            </select>
                        </div>

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
            // Initialize DataTable
            let table = $('#table_transactions').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('money-management.transactions.datatable') }}",
                    data: function(d) {
                        d.type = $('#filter_type').val();
                    }
                },
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'date', name: 'date'},
                    {data: 'type', name: 'type'},
                    {data: 'category_name', name: 'category.name'},
                    {data: 'investment_name', name: 'investment.name'},
                    {data: 'amount', name: 'amount'},
                    {data: 'description', name: 'description'},
                    {data: 'action', name: 'action', orderable: false, searchable: false, className: "text-end"},
                ],
                order: [[1, 'desc']],
            });

            // Refresh table on filter change
            $('#filter_type').change(function() {
                table.draw();
            });

            $('#transaction_search').keyup(function(){
                table.search($(this).val()).draw();
            });

            // Load Categories based on Type
            function loadCategories(type, selectedId = null) {
                $.ajax({
                    url: "{{ route('money-management.transactions.get-categories') }}",
                    data: { type: type },
                    success: function(data) {
                        let options = '<option></option>';
                        data.forEach(function(item) {
                            options += `<option value="${item.id}">${item.name}</option>`;
                        });
                        $('#trx_category').html(options).trigger('change');
                        if(selectedId) {
                            $('#trx_category').val(selectedId).trigger('change');
                        }
                    }
                });
            }

            // Trigger category load when type changes in modal
            $('#trx_type').change(function() {
                loadCategories($(this).val());
            });

            $('#btn_add_transaction').click(function() {
                $('#form_transaction')[0].reset();
                $('#trx_id').val('');
                $('#trx_type').val('expense').trigger('change'); // Default to expense
                
                // Set default date to today
                let today = new Date().toISOString().split('T')[0];
                $('#trx_date').val(today);

                $('#modal_transaction_title').text('Add Transaction');
                $('#modal_transaction').modal('show');
            });

            // Edit Transaction
            $(document).on('click', '.edit-transaction-btn', function() {
                let id = $(this).data('id');
                let date = $(this).data('date');
                let type = $(this).data('type');
                let categoryId = $(this).data('category');
                let investmentId = $(this).data('investment');
                let amount = $(this).data('amount');
                let desc = $(this).data('description');

                $('#trx_id').val(id);
                $('#trx_date').val(date);
                $('#trx_type').val(type).trigger('change');
                $('#trx_investment').val(investmentId).trigger('change');
                
                // Wait for categories to load then select
                // Note: since loadCategories is async, we might need a better way if we want to be 100% sure,
                // but usually the type change trigger handles it. However, to select the specific value we pass it.
                loadCategories(type, categoryId);

                $('#trx_amount').val(amount);
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
                    "{{ route('money-management.transactions.update', ':id') }}".replace(':id', id) : 
                    "{{ route('money-management.transactions.store') }}";
                
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
                        Swal.fire({ text: response.success, icon: "success", buttonsStyling: false, confirmButtonText: "Ok!", customClass: { confirmButton: "btn btn-primary" } });
                    },
                    error: function(xhr) {
                        Swal.fire({ text: "Error saving data", icon: "error", buttonsStyling: false, confirmButtonText: "Ok!", customClass: { confirmButton: "btn btn-primary" } });
                    }
                });
            });

            // Delete Transaction
            $(document).on('click', '.delete-transaction-btn', function() {
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
                            url: "{{ route('money-management.transactions.destroy', ':id') }}".replace(':id', id),
                            type: "DELETE",
                            data: { _token: "{{ csrf_token() }}" },
                            success: function(response) {
                                table.draw();
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
