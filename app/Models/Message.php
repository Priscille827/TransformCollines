<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'messages';

    protected $fillable = ['conversation_id', 'expediteur_id', 'contenu', 'lu_le'];

    protected $casts = [
        'lu_le' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function expediteur()
    {
        return $this->belongsTo(Utilisateur::class, 'expediteur_id');
    }
}