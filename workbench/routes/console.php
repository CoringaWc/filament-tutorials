<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;

Artisan::command('workbench:ping', fn () => $this->info('pong'));
