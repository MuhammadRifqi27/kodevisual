<x-default-layout>

    @section('title')
    Dashboard testing
    @endsection

    @section('breadcrumbs')
    {{ Breadcrumbs::render('detail-expenses-rifqi') }}
    @endsection

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h2 class="text-gray-800 fw-bold mb-0">Rincian Pengeluaran</h2>
            </div>
            <div class="card-toolbar">
                <div class="d-flex justify-content-end">
                    <div class="btn-group me-2">
                        <div class="card-toolbar d-flex flex-wrap justify-content-end gap-2">
                            <div class="me-2">
                                <div class="d-flex align-items-center position-relative my-1">
                                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                    <input type="text" data-kt_detail_expenses_table-filter="search"
                                        class="form-control form-control-solid w-250px ps-12" placeholder="Cari..." />
                                </div>
                            </div>
                            <div class="me-2">
                                <button type="button" class="btn btn-primary rounded" data-bs-toggle="modal" data-bs-target="#addDetailExpensesModal">
                                    <i class="ki-duotone ki-plus-square">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                    Add Detail Expenses
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body pt-0">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_detail_expenses_table">
                <thead>
                    <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th class="text-center">#</th>
                        <th class="text-center min-w-100px">Created Time</th>
                        <th class="text-center min-w-100px">Detail Expenses</th>
                        <th class="text-center min-w-100px">Cost</th>
                        <th class="text-center min-w-100px">Category</th>
                        <th class="text-center min-w-100px">Aksi</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-center text-gray-600">
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="addDetailExpensesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold">Add Detail Expenses</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </div>
                </div>
                <div class="modal-body py-lg-10 px-lg-10">
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="exampleFormControlInput1" class="required form-label">Detail Expenses</label>
                            <input type="text" class="form-control form-control-solid" placeholder="Nama Pengeluaran" />
                        </div>
                        <div class="col">
                            <label for="exampleFormControlInput1" class="required form-label">Cost</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control form-control-lg" id="total-nominal" placeholder="Masukkan Nominal..." autocomplete="off" />
                            </div>
                        </div>
                        <div class="col">
                            <label for="exampleFormControlInput1" class="required form-label">Category</label>
                            <select class="form-select" data-control="select2" data-placeholder="Pilih kategori...">
                                <option></option>
                                <option value="1">Option 1</option>
                                <option value="2">Option 2</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" id="kt_bukti_submit" class="btn btn-primary">
                        <span class="indicator-label">
                            <i class="ki-duotone ki-save-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i> Save
                        </span>
                        <span class="indicator-progress">Please wait...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            var datatable = $('#kt_detail_expenses_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('financial_summary.detailExpenses.datatable.rifqi') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: '#',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'created_time',
                        name: 'created_time',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'detail_expenses',
                        name: 'detail_expenses',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'cost',
                        name: 'cost',
                        orderable: true,
                        searchable: true,
                    },
                    {
                        data: 'master_category.name_category',
                        name: 'master_category.name_category',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'action',
                        name: 'Action',
                        orderable: false,
                        searchable: false
                    },
                ],
                columnDefs: [{
                    targets: 2,
                    className: 'text-center'
                }]
            });

            const filterSearch = document.querySelector('[data-kt-payment-method-table-filter="search"]');
            filterSearch.addEventListener('keyup', function(e) {
                datatable.search(e.target.value).draw();
            });

            // Handle create form submission
            $('#createForm').on('submit', function(e) {
                e.preventDefault();
                $('.error-name').text('');

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#createModal').modal('hide');
                        datatable.ajax.reload();
                        Swal.fire({
                            text: response.message,
                            icon: "success",
                            buttonsStyling: false,
                            confirmButtonText: "Ok, mengerti!",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        });
                        $('#createForm')[0].reset();
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            if (errors.name) {
                                $('.error-name').text(errors.name[0]);
                            }
                        }
                    }
                });
            });

            // Delete button handler
            $(document).on('click', '.delete-btn', function() {
                let id = $(this).data('id');
                Swal.fire({
                    text: "Apakah Anda Yakin Ingin Menghapus Data Ini?",
                    icon: "warning",
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: "Ya, hapus!",
                    cancelButtonText: "Tidak, batalkan",
                    customClass: {
                        confirmButton: "btn btn-primary",
                        cancelButton: "btn btn-active-light"
                    }
                }).then(function(result) {
                    if (result.value) {
                        $.ajax({
                            url: "",
                            type: 'DELETE',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        text: response.message,
                                        icon: "success",
                                        buttonsStyling: false,
                                        confirmButtonText: "Ok, mengerti!",
                                        customClass: {
                                            confirmButton: "btn btn-primary"
                                        }
                                    }).then(function() {
                                        datatable.ajax.reload();
                                    });
                                }
                            }
                        });
                    }
                });
            });

            // Reset form when modal is closed
            $('#createModal').on('hidden.bs.modal', function() {
                $('#createForm')[0].reset();
                $('.error-name').text('');
            });

            $('#editModal').on('hidden.bs.modal', function() {
                $('.error-name').text('');
            });
        });
    </script>
    @endpush

</x-default-layout>