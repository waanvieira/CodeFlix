<?php

namespace App\Http\Controllers\Api;

use App\Models\CastMember;
use Illuminate\Http\Request;

class CastMemberController extends BasicCrudController
{
    protected function model()
    {
        return CastMember::class;
    }
   
    protected function rulesStore()
    {
        return [
            'name' => 'required|max:255',
            'type' => 'nullable'
        ];
    }
}
