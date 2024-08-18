<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'rating',
        'comment',
        'idRequest'
    ];

    protected $hidden = [
        'password',
        'isAdmin',
        'isReturnSent',
    ];
}
