<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class Settings extends Component
{
    use WithFileUploads;

    public $meta_title, $meta_description, $meta_keywords, $favicon, $oldFavicon;

    #[Layout('components.layouts.superadmin.app')]
    public function render()
    {
        return view('livewire.admin.settings');
    }

    public function mount()
    {
        $setting = DB::table('settings')->first();

        if ($setting) {
            $this->meta_title = $setting->meta_title;
            $this->meta_description = $setting->meta_description;
            $this->meta_keywords = $setting->meta_keywords;
            $this->oldFavicon = $setting->favicon;
        }
    }

    public function submit()
    {
        $this->validate([
            'meta_title' => 'required|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'favicon' => 'nullable|image|max:1024',
        ]);

        $faviconPath = $this->oldFavicon;

        if ($this->favicon) {
            $faviconPath = $this->favicon->store('icon', 'public');
        }

        DB::table('settings')->updateOrInsert(
            ['user_id' => auth()->id()],
            [
                'meta_title' => $this->meta_title,
                'meta_description' => $this->meta_description,
                'meta_keywords' => $this->meta_keywords,
                'favicon' => $faviconPath,
                'updated_at' => now(),
            ]
        );

        session()->flash('success', 'Settings updated successfully!');
    }

}
