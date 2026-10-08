<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RiderAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('rider.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('rider')->attempt($credentials)) {
            $user = Auth::guard('rider')->user();
            if (!$user->is_approved) {
                Auth::guard('rider')->logout();
                return back()->withErrors([
                    'email' => 'Your account is pending admin approval.',
                ]);
            }
            $request->session()->regenerate();
            return redirect()->route('rider.dashboard');
        }

        return back()->withErrors([
            'email' => 'These credentials do not match our records.',
        ]);
    }

    public function showRegisterForm()
    {
        return view('rider.auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:riders',
            'password' => 'required|confirmed|min:8',
            'phone' => 'required|string|regex:/^[0-9]{11}$/', // 11 digits
            'cnic_number' => 'required|string|unique:riders', // 13 digits format usually, string for now
            'vehicle_type' => 'required|string',
            'vehicle_number' => 'required|string',
            'address' => 'required|string',
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:20480',
            'cnic_front' => 'required|image|mimes:jpeg,png,jpg,webp|max:20480',
            'cnic_back' => 'required|image|mimes:jpeg,png,jpg,webp|max:20480',
            'license_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:20480',
            'vehicle_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:20480',
            'registration_book' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:20480',
        ]);

        // Handle File Uploads
        $profileImagePath = $request->file('profile_image')->store('rider_docs/profile', 'public');
        $cnicFrontPath = $request->file('cnic_front')->store('rider_docs/cnic', 'public');
        $cnicBackPath = $request->file('cnic_back')->store('rider_docs/cnic', 'public');
        $licensePath = $request->file('license_image')->store('rider_docs/license', 'public');
        $vehicleImagePath = $request->file('vehicle_image')->store('rider_docs/vehicle', 'public');
        $regBookPath = $request->hasFile('registration_book')
            ? $request->file('registration_book')->store('rider_docs/vehicle', 'public')
            : null;

        $rider = Rider::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'],
            'cnic_number' => $validated['cnic_number'],
            'vehicle_type' => $validated['vehicle_type'],
            'vehicle_number' => $validated['vehicle_number'],
            'address' => $validated['address'],
            'profile_image' => $profileImagePath,
            'cnic_front' => $cnicFrontPath,
            'cnic_back' => $cnicBackPath,
            'license_image' => $licensePath,
            'vehicle_image' => $vehicleImagePath,
            'registration_book' => $regBookPath,
            'status' => 'offline',
            'is_approved' => false, // Pending Approval
        ]);

        // Do NOT login automatically.
        // Auth::guard('rider')->login($rider);

        // Redirect with success message
        return redirect()->route('rider.login')->with('success', 'Registration successful! Your account is pending admin approval.');
    }

    public function logout(Request $request)
    {
        Auth::guard('rider')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('rider.login');
    }
}
