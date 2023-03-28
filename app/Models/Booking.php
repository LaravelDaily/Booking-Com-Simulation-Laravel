<?php

namespace App\Models;

use App\Traits\ValidForRange;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes, ValidForRange;

    protected $fillable = [
        'apartment_id',
        'user_id',
        'start_date',
        'end_date',
        'guests_adults',
        'guests_children',
        'total_price',
        'rating',
        'review_comment',
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }
}
