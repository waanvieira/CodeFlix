<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;

class CategoryController extends BasicCrudController
{
    protected function model()
    {
        return Category::class;
    }

    protected function rulesStore()
    {
        return [
            'name'      => 'required|max:255',
            'description' => 'nullable',
            'is_active' => 'boolean'
        ];
    }
    
    // public function index()
    // {
    //     return Category::all();
    // }

    // public function store(Request $request)
    // {
    //     $this->validate($request, $this->rules);
    //     $category = Category::create($request->all());
    //     $category->refresh();
    //     return $category;
    // }

    // public function show(Category $category)
    // {
    //     //Para teste
    //     return $category;
    // }

    // public function update(Request $request, Category $category)
    // {
    //     $this->validate($request, $this->rules);
    //     $category->update($request->all());
    //     return $category;
    // }

    // public function destroy(Category $category)
    // {
    //     $category->delete();
    //     return response()->noContent(); //204 - No content
    // }
}
