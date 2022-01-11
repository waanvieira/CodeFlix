<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    protected $rules = [
        'name'      => 'required|max:255',
        'is_active' => 'boolean'
    ];

    public function index()
    {
        return Genre::all();
    }

    public function store(Request $request)
    {
        $this->validate($request, $this->rules);
        return Genre::create($request->all());
    }

    public function show(Genre $Genre)
    {
        return $Genre;
    }
    
    public function update(Request $request, Genre $Genre)
    {
        $this->validate($request, $this->rules);
        $Genre->update($request->all());
        return $Genre;
    }
    
    public function destroy(Genre $Genre)
    {
        $Genre->delete();
        return response()->noContent(); //204 - No content
    }
}
