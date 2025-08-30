<?php

namespace App\Livewire;

use App\Models\GeomappingUser;
use App\Models\Region;
use App\Models\Province;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use PHPMailer\PHPMailer\PHPMailer;

class InvestmentRegistration extends Component
{
    use WithFileUploads;

     public $image;
    public $firstname, $middlename, $lastname, $ext_name, $sex;
    public $institution, $office, $designation, $region, $province;
    public  $email, $contact_number;
    public $food_restriction;
    public $regions = [];
    public $provinces = [];

    public $showOfficeField = 'false';
    public $attendance_days = [];

    protected $rules = [
        'image'            => 'required|image|max:2048',
        'firstname'        => 'required|string|min:2',
        'middlename'       => 'nullable|string|min:2',
        'lastname'         => 'required|string|min:2',
        'ext_name'         => 'nullable|string|max:10',
        'sex'              => 'required|in:Male,Female',

        'institution'      => 'required|string',
        'office'           => 'required|string',
        'designation'      => 'required|string',

        'region'           => 'required|exists:regions,id',
        'province'         => 'required|exists:provinces,id',

        'email'            => 'required|email|unique:geomapping_users,email',
        'contact_number'   => 'required|numeric|digits:11',

        'food_restriction' => 'nullable|string|max:255',
        'attendance_days'  => 'required|array|min:1',
    ];


    public function mount()
    {
        $this->regions = Region::all();
        $this->provinces = collect();
    }

    public function updatedRegion($value)
    {
        $provs = Province::where('REGION_ID', $value)->orderBy('PROVINCE')->get();
        $this->provinces = $provs;
        $this->province = null;
    }


    public function register()
    {
        $this->validate();

        $filename = time() . '.' . $this->image->getClientOriginalExtension();
        $this->image->storeAs('investmentforum2025', $filename, 'public');
        $imagePath = 'storage/investmentforum2025/' . $filename;

        $loginCode = strtoupper(Str::random(8));

        GeomappingUser::create([
            'image'            => $imagePath,
            'name' => implode(' ', array_filter([$this->firstname, $this->middlename, $this->lastname, $this->ext_name])),
            'firstname'        => $this->firstname,
            'middlename'       => $this->middlename,
            'lastname'         => $this->lastname,
            'ext_name'         => $this->ext_name,
            'sex'              => $this->sex,

            'institution'      => $this->institution,
            'office'           => $this->office,
            'designation'      => $this->designation,
            'region_id'        => $this->region,
            'province_id'      => $this->province,

            'email'            => $this->email,
            'contact_number'   => $this->contact_number,

            'food_restriction' => $this->food_restriction,
            'attendance_days'  => implode(', ', $this->attendance_days),

            'login_code'       => $loginCode,
        ]);

        session()->flash('message', "✅ Registration successful! Your login code is: {$loginCode}");

        $this->reset();
        LivewireAlert::title('Success!')
            ->text('You have been successfully registered.')
            ->success()
            ->toast()
            ->position('top-end')
            ->show();
    }

    public function resetForm()
    {
        $this->reset([
            'image',
            'firstname',
            'middlename',
            'lastname',
            'ext_name',
            'sex',
            'institution',
            'office',
            'designation',
            'region',
            'province',
            'email',
            'contact_number',
            'food_restriction',
            'attendance_days',
        ]);

        $this->provinces = collect();
    }

    public function render()
    {
        return view('livewire.geomapping.iplan.investment-registration', [
            'regions' => $this->regions,
            'provinces' => $this->provinces,
        ])->layout('components.layouts.investmentForum2025.app');
    }
}
