<x-default-layout>
    @section('title')
    User Application Access: {{ $user->name }}
    @endsection

    @section('breadcrumbs')
    {{ Breadcrumbs::render('dashboard') }}
    @endsection

    <div class="row g-5 g-xl-10">
        <div class="col-xl-4">
            <div class="card card-flush h-xl-100">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-800">Assign Application</span>
                        <span class="text-gray-400 mt-1 fw-semibold fs-6">Give user access to a specific app</span>
                    </h3>
                </div>
                <div class="card-body">
                    <form id="assign_app_form">
                        @csrf
                        <div class="fv-row mb-7">
                            <label class="required fw-semibold fs-6 mb-2">Select Application</label>
                            <select name="app_id" id="app_id" class="form-select form-select-solid" data-control="select2" data-placeholder="Choose Application">
                                <option></option>
                                @foreach($apps as $app)
                                    <option value="{{ $app->id }}">{{ $app->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="fv-row mb-7">
                            <label class="required fw-semibold fs-6 mb-2">Select Role</label>
                            <select name="app_role_id" id="app_role_id" class="form-select form-select-solid" data-control="select2" data-placeholder="Choose Role" disabled>
                                <option></option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" id="assign_submit_btn">Assign Access</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card card-flush h-xl-100">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-800">Active Access</span>
                        <span class="text-gray-400 mt-1 fw-semibold fs-6">Applications this user can access</span>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-5">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th>Application</th>
                                    <th>Role Assigned</th>
                                    <th>Permissions Count</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-semibold">
                                @forelse($userApps as $ua)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="symbol symbol-40px me-3">
                                                    <div class="symbol-label bg-light-primary text-primary fw-bold">
                                                        {{ substr($ua->app->name, 0, 1) }}
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-800 text-hover-primary mb-1 fw-bold">{{ $ua->app->name }}</span>
                                                    <span class="fs-7 text-gray-400">{{ $ua->app->code }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-light-success fs-7 fw-bold">{{ $ua->role->name }}</span>
                                        </td>
                                        <td>{{ $ua->role->permissions->count() }} permissions</td>
                                        <td class="text-end">
                                            <button class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm remove-btn" data-app-id="{{ $ua->app_id }}">
                                                <i class="ki-duotone ki-trash fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-10">No applications assigned yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            $('#app_id').on('change', function() {
                const appId = $(this).val();
                const roleSelect = $('#app_role_id');
                
                roleSelect.empty().append('<option></option>');
                if (!appId) {
                    roleSelect.prop('disabled', true);
                    return;
                }

                $.get("{{ url('administrator/user-apps/roles') }}/" + appId, function(roles) {
                    roles.forEach(role => {
                        roleSelect.append(`<option value="${role.id}">${role.name}</option>`);
                    });
                    roleSelect.prop('disabled', false);
                });
            });

            $('#assign_app_form').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('administrator.user-apps.assign', $user->id) }}",
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        Swal.fire('Success!', response.success, 'success').then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON.message, 'error');
                    }
                });
            });

            $('.remove-btn').on('click', function() {
                const appId = $(this).data('app-id');
                Swal.fire({
                    title: 'Remove Access?',
                    text: 'User will lose all permissions for this application.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, remove'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('administrator/user-apps/remove') }}/" + "{{ $user->id }}/" + appId,
                            type: 'DELETE',
                            data: { _token: '{{ csrf_token() }}' },
                            success: function(response) {
                                Swal.fire('Removed!', response.success, 'success').then(() => {
                                    location.reload();
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
    @endpush
</x-default-layout>
