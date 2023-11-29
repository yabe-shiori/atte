<x-app-layout>
    <div class="h-screen w-full mx-auto mt-4 py-6 px-4 sm:px-6 lg:px-8 bg-neutral-100">
        <div class="text-center">
            <a href="{{ route('attendance-list', ['date' => \Carbon\Carbon::parse($selectedDate)->subDay()->toDateString()]) }}"
                class="inline-block w-10  border border-blue-400 text-blue-600 text-xl mr-4">&lt;</a>
            <span class="text-2xl">{{ $selectedDate }}</span>
            <a href="{{ route('attendance-list', ['date' => \Carbon\Carbon::parse($selectedDate)->addDay()->toDateString()]) }}"
                class="inline-block w-10  border border-blue-400 text-blue-600 text-xl ml-4">&gt;</a>
            <table class="min-w-full text-lg py-4 mt-10 leading-loose">
                <thead>
                    <tr>
                        <th class="py-3 px-4 border-y-2 border-y-gray-300">名前</th>
                        <th class="py-3 px-4 border-y-2 border-y-gray-300">勤務開始</th>
                        <th class="py-3 px-4 border-y-2 border-y-gray-300">勤務終了</th>
                        <th class="py-3 px-4 border-y-2 border-y-gray-300">休憩時間</th>
                        <th class="py-3 px-4 border-y-2 border-y-gray-300">勤務時間</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($attendances as $attendance)
                        <tr>
                            <td class="px-4 py-3 border-t-2 border-y-gray-300">{{ $attendance->user->name }}</td>
                            <td class="px-4 py-3 border-t-2 border-y-gray-300">
                                {{ \Carbon\Carbon::parse($attendance->start_time)->format('H:i:s') }}</td>
                            <td class="px-4 py-3 border-t-2 border-y-gray-300">
                                {{ \Carbon\Carbon::parse($attendance->end_time)->format('H:i:s') }}</td>
                            <td class="px-4 py-3 border-t-2 border-y-gray-300">
                                {{ $attendance->calculateBreakDuration() }}</td>
                            <td class="px-4 py-3 border-t-2 border-y-gray-300">{{ $attendance->calculateWorkTime() }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $attendances->appends(['date' => $selectedDate])->links('vendor.pagination.tailwind2') }}
    </div>
</x-app-layout>
