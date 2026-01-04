<x-default-layout>
    @section('title')
    Roles
    @endsection

    @section('breadcrumbs')
    {{ Breadcrumbs::render('dashboard') }}
    @endsection

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3>Roles List</h3>
            </div>
            <div class="card-toolbar">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_role">
                    <i class="ki-duotone ki-plus fs-2"></i> Add Role
                </button>
            </div>
        </div>
        <div class="card-body py-4">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_roles">
                <thead>
                    <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                        <th class="min-w-10px pe-2">No</th>
                        <th class="min-w-125px">Name</th>
                        <th class="min-w-125px">Description</th>
                        <th class="min-w-200px">Permissions</th>
                        <th class="min-w-100px">Created At</th>
                        <th class="text-end min-w-100px">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-bold">
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="kt_modal_role" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-750px">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold" id="modalTitle">Add Role</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                    <form id="kt_modal_role_form" class="form" action="#">
                        @csrf
                        <input type="hidden" name="id" id="role_id">
                        <div class="d-flex flex-column scroll-y me-n7 pe-7" id="kt_modal_role_scroll" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_role_header" data-kt-scroll-wrappers="#kt_modal_role_scroll" data-kt-scroll-offset="300px">
                            
                            <div class="fv-row mb-7">
                                <label class="required fw-semibold fs-6 mb-2">Role Name</label>
                                <input type="text" name="name" id="name" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="Role Name" />
                            </div>
                            
                            <div class="fv-row mb-7">
                                <label class="fw-semibold fs-6 mb-2">Description</label>
                                <input type="text" name="description" id="description" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="Description" />
                            </div>

                            <div class="fv-row">
                                <label class="fw-semibold fs-6 mb-2">Permissions</label>
                                <div class="row">
                                    @foreach($permissions as $permission)
                                    <div class="col-md-4 mb-2">
                                        <label class="form-check form-check-custom form-check-solid align-items-start">
                                            <input class="form-check-input me-3" type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="permission_{{ $permission->id }}" />
                                            <span class="form-check-label d-flex flex-column align-items-start">
                                                <span class="fw-bold fs-5 mb-0">{{ $permission->name }}</span>
                                                <span class="text-muted fs-7">{{ $permission->description }}</span>
                                            </span>
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                        </div>
                        <div class="text-center pt-15">
                            <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Discard</button>
                            <button type="submit" class="btn btn-primary" id="kt_modal_role_submit">
                                <span class="indicator-label">Submit</span>
                                <span class="indicator-progress">Please wait...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
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
            var table = $('#kt_table_roles').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('administrator.roles.datatable') }}",
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'name', name: 'name'},
                    {data: 'description', name: 'description'},
                    {data: 'permissions', name: 'permissions', orderable: false, searchable: false},
                    {data: 'created_at', name: 'created_at'},
                    {data: 'action', name: 'action', orderable: false, searchable: false, className: "text-end"},
                ]
            });

            $('#kt_modal_role_form').on('submit', function(e) {
                e.preventDefault();
                var id = $('#role_id').val();
                var url = id ? "{{ url('administrator/roles/update') }}/" + id : "{{ route('administrator.roles.store') }}";
                var type = id ? 'PUT' : 'POST';

                $.ajax({
                    url: url,
                    type: type,
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#kt_modal_role').modal('hide');
                        table.ajax.reload();
                        Swal.fire('Success!', response.success, 'success');
                        $('#kt_modal_role_form')[0].reset();
                        $('#role_id').val('');
                        $('input[name="permissions[]"]').prop('checked', false);
                        $('#modalTitle').text('Add Role');
                    },
                    error: function(xhr) {
                        var errors = xhr.responseJSON.errors;
                        var errorMessage = '';
                        $.each(errors, function(key, value) {
                            errorMessage += value[0] + '<br>';
                        });
                        Swal.fire('Error!', errorMessage, 'error');
                    }
                });
            });

            $(document).on('click', '.edit-btn', function() {
                var id = $(this).data('id');
                $.get("{{ url('administrator/roles/edit') }}/" + id, function(data) {
                    $('#modalTitle').text('Edit Role');
                    $('#role_id').val(data.role.id);
                    $('#name').val(data.role.name);
                    $('#description').val(data.role.description);
                    
                    // Reset checkboxes
                    $('input[name="permissions[]"]').prop('checked', false);
                    
                    // Check assigned permissions
                    $.each(data.rolePermissions, function(index, value) {
                        $('#permission_' + value).prop('checked', true);
                    });

                    $('#kt_modal_role').modal('show');
                });
            });

            $(document).on('click', '.delete-btn', function() {
                var id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('administrator/roles/destroy') }}/" + id,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                Swal.fire('Deleted!', response.success, 'success');
                                table.ajax.reload();
                            }
                        });
                    }
                })
            });

            $('#kt_modal_role').on('hidden.bs.modal', function () {
                $('#kt_modal_role_form')[0].reset();
                $('#role_id').val('');
                $('input[name="permissions[]"]').prop('checked', false);
                $('#modalTitle').text('Add Role');
            });
        });
    </script>
    @endpush
</x-default-layout>
