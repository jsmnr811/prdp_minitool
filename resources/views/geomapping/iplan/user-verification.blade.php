<x-layouts.geomapping.iplan.app>
            <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
                <!-- Header -->
                <div class="card-header text-center bg-gradient" 
                     style="background: linear-gradient(90deg, #4f46e5, #3b82f6);">
                    <h1 class="mt-2 fs-4 fw-bold text-uppercase">
                        User Registration Verification
                    </h1>
                </div>

                <div class="card-body">
                    @if ($user)
                        <!-- Certificate Text -->
                        <p class="fs-5 text-muted mb-4 text-center">
                            This is to certify that
                            <b class="text-dark">{{ $user->name }}</b>
                            is officially registered in our system
                        </p>

                        <!-- Flex Layout: ID left | Info right -->
                        <div class="row g-4 align-items-stretch">
                            
                            <!-- User ID Card -->
                            <div class="col-md-5">
                                <div class="card h-100 border shadow-sm d-flex justify-content-center align-items-center p-4">
                                    <x-user-id :user="$user" :logo-src="$logoSrc" :user-image-src="$userImageSrc" :bgSrc="$bgSrc" />
                                </div>
                            </div>

                            <!-- Info Section -->
                            <div class="col-md-7">
                                <div class="card h-100 border shadow-sm">
                                    <div class="card-body">
                                        <h3 class="fs-5 fw-semibold mb-3 border-bottom pb-2">
                                            User Information
                                        </h3>
                                        <dl class="row mb-0">
                                            <dt class="col-sm-4 fw-bold">Name:</dt>
                                            <dd class="col-sm-8">
                                                {{ $user->name }}
                                                @if($user->is_verified)
                                                    <span class="badge bg-success ms-2">Verified</span>
                                                @else
                                                    <span class="badge bg-danger ms-2">Not Verified</span>
                                                @endif
                                            </dd>

                                            <dt class="col-sm-4 fw-bold">Email:</dt>
                                            <dd class="col-sm-8">{{ $user->email }}</dd>

                                            <dt class="col-sm-4 fw-bold">Contact:</dt>
                                            <dd class="col-sm-8">{{ $user->contact_number }}</dd>

                                            <dt class="col-sm-4 fw-bold">Institution:</dt>
                                            <dd class="col-sm-8">{{ $user->institution }}</dd>

                                            <dt class="col-sm-4 fw-bold">Office:</dt>
                                            <dd class="col-sm-8">{{ $user->office }}</dd>

                                            <dt class="col-sm-4 fw-bold">Designation:</dt>
                                            <dd class="col-sm-8">{{ $user->designation }}</dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="py-5 text-center">
                            <h2 class="fs-4 fw-semibold text-danger">
                                ❌ User Not Found in our database.
                            </h2>
                        </div>
                    @endif
                </div>
            </div>
</x-layouts.geomapping.iplan.app>
