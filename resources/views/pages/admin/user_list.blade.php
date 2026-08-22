<x-default-layout>
    @section('title')
    Users List
    @endsection

    @section('breadcrumbs')
    {{ Breadcrumbs::render('dashboard') }}
    @endsection

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3>Users List</h3>
            </div>
        </div>
        <div class="card-body py-4">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_users_list">
                <thead>
                    <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                        <th class="min-w-10px pe-2">No</th>
                        <th class="min-w-125px">Name</th>
                        <th class="min-w-125px">Username</th>
                        <th class="min-w-125px">Email</th>
                        <th class="min-w-125px">Role</th>
                        <th class="min-w-125px">Status</th>
                        <th class="min-w-100px">Created At</th>
                        <th class="text-end min-w-100px">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-bold">
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="kt_modal_edit_user" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold">Edit User</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                    <form id="kt_modal_edit_user_form" class="form" action="#">
                        @csrf
                        <input type="hidden" name="id" id="edit_user_id">

                        <div class="fv-row mb-7">
                            <label class="required fw-semibold fs-6 mb-2">Name</label>
                            <input type="text" name="name" id="edit_user_name" class="form-control form-control-solid" placeholder="Name" />
                        </div>

                        <div class="fv-row mb-7">
                            <label class="fw-semibold fs-6 mb-2">Username</label>
                            <input type="text" name="username" id="edit_user_username" class="form-control form-control-solid" placeholder="Username" />
                        </div>

                        <div class="fv-row mb-7">
                            <label class="required fw-semibold fs-6 mb-2">Email</label>
                            <input type="email" name="email" id="edit_user_email" class="form-control form-control-solid" placeholder="Email" />
                        </div>

                        <div class="row">
                            <div class="col-md-6 fv-row mb-7">
                                <label class="fw-semibold fs-6 mb-2">Role</label>
                                <select name="role_id" id="edit_user_role_id" class="form-select form-select-solid">
                                    <option value="">No Role</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ ucfirst($role->name) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 fv-row mb-7">
                                <label class="required fw-semibold fs-6 mb-2">Status</label>
                                <select name="is_approved" id="edit_user_is_approved" class="form-select form-select-solid">
                                    <option value="1">Approved</option>
                                    <option value="0">Pending</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 fv-row mb-7">
                                <label class="fw-semibold fs-6 mb-2">New Password</label>
                                <input type="password" name="password" id="edit_user_password" class="form-control form-control-solid" placeholder="Leave blank to keep current password" autocomplete="new-password" />
                            </div>

                            <div class="col-md-6 fv-row mb-7">
                                <label class="fw-semibold fs-6 mb-2">Confirm Password</label>
                                <input type="password" name="password_confirmation" id="edit_user_password_confirmation" class="form-control form-control-solid" placeholder="Confirm new password" autocomplete="new-password" />
                            </div>
                        </div>

                        <div class="text-center pt-10">
                            <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Discard</button>
                            <button type="submit" class="btn btn-primary" id="kt_modal_edit_user_submit">
                                <span class="indicator-label">Save Changes</span>
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
            var table = $('#kt_table_users_list').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('administrator.user-approval.listing.datatable') }}",
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'name', name: 'name'},
                    {data: 'username', name: 'username'},
                    {data: 'email', name: 'email'},
                    {data: 'role_name', name: 'role.name'},
                    {data: 'status', name: 'is_approved'},
                    {data: 'created_at', name: 'created_at'},
                    {data: 'action', name: 'action', orderable: false, searchable: false, className: "text-end"},
                ]
            });

            $(document).on('click', '.edit-btn', function() {
                var id = $(this).data('id');
                $.get("{{ url('administrator/user-approval/edit') }}/" + id, function(response) {
                    var user = response.user;
                    $('#edit_user_id').val(user.id);
                    $('#edit_user_name').val(user.name);
                    $('#edit_user_username').val(user.username);
                    $('#edit_user_email').val(user.email);
                    $('#edit_user_role_id').val(user.role_id);
                    $('#edit_user_is_approved').val(user.is_approved ? '1' : '0');
                    $('#edit_user_password').val('');
                    $('#edit_user_password_confirmation').val('');

                    $('#kt_modal_edit_user').modal('show');
                });
            });

            $('#kt_modal_edit_user_form').on('submit', function(e) {
                e.preventDefault();
                var id = $('#edit_user_id').val();

                $.ajax({
                    url: "{{ url('administrator/user-approval/update') }}/" + id,
                    type: 'PUT',
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#kt_modal_edit_user').modal('hide');
                        table.ajax.reload();
                        Swal.fire('Success!', response.success, 'success');
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

            $('#kt_modal_edit_user').on('hidden.bs.modal', function () {
                $('#kt_modal_edit_user_form')[0].reset();
                $('#edit_user_id').val('');
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
                            url: "{{ url('administrator/user-approval/destroy') }}/" + id,
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
        });
    </script>
    @endpush
</x-default-layout>
