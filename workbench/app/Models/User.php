<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @var list<string> */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];
}
