<x-default-layout>
    @section('title')
        Monthly Summary
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('money-management.summary') }}
    @endsection

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
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
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
        @if(!$categorySummary->isEmpty())
        // amCharts 5 implementation
        am5.ready(function() {
            // Create root element
            var root = am5.Root.new("kt_summary_category_chart");

            // Set themes
            root.setThemes([
                am5themes_Animated.new(root)
            ]);

            // Create chart
            var chart = root.container.children.push(am5percent.PieChart.new(root, {
                layout: root.verticalLayout,
                innerRadius: am5.percent(50)
            }));

            // Create series
            var series = chart.series.push(am5percent.PieSeries.new(root, {
                valueField: "value",
                categoryField: "category",
                alignLabels: false
            }));

            series.labels.template.setAll({
                textType: "circular",
                centerX: 0,
                centerY: 0,
                forceHidden: true
            });

            series.ticks.template.setAll({
                forceHidden: true
            });

            // Set data
            series.data.setAll([
                @foreach($categorySummary as $item)
                { category: "{{ $item->name }}", value: {{ $item->total }} },
                @endforeach
            ]);

            // Create legend
            var legend = chart.children.push(am5.Legend.new(root, {
                centerX: am5.percent(50),
                x: am5.percent(50),
                marginTop: 15,
                marginBottom: 15
            }));

            legend.data.setAll(series.dataItems);

            // Play initial series animation
            series.appear(1000, 100);
        });
        @endif
    </script>
    @endpush
</x-default-layout>
