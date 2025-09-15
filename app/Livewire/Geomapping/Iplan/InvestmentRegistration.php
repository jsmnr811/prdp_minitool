<?php

namespace App\Livewire\Geomapping\Iplan;

use App\Models\Region;
use Livewire\Component;
use App\Models\Province;
use App\Models\GeoOffice;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use App\Models\GeomappingUser;
use Livewire\Attributes\Layout;
use PHPMailer\PHPMailer\PHPMailer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Notifications\UserRegistered;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

#[Layout('components.layouts.investmentForum2025.app')]
class InvestmentRegistration extends Component
{
    use WithFileUploads;

    public $image;
    public $firstname, $middlename, $lastname, $ext_name, $sex;
    public $designation, $region, $province;
    public  $email, $contact_number;
    public $food_restriction;
    public $regions = [];
    public $provinces = [];

    public $institution = "";
    public $office = "";

    // public $showOfficeField = 'false';
    public $availableOffices = [];

    public $attendance_days = [];
    public $institutions = [];
    public $offices = [];

    protected $rules = [
        'image' => 'required|image|mimes:jpeg,jpg,png,gif,heic|max:5048',
        'firstname'        => 'required|string|min:2',
        'middlename'       => 'nullable|string|min:2',
        'lastname'         => 'required|string|min:2',
        'ext_name'         => 'nullable|string|max:10',
        'sex'              => 'required|in:Male,Female',

        'institution'      => 'required|string',
        'office'           => 'required|string',
        'designation'      => 'required|string',

        'region'           => 'required|exists:regions,code',
        'province'         => 'required|exists:provinces,code',

        'email'            => 'required|email|unique:geomapping_users,email',
        'contact_number'   => 'required|numeric|digits:11',

        'food_restriction' => 'nullable|string|max:255',
        'attendance_days'  => 'required|array|min:1',
    ];


    public function mount()
    {
        // if (Auth::guard('geomapping')->check()) {
        //     $user = Auth::guard('geomapping')->user();

        //     if ((int) $user->role === 1) {
        //         return redirect()->route('investment.user-list');
        //     }

        //     return redirect()->route('geomapping.iplan.landing');
        // }
        // Check if the user is authenticated using the 'geomapping' guard
        if (Auth::guard('geomapping')->check()) {
            $user = Auth::guard('geomapping')->user();

            // Allow access only if the user ID is 4 or 5
            if ($user->id === 4 || $user->id === 5) {
                // User with ID 4 or 5 can access the page, no redirection needed
                return;
            }
        }
        $this->regions = Region::all();
        $this->provinces = collect();
        $this->institutions = GeoOffice::where('status', 1)
            ->distinct()
            ->pluck('institution')
            ->toArray();
        // Redirect any other user (including guests) to the login page
        return redirect()->route('geomapping.iplan.login');
    }

    public function updatedRegion($value)
    {
        $provs = Province::where('region_code', $value)->orderBy('name')->get();
        $this->provinces = $provs;
        $this->province = null;
    }
    public function updatedInstitution($value)
    {
        $this->availableOffices = GeoOffice::where('institution', $value)
            ->orderBy('office')
            ->pluck('office')
            ->toArray();

        $this->office = '';
    }

    public function updatedImage()
    {
        $this->validate([
            'image' => 'required|image|mimes:jpeg,png|max:25600', // max 2MB example
        ]);
    }

    public function register()
    {
        $this->validate();
        // If institution is "Provincial Local Government Unit", check uniqueness of office+province
        if ($this->institution === 'Provincial Local Government Unit') {
            $exists = GeomappingUser::where('institution', $this->institution)
                ->where('province_id', $this->province)
                ->where('office', $this->office)
                ->exists();

            if ($exists) {
                $this->addError('office', 'There is already a participant registered with this office in the selected province.');
                return; // Stop execution and show error
            }
        }

        $filename = time() . '.' . $this->image->getClientOriginalExtension();
        $this->image->storeAs('investmentforum2025', $filename, 'public');
        $imagePath = 'storage/investmentforum2025/' . $filename;

        $loginCode = strtoupper(Str::random(8));

        GeomappingUser::create([
            'image'            => $imagePath,
            'name' => implode(' ', array_filter([$this->firstname, $this->lastname])),
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
            'role'          => 2
        ]);

        session()->flash('message', "✅ Registration successful! Your login code is: {$loginCode}");

        // // return redirect()->to('/dashboard');
        // $mail = new PHPMailer(true);

        // try {
        //     $mail->isSMTP();
        //     $mail->Host       = 'smtp.gmail.com';
        //     $mail->SMTPAuth   = true;
        //     $mail->Username   = 'prdponline.ggu@gmail.com'; // your Gmail
        //     $mail->Password   = 'sidx daut wjse asas';       // app password
        //     $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        //     $mail->Port       = 587;

        //     $mail->setFrom('prdponline.ggu@gmail.com', 'PRDP Investment Forum');
        //     $mail->addAddress($this->email, $this->firstname . ' ' . $this->lastname);

        //     $mail->isHTML(true);
        //     $mail->Subject = 'PRDP Investment Forum Registration Confirmation';
        //     $mail->Body    = "<p>Thank you for registering, {$this->firstname}!</p><p>Your login code is: <strong>{$loginCode}</strong></p>";

        //     // Attach image
        //     $mail->addAttachment(public_path('storage/investmentforum2025/' . $filename), 'Photo.jpg');

        //     $mail->send();
        // } catch (Exception $e) {
        //     logger()->error("Email sending failed: {$mail->ErrorInfo}");
        // }

        $this->resetExcept('regions');

        LivewireAlert::title('Success!')
            ->text('You have been successfully registered.')
            ->success()
            ->toast()
            ->position('top-end')
            ->show();
    }
}
