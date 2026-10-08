<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\UserProfileUpdate;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $profileUpdate = UserProfileUpdate::where('user_id', $user->id)->first();
        return view('profile.edit', ['user' => $user, 'profileUpdate' => $profileUpdate]);
    }
    
    public function update(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return redirect()->route('login')->with('error', 'You must be logged in to update your profile.');
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
                'password' => 'nullable|string|min:8|confirmed',
                'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $user->name = $validated['name'];
            $user->email = $validated['email'];

            if (!empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }

            $user->save();

            if ($request->hasFile('profile_image')) {
                $profileUpdate = UserProfileUpdate::firstOrNew(['user_id' => $user->id]);

                if ($profileUpdate->profile_image && Storage::disk('public')->exists($profileUpdate->profile_image)) {
                    Storage::disk('public')->delete($profileUpdate->profile_image);
                }

                $path = $request->file('profile_image')->store('profile_images', 'public');
                $profileUpdate->profile_image = $path;
                $profileUpdate->save();
            }

            return redirect()->route('profile.edit')->with('success', 'Profile updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Failed to update profile: ' . $e->getMessage()]);
        }
    }
}
