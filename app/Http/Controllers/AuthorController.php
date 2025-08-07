<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Http\Requests\StoreAuthorRequest;
use App\Http\Requests\UpdateAuthorRequest;

class AuthorController extends Controller
{
    public function index()
    {
        $authors = Author::with('books')->get();
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
        $author = Author::with('books')->findOrFail($id);
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
        return response()->json($author);
    }

    public function destroy($id)
    {
        $author = Author::findOrFail($id);
        $author->delete();
        return response()->json(['message' => 'Author deleted successfully']);
    }
}
