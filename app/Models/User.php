<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['name', 'email', 'password', 'bio'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(TaskReport::class);
    }

    public function avatarUrl(): ?string
    {
        return $this->avatar_path ? Storage::disk($this->avatar_disk)->url($this->avatar_path) : null;
    }

    public function initials(): string
    {
        return Str::of($this->name)->trim()->explode(' ')->filter()->take(2)->map(fn (string $part): string => mb_substr($part, 0, 1))->implode('');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
