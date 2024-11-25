<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\LogsChanges;

class User extends Authenticatable
{
    use HasFactory, Notifiable, LogsChanges;

    protected $fillable = [
        'username',
        'email',
        'password',
        'birthday',
        'is_two_fa_enabled',
        'two_fa_code',
        'two_fa_expires_at',
        'two_fa_device_id',
    ];

    protected $hidden = [
        'password',
        'two_fa_code',  // Скрываем 2FA код
        // 'remember_token',
    ];

    protected $casts = [
        // 'email_verified_at' => 'datetime',
        'birthday' => 'date',
        'is_two_fa_enabled' => 'boolean',
        'two_fa_expires_at' => 'datetime',
        'last_request_time' => 'datetime',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function hasPermission(string $permissionName): bool
    {
        return $this->roles()
            ->whereNull('user_roles.deleted_at') // Фильтр на мягкое удаление
            ->whereHas('permissions', function ($query) use ($permissionName) {
                $query->where('code', $permissionName);
            })->exists();
    }
}
