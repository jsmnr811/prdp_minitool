<?php

namespace App\Livewire;

use App\Models\Region;
use Livewire\Component;
use App\Models\Province;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;
use App\Models\GeomappingUser;
use PHPMailer\PHPMailer\PHPMailer;
use Illuminate\Support\Facades\Log;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class UserList extends Component
{
    #[On('hello')]
    public function hello(GeomappingUser $user){
        dd($user);
    }

    public function render()
    {
        return view('livewire.geomapping.iplan.user-list', [
            'model' => GeomappingUser::class,
            'columns' => [
                'id' => '#',
                'name' => 'Name',
                'email' => 'Email',
                'created_at' => 'Created At',
                'actions' => 'Actions',
            ],
            'searchable' => ['name', 'email'],
            'customColumns' => [
                'actions' => 'components.geomapping-user-list-action'
            ]

        ])->layout('components.layouts.investmentForum2025.app');
    }
}
