<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Book::all();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
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

        // Nếu có trường is_trending và là true, thêm vào trending_books
        if ($request->has('is_trending') && $request->boolean('is_trending')) {
            \App\Models\TrendingBook::create(['book_id' => $book->id]);
        }

        return response()->json($book, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $book = Book::findOrFail($id);
        return response()->json($book);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Book $book)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $book = Book::findOrFail($id);
        $book->update($request->all());
        return response()->json($book);
    }

    /**
     * Remove the specified resource in storage.
     */
    public function destroy($id)
    {
        Book::findOrFail($id)->delete();
        return response()->json(null, 204);
    }

    /**
     * Display a listing of the trending books.
     */
    public function trending()
    {
        $trendingBooks = Book::join('trending_books', 'books.id', '=', 'trending_books.book_id')
                         ->select('books.*')
                         ->orderBy('trending_books.created_at', 'desc')
                         ->get();

        return response()->json($trendingBooks);
    }
}
