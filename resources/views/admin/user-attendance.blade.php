<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="font-semibold text-lg mt-10 text-gray-800 leading-tight">
            {{ $user->name }}さんの勤怠情報
        </h2>
        <div class="my-6 w-full">

            @if (count($months) > 0)
                <div class="mb-4 w-full sm:w-1/2 md:w-1/3 lg:w-1/4 xl:w-1/4">
                    <form action="{{ route('user-attendance', $user->id) }}" method="get">
                        @csrf
                        <label for="selectedMonth" class="block text-xs font-normal text-gray-700">月を選択</label>
                        <select id="selectedMonth" name="selectedMonth"
                            class="w-full mt-1 p-2 border border-gray-300 rounded-md" onchange="this.form.submit()">
                            @foreach ($months as $month)
                                <option value="{{ $month }}" {{ $month === $selectedMonth ? 'selected' : '' }}>
                                    {{ Carbon\Carbon::parse($month)->format('Y年m月') }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            @endif
        </div>

        @if ($attendancesByMonth->has($selectedMonth))
            <h3 class="text-xl font-semibold mt-4 mb-2">{{ Carbon\Carbon::parse($selectedMonth)->format('Y年m月') }}</h3>
            <table class="text-left w-full border-collapse mt-2">
                <thead>
                    <tr class="bg-blue-500 text-white">
                        <th class="p-3 text-center">日</th>
                        <th class="p-3 text-center">勤務開始</th>
                        <th class="p-3 text-center">勤務終了</th>
                        <th class="p-3 text-center">休憩時間</th>
                        <th class="p-3 text-center">勤務時間</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($attendancesByMonth[$selectedMonth] as $attendance)
                        <tr>
                            <td class="border-gray-light border p-3">{{ $attendance->work_date }}</td>
                            <td class="border-gray-light border p-3">
                                {{ \Carbon\Carbon::parse($attendance->start_time)->format('H:i:s') }}
                            </td>
                            <td class="border-gray-light border p-3">
                                {{ \Carbon\Carbon::parse($attendance->end_time)->format('H:i:s') }}
                            </td>
                            <td class="border-gray-light border p-3">
                                {{ $attendance->calculateBreakDuration() }}
                            </td>
                            <td class="border-gray-light border p-3">
                                {{ $attendance->calculateWorkTime() }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="font-bold text-xl mt-12">選択された月のデータはありません。</p>
        @endif
    </div>
</x-app-layout>
