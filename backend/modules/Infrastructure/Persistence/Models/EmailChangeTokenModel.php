<?php

namespace Modules\Infrastructure\Persistence\Models;

use Carbon\Carbon;
use Database\Factories\EmailChangeTokenFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $user_id
 * @property string $current_email
 * @property string $new_email
 * @property string $token
 * @property Carbon $expires_at
 * @property Carbon|null $confirmed_at
 *
 * @use HasFactory<EmailChangeTokenFactory>
 */
class EmailChangeTokenModel extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'email_change_tokens';

    /** @var list<string> */
    protected $fillable = [
        'id',
        'user_id',
        'current_email',
        'new_email',
        'token',
        'expires_at',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }

    protected static function newFactory(): EmailChangeTokenFactory
    {
        return EmailChangeTokenFactory::new();
    }
}
