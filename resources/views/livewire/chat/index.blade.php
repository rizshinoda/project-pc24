<div class="fixed h-full flex bg-white border lg:shadow-sm overflow-hidden inset-0 lg:top-5 lg:inset-x-2 m-auto lg:h-[90%] rounded-t-lg">

    <!-- Chat List -->
    <div class="relative w-full md:w-[420px] xl:w-[450px] overflow-y-auto shrink-0 h-full border border-gray-300">
        <livewire:chat.chat-list>
    </div>

    <!-- Chat Box -->
    <div class="hidden md:grid w-full border-l border-gray-300 h-full relative overflow-y-auto" style="contain:content">
        <div class="m-auto text-center justify-center flex flex-col gap-3">
            <h4 class="font-medium text-lg">Choose a conversation to start chatting</h4>
        </div>
    </div>

</div>