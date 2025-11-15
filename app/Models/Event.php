<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Event extends Model
{
    use HasFactory;

    // allow mass assignment for seeded fields
    protected $fillable = [
        'name',
        'slug',
        'start_date',
        'end_date',
        'timestamp',
        'capacity',
    ];
}
