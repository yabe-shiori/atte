<x-app-layout>
    <div class="h-screen max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="text-center h-3/4">
            <x-message :message="session('message')" />

            @if (session('error'))
                <div class="border mb-4 px-4 py-3 rounded relative bg-red-100 border-red-400 text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            @unless (session('message') || session('error'))
                <div class="mb-16"></div>
            @endunless
            <div class="flex flex-wrap justify-center">
                <div class="w-full md:w-1/2">
                    <form method="post" action="{{ route('start-work') }}">
                        @csrf
                        <button type="submit"
                            class="mx-2 mb-8 bg-white text-xl font-semibold h-56 w-full md:w-11/12 md:mx-auto
                            @if (!Auth::check() || (Auth::check() && !Auth::user()->work_started)) text-black hover:opacity-60
                            @else text-zinc-200 @endif">
                            勤務開始
                        </button>
                    </form>
                </div>

                <div class="w-full md:w-1/2">
                    <form method="post" action="{{ route('end-work') }}">
                        @csrf
                        <button type="submit"
                            class="mx-2 mb-8 bg-white text-xl font-semibold h-56 w-full md:w-11/12 md:mx-auto
                            @if (!Auth::check() || (Auth::check() && !Auth::user()->work_started)) text-zinc-200
                            @else text-zinc-black hover:opacity-60 @endif">
                            勤務終了
                        </button>
                    </form>
                </div>

                <div class="w-full md:w-1/2">
                    <form method="post" action="{{ route('start-break') }}">
                        @csrf
                        <button type="submit"
                            class="mx-2 mb-4 bg-white
                            @if (!Auth::check() || (Auth::check() && !Auth::user()->break_started)) text-black hover:opacity-60
                            @else text-zinc-200  @endif
                            text-xl font-semibold h-56 w-full md:w-11/12 md:mx-auto">
                            休憩開始
                        </button>
                    </form>
                </div>

                <div class="w-full md:w-1/2">
                    <form method="post" action="{{ route('end-break') }}">
                        @csrf
                        <button type="submit"
                            class="mx-2 mb-4 bg-white
                            @if (!Auth::check() || (Auth::check() && !Auth::user()->break_started)) text-zinc-200
                            @else text-black hover:opacity-60 @endif
                            text-xl font-semibold h-56 w-full md:w-11/12 md:mx-auto">
                            休憩終了
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
