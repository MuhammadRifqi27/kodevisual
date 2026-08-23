<?php

namespace App\Http\Controllers\Api\V1\MoneyManagement;

use App\Http\Controllers\Controller;
use App\Services\FinanceCategory\FinanceCategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(private FinanceCategoryService $financeCategoryService)
    {
    }

    public function index(Request $request)
    {
        $type = $request->filled('type') && in_array($request->type, ['income', 'expense'])
            ? $request->type
            : null;

        return response()->json($this->financeCategoryService->query($type)->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'description' => 'nullable|string',
        ]);

        $category = $this->financeCategoryService->create($validated);

        return response()->json($category, 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'description' => 'nullable|string',
        ]);

        $category = $this->financeCategoryService->update($id, $validated);

        return response()->json($category);
    }

    public function destroy($id)
    {
        $this->financeCategoryService->delete($id);

        return response()->json(['success' => 'Kategori berhasil dihapus']);
    }
}
