<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\FragLinkFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FragLink extends Model
{
    /** @use HasFactory<FragLinkFactory> */
    use HasFactory;

    protected $fillable = [
        'frag_file_id',
        'slug',
        'state',
        'expires_at',
        'password_hash',
        'user_id',
    ];

    /**
     * @return BelongsTo<FragFile, $this>
     */
    public function fragFile(): BelongsTo
    {
        return $this->belongsTo(FragFile::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'password_hash' => 'hashed',
        ];
    }
}
