<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'user_id',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'favicon',
    ];

    /**
     * Get the value of a setting by key.
     *
     * @param string $key
     * @return mixed
     */
    public static function get($key)
    {
        return self::where('key', $key)->value('value');
    }

    /**
     * Set the value of a setting by key.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function set($key, $value)
    {
        self::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
