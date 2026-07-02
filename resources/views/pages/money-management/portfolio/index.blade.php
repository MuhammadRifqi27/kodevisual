<x-default-layout>
    @section('title')
        Portfolio & Savings
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('money-management.portfolio') }}
    @endsection

    <!--begin::Card-->
    <div class="card card-flush shadow-sm">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    <input type="text" id="portfolio_search"
                        class="form-control form-control-solid w-250px ps-12" placeholder="Search My Accounts..." />
                </div>
            </div>
            <div class="card-toolbar">
                <button type="button" class="btn btn-primary" id="btn_add_portfolio">
                    <i class="ki-duotone ki-plus fs-2"></i> Add New Account
                </button>
            </div>
        </div>
        <div class="card-body">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="table_portfolio">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-50px">No</th>
                        <th class="min-w-150px">Account Name</th>
                        <th class="min-w-150px">Provider</th>
                        <th class="min-w-150px">Balance</th>
                        <th class="text-end min-w-100px">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold"></tbody>
            </table>
        </div>
    </div>
    <!--end::Card-->

    <!-- Modal Add/Edit Portfolio -->
    <div class="modal fade" id="modal_add_portfolio" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-500px">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold" id="modal_portfolio_title">Register My Account</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <div class="modal-body py-10 px-lg-17">
                    <form id="form_add_portfolio">
                        @csrf
                        <input type="hidden" name="id" id="portfolio_id" />
                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Platform / Bank Provider</label>
                            <select name="finance_investment_id" id="portfolio_investment_id" class="form-select form-select-solid" data-control="select2" data-dropdown-parent="#modal_add_portfolio" data-placeholder="Select Provider" data-minimum-results-for-search="0">
                                <option></option>
                                @foreach($globalInvestments as $inv)
                                    <option value="{{ $inv->id }}">{{ $inv->name }} ({{ $inv->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">My Account Name</label>
                            <input type="text" name="account_name" id="portfolio_account_name" class="form-control form-control-solid" placeholder="e.g. My Savings, Business Wallet" required />
                        </div>
                        <div class="fv-row mb-7">
                            <label class="fs-6 fw-semibold mb-2">Account Number (Optional)</label>
                            <input type="text" name="account_number" id="portfolio_account_number" class="form-control form-control-solid" placeholder="e.g. 123-456-789" />
                        </div>
                        <div class="fv-row mb-7">
                            <label class="fs-6 fw-semibold mb-2">Description</label>
                            <textarea name="description" id="portfolio_description" class="form-control form-control-solid" rows="2"></textarea>
                        </div>
                        <div class="fv-row mb-7">
                            <div class="form-check form-switch form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" value="1" name="account_investment" id="account_investment" />
                                <label class="form-check-label fs-6 fw-semibold" for="account_investment">
                                    Investment Account (Crypto / Stocks)
                                </label>
                            </div>
                        </div>
                        <div class="text-center pt-15">
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

    @push('scripts')
    <script>
        $(document).ready(function() {
            let table = $('#table_portfolio').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('money-management.portfolio.datatable') }}",
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'code_name', name: 'account_name'},
                    {data: 'investment.name', name: 'investment.name'},
                    {data: 'balance', name: 'balance', searchable: false},
                    {data: 'action', name: 'action', orderable: false, searchable: false, className: "text-end"},
                ]
            });

            $('#btn_add_portfolio').click(function() {
                $('#form_add_portfolio')[0].reset();
                $('#portfolio_id').val('');
                $('#portfolio_investment_id').val('').trigger('change');
                $('#modal_portfolio_title').text('Register My Account');
                $('#modal_add_portfolio').modal('show');
            });

            $(document).on('click', '.edit-portfolio-btn', function() {
                $('#form_add_portfolio')[0].reset();
                $('#portfolio_id').val($(this).data('id'));
                $('#portfolio_investment_id').val($(this).data('finance_investment_id')).trigger('change');
                $('#portfolio_account_name').val($(this).data('account_name'));
                $('#portfolio_account_number').val($(this).data('account_number'));
                $('#portfolio_description').val($(this).data('description'));
                $('#account_investment').prop('checked', $(this).data('account_investment') == 1);
                $('#modal_portfolio_title').text('Edit My Account');
                $('#modal_add_portfolio').modal('show');
            });

            $('#form_add_portfolio').submit(function(e) {
                e.preventDefault();
                let btn = $(this).find('button[type="submit"]');
                btn.attr('data-kt-indicator', 'on').prop('disabled', true);

                let id = $('#portfolio_id').val();
                let url = id ?
                    "{{ url('money-management/portfolio') }}/" + id :
                    "{{ route('money-management.portfolio.store') }}";

                $.ajax({
                    url: url,
                    method: id ? "PUT" : "POST",
                    data: $(this).serialize(),
                    success: function(res) {
                        Swal.fire({ text: res.success, icon: "success" });
                        $('#modal_add_portfolio').modal('hide');
                        $('#form_add_portfolio')[0].reset();
                        table.ajax.reload();
                    },
                    error: function(err) {
                        Swal.fire({ text: err.responseJSON.message || "Error occurred", icon: "error" });
                    },
                    complete: function() {
                        btn.removeAttr('data-kt-indicator').prop('disabled', false);
                    }
                });
            });

            $(document).on('click', '.delete-portfolio-btn', function() {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Hapus Akun?',
                    text: "Data transaksi terkait akan tetap ada, tapi akun ini tidak bisa digunakan lagi.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('money-management/portfolio') }}/" + id,
                            method: "DELETE",
                            data: { _token: "{{ csrf_token() }}" },
                            success: function(res) {
                                Swal.fire('Deleted!', res.success, 'success');
                                table.ajax.reload();
                            },
                            error: function(err) {
                                Swal.fire('Error!', err.responseJSON.error || "Gagal menghapus", 'error');
                            }
                        });
                    }
                });
            });

            $('#portfolio_search').keyup(function(){
                table.search($(this).val()).draw();
            });
        });
    </script>
    @endpush
</x-default-layout>
