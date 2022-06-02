<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;
    use UuidTrait;

    protected $fillable = ['name', 'description', 'is_active'];
    protected $dates = ['deleted_at'];
    protected $casts = [
        'id' => 'string',
        'is_active' => 'boolean'
    ];
    //Pra retornar o uuid, sem essa vari�vel o id retorna como 0
    public $incrementing = false;

    public function videos()
    {
        return $this->belongsToMany(Videos::class);
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class);
    }
}
