<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Returns extends Model
{
    use HasFactory;

    protected $fillable = [
        'idrequest',
        'idreturner',
        'is_approve',
    ];

    protected $hidden = [
        'password',
        'is_admin',
        'statusrequest',
        'is_available',
        'idrequester',
        'iditem'
    ];
}
