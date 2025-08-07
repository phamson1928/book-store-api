<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    public function index()
    {
        $authors = Author::with('books')->get();
        return response()->json($authors);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'age' => 'required|integer',
            'gender' => 'required|string',
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'description' => 'required|string',
        ]);
        
        $path = $request->file('image')->store('authors','public');
        $data = $request->except('image');
        $data['image'] = $path;
        $author = Author::create($data);
        return response()->json($author, 201);
    }

    public function show($id)
    {
        $author =  Author::with('books')->findOrFail($id);
        return response()->json($author);
    }

    public function update(Request $request, Author $author)
    {
        $request->validate([
            'name' => 'required|string',
            'age' => 'required|integer',
            'gender' => 'required|string',
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'description' => 'required|string',
        ]);
        $path = $request->file('image')->store('authors','public');
        $data = $request->except('image');
        $data['image'] = $path;
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
