@extends('layouts.app')

@section('title', 'Register - Bakala Express')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/styleHome.css') }}">
    <style>
        .auth-container {
            min-height: calc(100vh - 400px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            margin-top: 50px;
            margin-bottom: 40px;
        }

        .auth-card {
            width: 100%;
            max-width: 480px;
            background-color: var(--white);
            border-radius: var(--border-radius-lg, 12px);
            box-shadow: var(--shadow-md, 0 4px 6px -1px rgba(0, 0, 0, 0.1));
            padding: 40px 35px;
            overflow: hidden;
            position: relative;
        }

        .auth-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(to bottom, var(--primary-color), var(--primary-dark));
        }

        .auth-form h2 {
            font-size: 26px;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 8px;
            text-align: center;
        }

        .auth-subtitle {
            font-size: 14px;
            color: var(--text-muted, #6B7280);
            text-align: center;
            margin-bottom: 28px;
        }

        .form-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 6px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .input-group-custom {
            position: relative;
            margin-bottom: 18px;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9CA3AF;
            font-size: 15px;
            pointer-events: none;
            z-index: 5;
        }

        .toggle-password-btn {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9CA3AF;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            font-size: 15px;
            z-index: 5;
        }

        .toggle-password-btn:hover {
            color: var(--primary-color);
        }

        .form-control-custom {
            height: 48px;
            width: 100%;
            border: 1px solid var(--light-gray, #E5E7EB);
            border-radius: var(--border-radius-md, 8px);
            padding: 10px 14px 10px 40px;
            font-size: 14px;
            transition: all var(--transition, 0.2s);
            background-color: var(--white);
            color: var(--text-color);
            box-sizing: border-box;
        }

        .form-control-custom.has-toggle {
            padding-right: 40px;
        }

        .form-control-custom:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 166, 81, 0.15);
            border-color: var(--primary-color);
        }

        .form-control-custom.is-invalid {
            border-color: var(--danger, #ef4444);
        }

        .error-feedback {
            color: var(--danger, #ef4444);
            font-size: 0.8rem;
            margin-top: 4px;
            display: block;
            font-weight: 500;
        }

        .btn-register {
            height: 48px;
            width: 100%;
            font-weight: 600;
            font-size: 15px;
            border-radius: var(--border-radius-md, 8px);
            background: linear-gradient(to right, var(--primary-color), var(--primary-dark));
            border: none;
            transition: all var(--transition, 0.2s);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            margin-top: 10px;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md, 0 4px 6px -1px rgba(0, 0, 0, 0.1));
            color: #fff;
        }

        .terms-note {
            font-size: 12px;
            color: var(--text-muted, #6B7280);
            text-align: center;
            margin-top: 14px;
            line-height: 1.5;
        }

        .auth-footer {
            text-align: center;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color, #E5E7EB);
            font-size: 14px;
            color: var(--text-color);
        }

        .auth-footer a {
            color: var(--primary-color);
            font-weight: 600;
            text-decoration: none;
        }

        .auth-footer a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .seller-badge-link {
            display: inline-block;
            margin-top: 12px;
            font-size: 13px;
            color: #6B7280;
        }

        .seller-badge-link a {
            color: var(--primary-dark);
            font-weight: 600;
        }
    </style>
@endsection

@section('content')
    <div class="auth-container">
        <div class="auth-card">
            <form id="registrationForm" class="auth-form" action="{{ route('register') }}" method="POST" novalidate>
                @csrf
                <h2>Create an Account</h2>
                <p class="auth-subtitle">Get fresh groceries delivered quickly to your doorstep</p>

                @if ($errors->has('error'))
                    <div class="alert alert-danger py-2 px-3 text-sm rounded mb-4">
                        {{ $errors->first('error') }}
                    </div>
                @endif

                <!-- Full Name -->
                <div class="mb-3">
                    <label for="name" class="form-label">
                        <i class="fas fa-user text-xs text-gray-400"></i> Full Name
                    </label>
                    <div class="input-group-custom">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" 
                               class="form-control-custom @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               placeholder="e.g. John Doe"
                               value="{{ old('name') }}" 
                               required 
                               autofocus>
                    </div>
                    @error('name')
                        <span class="error-feedback">{{ $message }}</span>
                    @enderror
                    <span id="nameError" class="error-feedback"></span>
                </div>

                <!-- Email Address -->
                <div class="mb-3">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope text-xs text-gray-400"></i> Email Address
                    </label>
                    <div class="input-group-custom">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" 
                               class="form-control-custom @error('email') is-invalid @enderror" 
                               id="email" 
                               name="email" 
                               placeholder="you@example.com"
                               value="{{ old('email') }}" 
                               required>
                    </div>
                    @error('email')
                        <span class="error-feedback">{{ $message }}</span>
                    @enderror
                    <span id="emailError" class="error-feedback"></span>
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock text-xs text-gray-400"></i> Password
                    </label>
                    <div class="input-group-custom">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" 
                               class="form-control-custom has-toggle @error('password') is-invalid @enderror" 
                               id="password" 
                               name="password" 
                               placeholder="At least 6 characters" 
                               minlength="6"
                               required>
                        <button type="button" class="toggle-password-btn" onclick="togglePasswordVisibility('password', this)" aria-label="Toggle password visibility">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <span class="error-feedback">{{ $message }}</span>
                    @enderror
                    <span id="passwordError" class="error-feedback"></span>
                </div>

                <!-- Confirm Password -->
                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">
                        <i class="fas fa-check-circle text-xs text-gray-400"></i> Confirm Password
                    </label>
                    <div class="input-group-custom">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" 
                               class="form-control-custom has-toggle" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               placeholder="Re-enter password" 
                               minlength="6"
                               required>
                        <button type="button" class="toggle-password-btn" onclick="togglePasswordVisibility('password_confirmation', this)" aria-label="Toggle confirm password visibility">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                    <span id="passwordConfirmationError" class="error-feedback"></span>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-register" id="submitBtn">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>

                <p class="terms-note">
                    By registering, you agree to our Terms of Service & Privacy Policy.
                </p>

                <!-- Footer Links -->
                <div class="auth-footer">
                    <p class="mb-0">Already have an account? <a href="{{ route('login') }}">Log in here</a></p>
                    <div class="seller-badge-link">
                        Are you a merchant? <a href="{{ route('register.seller') }}">Join as Seller</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function togglePasswordVisibility(fieldId, btn) {
            const field = document.getElementById(fieldId);
            const icon = btn.querySelector('i');
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        document.getElementById('registrationForm').addEventListener('submit', function (event) {
            // Reset previous client error messages
            const errorFields = document.querySelectorAll('.error-feedback');
            errorFields.forEach(field => {
                if (field.id && field.id.endsWith('Error')) {
                    field.textContent = '';
                }
            });

            let isValid = true;

            const name = document.getElementById('name');
            if (!name.value.trim()) {
                document.getElementById('nameError').textContent = 'Please enter your name';
                isValid = false;
            }

            const email = document.getElementById('email');
            if (!email.value.trim()) {
                document.getElementById('emailError').textContent = 'Please enter your email address';
                isValid = false;
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
                document.getElementById('emailError').textContent = 'Please enter a valid email address';
                isValid = false;
            }

            const password = document.getElementById('password');
            if (!password.value) {
                document.getElementById('passwordError').textContent = 'Please enter a password';
                isValid = false;
            } else if (password.value.length < 6) {
                document.getElementById('passwordError').textContent = 'Password must be at least 6 characters';
                isValid = false;
            }

            const confirmPassword = document.getElementById('password_confirmation');
            if (password.value !== confirmPassword.value) {
                document.getElementById('passwordConfirmationError').textContent = 'Passwords do not match';
                isValid = false;
            }

            if (!isValid) {
                event.preventDefault();
            } else {
                const btn = document.getElementById('submitBtn');
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating account...';
            }
        });
    </script>
@endsection