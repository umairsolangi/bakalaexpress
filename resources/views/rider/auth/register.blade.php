@extends('layouts.app')

@section('title', 'Become a Rider - Bakala Express')

@section('content')
<div class="min-h-screen flex items-center justify-center relative overflow-hidden bg-stone-100 py-12 px-4 sm:px-6 lg:px-8">
    <!-- Background Elements -->
    <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-amber-400/10 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-orange-500/10 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="max-w-2xl w-full relative z-10">
        <div class="text-center mb-10">
            <div class="mx-auto h-20 w-20 bg-gradient-to-br from-amber-400 to-orange-500 rounded-2xl flex items-center justify-center text-white mb-6 shadow-xl shadow-amber-500/30 transform hover:scale-105 transition-transform">
                <i class="fas fa-motorcycle text-4xl"></i>
            </div>
            <h2 class="text-3xl font-extrabold text-gray-900 font-display tracking-tight">
                Join our Delivery Team
            </h2>
            <p class="mt-3 text-sm text-gray-500 font-medium">
                Register as a Rider and start earning on your own schedule
            </p>
        </div>

        <div class="bg-white/90 backdrop-blur-xl py-8 px-4 shadow-2xl rounded-3xl sm:px-10 border border-white/50 relative overflow-hidden">
            <div class="absolute top-0 inset-x-0 h-1.5 bg-gradient-to-r from-amber-400 to-orange-500"></div>

            <form class="space-y-8" action="{{ route('rider.register') }}" method="POST" enctype="multipart/form-data">
                @csrf

                @if ($errors->any())
                    <div class="rounded-2xl bg-red-50 p-5 border border-red-100 flex items-start gap-3">
                        <i class="fas fa-exclamation-circle text-red-500 mt-0.5"></i>
                        <div>
                            <h3 class="text-sm font-bold text-red-800">Registration Failed</h3>
                            <ul class="mt-1 text-xs text-red-600 list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <!-- Personal Information -->
                <div>
                    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2 mb-4">
                        <span class="w-6 h-6 rounded-md bg-amber-100 text-amber-600 flex items-center justify-center text-xs">1</span>
                        Personal Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                            <label for="name" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Full Name <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"><i class="fas fa-user text-gray-400"></i></div>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                    class="block w-full pl-11 pr-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-all" placeholder="Your Name">
                            </div>
                        </div>

                        <div>
                            <label for="email" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Email Address <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"><i class="fas fa-envelope text-gray-400"></i></div>
                                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                    class="block w-full pl-11 pr-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-all" placeholder="rider@example.com">
                            </div>
                        </div>

                        <div>
                            <label for="phone" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Phone Number <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"><i class="fas fa-phone text-gray-400"></i></div>
                                <input type="text" name="phone" id="phone" value="{{ old('phone') }}" required
                                    class="block w-full pl-11 pr-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-all" placeholder="0300-1234567">
                            </div>
                        </div>

                        <div>
                            <label for="cnic_number" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">CNIC Number <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"><i class="fas fa-id-card text-gray-400"></i></div>
                                <input type="text" name="cnic_number" id="cnic_number" value="{{ old('cnic_number') }}" required
                                    class="block w-full pl-11 pr-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-all" placeholder="42101-1234567-1">
                            </div>
                        </div>

                        <div>
                            <label for="address" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Residence Address <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"><i class="fas fa-home text-gray-400"></i></div>
                                <input type="text" name="address" id="address" value="{{ old('address') }}" required
                                    class="block w-full pl-11 pr-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-all" placeholder="House #, Street, Area">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-100"></div>

                <!-- Vehicle Details -->
                <div>
                    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2 mb-4">
                        <span class="w-6 h-6 rounded-md bg-amber-100 text-amber-600 flex items-center justify-center text-xs">2</span>
                        Vehicle Details
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="vehicle_type" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Vehicle Type <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"><i class="fas fa-bicycle text-gray-400"></i></div>
                                <select name="vehicle_type" id="vehicle_type" required
                                    class="block w-full pl-11 pr-10 py-3 bg-gray-50/50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-all appearance-none">
                                    <option value="" disabled selected>Select vehicle...</option>
                                    <option value="Bike" {{ old('vehicle_type') == 'Bike' ? 'selected' : '' }}>Motorbike</option>
                                    <option value="Scooter" {{ old('vehicle_type') == 'Scooter' ? 'selected' : '' }}>Scooter</option>
                                    <option value="Cycle" {{ old('vehicle_type') == 'Cycle' ? 'selected' : '' }}>Bicycle</option>
                                    <option value="Van" {{ old('vehicle_type') == 'Van' ? 'selected' : '' }}>Delivery Van</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none"><i class="fas fa-chevron-down text-gray-400 text-xs"></i></div>
                            </div>
                        </div>

                        <div>
                            <label for="vehicle_number" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Vehicle Plate No <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"><i class="fas fa-hashtag text-gray-400"></i></div>
                                <input type="text" name="vehicle_number" id="vehicle_number" value="{{ old('vehicle_number') }}" required
                                    class="block w-full pl-11 pr-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-all" placeholder="KHI-2024">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-100"></div>

                <!-- Documents Upload -->
                <div>
                    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2 mb-4">
                        <span class="w-6 h-6 rounded-md bg-amber-100 text-amber-600 flex items-center justify-center text-xs">3</span>
                        Documents
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @php
                            $documents = [
                                ['id' => 'profile_image', 'label' => 'Profile Photo (Selfie)', 'icon' => 'fa-camera', 'required' => true],
                                ['id' => 'vehicle_image', 'label' => 'Vehicle Image (Condition)', 'icon' => 'fa-motorcycle', 'required' => true],
                                ['id' => 'cnic_front', 'label' => 'CNIC Front', 'icon' => 'fa-id-card', 'required' => true],
                                ['id' => 'cnic_back', 'label' => 'CNIC Back', 'icon' => 'fa-id-card', 'required' => true],
                                ['id' => 'license_image', 'label' => 'Driving License', 'icon' => 'fa-id-badge', 'required' => true],
                                ['id' => 'registration_book', 'label' => 'Registration Book', 'icon' => 'fa-book', 'required' => false],
                            ];
                        @endphp

                        @foreach($documents as $doc)
                            <div class="border border-gray-200 rounded-xl p-3 hover:border-amber-300 transition-colors bg-gray-50/30">
                                <label for="{{ $doc['id'] }}" class="block text-xs font-semibold text-gray-700 mb-2">
                                    <i class="fas {{ $doc['icon'] }} w-4 text-gray-400"></i> {{ $doc['label'] }}
                                    @if($doc['required'])<span class="text-red-400">*</span>@else<span class="text-gray-400 font-normal ml-1">(Optional)</span>@endif
                                </label>
                                <input type="file" name="{{ $doc['id'] }}" id="{{ $doc['id'] }}" accept="image/*" {{ $doc['required'] ? 'required' : '' }}
                                    class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-600 hover:file:bg-amber-100 cursor-pointer transition-colors">
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="border-t border-gray-100"></div>

                <!-- Security -->
                <div>
                    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2 mb-4">
                        <span class="w-6 h-6 rounded-md bg-amber-100 text-amber-600 flex items-center justify-center text-xs">4</span>
                        Security
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="password" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Password <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"><i class="fas fa-lock text-gray-400"></i></div>
                                <input type="password" name="password" id="password" required
                                    class="block w-full pl-11 pr-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-all" placeholder="••••••••">
                            </div>
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Confirm Password <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"><i class="fas fa-lock text-gray-400"></i></div>
                                <input type="password" name="password_confirmation" id="password_confirmation" required
                                    class="block w-full pl-11 pr-4 py-3 bg-gray-50/50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-all" placeholder="••••••••">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit"
                        class="w-full flex justify-center items-center gap-2 py-4 px-4 border border-transparent rounded-xl shadow-lg shadow-amber-500/30 text-sm font-bold text-white bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-all transform hover:-translate-y-0.5">
                        <i class="fas fa-user-check"></i>
                        <span>Submit Registration</span>
                    </button>
                </div>
            </form>

            <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                <p class="text-sm text-gray-600 font-medium">
                    Already have a rider account?
                    <a href="{{ route('rider.login') }}" class="font-bold text-amber-600 hover:text-amber-700 transition-colors ml-1">
                        Sign in here
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection