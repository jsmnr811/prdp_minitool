<?php

namespace App\Livewire\Geomapping\Iplan;

use App\Models\Region;
use Livewire\Component;
use App\Models\Province;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use App\Models\GeomappingUser;
use Livewire\Attributes\Layout;
use PHPMailer\PHPMailer\PHPMailer;
use Illuminate\Support\Facades\Log;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

#[Layout('components.layouts.investmentForum2025.app')]
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

        'region'           => 'required|exists:regions,code',
        'province'         => 'required|exists:provinces,code',

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
        $provs = Province::where('region_code', $value)->orderBy('name')->get();
        $this->provinces = $provs;
        $this->province = null;
    }
    public function updatedInstitution($value)
    {
        $this->showOfficeField = $value === 'Provincial Local Government Unit';
        $this->office = '';
    }
    public function updatedImage()
    {
        $this->validate([
            'image' => 'required|image|mimes:jpeg,png|max:2048', // max 2MB example
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
            'name' => implode(' ', array_filter([$this->firstname, $this->lastname, $this->ext_name])),
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
