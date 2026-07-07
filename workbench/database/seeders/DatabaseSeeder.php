<?php

declare(strict_types=1);

namespace Workbench\Database\Seeders;

use Illuminate\Database\Seeder;
use Workbench\App\Models\TutorialRecord;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        TutorialRecord::query()->updateOrCreate(
            ['title' => 'Registro de exemplo'],
            [
                'status' => 'draft',
                'summary' => 'Registro usado pelo laboratório visual do plugin.',
            ],
        );
    }
}
