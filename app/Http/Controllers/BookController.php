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
            'image' => 'nullable|string',
            'publication_date' => 'nullable|date',
            'status' => 'nullable|string',
            'description' => 'nullable|string',
            'language' => 'nullable|string',
            'borrow_count' => 'nullable|integer',
            'borrow_date' => 'nullable|date',
            'return_date' => 'nullable|date',
        ]);

        $data = $request->all();

        $book = Book::create($data);
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
}
