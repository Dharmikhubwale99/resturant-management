<div class="p-6 max-w-xl mx-auto bg-white rounded shadow">
    <h2 class="text-xl font-bold mb-4">Website Meta & Favicon Settings</h2>

    @if (session()->has('success'))
        <div class="bg-green-100 text-green-800 p-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit.prevent="submit" enctype="multipart/form-data">
        <x-form.input name="meta_title" label="Meta Title" type="text" wireModel="meta_title" required />

        <x-form.input name="meta_description" label="Meta Description" type="textarea" wireModel="meta_description" />

        <x-form.input name="meta_keywords" label="Meta Keywords" type="text" wireModel="meta_keywords" />

        <x-form.input name="favicon" label="Favicon Image" type="file" wireModel="favicon" />

        @if ($oldFavicon)
            <div class="mt-2">
                <img src="{{ asset('storage/' . $oldFavicon) }}" class="h-12" alt="Current Favicon">
            </div>
        @endif
        <div class="flex flex-row text-center  space-x-3 mt-3">
            <x-form.button type="submit" title="Save" wireTarget="submit" />
            <x-form.button title="Back" class="bg-gray-500 hover:bg-gray-600 text-white"
                route="superadmin.dashboard" />
        </div>
    </form>
</div>
