<div class="p-4">

    <!-- ALERT -->
    @if($alertMessage)
        <div class="bg-green-500 text-white p-2 mb-3 rounded flex justify-between items-center">
            <span>{{ $alertMessage }}</span>

            <button wire:click="$set('alertMessage', null)">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endif
<div class="bg-white p-4 shadow">


    <!-- CREATE CHANNEL -->
    <div class="mb-4">
        <h3 class="font-bold mb-2">Create Channel</h3>
        <div class="flex gap-2">
            <input type="text" wire:model="newChannelName" placeholder="Channel name"
                   class="border p-1 w-full rounded">
            <button wire:click="createChannel"
                    class="bg-green-600 text-white px-4 py-1 rounded hover:bg-green-800 transition-all">
                Create
            </button>
        </div>
    </div>

    <!-- ASSIGN STAFF -->
    <div class="mb-6">
        <h3 class="font-bold mb-2">Assign Staff to Channel</h3>

        <div class="flex gap-2 flex-col md:flex-row">
            <!-- Staff Dropdown -->
            <select wire:model="selectedStaffId" class="border p-1 w-full rounded">
                <option value="">Select Staff</option>
                @foreach($staffs as $staff)
                    <option value="{{ $staff->id }}">{{ $staff->staff_name }}</option>
                @endforeach
            </select>

            <!-- Channel Dropdown -->
            <select wire:model="selectedChannelId" class="border p-1 w-full rounded">
                <option value="">Select Channel</option>
                @foreach($channels as $channel)
                    <option value="{{ $channel->id }}">{{ $channel->name }}</option>
                @endforeach
            </select>

            <button wire:click="assignStaffToChannel"
                    class="bg-blue-600 text-white px-4 py-1 rounded hover:bg-blue-800 transition-all">
                Save
            </button>
        </div>
    </div>

    <!-- CHANNEL LIST -->
    <div>
        <h3 class="font-bold mb-2">Channels</h3>

        <ul>
            @foreach($channels as $channel)
                <li class="border p-2 mb-2" wire:key="channel-{{ $channel->id }}-open-{{ $viewChannelId === $channel->id ? '1' : '0' }}">
                    <div class="flex justify-between items-center">
                        <span># {{ $channel->name }}</span>

                        <div class="flex gap-2">
                            <button wire:click="toggleChannelStaff({{ $channel->id }})"
                                    class="bg-gray-300 px-2 py-1 rounded hover:bg-gray-700 hover:text-white transition-all">
                                {{ $this->isChannelOpen($channel->id) ? 'Hide' : 'View/Edit' }}
                            </button>

                            <button wire:click="confirmDeleteChannel({{ $channel->id }})"
                                    class="bg-red-600 text-white px-1 py-0.5 shrink-0 rounded hover:bg-red-800 transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash2-icon lucide-trash-2"><path d="M10 11v6"/><path d="M14 11v6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- STAFF LIST DROPDOWN -->
                    @if($viewChannelId == $channel->id)
                        <div class="mt-2 border-t pt-2">
                            <strong>Assigned Staff:</strong>

                            @foreach($channelStaffList as $staff)
                                <div class="flex justify-between items-center border p-1 mt-1">
                                    <span>{{ $staff->staff_name }}</span>

                                    <button wire:click="confirmRemoveStaff({{ $staff->id }})"
                                            class="bg-red-600 text-white px-1 py-0.5 shrink-0 rounded hover:bg-red-800 transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash2-icon lucide-trash-2"><path d="M10 11v6"/><path d="M14 11v6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                </div>
                            @endforeach

                            @if(count($channelStaffList) == 0)
                                <div class="text-gray-500">No staff assigned</div>
                            @endif
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
</div>
    @if($deleteModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded shadow p-6 w-80 text-center">
                <h2 class="text-lg font-semibold mb-4">Confirm Delete</h2>

                <p>
                    @if($deleteType === 'channel')
                        Are you sure you want to delete this channel?
                    @else
                        Remove this staff from channel?
                    @endif
                </p>

                <div class="mt-4 flex justify-center gap-2">
                    <button wire:click="$set('deleteModal', false)" class="px-4 py-2 border rounded">
                        Cancel
                    </button>

                    <button wire:click="deleteConfirmed" class="px-4 py-2 bg-red-600 text-white rounded">
                        Yes, Delete
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
