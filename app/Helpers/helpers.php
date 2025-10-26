<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

if (! function_exists('formatIDR')) {
    function formatIDR($price)
    {
        return 'Rp ' . number_format($price, 0, ',', '.');
    }
}

if (! function_exists('audit_log')) {
    /**
     * Simpan log audit manual tanpa memicu event model.
     *
     * @param  array  $extra  ['changed' => array, 'meta' => array, 'properties' => array]
     */
    function audit_log(
        string $event,
        string $description,
        ?array $before = null,
        ?array $after = null,
        array $extra = [],
        int|string|null $subjectId = null,
        ?string $subjectType = null
    ): void {
        try {
            $req = request();
            $user = Auth::user();

            // Normalisasi extra
            $changed = (isset($extra['changed']) && is_array($extra['changed'])) ? $extra['changed'] : [];
            $extraMeta = (isset($extra['meta']) && is_array($extra['meta'])) ? $extra['meta'] : [];
            $extraProps = (isset($extra['properties']) && is_array($extra['properties'])) ? $extra['properties'] : [];

            // Meta default + merge
            $meta = array_merge([
                'route' => $req?->route()?->getName(),
                'action' => $req?->route()?->getActionName(),
                'guard' => Auth::getDefaultDriver(),
                'user_email' => $user?->email,
                'user_name' => $user?->name,
                'locale' => app()->getLocale(),
                'app_env' => config('app.env'),
                'app_version' => config('app.version'),
            ], $extraMeta);

            // Properties default + merge
            $properties = array_merge([
                'event' => $event,
                'before' => $before,
                'after' => $after,
            ], $extraProps);

            // Encoder JSON (karena bypass Eloquent casts)
            $J = static function ($value) {
                return $value === null
                    ? null
                    : json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            };

            DB::table('audit_logs')->insert([
                'description' => $description,
                'event' => $event,
                'subject_id' => $subjectId,
                'subject_type' => $subjectType,
                'user_id' => $user?->getKey(),
                'before' => $J($before),
                'after' => $J($after),
                'changed' => $J($changed),
                'meta' => $J($meta),
                'properties' => $J($properties),
                'host' => $req?->getHost(),
                'url' => $req?->fullUrl(),
                'method' => $req?->method(),
                'ip' => $req?->ip(),
                'user_agent' => $req?->userAgent(),
                'request_id' => (string) Str::uuid(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            logger()->warning('Failed to write manual audit: ' . $e->getMessage(), ['exception' => $e]);
        }
    }

    if (! function_exists('normalizePhoneNumber')) {
        function normalizePhoneNumber(string $phone): string
        {
            // Hapus semua spasi, strip, titik
            $phone = preg_replace('/[\s\.\-]/', '', $phone);

            // Jika diawali dengan 08 → ubah jadi +62
            if (preg_match('/^08/', $phone)) {
                $phone = '+62' . substr($phone, 1);
            }
            // Jika diawali dengan 62 tanpa plus → tambahkan +
            elseif (preg_match('/^62/', $phone)) {
                $phone = '+' . $phone;
            }
            // Jika sudah diawali +62 → biarkan
            elseif (! preg_match('/^\+62/', $phone)) {
                // Jika prefix tidak dikenal, bisa langsung ditambahkan +
                $phone = '+' . $phone;
            }

            return $phone;
        }
    }

    if (! function_exists('externalIconLink')) {
        function externalIconLink(string $url, string $iconClass = 'bi bi-box-arrow-up-right'): string
        {
            return
                '<a class="ml-1 text-muted small" href="' . e($url) . '" target="_blank" rel="noopener">
                    <i class="' . e($iconClass) . '"></i>
                </a>';
        }
    }

    if (! function_exists('badgeLabel')) {
        function badgeLabel(string $label, string $color): string
        {
            return '<span class="badge badge-' . e($color) . '">' . e($label) . '</span>';
        }
    }

    if (! function_exists('formatIDR')) {
        function formatIDR(int $number): string
        {
            return 'Rp ' . number_format($number, 0, ',', '.');
        }
    }
}
