<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <h2 class="text-2xl text-center font-bold mb-10">{{ __('ログイン') }}</h2>

        <!-- Email Address -->
        <div>
            <x-text-input id="email" class="block mt-1 w-full mb-8" type="email" name="email" :value="old('email')"
                required autofocus autocomplete="username" placeholder="{{ __('メールアドレス') }}" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-text-input id="password" class="block mt-1 w-full mb-8" type="password" name="password" required
                autocomplete="current-password" placeholder="{{ __('パスワード') }}" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        {{-- <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif --}}

        <x-primary-button>
            {{ __('ログイン') }}
        </x-primary-button>
        </div>
        <div class="text-center mb-4">
            <a class="text-blue-500 hover:underline text-base hover:text-blue-700" href="{{ route('register') }}">
                <p class="text-center text-gray-400">アカウントをお持ちでない方はこちらから</p>
                {{ __('会員登録') }}
            </a>
        </div>
    </form>
</x-guest-layout>
