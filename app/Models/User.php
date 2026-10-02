<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable(['name', 'email', 'password', 'department_id', 'role_id', 'job_title', 'phone', 'is_active'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    private const ROLE_PERMISSIONS = [
        'system-administrator' => ['*'],
        'management' => ['dashboard.view','partners.view','agreements.view','approvals.view','obligations.view','documents.view','reports.view','audit.view'],
        'legal-review-officer' => ['dashboard.view','partners.view','agreements.view','agreements.update','approvals.view','approvals.review','documents.view'],
        'department-officer' => ['dashboard.view','partners.view','partners.create','partners.update','agreements.view','agreements.create','agreements.update','obligations.view','obligations.manage','documents.view','documents.manage'],
        'read-only-user' => ['dashboard.view','partners.view','agreements.view','obligations.view','documents.view'],
    ];

    protected function casts(): array
    {
        return ['email_verified_at'=>'datetime','password'=>'hashed','is_active'=>'boolean'];
    }

    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function role(): BelongsTo { return $this->belongsTo(Role::class); }

    public function hasRole(string ...$roles): bool
    {
        return $this->role && in_array($this->role->slug, $roles, true);
    }

    public function hasPermission(string $permission): bool
    {
        if (! $this->is_active || ! $this->role) return false;
        $permissions = self::ROLE_PERMISSIONS[$this->role->slug] ?? [];
        return in_array('*', $permissions, true) || in_array($permission, $permissions, true);
    }

    public function isAdministrator(): bool { return $this->hasRole('system-administrator'); }
    public function canManageUsers(): bool { return $this->hasPermission('users.manage'); }

    public function initials(): string
    {
        $initials = Str::initials($this->name, true);
        return Str::length($initials) > 1 ? Str::substr($initials, 0, 1).Str::substr($initials, -1) : $initials;
    }
}
