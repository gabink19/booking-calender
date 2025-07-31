<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'key_name',
        'value',
        'created_at',
        'updated_at',
    ];

    // If you want to use Laravel's timestamps, uncomment below:
    // const CREATED_AT = 'created_at';
    // const UPDATED_AT = 'updated_at';

    /**
     * Get setting value by key name.
     *
     * @param string $key
     * @return string|null
     */
    public static function getValue($key)
    {
        $setting = self::where('key_name', $key)->first();
        return $setting ? $setting->value : null;
    }
}