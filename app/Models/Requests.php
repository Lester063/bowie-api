<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requests extends Model
{
    use HasFactory;

    protected $fillable = [
        'idRequester',
        'idItem',
        'statusRequest',
        'isReturnSent'
    ];

    protected $hidden = [
        'password',
        'isAdmin'
    ];
}
