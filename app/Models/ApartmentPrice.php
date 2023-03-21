<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApartmentPrice extends Model
{
    use HasFactory;

    protected $fillable = ['apartment_id', 'start_date', 'end_date', 'price'];
}
