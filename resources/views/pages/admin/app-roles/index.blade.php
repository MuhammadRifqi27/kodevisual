<x-default-layout>
    @section('title')
    App Roles Management
    @endsection

    @section('breadcrumbs')
    {{ Breadcrumbs::render('dashboard') }}
    @endsection

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <h3>Roles List: {{ $selectedApp->name ?? 'All Applications' }}</h3>
            </div>
            <div class="card-toolbar">
                <a href="{{ route('administrator.apps.index') }}" class="btn btn-light-primary me-3">
                    <i class="ki-duotone ki-arrow-left fs-2"></i> Back to Apps
                </a>
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
                        <th class="min-w-125px">Application</th>
                        <th class="min-w-125px">Role Name</th>
                        <th class="min-w-125px">Role Code</th>
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
                    <h2 class="fw-bold" id="modalTitle">Add App Role</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"></i>
                    </div>
                </div>
                <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                    <form id="kt_modal_role_form" class="form" action="#">
                        @csrf
                        <input type="hidden" name="id" id="role_id">
                        <div class="d-flex flex-column scroll-y me-n7 pe-7" id="kt_modal_role_scroll" data-kt-scroll="true" data-kt-scroll-max-height="auto" data-kt-scroll-offset="300px">
                            <div class="row g-9 mb-7">
                                <div class="col-md-6 fv-row">
                                    <label class="required fw-semibold fs-6 mb-2">Application</label>
                                    <select name="app_id" id="app_id" class="form-select form-select-solid" data-control="select2" data-placeholder="Select App" data-dropdown-parent="#kt_modal_role">
                                        <option></option>
                                        @foreach($apps as $app)
                                            <option value="{{ $app->id }}" {{ (isset($selectedApp) && $selectedApp->id == $app->id) ? 'selected' : '' }}>{{ $app->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 fv-row">
                                    <label class="required fw-semibold fs-6 mb-2">Role Name</label>
                                    <input type="text" name="name" id="name" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="e.g. Finance Manager" />
                                </div>
                            </div>
                            <div class="row g-9 mb-7">
                                <div class="col-md-6 fv-row">
                                    <label class="required fw-semibold fs-6 mb-2">Role Code</label>
                                    <input type="text" name="code" id="code" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="e.g. finance_manager" />
                                </div>
                            </div>

                            <div class="fv-row">
                                <label class="fw-semibold fs-6 mb-5">Permissions</label>
                                <div id="permissions_container" class="row">
                                    <div class="col-12 text-muted italic">Please select an application first to see available permissions.</div>
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
        const allPermissions = @json($permissions);

        $(document).ready(function() {
            var table = $('#kt_table_roles').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('administrator.app-roles.datatable', ['app_id' => request('app_id')]) }}",
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'app.name', name: 'app.name'},
                    {data: 'name', name: 'name'},
                    {data: 'code', name: 'code'},
                    {data: 'action', name: 'action', orderable: false, searchable: false, className: "text-end"},
                ]
            });

            $('#app_id').on('change', function() {
                const appId = $(this).val();
                const container = $('#permissions_container');
                container.empty();

                if (appId && allPermissions[appId]) {
                    allPermissions[appId].forEach(perm => {
                        container.append(`
                            <div class="col-md-6 mb-2">
                                <div class="form-check form-check-custom form-check-solid">
                                    <input class="form-check-input permission-checkbox" type="checkbox" name="permissions[]" value="${perm.id}" id="perm_${perm.id}" />
                                    <label class="form-check-label" for="perm_${perm.id}">
                                        ${perm.name} (${perm.code})
                                    </label>
                                </div>
                            </div>
                        `);
                    });
                } else if (appId) {
                    container.append('<div class="col-12 text-danger">No permissions found for this application.</div>');
                } else {
                    container.append('<div class="col-12 text-muted">Please select an application first.</div>');
                }
            });

            $('#kt_modal_role_form').on('submit', function(e) {
                e.preventDefault();
                var id = $('#role_id').val();
                var url = id ? "{{ url('administrator/app-roles/update') }}/" + id : "{{ route('administrator.app-roles.store') }}";
                var type = id ? 'PUT' : 'POST';

                $.ajax({
                    url: url,
                    type: type,
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#kt_modal_role').modal('hide');
                        table.ajax.reload();
                        Swal.fire('Success!', response.success, 'success');
                    },
                    error: function(xhr) {
                        var errorMessage = xhr.responseJSON.message || 'Error occurred';
                        Swal.fire('Error!', errorMessage, 'error');
                    }
                });
            });

            $(document).on('click', '.edit-btn', function() {
                var id = $(this).data('id');
                $.get("{{ url('administrator/app-roles/edit') }}/" + id, function(data) {
                    $('#modalTitle').text('Edit App Role');
                    $('#role_id').val(data.id);
                    $('#app_id').val(data.app_id).trigger('change');
                    $('#name').val(data.name);
                    $('#code').val(data.code);
                    
                    setTimeout(() => {
                        data.permissions.forEach(p => {
                            $(`#perm_${p.id}`).prop('checked', true);
                        });
                    }, 100);

                    $('#kt_modal_role').modal('show');
                });
            });

            $(document).on('click', '.delete-btn', function() {
                var id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('administrator/app-roles/destroy') }}/" + id,
                            type: 'DELETE',
                            data: { _token: '{{ csrf_token() }}' },
                            success: function(response) {
                                Swal.fire('Deleted!', response.success, 'success');
                                table.ajax.reload();
                            }
                        });
                    }
                })
            });

            $('#kt_modal_role').on('shown.bs.modal', function () {
                if ($('#app_id').val()) {
                    $('#app_id').trigger('change');
                }
            });

            $('#kt_modal_role').on('hidden.bs.modal', function () {
                $('#kt_modal_role_form')[0].reset();
                $('#role_id').val('');
                $('#app_id').val(null).trigger('change');
            });
        });
    </script>
    @endpush
</x-default-layout>
