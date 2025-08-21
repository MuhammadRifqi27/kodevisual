<?php

namespace App\Http\Controllers;

use App\Models\MasterCategoryExpenses;
use App\Services\MasterCategory\MasterCategoryService;
use Illuminate\Http\Request;

class MasterCategoryController extends Controller
{

    protected $masterCategoryService;

    public function __construct(
        MasterCategoryService $masterCategoryService,
    ) {
        $this->masterCategoryService = $masterCategoryService;
    }
    public function index()
    {
        return view('pages.master.master_category');
    }

    public function categoryDatatable()
    {
        $master_category = MasterCategoryExpenses::query();

        return datatables()->of($master_category)
            ->addColumn('action', function ($data) {
                $buttons = '';

                // if (auth()->user()->can('area-promosi-edit')) {
                $buttons .= '<button class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1 edit-btn" data-id="' . $data->id . '">
                    <i class="ki-duotone ki-pencil fs-2"><span class="path1"></span><span class="path2"></span></i>
                </button>';
                // }

                // if (auth()->user()->can('area-promosi-delete')) {
                $buttons .= '<button class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm delete-btn" data-id="' . $data->id . '">
                    <i class="ki-duotone ki-trash fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                </button>';
                // }

                return $buttons;
            })
            ->addColumn('DT_RowIndex', function ($data) {
                return '';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        try {
            $validated_data = $request->validate([
                'name_category' => 'required|string|max:255'
            ]);

            $isStored = $this->masterCategoryService->masterCategoryStore($validated_data);
            // dd($isStored);

            if (!$isStored) {
                throw new \Exception('Master Category Gagal Disimpan!');
            }

            return response()->json(['status' => 'success', 'message' => 'Master Category Berhasil Disimpan!'], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 400);
        }
    }
    public function edit(MasterCategoryExpenses $masterCategoryExpenses)
    {
        return response()->json($masterCategoryExpenses);
    }

    public function update(Request $request, $masterCategoryExpenses)
    {
        try {
            $validated_data = $request->validate([
                'name_category' => 'required|string|max:255'
            ]);

            $isStored = $this->masterCategoryService->masterCategoryStore($validated_data, $masterCategoryExpenses);

            if (!$isStored) {
                throw new \Exception('Master Category Gagal Diperbarui!');
            }

            return response()->json(['status' => 'success', 'message' => 'Master Category Berhasil Diperbarui!'], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()], 400);
        }
    }

    public function destroy(MasterCategoryExpenses $masterCategoryExpenses)
    {
        $masterCategoryExpenses->delete();

        return response()->json([
            'success' => true,
            'message' => 'Owner Berhasil Dihapus'
        ]);
    }
}
