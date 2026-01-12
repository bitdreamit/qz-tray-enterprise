<?php

namespace BitDreamIT\QzTray\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QzPrinter extends Model
{
    protected $table = 'qz_printers';

    protected $fillable = [
        'tenant_id',
        'name',
        'type',
        'connection_type',
        'driver',
        'port',
        'ip_address',
        'status',
        'is_default',
        'capabilities',
        'last_seen',
        'last_used',
        'error_count',
        'metadata'
    ];

    protected $casts = [
        'capabilities' => 'array',
        'last_seen' => 'datetime',
        'last_used' => 'datetime',
        'is_default' => 'boolean',
        'metadata' => 'array'
    ];

    /**
     * Get the print jobs for this printer
     */
    public function printJobs(): HasMany
    {
        return $this->hasMany(PrintJob::class, 'printer', 'name');
    }

    /**
     * Get printer status badge
     */
    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            'online' => '<span class="badge bg-success">Online</span>',
            'offline' => '<span class="badge bg-danger">Offline</span>',
            'error' => '<span class="badge bg-warning">Error</span>',
            'maintenance' => '<span class="badge bg-info">Maintenance</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    /**
     * Get connection type badge
     */
    public function getConnectionBadgeAttribute(): string
    {
        $badges = [
            'usb' => '<span class="badge bg-info">USB</span>',
            'network' => '<span class="badge bg-primary">Network</span>',
            'bluetooth' => '<span class="badge bg-warning">Bluetooth</span>',
            'virtual' => '<span class="badge bg-secondary">Virtual</span>',
            'shared' => '<span class="badge bg-success">Shared</span>',
        ];

        return $badges[$this->connection_type] ?? '<span class="badge bg-dark">Unknown</span>';
    }

    /**
     * Get printer type badge
     */
    public function getTypeBadgeAttribute(): string
    {
        $badges = [
            'label' => '<span class="badge bg-success">Label</span>',
            'receipt' => '<span class="badge bg-warning">Receipt</span>',
            'laser' => '<span class="badge bg-primary">Laser</span>',
            'inkjet' => '<span class="badge bg-info">Inkjet</span>',
            'dot_matrix' => '<span class="badge bg-secondary">Dot Matrix</span>',
            'virtual' => '<span class="badge bg-dark">Virtual</span>',
        ];

        return $badges[$this->type] ?? '<span class="badge bg-secondary">Standard</span>';
    }

    /**
     * Check if printer supports a specific feature
     */
    public function supports(string $feature): bool
    {
        if (!$this->capabilities || !is_array($this->capabilities)) {
            return false;
        }

        return in_array($feature, $this->capabilities);
    }

    /**
     * Get printer statistics
     */
    public function getStatsAttribute(): array
    {
        return [
            'total_jobs' => $this->printJobs()->count(),
            'successful_jobs' => $this->printJobs()->where('status', 'completed')->count(),
            'failed_jobs' => $this->printJobs()->where('status', 'failed')->count(),
            'last_24_hours' => $this->printJobs()->where('created_at', '>=', now()->subDay())->count(),
            'avg_print_time' => $this->printJobs()
                    ->where('status', 'completed')
                    ->whereNotNull('completed_at')
                    ->avg(\DB::raw('TIMESTAMPDIFF(SECOND, created_at, completed_at)')) ?? 0,
        ];
    }

    /**
     * Scope: Online printers
     */
    public function scopeOnline($query)
    {
        return $query->where('status', 'online');
    }

    /**
     * Scope: Default printer
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope: By type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: By connection type
     */
    public function scopeByConnection($query, string $connectionType)
    {
        return $query->where('connection_type', $connectionType);
    }

    /**
     * Scope: Recently used
     */
    public function scopeRecentlyUsed($query, int $hours = 24)
    {
        return $query->where('last_used', '>=', now()->subHours($hours));
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
