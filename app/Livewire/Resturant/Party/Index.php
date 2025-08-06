<?php

namespace App\Livewire\Resturant\Party;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\{Restaurant, Payment, Customer};

class Index extends Component
{
    public $parties;
    public $confirmingBlock = false;
    public $partyId = null;
    public $confirmingDelete = false;
    public $partyToDelete = null;

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        $restaurantId = auth()->user()->restaurant_id ?: Restaurant::where('user_id', auth()->id())->value('id');

        $this->parties = Customer::where('restaurant_id', $restaurantId)
            ->latest()
            ->get();


        return view('livewire.resturant.party.index');
    }

    public function mount()
    {
        if (!setting('party')) {
            abort(403, 'You do not have access to this module.');
        }
    }

    public function confirmBlock($id)
    {
        $this->partyId = $id;
        $this->confirmingBlock = true;
    }

    public function cancelBlock()
    {
        $this->partyId = null;
        $this->confirmingBlock = false;
    }

    public function toggleBlock()
    {
        $party = Customer::findOrFail($this->partyId);
        $party->is_active = !$party->is_active;
        $party->save();

        $status = $party->is_active ? 'unblocked' : 'blocked';
        session()->flash('message', "Party {$status} successfully.");

        $this->cancelBlock();
    }

    public function confirmDelete($id)
    {
        $this->partyToDelete = $id;
        $this->confirmingDelete = true;
    }

    public function cancelDelete()
    {
        $this->confirmingDelete = false;
        $this->partyToDelete = null;
    }

    public function deleteParty()
    {
        $party = Customer::find($this->partyToDelete);
        if ($party) {
            $party->delete();
            session()->flash('success', 'Customer deleted successfully.');
        } else {
            session()->flash('error', 'Customer not found.');
        }
        $this->cancelDelete();
    }

}
