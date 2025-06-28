<div class="p-4 bg-white shadow rounded mt-4 border">

        <h3 class="text-lg font-bold mb-4">Module Access for User #{{ $userId }}</h3>

        <form wire:submit.prevent="updateAccess">
            <div class="space-y-2">
                @foreach($modules as $module)
                    <div class="flex items-center space-x-3">
                        <input type="checkbox" wire:model="access.{{ $module->id }}">
                        <label>{{ $module->key }}</label>
                    </div>
                @endforeach
            </div>

            <button type="submit"
                class="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save Access</button>
        </form>

        @if (session()->has('success'))
            <div class="mt-2 text-green-600">{{ session('success') }}</div>
        @endif

</div>
