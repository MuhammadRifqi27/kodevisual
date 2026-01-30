<x-default-layout>
    @section('title')
        Master Data Management
    @endsection

    @section('breadcrumbs')
    {{ Breadcrumbs::render('money-management.master-data') }}
    @endsection


    <!--begin::Card-->
    <div class="card">
        <div class="card-header card-header-stretch border-0 pt-6">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    <input type="text" id="master_data_search"
                        class="form-control form-control-solid w-250px ps-12" placeholder="Search Master Data..." />
                </div>
            </div>
            <div class="card-toolbar">
                <ul class="nav nav-tabs nav-line-tabs nav-stretch fs-6 border-0">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#kt_tab_categories">Categories</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_investments">Investments</a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="card-body">
            <div class="tab-content" id="myTabContent">
                <!-- CATEGORIES TAB -->
                <div class="tab-pane fade show active" id="kt_tab_categories" role="tabpanel">
                    <div class="d-flex justify-content-end mb-4">
                        <button type="button" class="btn btn-primary" id="btn_add_category">
                            <i class="ki-duotone ki-plus fs-2"></i> Add Category
                        </button>
                    </div>
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="table_categories">
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

                <!-- INVESTMENTS TAB -->
                <div class="tab-pane fade" id="kt_tab_investments" role="tabpanel">
                    <div class="d-flex justify-content-end mb-4">
                        <button type="button" class="btn btn-primary" id="btn_add_investment">
                            <i class="ki-duotone ki-plus fs-2"></i> Add Investment
                        </button>
                    </div>
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="table_investments">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th class="min-w-50px">No</th>
                                <th class="min-w-150px">Name</th>
                                <th class="min-w-100px">Code</th>
                                <th class="min-w-200px">Description</th>
                                <th class="text-end min-w-100px">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!--end::Card-->

    <!-- BEGIN::Modal Category -->
    <div class="modal fade" id="modal_category" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold" id="modal_category_title">Add Category</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <form id="form_category" class="form">
                    @csrf
                    <input type="hidden" name="id" id="cat_id">
                    <div class="modal-body py-10 px-lg-17">
                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Name</label>
                            <input type="text" class="form-control form-control-solid" placeholder="Category Name" name="name" id="cat_name" required />
                        </div>
                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Type</label>
                            <select class="form-select form-select-solid" name="type" id="cat_type" data-control="select2" data-hide-search="true" required>
                                <option value="income">Income</option>
                                <option value="expense">Expense</option>
                            </select>
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

    <!-- BEGIN::Modal Investment -->
    <div class="modal fade" id="modal_investment" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold" id="modal_investment_title">Add Investment</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <form id="form_investment" class="form">
                    @csrf
                    <input type="hidden" name="id" id="inv_id">
                    <div class="modal-body py-10 px-lg-17">
                        <div class="fv-row mb-7">
                            <label class="required fs-6 fw-semibold mb-2">Name</label>
                            <input type="text" class="form-control form-control-solid" placeholder="Investment Name" name="name" id="inv_name" required />
                        </div>
                        <div class="fv-row mb-7">
                            <label class="fs-6 fw-semibold mb-2">Code</label>
                            <input type="text" class="form-control form-control-solid" placeholder="Code (e.g. BTC, NASDAQ)" name="code" id="inv_code" />
                        </div>
                        <div class="fv-row mb-7">
                            <label class="fs-6 fw-semibold mb-2">Description</label>
                            <textarea class="form-control form-control-solid" rows="3" name="description" id="inv_description" placeholder="Optional description"></textarea>
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
    <!-- END::Modal Investment -->

    @push('scripts')
    <script>
        $(document).ready(function() {
            // ==========================================
            // SEARCH LOGIC
            // ==========================================
            $('#master_data_search').keyup(function(){
                let value = $(this).val();
                if($('#kt_tab_categories').hasClass('active')) {
                    tableCat.search(value).draw();
                } else {
                    tableInv.search(value).draw();
                }
            });
            
            // Adjust search on tab change
            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
                let value = $('#master_data_search').val();
                if($(e.target).attr('href') == '#kt_tab_categories') {
                    tableCat.search(value).draw();
                } else {
                    tableInv.search(value).draw();
                }
            });

            // ==========================================
            // CATEGORIES LOGIC
            // ==========================================
            let tableCat = $('#table_categories').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('money-management.master-data.categories.datatable') }}",
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'name', name: 'name'},
                    {data: 'type', name: 'type', render: function(data) {
                        if(data == 'income') return '<span class="badge badge-light-success">Income</span>';
                        return '<span class="badge badge-light-danger">Expense</span>';
                    }},
                    {data: 'description', name: 'description'},
                    {data: 'action', name: 'action', orderable: false, searchable: false, className: "text-end"},
                ]
            });

            $('#btn_add_category').click(function() {
                $('#form_category')[0].reset();
                $('#cat_id').val('');
                $('#cat_type').val('income').trigger('change');
                $('#modal_category_title').text('Add Category');
                $('#modal_category').modal('show');
            });

            // Edit Category
            $(document).on('click', '.edit-category-btn', function() {
                let id = $(this).data('id');
                let name = $(this).data('name');
                let type = $(this).data('type');
                let desc = $(this).data('description');

                $('#cat_id').val(id);
                $('#cat_name').val(name);
                $('#cat_type').val(type).trigger('change');
                $('#cat_description').val(desc);
                
                $('#modal_category_title').text('Edit Category');
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
                
                // For PUT request via FormData/POST
                if(id) formData.append('_method', 'PUT');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        $('#modal_category').modal('hide');
                        tableCat.ajax.reload();
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
                    text: "Are you sure you want to delete this category?",
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
                                tableCat.ajax.reload();
                                Swal.fire({ text: response.success, icon: "success", buttonsStyling: false, confirmButtonText: "Ok!", customClass: { confirmButton: "btn btn-primary" } });
                            }
                        });
                    }
                });
            });

            // ==========================================
            // INVESTMENTS LOGIC
            // ==========================================
            let tableInv = $('#table_investments').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('money-management.master-data.investments.datatable') }}",
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'name', name: 'name', render: function(data, type, row) {
                         return `<span class="fw-bold text-gray-800">${data}</span>`;
                    }},
                    {data: 'code', name: 'code', render: function(data) {
                        return data ? `<span class="badge badge-light-primary">${data}</span>` : '-';
                    }},
                    {data: 'description', name: 'description'},
                    {data: 'action', name: 'action', orderable: false, searchable: false, className: "text-end"},
                ]
            });

            $('#btn_add_investment').click(function() {
                $('#form_investment')[0].reset();
                $('#inv_id').val('');
                $('#modal_investment_title').text('Add Investment');
                $('#modal_investment').modal('show');
            });

            // Edit Investment
            $(document).on('click', '.edit-investment-btn', function() {
                let id = $(this).data('id');
                let name = $(this).data('name');
                let code = $(this).data('code');
                let desc = $(this).data('description');

                $('#inv_id').val(id);
                $('#inv_name').val(name);
                $('#inv_code').val(code);
                $('#inv_description').val(desc);
                
                $('#modal_investment_title').text('Edit Investment');
                $('#modal_investment').modal('show');
            });

            // Submit Investment Form
            $('#form_investment').submit(function(e) {
                e.preventDefault();
                let formData = new FormData(this);
                let id = $('#inv_id').val();
                let url = id ? 
                    "{{ route('money-management.master-data.investments.update', ':id') }}".replace(':id', id) : 
                    "{{ route('money-management.master-data.investments.store') }}";
                
                if(id) formData.append('_method', 'PUT');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        $('#modal_investment').modal('hide');
                        tableInv.ajax.reload();
                        Swal.fire({ text: response.success, icon: "success", buttonsStyling: false, confirmButtonText: "Ok!", customClass: { confirmButton: "btn btn-primary" } });
                    },
                    error: function(xhr) {
                        Swal.fire({ text: "Error saving data", icon: "error", buttonsStyling: false, confirmButtonText: "Ok!", customClass: { confirmButton: "btn btn-primary" } });
                    }
                });
            });

            // Delete Investment
            $(document).on('click', '.delete-investment-btn', function() {
                let id = $(this).data('id');
                Swal.fire({
                    text: "Are you sure you want to delete this investment?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, delete!",
                    customClass: { confirmButton: "btn btn-danger", cancelButton: "btn btn-active-light" }
                }).then(function(result) {
                    if (result.value) {
                        $.ajax({
                            url: "{{ route('money-management.master-data.investments.destroy', ':id') }}".replace(':id', id),
                            type: "DELETE",
                            data: { _token: "{{ csrf_token() }}" },
                            success: function(response) {
                                tableInv.ajax.reload();
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
