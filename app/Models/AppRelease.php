<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppRelease extends Model
{
    use HasFactory;

    /**
     * The database connection name for the model.
     *
     * @var string|null
     */
    protected $connection = 'admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'version',
        'title',
        'release_notes',
        'download_url',
        'file_path',
        'file_hash',
        'file_size',
        'download_count',
        'min_supported_version',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'release_notes' => 'array',
        'is_active' => 'boolean',
        'file_size' => 'integer',
        'download_count' => 'integer',
    ];

    /**
     * Get human-readable formatted file size string.
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = (int) $this->file_size;
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2).' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 2).' KB';
        }

        return $bytes.' B';
    }

    /**
     * Get the latest active release.
     */
    public static function getLatestActive(): ?self
    {
        return self::where('is_active', true)
            ->latest('id')
            ->first();
    }
}
