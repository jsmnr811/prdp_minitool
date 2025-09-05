<x-public-layout>
    <form action="{{ route('testor') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="w-full sm:w-auto">
            <input type="file" name="image" accept="image/*" capture="environment"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2 cursor-pointer dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            @error('image')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
        <button type="submit"
            class="mt-3 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Upload
        </button>
    </form>
</x-public-layout>
