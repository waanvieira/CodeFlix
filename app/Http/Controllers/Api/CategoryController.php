<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;

class CategoryController extends BasicCrudController
{

    /** @vard array */
    private $rules;

    public function __construct()
    {
        $this->rules = [
            'name'      => 'required|max:255',
            'description' => 'nullable',
            'is_active' => 'boolean'
        ];
    }

    protected function model()
    {
        return Category::class;
    }

    protected function rulesStore()
    {
        return $this->rules;
    }

    protected function rulesUpdate()
    {
        return $this->rules;
    }


}
