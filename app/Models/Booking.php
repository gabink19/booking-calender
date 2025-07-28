<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $table = 'bookings';

    protected $fillable = [
        'date',
        'hour',
        'unit',
        'status',
        'created_at',
        'updated_at',
        'notified_at',
    ];

    public $timestamps = false; // Karena created_at dan updated_at diatur manual di migration
}