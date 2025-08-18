<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::with('category')->get();
        return response()->json($books);
    }

    public function store(StoreBookRequest $request)
    {
        $data = $request->validated();
        
        // Xử lý hình ảnh
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('books', 'public');
            $data['image'] = $path;
        }
        
        $book = Book::create($data);
        return response()->json($book, 201);
    }

    public function show($id)
    {
        $book = Book::with('author','category')->findOrFail($id);
        return response()->json($book);
    }

    public function update(UpdateBookRequest $request, $id)
    {
        $book = Book::with('category')->findOrFail($id);
        $data = $request->validated();
        
        // Xử lý hình ảnh
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('books', 'public');
            $data['image'] = $path;
        }
        
        $book->update($data);
        return response()->json($book);
    }

    public function destroy($id)
    {
        Book::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}
