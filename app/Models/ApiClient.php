<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiClient extends Model
{
    protected $fillable = [
        'client_name',
        'api_key',
        'api_secret',
    ];
}
