<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Video extends Model
{
    use SoftDeletes;
    use UuidTrait;

    const NO_RATING = 'L';

    const RATING_LIST = ['L', '10', '12', '14', '16', '18'];
    const VIDEO_FILE_MAX_SIZE = 100000;
    const BANNER_FILE_MAX_SIZE = 1000;
    const TRAILER_FILE_MAX_SIZE = 10000;

    protected $fillable = ['title', 'video_file', 'description', 'year_launched', 'opened', 'rating', 'duration'];
    protected $dates = ['deleted_at'];
    protected $casts = [
        'id' => 'string',
        'opened' => 'boolean',
        'year_launched' => 'integer',
        'duration' => 'integer'
    ];

    public $incrementing = false;

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class);
    }

    public function castMembers()
    {
        return $this->belongsToMany(CastMember::class);
    }

    /**
     *  Get all of the file.
     *     
     */
    public function file()
    {
        return $this->morphOne(File::class, 'fileable');
    }
}
