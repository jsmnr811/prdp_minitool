<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\User;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

use Illuminate\Support\Facades\Mail;
use App\Mail\SendEmail;

class UserTable extends Component
{
    use WithPagination;

    #[Layout('layouts.app')]

    public string $search = '';
    public bool $confirmDelete = false;
    public bool $confirmRestore = false;
    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    protected $paginationTheme = 'tailwind';
    public $allRoles;
    public $user_id;

    public $user = [
        'name' => '',
        'email' => '',
        'contact_number' => '',
        'status' => 1,
    ];

    public $role;

    public function sendEmail()
    {
        $data = [
            'name' => 'John Doe',
            'otp' => 'ABCDEFG'
        ];

        Mail::to('recipient@example.com')->send(new SendEmail($data));

        return response()->json(['success' => 'Email sent successfully.']);
    }
    public function openCreateModal()
    {
        $this->allRoles = Role::where('id', '!=', 1)->get();
        $this->showCreateModal = true;
    }
    public function closeCreateModal()
    {
        $this->showCreateModal = false;
    }

    public function createAlert()
    {
        $this->validate([
            'user.name' => 'required|string|max:255',
            'user.email' => 'required|email|unique:users,email',
            'user.contact_number' => 'required|numeric|digits:11|unique:users,contact_number',
            'role' => 'required|string',
        ], [
            'user.name.required' => 'The name field is required.',

            'user.email.required' => 'The email address is required.',
            'user.email.email' => 'Please provide a valid email address.',
            'user.email.unique' => 'This email address is already in use.',

            'user.contact_number.required' => 'The contact number is required.',
            'user.contact_number.numeric' => 'The contact number must be numeric.',
            'user.contact_number.digits' => 'The contact number must be exactly 11 digits.',
            'user.contact_number.unique' => 'This contact number is already in use.',

            'role.required' => 'The role field is required.',
            'role.string' => 'The role must be a string.',
        ]);

        LivewireAlert::title('Are you sure?')
            ->text('This will create the user and they will be active immediately.')
            ->warning()
            ->timer(0)
            ->withConfirmButton('Create User')
            ->withCancelButton('Cancel')
            ->onConfirm('createUser')
            ->show();
    }

    public function createUser()
    {
        $user = User::create([
            'name' => $this->user['name'],
            'email' => $this->user['email'],
            'contact_number' => $this->user['contact_number'] ?? null,
            'email_verified_at' => now(),
            'status' => 1,
        ]);

        $user->assignRole($this->role);

        $this->showCreateModal = false;

        LivewireAlert::title('User Created!')
            ->text('The user has been created and is active now.')
            ->success()
            ->toast()
            ->position('top-end')
            ->show();
    }

    public function openEditModal(User $user): void
    {
        $this->user = $user->only(array_keys($this->user));
        $this->user_id = $user->id;

        $this->role = $user->roles()->first()?->name;
        $this->allRoles = Role::where('id', '!=', 1)->get();

        $this->showEditModal = true;
    }
    public function updateUser()
    {
        $this->validate([
            'user.name' => 'required|string|max:255',
            'user.email' => 'required|email|unique:users,email,' . $this->user_id,
            'user.contact_number' => 'nullable|string|max:11|unique:users,contact_number,' . $this->user_id,
            'user.status' => 'required|in:0,1',
            'role' => 'required|string',
        ]);

        $user = User::findOrFail($this->user_id);
        $user->update($this->user);
        $user->syncRoles([$this->role]);

        $this->showEditModal = false;

        LivewireAlert::title('Updated!')
            ->text('User has been updated successfully.')
            ->success()
            ->toast()
            ->position('top-end')
            ->show();
    }
    public function closeEditModal()
    {
        $this->showEditModal = false;
    }
    public function restoreAlert(int $user_id)
    {
        $this->user_id = $user_id;
        LivewireAlert::title('Are you sure?')
            ->text('This will restore the user and they will be active again.')
            ->warning()
            ->timer(0)
            ->withConfirmButton('Restore')
            ->withCancelButton('Cancel')
            ->onConfirm('restoreUser')
            ->show();
    }
    public function restoreUser()
    {
        $user = User::withTrashed()->find($this->user_id);

        if ($user) {
            $user->restore();

            LivewireAlert::title('Restored!')
                ->text('User has been restored successfully.')
                ->success()
                ->toast()
                ->position('top-end')
                ->show();
        }
    }
    public function deleteAlert(int $user_id)
    {
        $this->user_id = $user_id;
        LivewireAlert::title('Are you sure?')
            ->text('This will move the user to trash. You can restore them later.')
            ->warning()
            ->timer(0)
            ->withConfirmButton('Move to Trash')
            ->withCancelButton('Cancel')
            ->onConfirm('deleteUser')
            ->show();
    }
    public function deleteUser()
    {
        if ($this->user_id) {
            $user = User::findOrFail($this->user_id);

            $user->delete();

            $this->reset();

            LivewireAlert::title('Success!')
                ->text('User has been deleted.')
                ->success()
                ->toast()
                ->position('top-end')
                ->show();
        }
    }
    public function render(): View
    {
        $users = User::search($this->search)
            ->with('roles')
            ->withTrashed()
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'superadministrator');
            })
            ->get();

        return view('livewire.user-table', [
            'users' => $users,
        ]);
    }
}
