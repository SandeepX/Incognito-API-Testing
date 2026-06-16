<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reset Password - Incognito Testing</title>
    @include('apitester.partials.css')
    <style>
        .auth-card {
            background: linear-gradient(135deg, rgba(30, 30, 40, 0.95), rgba(20, 20, 30, 0.98));
            border: 1px solid rgba(255, 255, 255, 0.06);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .auth-input {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: #e2e8f0;
            transition: all 0.2s ease;
        }
        .auth-input:focus {
            background: rgba(255, 255, 255, 0.06);
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
            outline: none;
        }
        .auth-input::placeholder {
            color: rgba(255, 255, 255, 0.25);
        }
        .auth-btn {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            transition: all 0.2s ease;
        }
        .auth-btn:hover {
            background: linear-gradient(135deg, #60a5fa, #3b82f6);
            transform: translateY(-1px);
            box-shadow: 0 8px 25px -5px rgba(59, 130, 246, 0.4);
        }
        .auth-btn:active {
            transform: translateY(0);
        }
        .brand-glow {
            filter: drop-shadow(0 0 20px rgba(59, 130, 246, 0.15));
        }
    </style>
</head>
<body class="bg-surface-900 text-surface-100 min-h-screen flex items-center justify-center p-4 selection:bg-blue-500/30">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="flex items-center justify-center gap-2 mb-2 brand-glow">
                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <span class="text-xl font-bold text-surface-100">Incognito Testing</span>
            </div>
            <p class="text-sm text-surface-400">Reset your password</p>
        </div>

        <!-- Card -->
        <div class="auth-card rounded-2xl p-8">
            @if ($errors->any())
                <div class="mb-6 p-3 bg-red-500/10 border border-red-500/20 rounded-lg">
                    @foreach ($errors->all() as $error)
                        <p class="text-xs text-red-400">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label for="email" class="block text-xs font-medium text-surface-300 mb-1.5">Email address</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required autofocus readonly
                           class="auth-input w-full rounded-lg px-4 py-2.5 text-sm opacity-60 cursor-not-allowed">
                </div>

                <div>
                    <label for="password" class="block text-xs font-medium text-surface-300 mb-1.5">New password</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password"
                           class="auth-input w-full rounded-lg px-4 py-2.5 text-sm" placeholder="••••••••">
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-medium text-surface-300 mb-1.5">Confirm new password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                           class="auth-input w-full rounded-lg px-4 py-2.5 text-sm" placeholder="••••••••">
                </div>

                <button type="submit" class="auth-btn w-full text-white font-semibold text-sm py-2.5 rounded-lg transition-all">
                    Reset Password
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-surface-700/50 text-center">
                <p class="text-xs text-surface-400">
                    <a href="{{ route('login') }}" class="text-blue-400 hover:text-blue-300 font-medium transition">Back to sign in</a>
                </p>
            </div>
        </div>

        <p class="text-center mt-6 text-xs text-surface-500">
            &copy; {{ date('Y') }} Incognito Testing. All rights reserved.
        </p>
    </div>
</body>
</html>
