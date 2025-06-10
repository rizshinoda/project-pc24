<div
    x-data="{
        height: 0,
        conversationElement: null,
        markAsRead: null
    }"
    x-init="
        conversationElement = document.getElementById('conversation'); 
        height = conversationElement.scrollHeight;
        $nextTick(() => conversationElement.scrollTop = height);

        Echo.private('users.{{ Auth()->User()->id }}')
        .notification((notification) => {
            if (notification['type'] === 'App\\Notifications\\MessageRead' && 
                notification['conversation_id'] == {{$this->selectedConversation->id}}) {
                markAsRead = true;
                
            }
            
            // Scroll ke bawah saat ada notifikasi baru
            $nextTick(() => conversationElement.scrollTop = conversationElement.scrollHeight);
        });
    "

    @scroll-bottom.window="
        $nextTick(() => conversationElement.scrollTop = conversationElement.scrollHeight);
    "
    class="flex flex-col h-screen overflow-hidden">
    <!-- Header -->
    <header class="w-full sticky inset-x-0 flex pb-[5px] pt-[5px] top-0 z-10 bg-white border-b border-gray-300">
        <div class="flex w-full items-center px-2 lg:px-4 gap-2 md:gap-5">
            <!-- Tombol kembali ke daftar chat (hanya tampil di layar kecil) -->
            <a href="#" @click.prevent="showChatList = true" class="shrink-0 lg:hidden">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="black" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
            </a>

            <div class="shrink-0">
                <x-avatar src="{{ asset('/dist/assets/images/faces/2.png') }}" class="h-9 w-9 lg:w-11 lg:h-11"></x-avatar>
            </div>
            <h6 class="font-bold truncate">{{ $selectedConversation->getReceiver()?->name ?? 'user sudah dihapus' }}
            </h6>
        </div>
    </header>


    <!-- Chat Messages -->
    <main
        @scroll="
      scropTop = $el.scrollTop;
      if(scropTop <= 0){
        $dispatch('loadMore');
      }
     
     "

        @update-chat-height.window="
     setTimeout(() => {
        newHeight = $el.scrollHeight;
        oldHeight = height;
        $el.scrollTop = newHeight - oldHeight;
        height = newHeight;
       }, 1);
        "
        id="conversation"
        class="flex flex-col gap-2 p-2.5 overflow-y-auto flex-grow overscroll-contain overflow-x-hidden w-full">
        @if ($loadedMessages)
        @php $previousDate = null; @endphp

        @foreach ($loadedMessages as $key => $message)
        @php
        $messageDate = $message->created_at->format('Y-m-d');
        $formattedDate = $message->created_at->isToday() ? 'Hari ini' :
        ($message->created_at->isYesterday() ? 'Kemarin' :
        $message->created_at->format('d M Y'));
        @endphp

        {{-- Menampilkan separator tanggal jika ada perubahan hari --}}
        @if ($previousDate !== $messageDate)
        <div class="flex justify-center my-3">
            <span class="bg-gray-200 text-gray-600 text-xs py-1 px-3 rounded-full">
                {{ $formattedDate }}
            </span>
        </div>
        @php $previousDate = $messageDate; @endphp
        @endif
        <div
            wire:key="{{time().$key}}"
            @class(['max-w-[85%] md:max-w-[78%] flex w-auto gap-2 relative mt-2', 'ml-auto'=> $message->sender_id === auth()->id()])>
            <div @class([ 'shrink-0' , 'invisible'=> $key > 0 && $loadedMessages[$key - 1]->sender_id == $message->sender_id,
                'hidden' => $message->sender_id === auth()->id()
                ])>
                <x-avatar src="{{ asset('/dist/assets/images/faces/2.png') }}"> </x-avatar>
            </div>

            <div @class([ 'flex flex-wrap text-[15px] rounded-xl p-2.5 flex flex-col text-black bg-[#f6f6f8fb]' , 'rounded-bl-none border border-gray-200/40'=> !($message->sender_id === auth()->id()),
                'rounded-br-none bg-blue-500/80 text-white' => ($message->sender_id === auth()->id())
                ])>
                <!-- Tampilkan gambar jika ada -->
                @if($message->images->isNotEmpty())
                <div class="mt-3 max-w-[300px] overflow-hidden relative" x-data="{ activeIndex: 0, showModal: false, modalImage: '' }">
                    <div class="flex transition-transform duration-300" :style="'transform: translateX(-' + (activeIndex * 100) + '%)'">
                        @foreach($message->images as $image)
                        <img src="{{ asset('storage/' . $image->image_path) }}"
                            alt="Chat Image"
                            class="rounded-lg w-[300px] h-auto object-cover flex-shrink-0 cursor-pointer"
                            @click="modalImage = '{{ asset('storage/' . $image->image_path) }}'; showModal = true">
                        @endforeach
                    </div>

                    <!-- Navigasi -->
                    @if($message->images->count() > 1)
                    <!-- Tombol Panah Kiri -->
                    <button @click="activeIndex = Math.max(0, activeIndex - 1)"
                        x-show="activeIndex > 0"
                        class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-gray-500 text-white p-2 rounded-full">‹</button>

                    <!-- Tombol Panah Kanan -->
                    <button @click="activeIndex = Math.min({{ $message->images->count() - 1 }}, activeIndex + 1)"
                        x-show="activeIndex < {{ $message->images->count() - 1 }}"
                        class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-gray-500 text-white p-2 rounded-full">›</button>
                    @endif

                    <!-- Modal Preview Gambar -->
                    <div x-show="showModal" x-cloak
                        class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50"
                        @click.away="showModal = false">
                        <img :src="modalImage" class="max-w-full max-h-full rounded-lg shadow-lg">
                        <button @click="showModal = false"
                            class="absolute top-5 right-5 bg-red-600 text-white px-3 py-1 rounded-full">✕</button>
                    </div>
                </div>

                @endif





                <div>
                    <!-- Tampilkan pesan teks jika ada -->
                    @if($message->body)
                    <p class="whitespace-normal text-sm md:text-base tracking-wide lg:tracking-normal break-words">
                        {{ safeDecrypt($message->body) }}
                    </p>
                    @endif

                    <!-- Container untuk waktu dan tanda centang -->
                    <div class="ml-auto flex items-center gap-1 space-x-1">
                        <p class="text-xs"
                            @class(['text-gray-500'=> !($message->sender_id === auth()->id()), 'text-white' => ($message->sender_id === auth()->id())])>
                            {{ $message->created_at->format('g:i a') }}
                        </p>

                        @if ($message->sender_id === auth()->id())
                        <div x-data="{markAsRead:@json($message->isRead())}" class="flex items-center">
                            <span x-cloak x-show="markAsRead" class="text-gray-200 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2-all" viewBox="0 0 16 16">
                                    <path d="M12.354 4.354a.5.5 0 0 0-.708-.708L5 10.293 1.854 7.146a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0zm-4.208 7-.896-.897.707-.707.543.543 6.646-6.647a.5.5 0 0 1 .708.708l-7 7a.5.5 0 0 1-.708 0" />
                                    <path d="m5.354 7.146.896.897-.707.707-.897-.896a.5.5 0 1 1 .708-.708" />
                                </svg>
                            </span>

                            <span x-cloak x-show="!markAsRead" class="text-gray-200 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2" viewBox="0 0 16 16">
                                    <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0" />
                                </svg>
                            </span>
                        </div>
                        @endif
                    </div>
                </div>


                <button
                    x-data="{ show: false }"
                    x-show="show"
                    x-transition.opacity
                    @click="document.getElementById('conversation').scrollTop = document.getElementById('conversation').scrollHeight; show = false"
                    x-init="document.getElementById('conversation').addEventListener('scroll', function() { show = this.scrollTop < this.scrollHeight - this.clientHeight - 50 })"
                    class="fixed bottom-35 right-5 bg-blue-500 text-white p-2 rounded-full  transition-opacity duration-300 flex items-center justify-center w-12 h-12">
                    <i class="fas fa-arrow-down text-lg"></i>
                </button>

            </div>

        </div>

        @endforeach
        @endif
    </main>

    <!-- Footer / Input Chat -->
    <footer class="shrink-0 z-10 bg-white inset-x-0">
        <div class="p-1 mt-1">
            <form x-data="{body: @entangle('body'), images: @entangle('images')}"
                @submit.prevent="$wire.sendMessage"
                method="POST"
                enctype="multipart/form-data"
                autocapitalize="off"
                class="w-full p-2 bg-white rounded-lg shadow-md">
                @csrf
                <input type="hidden" autocomplete="false" style="display:none">

                <div class="flex items-center gap-2">
                    <!-- Input File (Hidden) -->
                    <input type="file" id="uploadImage" wire:model="images" multiple class="hidden">

                    <!-- Button Upload Gambar -->
                    <button type="button" onclick="document.getElementById('uploadImage').click()"
                        class="flex items-center justify-center bg-gray-200 w-10 h-10 md:w-12 md:h-12 rounded-lg hover:bg-gray-300 transition">
                        <span class="mdi mdi-image-plus-outline text-xl md:text-2xl text-gray-700"></span>
                    </button>

                    <!-- Input Pesan -->
                    <input x-model="body" type="text" autocomplete="off" autofocus
                        placeholder="Write your message here..."
                        maxlength="1700"
                        class="flex-1 bg-gray-100 border-0 outline-none focus:ring-2 focus:ring-blue-300 hover:ring-0 rounded-lg p-3 md:p-3 text-base md:text-lg placeholder-gray-500 w-full">

                    <!-- Tombol Kirim -->
                    <button class="bg-blue-500 text-white px-3 md:px-4 py-2 md:py-3 rounded-lg hover:bg-blue-600 transition" type="submit">
                        <span class="mdi mdi-send text-lg md:text-xl"></span>
                    </button>
                </div>
            </form>



            <!-- Preview Multiple Images -->
            @if ($images)
            <div class="mt-2 flex flex-wrap gap-3">
                @foreach ($images as $index => $image)
                <div class="relative">
                    <img src="{{ $image->temporaryUrl() }}" class="w-24 h-24 rounded">
                    <!-- Remove Button -->
                    <button wire:click="removeImage({{ $index }})"
                        class="absolute top-0 right-0 bg-red-500 text-white w-6 h-6 rounded-full flex items-center justify-center text-xs hover:bg-red-600 transition">
                        ×
                    </button>
                </div>
                @endforeach
            </div>
            @endif

            <!-- Error Handling -->
            @error('body')
            <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror
            @error('images')
            <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror

        </div>
    </footer>



</div>