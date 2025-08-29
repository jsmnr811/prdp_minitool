<div>
    <h1 class="text-xl font-semibold">User List</h1>
    <section class="bg-gray-50 dark:bg-gray-900 p-3 sm:p-5">
        <div class="mx-auto max-w-screen-2xl px-8 lg:px-8">
            <!-- Start coding here -->
            <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">
                <div
                    class="flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-4">
                    <div class="w-full md:w-1/2">
                        <form class="flex items-center">
                            <label for="simple-search" class="sr-only">Search</label>
                            <div class="relative w-full">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400"
                                        fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd"
                                            d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <input type="text" id="simple-search" wire:model.live.debounce.500ms="search"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                    placeholder="Search" required="">
                            </div>
                        </form>
                    </div>
                    <div class="w-full md:w-auto flex flex-col md:flex-row space-y-2 md:space-y-0 items-stretch md:items-center justify-end md:space-x-3 flex-shrink-0">
                        <button type="button" wire:click="openCreateModal()"
                            class="flex items-center justify-center text-white bg-blue-500 hover:bg-blue-600 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
                            <svg class="h-4 w-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path clip-rule="evenodd" fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" />
                            </svg>
                            Add user
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <x-flowbite.table>
                        <x-slot:thead>
                            <th class="px-4 py-2 border text-center">#</th>
                            <th class="px-4 py-2 border text-center">Name</th>
                            <th class="px-4 py-2 border text-center">Email</th>
                            <th class="px-4 py-2 border text-center">Contact Number</th>
                            <th class="px-4 py-2 border text-center">Role</th>
                            <th class="px-4 py-2 border text-center">Status</th>
                            <!-- <th class="px-4 py-2 border text-center">OTP</th> -->
                            <th class="px-4 py-2 border text-center">Actions</th>
                        </x-slot:thead>

                        @forelse ($users as $index => $user)
                        <tr class="hover:bg-gray-50 text-center">
                            <td class="px-4 py-2 border">{{ $index + 1 }}</td>
                            <td class="px-4 py-2 border">{{ $user->name }}</td>
                            <td class="px-4 py-2 border">{{ $user->email }}</td>
                            <td class="px-4 py-2 border">{{ $user->contact_number }}</td>
                            <td class="px-4 py-2 border">{{ ucwords($user->roles->first()->name) }}</td>
                            <td class="px-4 py-2 border">
                                @if($user->trashed())
                                <span class="inline-flex items-center px-2 py-1 text-xs font-semibold text-gray-600 bg-gray-100 rounded-full">
                                    Deleted
                                </span>
                                @else
                                <span class="inline-flex items-center px-2 py-1 text-xs font-semibold
                                        {{ $user->status == 1 ? 'text-green-800 bg-green-100' : 'text-red-800 bg-red-100' }}
                                        rounded-full">
                                    {{ $user->status == 1 ? 'Active' : 'Inactive' }}
                                </span>
                                @endif
                            </td>

                            <!-- <td class="px-4 py-2 border">{{ $user->otp }}</td> -->
                            <td class="w-40 px-4 py-2 border space-x-1">
                                <div class="flex justify-center items-center space-x-2">
                                    <!-- Edit Button -->
                                    <a role="button" wire:click="openEditModal({{ $user->id }})"
                                        class="bg-blue-500 text-white px-3 py-2 text-xs rounded inline-flex items-center hover:bg-blue-600">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>

                                    <!-- Delete Button or Restore Button -->
                                    @if ($user->trashed())
                                    <a role="button" wire:click="restoreAlert({{ $user->id }})"
                                        class="bg-green-500 text-white px-3 py-2 text-xs rounded inline-flex items-center hover:bg-green-600">
                                        <i class="fa-solid fa-undo"></i>
                                    </a>
                                    @else
                                    <a role="button" wire:click="sendEmail()"
                                        class="bg-red-500 text-white px-3 py-2 text-xs rounded inline-flex items-center hover:bg-red-600">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>
                                    @endif

                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="13" class="px-4 py-2 border text-center">
                                No user found.
                            </td>
                        </tr>
                        @endforelse
                    </x-flowbite.table>

                </div>

            </div>
        </div>
    </section>

    @if ($showCreateModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Create New User</h2>

            <!-- Form -->
            <form wire:submit.prevent="createAlert">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" wire:model.defer="user.name"
                        class="w-full mt-1 px-3 py-2 border rounded shadow-sm focus:ring focus:border-blue-300">
                    <x-input-error :messages="$errors->get('user.name')" class="mt-2" />
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" wire:model.defer="user.email"
                        class="w-full mt-1 px-3 py-2 border rounded shadow-sm focus:ring focus:border-blue-300">
                    <x-input-error :messages="$errors->get('user.email')" class="mt-2" />
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Contact Number</label>
                    <input type="text" wire:model.defer="user.contact_number"
                        class="w-full mt-1 px-3 py-2 border rounded shadow-sm focus:ring focus:border-blue-300">
                    <x-input-error :messages="$errors->get('user.contact_number')" class="mt-2" />
                </div>

                <!-- Role selector (optional) -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Role</label>
                    <select wire:model.defer="role"
                        class="w-full mt-1 px-3 py-2 border rounded shadow-sm focus:ring focus:border-blue-300">
                        <option value="">Select Role</option>
                        @foreach ($allRoles as $roleOption)
                        <option value="{{ $roleOption->name }}">{{ ucfirst($roleOption->name) }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('role')" class="mt-2" />
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" wire:click="closeCreateModal"
                        class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded">
                        Cancel
                    </button>

                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded">
                        Create
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
    @if ($showEditModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Edit User</h2>

            <!-- Form -->
            <form wire:submit.prevent="updateUser">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" wire:model.defer="user.name"
                        class="w-full mt-1 px-3 py-2 border rounded shadow-sm focus:ring focus:border-blue-300">
                    <x-input-error :messages="$errors->get('user.name')" class="mt-2" />
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" wire:model.defer="user.email"
                        class="w-full mt-1 px-3 py-2 border rounded shadow-sm focus:ring focus:border-blue-300">
                    <x-input-error :messages="$errors->get('user.email')" class="mt-2" />
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Contact Number</label>
                    <input type="text" wire:model.defer="user.contact_number"
                        class="w-full mt-1 px-3 py-2 border rounded shadow-sm focus:ring focus:border-blue-300">
                    <x-input-error :messages="$errors->get('user.contact_number')" class="mt-2" />
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <select wire:model.defer="user.status"
                        class="w-full mt-1 px-3 py-2 border rounded shadow-sm focus:ring focus:border-blue-300">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                    <x-input-error :messages="$errors->get('user.status')" class="mt-2" />
                </div>

                <!-- Role selector (optional) -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Role</label>
                    <select wire:model.defer="role"
                        class="w-full mt-1 px-3 py-2 border rounded shadow-sm focus:ring focus:border-blue-300">
                        @foreach ($allRoles as $roleOption)
                        <option value="{{ $roleOption->name }}">{{ ucfirst($roleOption->name) }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('role')" class="mt-2" />
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" wire:click="closeEditModal"
                        class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded">
                        Cancel
                    </button>

                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>