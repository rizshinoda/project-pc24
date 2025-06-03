<div x-data="{ showChatList: window.innerWidth >= 1024 }"
    x-init="window.addEventListener('resize', () => showChatList = window.innerWidth >= 1024)"
    class="fixed h-full flex flex-col lg:flex-row bg-white border lg:shadow-sm overflow-hidden inset-0 lg:top-5 lg:inset-x-2 m-auto lg:h-[90%] rounded-t-lg w-full">

    <!-- Chat List (Tampil di layar besar, bisa di-toggle di layar kecil) -->
    <div
        x-show="showChatList"
        x-transition:enter="transform transition ease-out duration-300"
        x-transition:enter-start="-translate-x-full opacity-0"
        x-transition:enter-end="translate-x-0 opacity-100"
        x-transition:leave="transform transition ease-in duration-300"
        x-transition:leave-start="translate-x-0 opacity-100"
        x-transition:leave-end="-translate-x-full opacity-0"
        class="absolute inset-0 md:relative md:w-[420px] xl:w-[450px] overflow-y-auto shrink-0 h-full border bg-white z-20 md:block">
        <livewire:chat.chat-list :selectedConversation="$selectedConversation" :query="$query">
    </div>

    <!-- Chat Box (Selalu tampil di layar besar, atau jika chat list tertutup di layar kecil) -->
    <div
        x-show="!showChatList || window.innerWidth >= 1024"
        x-transition:enter="transform transition ease-out duration-300"
        x-transition:enter-start="translate-x-full opacity-0"
        x-transition:enter-end="translate-x-0 opacity-100"
        x-transition:leave="transform transition ease-in duration-300"
        x-transition:leave-start="translate-x-0 opacity-100"
        x-transition:leave-end="translate-x-full opacity-0"
        class="w-full h-full flex flex-col border-l border-gray-300 overflow-auto flex-1  lg:flex">
        <livewire:chat.chat-box :selectedConversation="$selectedConversation">
    </div>

    <!-- Tombol untuk Menampilkan Chat List (Hanya di layar kecil) -->
    <button @click="showChatList = true" x-show="!showChatList && window.innerWidth < 1024" x-transition class="absolute top-4 left-4 md:hidden bg-gray-200 p-2 rounded-full shadow">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
        </svg>
    </button>

    <!-- Tombol untuk Kembali ke Chatbox (Hanya di layar kecil) -->
    <button @click="showChatList = false" x-show="showChatList && window.innerWidth < 1024" x-transition class="absolute top-4 right-4 md:hidden bg-gray-200 p-2 rounded-full shadow">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.5 21 12m0 0-6-7.5M21 12H3" />
        </svg>
    </button>

</div>