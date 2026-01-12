<?php

namespace BitDreamIT\QzTray\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintJob extends Model
{
    protected $table = 'print_jobs';

    protected $fillable = [
        'user_id',
        'tenant_id',
        'printer',
        'type',
        'data',
        'options',
        'status',
        'copies',
        'paper_size',
        'orientation',
        'error_message',
        'job_id',
        'ip_address',
        'user_agent',
        'queued_at',
        'started_at',
        'completed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'data' => 'array',
        'options' => 'array',
        'queued_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'copies' => 'integer',
    ];

    /**
     * Status constants
     */
    const STATUS_QUEUED = 'queued';

    const STATUS_PROCESSING = 'processing';

    const STATUS_COMPLETED = 'completed';

    const STATUS_FAILED = 'failed';

    const STATUS_CANCELLED = 'cancelled';

    /**
     * Type constants
     */
    const TYPE_RAW = 'raw';

    const TYPE_HTML = 'html';

    const TYPE_PDF = 'pdf';

    const TYPE_IMAGE = 'image';

    const TYPE_ZPL = 'zpl';

    const TYPE_ESCPOS = 'escpos';

    /**
     * Get the user who queued the job
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id');
    }

    /**
     * Get the printer model
     */
    public function printerModel(): BelongsTo
    {
        return $this->belongsTo(QzPrinter::class, 'printer', 'name');
    }

    /**
     * Get status badge
     */
    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            self::STATUS_QUEUED => '<span class="badge bg-warning">Queued</span>',
            self::STATUS_PROCESSING => '<span class="badge bg-info">Processing</span>',
            self::STATUS_COMPLETED => '<span class="badge bg-success">Completed</span>',
            self::STATUS_FAILED => '<span class="badge bg-danger">Failed</span>',
            self::STATUS_CANCELLED => '<span class="badge bg-secondary">Cancelled</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-dark">Unknown</span>';
    }

    /**
     * Get type badge
     */
    public function getTypeBadgeAttribute(): string
    {
        $badges = [
            self::TYPE_RAW => '<span class="badge bg-secondary">Raw</span>',
            self::TYPE_HTML => '<span class="badge bg-primary">HTML</span>',
            self::TYPE_PDF => '<span class="badge bg-danger">PDF</span>',
            self::TYPE_IMAGE => '<span class="badge bg-info">Image</span>',
            self::TYPE_ZPL => '<span class="badge bg-success">ZPL</span>',
            self::TYPE_ESCPOS => '<span class="badge bg-warning">ESC/POS</span>',
        ];

        return $badges[$this->type] ?? '<span class="badge bg-dark">Unknown</span>';
    }

    /**
     * Get processing time in seconds
     */
    public function getProcessingTimeAttribute(): ?int
    {
        if (! $this->started_at || ! $this->completed_at) {
            return null;
        }

        return $this->started_at->diffInSeconds($this->completed_at);
    }

    /**
     * Get queue time in seconds
     */
    public function getQueueTimeAttribute(): ?int
    {
        if (! $this->queued_at || ! $this->started_at) {
            return null;
        }

        return $this->queued_at->diffInSeconds($this->started_at);
    }

    /**
     * Get total time in seconds
     */
    public function getTotalTimeAttribute(): ?int
    {
        if (! $this->queued_at || ! $this->completed_at) {
            return null;
        }

        return $this->queued_at->diffInSeconds($this->completed_at);
    }

    /**
     * Check if job is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if job failed
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if job is pending
     */
    public function isPending(): bool
    {
        return in_array($this->status, [self::STATUS_QUEUED, self::STATUS_PROCESSING]);
    }

    /**
     * Mark job as processing
     */
    public function markAsProcessing(): bool
    {
        return $this->update([
            'status' => self::STATUS_PROCESSING,
            'started_at' => now(),
        ]);
    }

    /**
     * Mark job as completed
     */
    public function markAsCompleted(): bool
    {
        return $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
            'error_message' => null,
        ]);
    }

    /**
     * Mark job as failed
     */
    public function markAsFailed(string $errorMessage): bool
    {
        return $this->update([
            'status' => self::STATUS_FAILED,
            'completed_at' => now(),
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Mark job as cancelled
     */
    public function markAsCancelled(): bool
    {
        return $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);
    }

    /**
     * Scope: Completed jobs
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope: Failed jobs
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope: Pending jobs
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', [self::STATUS_QUEUED, self::STATUS_PROCESSING]);
    }

    /**
     * Scope: By user
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: By printer
     */
    public function scopeByPrinter($query, string $printer)
    {
        return $query->where('printer', $printer);
    }

    /**
     * Scope: By type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Recent jobs
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    /**
     * Scope: Today's jobs
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Get job statistics
     */
    public static function getStats(): array
    {
        return self::query()
            ->selectRaw('
            COUNT(*) as total,
            SUM(status = "completed") as completed,
            SUM(status = "failed") as failed,
            SUM(status = "pending") as pending,
            SUM(DATE(created_at) = CURDATE()) as today,
            AVG(
                CASE
                    WHEN started_at IS NOT NULL AND completed_at IS NOT NULL
                    THEN TIMESTAMPDIFF(SECOND, started_at, completed_at)
                END
            ) as avg_processing_time,
            AVG(
                CASE
                    WHEN queued_at IS NOT NULL AND started_at IS NOT NULL
                    THEN TIMESTAMPDIFF(SECOND, queued_at, started_at)
                END
            ) as avg_queue_time
        ')
            ->first()
            ->toArray();
    }


    protected static function booted()
    {
        parent::booted();

        static::creating(function ($model) {

            if (! config('qz-tray.tenancy.enabled')) {
                return;
            }

            if (! auth()->check()) {
                return;
            }

            $tenantColumn = config('qz-tray.tenancy.tenant_column', 'tenant_id');

            if (
                isset(auth()->user()->{$tenantColumn}) &&
                empty($model->{$tenantColumn})
            ) {
                $model->{$tenantColumn} = auth()->user()->{$tenantColumn};
            }
        });
    }

}
