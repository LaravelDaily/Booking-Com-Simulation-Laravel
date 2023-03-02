<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = ['apartment_id', 'room_type_id', 'name'];

    public function room_type()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function beds()
    {
        return $this->hasMany(Bed::class);
    }
}
