<x-default-layout>
    @section('title')
    Master Data
    @endsection

    @section('breadcrumbs')
    {{ Breadcrumbs::render('dashboard') }}
    @endsection

    <!--begin::Card-->
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    <input type="text" data-kt-category-expenses-table-filter="search"
                        class="form-control form-control-solid w-250px ps-12" placeholder="Cari..." />
                </div>
            </div>
            <div class="card-toolbar">
                <div class="d-flex justify-content-end">
                    {{-- @can('Kategori Pengeluaran-tambah') --}}
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                        <i class="ki-duotone ki-plus-square"><span class="path1"></span><span class="path2"></span><span
                                class="path3"></span></i>
                        Tambah Kategori Pengeluaran
                    </button>
                    {{-- @endcan --}}
                </div>
            </div>
        </div>
        <div class="card-body pt-0">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_category_expenses_table">
                <thead>
                    <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th class="text-center">#</th>
                        <th class="text-center min-w-100px">Nama Kategori Pengeluaran</th>
                        <th class="text-center min-w-100px">Aksi</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-center text-gray-600">

                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Tambah Kategori Pengeluaran</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createForm" action="{{ route('category.store') }}" method="POST">
                    @csrf
                    <div class="modal-body py-10 px-4">
                        <div class="mb-10">
                            <label class="form-label required">Nama Kategori Pengeluaran</label>
                            <input type="text" name="name_category" class="form-control form-control-solid"
                                placeholder="Masukkan Kategori Pengeluaran" required />
                            <div class="text-danger mt-2 error-name_category"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Edit Kategori Pengeluaran</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editForm" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body py-10 px-4">
                        <div class="mb-10">
                            <label class="form-label required">Nama Kategori Pengeluaran</label>
                            <input type="text" name="name_category" class="form-control form-control-solid"
                                placeholder="Masukkan Kategori Pengeluaran" required />
                            <div class="text-danger mt-2 error-name_category"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            var datatable = $('#kt_category_expenses_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('category.datatable') }}",
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
                        data: 'name_category',
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
            });

            const filterSearch = document.querySelector('[data-kt-category-expenses-table-filter="search"]');
            filterSearch.addEventListener('keyup', function(e) {
                datatable.search(e.target.value).draw();
            });

            // Handle create form submission
            $('#createForm').on('submit', function(e) {
                e.preventDefault();
                $('.error-name_category').text('');

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
                            if (errors.name_category) {
                                $('.error-name_category').text(errors.name_category[0]);
                            }
                        }
                    }
                });
            });

            // Handle edit button click
            $(document).on('click', '.edit-btn', function() {
                const id = $(this).data('id');
                const editUrl = "{{ route('category.edit', ':id') }}".replace(':id', id);
                const updateUrl = "{{ route('category.update', ':id') }}".replace(':id', id);

                $.get(editUrl, function(response) {
                    $('#editForm').attr('action', updateUrl);
                    $('#editForm input[name="name_category"]').val(response.name_category);
                    $('#editModal').modal('show');
                });
            });

            // Handle edit form submission
            $('#editForm').on('submit', function(e) {
                e.preventDefault();
                $('.error-name_category').text('');

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize() + '&_method=PUT',
                    success: function(response) {
                        $('#editModal').modal('hide');
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
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            if (errors.name_category) {
                                $('.error-name_category').text(errors.name_category[0]);
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