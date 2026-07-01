<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use App\Models\AppConfiguration;
use App\Models\RestaurantConfiguration;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;

class UserAccess extends Component
{
    public $userId;
    public $restaurantId;
    public $access = [];
    public $selectAll = false;
    protected $listeners = ['openAccess' => 'loadAccess'];

    #[Layout('components.layouts.admin.app')]
    public function render()
    {
        $access = AppConfiguration::get();

        return view('livewire.admin.user-access', [
            'modules' => $access,
        ]);
    }

    public function mount($id)
    {
        $this->userId = $id;
        $user = User::findOrFail($id);
        $this->restaurantId = $user->restaurants()->first()->id;

        $existing = RestaurantConfiguration::where('restaurant_id', $this->restaurantId)
        ->pluck('value', 'configuration_id')
        ->map(fn ($v) => (bool) $v)
        ->toArray();

        $allIds = AppConfiguration::pluck('id')->all();
        $this->access = [];
        foreach ($allIds as $mid) {
            $this->access[$mid] = $existing[$mid] ?? false;
        }

        $this->selectAll = !in_array(false, $this->access, true);
    }

    public function updateAccess()
    {
        $this->validate([
            'access'   => 'array',
            'access.*' => 'boolean',
        ]);

        $moduleIds = AppConfiguration::pluck('id');

        DB::transaction(function () use ($moduleIds) {

            foreach ($moduleIds as $moduleId) {

                $value = !empty($this->access[$moduleId]) ? 1 : 0;

                RestaurantConfiguration::updateOrCreate(
                    [
                        'restaurant_id'    => $this->restaurantId,
                        'configuration_id' => $moduleId,
                    ],
                    [ 'value' => $value ]
                );
            }
        });

        session()->flash('success', 'Access updated successfully.');
        return redirect()->route('superadmin.admin.index');
    }

    public function toggleSelectAll()
    {
        // apply current selectAll value to all modules
        $allIds = AppConfiguration::pluck('id')->all();
        foreach ($allIds as $mid) {
            $this->access[$mid] = (bool) $this->selectAll;
        }
    }

    public function updated($name, $value)
{
    if (str_starts_with($name, 'access.')) {
        $total   = AppConfiguration::count();
        $checked = collect($this->access)->filter()->count();
        $this->selectAll = ($checked === $total);
    }
}
}
