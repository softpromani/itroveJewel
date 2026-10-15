<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendriveRegistration extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'client_id',
        'serial_number',
        'pendrive_model',
        'label',
        'status',
        'registered_at',
        'last_verified_at',
        'verification_count',
        'registered_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'registered_at' => 'datetime',
            'last_verified_at' => 'datetime',
            'verification_count' => 'integer',
        ];
    }

    /**
     * Mutator to ensure serial number is always trimmed.
     */
    public function setSerialNumberAttribute(string $value): void
    {
        $this->attributes['serial_number'] = trim($value);
    }

    /**
     * Get the client that owns this pendrive registration.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Scope to find active registrations by serial number.
     */
    public function scopeActiveBySerial($query, string $serialNumber)
    {
        return $query->where('serial_number', trim($serialNumber))
            ->where('status', 'active')
            ->whereHas('client', function ($q) {
                $q->where('status', 'active');
            });
    }
}
