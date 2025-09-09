<?php

use App\Models\Region;
use App\Models\Province;
use App\Models\GeoOffice;
use Livewire\Attributes\On;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Models\GeomappingUser;
use App\Notifications\MailUserId;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Storage;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

new class extends Component {
    use WithFileUploads;

    public GeomappingUser $user;

    public $image, $existing_image;
    public $firstname, $middlename, $lastname, $ext_name, $sex;
    public $designation, $region_id, $province_id;
    public $email, $contact_number;
    public $food_restriction;
    public $group_number, $table_number;
    public $institution = '';
    public $office = '';
    public $is_iplan = false;
    public $is_livein = false;
    public $role = '';
    public $room_assignment = '';
    public $institutions = [];
    public $offices = [];
    public $regions = [];
    public $provinces = [];
    public $showOfficeField = false;
    public $attendance_days = [];
    public $availableOffices = [];

    public $validatedUserData = [];

    public $editModal = false;
    public $assignModal = false;

    public function mount(): void
    {
        $this->regions = Region::orderBy('name')->get();
        $this->provinces = collect();
        $this->institutions = GeoOffice::distinct('institution')->pluck('institution')->toArray();
    }

    #[On('editGeomappingUser')]
    public function edit(GeomappingUser $user)
    {
        $this->user = $user;
        $this->existing_image = preg_replace('#^storage/#', '', $user->image);
        $this->image = null;
        $this->firstname = $user->firstname;
        $this->middlename = $user->middlename;
        $this->lastname = $user->lastname;
        $this->ext_name = $user->ext_name;
        $this->sex = $user->sex;

        $this->institution = $user->institution;
        $this->office = $user->office;
        $this->designation = $user->designation;
        $this->region_id = $user->region_id;
        $this->province_id = $user->province_id;

        $this->showOfficeField = $user->office ? true : false;

        $this->attendance_days = explode(', ', $user->attendance_days);

        if ($this->region_id) {
            $this->provinces = Province::where('region_code', $this->region_id)->orderBy('name')->get();
        } else {
            $this->provinces = collect();
        }

        $this->email = $user->email;
        $this->contact_number = $user->contact_number;

        $this->food_restriction = $user->food_restriction;

        $this->group_number = $user->group_number;
        $this->table_number = $user->table_number;

        $this->editModal = true;
    }

    #[On('assignGeomappingUser')]
    public function assignGeomappingUser(GeomappingUser $user)
    {
        $this->user = $user;
        $this->group_number = $user->group_number;
        $this->table_number = $user->table_number;

        $this->role = $user->role;
        $this->is_iplan = $user->is_iplan;
        $this->room_assignment = $user->room_assignment;
        $this->is_livein = $user->is_livein;

        $this->assignModal = true;
    }

    public function confirmUpdate()
    {
        $this->validatedUserData = $this->validate([
            'firstname' => 'required|string|max:255',
            'middlename' => 'nullable|string|max:255',
            'lastname' => 'required|string|max:255',
            'ext_name' => 'nullable|string|max:255',
            'sex' => 'required|in:Male,Female',

            'email' => 'required|email|unique:users,email,' . $this->user->id,
            'contact_number' => 'nullable|string|max:11|unique:users,contact_number,' . $this->user->id,
            'food_restriction' => 'nullable|string|max:255',

            'region_id' => 'required',
            'province_id' => 'required',
            'institution' => 'required|string|max:255',
            'office' => 'required|string|max:255',
            'designation' => 'required|string|max:255',

            'attendance_days' => 'required|array|min:1',
        ]);

        if (!$this->existing_image && !$this->image) {
            $this->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            ]);
        }

        // if ($this->image) {
        //     $path = $this->image->store('storage/investmentforum2025', 'public');
        //     $this->validatedUserData['image'] = $path;
        // }
        if ($this->image) {
            $filename = time() . '.' . $this->image->getClientOriginalExtension();
            $this->image->storeAs('investmentforum2025', $filename, 'public');
            $imagePath = 'storage/investmentforum2025/' . $filename;
            $this->validatedUserData['image'] = $imagePath;
        }

        LivewireAlert::title('Are you sure?')->question()->timer(0)->withConfirmButton('Update')->withCancelButton('Cancel')->onConfirm('updateUser')->show();
    }

    public function confirmUpdateAssignment()
    {
        $this->validate([
            'role' => 'required|integer',
            'is_iplan' => 'required|boolean',
            'group_number' => 'required|integer|min:1|max:255',
            'table_number' => 'nullable|integer|min:1|max:255',
            'room_assignment' => 'nullable|string|max:255',
            'is_livein' => 'required|boolean',
        ]);

        LivewireAlert::title('Are you sure?')->text('Are you sure you want to assign this user to this group?')->question()->timer(0)->withConfirmButton('Assign')->withCancelButton('Cancel')->onConfirm('assignUser')->show();
    }

    public function assignUser()
    {
        $this->user->role = $this->role;
        $this->user->is_iplan = $this->is_iplan;
        $this->user->group_number = $this->group_number;
        $this->user->table_number = $this->table_number;
        $this->user->room_assignment = $this->room_assignment;
        $this->user->is_livein = $this->is_livein;
        $this->user->save();
        LivewireAlert::success()->title('Success!')->text('Group and Table has been assigned successfully.')->toast()->position('top-end')->show();
        $this->dispatch('reloadDataTable');
        $this->assignModal = false;
    }

    public function updatedRegionId()
    {
        $this->provinces = Province::where('region_code', $this->region_id)->orderBy('name')->get();
        $this->province_id = null;
    }

    public function updateUser()
    {
        $this->validatedUserData['name'] = trim($this->firstname . ' ' . $this->lastname . ' ' . $this->ext_name);
        $this->validatedUserData['attendance_days'] = implode(', ', $this->attendance_days);
        $this->user->update($this->validatedUserData);
        $this->editModal = false;
        LivewireAlert::title('Success')->success()->toast()->position('top-end')->show();
        $this->dispatch('reloadDataTable');
    }

    public function updatedInstitution($value)
    {
        $this->availableOffices = GeoOffice::where('institution', $value)->orderBy('office')->pluck('office')->toArray();

        $this->office = '';
    }
    #[On('confirmUpdateBlockStatus')]
    public function confirmUpdateBlockStatus(GeomappingUser $user)
    {
        $this->user = $user;
        LivewireAlert::title('Are you sure?')->question()->text('Are you sure you want to update the status of this user? ')->timer(0)->withConfirmButton('Update')->withCancelButton('Cancel')->onConfirm('updateBlockStatus')->show();
    }

    public function updateBlockStatus()
    {
        if ($this->user->is_blocked) {
            $this->user->is_blocked = 0;
        } else {
            $this->user->is_blocked = 1;
        }
        $this->user->save();
        LivewireAlert::title('Success')->success()->text('User status has been updated successfully')->toast()->position('top-end')->show();
        $this->dispatch('reloadDataTable');
    }

    #[On('confirmSendGeomappingUserId')]
    public function confirmSendGeomappingUserId(GeomappingUser $user)
    {
        $this->user = $user;
        LivewireAlert::title('Are you sure?')->question()->text('Are you sure you want to mail the geomapping user id? ')->timer(0)->withConfirmButton('Send')->withCancelButton('Cancel')->onConfirm('sendGeomappingUserId')->show();
    }

    public function sendGeomappingUserId()
    {
        $fileName = 'user-id-' . $this->user->id . '.png';
        $storagePath = storage_path('app/public/' . $fileName);
        // Load logo image and convert to base64
        $logoPath = public_path('media/Scale-Up.png');
        $logoData = base64_encode(file_get_contents($logoPath));
        $logoSrc = 'data:image/png;base64,' . $logoData;

        // Load user image and convert to base64 (check if exists, otherwise use default)
        $userImagePath = $this->user->image && Storage::disk('public')->exists(str_replace('storage/', '', $this->user->image)) && file_exists(public_path($this->user->image)) ? public_path($this->user->image) : storage_path('app/public/investmentforum2025/default.png');

        $userImageData = base64_encode(file_get_contents($userImagePath));
        $userImageSrc = 'data:image/png;base64,' . $userImageData;

        $html = view('components.user-id', ['user' => $this->user, 'logoSrc' => $logoSrc, 'userImageSrc' => $userImageSrc])->render();

        if (file_exists($storagePath)) {
            unlink($storagePath);
        }
        // Generate a PNG snapshot of the HTML
        Browsershot::html($html)
            ->setChromePath('/usr/bin/chromium')
            ->windowSize(330, 520) // match your ID card width & height
            ->waitUntilNetworkIdle() // ensures images/fonts are loaded
            ->save($storagePath);
        $this->user->notify(new MailUserId($this->user));
        LivewireAlert::title('Success')->text('Geomapping User ID has been sent successfully')->success()->toast()->position('top-end')->show();
        $this->dispatch('reloadDataTable');
    }

    #[On('updateVerified')]
    public function updateVerified(GeomappingUser $user)
    {
        $user->is_verified = !$user->is_verified;
        $user->save();
        $this->dispatch('reloadDataTable');
    }
};

?>
<div>
    @vite(['resources/css/app.css'])

    @if ($editModal)
        <!-- Bootstrap Modal (container only) -->
        <div class="modal fade show d-block" id="editUserModal" tabindex="-1" role="dialog"
            aria-labelledby="editUserModalLabel" aria-modal="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content rounded-2xl shadow-lg">

                    <!-- Modal Header -->
                    <div class="modal-header border-b d-flex justify-content-between align-items-center">
                        <h5 class="modal-title font-semibold text-lg" id="editUserModalLabel">Edit Information</h5>
                        <span aria-hidden="true">&times;</span>
                        <button type="button" class="btn-close" wire:click='$set("editModal", false)'
                            aria-label="Close"></button>
                    </div>


                    <!-- Modal Body -->
                    <form wire:submit.prevent="confirmUpdate">
                        <div class="modal-body space-y-6">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            {{-- ✅ Profile Image --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Profile Image</label>
                                <div class="flex items-center gap-3 mt-2">
                                    @if ($image)
                                        {{-- New upload preview --}}
                                        <img src="{{ $image->temporaryUrl() }}" class="rounded-lg border" width="80"
                                            height="80">
                                    @elseif ($existing_image && Storage::disk('public')->exists($existing_image))
                                        {{-- Existing stored image (only if file actually exists) --}}
                                        <div style="position: relative">
                                            <img src="{{ asset('storage/' . $existing_image) }}"
                                                class="rounded-lg border" width="200" height="200">
                                            <div class="absolute top-0 right-0 bg-white rounded-full w-6 h-6 flex items-center justify-center cursor-pointer"
                                                onclick="document.getElementById('profile_image').click()">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                    class="w-4 h-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M16.862 4.487l1.687 1.687a1.875 1.875 0 010 2.652l-8.955 8.955a4.5 4.5 0 01-1.897 1.13l-3.615.965a.75.75 0 01-.927-.928l.965-3.615a4.5 4.5 0 011.13-1.897l8.955-8.955a1.875 1.875 0 012.652 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M19.5 7.125L16.875 4.5" />
                                                </svg>
                                            </div>
                                        </div>
                                    @else
                                        {{-- ✅ Fallback to default-image.png --}}
                                        <div style="position: relative">
                                            <img src="{{ asset('storage/investmentforum2025/default.png') }}"
                                                class="rounded-lg border" width="200" height="200">
                                            <div class="absolute top-0 right-0 bg-white rounded-full w-6 h-6 flex items-center justify-center cursor-pointer"
                                                onclick="document.getElementById('profile_image').click()">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                    class="w-4 h-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M16.862 4.487l1.687 1.687a1.875 1.875 0 010 2.652l-8.955 8.955a4.5 4.5 0 01-1.897 1.13l-3.615.965a.75.75 0 01-.927-.928l.965-3.615a4.5 4.5 0 011.13-1.897l8.955-8.955a1.875 1.875 0 012.652 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M19.5 7.125L16.875 4.5" />
                                                </svg>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Hidden file input --}}
                                    <input type="file" wire:model="image" accept=".jpg,.jpeg,.png"
                                        style="display: none" id="profile_image" class="text-sm">
                                </div>

                                @error('image')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>



                            {{-- 🆔 Primary Info --}}
                            <div>
                                <h6 class="text-gray-700 font-semibold mb-2">Primary Info</h6>
                                <div class="grid grid-cols-3 gap-4">
                                    <div>
                                        <label class="text-sm font-medium">First Name <span
                                                class="text-red-500">*</span></label>
                                        <input type="text" wire:model="firstname" placeholder="First Name"
                                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                        @error('firstname')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium">Middle Name <small
                                                class="text-gray-400">(optional)</small></label>
                                        <input type="text" wire:model="middlename" placeholder="Middle Name"
                                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium">Last Name <span
                                                class="text-red-500">*</span></label>
                                        <input type="text" wire:model="lastname" placeholder="Last Name"
                                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Name
                                            Extension<span class="text-gray-400"> (optional)</span></label>
                                        <input type="text" wire:model="ext_name" placeholder="e.g. Jr., Sr."
                                            class="form-control w-full rounded border border-gray-300 p-2">
                                        @error('ext_name')
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Sex
                                            <span class="text-red-600">*</span></label>
                                        <select wire:model="sex"
                                            class="form-control w-full rounded border border-gray-300 p-2">
                                            <option value="">Select Sex</option>
                                            <option {{ $sex == 'Male' ? 'selected' : '' }} value="Male">Male</option>
                                            <option {{ $sex == 'Female' ? 'selected' : '' }} value="Female">Female
                                            </option>
                                        </select>
                                        @error('sex')
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Organizational Info --}}
                            <div>
                                <h6 class="text-gray-700 font-semibold mb-4">Organizational Info</h6>

                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

                                    {{-- Institution --}}
                                    <div>
                                        <label for="institution"
                                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                            Institution <span class="text-red-600">*</span>
                                        </label>
                                        <select id="institution" wire:model.live="institution"
                                            class="form-control w-full rounded border border-gray-300 p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                            <option value="">Select Institution</option>
                                            @foreach ($institutions as $inst)
                                                <option value="{{ $inst }}">{{ $inst }}</option>
                                            @endforeach
                                        </select>
                                        @error('institution')
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    {{-- Office --}}
                                    <div>
                                        <label for="office"
                                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                            Office <span class="text-red-600">*</span>
                                        </label>
                                        @if (!empty($availableOffices))
                                            <select id="office" wire:model="office"
                                                class="form-control w-full rounded border border-gray-300 p-2">
                                                <option value="">Select an office</option>
                                                @foreach ($availableOffices as $off)
                                                    <option value="{{ $off }}">{{ $off }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <input type="text" id="office" wire:model="office"
                                                placeholder="Enter your office"
                                                class="form-control w-full rounded border border-gray-300 p-2" />
                                        @endif
                                        @error('office')
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    {{-- Designation --}}
                                    <div>
                                        <label for="designation"
                                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                            Designation <span class="text-red-600">*</span>
                                        </label>
                                        <input type="text" id="designation" wire:model="designation"
                                            placeholder="Designation"
                                            class="form-control w-full rounded border border-gray-300 p-2" />
                                        @error('designation')
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Region & Province (with ids) --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6">
                                    <div>
                                        <label for="region_id" class="text-sm font-medium">
                                            Region <span class="text-red-500">*</span>
                                        </label>
                                        <select id="region_id" wire:model.debounce.500ms="region_id"
                                            class="form-control w-full rounded border border-gray-300 p-2">
                                            <option value="">Select Region</option>
                                            @foreach ($regions as $reg)
                                                <option value="{{ $reg->code }}"
                                                    {{ $reg->code == $region_id ? 'selected' : '' }}>
                                                    {{ $reg->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('region_id')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="province_id" class="text-sm font-medium">
                                            Province <span class="text-red-500">*</span>
                                        </label>
                                        <select id="province_id" wire:model="province_id"
                                            class="form-control w-full rounded border border-gray-300 p-2">
                                            <option value="">Select Province</option>
                                            @foreach ($provinces as $prov)
                                                <option value="{{ $prov->id }}"
                                                    {{ $prov->id == $province_id ? 'selected' : '' }}>
                                                    {{ $prov->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('province_id')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>


                            {{-- 📞 Contact Info --}}
                            <div>
                                <h6 class="text-gray-700 font-semibold mb-2">Contact Information</h6>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-sm font-medium">Email <span
                                                class="text-red-500">*</span></label>
                                        <input type="email" wire:model="email" placeholder="you@example.com"
                                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium">Contact Number <span
                                                class="text-red-500">*</span></label>
                                        <input type="text" wire:model="contact_number" minlength="11"
                                            maxlength="11" placeholder="09123456789"
                                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </div>
                            </div>


                            <!-- Food Restriction -->
                            <div>
                                <label class="text-sm font-medium">Food Restriction <span
                                        class="text-red-500">*</span>
                                    <small class="text-gray-400">(Put N/A if not applicable)</small>
                                </label>
                                <textarea wire:model="food_restriction" rows="3" placeholder="Specify any food restriction..."
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                                @error('food_restriction')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mt-4">
                                <label class="block mb-2 font-semibold text-gray-900 dark:text-white">
                                    Select days you will attend <span class="text-red-600">*</span>
                                </label>

                                <div class="flex flex-wrap gap-4">
                                    @foreach (['Day 1', 'Day 2', 'Day 3'] as $day)
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" wire:model="attendance_days"
                                                value="{{ $day }}"
                                                class="form-checkbox h-5 w-5 text-indigo-600 rounded focus:ring-indigo-500">
                                            <span
                                                class="ml-2 text-gray-700 dark:text-gray-300">{{ $day }}</span>
                                        </label>
                                    @endforeach
                                </div>

                                @error('attendance_days')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer border-t mt-6">
                            <button type="button" class="btn btn-secondary"
                                wire:click='$set("editModal", false)'>Close</button>
                            <button type="submit" class="btn btn-primary">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Backdrop -->
        <div class="modal-backdrop fade show"></div>
    @endif

    @if ($assignModal)
        <!-- Bootstrap Modal (container only) -->
        <div class="modal fade show d-block" id="assignUser" tabindex="-1" role="dialog"
            aria-labelledby="assignUserLabel" aria-modal="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content rounded-2xl shadow-lg">

                    <!-- Modal Header -->
                    <div class="modal-header border-b">
                        <h5 class="modal-title font-semibold text-lg" id="assignUserLabel">Assign Role, Group Number,
                            Table Number and Room Assignment</h5>
                        <button type="button" class="close" wire:click='$set("assignModal", false)'
                            aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <form wire:submit.prevent="confirmUpdateAssignment">
                        <div class="modal-body space-y-6">

                            <div>
                                <!-- Group & Table -->
                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    {{-- Role Dropdown --}}
                                    <div>
                                        <label class="text-sm font-medium">Role <span
                                                class="text-red-500">*</span></label>
                                        <select wire:model.live="role"
                                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                            <option value="" disabled>Select role</option>
                                            <option value="1">Administrator</option>
                                            <option value="2">Participant</option>
                                        </select>
                                        @error('role')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- Conditional: Show if Administrator --}}
                                    @if ($role == 1)
                                        <div>
                                            <label class="text-sm font-medium">Is I-PLAN? <span
                                                    class="text-red-500">*</span></label>
                                            <select wire:model="is_iplan"
                                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                                <option value="" disabled>Select option</option>
                                                <option value="1">Yes</option>
                                                <option value="0">No</option>
                                            </select>
                                            @error('is_iplan')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    @endif
                                </div>

                                {{-- Group Number and Table Number on the same row --}}
                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="text-sm font-medium">Group Number <span
                                                class="text-red-500">*</span></label>
                                        <input type="text" wire:model="group_number"
                                            placeholder="Enter group number" minlength="1" maxlength="2"
                                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                        @error('group_number')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="text-sm font-medium">Table Number</label>
                                        <input type="text" wire:model="table_number"
                                            placeholder="Enter table number" minlength="1" maxlength="1"
                                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                        @error('table_number')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Room Assignment on the same row --}}
                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="text-sm font-medium">Is Live-in <span
                                                class="text-red-500">*</span></label>
                                        <select wire:model.live="is_livein"
                                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                            <option value="" disabled>Select option</option>
                                            <option value="1">Yes</option>
                                            <option value="0">No</option>
                                        </select>
                                        @error('is_livein')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    @if ($is_livein)
                                        <div>
                                            <label class="text-sm font-medium">Room Assignment</label>
                                            <select wire:model="room_assignment"
                                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                                <option value="">-- Select Room Type --</option>
                                                <option value="Diamond - Solo">Diamond (Solo)</option>
                                                <option value="Premier - Solo">Premier (Solo)</option>
                                                <option value="Premier - Double">Premier (Double)</option>
                                                <option value="Premier - Triple">Premier (Triple)</option>
                                                <option value="Deluxe - Solo">Deluxe (Solo)</option>
                                                <option value="Deluxe - Double">Deluxe (Double)</option>
                                                <option value="Deluxe - Triple">Deluxe (Triple)</option>
                                                <option value="Suite">Suite</option>
                                            </select>

                                            @error('room_assignment')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    @endif


                                </div>


                            </div>

                        </div>

                        <!-- Modal Footer -->
                        <div class="modal-footer border-t mt-6">
                            <button type="button" class="btn btn-secondary"
                                wire:click='$set("assignModal", false)'>Close</button>
                            <button type="submit" class="btn btn-primary">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Backdrop -->
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
