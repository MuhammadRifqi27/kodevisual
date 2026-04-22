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
                    <div class="card-toolbar">
                        <button class="btn btn-sm btn-light btn-active-info" id="btn_take_snapshot" title="Take Snapshot">
                            Snapshoot
                        </button>
                    </div>
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
                        <div class="separator separator-dashed"></div>
                        <div class="d-flex flex-stack">
                            <span class="text-gray-600 fw-semibold">Snapshots Taken</span>
                            <span class="text-gray-800 fw-bold">{{ $netWorthHistory->count() }} Recordings</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Large Growth Tracking Chart -->
    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        <div class="col-12">
            <div class="card card-flush h-md-100">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <div class="d-flex align-items-center mb-1">
                            <span class="card-label fw-bold text-gray-800 me-2">Growth Tracking</span>
                            <span class="badge badge-light-primary fw-bold px-4 py-3">Net Worth Trend</span>
                        </div>
                        <span class="text-gray-400 mt-1 fw-semibold fs-6">Historical balance trend across all accounts over time</span>
                    </h3>
                </div>
                <div class="card-body pt-2">
                    @if($netWorthHistory->count() < 2)
                        <div class="d-flex flex-column flex-center h-400px border border-dashed rounded bg-light">
                            <i class="ki-duotone ki-chart-line-star fs-3x text-primary mb-5"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                            <p class="text-gray-600 fw-bold fs-5">Insufficient Data</p>
                            <p class="text-muted fw-semibold fs-7">Take snapshots regularly to see your wealth growth chart here over time.</p>
                        </div>
                    @else
                        <div id="kt_net_worth_trend_chart" style="height: 400px;"></div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Snapshot Information Alert --}}
    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        <div class="col-12">
            <div class="notice d-flex bg-light-info rounded border-info border border-dashed p-6">
                <i class="ki-duotone ki-information-5 fs-2tx text-info me-4">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                </i>
                <div class="d-flex flex-stack flex-grow-1 flex-wrap flex-md-nowrap">
                    <div class="mb-3 mb-md-0 fw-semibold">
                        <h4 class="text-gray-900 fw-bold">Tentang Fitur Snapshot Net Worth</h4>
                        <div class="fs-6 text-gray-700 pe-7">
                            <strong>Snapshot</strong> adalah fitur untuk menyimpan "foto" kondisi keuangan Anda pada tanggal tertentu. 
                            Dengan mengambil snapshot secara rutin, Anda dapat:
                            <ul class="mt-2 mb-0">
                                <li><strong>Melihat grafik pertumbuhan</strong> kekayaan bersih dari waktu ke waktu</li>
                                <li><strong>Tracking progress</strong> terhadap target finansial Anda</li>
                                <li><strong>Menganalisis tren</strong> naik/turun untuk evaluasi keuangan</li>
                            </ul>
                            <div class="mt-3 text-gray-600 fs-7">
                                <strong>Tips:</strong> Ambil snapshot minimal 1x seminggu atau setiap awal bulan untuk hasil tracking yang optimal. 
                                Klik tombol <span class="badge badge-light-info">Snapshoot</span> di card Net Worth untuk menyimpan data hari ini.
                            </div>
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
                            <span class="text-gray-400 mt-1 fw-semibold fs-6">Select month and year to see summary</span>
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
        am5.ready(function() {
            // --- Asset Allocation Chart ---
            var rootAsset = am5.Root.new("kt_asset_allocation_chart");
            rootAsset.setThemes([am5themes_Animated.new(rootAsset)]);
            var chartAsset = rootAsset.container.children.push(am5percent.PieChart.new(rootAsset, {
                layout: rootAsset.verticalLayout,
                innerRadius: am5.percent(70)
            }));
            var seriesAsset = chartAsset.series.push(am5percent.PieSeries.new(rootAsset, {
                valueField: "value",
                categoryField: "name",
                alignLabels: false
            }));
            seriesAsset.data.setAll(@json($assetAllocation));
            seriesAsset.labels.template.set("forceHidden", true);
            seriesAsset.ticks.template.set("forceHidden", true);
            seriesAsset.appear(1000, 100);

            // --- Net Worth Trend Chart ---
            @if($netWorthHistory->count() >= 2)
            var rootTrend = am5.Root.new("kt_net_worth_trend_chart");
            rootTrend.setThemes([am5themes_Animated.new(rootTrend)]);
            var chartTrend = rootTrend.container.children.push(am5xy.XYChart.new(rootTrend, {
                panX: true,
                panY: true,
                wheelX: "panX",
                wheelY: "zoomX",
                pinchZoomX: true
            }));
            var xAxis = chartTrend.xAxes.push(am5xy.DateAxis.new(rootTrend, {
                maxDeviation: 0.5,
                baseInterval: { timeUnit: "day", count: 1 },
                renderer: am5xy.AxisRendererX.new(rootTrend, { pan:"offset" }),
                tooltip: am5.Tooltip.new(rootTrend, {})
            }));
            var yAxis = chartTrend.yAxes.push(am5xy.ValueAxis.new(rootTrend, {
                renderer: am5xy.AxisRendererY.new(rootTrend, { pan:"offset" })
            }));
            var seriesTrend = chartTrend.series.push(am5xy.LineSeries.new(rootTrend, {
                name: "Net Worth",
                xAxis: xAxis,
                yAxis: yAxis,
                valueYField: "amount",
                valueXField: "date",
                tooltip: am5.Tooltip.new(rootTrend, {
                    labelText: "Rp {valueY}"
                })
            }));
            
            // Add area fill for better visualization
            seriesTrend.fills.template.setAll({
                fillOpacity: 0.2,
                visible: true
            });

            seriesTrend.strokes.template.setAll({ strokeWidth: 3 });
            seriesTrend.data.setAll(@json($netWorthHistory->map(fn($item) => ['date' => strtotime($item['date']) * 1000, 'amount' => $item['amount']])));
            
            // Add scrollbar
            chartTrend.set("scrollbarX", am5.Scrollbar.new(rootTrend, {
                orientation: "horizontal"
            }));

            seriesTrend.appear(1000);
            chartTrend.appear(1000, 100);
            @endif

            // --- Expenses Category Chart ---
            @if(!$categorySummary->isEmpty())
            var rootExpense = am5.Root.new("kt_summary_category_chart");
            rootExpense.setThemes([am5themes_Animated.new(rootExpense)]);
            var chartExpense = rootExpense.container.children.push(am5percent.PieChart.new(rootExpense, {
                layout: rootExpense.verticalLayout,
                innerRadius: am5.percent(50)
            }));
            var seriesExpense = chartExpense.series.push(am5percent.PieSeries.new(rootExpense, {
                valueField: "value",
                categoryField: "category",
                alignLabels: false
            }));
            seriesExpense.data.setAll([
                @foreach($categorySummary as $item)
                { category: "{{ $item->name }}", value: {{ $item->total }} },
                @endforeach
            ]);
            seriesExpense.labels.template.set("forceHidden", true);
            seriesExpense.ticks.template.set("forceHidden", true);
            var legendExpense = chartExpense.children.push(am5.Legend.new(rootExpense, {
                centerX: am5.percent(50),
                x: am5.percent(50),
                marginTop: 15,
                marginBottom: 15
            }));
            legendExpense.data.setAll(seriesExpense.dataItems);
            seriesExpense.appear(1000, 100);
            @endif
        });

        $('#btn_take_snapshot').click(function() {
            let btn = $(this);
            btn.addClass('disabled');
            $.get("{{ route('money-management.summary.net-worth-snapshot') }}", function(res) {
                Swal.fire({ text: res.success, icon: "success" }).then(() => { window.location.reload(); });
            });
        });
    </script>
    @endpush
</x-default-layout>
