<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestCommunication extends Model
{
    use HasFactory;

    protected $fillable = [
        'idrequest',
        'idsender',
        'message',
        'isRead',
    ];

    protected $hidden = [
        'password',
        'is_admin'
    ];
}
