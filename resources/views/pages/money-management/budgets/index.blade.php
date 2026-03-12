<x-default-layout>
    @section('title')
        Monthly Budgets
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('money-management.budgets') }}
    @endsection

    <div class="card mb-10">
        <div class="card-body">
            <div class="alert alert-dismissible bg-light-primary d-flex flex-column flex-sm-row p-5 mb-5">
                <i class="ki-outline ki-notification-bing fs-2hx text-primary me-4 mb-5 mb-sm-0"></i>
                <div class="d-flex flex-column pe-0 pe-sm-10">
                    <h4 class="fw-bold">Active Payroll Cycle</h4>
                    <span>This budget tracks spending from <strong>{{ $cycleStartDate->format('d M Y') }}</strong> to <strong>{{ $cycleEndDate->format('d M Y') }}</strong> (based on your payroll start day setting).</span>
                </div>
            </div>
            
            <form action="{{ route('money-management.budgets.index') }}" method="GET" class="row g-5 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Month</label>
                    <select name="month" class="form-select form-select-solid" data-control="select2" data-hide-search="true">
                        @for($m=1; $m<=12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Year</label>
                    <select name="year" class="form-select form-select-solid" data-control="select2" data-hide-search="true">
                        @for($y=date('Y')-2; $y<=date('Y')+2; $y++)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-5 g-xl-10 mb-10">
        <div class="col-md-4">
            <div class="card h-100 bg-light-success shadow-sm">
                <div class="card-body p-9">
                    <div class="d-flex flex-stack">
                        <div class="d-flex flex-column">
                            <span class="text-success fw-bold fs-6">Income Pool (Cycle)</span>
                            <span class="text-gray-800 fw-bolder fs-2hx">Rp {{ number_format($incomePool, 0, ',', '.') }}</span>
                        </div>
                        <div class="symbol symbol-50px">
                            <div class="symbol-label bg-success">
                                <i class="ki-outline ki-entrance-left text-white fs-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 bg-light-primary shadow-sm">
                <div class="card-body p-9">
                    <div class="d-flex flex-stack">
                        <div class="d-flex flex-column">
                            <span class="text-primary fw-bold fs-6">Total Budget Limit</span>
                            <span class="text-gray-800 fw-bolder fs-2hx">Rp {{ number_format($totalBudget, 0, ',', '.') }}</span>
                        </div>
                        <div class="symbol symbol-50px">
                            <div class="symbol-label bg-primary">
                                <i class="ki-outline ki-abstract-26 text-white fs-2x"></i>
                            </div>
                        </div>
                    </div>
                    @if($incomePool > 0)
                        <div class="progress h-6px w-100 mt-2">
                             <div class="progress-bar bg-primary" role="progressbar" style="width: {{ min(($totalBudget / $incomePool) * 100, 100) }}%"></div>
                        </div>
                        <span class="text-gray-400 fs-7 fw-bold mt-1">{{ number_format(($totalBudget / $incomePool) * 100, 1) }}% of income pools</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 bg-light-danger shadow-sm">
                <div class="card-body p-9">
                    <div class="d-flex flex-stack">
                        <div class="d-flex flex-column">
                            <span class="text-danger fw-bold fs-6">Total Spent (Cycle)</span>
                            <span class="text-gray-800 fw-bolder fs-2hx">Rp {{ number_format($totalSpent, 0, ',', '.') }}</span>
                        </div>
                        <div class="symbol symbol-50px">
                            <div class="symbol-label bg-danger">
                                <i class="ki-outline ki-wallet text-white fs-2x"></i>
                            </div>
                        </div>
                    </div>
                    @if($totalBudget > 0)
                        <div class="progress h-6px w-100 mt-2">
                             <div class="progress-bar bg-danger" role="progressbar" style="width: {{ min(($totalSpent / $totalBudget) * 100, 100) }}%"></div>
                        </div>
                        <span class="text-gray-400 fs-7 fw-bold mt-1">{{ number_format(($totalSpent / $totalBudget) * 100, 1) }}% of budgets used</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-5 g-xl-10">
        @foreach($categories as $category)
            @php
                $budget = $budgets->get($category->id)->amount ?? 0;
                $spent = $spending->get($category->id)->total ?? 0;
                $percentage = $budget > 0 ? min(($spent / $budget) * 100, 100) : 0;
                $exceeded = $spent > $budget && $budget > 0;
                $remaining = max($budget - $spent, 0);
                $color = 'primary';
                if ($percentage >= 80) $color = 'warning';
                if ($exceeded) $color = 'danger';
            @endphp
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body p-9">
                        <div class="d-flex flex-stack mb-5">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-40px me-4">
                                    <div class="symbol-label bg-light-{{ $color }}">
                                        <i class="ki-outline ki-chart-pie-3 fs-2 text-{{ $color }}"></i>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="text-gray-800 text-hover-primary fs-6 fw-bold">{{ $category->name }}</span>
                                    <span class="text-gray-400 fw-semibold fs-7">{{ $category->description ?? 'No description' }}</span>
                                </div>
                            </div>
                            <button class="btn btn-icon btn-sm btn-light-primary edit-budget-btn" 
                                data-id="{{ $category->id }}" 
                                data-name="{{ $category->name }}"
                                data-amount="{{ $budget }}">
                                <i class="ki-outline ki-pencil fs-4"></i>
                            </button>
                        </div>

                        <div class="d-flex flex-stack fw-semibold fs-7 text-gray-400 mb-2">
                            <span>Progress</span>
                            <span>{{ number_format($percentage, 1) }}%</span>
                        </div>
                        <div class="h-8px mx-3 w-100 bg-light-{{ $color }} rounded mb-5">
                            <div class="bg-{{ $color }} rounded h-8px" role="progressbar" style="width:{{ $percentage }}%"></div>
                        </div>

                        <div class="d-flex flex-stack mb-2">
                            <span class="text-gray-400 fw-semibold fs-7">Spent:</span>
                            <span class="text-gray-800 fw-bold fs-6">Rp {{ number_format($spent, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex flex-stack">
                            <span class="text-gray-400 fw-semibold fs-7">Budget:</span>
                            <span class="text-gray-800 fw-bold fs-6">Rp {{ number_format($budget, 0, ',', '.') }}</span>
                        </div>
                        
                        @if($exceeded)
                            <div class="mt-4 text-center">
                                <span class="badge badge-light-danger fw-bold">Over Budget: Rp {{ number_format($spent - $budget, 0, ',', '.') }}</span>
                            </div>
                        @else
                            <div class="mt-4 text-center">
                                <span class="badge badge-light-success fw-bold">Remaining: Rp {{ number_format($remaining, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Edit Budget Modal -->
    <div class="modal fade" id="modal_edit_budget" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-400px">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold" id="modal_title">Set Budget</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <form id="form_budget" class="form">
                    @csrf
                    <input type="hidden" name="finance_category_id" id="budget_category_id">
                    <input type="hidden" name="month" value="{{ $month }}">
                    <input type="hidden" name="year" value="{{ $year }}">
                    
                    <div class="modal-body py-10 px-lg-17">
                        <div class="fv-row mb-7 text-center">
                            <h3 class="text-gray-600 mb-5" id="category_display_name">Category Name</h3>
                            <label class="required fs-6 fw-semibold mb-2">Monthly Limit (Rp)</label>
                            <input type="number" class="form-control form-control-solid text-center fs-2" name="amount" id="budget_amount" placeholder="0" required />
                        </div>
                    </div>
                    <div class="modal-footer flex-center">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="btn_save_budget">
                            <span class="indicator-label">Save Budget</span>
                            <span class="indicator-progress">Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            $('.edit-budget-btn').click(function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const amount = $(this).data('amount');
                
                $('#budget_category_id').val(id);
                $('#category_display_name').text(name);
                $('#budget_amount').val(amount);
                $('#modal_edit_budget').modal('show');
            });

            $('#form_budget').submit(function(e) {
                e.preventDefault();
                let btn = $('#btn_save_budget');
                btn.attr('data-kt-indicator', 'on').prop('disabled', true);

                $.post("{{ route('money-management.budgets.store') }}", $(this).serialize(), function(res) {
                    btn.removeAttr('data-kt-indicator').prop('disabled', false);
                    $('#modal_edit_budget').modal('hide');
                    Swal.fire({ text: res.success, icon: "success", confirmButtonText: "Ok!" }).then(() => {
                        window.location.reload();
                    });
                }).fail(function(xhr) {
                    btn.removeAttr('data-kt-indicator').prop('disabled', false);
                    Swal.fire({ text: "Error saving budget", icon: "error" });
                });
            });
        });
    </script>
    @endpush
</x-default-layout>
