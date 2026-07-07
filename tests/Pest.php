<?php

declare(strict_types=1);

use CoringaWc\FilamentTutorials\Tests\TestCase;

uses(TestCase::class)->in('Architecture', 'Browser', 'Feature', 'Unit');

pest()->browser()->inChrome()->timeout(10000);
