<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'person_id',
        'type',
        'title',
        'message',
        'dashboard_link',
        'data',
        'read',
        'email_sent',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read' => 'boolean',
        'email_sent' => 'boolean',
        'created_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    public function scopeUnread($query)
    {
        return $query->where('read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('read', true);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function markAsRead()
    {
        $this->update([
            'read' => true,
            'read_at' => now()
        ]);
    }

    public function getTimeAgoAttribute()
    {
        $now = now();
        $diff = $now->diffInHours($this->created_at);
        
        if ($diff < 1) {
            return "À l'instant";
        }
        if ($diff < 24) {
            return "Il y a " . $diff . "h";
        }
        $days = $now->diffInDays($this->created_at);
        return "Il y a " . $days . "j";
    }
}