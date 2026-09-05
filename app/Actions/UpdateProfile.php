<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class UpdateProfile
{
    /** @param array{name:string,email:string,bio?:?string} $data */
    public function handle(User $user, array $data, ?UploadedFile $avatar, bool $remove): void
    {
        $disk = config('hub.avatar_disk');
        $path = $avatar?->store('avatars', $disk);
        throw_if($avatar && ! $path, \RuntimeException::class, 'Unable to store profile image.');
        $old = null;
        try {
            DB::transaction(function () use ($user, $data, $avatar, $remove, $disk, $path, &$old): void {
                $locked = User::query()->lockForUpdate()->findOrFail($user->id);
                $old = [$locked->avatar_disk, $locked->avatar_path];
                $locked->fill($data);
                if ($locked->isDirty('email')) {
                    $locked->email_verified_at = null;
                }
                if ($avatar || $remove) {
                    $locked->avatar_disk = $avatar ? $disk : null;
                    $locked->avatar_path = $avatar ? $path : null;
                }
                $locked->save();
            });
        } catch (Throwable $exception) {
            if ($path) {
                Storage::disk($disk)->delete($path);
            }

            throw $exception;
        }
        if (($avatar || $remove) && $old[1]) {
            Storage::disk($old[0])->delete($old[1]);
        }
    }
}
