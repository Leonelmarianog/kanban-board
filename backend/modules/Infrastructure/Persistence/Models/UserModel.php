<?php

namespace Modules\Infrastructure\Persistence\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @use HasFactory<UserFactory>
 */
class UserModel extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuids, Notifiable, SoftDeletes;

    protected $table = 'users';

    /** @var list<string> */
    protected $fillable = [
        'id',
        'first_name',
        'last_name',
        'email',
        'password',
        'username',
        'picture',
        'bio',
    ];

    /** @var list<string> */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
