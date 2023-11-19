<x-app-layout>
    <div class="h-screen max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <a href="{{ route('attendance-list', ['date' => \Carbon\Carbon::parse($selectedDate)->subDay()->toDateString()]) }}"
                class="inline-block w-10  border border-blue-200 text-blue-500 text-xl mr-4">&lt;</a>
            <span class="text-2xl">{{ $selectedDate }}</span>
            <a href="{{ route('attendance-list', ['date' => \Carbon\Carbon::parse($selectedDate)->addDay()->toDateString()]) }}"
                class="inline-block w-10  border border-blue-200 text-blue-500 text-xl ml-4">&gt;</a>

            <table class="min-w-full bg-white text-lg py-4 border-green-950 mt-10 leading-loose">
                <thead>
                    <tr>
                        <th class="py-2 px-4 border-b">名前</th>
                        <th class="py-2 px-4 border-b">勤務開始</th>
                        <th class="py-2 px-4 border-b">勤務終了</th>
                        <th class="py-2 px-4 border-b">休憩時間</th>
                        <th class="py-2 px-4 border-b">勤務時間</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($attendances as $attendance)
                        <tr>
                            <td class="py-2 px-4 py-4 border-b">{{ $attendance->user->name }}</td>
                            <td class="py-2 px-4 py-4 border-b">
                                {{ \Carbon\Carbon::parse($attendance->start_time)->format('H:i:s') }}</td>
                            <td class="py-2 px-4 py-4 border-b">
                                {{ \Carbon\Carbon::parse($attendance->end_time)->format('H:i:s') }}</td>
                            <td class="py-2 px-4 py-4 border-b">{{ $attendance->calculateBreakDuration() }}</td>
                            <td class="py-2 px-4 bpy-4 border-b">{{ $attendance->calculateWorkDuration() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $attendances->links('vendor.pagination.tailwind2') }}
        </div>
    </div>
</x-app-layout>
