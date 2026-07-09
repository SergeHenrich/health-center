<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\App;

trait HasAuditLog
{
    public static function bootHasAuditLog(): void
    {
        foreach (['created', 'updated', 'deleted'] as $event) {
            static::$event(function ($model) use ($event) {
                if (App::runningInConsole()) {
                    return;
                }

                AuditLog::create([
                    'user_id'        => auth()->id(),
                    'event'          => $event,
                    'auditable_type' => get_class($model),
                    'auditable_id'   => $model->getKey(),
                    'old_values'     => $event === 'updated' ? $model->getOriginal() : null,
                    'new_values'     => $event === 'deleted' ? null : $model->getAttributes(),
                    'url'            => request()?->fullUrl(),
                    'ip_address'     => request()?->ip(),
                    'user_agent'     => request()?->userAgent(),
                ]);
            });
        }
    }
}
