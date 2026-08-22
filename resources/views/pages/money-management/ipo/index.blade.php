<x-default-layout>
    @section('title')
        IPO Orders
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('money-management.ipo') }}
    @endsection

    <!--begin::Card-->
    <div class="card card-flush shadow-sm">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    <input type="text" id="ipo_search"
                        class="form-control form-control-solid w-250px ps-12" placeholder="Search IPO Orders..." />
                </div>
            </div>
            <div class="card-toolbar">
                <button type="button" class="btn btn-primary" id="btn_add_ipo">
                    <i class="ki-duotone ki-plus fs-2"></i> New IPO Order
                </button>
            </div>
        </div>
        <div class="card-body">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="table_ipo">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-50px">No</th>
                        <th class="min-w-100px">Emiten</th>
                        <th class="min-w-150px">Broker Account</th>
                        <th class="min-w-100px">Order Date</th>
                        <th class="min-w-100px">Price/Share</th>
                        <th class="min-w-80px">Lot Order</th>
                        <th class="min-w-80px">Lot Allotted</th>
                        <th class="min-w-100px">Status</th>
                        <th class="min-w-150px">Amount</th>
                        <th class="text-end min-w-120px">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold"></tbody>
            </table>
        </div>
    </div>
    <!--end::Card-->

    <!-- Modal Add/Edit IPO Order -->
    <div class="modal fade" id="modal_ipo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-500px">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold" id="modal_ipo_title">New IPO Order</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <div class="modal-body py-10 px-lg-17">
                    <form id="form_ipo">
                        @csrf
                        <input type="hidden" name="id" id="ipo_id" />
                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Broker Account</label>
                            <select name="finance_investment_id" id="ipo_finance_investment_id" class="form-select form-select-solid" data-control="select2" data-dropdown-parent="#modal_ipo" data-placeholder="Select Stock Broker" data-minimum-results-for-search="0" required>
                                <option></option>
                                @foreach($stockPortfolios as $p)
                                    <option value="{{ $p->id }}">{{ $p->account_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Emiten</label>
                            <input type="text" name="asset" id="ipo_asset" class="form-control form-control-solid" placeholder="e.g. COIN" required />
                        </div>
                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Order Date</label>
                            <input type="date" name="order_date" id="ipo_order_date" class="form-control form-control-solid" required />
                        </div>
                        <div class="row g-9 mb-7">
                            <div class="col-md-6 fv-row">
                                <label class="required fs-6 fw-semibold mb-2">Price / Share</label>
                                <input type="number" step="0.01" min="0.01" name="price_per_share" id="ipo_price_per_share" class="form-control form-control-solid" placeholder="e.g. 1000" required />
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="required fs-6 fw-semibold mb-2">Lot Ordered</label>
                                <input type="number" step="1" min="1" name="lot_ordered" id="ipo_lot_ordered" class="form-control form-control-solid" placeholder="e.g. 50" required />
                            </div>
                        </div>
                        <div class="fv-row mb-7">
                            <label class="fs-6 fw-semibold mb-2">Description</label>
                            <textarea name="description" id="ipo_description" class="form-control form-control-solid" rows="2"></textarea>
                        </div>
                        <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-6">
                            <div class="fs-7 text-gray-700">Dana sejumlah Lot &times; 100 &times; Harga akan langsung diblokir dari saldo broker sampai hasil penjatahan dikonfirmasi.</div>
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

    <!-- Modal Confirm Allotment -->
    <div class="modal fade" id="modal_allotment" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-500px">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold">Confirm Allotment</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <div class="modal-body py-10 px-lg-17">
                    <form id="form_allotment">
                        @csrf
                        <input type="hidden" name="id" id="allotment_id" />
                        <div class="fv-row mb-7">
                            <label class="fw-bold fs-6" id="allotment_summary"></label>
                        </div>
                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Lot Allotted</label>
                            <input type="number" step="1" min="0" name="lot_allotted" id="allotment_lot_allotted" class="form-control form-control-solid" placeholder="e.g. 20" required />
                        </div>
                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Allotment Date</label>
                            <input type="date" name="allotment_date" id="allotment_date" class="form-control form-control-solid" required />
                        </div>
                        <div class="notice d-flex bg-light-info rounded border-info border border-dashed p-6">
                            <div class="fs-7 text-gray-700" id="allotment_preview">-</div>
                        </div>
                        <div class="text-center pt-10">
                            <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <span class="indicator-label">Confirm</span>
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
            let table = $('#table_ipo').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('money-management.ipo.datatable') }}",
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'asset', name: 'asset'},
                    {data: 'portfolio_name', name: 'portfolio_name'},
                    {data: 'order_date', name: 'order_date'},
                    {data: 'price_per_share', name: 'price_per_share'},
                    {data: 'lot_ordered', name: 'lot_ordered'},
                    {data: 'lot_allotted', name: 'lot_allotted', defaultContent: '-'},
                    {data: 'status', name: 'status'},
                    {data: 'amount', name: 'amount', orderable: false, searchable: false},
                    {data: 'action', name: 'action', orderable: false, searchable: false, className: "text-end"},
                ],
                order: [[3, 'desc']]
            });

            $('#ipo_search').keyup(function(){
                table.search($(this).val()).draw();
            });

            $('#btn_add_ipo').click(function() {
                $('#form_ipo')[0].reset();
                $('#ipo_id').val('');
                $('#ipo_finance_investment_id').val('').trigger('change');
                $('#ipo_order_date').val(new Date().toISOString().split('T')[0]);
                $('#modal_ipo_title').text('New IPO Order');
                $('#modal_ipo').modal('show');
            });

            $(document).on('click', '.edit-ipo-btn', function() {
                $('#form_ipo')[0].reset();
                $('#ipo_id').val($(this).data('id'));
                $('#ipo_finance_investment_id').val($(this).data('finance_investment_id')).trigger('change');
                $('#ipo_asset').val($(this).data('asset'));
                $('#ipo_order_date').val($(this).data('order_date'));
                $('#ipo_price_per_share').val($(this).data('price_per_share'));
                $('#ipo_lot_ordered').val($(this).data('lot_ordered'));
                $('#ipo_description').val($(this).data('description'));
                $('#modal_ipo_title').text('Edit IPO Order');
                $('#modal_ipo').modal('show');
            });

            $('#form_ipo').submit(function(e) {
                e.preventDefault();
                let btn = $(this).find('button[type="submit"]');
                btn.attr('data-kt-indicator', 'on').prop('disabled', true);

                let id = $('#ipo_id').val();
                let url = id ?
                    "{{ url('money-management/ipo') }}/" + id :
                    "{{ route('money-management.ipo.store') }}";

                $.ajax({
                    url: url,
                    method: id ? 'PUT' : 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        Swal.fire({ text: res.success, icon: "success" });
                        $('#modal_ipo').modal('hide');
                        table.ajax.reload();
                    },
                    error: function(err) {
                        Swal.fire({ text: err.responseJSON.error || err.responseJSON.message || "Error occurred", icon: "error" });
                    },
                    complete: function() {
                        btn.removeAttr('data-kt-indicator').prop('disabled', false);
                    }
                });
            });

            $(document).on('click', '.allotment-ipo-btn', function() {
                $('#form_allotment')[0].reset();
                let asset = $(this).data('asset');
                let lotOrdered = $(this).data('lot_ordered');
                let price = parseFloat($(this).data('price_per_share'));

                $('#allotment_id').val($(this).data('id'));
                $('#allotment_summary').text(asset + ' - Order: ' + lotOrdered + ' lot @ Rp ' + price.toLocaleString('id-ID'));
                $('#allotment_lot_allotted').attr('max', lotOrdered).data('lot-ordered', lotOrdered).data('price', price);
                $('#allotment_date').val(new Date().toISOString().split('T')[0]);
                $('#allotment_preview').text('-');
                $('#modal_allotment').modal('show');
            });

            $('#allotment_lot_allotted').on('input', function() {
                let lotOrdered = parseInt($(this).data('lot-ordered'));
                let price = parseFloat($(this).data('price'));
                let lotAllotted = parseInt($(this).val()) || 0;
                let orderAmount = lotOrdered * 100 * price;
                let allottedAmount = lotAllotted * 100 * price;
                let refund = orderAmount - allottedAmount;
                $('#allotment_preview').text(
                    'Allotted: Rp ' + allottedAmount.toLocaleString('id-ID') + ' (' + lotAllotted + ' lot) | Refund: Rp ' + refund.toLocaleString('id-ID')
                );
            });

            $('#form_allotment').submit(function(e) {
                e.preventDefault();
                let btn = $(this).find('button[type="submit"]');
                btn.attr('data-kt-indicator', 'on').prop('disabled', true);
                let id = $('#allotment_id').val();

                $.ajax({
                    url: "{{ url('money-management/ipo') }}/" + id + "/allotment",
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        Swal.fire({ text: res.success, icon: "success" });
                        $('#modal_allotment').modal('hide');
                        table.ajax.reload();
                    },
                    error: function(err) {
                        Swal.fire({ text: err.responseJSON.error || err.responseJSON.message || "Error occurred", icon: "error" });
                    },
                    complete: function() {
                        btn.removeAttr('data-kt-indicator').prop('disabled', false);
                    }
                });
            });

            $(document).on('click', '.delete-ipo-btn', function() {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Hapus Pesanan IPO?',
                    text: "Semua transaksi terkait (blokir dana, penjatahan, refund) akan ikut terhapus.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('money-management/ipo') }}/" + id,
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
        });
    </script>
    @endpush
</x-default-layout>
