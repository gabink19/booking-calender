<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SendNotif extends Model
{
    protected $table = 'send_notif';

    protected $fillable = [
        'id',
        'messages',
        'user_id',
        'status',
        'created_at',
        'updated_at',
    ];

    public $timestamps = true;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'uuid');
    }
}