<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use UuidTrait;

    protected $fillable = ['file'];

    protected $casts = [
        'id' => 'string',
        'file' => 'string'
    ];

    public $incrementing = false;

    /**
     * Get the owning fileable model.
     */
    public function fileable()
    {
        return $this->morphTo();
    }
}
