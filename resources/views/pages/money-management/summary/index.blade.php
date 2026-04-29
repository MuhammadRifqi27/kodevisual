<x-default-layout>
    @section('title')
        Monthly Summary
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('money-management.summary') }}
    @endsection

    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        <!-- Net Worth Card -->
        <div class="col-xl-4">
            <div class="card card-flush h-md-100 bg-primary shadow-sm" style="background: linear-gradient(112.14deg, #1B8ADB 0%, #21C1CF 100%)">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-white">Current Net Worth</span>
                        <span class="text-white opacity-75 mt-1 fw-semibold fs-6">Total assets across all accounts</span>
                    </h3>
                </div>
                <div class="card-body d-flex flex-column justify-content-center text-center py-10">
                    <div class="text-white fw-boldest fs-3x mb-2">Rp {{ number_format($totalNetWorth, 0, ',', '.') }}</div>
                    <div class="text-white opacity-75 fw-bold fs-6">Balance as of Today</div>
                </div>
            </div>
        </div>

        <!-- Asset Allocation Chart -->
        <div class="col-xl-4">
            <div class="card card-flush h-md-100">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-800">Asset Allocation</span>
                        <span class="text-gray-400 mt-1 fw-semibold fs-6">Distribution by Account</span>
                    </h3>
                </div>
                <div class="card-body pt-2">
                    <div id="kt_asset_allocation_chart" style="height: 200px;"></div>
                </div>
            </div>
        </div>

        <!-- Quick Stats / Insights -->
        <div class="col-xl-4">
            <div class="card card-flush h-md-100">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-800">Financial Insights</span>
                        <span class="text-gray-400 mt-1 fw-semibold fs-6">Quick overview of your status</span>
                    </h3>
                </div>
                <div class="card-body pt-2">
                    <div class="d-flex flex-column gap-5">
                        <div class="d-flex flex-stack">
                            <span class="text-gray-600 fw-semibold">Monthly Profit</span>
                            <span class="text-{{ $netProfit >= 0 ? 'success' : 'danger' }} fw-bold">Rp {{ number_format($netProfit, 0, ',', '.') }}</span>
                        </div>
                        <div class="separator separator-dashed"></div>
                        <div class="d-flex flex-stack">
                            <span class="text-gray-600 fw-semibold">Active Portfolios</span>
                            <span class="text-gray-800 fw-bold">{{ $assetAllocation->count() }} Accounts</span>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Large Cash Flow Chart -->
    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        <div class="col-12">
            <div class="card card-flush h-md-100">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <div class="d-flex align-items-center mb-1">
                            <span class="card-label fw-bold text-gray-800 me-2">Income vs Expenses</span>
                            <span class="badge badge-light-primary fw-bold px-4 py-3">Overall Trend</span>
                        </div>
                        <span class="text-gray-400 mt-1 fw-semibold fs-6">Monthly historical trend of your income and expenses for the last 12 months</span>
                    </h3>
                </div>
                <div class="card-body pt-2">
                    @if($chartData->isEmpty())
                        <div class="d-flex flex-column flex-center h-400px border border-dashed rounded bg-light">
                            <i class="ki-duotone ki-chart-line-star fs-3x text-primary mb-5"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                            <p class="text-gray-600 fw-bold fs-5">Insufficient Data</p>
                            <p class="text-muted fw-semibold fs-7">No transaction history found to display the chart.</p>
                        </div>
                    @else
                        <div id="kt_cash_flow_trend_chart" style="height: 400px;"></div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Chart Explanation Alert --}}
    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        <div class="col-12">
            <div class="notice d-flex bg-light-success rounded border-success border border-dashed p-6">
                <i class="ki-duotone ki-chart-line-star fs-2tx text-success me-4">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                </i>
                <div class="d-flex flex-stack flex-grow-1 flex-wrap flex-md-nowrap">
                    <div class="mb-3 mb-md-0 fw-semibold">
                        <h4 class="text-gray-900 fw-bold">Panduan Grafik Arus Kas</h4>
                        <div class="fs-6 text-gray-700 pe-7">
                            Grafik **Income vs Expenses** di atas menampilkan tren performa keuangan Anda sejak awal tahun 2026. 
                            <ul class="mt-2 mb-0">
                                <li><strong>Garis Biru (Income):</strong> Total pendapatan yang masuk ke portofolio Anda per siklus gajian.</li>
                                <li><strong>Garis Merah (Expenses):</strong> Total pengeluaran yang tercatat per siklus gajian.</li>
                                <li><strong>Analisa:</strong> Jika garis biru berada di atas garis merah, berarti Anda memiliki surplus (keuntungan) pada periode tersebut.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-5 g-xl-10 mb-2 mb-xl-10">
        <!-- Filter Card -->
        <div class="col-12">
            <div class="card card-flush pt-5 pb-5">
                <div class="card-header">
                    <div class="card-title">
                        <div class="d-flex flex-column">
                            <h3 class="card-label fw-bold text-gray-800">Filter Period</h3>
                            <span class="text-gray-400 mt-1 fw-semibold fs-6">Select month and year for breakdown below</span>
                        </div>
                    </div>
                    <div class="card-toolbar">
                        <form action="{{ route('money-management.summary.index') }}"
                            method="GET"
                            class="d-flex flex-group gap-3 align-items-center">
                            
                            <select name="month" class="form-select form-select-solid w-150px"
                                    data-control="select2" data-hide-search="true">
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ sprintf('%02d', $m) }}" {{ $month == sprintf('%02d', $m) ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                    </option>
                                @endforeach
                            </select>

                            <select name="year" class="form-select form-select-solid w-125px"
                                    data-control="select2" data-hide-search="true">
                                @foreach(range(date('Y') - 5, date('Y') + 1) as $y)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endforeach
                            </select>

                            <button type="submit" class="btn btn-primary">Apply</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Breakdown Chart -->
        <div class="col-xl-6">
            <div class="card card-flush h-md-100">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-800">Expenses by Category</span>
                        <span class="text-gray-400 mt-1 fw-semibold fs-6">Where your money goes this month</span>
                    </h3>
                </div>
                <div class="card-body pt-2">
                    @if($categorySummary->isEmpty())
                        <div class="d-flex flex-column flex-center h-300px">
                            <span class="text-muted fw-bold">No data available for this period.</span>
                        </div>
                    @else
                        <div id="kt_summary_category_chart" style="height: 350px;"></div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Smart Advisor -->
        <div class="col-xl-6">
            <div class="card card-flush h-md-100 bg-light-success border-success border-dashed">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-success">Smart Advisor</span>
                        <span class="text-gray-600 mt-1 fw-semibold fs-6">Recommendations for your financial health</span>
                    </h3>
                    <div class="card-toolbar">
                        <i class="ki-duotone ki-notification-on fs-2hx text-success">
                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                        </i>
                    </div>
                </div>
                <div class="card-body pt-5">
                    <div class="d-flex flex-column gap-5">
                        @foreach($advice as $item)
                        <div class="d-flex align-items-center">
                            <div class="symbol symbol-40px me-4">
                                <span class="symbol-label bg-light-success">
                                    <i class="ki-duotone ki-check-circle fs-2 text-success">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                </span>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="text-gray-800 fw-bold fs-6">{{ $item }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="separator separator-dashed my-8 border-success opacity-25"></div>

                    <div class="d-flex flex-stack">
                        <div class="d-flex flex-column me-3">
                            <span class="text-gray-800 fw-bold fs-4">Current Month Status</span>
                            <span class="text-gray-600 fw-semibold">Net Profit / Loss</span>
                        </div>
                        <div class="text-end">
                            <span class="text-{{ $netProfit >= 0 ? 'success' : 'danger' }} fw-boldest fs-2">
                                Rp {{ number_format($netProfit, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Table Recap -->
        <div class="col-12">
            <div class="card card-flush h-md-100">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-800">Recapitulation Table</span>
                        <span class="text-gray-400 mt-1 fw-semibold fs-6">Detailed breakdown of monthly spending</span>
                    </h3>
                </div>
                <div class="card-body pt-2">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-5">
                            <thead>
                                <tr class="text-start fw-bold fs-7 text-uppercase gs-0">
                                    <th>Category</th>
                                    <th class="text-end">Total Amount</th>
                                    <th class="text-end">Percentage</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-semibold">
                                @forelse($categorySummary as $row)
                                <tr>
                                    <td>{{ $row->name }}</td>
                                    <td class="text-end">Rp {{ number_format($row->total, 0, ',', '.') }}</td>
                                    <td class="text-end">
                                        <div class="d-flex align-items-center justify-content-end">
                                            <span class="me-2">{{ round(($row->total / ($totalExpense ?: 1)) * 100) }}%</span>
                                            <div class="progress h-6px w-100px bg-light-primary">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ ($row->total / ($totalExpense ?: 1)) * 100 }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center">No transactions found.</td>
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
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof ApexCharts === 'undefined') {
                console.error('ApexCharts is not defined. Please check your vendor configuration.');
                return;
            }

            // --- Asset Allocation Chart ---
            var assetValues = {!! json_encode($assetAllocation->pluck('value')->map(fn($v) => (float)$v)) !!};
            var assetLabels = {!! json_encode($assetAllocation->pluck('name')) !!};

            if (document.querySelector("#kt_asset_allocation_chart") && assetValues.length > 0) {
                var optionsAsset = {
                    series: assetValues,
                    chart: {
                        height: 250,
                        type: 'donut',
                    },
                    labels: assetLabels,
                    stroke: {
                        show: false
                    },
                    dataLabels: {
                        enabled: false
                    },
                    legend: {
                        show: false
                    },
                    colors: ['#009ef7', '#50cd89', '#f1416c', '#ffc700', '#7239ea', '#009ef7', '#50cd89', '#f1416c']
                };
                var chartAsset = new ApexCharts(document.querySelector("#kt_asset_allocation_chart"), optionsAsset);
                chartAsset.render();
            }

            // --- Cash Flow Trend Chart ---
            @if(!$chartData->isEmpty())
            var cashFlowIncome = {!! json_encode($chartData->pluck('income')->map(fn($v) => (float)$v)) !!};
            var cashFlowExpense = {!! json_encode($chartData->pluck('expense')->map(fn($v) => (float)$v)) !!};
            var cashFlowCategories = {!! json_encode($chartData->pluck('label')) !!};

            if (document.querySelector("#kt_cash_flow_trend_chart")) {
                var optionsCashFlow = {
                    series: [{
                        name: 'Income',
                        data: cashFlowIncome
                    }, {
                        name: 'Expenses',
                        data: cashFlowExpense
                    }],
                    chart: {
                        height: 400,
                        type: 'area',
                        toolbar: {
                            show: false
                        }
                    },
                    colors: ['#009ef7', '#f1416c'],
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    xaxis: {
                        categories: cashFlowCategories,
                        axisBorder: {
                            show: false,
                        },
                        axisTicks: {
                            show: false,
                        }
                    },
                    yaxis: {
                        labels: {
                            formatter: function (value) {
                                return "Rp " + value.toLocaleString('id-ID');
                            }
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function (val) {
                                return "Rp " + val.toLocaleString('id-ID');
                            }
                        }
                    },
                    grid: {
                        borderColor: '#f1f1f1',
                    }
                };
                var chartCashFlow = new ApexCharts(document.querySelector("#kt_cash_flow_trend_chart"), optionsCashFlow);
                chartCashFlow.render();
            }
            @endif

            // --- Expenses Category Chart ---
            @if(!$categorySummary->isEmpty())
            var categorySeries = [
                @foreach($categorySummary as $item)
                    {{ (float)$item->total }},
                @endforeach
            ];
            var categoryLabels = [
                @foreach($categorySummary as $item)
                    "{{ $item->name }}",
                @endforeach
            ];

            if (document.querySelector("#kt_summary_category_chart")) {
                var optionsCategory = {
                    series: categorySeries,
                    chart: {
                        height: 350,
                        type: 'donut',
                    },
                    labels: categoryLabels,
                    stroke: {
                        show: false
                    },
                    dataLabels: {
                        enabled: false
                    },
                    legend: {
                        position: 'bottom'
                    }
                };
                var chartCategory = new ApexCharts(document.querySelector("#kt_summary_category_chart"), optionsCategory);
                chartCategory.render();
            }
            @endif
        });
    </script>
    @endpush
</x-default-layout>
