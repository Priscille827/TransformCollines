<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $table = 'conversations';

    protected $fillable = ['user1_id', 'user2_id', 'dernier_message_le'];

    protected $casts = [
        'dernier_message_le' => 'datetime',
    ];

    public function user1()
    {
        return $this->belongsTo(Utilisateur::class, 'user1_id');
    }

    public function user2()
    {
        return $this->belongsTo(Utilisateur::class, 'user2_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function dernierMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /**
     * Retourne l'autre participant (celui qui n'est pas $userId).
     */
    public function autreUtilisateur(int $userId): ?Utilisateur
    {
        return $this->user1_id === $userId ? $this->user2 : $this->user1;
    }

    /**
     * Trouve ou crée une conversation entre 2 users.
     */
    public static function entre(int $a, int $b): self
    {
        // Normalisation : plus petit ID en user1
        [$u1, $u2] = $a < $b ? [$a, $b] : [$b, $a];

        return self::firstOrCreate([
            'user1_id' => $u1,
            'user2_id' => $u2,
        ]);
    }

    /**
     * Nombre de messages non lus pour un utilisateur donné.
     */
    public function nonLusPour(int $userId): int
    {
        return $this->messages()
            ->where('expediteur_id', '!=', $userId)
            ->whereNull('lu_le')
            ->count();
    }
}