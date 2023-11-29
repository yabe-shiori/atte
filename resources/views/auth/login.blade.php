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

        <x-primary-button>
            {{ __('ログイン') }}
        </x-primary-button>

        <div class="text-center mb-4">
            <a class="text-blue-500 hover:underline text-base hover:text-blue-700" href="{{ route('register') }}">
                <p class="text-center text-zinc-400">アカウントをお持ちでない方はこちらから</p>
                {{ __('会員登録') }}
            </a>
        </div>
    </form>
</x-guest-layout>
