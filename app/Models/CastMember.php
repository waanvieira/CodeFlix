<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;

class CastMember extends Model
{
    use UuidTrait;
    use UuidTrait;

    protected $fillable = ['name', 'type'];
    protected $dates = ['deleted_at'];
    protected $casts = [
        'id' => 'string'
    ];

    public $incrementing = false;
}
