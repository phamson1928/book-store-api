<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index()
    {
        return Book::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'author' => 'required|string',
            'price' => 'required|numeric',
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'publication_date' => 'required|date',
            'description' => 'required|string',
            'language' => 'required|string',
            'category' => 'required|string',
            'discount_price' => 'required|numeric',
            'new_best_seller' => 'required|boolean',
            'weight_in_grams' => 'required|integer',
            'packaging_size_cm' => 'required|string',
            'number_of_pages' => 'required|integer',
            'form' => 'required|string',
            'state' => 'required|in:available,out_of_stock',
        ]);

        $path = $request->file('image')->store('books','public');
        $data = $request->except('image');
        $data['image'] = $path;
        $book = Book::create($data);
        return response()->json($book, 201);
    }

    public function show($id)
    {
        $book = Book::findOrFail($id);
        return response()->json($book);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string',
            'author' => 'required|string',
            'price' => 'required|numeric',
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'publication_date' => 'required|date',
            'description' => 'required|string',
            'language' => 'required|string',
            'category' => 'required|string',
            'discount_price' => 'required|numeric',
            'new_best_seller' => 'required|boolean',
            'weight_in_grams' => 'required|integer',
            'packaging_size_cm' => 'required|string',
            'number_of_pages' => 'required|integer',
            'form' => 'required|string',
            'state' => 'required|in:available,out_of_stock',
        ]);
        $book = Book::findOrFail($id);
        $path = $request->file('image')->store('books','public');
        $data = $request->except('image');
        $data['image'] = $path;
        $book->update($data);
        return response()->json($book);
    }
    public function destroy($id)
    {
        Book::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}
