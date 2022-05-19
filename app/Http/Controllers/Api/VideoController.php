<?php

namespace App\Http\Controllers\Api;

use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VideoController extends BasicCrudController
{
    /** @vard array */
    private $rules;

    public function __construct()
    {
        $this->rules = [
            'title' => 'required|max:255',
            'description' => 'nullable',
            'year_launched' => 'required|max:5',
            'opened' => 'nullable',
            'rating' => 'required|in:' . implode(',', Video::RATING_LIST),
            'duration' => 'required',
            'genres_id' => 'required|array|exists:genres,id,deleted_at:null',
            'categories_id' => 'required|array|exists:categories,id,deleted_at:null'
        ];
    }

    protected function model()
    {
        return Video::class;
    }

    public function rulesStore()
    {
        return $this->rules;
    }

    protected function rulesUpdate()
    {
        return $this->rules;
    }

    public function store(Request $request)
    {
        $validateData = Validator::make($request->all(), $this->rulesStore());
        if ($validateData->fails()) {
            return $validateData->errors();
        }

        $response = DB::transaction(function () use ($request){
            $response = $this->model()::create($request->all());
            $response->genres()->sync($request->get('genres_id'));
            $response->category()->sync($request->get('category_id'));
            return $response;
        });

        $response->refresh();
        return $response;
    }

    public function show($id)
    {
        return $this->model()::with('genres', 'categories')->where('id', $id)->first();
    }

    protected function findOrFail($id)
    {
        $model = $this->model();
        $keyName = (new $model)->getRouteKeyName();
        return $this->model()::where($keyName, $id)->firstOrFail();
    }
}
