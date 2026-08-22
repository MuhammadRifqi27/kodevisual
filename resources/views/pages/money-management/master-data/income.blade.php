<x-default-layout>
    @section('title')
        Category Income
    @endsection

    @section('breadcrumbs')
    {{ Breadcrumbs::render('money-management.master-data.income') }}
    @endsection


    <!--begin::Card-->
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    <input type="text" id="income_search"
                        class="form-control form-control-solid w-250px ps-12" placeholder="Search Income..." />
                </div>
            </div>
            <div class="card-toolbar">
                <button type="button" class="btn btn-primary" id="btn_add_category">
                    <i class="ki-duotone ki-plus fs-2"></i> Add Income Category
                </button>
            </div>
        </div>
        <div class="card-body">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="table_income">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-50px">No</th>
                        <th class="min-w-150px">Name</th>
                        <th class="min-w-100px">Type</th>
                        <th class="min-w-200px">Description</th>
                        <th class="text-end min-w-100px">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold"></tbody>
            </table>
        </div>
    </div>
    <!--end::Card-->

    <!-- BEGIN::Modal Category -->
    <div class="modal fade" id="modal_category" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold" id="modal_category_title">Add Income Category</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <form id="form_category" class="form">
                    @csrf
                    <input type="hidden" name="id" id="cat_id">
                    <input type="hidden" name="type" value="income">
                    
                    <div class="modal-body py-10 px-lg-17">
                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Name</label>
                            <input type="text" class="form-control form-control-solid" placeholder="Income Name" name="name" id="cat_name" required />
                        </div>
                        <div class="fv-row mb-7">
                            <label class="fs-6 fw-semibold mb-2">Description</label>
                            <textarea class="form-control form-control-solid" rows="3" name="description" id="cat_description" placeholder="Optional description"></textarea>
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
    <!-- END::Modal Category -->

    @push('scripts')
    <script>
        $(document).ready(function() {
            let table = $('#table_income').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('money-management.master-data.income.datatable') }}",
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'name', name: 'name'},
                    {data: 'type', name: 'type'},
                    {data: 'description', name: 'description'},
                    {data: 'action', name: 'action', orderable: false, searchable: false, className: "text-end"},
                ]
            });

            $('#income_search').keyup(function(){
                table.search($(this).val()).draw();
            });

            $('#btn_add_category').click(function() {
                $('#form_category')[0].reset();
                $('#cat_id').val('');
                $('#modal_category_title').text('Add Income Category');
                $('#modal_category').modal('show');
            });

            // Edit Category
            $(document).on('click', '.edit-category-btn', function() {
                let id = $(this).data('id');
                let name = $(this).data('name');
                let desc = $(this).data('description');

                $('#cat_id').val(id);
                $('#cat_name').val(name);
                $('#cat_description').val(desc);
                
                $('#modal_category_title').text('Edit Income Category');
                $('#modal_category').modal('show');
            });

            // Submit Category Form
            $('#form_category').submit(function(e) {
                e.preventDefault();
                let formData = new FormData(this);
                let id = $('#cat_id').val();
                let url = id ? 
                    "{{ route('money-management.master-data.categories.update', ':id') }}".replace(':id', id) : 
                    "{{ route('money-management.master-data.categories.store') }}";
                
                if(id) formData.append('_method', 'PUT');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        $('#modal_category').modal('hide');
                        table.ajax.reload();
                        Swal.fire({ text: response.success, icon: "success", buttonsStyling: false, confirmButtonText: "Ok!", customClass: { confirmButton: "btn btn-primary" } });
                    },
                    error: function(xhr) {
                        Swal.fire({ text: "Error saving data", icon: "error", buttonsStyling: false, confirmButtonText: "Ok!", customClass: { confirmButton: "btn btn-primary" } });
                    }
                });
            });

            // Delete Category
            $(document).on('click', '.delete-category-btn', function() {
                let id = $(this).data('id');
                Swal.fire({
                    text: "Are you sure you want to delete this income category?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, delete!",
                    customClass: { confirmButton: "btn btn-danger", cancelButton: "btn btn-active-light" }
                }).then(function(result) {
                    if (result.value) {
                        $.ajax({
                            url: "{{ route('money-management.master-data.categories.destroy', ':id') }}".replace(':id', id),
                            type: "DELETE",
                            data: { _token: "{{ csrf_token() }}" },
                            success: function(response) {
                                table.ajax.reload();
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
