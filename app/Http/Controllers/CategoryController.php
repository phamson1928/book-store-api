<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Book;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Cache::rememberForever('categories_with_count', function () {
            return Category::withCount('books')->get();
        });
        return response()->json($categories);
    }

    public function store(StoreCategoryRequest $request)
    {
        $data = $request->validated();
        $category = Category::create($data);
        Cache::forget('categories_with_count');
        return response()->json($category, 201);
    }

    public function show($id)
    {
        $category = Cache::rememberForever("category_detail_{$id}", function () use ($id) {
            return Category::findOrFail($id);
        });
        return response()->json($category);
    }

    public function update(UpdateCategoryRequest $request, $id)
    {
        $category = Category::findOrFail($id);
        $category->update($request->validated());
        Cache::forget('categories_with_count');
        Cache::forget("category_detail_{$id}");
        return response()->json($category);
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        Cache::forget('categories_with_count');
        Cache::forget("category_detail_{$id}");
        return response()->json(['message' => 'Category deleted successfully']);
    }

    
    public function stats(){
        $categoriesTotal = Category::count();

        $booksTotal = Book::count();

        $average = $categoriesTotal > 0 ? $booksTotal / $categoriesTotal : 0;

        return response()->json([
            'categoriesTotal' => $categoriesTotal,
            'booksTotal' => $booksTotal,
            'average' => $average
        ]);
    }
}
