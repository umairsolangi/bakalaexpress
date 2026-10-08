@extends('layouts.app')

@section('title', 'Rider Login - Bakala Express')

@section('content')
<div class="min-h-screen flex items-center justify-center relative overflow-hidden bg-stone-100 py-12 px-4 sm:px-6 lg:px-8">
    <!-- Background Elements -->
    <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-amber-400/20 rounded-full blur-[100px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-96 h-96 bg-orange-500/20 rounded-full blur-[100px] pointer-events-none"></div>

    <div class="max-w-md w-full relative z-10">
        <div class="text-center mb-10">
            <div class="mx-auto h-20 w-20 bg-gradient-to-br from-amber-400 to-orange-500 rounded-2xl flex items-center justify-center text-white mb-6 shadow-xl shadow-amber-500/30 transform hover:scale-105 transition-transform">
                <i class="fas fa-motorcycle text-4xl"></i>
            </div>
            <h2 class="text-3xl font-extrabold text-gray-900 font-display tracking-tight">
                Welcome Back, Rider
            </h2>
            <p class="mt-3 text-sm text-gray-500 font-medium">
                Log in to hit the road and start earning
            </p>
        </div>

        <div class="bg-white/80 backdrop-blur-xl py-8 px-4 shadow-2xl rounded-3xl sm:px-10 border border-white/50 relative overflow-hidden">
            <div class="absolute top-0 inset-x-0 h-1.5 bg-gradient-to-r from-amber-400 to-orange-500"></div>

            <form class="space-y-6" action="{{ route('rider.login') }}" method="POST">
                @csrf

                @if ($errors->any())
                    <div class="rounded-2xl bg-red-50 p-4 border border-red-100 mb-6 flex items-start gap-3">
                        <i class="fas fa-exclamation-circle text-red-500 mt-0.5"></i>
                        <div>
                            <h3 class="text-sm font-bold text-red-800">Login Failed</h3>
                            <ul class="mt-1 text-xs text-red-600 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <div class="space-y-5">
                    <div>
                        <label for="email" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Email Address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                            <input type="email" name="email" id="email" required
                                class="block w-full pl-11 pr-4 py-3.5 bg-gray-50/50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 focus:bg-white outline-none transition-all"
                                placeholder="rider@bakala.com">
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="password" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide">Password</label>
                            <a href="#" class="text-xs font-semibold text-amber-600 hover:text-amber-700 transition-colors">Forgot password?</a>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input type="password" name="password" id="password" required
                                class="block w-full pl-11 pr-4 py-3.5 bg-gray-50/50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 focus:bg-white outline-none transition-all"
                                placeholder="••••••••">
                        </div>
                    </div>
                </div>

                <div class="flex items-center mt-6">
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <div class="relative flex items-center justify-center">
                            <input type="checkbox" name="remember" id="remember" class="peer sr-only">
                            <div class="w-5 h-5 border-2 border-gray-300 rounded-md peer-checked:bg-amber-500 peer-checked:border-amber-500 transition-colors"></div>
                            <i class="fas fa-check absolute text-white text-[10px] opacity-0 peer-checked:opacity-100 transition-opacity"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-600 group-hover:text-gray-900 transition-colors">Remember me for 30 days</span>
                    </label>
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="w-full flex justify-center items-center gap-2 py-3.5 px-4 border border-transparent rounded-xl shadow-lg shadow-amber-500/30 text-sm font-bold text-white bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-all transform hover:-translate-y-0.5">
                        <span>Sign In</span>
                        <i class="fas fa-arrow-right text-xs"></i>
                    </button>
                </div>
            </form>

            <div class="mt-8">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-4 bg-white text-xs font-semibold text-gray-400 uppercase tracking-wide">
                            New to Bakala Express?
                        </span>
                    </div>
                </div>

                <div class="mt-8">
                    <a href="{{ route('rider.register') }}"
                        class="w-full flex justify-center items-center gap-2 py-3.5 px-4 border-2 border-gray-200 rounded-xl text-sm font-bold text-gray-700 bg-transparent hover:bg-gray-50 hover:border-gray-300 transition-all">
                        <i class="fas fa-user-plus text-gray-400"></i>
                        Create Rider Account
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection