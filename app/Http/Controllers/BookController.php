<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use Illuminate\Support\Facades\Cache;

class BookController extends Controller
{
    public function index()
    {
        $books = Cache::rememberForever('books_with_category',function(){
            return Book::with('category')->get();
    });
        return response()->json($books);
    }

    public function store(StoreBookRequest $request)
    {
        $data = $request->validated();
        
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('books', 'public');
            $data['image'] = $path;
        }
        
        $book = Book::create($data);
        Cache::forget('books_with_category');
        return response()->json($book, 201);
    }

    public function show($id)
    {
        $book = Cache::rememberForever("book_detail_{$id}", function() use ($id) {
            return Book::with('author','category')->findOrFail($id);
        });
        return response()->json($book);
    }

    public function update(UpdateBookRequest $request, $id)
    {
        $book = Book::with('category')->findOrFail($id);
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('books', 'public');
            $data['image'] = $path;
        }
        
        $book->update($data);
        Cache::forget("book_detail_{$id}");
        Cache::forget('books_with_category');
        return response()->json($book);
    }

    public function destroy($id)
    {
        Book::findOrFail($id)->delete();
        Cache::forget("book_detail_{$id}");
        Cache::forget('books_with_category');
        return response()->json(null, 204);
    }
}
