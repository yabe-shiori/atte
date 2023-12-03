<x-app-layout>
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-2xl font-semibold mb-6">{{ $user->name }}さんの勤怠情報編集</h2>

                <form action="{{ route('update-attendance', ['user' => $user->id, 'attendance' => $attendance->id]) }}"
                    method="post" onsubmit="return showConfirmation()">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="start_time" class="block text-sm font-medium text-gray-700">勤務開始時間</label>
                        <input type="text" id="start_time" name="start_time"
                            value="{{ old('start_time', \Carbon\Carbon::parse($attendance->start_time)->format('H:i:s')) }}"
                            class="form-input w-full mt-1 py-2 px-3 border border-gray-300 rounded-md"
                            placeholder="HH:MM:SS">
                    </div>

                    <div class="mb-4">
                        <label for="end_time" class="block text-sm font-medium text-gray-700">勤務終了時間</label>
                        <input type="text" id="end_time" name="end_time"
                            value="{{ old('end_time', \Carbon\Carbon::parse($attendance->end_time)->format('H:i:s')) }}"
                            class="form-input w-full mt-1 py-2 px-3 border border-gray-300 rounded-md"
                            placeholder="HH:MM:SS">
                    </div>

                    <div class="mb-4">
                        <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded-md">更新する</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showConfirmation() {
            var startTime = document.getElementById('start_time').value;
            var endTime = document.getElementById('end_time').value;
            var message = 'この情報で保存してもよろしいですか？\n\n勤務開始時間: ' + startTime + '\n勤務終了時間: ' + endTime;

            return confirm(message);
        }
    </script>
</x-app-layout>
