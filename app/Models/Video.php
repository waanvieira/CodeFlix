<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Video extends Model
{
    use SoftDeletes;
    use UuidTrait;

    const NO_RATING = 'L';

    const RATING_LIST = ['L', '10', '12', '14', '16', '18'];
    
    const BANNER_FILE_MAX_SIZE = 10000; // 10 MB
    const TRAILER_FILE_MAX_SIZE = 1000000; // 1GB
    const THUMB_FILE_MAX_SIZE = 5000; //5 MB
    const VIDEO_FILE_MAX_SIZE = 50000000; //50 GB

    protected $fillable = [
        'title',
        'banner_file',
        'trailer_file',
        'thumb_file',
        'video_file',        
        'description',
        'year_launched',
        'opened',
        'rating',
        'duration'
    ];

    protected $dates = ['deleted_at'];
    protected $casts = [
        'id' => 'string',
        'opened' => 'boolean',
        'year_launched' => 'integer',
        'duration' => 'integer'
    ];

    public $incrementing = false;

    public static $fileFields = ['banner_file', 'trailer_file', 'thumb_file', 'video_file'];

    public static function create(array $attributes = [])
    {
        $files = self::extractFiles($attributes);
        try {
            DB::beginTransaction();
            /** @var Video $obj */
            $obj = static::query()->create($attributes);
            static::handleRelations($obj, $attributes);
            $obj->uploadFiles($files);
            DB::commit();
            return $obj;
        } catch (\Exception $e) {
            if (isset($obj)) {
                $obj->deleteFiles($files);
            }
            DB::rollBack();
            throw $e;
        }
    }

    public function update(array $attributes = [], array $options = [])
    {
        $files = self::extractFiles($attributes);
        try {
            DB::beginTransaction();
            $saved = parent::update($attributes, $options);
            static::handleRelations($this, $attributes);
            if ($saved) {
                $this->uploadFiles($files);
            }
            DB::commit();
            if ($saved && count($files)) {
                $this->deleteOldFiles();
            }
            return $saved;
        } catch (\Exception $e) {
            $this->deleteFiles($files);
            DB::rollBack();
            throw $e;
        }
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class)->withTrashed();
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class)->withTrashed();
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
