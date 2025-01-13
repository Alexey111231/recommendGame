<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Wkhooy\ObsceneCensorRus;

class User extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function booted()
    {
        static::updating(function ($user) {
            // Если новое значение score больше текущего, обновляем его
            if ($user->score > $user->getOriginal('score')) {
                $user->score = (int) $user->score;
            } else {
                $user->score = $user->getOriginal('score');
                $user->reasons = $user->getOriginal('reasons');
            }
        });
    }

    public function app() {
        return $this->belongsTo(App::class);
    }
    public function attempts()
    {
        return $this->hasMany(Attempt::class);
    }
}
