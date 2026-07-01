<?php

namespace App\Livewire\Resturant\Area;

use App\Models\Area;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    public $confirmingDelete = false;
    public $areaToDelete = null;
    public $search = '';
    public $confirmingBlock = false;
    public $areaId = null;


    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        $area = Area::where('restaurant_id', auth()->user()->restaurants()->first()->id)
        ->when($this->search, function($query) {
            $query->where('name', 'like', '%' . $this->search . '%');
        })->orderByDesc('id')->paginate(10);

        return view('livewire.resturant.area.index',[
            'areas' => $area
        ]);
    }

    public function mount()
    {
        if (!setting('area_module')) {
            abort(403, 'You do not have access to this module.');
        }
    }

    public function confirmDelete($id)
    {
        $this->areaToDelete = $id;
        $this->confirmingDelete = true;
    }

    public function cancelDelete()
    {
        $this->confirmingDelete = false;
        $this->areaToDelete = null;
    }

    public function deleteArea()
    {
        $area = Area::find($this->areaToDelete);
        if ($area) {
            $area->delete();
            session()->flash('success', 'Area deleted successfully.');
        } else {
            session()->flash('error', 'Area not found.');
        }

        $this->cancelDelete();
    }

    public function confirmBlock($id)
    {
        $this->areaId = $id;
        $this->confirmingBlock = true;
    }

    public function cancelBlock()
    {
        $this->areaId = null;
        $this->confirmingBlock = false;
    }

    public function toggleBlock()
    {
        $area = Area::findOrFail($this->areaId);
        $area->is_active = !$area->is_active;
        $area->save();

        $status = $area->is_active ? 'unblocked' : 'blocked';
        session()->flash('message', "Area {$status} successfully.");

        $this->cancelBlock();
    }
}
