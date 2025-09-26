<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Http\Requests\StoreAuthorRequest;
use App\Http\Requests\UpdateAuthorRequest;
use App\Models\Book;
use Illuminate\Support\Facades\Cache;

class AuthorController extends Controller
{
    public function index()
    {
        $authors = Cache::rememberForever('authors_all', function () {
            return Author::all();
        });
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
        Cache::forget('authors_all');
        return response()->json($author, 201);
    }

    public function show($id)
{
        $author = Cache::rememberForever("author_detail_{$id}", function () use ($id) {
            return Author::findOrFail($id);
        });
        
        return response()->json($author);
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
        Cache::forget('authors_all');
        Cache::forget("author_detail_{$id}");
        return response()->json($author);
    }

    public function destroy($id)
    {
        $author = Author::findOrFail($id);
        $author->delete();
        Cache::forget('authors_all');
        Cache::forget("author_detail_{$id}");
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
