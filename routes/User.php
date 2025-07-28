<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'users';

    protected $fillable = [
        'username',
        'password',
        'whatsapp',
    ];

    public $timestamps = false; // Tidak ada kolom created_at/updated_at di tabel users

    // Relasi ke Booking
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}