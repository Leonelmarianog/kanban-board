<?php

namespace Modules\Infrastructure\Persistence\Models;

use Carbon\Carbon;
use Database\Factories\EmailVerificationTokenFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $user_id
 * @property string $token
 * @property Carbon $expires_at
 * @property Carbon|null $used_at
 *
 * @use HasFactory<EmailVerificationTokenFactory>
 */
class EmailVerificationTokenModel extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'email_verification_tokens';

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'token',
        'expires_at',
        'used_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    protected static function newFactory(): EmailVerificationTokenFactory
    {
        return EmailVerificationTokenFactory::new();
    }
}
