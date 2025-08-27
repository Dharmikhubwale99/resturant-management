<?php

namespace App\Livewire;

use App\Models\Restaurant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.resturant.app')]
class FileManager extends Component
{
    use WithFileUploads;

    public Restaurant $restaurant;

    // Upload state
    public $uploads = [];
    public bool $multi = true;
    public string $subpath = '';
    public float $usedMb = 0.0;
    public float $quotaMb = 0.0;
    public string $status = '';

    // Modals
    public bool $showUploadModal = false;
    public bool $showNewFolderModal = false;
    public bool $showRenameFolderModal = false;
    public bool $showDeleteFolderModal = false;
    public bool $showBulkDeleteModal = false;

    // Move modals
    public bool $showMoveFileModal = false;
    public bool $showBulkMoveModal = false;

    // Move state
    public string $moveTargetSubpath = '';
    public array $bulkMoveFolders = [];
    public array $bulkMoveFiles = [];
    public string $bulkMoveTargetSubpath = '';

    // Delete (single file)
    public bool $showDeleteFileModal = false;

    // Bulk delete state
    public array $bulkDeleteFolders = [];
    public array $bulkDeleteFiles = [];

    // File detail/rename
    public ?array $selected = null;
    public ?string $renaming = null;
    public string $renameTo = '';

    // Folder rename/delete state
    public string $newFolder = '';
    public string $folderOldName = '';
    public string $folderNewName = '';

    // Picker
    public bool $picker = false;
    public ?string $returnTo = null;

    // 🆕 Sort & View
    public string $sortMode = 'time';
    public string $viewMode = 'thumb';

    public bool $showDetailModal = false;

    public function mount(): void
    {
        $restaurantId = auth()->user()->restaurant_id ?? Restaurant::where('user_id', auth()->id())->value('id');
        abort_unless($restaurantId, 403, 'No restaurant assigned.');

        $this->restaurant = Restaurant::findOrFail($restaurantId);
        $this->usedMb = round(($this->restaurant->storage_used_kb ?? 0) / 1024, 2);
        $this->quotaMb = (float) ($this->restaurant->storage_quota_mb ?? 0);

        $this->picker = (bool) request('picker', false);
        $ret = request('return');
        $this->returnTo = $ret ? urldecode($ret) : url()->previous();
    }

    /** ROOT: restaurants/<restaurant-name>-<id> */
    private function folderRoot(): string
    {
        $name = trim((string) ($this->restaurant->name ?? ''));
        $name = $name === '' ? 'restaurant' : $name;
        $name = str_replace(['/', '\\'], '-', $name);
        $id = (int) $this->restaurant->id;
        return "restaurants/{$name}-{$id}";
    }

    private function currentPath(): string
    {
        $root = $this->folderRoot();
        return $this->subpath ? $root . '/' . trim($this->subpath, '/') : $root;
    }

    private function normalizeSub(string $sub): string
    {
        return trim($sub, '/');
    }

    private function perFileMaxKb(): int
    {
        $kb = (int) ($this->restaurant->max_file_size_kb ?? 5120);
        return $kb > 0 ? $kb : 5120;
    }

    /* ---------------- Upload flows ---------------- */

    private function filterOversize(array $files, int $maxKb): array
    {
        $kept = [];
        $removed = [];
        foreach ($files as $file) {
            if (!$file) {
                continue;
            }
            $sizeKb = (int) ceil(($file->getSize() ?? 0) / 1024);
            if ($sizeKb > $maxKb) {
                try {
                    $file->delete();
                } catch (\Throwable $e) {
                }
                $removed[] = basename($file->getClientOriginalName());
                continue;
            }
            $kept[] = $file;
        }
        if ($removed) {
            $this->addError('uploads', 'Removed (over ' . number_format($maxKb) . ' KB): ' . implode(', ', $removed));
        }
        return $kept;
    }

    public function updatedUploads(): void
    {
        $this->resetErrorBag('uploads'); // 👈 ખાતરીથી error દેખાશે

        $files = is_array($this->uploads) ? $this->uploads : [$this->uploads];
        if (!$this->multi && count($files) > 1) {
            $files = [$files[0]];
        }

        // Per-file limit filter (e.g. 5 MB)
        $files = $this->filterOversize($files, $this->perFileMaxKb());

        // Optional: quota-fit preview (only while choosing)
        [$keep, $skipped] = $this->capToRemainingQuota($files);
        $this->uploads = $keep;

        if (!empty($skipped)) {
            $this->addError('uploads', 'Skipped (quota full): ' . implode(', ', $skipped));
        }
    }

    private function remainingQuotaKb(): float
    {
        if ($this->quotaMb <= 0) {
            return INF;
        } // unlimited
        return max(0, $this->quotaMb * 1024 - (float) ($this->restaurant->storage_used_kb ?? 0));
    }

    /** Selection-stage capping: returns [keptFiles, skippedNames] */
    private function capToRemainingQuota(array $files): array
    {
        $remain = $this->remainingQuotaKb();
        if ($remain === INF) {
            return [$files, []];
        }

        $keep = [];
        $skipNames = [];
        foreach ($files as $file) {
            if (!$file) {
                continue;
            }
            $sizeKb = (float) (($file->getSize() ?? 0) / 1024);
            if ($sizeKb <= $remain) {
                $keep[] = $file;
                $remain -= $sizeKb;
            } else {
                $skipNames[] = basename($file->getClientOriginalName());
                try {
                    $file->delete();
                } catch (\Throwable $e) {
                }
            }
        }
        return [$keep, $skipNames];
    }

    public function clearSelection(): void
    {
        $files = is_array($this->uploads) ? $this->uploads : [$this->uploads];
        foreach ($files as $file) {
            if ($file) {
                try {
                    $file->delete();
                } catch (\Throwable $e) {
                }
            }
        }
        $this->reset('uploads');
        $this->resetErrorBag('uploads'); // 👈
    }

    public function storeUploads(): void
    {
        $this->resetErrorBag('uploads');

        $files = is_array($this->uploads) ? $this->uploads : [$this->uploads];
        $files = $this->filterOversize($files, $this->perFileMaxKb());

        if (empty($files) || (count($files) === 1 && $files[0] === null)) {
            $this->addError('uploads', 'Please choose at least one image.');
            return;
        }

        // MIME validation
        $this->validate(['uploads.*' => 'file|mimetypes:image/*,image/svg+xml']);

        $path = $this->currentPath();
        Storage::disk('public')->makeDirectory($path);

        $skippedQuota = []; // filenames skipped due to quota
        $skippedExists = []; // filenames skipped because already exists
        $saved = 0;

        foreach ($files as $file) {
            if (!$file) {
                continue;
            }

            $sizeKb = (float) (($file->getSize() ?? 0) / 1024);
            $remain = $this->remainingQuotaKb();

            // 👉 quota check: save only if it fits
            if ($remain !== INF && $sizeKb > $remain) {
                $skippedQuota[] = basename($file->getClientOriginalName());
                try {
                    $file->delete();
                } catch (\Throwable $e) {
                }
                continue;
            }

            $origName = basename($file->getClientOriginalName());
            if (Storage::disk('public')->exists($path . '/' . $origName)) {
                $skippedExists[] = $origName;
                try {
                    $file->delete();
                } catch (\Throwable $e) {
                }
                continue;
            }

            // Save
            $file->storeAs($path, $origName, 'public');
            try {
                $file->delete();
            } catch (\Throwable $e) {
            }

            // Update usage (immediately so next file sees reduced space)
            $this->restaurant->storage_used_kb = round(($this->restaurant->storage_used_kb ?? 0) + $sizeKb, 2);
            $this->restaurant->save();
            $saved++;
        }

        // Refresh meters
        $this->usedMb = round(($this->restaurant->storage_used_kb ?? 0) / 1024, 2);
        $this->status = $saved > 0 ? "Upload complete. Saved {$saved} file(s)." : 'No files saved.';

        // Show reasons for skips
        if ($skippedQuota) {
            $this->addError('uploads', 'Skipped (quota full): ' . implode(', ', $skippedQuota));
        }
        if ($skippedExists) {
            $this->addError('uploads', 'Skipped (already exists): ' . implode(', ', $skippedExists));
        }

        $this->reset('uploads');
        $this->showUploadModal = false;

        $this->dispatch('uploaded');
        $this->dispatch('$refresh');
    }

    /* ---------------- Folder features ---------------- */

    private function sanitizeFolder(string $name): string
    {
        $name = trim($name);
        $name = preg_replace('/[^\pL\pN _-]+/u', '_', $name);
        $name = preg_replace('/\s+/', ' ', $name);
        $name = preg_replace('/_+/', '_', $name);
        return trim($name, ' _-.');
    }

    /** Sorted folder names for grid & bulk modals */
    public function getFoldersProperty(): Collection
    {
        $path = $this->currentPath();
        $disk = Storage::disk('public');
        $disk->makeDirectory($path);

        $infos = collect($disk->directories($path))->map(function ($d) use ($path) {
            $name = basename($d);
            $full = rtrim($path, '/') . '/' . $name;
            return [
                'name' => $name,
                'mtime' => $this->dirLastModified($full),
            ];
        });

        if ($this->sortMode === 'alpha') {
            $infos = $infos->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE);
        } else {
            $infos = $infos->sortByDesc('mtime');
        }
        return $infos->pluck('name')->values();
    }

    /** Folder infos (name + mtime) for LIST view */
    public function getFolderInfosProperty(): Collection
    {
        $path = $this->currentPath();
        $disk = Storage::disk('public');
        $disk->makeDirectory($path);

        $infos = collect($disk->directories($path))->map(function ($d) use ($path) {
            $name = basename($d);
            $full = rtrim($path, '/') . '/' . $name;
            return [
                'name' => $name,
                'mtime' => $this->dirLastModified($full),
            ];
        });

        if ($this->sortMode === 'alpha') {
            return $infos->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values();
        }
        return $infos->sortByDesc('mtime')->values();
    }

    public function getBreadcrumbsProperty(): array
    {
        $crumbs = [];
        $acc = [];
        foreach (array_filter(explode('/', trim($this->subpath, '/'))) as $seg) {
            $acc[] = $seg;
            $crumbs[] = ['name' => $seg, 'sub' => implode('/', $acc)];
        }
        return $crumbs;
    }

    public function createFolder(): void
    {
        $this->validate(['newFolder' => 'required|string|min:1|max:100']);
        $name = $this->sanitizeFolder($this->newFolder);
        if ($name === '') {
            $this->addError('newFolder', 'Invalid folder name.');
            return;
        }

        $path = $this->currentPath() . '/' . $name;

        if (Storage::disk('public')->exists($path) || Storage::disk('public')->exists($path . '/.')) {
            $this->addError('newFolder', 'Folder already exists.');
            return;
        }

        Storage::disk('public')->makeDirectory($path);

        $this->newFolder = '';
        $this->showNewFolderModal = false;
        $this->status = "Folder created: {$name}";
        $this->dispatch('$refresh');
    }

    public function openFolder(string $name): void
    {
        $name = basename($name);
        $candidate = trim(($this->subpath ? $this->subpath . '/' : '') . $name, '/');
        $full = $this->folderRoot() . '/' . $candidate;

        if (!Storage::disk('public')->exists($full)) {
            $this->status = 'Folder not found.';
            return;
        }
        $this->subpath = $candidate;
        $this->clearSelected();
        $this->dispatch('$refresh');
    }

    public function up(): void
    {
        if (!$this->subpath) {
            return;
        }
        $parts = explode('/', trim($this->subpath, '/'));
        array_pop($parts);
        $this->subpath = implode('/', array_filter($parts));
        $this->clearSelected();
        $this->dispatch('$refresh');
    }

    /* --------- Folder rename & delete --------- */

    public function startRenameFolder(string $name): void
    {
        $this->folderOldName = basename($name);
        $this->folderNewName = $this->folderOldName;
        $this->resetErrorBag('folderNewName');
        $this->showRenameFolderModal = true;
    }

    public function startDeleteFolder(string $name): void
    {
        $this->folderOldName = basename($name);
        $this->showDeleteFolderModal = true;
    }

    public function confirmRenameFolder(): void
    {
        $this->validate(['folderNewName' => 'required|string|min:1|max:100']);
        $from = $this->sanitizeFolder($this->folderOldName);
        $to = $this->sanitizeFolder($this->folderNewName);

        if ($to === '') {
            $this->addError('folderNewName', 'Invalid folder name.');
            return;
        }
        if (strcasecmp($from, $to) === 0) {
            $this->status = 'No changes.';
            $this->showRenameFolderModal = false;
            return;
        }

        $old = $this->currentPath() . '/' . $from;
        $new = $this->currentPath() . '/' . $to;

        if (!Storage::disk('public')->exists($old)) {
            $this->addError('folderNewName', 'Original folder not found.');
            return;
        }
        if (Storage::disk('public')->exists($new)) {
            $this->addError('folderNewName', "A folder named {$to} already exists.");
            return;
        }

        $moved = false;
        try {
            $moved = Storage::disk('public')->move($old, $new);
        } catch (\Throwable $e) {
            $moved = false;
        }
        if (!$moved) {
            $moved = $this->safeMoveDirectory($old, $new);
        }
        if (!$moved) {
            $this->addError('folderNewName', 'Rename failed.');
            return;
        }

        $this->showRenameFolderModal = false;
        $this->status = "Folder renamed to {$to}.";
        $this->dispatch('folderActionDone');
        $this->dispatch('$refresh');
    }

    public function confirmDeleteFolder(): void
    {
        $name = $this->sanitizeFolder($this->folderOldName);
        $full = $this->currentPath() . '/' . $name;

        if (!Storage::disk('public')->exists($full)) {
            $this->status = 'Folder not found.';
            $this->showDeleteFolderModal = false;
            return;
        }

        $ok = false;
        try {
            $ok = Storage::disk('public')->deleteDirectory($full);
        } catch (\Throwable $e) {
            $ok = false;
        }

        $this->status = $ok ? "Folder deleted: {$name}" : 'Delete failed.';
        $this->showDeleteFolderModal = false;
        $this->dispatch('folderActionDone');
        $this->dispatch('$refresh');
        $this->recalcUsageFromDisk();
    }

    private function safeMoveDirectory(string $from, string $to): bool
    {
        $disk = Storage::disk('public');

        try {
            $disk->makeDirectory($to);
            foreach ($disk->allDirectories($from) as $dir) {
                $disk->makeDirectory(str_replace($from, $to, $dir));
            }
            foreach ($disk->allFiles($from) as $file) {
                $disk->copy($file, str_replace($from, $to, $file));
            }
            $disk->deleteDirectory($from);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /* --------- File detail + rename + delete --------- */

    private function sanitizeBasename(string $name): string
    {
        $name = trim($name);
        $name = preg_replace('/[^\pL\pN._-]+/u', '_', $name);
        $name = preg_replace('/_+/', '_', $name);
        return trim($name, '._- ');
    }

    public function selectFile(string $name): void
    {
        $name = basename($name);
        $path = $this->currentPath();
        $full = $path . '/' . $name;

        if (!Storage::disk('public')->exists($full)) {
            $this->status = 'File not found.';
            $this->selected = null;
            return;
        }

        $this->selected = [
            'name' => $name,
            'size_kb' => round(Storage::disk('public')->size($full) / 1024, 2),
            'url' => Storage::disk('public')->url($full),
            'disk_path' => $full,
        ];

        $this->renaming = $name;
        $this->renameTo = pathinfo($name, PATHINFO_FILENAME);
        $this->resetErrorBag('renameTo');
        $this->dispatch('fileSelected');
    }

    public function clearSelected(): void
    {
        $this->selected = null;
        $this->renaming = null;
        $this->renameTo = '';
        $this->showDetailModal = false;
        $this->resetErrorBag('renameTo');
    }

    public function saveRename(): void
    {
        if (!$this->selected || !$this->renaming) {
            return;
        }

        $this->validate(['renameTo' => 'required|string|min:1|max:120']);
        $base = $this->sanitizeBasename($this->renameTo);
        if ($base === '') {
            $this->addError('renameTo', 'Invalid name.');
            return;
        }

        $ext = pathinfo($this->renaming, PATHINFO_EXTENSION);
        $newName = $base . ($ext ? '.' . strtolower($ext) : '');

        if (strcasecmp($newName, $this->renaming) === 0) {
            $this->status = 'No changes.';
            return;
        }

        $path = $this->currentPath();
        $old = $path . '/' . $this->renaming;
        $new = $path . '/' . $newName;

        if (Storage::disk('public')->exists($new)) {
            $this->addError('renameTo', "A file named {$newName} already exists.");
            return;
        }
        if (!Storage::disk('public')->exists($old)) {
            $this->addError('renameTo', 'Original file not found.');
            return;
        }

        Storage::disk('public')->move($old, $new);

        $this->renaming = $newName;
        $this->selected['name'] = $newName;
        $this->selected['disk_path'] = $new;
        $this->selected['url'] = Storage::disk('public')->url($new);
        $this->status = "Renamed to {$newName}.";
        $this->dispatch('$refresh');
    }

    public function openDeleteFile(): void
    {
        if (!$this->selected) {
            $this->status = 'No file selected.';
            return;
        }
        $this->showDeleteFileModal = true;
    }

    public function confirmDeleteFile(): void
    {
        if (!$this->selected) {
            $this->showDeleteFileModal = false;
            return;
        }

        $path = $this->currentPath();
        $file = $path . '/' . basename($this->selected['name']);

        $ok = false;
        try {
            if (Storage::disk('public')->exists($file)) {
                $ok = Storage::disk('public')->delete($file);
            }
        } catch (\Throwable $e) {
            $ok = false;
        }

        $this->showDeleteFileModal = false;

        if ($ok) {
            $this->status = 'File deleted: ' . $this->selected['name'];
            $this->clearSelected();
            $this->dispatch('$refresh');
            $this->recalcUsageFromDisk();
        } else {
            $this->status = 'Delete failed or file not found.';
        }
    }

    /* --------- Move (single + bulk) --------- */

    public function openMoveFile(): void
    {
        if (!$this->selected) {
            $this->status = 'No file selected.';
            return;
        }
        $this->moveTargetSubpath = '';
        $this->resetErrorBag('moveTargetSubpath');
        $this->showMoveFileModal = true;
    }

    public function confirmMoveFile(): void
    {
        if (!$this->selected) {
            $this->showMoveFileModal = false;
            return;
        }

        $fromDir = $this->currentPath();
        $fileName = basename($this->selected['name']);
        $from = $fromDir . '/' . $fileName;

        $destSub = $this->normalizeSub($this->moveTargetSubpath);
        $destDir = $destSub ? $this->folderRoot() . '/' . $destSub : $this->folderRoot();

        if ($destDir === $fromDir) {
            $this->addError('moveTargetSubpath', 'Already in this folder.');
            return;
        }

        Storage::disk('public')->makeDirectory($destDir);

        $to = $destDir . '/' . $fileName;

        if (!Storage::disk('public')->exists($from)) {
            $this->status = 'Source file missing.';
            $this->showMoveFileModal = false;
            return;
        }
        if (Storage::disk('public')->exists($to)) {
            $this->addError('moveTargetSubpath', "A file named {$fileName} already exists in destination.");
            return;
        }

        $ok = false;
        try {
            $ok = Storage::disk('public')->move($from, $to);
        } catch (\Throwable $e) {
            $ok = false;
        }

        $this->showMoveFileModal = false;

        if ($ok) {
            $this->status = "Moved {$fileName}.";
            if ($destDir !== $fromDir) {
                $this->clearSelected();
            }
            $this->dispatch('$refresh');
        } else {
            $this->status = 'Move failed.';
        }
    }

    public function openBulkMove(): void
    {
        $this->reset('bulkMoveFolders', 'bulkMoveFiles');
        $this->bulkMoveTargetSubpath = '';
        $this->showBulkMoveModal = true;
    }

    public function confirmBulkMove(): void
    {
        $path = $this->currentPath();
        $disk = Storage::disk('public');

        $destSub = $this->normalizeSub($this->bulkMoveTargetSubpath);
        $destDir = $destSub ? $this->folderRoot() . '/' . $destSub : $this->folderRoot();

        if ($destDir === $path) {
            $this->addError('bulkMoveTargetSubpath', 'Destination is same as current.');
            return;
        }

        $disk->makeDirectory($destDir);

        $movedFolders = 0;
        $skippedFolders = 0;
        $movedFiles = 0;
        $skippedFiles = 0;

        foreach ($this->bulkMoveFolders as $name) {
            $name = $this->sanitizeFolder(basename($name));
            if ($name === '') {
                continue;
            }

            $src = $path . '/' . $name;
            $dst = $destDir . '/' . $name;

            $destInsideSrc = str_starts_with($destDir . '/', $src . '/');
            if ($destInsideSrc) {
                $skippedFolders++;
                continue;
            }

            if (!$disk->exists($src)) {
                $skippedFolders++;
                continue;
            }
            if ($disk->exists($dst)) {
                $skippedFolders++;
                continue;
            }

            $ok = false;
            try {
                $ok = $disk->move($src, $dst);
            } catch (\Throwable $e) {
                $ok = false;
            }
            if (!$ok) {
                $ok = $this->safeMoveDirectory($src, $dst);
            }

            $ok ? $movedFolders++ : $skippedFolders++;
        }

        foreach ($this->bulkMoveFiles as $name) {
            $base = basename($name);
            if ($base === '') {
                continue;
            }

            $src = $path . '/' . $base;
            $dst = $destDir . '/' . $base;

            if (!$disk->exists($src)) {
                $skippedFiles++;
                continue;
            }
            if ($disk->exists($dst)) {
                $skippedFiles++;
                continue;
            }

            $ok = false;
            try {
                $ok = $disk->move($src, $dst);
            } catch (\Throwable $e) {
                $ok = false;
            }
            $ok ? $movedFiles++ : $skippedFiles++;
        }

        $this->showBulkMoveModal = false;
        $this->reset('bulkMoveFolders', 'bulkMoveFiles');
        $this->status = "Moved {$movedFolders} folder(s) & {$movedFiles} file(s). Skipped {$skippedFolders} folder(s) & {$skippedFiles} file(s).";
        $this->dispatch('folderActionDone');
        $this->dispatch('$refresh');
    }

    /* ---------------- Utility ---------------- */

    private function dirLastModified(string $dir): int
    {
        $disk = Storage::disk('public');
        try {
            $files = $disk->allFiles($dir);
            if (empty($files)) {
                return 0;
            }
            $max = 0;
            foreach ($files as $f) {
                $t = $disk->lastModified($f);
                if ($t > $max) {
                    $max = $t;
                }
            }
            return $max;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function recalcUsageFromDisk(): void
    {
        $root = $this->folderRoot();
        $bytes = collect(Storage::disk('public')->allFiles($root))->sum(fn($f) => Storage::disk('public')->size($f));

        $this->restaurant->storage_used_kb = round($bytes / 1024, 2);
        $this->restaurant->save();

        $this->usedMb = round($this->restaurant->storage_used_kb / 1024, 2);
        $this->status = 'Usage refreshed.';
    }

    /** Files in current path (sorted + mtime) */
    public function getExistingFilesProperty(): Collection
    {
        $path = $this->currentPath();
        $disk = Storage::disk('public');
        $disk->makeDirectory($path);

        $files = collect($disk->files($path))->filter(fn($p) => preg_match('/\.(png|jpe?g|gif|webp|svg)$/i', $p))->map(
            fn($p) => [
                'name' => basename($p),
                'size_kb' => round($disk->size($p) / 1024, 2),
                'url' => $disk->url($p),
                'mtime' => $disk->lastModified($p),
            ],
        );

        if ($this->sortMode === 'alpha') {
            return $files->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values();
        }
        return $files->sortByDesc('mtime')->values();
    }

    public function render()
    {
        return view('livewire.file-manager');
    }

    /* --------- BULK DELETE --------- */

    public function openBulkDelete(): void
    {
        $this->reset('bulkDeleteFolders', 'bulkDeleteFiles');
        $this->showBulkDeleteModal = true;
    }

    public function confirmBulkDelete(): void
    {
        $path = $this->currentPath();
        $disk = Storage::disk('public');

        $deletedFolders = 0;
        $deletedFiles = 0;

        foreach ($this->bulkDeleteFolders as $name) {
            $name = $this->sanitizeFolder(basename($name));
            if ($name === '') {
                continue;
            }

            $full = $path . '/' . $name;
            try {
                if ($disk->exists($full) && $disk->deleteDirectory($full)) {
                    $deletedFolders++;
                }
            } catch (\Throwable $e) {
            }
        }

        foreach ($this->bulkDeleteFiles as $name) {
            $base = basename($name);
            if ($base === '') {
                continue;
            }

            $full = $path . '/' . $base;
            try {
                if ($disk->exists($full) && $disk->delete($full)) {
                    $deletedFiles++;
                }
            } catch (\Throwable $e) {
            }
        }

        $this->showBulkDeleteModal = false;
        $this->reset('bulkDeleteFolders', 'bulkDeleteFiles');
        $this->status = "Deleted {$deletedFolders} folder(s) & {$deletedFiles} file(s).";
        $this->dispatch('folderActionDone');
        $this->dispatch('$refresh');
        $this->recalcUsageFromDisk();
    }

    public function pickImage(string $name)
    {
        $base = basename($name);
        $full = $this->currentPath() . '/' . $base;

        if (!Storage::disk('public')->exists($full)) {
            $this->status = 'File not found.';
            return;
        }

        $url = Storage::disk('public')->url($full);
        $ret = $this->returnTo ?: url()->previous();
        $sep = str_contains($ret, '?') ? '&' : '?';
        return redirect()->to($ret . $sep . 'picked=' . urlencode($url));
    }

    /** All folders (root + recursive) relative to root ('' means root) */
    public function getAllFoldersProperty(): \Illuminate\Support\Collection
    {
        $root = $this->folderRoot();
        Storage::disk('public')->makeDirectory($root);

        $list = collect(Storage::disk('public')->allDirectories($root))
            ->map(fn($p) => trim(preg_replace('#^' . preg_quote($root, '#') . '/?#', '', $p), '/'))
            ->filter() // remove empty
            ->values();

        // add root as ''
        return collect([''])
            ->merge($list)
            ->values();
    }
}
