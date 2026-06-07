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
                <div class="d-flex justify-content-end align-items-center gap-2 gap-md-3">
                    <!-- Lihat Saldo -->
                    <button type="button" class="btn btn-light-primary btn-md" id="btn_lihat_saldo">
                        <i class="ki-duotone ki-eye fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Lihat Saldo
                    </button>
                    <!-- Filter Toggle Button -->
                    <button type="button" class="btn btn-light-info btn-md" id="kt_transaction_filter_drawer_toggle">
                        <i class="ki-duotone ki-filter fs-2"><span class="path1"></span><span class="path2"></span></i> Filter
                        <span class="badge badge-circle badge-info ms-2 d-none" id="filter_count_badge">0</span>
                    </button>
                    <button type="button" class="btn btn-primary" id="btn_add_transaction">
                        <i class="ki-duotone ki-plus fs-2"></i> Add
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Summary Stats -->
            <div class="row g-5 mb-10 mt-n5">
                <div class="col-md-4">
                    <div class="card card-dashed flex-center min-w-175px my-3 p-6 bg-light-success border-success border-dashed">
                        <span class="fs-4 fw-semibold text-success pb-1 px-2">Total Income</span>
                        <span class="fs-2tx fw-boldest" id="stat_total_income">******</span>
                        <span class="fs-7 fw-semibold opacity-50 mt-1 active_period_label">Overall</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-dashed flex-center min-w-175px my-3 p-6 bg-light-danger border-danger border-dashed">
                        <span class="fs-4 fw-semibold text-danger pb-1 px-2">Total Expense</span>
                        <span class="fs-2tx fw-boldest" id="stat_total_expense">******</span>
                        <span class="fs-7 fw-semibold opacity-50 mt-1 active_period_label">Overall</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-dashed flex-center min-w-175px my-3 p-6 bg-light-primary border-primary border-dashed">
                        <span class="fs-4 fw-semibold text-primary pb-1 px-2">Net Balance</span>
                        <span class="fs-2tx fw-boldest" id="stat_net_balance">******</span>
                        <span class="fs-7 fw-semibold opacity-50 mt-1 active_period_label">Overall</span>
                    </div>
                </div>
            </div>

            <table class="table align-middle table-bordered fs-6 gy-5" id="table_transactions">
                <thead>
                    <tr class="fw-bold fs-7 text-uppercase bg-secondary gs-0">
                        <th class="text-center min-w-50px">No</th>
                        <th class="text-center min-w-100px">TRX Number</th>
                        <th class="text-center min-w-100px">Date</th>
                        <th class="text-center min-w-100px">Type</th>
                        <th class="text-center min-w-150px">Category</th>
                        <th class="text-center min-w-150px">Account/Portfolio</th>
                        <th class="text-center min-w-150px">Amount</th>
                        <th class="text-center min-w-200px">Description</th>
                        <th class="text-center min-w-100px">Actions</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-center"></tbody>
            </table>
        </div>
    </div>
    <!--end::Card-->

    <!-- BEGIN::Drawer Filter -->
    <div
        id="kt_transaction_filter_drawer"
        class="bg-white"
        data-kt-drawer="true"
        data-kt-drawer-activate="true"
        data-kt-drawer-toggle="#kt_transaction_filter_drawer_toggle"
        data-kt-drawer-close="#kt_transaction_filter_drawer_close"
        data-kt-drawer-width="{default:'300px', 'md': '400px'}"
        data-kt-drawer-direction="end"
    >
        <div class="card w-100 rounded-0 border-0">
            <div class="card-header pe-5">
                <div class="card-title">
                    <div class="d-flex justify-content-center flex-column me-3">
                        <span class="fs-4 fw-bold text-gray-900 fw-boldest me-1 lh-1">Filter Transactions</span>
                    </div>
                </div>
                <div class="card-toolbar">
                    <div class="btn btn-sm btn-icon btn-active-light-danger" id="kt_transaction_filter_drawer_close">
                        <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Date Range -->
                <div class="mb-10">
                    <label class="form-label fw-bold fs-6 mb-3">Date Range</label>
                    <div class="position-relative">
                        <i class="ki-duotone ki-calendar-8 fs-2 position-absolute top-50 translate-middle-y ms-4">
                            <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span>
                        </i>
                        <input class="form-control form-control-solid ps-12" placeholder="Select date range" id="filter_daterange" />
                    </div>
                </div>

                <!-- Type -->
                <div class="mb-10">
                    <label class="form-label fw-bold fs-6 mb-3">Transaction Type</label>
                    <select id="filter_type" class="form-select form-select-solid" data-control="select2" data-hide-search="true" data-dropdown-parent="#kt_transaction_filter_drawer">
                        <option value="all">All Types</option>
                        <option value="income">Income</option>
                        <option value="expense">Expense</option>
                    </select>
                </div>

                <!-- Category -->
                <div class="mb-10">
                    <label class="form-label fw-bold fs-6 mb-3">Category</label>
                    <select id="filter_category" class="form-select form-select-solid" data-control="select2" data-placeholder="Select Category" data-dropdown-parent="#kt_transaction_filter_drawer">
                        <option value="all">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-end gap-3 py-5">
                <button type="button" class="btn btn-light" id="btn_reset_filter">Reset</button>
                <button type="button" class="btn btn-primary" id="btn_apply_filter">Apply Filter</button>
            </div>
        </div>
    </div>
    <!-- END::Drawer Filter -->

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
                            <select class="form-select form-select-solid" name="type" id="trx_type" data-control="select2" data-dropdown-parent="#modal_transaction" data-minimum-results-for-search="0" required>
                                <option value="income">Income</option>
                                <option value="expense" selected>Expense</option>
                            </select>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="fs-6 fw-semibold mb-2">Source/Destination Portofolio</label>
                            <select class="form-select form-select-solid" name="investment_id" id="trx_investment" data-control="select2" data-placeholder="Select Portfolio (Optional)" data-dropdown-parent="#modal_transaction" data-minimum-results-for-search="0" required>
                                <option></option>
                                @foreach($investments as $inv)
                                    <option value="{{ $inv->id }}">{{ $inv->account_name }}</option>
                                @endforeach
                            </select>
                            <div class="text-muted fs-7">Uang akan bertambah/berkurang dari portofolio yang dipilih.</div>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Category</label>
                            <select class="form-select form-select-solid" name="category_id" id="trx_category" data-control="select2" data-placeholder="Select Category" data-dropdown-parent="#modal_transaction" data-minimum-results-for-search="0" required>
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
                        d.category_id = $('#filter_category').val();
                        
                        // Parse daterange from flatpickr
                        const fp = document.querySelector("#filter_daterange")._flatpickr;
                        if (fp && fp.selectedDates.length === 2) {
                            d.start_date = fp.formatDate(fp.selectedDates[0], "Y-m-d");
                            d.end_date = fp.formatDate(fp.selectedDates[1], "Y-m-d");
                        }
                    }
                },
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'id', name: 'id'},
                    {data: 'date', name: 'date'},
                    {data: 'type', name: 'type'},
                    {data: 'category_name', name: 'category.name'},
                    {data: 'investment_name', name: 'portfolio.account_name'},
                    {data: 'amount', name: 'amount'},
                    {data: 'description', name: 'description'},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                ],
                order: [[1, 'desc']],
                drawCallback: function(settings) {
                    const json = settings.json;
                    if (json) {
                        $('#stat_total_income').attr('data-value', json.total_income);
                        $('#stat_total_expense').attr('data-value', json.total_expense);
                        $('#stat_net_balance').attr('data-value', json.net_balance);
                        
                        updateSaldoVisibility();

                        // Dynamic color for net balance
                        if (json.net_balance_raw < 0) {
                            $('#stat_net_balance').removeClass('text-success').addClass('text-danger');
                        } else {
                            $('#stat_net_balance').removeClass('text-danger').addClass('text-success');
                        }
                    }
                }
            });

            let isSaldoVisible = false;

            function updateSaldoVisibility() {
                const stats = [
                    { id: '#stat_total_income', default: 'Rp 0' },
                    { id: '#stat_total_expense', default: 'Rp 0' },
                    { id: '#stat_net_balance', default: 'Rp 0' }
                ];

                if (isSaldoVisible) {
                    stats.forEach(stat => {
                        const val = $(stat.id).attr('data-value');
                        $(stat.id).text(val ? val : stat.default);
                    });
                    $('#btn_lihat_saldo').html('<i class="ki-duotone ki-eye-slash fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Tutup Saldo');
                } else {
                    stats.forEach(stat => {
                        $(stat.id).text('******');
                    });
                    $('#btn_lihat_saldo').html('<i class="ki-duotone ki-eye fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Lihat Saldo');
                }
            }

            $('#btn_lihat_saldo').click(function() {
                isSaldoVisible = !isSaldoVisible;
                updateSaldoVisibility();
            });

            // Refresh table on filter change
            $('#filter_type, #filter_category, #filter_daterange').change(function() {
                table.draw();
            });

            // Initialize Flatpickr for daterange
            const fp = $("#filter_daterange").flatpickr({
                altInput: true,
                altFormat: "d M Y",
                dateFormat: "Y-m-d",
                mode: "range"
            });

            // Update filter count badge
            function updateFilterCount() {
                let count = 0;
                if ($('#filter_type').val() !== 'all') count++;
                if ($('#filter_category').val() !== 'all') count++;
                if (fp.selectedDates.length === 2) count++;
                
                if (count > 0) {
                    $('#filter_count_badge').text(count).removeClass('d-none');
                } else {
                    $('#filter_count_badge').addClass('d-none');
                }
            }

            $('#btn_apply_filter').click(function() {
                updateFilterCount();
                
                // Update period label in cards
                const daterange = $('#filter_daterange').val();
                if (daterange) {
                    $('.active_period_label').text(daterange);
                } else {
                    $('.active_period_label').text('Overall');
                }

                table.draw();
                // Close drawer
                const drawerElement = document.querySelector("#kt_transaction_filter_drawer");
                const drawer = KTDrawer.getInstance(drawerElement);
                drawer.hide();
            });

            $('#btn_reset_filter').click(function() {
                $('#filter_type').val('all').trigger('change');
                $('#filter_category').val('all').trigger('change');
                fp.clear();
                $('.active_period_label').text('Overall');
                updateFilterCount();
                table.draw();
                
                const drawerElement = document.querySelector("#kt_transaction_filter_drawer");
                const drawer = KTDrawer.getInstance(drawerElement);
                drawer.hide();
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

            // Handle Modal Hidden (Reset Form except Date)
            $('#modal_transaction').on('hidden.bs.modal', function () {
                const currentDate = $('#trx_date').val();
                $('#form_transaction')[0].reset();
                $('#trx_id').val('');
                $('#trx_type').val('expense').trigger('change');
                $('#trx_investment').val('').trigger('change');
                $('#trx_category').val('').trigger('change');
                $('#trx_date').val(currentDate); // Restore date
            });

            // Submit Form
            $('#form_transaction').submit(function(e) {
                e.preventDefault();
                let btn = $(this).find('button[type="submit"]');
                btn.attr('data-kt-indicator', 'on').prop('disabled', true);

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
                        btn.removeAttr('data-kt-indicator').prop('disabled', false);
                        $('#modal_transaction').modal('hide');
                        table.draw();
                        Swal.fire({ text: response.success, icon: "success", buttonsStyling: false, confirmButtonText: "Ok!", customClass: { confirmButton: "btn btn-primary" } });
                    },
                    error: function(xhr) {
                        btn.removeAttr('data-kt-indicator').prop('disabled', false);
                        let errorMessage = "Error saving data";
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            errorMessage = Object.values(xhr.responseJSON.errors).flat().join("<br>");
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }

                        Swal.fire({ 
                            html: errorMessage, 
                            icon: "error", 
                            buttonsStyling: false, 
                            confirmButtonText: "Ok!", 
                            customClass: { confirmButton: "btn btn-primary" } 
                        });
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
