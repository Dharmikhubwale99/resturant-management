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

        $this->access = RestaurantConfiguration::where('restaurant_id', $this->restaurantId)
        ->pluck('value', 'configuration_id')
        ->toArray();
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
    }


}
