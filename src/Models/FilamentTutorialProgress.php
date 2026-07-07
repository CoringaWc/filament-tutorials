<?php

declare(strict_types=1);

namespace CoringaWc\FilamentTutorials\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $user_type
 * @property string $user_id
 * @property string $panel_id
 * @property string $tutorial_key
 * @property string $status
 * @property string|null $last_step_key
 * @property int|null $last_step_index
 * @property array<string, mixed>|null $metadata
 * @property Carbon|null $started_at
 * @property Carbon|null $completed_at
 * @property Carbon|null $dismissed_at
 * @property Carbon|null $restarted_at
 */
class FilamentTutorialProgress extends Model
{
    public const StatusStarted = 'started';

    public const StatusCompleted = 'completed';

    public const StatusDismissed = 'dismissed';

    /** @var list<string> */
    protected $fillable = [
        'user_type',
        'user_id',
        'panel_id',
        'tutorial_key',
        'status',
        'last_step_key',
        'last_step_index',
        'metadata',
        'started_at',
        'completed_at',
        'dismissed_at',
        'restarted_at',
    ];

    public function getTable(): string
    {
        return config('filament-tutorials.progress.table', 'filament_tutorial_progress');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'dismissed_at' => 'datetime',
            'restarted_at' => 'datetime',
        ];
    }
}
