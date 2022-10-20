<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\VideoResource;
use App\Models\Video;
use App\Traits\UploadTrait;
use Illuminate\Http\Request;

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
            'year_launched' => 'required|date_format:Y',
            'opened' => 'boolean',
            'rating' => 'required|in:' . implode(',', Video::RATING_LIST),
            'duration' => 'required|integer',
            'categories_id' => 'required|array|exists:categories,id,deleted_at,NULL',
            'genres_id' => 'required|array|exists:genres,id,deleted_at,NULL',
            'banner_file' => 'image|max:' . Video::BANNER_FILE_MAX_SIZE,
            'thumb_file' => 'image|max:' . Video::THUMB_FILE_MAX_SIZE,
            'trailer_file' => 'nullable|mimetypes:video/mp4|max:' . Video::TRAILER_FILE_MAX_SIZE,
            'video_file' => 'nullable|mimetypes:video/mp4|max:' . Video::VIDEO_FILE_MAX_SIZE,
        ];
    }
    
    public function store(Request $request)
    {
        $validateData = $this->validate($request, $this->rulesStore());
        $obj = $this->model()::create($validateData);
        $obj->refresh();
        $resource = $this->resource();
        return new $resource($obj);
    }

    public function update(Request $request, $id)
    {
        $obj = $this->findOrFail($id);
        $validateData = $this->validate($request, $this->rulesStore());
        $obj->update($validateData);
        $obj->refresh();
        $resource = $this->resource();
        return new $resource($obj);
    }

    public function show($id)
    {
        return parent::show($id);
        // return $this->model()::with('genres', 'categories')->where('id', $id)->first();
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

    protected function resourceCollection()
    {
        return $this->resource();
    }

    protected function resource()
    {
        return VideoResource::class;
    }
}
