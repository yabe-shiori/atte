<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <!-- 会員登録 -->
        <h2 class="text-2xl text-center font-bold mb-10">{{ __('会員登録') }}</h2>

        <!-- Name -->
        <div>
            <x-text-input id="name" class="block mt-1 mb-6 w-full" type="text" name="name" :value="old('name')"
                required autofocus autocomplete="name" placeholder="{{ __('名前') }}" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-text-input id="email" class="block mt-1 mb-6 w-full" type="email" name="email" :value="old('email')"
                required autocomplete="username" placeholder="{{ __('メールアドレス') }}" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-text-input id="password" class="block mt-1 mb-6 w-full" type="password" name="password" required
                autocomplete="new-password" placeholder="{{ __('パスワード') }}" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-text-input id="password_confirmation" class="block mt-1 w-full mb-6" type="password"
                name="password_confirmation" required autocomplete="new-password" placeholder="{{ __('確認用パスワード') }}" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="block w-full">
            <x-primary-button class="block w-full">
                {{ __('会員登録') }}
            </x-primary-button>
            <div class="text-center mb-4">
                <a class="text-blue-500 hover:underline text-base hover:text-blue-700" href="{{ route('login') }}">
                    <p class="text-center text-zinc-400">アカウントをお持ちの方はこちらから</p>
                    {{ __('ログイン') }}
                </a>
            </div>
        </div>
    </form>
</x-guest-layout>
