<?php

namespace App\Http\Controllers\Travel;

use App\Http\Controllers\Controller;
use App\Models\TravelTrip;
use App\Models\TravelTripImage;
use Illuminate\Http\Request;

class TravelTripController extends Controller
{
    public function index()
    {
        $trips = TravelTrip::where('user_id', auth()->id())
            ->with(['itineraries', 'budgets', 'expenses', 'coverImage'])
            ->orderBy('start_date', 'desc')
            ->get();
        return view('pages.travel.trips.index', compact('trips'));
    }

    public function create()
    {
        return view('pages.travel.trips.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'             => 'required|string|max:255',
            'destination'       => 'required|string|max:255',
            'start_date'        => 'required|date',
            'end_date'          => 'required|date|after_or_equal:start_date',
            'total_budget'      => 'required|numeric|min:0',
            'currency'          => 'required|string|max:10',
            'number_of_persons' => 'required|integer|min:1',
            'cover_image'       => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['status']  = 'planned';
        unset($validated['cover_image']); // simpan di tabel terpisah

        $trip = TravelTrip::create($validated);

        // Simpan gambar ke tabel travel_trip_images
        if ($request->hasFile('cover_image')) {
            $file     = $request->file('cover_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path     = 'assets/media/icon-travel/' . $filename;
            $file->move(public_path('assets/media/icon-travel'), $filename);

            TravelTripImage::create([
                'trip_id'  => $trip->id,
                'filename' => $filename,
                'path'     => $path,
                'url'      => asset($path),
                'is_cover' => true,
            ]);
        }

        return redirect()->route('travel.trips.index')->with('success', 'Trip created successfully!');
    }

    public function show($id)
    {
        $trip = TravelTrip::with(['itineraries', 'budgets', 'expenses', 'coverImage'])->findOrFail($id);
        return view('pages.travel.trips.show', compact('trip'));
    }

    public function edit($id)
    {
        $trip = TravelTrip::with('coverImage')->findOrFail($id);
        return view('pages.travel.trips.edit', compact('trip'));
    }

    public function update(Request $request, $id)
    {
        $trip = TravelTrip::findOrFail($id);
        $oldPersons = $trip->number_of_persons;

        $validated = $request->validate([
            'title'             => 'required|string|max:255',
            'destination'       => 'required|string|max:255',
            'start_date'        => 'required|date',
            'end_date'          => 'required|date|after_or_equal:start_date',
            'total_budget'      => 'required|numeric|min:0',
            'currency'          => 'required|string|max:10',
            'number_of_persons' => 'required|integer|min:1',
            'status'            => 'required|in:draft,planned,ongoing,completed,cancelled',
            'cover_image'       => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
        ]);

        unset($validated['cover_image']);
        $trip->update($validated);

        // If number_of_persons changed, recalculate itineraries
        if ($oldPersons != $trip->number_of_persons) {
            $newPersons = max($trip->number_of_persons, 1);
            foreach ($trip->itineraries as $itinerary) {
                if ($itinerary->cost_type === 'total') {
                    $itinerary->cost_per_person = $itinerary->cost_estimate / $newPersons;
                } else {
                    $itinerary->cost_estimate = $itinerary->cost_per_person * $newPersons;
                }
                $itinerary->save();
            }
        }

        // Ganti cover image jika ada upload baru
        if ($request->hasFile('cover_image')) {
            // Hapus cover lama dari disk & DB
            $oldCover = TravelTripImage::where('trip_id', $trip->id)->where('is_cover', true)->first();
            if ($oldCover) {
                $oldFullPath = public_path($oldCover->path);
                if (file_exists($oldFullPath)) {
                    unlink($oldFullPath);
                }
                $oldCover->delete();
            }

            $file     = $request->file('cover_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path     = 'assets/media/icon-travel/' . $filename;
            $file->move(public_path('assets/media/icon-travel'), $filename);

            TravelTripImage::create([
                'trip_id'  => $trip->id,
                'filename' => $filename,
                'path'     => $path,
                'url'      => asset($path),
                'is_cover' => true,
            ]);
        }

        return redirect()->route('travel.trips.index')->with('success', 'Trip updated successfully!');
    }

    public function destroy($id)
    {
        $trip = TravelTrip::with('images')->findOrFail($id);

        // Hapus semua file gambar dari disk
        foreach ($trip->images as $image) {
            $fullPath = public_path($image->path);
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }

        $trip->delete();
        return redirect()->route('travel.trips.index')->with('success', 'Trip deleted successfully!');
    }

    public function exportAll()
    {
        $trips = TravelTrip::where('user_id', auth()->id())
            ->with(['itineraries', 'budgets', 'expenses'])
            ->orderBy('start_date', 'desc')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="all_trips_export_' . date('Ymd_His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($trips) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, [
                'ID', 'Title', 'Destination', 'Start Date', 'End Date', 
                'Total Budget', 'Currency', 'Number of Persons', 'Status', 
                'Total Allocated', 'Total Spent', 'Remaining Budget'
            ]);

            foreach ($trips as $trip) {
                $allocated = $trip->budgets->sum('amount');
                $spent = $trip->expenses->sum('amount');
                $remaining = $trip->total_budget - $spent;

                fputcsv($file, [
                    $trip->id,
                    $trip->title,
                    $trip->destination,
                    $trip->start_date,
                    $trip->end_date,
                    $trip->total_budget,
                    $trip->currency,
                    $trip->number_of_persons,
                    ucfirst($trip->status),
                    $allocated,
                    $spent,
                    $remaining
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportFull($id)
    {
        $trip = TravelTrip::where('user_id', auth()->id())
            ->with(['itineraries', 'budgets', 'expenses'])
            ->findOrFail($id);

        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="trip_' . str_replace(' ', '_', $trip->title) . '_full_' . date('Ymd_His') . '.xlsx"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($trip) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Trip Full Summary');

            // Default font
            $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

            // Universal style definitions
            $formalHeaderStyle = [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                    'size' => 10,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => '1F4E79'], // Dark Corporate Navy Blue
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ];

            $gridBorderStyle = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => 'BFBFBF'], // Soft grey border
                    ],
                ],
            ];

            // Title Block
            $sheet->setCellValue('A1', 'EXPEDITION DETAILED SUMMARY');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1F4E79'));

            // Section 1: Trip Summary Table
            $sheet->setCellValue('A3', 'TRIP SUMMARY');
            $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1F4E79'));

            $summaryHeaders = ['ID', 'Title', 'Destination', 'Start Date', 'End Date', 'Total Budget', 'Currency', 'Number of Persons', 'Status'];
            foreach ($summaryHeaders as $index => $sh) {
                $col = chr(65 + $index);
                $sheet->setCellValue($col . '4', $sh);
            }
            $sheet->getStyle('A4:I4')->applyFromArray($formalHeaderStyle);

            $sheet->setCellValue('A5', $trip->id);
            $sheet->setCellValue('B5', $trip->title);
            $sheet->setCellValue('C5', $trip->destination);
            $sheet->setCellValue('D5', $trip->start_date);
            $sheet->setCellValue('E5', $trip->end_date);
            $sheet->setCellValue('F5', $trip->total_budget);
            $sheet->setCellValue('G5', $trip->currency);
            $sheet->setCellValue('H5', $trip->number_of_persons);
            $sheet->setCellValue('I5', ucfirst($trip->status));

            $sheet->getStyle('F5')->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('A5:I5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle('B5:C5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

            $sheet->getStyle('A4:I5')->applyFromArray($gridBorderStyle);

            // Section 2: Itinerary Schedule
            $currentRow = 7;
            $sheet->setCellValue('A' . $currentRow, 'ITINERARY SCHEDULE');
            $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1F4E79'));

            $currentRow++; // Row 8 for Itinerary Headers
            $itineraryHeaders = ['Day', 'Date', 'Time', 'Activity', 'Description', 'Location', 'Notes', 'Total Cost Estimate (' . $trip->currency . ')', 'Cost Per Person (' . $trip->currency . ')'];
            foreach ($itineraryHeaders as $index => $ih) {
                $col = chr(65 + $index);
                $sheet->setCellValue($col . $currentRow, $ih);
            }
            $sheet->getStyle("A{$currentRow}:I{$currentRow}")->applyFromArray($formalHeaderStyle);

            $startItineraryDataRow = $currentRow + 1;
            $itineraries = $trip->itineraries->sortBy(['day_number', 'time']);
            $groupedItineraries = $itineraries->groupBy('day_number');
            $itineraryDataRow = $startItineraryDataRow;

            foreach ($groupedItineraries as $day => $items) {
                $count = $items->count();
                $firstItem = $items->first();

                if ($count > 1) {
                    $sheet->mergeCells("A{$itineraryDataRow}:A" . ($itineraryDataRow + $count - 1));
                    $sheet->mergeCells("B{$itineraryDataRow}:B" . ($itineraryDataRow + $count - 1));
                }

                $sheet->setCellValue("A{$itineraryDataRow}", 'Day ' . $day);
                $sheet->setCellValue("B{$itineraryDataRow}", $firstItem->date);

                $sheet->getStyle("A{$itineraryDataRow}:A" . ($itineraryDataRow + $count - 1))->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $sheet->getStyle("B{$itineraryDataRow}:B" . ($itineraryDataRow + $count - 1))->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                foreach ($items->sortBy('time') as $index => $item) {
                    $rowIdx = $itineraryDataRow + $index;
                    $sheet->setCellValue("C{$rowIdx}", $item->time ? substr($item->time, 0, 5) : '--:--');
                    $sheet->setCellValue("D{$rowIdx}", $item->activity);
                    $sheet->setCellValue("E{$rowIdx}", $item->description);
                    $sheet->setCellValue("F{$rowIdx}", $item->location);
                    $sheet->setCellValue("G{$rowIdx}", $item->notes);
                    $sheet->setCellValue("H{$rowIdx}", $item->cost_estimate);
                    $sheet->setCellValue("I{$rowIdx}", $item->cost_per_person);

                    $sheet->getStyle("C{$rowIdx}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("H{$rowIdx}")->getNumberFormat()->setFormatCode('#,##0');
                    $sheet->getStyle("I{$rowIdx}")->getNumberFormat()->setFormatCode('#,##0');
                }
                $itineraryDataRow += $count;
            }

            if ($itineraries->isEmpty()) {
                $sheet->setCellValue("A{$itineraryDataRow}", 'No activities recorded');
                $sheet->mergeCells("A{$itineraryDataRow}:I{$itineraryDataRow}");
                $sheet->getStyle("A{$itineraryDataRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $itineraryDataRow++;
            }

            $sheet->getStyle("A" . ($startItineraryDataRow - 1) . ":I" . ($itineraryDataRow - 1))->applyFromArray($gridBorderStyle);

            // Section 3: Budget Allocations
            $currentRow = $itineraryDataRow + 2;
            $sheet->setCellValue('A' . $currentRow, 'BUDGET ALLOCATIONS');
            $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1F4E79'));

            $currentRow++; // Budget headers row
            $budgetHeaders = ['Category', 'Amount Allocated (' . $trip->currency . ')', 'Notes'];
            foreach ($budgetHeaders as $index => $bh) {
                $col = chr(65 + $index);
                $sheet->setCellValue($col . $currentRow, $bh);
            }
            $sheet->getStyle("A{$currentRow}:C{$currentRow}")->applyFromArray($formalHeaderStyle);
            $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

            $startBudgetDataRow = $currentRow + 1;
            $budgetDataRow = $startBudgetDataRow;
            foreach ($trip->budgets as $budget) {
                $sheet->setCellValue("A{$budgetDataRow}", $budget->category);
                $sheet->setCellValue("B{$budgetDataRow}", $budget->amount);
                $sheet->setCellValue("C{$budgetDataRow}", $budget->notes);

                $sheet->getStyle("B{$budgetDataRow}")->getNumberFormat()->setFormatCode('#,##0');
                $budgetDataRow++;
            }

            if ($trip->budgets->isEmpty()) {
                $sheet->setCellValue("A{$budgetDataRow}", 'No budget allocations recorded');
                $sheet->mergeCells("A{$budgetDataRow}:C{$budgetDataRow}");
                $sheet->getStyle("A{$budgetDataRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $budgetDataRow++;
            }

            $sheet->getStyle("A" . ($startBudgetDataRow - 1) . ":C" . ($budgetDataRow - 1))->applyFromArray($gridBorderStyle);

            // Section 4: Expenses Recorded
            $currentRow = $budgetDataRow + 2;
            $sheet->setCellValue('A' . $currentRow, 'EXPENSES RECORDED');
            $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1F4E79'));

            $currentRow++; // Expenses headers row
            $expenseHeaders = ['Date', 'Category', 'Description', 'Amount Spent (' . $trip->currency . ')', 'Pre-Trip?'];
            foreach ($expenseHeaders as $index => $eh) {
                $col = chr(65 + $index);
                $sheet->setCellValue($col . $currentRow, $eh);
            }
            $sheet->getStyle("A{$currentRow}:E{$currentRow}")->applyFromArray($formalHeaderStyle);

            $startExpenseDataRow = $currentRow + 1;
            $expenseDataRow = $startExpenseDataRow;
            foreach ($trip->expenses->sortBy('date') as $expense) {
                $sheet->setCellValue("A{$expenseDataRow}", $expense->date);
                $sheet->setCellValue("B{$expenseDataRow}", $expense->category);
                $sheet->setCellValue("C{$expenseDataRow}", $expense->description);
                $sheet->setCellValue("D{$expenseDataRow}", $expense->amount);
                $sheet->setCellValue("E{$expenseDataRow}", $expense->is_pre_trip ? 'Yes' : 'No');

                $sheet->getStyle("A{$expenseDataRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("E{$expenseDataRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D{$expenseDataRow}")->getNumberFormat()->setFormatCode('#,##0');
                $expenseDataRow++;
            }

            if ($trip->expenses->isEmpty()) {
                $sheet->setCellValue("A{$expenseDataRow}", 'No expenses recorded');
                $sheet->mergeCells("A{$expenseDataRow}:E{$expenseDataRow}");
                $sheet->getStyle("A{$expenseDataRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $expenseDataRow++;
            }

            $sheet->getStyle("A" . ($startExpenseDataRow - 1) . ":E" . ($expenseDataRow - 1))->applyFromArray($gridBorderStyle);

            // Auto column widths for all columns
            foreach (range('A', 'I') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportItinerary($id)
    {
        $trip = TravelTrip::where('user_id', auth()->id())
            ->with(['itineraries'])
            ->findOrFail($id);

        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="trip_' . str_replace(' ', '_', $trip->title) . '_itinerary_' . date('Ymd_His') . '.xlsx"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($trip) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Itinerary');

            // Default font
            $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

            // Title block
            $sheet->setCellValue('A1', 'ITINERARY SCHEDULE FOR ' . strtoupper($trip->title));
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1F4E79'));

            $sheet->setCellValue('A2', 'Destination: ' . $trip->destination . ' | Participants: ' . $trip->number_of_persons . ' person(s)');
            $sheet->getStyle('A2')->getFont()->setItalic(true);

            // Headers
            $headersRow = ['Day', 'Date', 'Time', 'Activity', 'Description', 'Location', 'Notes', 'Total Cost Estimate (' . $trip->currency . ')', 'Cost Per Person (' . $trip->currency . ')'];
            $colLetter = 'A';
            foreach ($headersRow as $headerText) {
                $sheet->setCellValue($colLetter . '4', $headerText);
                $colLetter++;
            }

            // Style headers
            $headerRange = 'A4:I4';
            $sheet->getStyle($headerRange)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));
            $sheet->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('1F4E79'); // Formal Dark Navy Blue
            $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Data
            $itineraries = $trip->itineraries->sortBy(['day_number', 'time']);
            $grouped = $itineraries->groupBy('day_number');

            $currentRow = 5;
            $totalCost = 0;
            $totalCostPerPerson = 0;

            foreach ($grouped as $day => $items) {
                $count = $items->count();
                $firstItem = $items->first();

                // Merge Day and Date cells vertically if count > 1
                if ($count > 1) {
                    $sheet->mergeCells("A{$currentRow}:A" . ($currentRow + $count - 1));
                    $sheet->mergeCells("B{$currentRow}:B" . ($currentRow + $count - 1));
                }

                $sheet->setCellValue("A{$currentRow}", 'Day ' . $day);
                $sheet->setCellValue("B{$currentRow}", $firstItem->date);

                $sheet->getStyle("A{$currentRow}:A" . ($currentRow + $count - 1))->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $sheet->getStyle("B{$currentRow}:B" . ($currentRow + $count - 1))->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                foreach ($items->sortBy('time') as $index => $item) {
                    $rowIdx = $currentRow + $index;
                    $sheet->setCellValue("C{$rowIdx}", $item->time ? substr($item->time, 0, 5) : '--:--');
                    $sheet->setCellValue("D{$rowIdx}", $item->activity);
                    $sheet->setCellValue("E{$rowIdx}", $item->description);
                    $sheet->setCellValue("F{$rowIdx}", $item->location);
                    $sheet->setCellValue("G{$rowIdx}", $item->notes);
                    $sheet->setCellValue("H{$rowIdx}", $item->cost_estimate);
                    $sheet->setCellValue("I{$rowIdx}", $item->cost_per_person);

                    $sheet->getStyle("C{$rowIdx}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("H{$rowIdx}")->getNumberFormat()->setFormatCode('#,##0');
                    $sheet->getStyle("I{$rowIdx}")->getNumberFormat()->setFormatCode('#,##0');

                    $totalCost += $item->cost_estimate;
                    $totalCostPerPerson += $item->cost_per_person;
                }

                $currentRow += $count;
            }

            // Total row
            $sheet->setCellValue("A{$currentRow}", 'TOTAL');
            $sheet->mergeCells("A{$currentRow}:G{$currentRow}");
            $sheet->getStyle("A{$currentRow}")->getFont()->setBold(true);
            $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

            $sheet->setCellValue("H{$currentRow}", $totalCost);
            $sheet->setCellValue("I{$currentRow}", $totalCostPerPerson);
            $sheet->getStyle("H{$currentRow}:I{$currentRow}")->getFont()->setBold(true);
            $sheet->getStyle("H{$currentRow}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("I{$currentRow}")->getNumberFormat()->setFormatCode('#,##0');

            // Apply borders to table
            $styleArray = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => 'BFBFBF'],
                    ],
                ],
            ];
            $sheet->getStyle('A4:I' . $currentRow)->applyFromArray($styleArray);

            // Double line bottom border for total row
            $totalRowStyle = [
                'borders' => [
                    'bottom' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE,
                        'color' => ['argb' => '000000'],
                    ],
                    'top' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => '000000'],
                    ],
                ],
            ];
            $sheet->getStyle("A{$currentRow}:I{$currentRow}")->applyFromArray($totalRowStyle);

            // Set auto column width
            foreach (range('A', 'I') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        };

        return response()->stream($callback, 200, $headers);
    }
}
