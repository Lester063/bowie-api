<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestCommunication extends Model
{
    use HasFactory;

    protected $fillable = [
        'idRequest',
        'idSender',
        'message',
        'isRead',
    ];

    protected $hidden = [
        'password',
        'isAdmin'
    ];
}
