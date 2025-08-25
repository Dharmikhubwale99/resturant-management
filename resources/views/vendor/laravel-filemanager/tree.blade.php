@php
    $restaurant = auth()->user()?->restaurants()?->first();

    // DB values
    $quotaMb = (int) ($restaurant?->storage_quota_mb ?? 0); // MB
    $usedKb = (int) ($restaurant?->storage_used_kb ?? 0); // KB
    $perFileKb = (int) ($restaurant?->max_file_size_kb ?? 0); // KB

    // Unit helpers
    $usedMb = $usedKb / 1024; // KB → MB
    $usedGb = $usedKb / 1048576; // KB → GB
    $quotaGb = $quotaMb / 1024; // MB → GB
    $perFileMb = $perFileKb / 1024; // KB → MB

    // Progress %
    $pct = $quotaMb > 0 ? min(100, round(($usedMb / max($quotaMb, 1)) * 100)) : 0;

    // Nice display text
    $usedText = $usedGb >= 1 ? number_format($usedGb, 2) . ' GB' : number_format($usedMb, 0) . ' MB';
    $quotaText =
        $quotaMb > 0
            ? ($quotaGb >= 1
                ? number_format($quotaGb, 0) . ' GB'
                : number_format($quotaMb, 0) . ' MB')
            : 'Unlimited';

    // progress color thresholds
    $barClass = $pct >= 90 ? 'bg-danger' : ($pct >= 75 ? 'bg-warning' : 'bg-main'); // your theme class
@endphp

<div class="m-3 d-block d-lg-none">
    <h1 style="font-size: 1.5rem;">{{ $restaurant->name }} File Manager</h1>
    <small class="d-block">Ver 2.0</small>

    <div class="row mt-3">
        <div class="col-4">
            <img src="{{ asset('vendor/laravel-filemanager/img/152px color.png') }}" class="w-100">
        </div>

        <div class="col-8">
            <p class="mb-1">Current usage :</p>
            <p class="mb-1">{{ $usedText }} (Max: {{ $quotaText }})</p>

            @if ($perFileMb > 0)
                <p class="text-muted small mb-0">Max per file: {{ number_format($perFileMb, 2) }} MB</p>
            @endif
        </div>
    </div>

    @if ($quotaMb > 0)
        <div class="progress mt-3" style="height:.5rem;">
            <div class="progress-bar progress-bar-striped progress-bar-animated {{ $barClass }}" role="progressbar"
                style="width: {{ $pct }}%;" aria-valuenow="{{ $pct }}" aria-valuemin="0"
                aria-valuemax="100"></div>
        </div>
    @endif
</div>

<ul class="nav nav-pills flex-column">
    @foreach ($root_folders as $root_folder)
        <li class="nav-item">
            <a class="nav-link" href="#" data-type="0" data-path="{{ $root_folder->url }}">
                <i class="fa fa-folder fa-fw"></i> {{ $root_folder->name }}
            </a>
        </li>
        @foreach ($root_folder->children as $directory)
            <li class="nav-item sub-item">
                <a class="nav-link" href="#" data-type="0" data-path="{{ $directory->url }}">
                    <i class="fa fa-folder fa-fw"></i> {{ $directory->name }}
                </a>
            </li>
        @endforeach
    @endforeach
</ul>
