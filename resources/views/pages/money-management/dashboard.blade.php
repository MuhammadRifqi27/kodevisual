<x-default-layout>
    @section('title')
        Money Management Dashboard
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('money-management') }}
    @endsection

    <!--begin::Row Stats-->
    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        <!--begin::Col-->
        <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3">
            <div class="card card-flush h-md-100">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">Rp {{ number_format($totalBalance, 0, ',', '.') }}</span>
                        <span class="text-gray-500 pt-1 fw-semibold fs-6">Total Combined Assets</span>
                    </div>
                </div>
                <div class="card-body d-flex align-items-end pt-0 pb-6">
                    <div class="d-flex align-items-center flex-column mt-3 w-100">
                        <div class="d-flex justify-content-between w-100 mt-auto mb-2">
                            <span class="fw-bold fs-6 text-gray-500">Wealth Status</span>
                            <span class="fw-bold fs-6 text-gray-900">Active</span>
                        </div>
                        <div class="h-8px mx-3 w-100 bg-light-success rounded">
                            <div class="bg-success rounded h-8px" role="progressbar" style="width: 100%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Col-->

        <!--begin::Col-->
        <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3">
            <div class="card card-flush h-md-100">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <div class="d-flex align-items-center">
                            <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">Rp {{ number_format($monthlyIncome, 0, ',', '.') }}</span>
                        </div>
                        <span class="text-gray-500 pt-1 fw-semibold fs-6">Income this Month</span>
                    </div>
                </div>
                <div class="card-body d-flex flex-column justify-content-end pe-0">
                    <span class="fs-6 fw-bolder text-gray-800 d-block mb-2">Recent Inflow</span>
                    <div class="symbol-group symbol-hover">
                        <div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip" title="Salary">
                            <span class="symbol-label bg-light-success text-success fw-bold">S</span>
                        </div>
                        <div class="symbol symbol-35px symbol-circle" data-bs-toggle="tooltip" title="Profit">
                            <span class="symbol-label bg-light-primary text-primary fw-bold">P</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Col-->

        <!--begin::Col-->
        <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3">
            <div class="card card-flush h-md-100">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <div class="d-flex align-items-center">
                            <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">Rp {{ number_format($monthlyExpense, 0, ',', '.') }}</span>
                        </div>
                        <span class="text-gray-500 pt-1 fw-semibold fs-6">Expense this Month</span>
                    </div>
                </div>
                <div class="card-body d-flex flex-column justify-content-end pe-0">
                    <span class="fs-6 fw-bolder text-gray-800 d-block mb-2">Top Burning</span>
                    <div class="d-flex align-items-center">
                        @foreach($topExpenses as $expense)
                            <div class="symbol symbol-35px symbol-circle me-2" data-bs-toggle="tooltip" title="{{ $expense['name'] }}: Rp {{ number_format($expense['total'], 0, ',', '.') }}">
                                <span class="symbol-label bg-light-danger text-danger fw-bold">{{ substr($expense['name'], 0, 1) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <!--end::Col-->

        <!--begin::Col-->
        <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3">
            <div class="card card-flush h-md-100 shadow-sm" style="background-color: {{ $netProfit >= 0 ? '#E8FFF3' : '#FFF5F8' }}">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <div class="d-flex align-items-center">
                            <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">Rp {{ number_format($netProfit, 0, ',', '.') }}</span>
                        </div>
                        <span class="text-gray-600 pt-1 fw-semibold fs-6">Net Cashflow (P&L)</span>
                    </div>
                </div>
                <div class="card-body d-flex flex-column justify-content-end">
                    <div class="d-flex align-items-center fw-bold">
                        @if($netProfit >= 0)
                            <i class="ki-duotone ki-trending-up fs-2 text-success me-2"></i>
                            <span class="text-success fs-7">You are profitable this month!</span>
                        @else
                            <i class="ki-duotone ki-trending-down fs-2 text-danger me-2"></i>
                            <span class="text-danger fs-7">Spending more than earning.</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <!--end::Col-->
    </div>
    <!--end::Row Stats-->

    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        <!--begin::Col Portfolio Distribution-->
        <div class="col-xl-4">
            <div class="card card-flush h-xl-100">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-800">Portfolio Distribution</span>
                        <span class="text-gray-500 mt-1 fw-semibold fs-7">Where your money sits</span>
                    </h3>
                </div>
                <div class="card-body pt-2">
                    <div class="d-flex flex-column">
                        @foreach($portfolioData as $data)
                            <div class="d-flex align-items-center mb-5">
                                <div class="symbol symbol-40px me-3">
                                    <span class="symbol-label bg-light-primary">
                                        <i class="ki-duotone ki-wallet fs-2x text-primary"></i>
                                    </span>
                                </div>
                                <div class="d-flex flex-column flex-grow-1">
                                    <a href="#" class="text-gray-800 text-hover-primary fw-bold fs-6">{{ $data['name'] }}</a>
                                    <span class="text-gray-500 fw-semibold fs-7">Saving Account</span>
                                </div>
                                <div class="text-end">
                                    <span class="text-gray-900 fw-bold fs-6">Rp {{ number_format($data['balance'], 0, ',', '.') }}</span>
                                    <div class="text-gray-500 fs-8">{{ number_format(($data['balance'] / ($totalBalance ?: 1)) * 100, 1) }}%</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <!--end::Col-->

        <!--begin::Col Top Expenses-->
        <div class="col-xl-4">
            <div class="card card-flush h-xl-100">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-800">Top Spending</span>
                        <span class="text-gray-500 mt-1 fw-semibold fs-7">Budget breakdown</span>
                    </h3>
                </div>
                <div class="card-body pt-2">
                    <div id="kt_expense_chart" style="height: 250px;"></div>
                    <div class="mt-5">
                        @foreach($topExpenses as $expense)
                            <div class="d-flex flex-stack mb-3">
                                <div class="d-flex align-items-center me-2">
                                    <div class="symbol symbol-10px symbol-circle me-3">
                                        <span class="symbol-label bg-danger"></span>
                                    </div>
                                    <div class="text-gray-800 fw-bold fs-7">{{ $expense['name'] }}</div>
                                </div>
                                <div class="text-gray-600 fw-semibold fs-8">Rp {{ number_format($expense['total'], 0, ',', '.') }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <!--end::Col-->

        <!--begin::Col Recent Transactions-->
        <div class="col-xl-4">
            <div class="card card-flush h-xl-100">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-800">Recent Transactions</span>
                        <span class="text-gray-500 mt-1 fw-semibold fs-7">Last 10 activities</span>
                    </h3>
                    <div class="card-toolbar">
                        <a href="{{ route('money-management.transactions.index') }}" class="btn btn-sm btn-light-primary">View All</a>
                    </div>
                </div>
                <div class="card-body pt-5">
                    <div class="timeline-label">
                        @foreach($recentTransactions as $trx)
                            <div class="timeline-item">
                                <div class="timeline-label fw-bold text-gray-800 fs-8">{{ date('d/m', strtotime($trx->date)) }}</div>
                                <div class="timeline-badge">
                                    <i class="fa fa-genderless text-{{ $trx->type == 'income' ? 'success' : 'danger' }} fs-1"></i>
                                </div>
                                <div class="timeline-content d-flex align-items-center">
                                    <span class="fw-bold text-gray-800 ps-3 flex-grow-1 fs-7">
                                        {{ $trx->description ?: ($trx->category->name ?? 'Transaction') }}
                                    </span>
                                    <span class="text-{{ $trx->type == 'income' ? 'success' : 'danger' }} fw-bold fs-7">
                                        {{ $trx->type == 'income' ? '+' : '-' }} {{ number_format($trx->amount, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <!--end::Col-->
    </div>

    @push('scripts')
    <script>
        // Simple Pie Chart for Expenses if needed, using ApexCharts which is usually included in Metronic
        var initExpenseChart = function() {
            var element = document.getElementById('kt_expense_chart');
            if (!element) return;

            var options = {
                series: [@foreach($topExpenses as $e) {{ $e['total'] }}, @endforeach],
                chart: {
                    type: 'donut',
                    height: 250,
                },
                labels: [@foreach($topExpenses as $e) "{{ $e['name'] }}", @endforeach],
                legend: { show: false },
                dataLabels: { enabled: false },
                colors: ['#F1416C', '#7239EA', '#50CD89', '#FFC700', '#009EF7']
            };

            var chart = new ApexCharts(element, options);
            chart.render();
        }

        $(document).ready(function() {
            initExpenseChart();
        });
    </script>
    @endpush
</x-default-layout>
