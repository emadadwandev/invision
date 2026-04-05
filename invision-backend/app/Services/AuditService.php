<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditService
{
    /**
     * Log a generic audit event.
     */
    public static function log(
        string $action,
        string $entityType,
        ?int $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?Request $request = null,
    ): AuditLog {
        $request ??= request();
        $user = $request?->user();

        return AuditLog::create([
            'tenant_id' => $user?->tenant_id ?? (app()->bound('current_tenant_id') ? app('current_tenant_id') : null),
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'url' => $request?->fullUrl(),
            'method' => $request?->method(),
        ]);
    }

    /**
     * Log a model creation.
     */
    public static function logCreated(Model $model, ?Request $request = null): AuditLog
    {
        return self::log(
            action: 'create',
            entityType: class_basename($model),
            entityId: $model->getKey(),
            newValues: $model->toArray(),
            request: $request,
        );
    }

    /**
     * Log a model update (captures old + new values).
     */
    public static function logUpdated(Model $model, ?Request $request = null): AuditLog
    {
        $dirty = $model->getDirty();
        $original = array_intersect_key($model->getOriginal(), $dirty);

        return self::log(
            action: 'update',
            entityType: class_basename($model),
            entityId: $model->getKey(),
            oldValues: $original,
            newValues: $dirty,
            request: $request,
        );
    }

    /**
     * Log a model deletion.
     */
    public static function logDeleted(Model $model, ?Request $request = null): AuditLog
    {
        return self::log(
            action: 'delete',
            entityType: class_basename($model),
            entityId: $model->getKey(),
            oldValues: $model->toArray(),
            request: $request,
        );
    }

    /**
     * Log an authentication event.
     */
    public static function logAuth(string $action, ?int $userId = null, ?Request $request = null): AuditLog
    {
        return self::log(
            action: $action,
            entityType: 'Auth',
            entityId: $userId,
            request: $request,
        );
    }

    /**
     * Query audit logs with filters.
     */
    public static function query(int $tenantId, array $filters = [])
    {
        $query = AuditLog::where('tenant_id', $tenantId);

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (!empty($filters['entity_type'])) {
            $query->where('entity_type', $filters['entity_type']);
        }

        if (!empty($filters['entity_id'])) {
            $query->where('entity_id', $filters['entity_id']);
        }

        if (!empty($filters['from'])) {
            $query->where('created_at', '>=', $filters['from']);
        }

        if (!empty($filters['to'])) {
            $query->where('created_at', '<=', $filters['to']);
        }

        return $query->orderByDesc('created_at');
    }
}
