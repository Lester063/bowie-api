<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Returns extends Model
{
    use HasFactory;

    protected $fillable = [
        'idRequest',
        'idReturner',
        'isApprove',
        'isReviewed'
    ];

    protected $hidden = [
        'password',
        'isAdmin',
        'statusRequest',
        'isAvailable',
        'idRequester',
        'idItem'
    ];
}
