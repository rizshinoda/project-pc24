<div
    x-data="{type:'all',query:@entangle('query')}"
    x-init="
setTimeout(()=>{
conversationElement = document.getElementById('conversation-'+query);

//scroll to the element
if(conversationElement)
{
conversationElement.scrollIntoView({'behavior':''smooth'});
}
}
),200;
   Echo.private('users.{{ Auth()->User()->id }}')
  .notification((notification) => {
  
            if (notification['type'] === 'App\\Notifications\\MessageRead'||notification['type'] === 'App\\Notifications\\MessageSent') 
            {
            Livewire.emit('refresh');
            }
            
        
        });
">

    <header class="px-3 z-10 bg-white sticky top-0 w-full py-2">
        <div class="border-b border-gray-300 flex justify-between items-center pb-2">
            <div class="flex items-center gap-2">
                <h3 class="font-extrabold text-2xl">Chats</h3>
            </div>
            <button>
                <svg class="w-7 h-7" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M6 10.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5" />
                </svg>
            </button>
        </div>


    </header>


    <main class="overflow-visible grow h-full relative">
        <ul class="p-2 grid w-full space-y-2">
            @if ($conversations)
            @foreach ($conversations as $conversation)
            @php
            // Menentukan route chat berdasarkan role
            $chatRoutes = [
            1 => 'admin.chat',
            2 => 'ga.chat',
            3 => 'helpdesk.chat',
            4 => 'noc.chat',
            5 => 'psb.chat',
            6 => 'na.chat',
            ];

            $userRole = Auth::user()->is_role;
            $chatRoute = isset($chatRoutes[$userRole]) ? route($chatRoutes[$userRole], $conversation->id) : '#';
            @endphp

            <li id="conversation-{{$conversation->id}}" wire:key="{{$conversation->id}}"
                class="py-1 hover:bg-blue-50 rounded-2xl transition-colors duration-150 flex gap-4 relative w-full cursor-pointer px-2 {{$conversation->id == $selectedConversation?->id ? 'bg-gray-100/70':''}}">

                <a href="#" class="shrink-0">
                    <x-avatar class="mt-2" src="{{asset('/dist/assets/images/faces/2.png')}}"></x-avatar>
                </a>

                <aside class="grid grid-cols-10 w-full items-center">
                    <a href="{{ $chatRoute }}"
                        class="col-span-9 border-b pb-2 border-gray-300 relative overflow-hidden truncate leading-5 w-full flex-nowrap p-1 !text-gray-900 no-underline text-decoration-none hover:text-gray-700">
                        <div class="w-full flex flex-col gap-0">
                            <div class="w-full flex justify-between items-center">
                                <h6 class="truncate font-medium tracking-wider text-gray-700">
                                    {{$conversation->getReceiver()->name}}
                                </h6>
                                <small class="text-gray-700">
                                    {{$conversation->messages?->last()?->created_at?->shortAbsoluteDiffForHumans()}}
                                </small>
                            </div>
                            <div class="flex gap-x-2 items-center -mt-1">

                                @if ($conversation->messages?->last()?->sender_id == auth()->id())
                                @if ($conversation->isLastMessageReadByUser())
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2-all" viewBox="0 0 16 16">
                                        <path d="M12.354 4.354a.5.5 0 0 0-.708-.708L5 10.293 1.854 7.146a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0zm-4.208 7-.896-.897.707-.707.543.543 6.646-6.647a.5.5 0 0 1 .708.708l-7 7a.5.5 0 0 1-.708 0" />
                                        <path d="m5.354 7.146.896.897-.707.707-.897-.896a.5.5 0 1 1 .708-.708" />
                                    </svg>
                                </span>
                                @else
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2" viewBox="0 0 16 16">
                                        <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0" />
                                    </svg>
                                </span>
                                @endif
                                @endif

                                <p class="grow truncate text-sm font-[100] leading-tight -mt-1 flex items-center gap-1">
                                    @if ($conversation->messages?->last()?->images->isNotEmpty())
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-image" viewBox="0 0 16 16">
                                        <path d="M13.5 2a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1h-11a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1h11zm0 1h-11v10h11V3z" />
                                        <path d="m4.5 8.5 3-3 2.5 2.5 3.5-3.5V12h-11V8.5z" />
                                    </svg>
                                    <span>Image</span>
                                    @else
                                    @if ($conversation->messages?->last()?->body)
                                    {{ safeDecrypt($conversation->messages?->last()?->body) }}
                                    @else
                                    <span class="text-gray-400 italic">No message</span>
                                    @endif
                                    @endif
                                </p>

                                @if ($conversation->unreadMessagesCount() > 0)
                                <span class="font-bold p-px px-2 text-xs shrink-0 rounded-full bg-blue-500 text-white">
                                    {{$conversation->unreadMessagesCount()}}
                                </span>
                                @endif
                            </div>
                        </div>

                    </a>

                    <!-- Dropdown -->
                    <div class="col-span-1 flex flex-col text-center my-auto">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots-vertical w-5 h-5 text-gray-700" viewBox="0 0 16 16">
                                        <path d="M9.5 13a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0" />
                                    </svg>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <div class="w-full">
                                    <button
                                        onclick="confirm('Are you sure?')||event.stopImmediatePropagation()"
                                        wire:click="deleteByUser('{{encrypt($conversation->id)}}')"
                                        class="items-center gap-3 flex w-full px-4 py-2 text-left text-sm leading-5 text-gray-500 hover:bg-gray-100 transition-all duration-150 ease-in-out focus:outline-none focus:bg-gray-100">
                                        <span>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16">
                                                <path d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5M8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5m3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0" />
                                            </svg>
                                        </span>
                                        Delete
                                    </button>
                                </div>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </aside>
            </li>
            @endforeach
            @endif
        </ul>
    </main>

</div>