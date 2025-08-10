<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Book;

class CategoryController extends Controller
{
    public function index()
    {
        return response()->json(Category::withCount('books')->get());
    }

    public function store(StoreCategoryRequest $request)
    {
        $data = $request->validated();
        $category = Category::create($data);
        return response()->json($category, 201);
    }

    public function show($id)
    {
        $category = Category::findOrFail($id);
        return response()->json($category);
    }

    public function update(UpdateCategoryRequest $request, $id)
    {
        $category = Category::findOrFail($id);
        $data = $request->validated();
        $category->update($data);
        return response()->json($category);
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return response()->json(['message' => 'Category deleted successfully']);
    }

    
    public function stats(){
        $categoriesTotal = Category::count();

        $booksTotal = Book::count();

        $evarage = $booksTotal/$categoriesTotal;

        return response()->json([
            'categoriesTotal' => $categoriesTotal,
            'booksTotal' => $booksTotal,
            'evarage' => $evarage
        ]);
    }
}
