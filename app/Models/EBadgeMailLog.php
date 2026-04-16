<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EBadgeMailLog extends Model
{
    protected $table = 'e_badge_mail_logs';

    protected $fillable = [
        'user_detail_id',
        'regid',
        'category',
        'email',
        'status',
        'message',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];
}
