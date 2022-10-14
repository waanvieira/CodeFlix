<?php

namespace App\Models;

use App\Traits\UploadTrait;
use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Video extends Model
{
    use SoftDeletes;
    use UuidTrait;
    use UploadTrait;

    const NO_RATING = 'L';

    const RATING_LIST = ['L', '10', '12', '14', '16', '18'];
    
    const BANNER_FILE_MAX_SIZE = 1024 * 10; // 10 MB
    const TRAILER_FILE_MAX_SIZE = 1024 * 1024 * 1; // 1GB
    const THUMB_FILE_MAX_SIZE = 1024 * 5; //5 MB
    const VIDEO_FILE_MAX_SIZE = 1024 * 1024 * 50; //50 GB

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

    /**
     *  Método subscreve o método do controller
     *
     * @param array $attributes
     * @return void
     */
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

    /**
     * Método subscreve o método update do controller
     *
     * @param array $attributes
     * @param array $options
     * @return Collection|throw
     */
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

    public static function handleRelations(Video $video, array $attributes)
    {
        if (isset($attributes['categories_id'])) {
            $video->categories()->sync($attributes['categories_id']);
        }
        if (isset($attributes['genres_id'])) {
            $video->genres()->sync($attributes['genres_id']);
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

    protected function uploadDir()
    {
        return $this->id;
    }

    public function getBannerFileAttribute($value)
    {
        return $this->getFile($value);
    }

    public function getTrailerFileAttribute($value)
    {
        return $this->getFile($value);
    }

    public function getThumbFileAttribute($value)
    {
        return $this->getFile($value);
    }

    public function getVideoFileAttribute($value)
    {
        return $this->getFile($value);
    }

}
