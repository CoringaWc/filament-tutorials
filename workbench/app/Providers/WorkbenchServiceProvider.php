<?php

declare(strict_types=1);

namespace Workbench\App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class WorkbenchServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom([
            dirname(__DIR__, 3).'/database/migrations',
            dirname(__DIR__, 2).'/database/migrations',
        ]);

        Route::redirect('/', '/admin/workbench-dashboard');
    }
}
