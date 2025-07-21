<?php
namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        return response()->json(Category::all());
    }

    public function store(Request $request) // Changed from create() to store()
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:categories,name',
        ]);

        $category = Category::create(array_merge(
            $validated,
            ['created_by' => Auth::id()]
        ));

        return response()->json($category, 201);
    }

    public function show($id)
    {
        $category = Category::find($id);

        if (! $category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], Response::HTTP_NOT_FOUND); // 404
        }

        return response()->json([
            'success' => true,
            'data'    => $category,
        ]);
    }

    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        if (! $category) {
            return response()->json(['message' => 'Category not found'], 404);
        }
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                Rule::unique('categories', 'name')->ignore($category->id),
            ],
        ]);

        $category->update($validated);

        return response()->json($category);
    }

    public function destroy($id) // Changed from delete() to destroy()
    {
        $category = Category::find($id);
        if (! $category) {
            return response()->json(['message' => 'Category not found'], 404);
        }
        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully',
        ]);
    }
}
