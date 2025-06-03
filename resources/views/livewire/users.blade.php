<div class="max-w-6xl mx-auto my-16">

    <h5 class="text-center text-5xl font-bold py-3">Users</h5>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 ">

        @foreach ($users as $key=> $user)

        {{-- child --}}
        <div class="w-full bg-white border border-gray-200 rounded-lg p-5 shadow">

            <div class="flex flex-col items-center pb-6">

                <img src="{{asset('/dist/assets/images/faces/2.png')}}" alt="image" class="w-24 h-24 mb-2.5 mt-2 rounded-full shadow-lg">

                <h5 class="mb-1 text-xl font-medium text-gray-900">
                    {{$user->name}}
                </h5>
                <span class="text-sm text-gray-500">{{$user->email}}</span>

                <!-- Tombol lebih kecil & rounded -->
                <div class="flex mt-4 gap-3 md:mt-6">
                    <button wire:click="message({{$user->id}})" style="border-radius: 9999px !important;"
                        class="bg-gray-300 text-black px-4 py-2 text-sm rounded-full shadow hover:bg-gray-400 transition">
                        Chat
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>

</div>