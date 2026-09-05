<?php

namespace App\Http\Controllers;

use App\Actions\UpdateProfile;
use App\Http\Requests\PasswordRequest;
use App\Http\Requests\ProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile', ['user' => $request->user()]);
    }

    public function update(ProfileRequest $request, UpdateProfile $update): RedirectResponse
    {
        $update->handle($request->user(), $request->safe()->only(['name', 'email', 'bio']), $request->file('avatar'), $request->boolean('remove_avatar'));

        return to_route('profile.edit')->with('status', 'Profile updated.');
    }

    public function password(PasswordRequest $request): RedirectResponse
    {
        $request->user()->forceFill(['password' => $request->validated('password'), 'remember_token' => Str::random(60)])->save();
        $request->session()->regenerate();

        return to_route('profile.edit')->with('status', 'Password changed.');
    }
}
