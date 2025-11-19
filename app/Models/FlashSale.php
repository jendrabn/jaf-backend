<?php

namespace App\Models;

use Carbon\CarbonInterface;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FlashSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'start_at',
        'end_at',
        'is_active',
    ];

    protected $appends = [
        'status',
        'status_label',
        'status_color',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('d-m-Y H:i:s');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'flash_sale_products')
            ->withPivot([
                'flash_price',
                'stock_flash',
                'sold',
                'max_qty_per_user',
            ])
            ->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeRunningNow(Builder $query, ?CarbonInterface $moment = null): Builder
    {
        $moment ??= now();

        return $query->active()
            ->where('start_at', '<=', $moment)
            ->where('end_at', '>=', $moment);
    }

    public function scopeScheduled(Builder $query, ?CarbonInterface $moment = null): Builder
    {
        $moment ??= now();

        return $query->active()
            ->where('start_at', '>', $moment);
    }

    public function timelineStatus(?CarbonInterface $moment = null): string
    {
        $moment ??= now();

        if (! $this->start_at || ! $this->end_at) {
            return 'scheduled';
        }

        if ($moment->lt($this->start_at)) {
            return 'scheduled';
        }

        if ($moment->between($this->start_at, $this->end_at)) {
            return 'running';
        }

        return 'finished';
    }

    public function isRunning(?CarbonInterface $moment = null): bool
    {
        return $this->timelineStatus($moment) === 'running';
    }

    protected function status(): Attribute
    {
        return Attribute::get(fn () => $this->timelineStatus());
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::get(fn () => $this->statusMeta()['label']);
    }

    protected function statusColor(): Attribute
    {
        return Attribute::get(fn () => $this->statusMeta()['color']);
    }

    private function statusMeta(): array
    {
        return match ($this->timelineStatus()) {
            'scheduled' => [
                'label' => 'Scheduled',
                'color' => 'info',
            ],
            'running' => [
                'label' => 'Running',
                'color' => 'success',
            ],
            default => [
                'label' => 'Finished',
                'color' => 'secondary',
            ],
        };
    }
}
