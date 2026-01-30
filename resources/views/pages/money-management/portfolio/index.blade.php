<x-default-layout>
    @section('title')
        Portfolio & Savings
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('money-management.portfolio') }}
    @endsection

    <!--begin::Card-->
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    <input type="text" id="portfolio_search"
                        class="form-control form-control-solid w-250px ps-12" placeholder="Search Investments..." />
                </div>
            </div>
            <div class="card-toolbar">
                <a href="{{ route('money-management.master-data.investments.index') }}" class="btn btn-primary">
                    <i class="ki-duotone ki-eye fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Manage Investment Items
                </a>
            </div>
        </div>
        <div class="card-body">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="table_portfolio">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-50px">No</th>
                        <th class="min-w-150px">Name</th>
                        <th class="min-w-200px">Description</th>
                        <th class="min-w-150px">Current Balance</th>
                        <th class="text-end min-w-100px">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold"></tbody>
            </table>
        </div>
    </div>
    <!--end::Card-->

    @push('scripts')
    <script>
        $(document).ready(function() {
            let table = $('#table_portfolio').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('money-management.portfolio.datatable') }}",
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'code_name', name: 'name'},
                    {data: 'description', name: 'description'},
                    {data: 'balance', name: 'balance', searchable: false},
                    {data: 'action', name: 'action', orderable: false, searchable: false, className: "text-end"},
                ]
            });

            $('#portfolio_search').keyup(function(){
                table.search($(this).val()).draw();
            });
        });
    </script>
    @endpush
</x-default-layout>
