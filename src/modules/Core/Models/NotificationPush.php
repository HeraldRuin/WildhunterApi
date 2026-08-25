<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NotificationPush extends Model
{
    protected $table = 'notifications';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'for_admin',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'for_admin' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public function payload(): array
    {
        $data = $this->data;

        if (! is_array($data)) {
            return [];
        }

        $notification = $data['notification'] ?? null;

        return is_array($notification) ? $notification : [];
    }
}
