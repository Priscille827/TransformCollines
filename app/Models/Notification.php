<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';

    // Ta table n'a que created_at, pas updated_at
    public $timestamps = false;

    protected $fillable = [
        'utilisateur_id', 'code', 'canal', 'contenu', 'lu','created_at',
    ];

    protected $casts = [
        'lu' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class);
    }
}