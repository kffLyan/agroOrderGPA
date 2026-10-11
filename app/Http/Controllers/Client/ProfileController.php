<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __invoke(): View
    {
        return view('client.profile-edit', [
            'user' => Auth::user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $data = $request->only(['name', 'email', 'address']);

        // Only update password if a new password was provided
        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $user->fill($data)->save();

        return Redirect::route('client.profile.edit')
            ->with('status', 'profile-updated');
    }
}