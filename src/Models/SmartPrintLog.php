<?php

namespace BitDreamIT\QzTray\Models;

use Illuminate\Database\Eloquent\Model;

class SmartPrintLog extends Model
{
    protected $table = 'smart_print_logs';

    protected $fillable = [
        'user_id',
        'tenant_id',
        'url',
        'type',
        'printer',
        'copies',
        'status',
        'method_used',
        'error_message',
        'ip_address',
        'user_agent',
        'metadata',
        'response',
    ];

    protected $casts = [
        'metadata' => 'array',
        'response' => 'array',
        'copies' => 'integer',
    ];

    /**
     * Relationship with user
     */
    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    /**
     * Scope for successful prints
     */
    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope for failed prints
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope by method used
     */
    public function scopeByMethod($query, $method)
    {
        return $query->where('method_used', $method);
    }

    /**
     * Get readable status
     */
    public function getStatusTextAttribute()
    {
        return ucfirst($this->status);
    }

    /**
     * Get formatted created at
     */
    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('Y-m-d H:i:s');
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
