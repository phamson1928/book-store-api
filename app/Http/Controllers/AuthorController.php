<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Http\Requests\StoreAuthorRequest;
use App\Http\Requests\UpdateAuthorRequest;
use App\Models\Book;

class AuthorController extends Controller
{
    public function index()
    {
        $authors = Author::withCount('books')->get();
        return response()->json($authors);
    }

    public function store(StoreAuthorRequest $request)
    {
        $data = $request->validated();
        
        // Handle image upload
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('authors', 'public');
            $data['image'] = $path;
        }
        
        $author = Author::create($data);
        return response()->json($author, 201);
    }

    public function show($id)
{
    $author = Author::withCount('books')
        ->with(['books' => function($query){
            $query -> select('id', 'author_id', 'title', 'price', 'discount_price');
        }])
        ->findOrFail($id);

    return response()->json([
        'author' => [
            'id' => $author->id,
            'name' => $author->name,
            'booksCount' => $author->books_count,
            'books' => $author->books,
            'joiningYear' => $author-> created_at
        ]
    ]);
}


    public function update(UpdateAuthorRequest $request, $id)
    {
        $author = Author::findOrFail($id);
        $data = $request->validated();
        
        // Handle image upload if provided
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('authors', 'public');
            $data['image'] = $path;
        }
        $author->update($data);
        return response()->json($author);
    }

    public function destroy($id)
    {
        $author = Author::findOrFail($id);
        $author->delete();
        return response()->json(['message' => 'Author deleted successfully']);
    }

     public function stats(){
        $authorsTotal = Author::count();
        $booksTotal = Book::count();
        $maleAuthors = Author::where('gender','male')->count();
        $femaleAuthors = Author::where('gender','female')->count();
        return response()->json([
            'authorsTotal' => $authorsTotal,
            'booksTotal' => $booksTotal,
            'maleAuthors' => $maleAuthors,
            'femaleAuthors' => $femaleAuthors
        ]);
    }
}
