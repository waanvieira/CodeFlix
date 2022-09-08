<?php

namespace App\Http\Controllers\Api;

use App\Models\Video;
use App\Traits\UploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VideoController extends BasicCrudController
{
    /** @vard array */
    private $rules;
    use UploadTrait;

    public function __construct()
    {
        $this->rules = [
            'title' => 'required|max:255',
            'description' => 'nullable',
            'year_launched' => 'required|max:5',
            'opened' => 'nullable|boolean',
            'rating' => 'required|in:' . implode(',', Video::RATING_LIST),
            'duration' => 'required',
            'categories_id' => 'required|array|exists:categories,id,deleted_at,NULL',
            'genres_id' => 'required|array|exists:genres,id,deleted_at,NULL',
            'file' => 'nullable|mimetypes:video/mp4|max:' . Video::VIDEO_FILE_MAX_SIZE,
        ];
    }

    public function store(Request $request)
    {
        $validateData = $this->validate($request, $this->rulesStore());
        
        $response = DB::transaction(function () use ($validateData) {
            $response = $this->model()::create($validateData);
            $this->handleRelations($response, $validateData);
            $this->putFile($validateData, 'video', $response);
            return $response;
        });

        $response->refresh();
        return $response;
    }

    public function update(Request $request, $id)
    {
        $obj = $this->findOrFail($id);
        $validateData = $this->validate($request, $this->rulesStore());
        $response = DB::transaction(function () use ($validateData, $obj, $request) {
            $obj->update($validateData);
            $this->handleRelations($obj, $request);
            return $obj;
        });

        $response->refresh();
        return $response;
    }

    protected function handleRelations($response, $request)
    {
        $response->genres()->sync($request->get('genres_id'));
        $response->category()->sync($request->get('category_id'));
    }

    public function show($id)
    {
        return $this->model()::with('genres', 'categories')->where('id', $id)->first();
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

    protected function uploadDir()
    {
        return 'video' ;
    }
}
