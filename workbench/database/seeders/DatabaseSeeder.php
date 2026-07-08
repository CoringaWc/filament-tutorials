<?php

declare(strict_types=1);

namespace Workbench\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Workbench\App\Models\TutorialRecord;
use Workbench\App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'workbench@example.test'],
            [
                'name' => 'Usuario Workbench',
                'password' => Hash::make('password'),
            ],
        );

        TutorialRecord::query()->updateOrCreate(
            ['title' => 'Registro de exemplo'],
            [
                'status' => 'draft',
                'summary' => 'Registro usado pelo laboratório visual do plugin.',
            ],
        );
    }
}
