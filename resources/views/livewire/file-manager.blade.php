<div id="fileManagerRoot" x-data="{
    progress: 0,
    setProgress(p) { this.progress = p },
    openUpload: @entangle('showUploadModal'),
    openNewFolder: @entangle('showNewFolderModal'),
    openRenameFolder: @entangle('showRenameFolderModal'),
    openDeleteFolder: @entangle('showDeleteFolderModal'),
    openBulkDelete: @entangle('showBulkDeleteModal'),
    openDeleteFile: @entangle('showDeleteFileModal'),
}"
    x-on:livewire-upload-progress.window="setProgress($event.detail.progress)"
    x-on:livewire-upload-start.window="setProgress(0)" x-on:livewire-upload-finish.window="setProgress(100)"
    class="max-w-6xl mx-auto p-5"
    x-effect="(openUpload || openNewFolder || openRenameFolder || openDeleteFolder || openBulkDelete) && $dispatch('close-all-menus')">
    <style>
        :root {
            --modal-blur: 8px;
            --modal-tint: rgba(2, 6, 23, .45);
        }

        .card {
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .04)
        }

        .btn {
            padding: .6rem 1rem;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            background: #111827;
            color: #fff
        }

        .btn:disabled {
            opacity: .6
        }

        .btn-outline {
            background: #fff;
            color: #111827
        }

        .btn-sm {
            padding: .35rem .6rem;
            font-size: .85rem;
            border-radius: 8px
        }

        .usage {
            height: 10px;
            background: #f3f4f6;
            border-radius: 999px;
            overflow: hidden
        }

        .usage>span {
            display: block;
            height: 100%;
            background: #4f46e5;
            width: 0%
        }

        .drop {
            border: 2px dashed #cbd5e1;
            border-radius: 16px;
            padding: 26px;
            text-align: center;
            background: #f8fafc
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 12px
        }

        .tile {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: visible;
            background: #fff;
            cursor: pointer
        }

        .tile img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            display: block
        }

        .tile .meta {
            padding: 8px 10px;
            font-size: .85rem
        }

        .row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 10px 12px
        }

        .row-actions {
            position: relative;
            display: flex;
            align-items: center
        }

        [x-cloak] {
            display: none !important
        }

        .layout {
            display: grid;
            grid-template-columns: 1fr 360px;
            gap: 16px
        }

        .side {
            position: sticky;
            top: 16px;
            height: fit-content
        }

        @media (max-width:1024px) {
            .layout {
                grid-template-columns: 1fr
            }
        }

        .small-preview {
            width: 100%;
            aspect-ratio: 1.2/1;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center
        }

        .small-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            display: block
        }

        .kv {
            font-size: .85rem;
            color: #6b7280
        }

        .kv b {
            color: #111827
        }

        .input {
            width: 100%;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: .5rem .6rem;
            font-size: .9rem
        }

        .muted {
            font-size: .8rem;
            color: #6b7280
        }

        .crumbs {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            font-size: .85rem;
            color: #6b7280
        }

        .crumbs button {
            color: #2563eb;
        }

        .kebab {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 999px;
            border: 1px solid #e5e7eb;
            background: #fff;
            cursor: pointer;
            transition: .15s
        }

        .kebab:hover {
            background: #f9fafb
        }

        .menu {
            position: absolute;
            right: 0;
            top: 36px;
            min-width: 160px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            box-shadow: 0 12px 24px rgba(0, 0, 0, .08);
            padding: 6px;
            z-index: 1000
        }

        .menu button {
            width: 100%;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 10px;
            border-radius: 8px;
            font-size: .9rem
        }

        .menu button:hover {
            background: #f3f4f6
        }

        .menu .danger {
            color: #b91c1c
        }

        .backdrop {
            position: fixed;
            inset: 0;
            background: var(--modal-tint);
            backdrop-filter: blur(var(--modal-blur)) saturate(125%);
            -webkit-backdrop-filter: blur(var(--modal-blur)) saturate(125%);
            z-index: 50
        }

        .modal {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            z-index: 60
        }

        .modal-card {
            width: 100%;
            max-width: 560px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            box-shadow: 0 24px 60px rgba(2, 6, 23, .25)
        }

        .list {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden
        }

        .list .item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-bottom: 1px solid #f1f5f9
        }

        .list .item:last-child {
            border-bottom: 0
        }
    </style>

    <div class="layout">
        <div class="card p-5">
            <div class="flex items-end gap-4">
                @php $pct = $this->quotaMb>0 ? min(100, round(($this->usedMb/$this->quotaMb)*100,2)) : 0; @endphp
                <div class="flex-1">
                    <div class="flex justify-between text-sm text-gray-500 mb-1">
                        <span>Storage</span>
                        <span><span id="usage-text">{{ number_format($this->usedMb, 2) }}</span> MB /
                            {{ number_format($this->quotaMb, 2) }} MB</span>
                    </div>
                    <div class="usage"><span id="usage-bar" style="width: {{ $pct }}%"></span></div>
                </div>
                <button wire:click="recalcUsageFromDisk" class="btn">Refresh</button>
            </div>

            {{-- Breadcrumbs + actions --}}
            <div class="mt-4 flex items-center justify-between gap-3">
                <div class="crumbs">
                    <span>Path:</span><span>/</span>
                    <button type="button" wire:click="$set('subpath','')">{{ $restaurant->name }}</button>
                    @foreach ($this->breadcrumbs as $c)
                        <span>/</span>
                        <button type="button"
                            wire:click="$set('subpath', @js($c['sub']))">{{ $c['name'] }}</button>
                    @endforeach
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" class="btn btn-sm" x-on:click="openUpload = true">Upload</button>
                    <button type="button" class="btn btn-outline btn-sm" x-on:click="openNewFolder = true">New
                        Folder</button>
                    <button type="button" class="btn btn-outline btn-sm" style="color: red"
                        x-on:click="$wire.openBulkDelete(); openBulkDelete = true">Bulk Delete</button>
                </div>
            </div>

            {{-- Folders --}}
            @if ($this->folders->count())
                <h3 class="mt-5 mb-2 font-semibold">Folders</h3>
                <div class="grid">
                    @foreach ($this->folders as $folder)
                        @php $key = md5(($subpath ?: '') . '/' . $folder); @endphp
                        <div class="tile hover:shadow" wire:key="dir-{{ $key }}" x-data="{ myKey: '{{ $key }}' }"
                            @close-all-menus.window="Alpine.store('fm').open=null">
                            <div class="row">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span style="font-size:22px">📁</span>
                                    <button type="button" class="truncate text-left" style="flex:1 1 auto"
                                        x-on:click="$wire.openFolder(@js($folder))" title="Open">
                                        {{ $folder }}
                                    </button>
                                </div>
                                <div class="row-actions"
                                    @click.outside="Alpine.store('fm').open === myKey && (Alpine.store('fm').open=null)">
                                    <button type="button" class="kebab"
                                        :aria-expanded="Alpine.store('fm').open === myKey"
                                        @click.stop="Alpine.store('fm').open === myKey ? Alpine.store('fm').open=null : Alpine.store('fm').open=myKey">
                                        <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
                                            <circle cx="12" cy="5" r="1.8" />
                                            <circle cx="12" cy="12" r="1.8" />
                                            <circle cx="12" cy="19" r="1.8" />
                                        </svg>
                                    </button>
                                    <div x-cloak x-show="Alpine.store('fm').open===myKey" x-transition class="menu"
                                        role="menu">
                                        <button type="button"
                                            @click.stop="Alpine.store('fm').open=null; $wire.startRenameFolder(@js($folder)); openRenameFolder = true">Rename</button>
                                        <button type="button" class="danger"
                                            @click.stop="Alpine.store('fm').open=null; $wire.startDeleteFolder(@js($folder)); openDeleteFolder = true">Delete</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Files --}}
            @if ($this->existingFiles->count())
                <h3 class="mt-6 mb-2 font-semibold">Existing Files</h3>
                <div class="grid">
                    @foreach ($this->existingFiles as $f)
                        <button type="button"
                            class="tile hover:shadow"
                            wire:key="file-{{ $loop->index }}-{{ crc32($f['name']) }}"
                            x-data
                            data-name="{{ $f['name'] }}"
                            x-on:click="$wire.{{ $picker ? 'pickImage' : 'selectFile' }}($el.dataset.name)"
                            title="{{ $picker ? 'Use this image' : 'Click for details' }}">
                            <img src="{{ $f['url'] }}" alt="{{ $f['name'] }}">
                            <div class="meta truncate">{{ $f['name'] }}</div>
                        </button>
                    @endforeach
                </div>
            @endif

            @if ($status)
                <div class="text-sm text-green-700 mt-4">{{ $status }}</div>
            @endif
        </div>

        {{-- Right-side detail --}}
        @if ($selected)
            <div class="side card p-4">
                <div class="small-preview mb-3"><img src="{{ $selected['url'] }}" alt="{{ $selected['name'] }}"></div>

                <div class="mb-3">
                    <label class="muted block mb-1">File name (without extension)</label>
                    @php $ext = pathinfo($selected['name'], PATHINFO_EXTENSION); @endphp
                    <div class="flex items-center gap-2">
                        <input type="text" class="input" wire:model.defer="renameTo" placeholder="e.g. menu_dish_1">
                        <span class="muted">.{{ strtolower($ext) }}</span>
                    </div>
                    @error('renameTo')
                        <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                    @enderror

                    <div class="mt-2 flex gap-2 justify-end">
                        @if ($picker)
                            <span class="text-xs px-2 py-1 rounded bg-amber-100 text-amber-800">Picker mode: click an image to use it</span>
                            <a href="{{ $returnTo ?? url()->previous() }}" class="btn btn-outline btn-sm">Cancel</a>
                        @endif
                        <button class="btn btn-outline" style="border-color:#e5e7eb"
                            wire:click="clearSelected">Close</button>
                        <button class="btn" wire:click="saveRename" wire:loading.attr="disabled"
                            wire:target="saveRename">Save</button>
                        <button type="button" class="btn btn-outline" style="border-color:#e5e7eb; color:#b91c1c"
                            x-on:click="$wire.openDeleteFile(); openDeleteFile = true">
                            Delete
                        </button>
                    </div>
                </div>

                <div class="space-y-2">
                    <div class="kv"><b>Current name:</b> {{ $selected['name'] }}</div>
                    <div class="kv"><b>Size:</b> {{ number_format($selected['size_kb'] / 1024, 2) }} MB
                        ({{ number_format($selected['size_kb'], 2) }} KB)</div>
                    <div class="kv break-all"><b>Path (disk):</b> {{ $selected['disk_path'] }}</div>

                    <div class="kv break-all flex items-center gap-2" x-data="{
                        copied: false,
                        async copy(u) {
                            try { await navigator.clipboard.writeText(u);
                                this.copied = true; } catch (e) { const t = $refs.fallback;
                                t.value = u;
                                t.select();
                                document.execCommand('copy');
                                this.copied = true; }
                            setTimeout(() => this.copied = false, 1200);
                        }
                    }">
                        <b>URL:</b>
                        <a href="{{ $selected['url'] }}" target="_blank" class="underline text-blue-600">Open in new
                            tab</a>
                        <button type="button" class="btn btn-outline btn-sm"
                            x-on:click="copy(@js($selected['url']))" x-bind:disabled="copied">
                            <span x-show="!copied">Copy URL</span><span x-show="copied">Copied ✓</span>
                        </button>
                        <input x-ref="fallback" type="text" aria-hidden="true"
                            style="position:absolute; left:-9999px; top:-9999px;">
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- ======= MODALS ======= -->

    <!-- Upload -->
    <div x-cloak x-show="openUpload" x-transition.opacity class="backdrop" aria-hidden="true"
        x-on:keydown.escape.window="openUpload=false"></div>
    <div x-cloak x-show="openUpload" x-transition class="modal" role="dialog" aria-modal="true"
        aria-label="Upload images" x-on:keydown.escape.window="openUpload=false">
        <div class="modal-card">
            <div class="p-5 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold">Upload Images</h3>
                <button class="btn btn-outline" x-on:click="openUpload = false">Close</button>
            </div>
            <div class="p-5">
                <div class="flex items-center gap-3 mb-3">
                    <label class="flex items-center gap-2"><input type="checkbox" wire:model="multi"><span>Multi
                            upload</span></label>
                    <span class="text-xs text-gray-500">
                        @php
                            $maxKb = (int) ($this->restaurant->max_file_size_kb ?? 5120);
                            $maxKb = $maxKb > 0 ? $maxKb : 5120;
                            $maxMb = number_format($maxKb / 1024, 2);
                        @endphp
                        Allowed: JPG, PNG, GIF, WEBP, SVG (max {{ $maxMb }} MB each)
                    </span>
                </div>

                <input x-ref="pickerModal" hidden type="file" accept="image/*,image/svg+xml"
                    @if ($multi) multiple @endif wire:model="uploads">

                <div class="flex gap-2 mb-3">
                    <button type="button" class="btn"
                        x-on:click="$refs.pickerModal.showPicker?.() ?? $refs.pickerModal.click()">Choose
                        Files</button>
                    <button type="button" class="btn btn-outline" style="border-color:#e5e7eb"
                        wire:click="clearSelection">Clear</button>
                </div>

                <div class="drop cursor-pointer outline-none" role="button" tabindex="0"
                    x-on:click="$refs.pickerModal.showPicker?.() ?? $refs.pickerModal.click()"
                    x-on:keydown.enter.prevent="$refs.pickerModal.showPicker?.() ?? $refs.pickerModal.click()"
                    x-on:keydown.space.prevent="$refs.pickerModal.showPicker?.() ?? $refs.pickerModal.click()"
                    x-on:dragover.prevent
                    x-on:drop.prevent="
                        const dt = new DataTransfer();
                        [...$event.dataTransfer.files]
                          .filter(f => f.type.startsWith('image/') || f.type==='image/svg+xml')
                          .slice(0, {{ $multi ? 9999 : 1 }})
                          .forEach(f => dt.items.add(f));
                        $refs.pickerModal.files = dt.files;
                        $refs.pickerModal.dispatchEvent(new Event('change', { bubbles:true }));
                    ">
                    <strong>Click or drag & drop</strong> images here
                    <div class="text-xs text-gray-500 mt-1">Files exceeding the per-file limit will be removed
                        automatically.</div>
                </div>

                <div class="mt-4" x-show="progress > 0">
                    <div class="usage"><span :style="`width:${progress}%`"
                            style="display:block; height:10px; background:#10b981"></span></div>
                    <div class="text-xs text-gray-500 mt-1" x-text="`${progress}%`"></div>
                </div>

                @error('uploads')
                    <div class="text-red-600 text-sm mt-2">{{ $message }}</div>
                @enderror
                @error('uploads.*')
                    <div class="text-red-600 text-sm mt-2">{{ $message }}</div>
                @enderror

                @if (count($uploads))
                    <div class="grid mt-4">
                        @foreach ($uploads as $i => $file)
                            <div class="tile"><img src="{{ $file->temporaryUrl() }}" alt="preview">
                                <div class="meta">
                                    <div class="text-xs text-gray-600" title="{{ $file->getClientOriginalName() }}">
                                        {{ \Illuminate\Support\Str::limit($file->getClientOriginalName(), 30) }}</div>
                                    <div class="text-[11px] text-gray-400 mt-1">~
                                        {{ number_format($file->getSize() / (1024 * 1024), 2) }} MB</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="p-5 border-t border-gray-200 flex items-center justify-end gap-2">
                <button class="btn btn-outline" style="border-color:#e5e7eb"
                    x-on:click="openUpload = false">Cancel</button>
                <button type="button" class="btn" wire:click.prevent="storeUploads" wire:loading.attr="disabled"
                    wire:target="storeUploads,uploads">Submit</button>
            </div>
        </div>
    </div>

    <!-- New Folder -->
    <div x-cloak x-show="openNewFolder" x-transition.opacity class="backdrop" aria-hidden="true"
        x-on:keydown.escape.window="openNewFolder=false"></div>
    <div x-cloak x-show="openNewFolder" x-transition class="modal" role="dialog" aria-modal="true"
        aria-label="Create folder" x-trap.noscroll="openNewFolder">
        <div class="modal-card">
            <form x-on:submit.prevent="$wire.createFolder()">
                <div class="p-5 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Create new folder</h3>
                    <button type="button" class="btn btn-outline" x-on:click="openNewFolder = false">Close</button>
                </div>
                <div class="p-5">
                    <div class="muted mb-2">Current path: <b>/{{ $subpath ?: 'root' }}</b></div>
                    <label class="muted block mb-1">Folder name</label>
                    <input x-ref="nf" type="text" class="input" placeholder="Enter Folder Name"
                        wire:model.defer="newFolder"
                        x-effect="openNewFolder && setTimeout(()=> $refs.nf?.focus(), 0)">
                    @error('newFolder')
                        <div class="text-red-600 text-xs mt-2">{{ $message }}</div>
                    @enderror
                </div>
                <div class="p-5 border-t border-gray-200 flex items-center justify-end gap-2">
                    <button type="button" class="btn btn-outline" style="border-color:#e5e7eb"
                        x-on:click="openNewFolder = false">Cancel</button>
                    <button type="submit" class="btn" wire:loading.attr="disabled" wire:target="createFolder">
                        <span wire:loading.remove wire:target="createFolder">Create</span>
                        <span wire:loading wire:target="createFolder">Creating…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Rename folder (single) -->
    <div x-cloak x-show="openRenameFolder" x-transition.opacity class="backdrop" aria-hidden="true"
        x-on:keydown.escape.window="openRenameFolder=false"></div>
    <div x-cloak x-show="openRenameFolder" x-transition class="modal" role="dialog" aria-modal="true"
        aria-label="Rename folder" x-trap.noscroll="openRenameFolder">
        <div class="modal-card">
            <form x-on:submit.prevent="$wire.confirmRenameFolder()">
                <div class="p-5 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Rename folder</h3>
                    <button type="button" class="btn btn-outline"
                        x-on:click="openRenameFolder = false">Close</button>
                </div>
                <div class="p-5">
                    <div class="muted mb-2">Current: <b>{{ $folderOldName }}</b></div>
                    <label class="muted block mb-1">New name</label>
                    <input x-ref="rf" type="text" class="input" wire:model.defer="folderNewName"
                        x-effect="openRenameFolder && setTimeout(()=> $refs.rf?.focus(), 0)">
                    @error('folderNewName')
                        <div class="text-red-600 text-xs mt-2">{{ $message }}</div>
                    @enderror
                </div>
                <div class="p-5 border-t border-gray-200 flex items-center justify-end gap-2">
                    <button type="button" class="btn btn-outline btn-sm"
                        x-on:click="openRenameFolder=false">Cancel</button>
                    <button type="submit" class="btn btn-sm" wire:loading.attr="disabled"
                        wire:target="confirmRenameFolder">
                        <span wire:loading.remove wire:target="confirmRenameFolder">Save</span>
                        <span wire:loading wire:target="confirmRenameFolder">Renaming…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete folder (single) -->
    <div x-cloak x-show="openDeleteFolder" x-transition.opacity class="backdrop" aria-hidden="true"
        x-on:keydown.escape.window="openDeleteFolder=false"></div>
    <div x-cloak x-show="openDeleteFolder" x-transition class="modal" role="dialog" aria-modal="true"
        aria-label="Delete folder" x-trap.noscroll="openDeleteFolder">
        <div class="modal-card">
            <form x-on:submit.prevent="$wire.confirmDeleteFolder()">
                <div class="p-5 border-b border-gray-200">
                    <h3 class="text-lg font-semibold">Delete folder</h3>
                </div>
                <div class="p-5">
                    <p class="text-sm">Are you sure you want to delete <b>{{ $folderOldName }}</b>? This will remove
                        the folder and <u>all files and subfolders</u> inside it.</p>
                </div>
                <div class="p-5 border-t border-gray-200 flex items-center justify-end gap-2">
                    <button type="button" class="btn btn-outline btn-sm"
                        x-on:click="openDeleteFolder=false">Cancel</button>
                    <button type="submit" class="btn btn-sm" wire:loading.attr="disabled"
                        wire:target="confirmDeleteFolder">
                        <span wire:loading.remove wire:target="confirmDeleteFolder">Delete</span>
                        <span wire:loading wire:target="confirmDeleteFolder">Deleting…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- BULK DELETE (Folders + Images) -->
    <div x-cloak x-show="openBulkDelete" x-transition.opacity class="backdrop" aria-hidden="true"
        x-on:keydown.escape.window="openBulkDelete=false"></div>
    <div x-cloak x-show="openBulkDelete" x-transition class="modal" role="dialog" aria-modal="true"
        aria-label="Bulk delete" x-trap.noscroll="openBulkDelete">
        <div class="modal-card">
            <div class="p-5 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold">Delete items</h3>
                <button type="button" class="btn btn-outline" x-on:click="openBulkDelete=false">Close</button>
            </div>

            <div class="p-5 space-y-6">
                {{-- Folders --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-medium">Folders</h4>
                        <div class="flex items-center gap-2 text-sm">
                            <button type="button" class="btn btn-outline btn-sm"
                                x-on:click="$wire.set('bulkDeleteFolders', @js($this->folders))">Select
                                all</button>
                            <button type="button" class="btn btn-outline btn-sm"
                                x-on:click="$wire.set('bulkDeleteFolders', [])">Clear</button>
                        </div>
                    </div>
                    <div class="list">
                        @forelse ($this->folders as $folder)
                            <label class="item">
                                <input type="checkbox" class="mr-2" value="{{ $folder }}"
                                    wire:model.live="bulkDeleteFolders">
                                <span>📁 {{ $folder }}</span>
                            </label>
                        @empty
                            <div class="item text-gray-500 text-sm">No folders</div>
                        @endforelse
                    </div>
                </div>

                {{-- Files --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-medium">Images</h4>
                        <div class="flex items-center gap-2 text-sm">
                            <button type="button" class="btn btn-outline btn-sm"
                                x-on:click="$wire.set('bulkDeleteFiles', @js($this->existingFiles->pluck('name')->values()))">Select
                                all</button>
                            <button type="button" class="btn btn-outline btn-sm"
                                x-on:click="$wire.set('bulkDeleteFiles', [])">Clear</button>
                        </div>
                    </div>
                    <div class="list">
                        @forelse ($this->existingFiles as $f)
                            <label class="item">
                                <input type="checkbox" class="mr-2" value="{{ $f['name'] }}"
                                    wire:model.live="bulkDeleteFiles">
                                <img src="{{ $f['url'] }}" alt=""
                                    style="width:28px;height:28px;object-fit:cover;border-radius:6px;border:1px solid #e5e7eb">
                                <span class="truncate">{{ $f['name'] }}</span>
                            </label>
                        @empty
                            <div class="item text-gray-500 text-sm">No images</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="p-5 border-t border-gray-200 flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    Selected:
                    <b>{{ count($bulkDeleteFolders) }}</b> folder(s),
                    <b>{{ count($bulkDeleteFiles) }}</b> file(s)
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" class="btn btn-outline" style="border-color:#e5e7eb"
                        x-on:click="openBulkDelete=false">Cancel</button>
                    <button type="button" class="btn" wire:click="confirmBulkDelete"
                        x-bind:disabled="{{ count($bulkDeleteFolders) + count($bulkDeleteFiles) }} === 0"
                        wire:loading.attr="disabled" wire:target="confirmBulkDelete">
                        <span wire:loading.remove wire:target="confirmBulkDelete">Delete selected</span>
                        <span wire:loading wire:target="confirmBulkDelete">Deleting…</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 🆕 Delete single FILE modal -->
    <div x-cloak x-show="openDeleteFile" x-transition.opacity class="backdrop" aria-hidden="true"
        x-on:keydown.escape.window="openDeleteFile=false"></div>
    <div x-cloak x-show="openDeleteFile" x-transition class="modal" role="dialog" aria-modal="true"
        aria-label="Delete file" x-trap.noscroll="openDeleteFile">
        <div class="modal-card">
            <form x-on:submit.prevent="$wire.confirmDeleteFile()">
                <div class="p-5 border-b border-gray-200">
                    <h3 class="text-lg font-semibold">Delete image</h3>
                </div>
                <div class="p-5">
                    <p class="text-sm">
                        Are you sure you want to delete
                        <b>{{ $selected['name'] ?? '' }}</b> ?
                    </p>
                </div>
                <div class="p-5 border-t border-gray-200 flex items-center justify-end gap-2">
                    <button type="button" class="btn btn-outline btn-sm"
                        x-on:click="openDeleteFile=false">Cancel</button>
                    <button type="submit" class="btn btn-sm" wire:loading.attr="disabled"
                        wire:target="confirmDeleteFile">
                        <span wire:loading.remove wire:target="confirmDeleteFile">Delete</span>
                        <span wire:loading wire:target="confirmDeleteFile">Deleting…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('fm', {
                open: null
            });
        });

        document.addEventListener('livewire:initialized', () => {
            @this.on('uploaded', () => {
                const used = @this.get('usedMb');
                const quota = @this.get('quotaMb');
                const pct = quota > 0 ? Math.min(100, Math.round((used / quota) * 100)) : 0;
                document.getElementById('usage-bar').style.width = pct + '%';
                document.getElementById('usage-text').textContent = used.toFixed(2);
            });

            @this.on('folderActionDone', () => Alpine.store('fm').open = null);
        });
    </script>
</div>
