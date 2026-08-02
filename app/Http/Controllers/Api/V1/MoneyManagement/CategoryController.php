<?php

namespace App\Http\Controllers\Api\V1\MoneyManagement;

use App\Http\Controllers\Controller;
use App\Models\FinanceCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = FinanceCategory::orderBy('name');

        if ($request->filled('type') && in_array($request->type, ['income', 'expense'])) {
            $query->where('type', $request->type);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'description' => 'nullable|string',
        ]);

        $category = FinanceCategory::create($request->only('name', 'type', 'description'));

        return response()->json($category, 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'description' => 'nullable|string',
        ]);

        $category = FinanceCategory::findOrFail($id);
        $category->update($request->only('name', 'type', 'description'));

        return response()->json($category);
    }

    public function destroy($id)
    {
        FinanceCategory::findOrFail($id)->delete();

        return response()->json(['success' => 'Kategori berhasil dihapus']);
    }
}
