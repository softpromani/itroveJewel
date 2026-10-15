<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'client_name',
        'shop_name',
        'mobile_number',
        'email',
        'status',
        'notes',
    ];

    /**
     * Get the pendrive registrations assigned to this client.
     */
    public function pendriveRegistrations(): HasMany
    {
        return $this->hasMany(PendriveRegistration::class);
    }

    /**
     * Get active pendrives only.
     */
    public function activePendrives(): HasMany
    {
        return $this->hasMany(PendriveRegistration::class)->where('status', 'active');
    }
}
