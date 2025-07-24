<?php

namespace App\Livewire\Resturant\User;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

class Index extends Component
{
    use WithPagination;
    public $confirmingDelete = false;
    public $userToDelete = null;
    public $search = '';   

    public string $role = 'all';  
     protected $queryString = [
        'search' => ['except' => ''],
        'role'   => ['except' => ''], 
        'page'   => ['except' => 1],
    ];


    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        $user = User::where('restaurant_id', auth()->user()->restaurants()->first()->id)
        ->when($this->search, function($query) {
            $query->where('name', 'like', '%' . $this->search . '%')
            ->orWhere('email', 'like', '%' . $this->search . '%')
            ->orWhere('mobile', 'like', '%' . $this->search . '%');
        }) 
          ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['manager', 'waiter', 'kitchen'])
                  ->when($this->role !== 'all', fn ($sub) =>
                      $sub->where('name', $this->role)
                  );
            })

        ->orderByDesc('id')->paginate(10);

        return view('livewire.resturant.user.index', [
            'users' => $user
        ]);
    }

    
    public function updatingSearch() 
    { 
        $this->resetPage(); 
    }
    
    public function updatingRole()   
    { 
        $this->resetPage(); 
    }

    public function confirmDelete($id)
    {
        $this->userToDelete = $id;
        $this->confirmingDelete = true;
    }

    public function cancelDelete()
    {
        $this->confirmingDelete = false;
        $this->userToDelete = null;
    }

    public function deleteUser()
    {
        $user = User::find($this->userToDelete);
        if ($user) {
            $user->delete();
            session()->flash('success', 'User deleted successfully.');
        } else {
            session()->flash('error', 'User not found.');
        }

        $this->cancelDelete();
    }
}
