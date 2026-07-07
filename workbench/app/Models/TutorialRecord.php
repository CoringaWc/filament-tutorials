<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Model;

class TutorialRecord extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'title',
        'status',
        'summary',
    ];
}
