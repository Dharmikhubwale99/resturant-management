  @if (session()->has('success'))
      <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
          class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
          <strong class="font-bold">Success!</strong>
          <span class="block sm:inline">{{ session('success') }}</span>
      </div>
  @endif
  @if (session()->has('error'))
      <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
          class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
          <strong class="font-bold">Error!</strong>
          <span class="block sm:inline">{{ session('error') }}</span>
      </div>
  @endif

  @if ($errors->any())
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
          <ul class="list-disc pl-5">
              @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
              @endforeach
          </ul>
      </div>
  @endif
