<x-default-layout>
    @section('title')
        Money Management Dashboard
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('money-management') }}
    @endsection

    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        <!-- Filter Card -->
        <div class="col-12">
            <div class="card card-flush pt-5 pb-5">
                <div class="card-header">
                    <div class="card-title">
                        <div class="d-flex flex-column">
                            <h3 class="card-label fw-bold text-gray-800">Filter Period</h3>
                            <span class="text-gray-400 mt-1 fw-semibold fs-6">Select month and year to see dashboard stats</span>
                        </div>
                    </div>
                    <div class="card-toolbar">
                        <!-- Lihat Saldo -->
                        <button type="button" class="btn btn-light-primary btn-md me-3" id="btn_lihat_saldo">
                            <i class="ki-duotone ki-eye fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Lihat Saldo
                        </button>
                        <form action="{{ route('money-management.dashboard') }}" method="GET" class="d-flex flex-group gap-3 align-items-center">
                            <select name="month" class="form-select form-select-solid w-150px" data-control="select2" data-hide-search="true">
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ sprintf('%02d', $m) }}" {{ $month == sprintf('%02d', $m) ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                    </option>
                                @endforeach
                            </select>
                            <select name="year" class="form-select form-select-solid w-125px" data-control="select2" data-hide-search="true">
                                @foreach(range(date('Y') - 5, date('Y') + 1) as $y)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-primary">Apply</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--begin::Row Stats-->
    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        <!-- Card 1: Total Wealth -->
        <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3">
            <div class="card card-flush h-md-100">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <span class="fs-2hx fw-bold me-2 lh-1 ls-n2 rupiah-value" data-value="Rp {{ number_format($totalNetWorthAtEnd, 0, ',', '.') }}">******</span>
                        <span class="text-muted pt-1 fw-semibold fs-6">Net Worth (Total Harta)</span>
                    </div>
                </div>
                <div class="card-body d-flex align-items-end pt-0 pb-6">
                    <div class="d-flex align-items-center flex-column mt-3 w-100">
                        <div class="d-flex justify-content-between w-100 mt-auto mb-2">
                            <span class="fw-bold fs-6 text-muted">Aset Aman</span>
                            <span class="fw-bold fs-6 text-success">Active</span>
                        </div>
                        <div class="h-8px mx-3 w-100 bg-light-success rounded">
                            <div class="bg-success rounded h-8px" role="progressbar" style="width: 100%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Col-->

        <!-- Card 2: Rolling Income -->
        <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3">
            <div class="card card-flush h-md-100">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <div class="d-flex align-items-center">
                            <span class="fs-2hx fw-bold me-2 lh-1 ls-n2 text-success rupiah-value" data-value="Rp {{ number_format($incomePool, 0, ',', '.') }}">******</span>
                        </div>
                        <span class="text-muted pt-1 fw-semibold fs-6">Income Pool (Cycle)</span>
                    </div>
                </div>
                <div class="card-body d-flex flex-column justify-content-end pb-6">
                    <div class="d-flex flex-column gap-1 mb-2">
                        @foreach($incomeBreakdown as $inc)
                            <div class="d-flex flex-stack fs-7">
                                <span class="text-gray-500 fw-semibold">{{ $inc['name'] }}:</span>
                                <span class="text-gray-800 fw-bold rupiah-value" data-value="Rp {{ number_format($inc['total'], 0, ',', '.') }}">******</span>
                            </div>
                        @endforeach
                    </div>
                    <span class="text-muted fs-8 font-italic">Includes activity since {{ date('d M Y', strtotime($cycleStartDate)) }}</span>
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
                            <span class="fs-2hx fw-bold me-2 lh-1 ls-n2 rupiah-value" data-value="Rp {{ number_format($monthlyExpense, 0, ',', '.') }}">******</span>
                        </div>
                        <span class="text-muted pt-1 fw-semibold fs-6">Expense (Cycle)</span>
                    </div>
                </div>
                <div class="card-body d-flex flex-column justify-content-end pe-0">
                    <span class="fs-6 fw-bolder  d-block mb-2">Top Burning</span>
                    <div class="d-flex align-items-center">
                        @foreach($topExpenses as $expense)
                            <div class="symbol symbol-35px symbol-circle me-2 rupiah-tooltip" data-bs-toggle="tooltip" data-real-title="{{ $expense['name'] }}: Rp {{ number_format($expense['total'], 0, ',', '.') }}" title="******">
                                <span class="symbol-label bg-light-danger text-danger fw-bold">{{ substr($expense['name'], 0, 1) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <!--end::Col-->

        <!-- Card 4: Liquid Bank Balance -->
        <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3">
           <div class="card card-flush h-md-100 shadow-sm {{ $totalLiquidCash > 0 ? 'bg-light-success' : 'bg-light-primary' }}">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <div class="d-flex align-items-center">
                            <span class="fs-2hx fw-bold me-2 lh-1 ls-n2 rupiah-value" data-value="Rp {{ number_format($totalLiquidCash, 0, ',', '.') }}">******</span>
                        </div>
                        <span class="text-muted pt-1 fw-semibold fs-6">Active Balance (Sisa di Bank)</span>
                    </div>
                </div>
                <div class="card-body d-flex flex-column justify-content-end pb-6">
                    <div class="d-flex flex-column gap-1 mb-2">
                        @foreach($liquidAccounts as $acc)
                            <div class="d-flex flex-stack fs-7">
                                <span class="text-gray-500 fw-semibold">{{ $acc['name'] }}:</span>
                                <span class="text-gray-800 fw-bold rupiah-value" data-value="Rp {{ number_format($acc['balance'], 0, ',', '.') }}">******</span>
                            </div>
                        @endforeach
                    </div>
                    @php $perf = $incomePool - $monthlyExpense; @endphp
                    <div class="border-top mt-2 pt-2">
                        <span class="fs-8 fw-bold {{ $perf >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $perf >= 0 ? 'Monthly Profit: +' : 'Monthly Deficit: -' }} <span class="rupiah-value" data-value="Rp {{ number_format(abs($perf), 0, ',', '.') }}">******</span>
                        </span>
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
                        <span class="card-label fw-bold ">Portfolio Distribution</span>
                        <span class="text-muted mt-1 fw-semibold fs-7">Where your money sits</span>
                    </h3>
                </div>
                <div class="card-body pt-2">
                    <div class="d-flex flex-column">
                        @foreach($portfolioData as $data)
                            <div class="d-flex align-items-center mb-5">
                                <div class="symbol symbol-40px me-3">
                                    <span class="symbol-label">
                                        @if($data['assets'] == 1)
                                            <i class="ki-duotone ki-bitcoin fs-2x text-warning"><span class="path1"></span><span class="path2"></span></i>
                                        @elseif($data['assets'] == 2)
                                            <i class="ki-duotone ki-ocean fs-2x text-warning">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                                <span class="path4"></span>
                                                <span class="path5"></span>
                                                <span class="path6"></span>
                                                <span class="path7"></span>
                                                <span class="path8"></span>
                                                <span class="path9"></span>
                                                <span class="path10"></span>
                                                <span class="path11"></span>
                                                <span class="path12"></span>
                                                <span class="path13"></span>
                                                <span class="path14"></span>
                                                <span class="path15"></span>
                                                <span class="path16"></span>
                                                <span class="path17"></span>
                                                <span class="path18"></span>
                                                <span class="path19"></span>
                                            </i>
                                        @else
                                            <i class="ki-duotone ki-bank fs-2x text-success"><span class="path1"></span><span class="path2"></span></i>
                                        @endif
                                    </span>
                                </div>
                                <div class="d-flex flex-column flex-grow-1">
                                    <a href="#" class="text-gray-800 text-hover-primary fw-bold fs-6">{{ $data['name'] }} - {{ $data['investment-code'] }}</a>
                                    <span class="text-muted fw-semibold fs-7">Saving Account</span>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold fs-6 rupiah-value" data-value="Rp {{ number_format($data['balance'], 0, ',', '.') }}">******</span>
                                    <div class="text-muted fs-8">{{ number_format(($data['balance'] / ($totalNetWorthAtEnd ?: 1)) * 100, 1) }}%</div>
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
                        <span class="card-label fw-bold ">Top Spending</span>
                        <span class="text-muted mt-1 fw-semibold fs-7">Budget breakdown</span>
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
                                    <div class=" fw-bold fs-7">{{ $expense['name'] }}</div>
                                </div>
                                <div class="text-muted fw-semibold fs-8 rupiah-value" data-value="Rp {{ number_format($expense['total'], 0, ',', '.') }}">******</div>
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
                        <span class="card-label fw-bold ">Recent Transactions</span>
                        <span class="text-muted mt-1 fw-semibold fs-7">Last 10 activities</span>
                    </h3>
                    <div class="card-toolbar">
                        <a href="{{ route('money-management.transactions.index') }}" class="btn btn-sm btn-light-primary">View All</a>
                    </div>
                </div>
                <div class="card-body pt-5">
                    <div class="timeline-label">
                        @foreach($recentTransactions as $trx)
                            <div class="timeline-item">
                                <div class="timeline-label fw-bold  fs-8">{{ date('d/m', strtotime($trx->date)) }}</div>
                                <div class="timeline-badge">
                                    @php
                                        $color = 'danger';
                                        if($trx->type == 'income') $color = 'success';
                                        if($trx->type == 'transfer') $color = 'primary';
                                    @endphp
                                    <i class="fa fa-genderless text-{{ $color }} fs-1"></i>
                                </div>
                                <div class="timeline-content d-flex align-items-center">
                                    <span class="fw-bold  ps-3 flex-grow-1 fs-7">
                                        {{ $trx->description ?: ($trx->category->name ?? 'Transaction') }}
                                    </span>
                                    @php
                                        $displayAmount = $trx->amount;
                                        if($trx->type == 'expense' && $displayAmount > 0) $displayAmount = -$displayAmount;
                                        $displayColor = $displayAmount >= 0 ? 'success' : 'danger';
                                    @endphp
                                    <span class="text-{{ $displayColor }} fw-bold fs-7">
                                        {{ $displayAmount >= 0 ? '+' : '-' }} <span class="rupiah-value" data-value="Rp {{ number_format(abs($displayAmount), 0, ',', '.') }}">******</span>
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

            let isSaldoVisible = false;

            function updateSaldoVisibility() {
                if (isSaldoVisible) {
                    $('.rupiah-value').each(function() {
                        $(this).text($(this).attr('data-value'));
                    });
                    $('.rupiah-tooltip').each(function() {
                        const realTitle = $(this).attr('data-real-title');
                        $(this).attr('title', realTitle).attr('data-bs-original-title', realTitle);
                        // Re-init tooltip if needed
                        const tooltip = bootstrap.Tooltip.getInstance(this);
                        if (tooltip) {
                            tooltip._fixTitle();
                        }
                    });
                    $('#btn_lihat_saldo').html('<i class="ki-duotone ki-eye-slash fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Tutup Saldo');
                } else {
                    $('.rupiah-value').text('******');
                    $('.rupiah-tooltip').each(function() {
                        $(this).attr('title', '******').attr('data-bs-original-title', '******');
                        const tooltip = bootstrap.Tooltip.getInstance(this);
                        if (tooltip) {
                            tooltip._fixTitle();
                        }
                    });
                    $('#btn_lihat_saldo').html('<i class="ki-duotone ki-eye fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i> Lihat Saldo');
                }
            }

            $('#btn_lihat_saldo').click(function() {
                isSaldoVisible = !isSaldoVisible;
                updateSaldoVisibility();
            });

            // Initial state
            updateSaldoVisibility();
        });
    </script>
    @endpush
</x-default-layout>
