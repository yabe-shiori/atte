<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl mt-10 text-gray-800 leading-tight">
            {{ $user->name }}の勤怠情報
        </h2>
        <x-message :message="session('message')"/>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="my-6 w-full">

            <div class="mb-4 w-1/2">
                <label for="selectedMonth" class="block text-sm font-medium text-gray-700">月を選択</label>
                <select id="selectedMonth" name="selectedMonth" class="w-1/4 mt-1 p-2 border border-gray-300 rounded-md">
                    @foreach($months as $month)
                        <option value="{{ $month }}" {{ $month === $selectedMonth ? 'selected' : '' }}>
                            {{ Carbon\Carbon::parse($month)->format('Y年m月') }}
                        </option>
                    @endforeach
                </select>
            </div>

            @foreach($attendancesByMonth as $month => $monthAttendances)
                <h3 class="text-2xl font-semibold mt-4 mb-2">{{ Carbon\Carbon::parse($month)->format('Y年m月') }}</h3>
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
                        @foreach($monthAttendances as $attendance)
                            <tr>
                                <td class="border-gray-light border p-3">{{ $attendance->work_date }}</td>
                                <td class="border-gray-light border p-3">
                                    {{ \Carbon\Carbon::parse($attendance->start_time)->format('H:i:s') }}
                                </td>
                                <td class="border-gray-light border p-3">
                                    {{ \Carbon\Carbon::parse($attendance->end_time)->format('H:i:s') }}
                                </td>
                                <td class="border-gray-light border p-3">
                                    {{ $attendance->calculateBreakDuration()  }}
                                </td>
                                <td class="border-gray-light border p-3">
                                    {{ $attendance->calculateWorkDuration() }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        </div>
    </div>
</x-app-layout>
