<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    /** field sensitif/otomatis yang diabaikan dari audit */
    protected array $auditIgnore = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'current_team_id', // kalau pakai jetstream
    ];

    /** field timestamp yang (opsional) diabaikan saat updated */
    protected array $auditIgnoreOnUpdate = [
        'updated_at',
    ];

    /** request-id per request untuk mengelompokkan banyak log */
    protected static ?string $auditRequestId = null;

    public static function bootAuditable(): void
    {
        static::created(function (Model $model) {
            self::writeAudit('created', $model, [
                'before'  => null,
                'after'   => self::maskAttributes($model->getAttributes(), $model),
                'changed' => array_keys(self::maskAttributes($model->getAttributes(), $model)),
            ]);
        });

        // gunakan "updating" untuk jaga-jaga dapat original; "updated" juga tetap valid
        static::updated(function (Model $model) {
            // field yang berubah menurut Eloquent
            $changes = $model->getChanges();

            // buang field yang memang mau diabaikan
            $changes = Arr::except($changes, self::fieldsToIgnoreOnUpdate($model));

            if (empty($changes)) {
                return;
            }

            $old = Arr::only($model->getOriginal(), array_keys($changes));
            $new = Arr::only($model->getAttributes(), array_keys($changes));

            $old = self::maskAttributes($old, $model);
            $new = self::maskAttributes($new, $model);

            self::writeAudit('updated', $model, [
                'before'  => $old,
                'after'   => $new,
                'changed' => array_keys($new),
            ]);
        });

        static::deleted(function (Model $model) {
            // soft delete vs force delete
            $usesSoftDeletes = method_exists($model, 'getDeletedAtColumn');
            $original = self::maskAttributes($model->getOriginal(), $model);

            $after = $usesSoftDeletes
                ? ['deleted_at' => $model->{$model->getDeletedAtColumn()}]
                : null;

            self::writeAudit('deleted', $model, [
                'before'  => $original,
                'after'   => $after,
                'changed' => $usesSoftDeletes ? ['deleted_at'] : ['force_deleted'],
                'meta'    => ['soft_delete' => $usesSoftDeletes, 'force_delete' => !$usesSoftDeletes],
            ]);
        });

        // kalau model pakai SoftDeletes, catat event restore juga
        if (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive(static::class))) {
            static::restored(function (Model $model) {
                $new = self::maskAttributes($model->getAttributes(), $model);
                self::writeAudit('restored', $model, [
                    'before'  => ['deleted_at' => null], // indikatif
                    'after'   => $new,
                    'changed' => ['deleted_at'],
                ]);
            });
        }
    }

    /** Tulis ke tabel audit_logs, aman dari error (tidak mengganggu transaksi utama) */
    protected static function writeAudit(string $event, Model $model, array $payload): void
    {
        try {
            $req = request();
            $user = Auth::user();
            self::$auditRequestId ??= (string) Str::uuid();

            // siapkan metadata tambahan
            $meta = [
                'model'        => class_basename($model),
                'connection'   => $model->getConnectionName(),
                'table'        => $model->getTable(),
                'route'        => $req?->route()?->getName(),
                'action'       => $req?->route()?->getActionName(),
                'guard'        => Auth::getDefaultDriver(),
                'user_email'   => $user?->email,
                'user_name'    => $user?->name,
                'locale'       => app()->getLocale(),
                'app_env'      => config('app.env'),
                'app_version'  => config('app.version'),
            ];

            // gabungkan meta manual dari payload jika ada
            if (!empty($payload['meta'])) {
                $meta = array_merge($meta, (array) $payload['meta']);
                unset($payload['meta']);
            }

            // properti legacy (biar kompatibel): simpan ringkas "changed"
            $legacyProperties = [
                'event'   => $event,
                'before'  => $payload['before'] ?? null,
                'after'   => $payload['after'] ?? null,
                'changed' => $payload['changed'] ?? [],
            ];

            AuditLog::create([
                'description'  => "audit:{$event}",
                'event'        => $event,
                'subject_id'   => $model->getKey(),
                'subject_type' => get_class($model),
                'user_id'      => $user?->getKey(),
                'before'       => $payload['before'] ?? null,
                'after'        => $payload['after'] ?? null,
                'changed'      => $payload['changed'] ?? [],
                'meta'         => $meta,
                'properties'   => $legacyProperties, // legacy
                'host'         => $req?->getHost(),
                'url'          => $req?->fullUrl(),
                'method'       => $req?->method(),
                'ip'           => $req?->ip(),
                'user_agent'   => $req?->userAgent(),
                'request_id'   => self::$auditRequestId,
            ]);
        } catch (\Throwable $e) {
            // jangan sampai audit bikin operasi utama gagal
            logger()->warning('Audit write failed: ' . $e->getMessage(), ['exception' => $e]);
        }
    }

    /** Filter atribut berdasarkan $auditIgnore + $hidden model */
    protected static function maskAttributes(array $attrs, Model $model): array
    {
        $ignore = array_flip(array_unique(array_merge(
            (property_exists($model, 'auditIgnore') ? $model->auditIgnore : []),
            (property_exists($model, 'auditIgnoreOnUpdate') ? $model->auditIgnoreOnUpdate : []),
            $model->getHidden(),
            (new static)->auditIgnore
        )));

        // hilangkan null yang tidak berarti pada created agar payload tidak bengkak
        $filtered = [];
        foreach ($attrs as $k => $v) {
            if (isset($ignore[$k])) {
                continue;
            }
            // contoh: abaikan null default agar ringkas
            if ($v === null) {
                continue;
            }
            $filtered[$k] = $v;
        }

        return $filtered;
    }

    /** daftar field yang diabaikan saat updated */
    protected static function fieldsToIgnoreOnUpdate(Model $model): array
    {
        return array_unique(array_merge(
            (property_exists($model, 'auditIgnoreOnUpdate') ? $model->auditIgnoreOnUpdate : []),
            (new static)->auditIgnoreOnUpdate
        ));
    }
}
