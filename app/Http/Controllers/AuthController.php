<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\OtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterUserRequest;
use Illuminate\Support\Carbon;

class AuthController extends Controller
{
    public function logout(Request $request)
    {
        Auth::logout();
    
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect to the home page after logout
        return redirect()->route('home')->with('success', 'You have been logged out.');
    }

    
public function showOtpForm()
{
    return view('auth.verify-otp');
}


public function verifyOtp(Request $request)
{
    $request->validate([
        'otp' => 'required|digits:6',
    ]);

    $userId = (int) session('otp_user_id');
    if (!$userId) {
        return redirect()->route('register')->withErrors(['otp' => 'Session expired. Please register again.']);
    }

    $user = User::find($userId);

    if (!$user) {
        return back()->withErrors(['otp' => 'User not found. Please try registering again.']);
    }

    if (empty($user->otp) || empty($user->otp_expires_at) || Carbon::parse($user->otp_expires_at)->isPast()) {
        return back()->withErrors(['otp' => 'OTP has expired. Please register again.']);
    }

    if ((string) $user->otp === (string) $request->otp) {
        $user->is_verified = true;
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();

        session()->forget('otp_user_id');

        // Redirect to the login page with a success message
        return redirect()->route('login')->with('success', 'Your account has been verified. Please log in.');
    } else {
        // Return error for invalid OTP
        return back()->withErrors(['otp' => 'Invalid OTP. Please try again.']);
    }
}
public function register(RegisterUserRequest $request) 
{
    try {
        $validated = $request->validated();

        // Generate a 6-digit OTP for email verification
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user = new User([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'mobile' => $request->input('mobile'),
            'address' => $request->input('address'),
            'address2' => $request->input('address2'),
            'city' => $request->input('city'),
            'state' => $request->input('state'),
            'zip' => $request->input('zip'),
            'pickup_time' => $request->input('pickup_time'),
            'is_verified' => false,
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);
        $user->sellerType = 2;
        $user->save();

        Mail::to($request->email)->send(new OtpMail($otp));

        session(['otp_user_id' => $user->id]);
        
        return redirect()->route('verify.otp')->with('success', 'Please check your email for OTP');
        
    } catch (\Exception $e) {
        Log::error('Registration error: ' . $e->getMessage());
        return back()
            ->withInput()
            ->withErrors(['error' => 'Registration failed: ' . $e->getMessage()]);
    }
}
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('login');
    }
    
    public function showRegisterForm()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('register');
    }
    

    public function login(Request $request)
    {
        // Validate input data
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
    
        $credentials = $request->only('email', 'password');
        
        // Test Case 4: Check for empty fields
        if (empty($credentials['email']) || empty($credentials['password'])) {
            return back()->withErrors(['login' => 'Please enter email and password.'])->withInput();
        }
    
        // Attempt to authenticate the user with provided credentials
        if (Auth::attempt($credentials)) {
            // Regenerate session to prevent session fixation attacks
            $request->session()->regenerate();
    
            $user = Auth::user();
    
            // Test Case 3: If user credentials are valid, check for admin
            if ((int) ($user->sellerType ?? 0) === 1) {
                return redirect()->route('admin.dashboard')->with('success', 'You are logged in as admin.');
            }
    
            // Redirect to the intended page or home
            return redirect()->intended('/')->with('success', 'You are logged in.');
        }
    
        // Return a single generic error to prevent email enumeration attacks
        return back()->withErrors(['login' => 'These credentials do not match our records.'])->withInput();
    }
    
    
                    
    public function home()
    {
        return view('home');
    }
}