<div class="p-4">

    <!-- ALERT -->
    @if($alertMessage)
        <div class="bg-green-500 text-white p-2 mb-3 rounded">
            {{ $alertMessage }}
        </div>
    @endif

    <!-- CREATE CHANNEL -->
    <div class="mb-4">
        <h3 class="font-bold mb-2">Create Channel</h3>
        <div class="flex gap-2">
            <input type="text" wire:model="newChannelName" placeholder="Channel name"
                   class="border p-1 w-full">
            <button wire:click="createChannel"
                    class="bg-green-600 text-white px-4 py-1 rounded">
                Create
            </button>
        </div>
    </div>

    <!-- ASSIGN STAFF -->
    <div class="mb-6">
        <h3 class="font-bold mb-2">Assign Staff to Channel</h3>

        <div class="flex gap-2">
            <!-- Staff Dropdown -->
            <select wire:model="selectedStaffId" class="border p-1 w-full">
                <option value="">Select Staff</option>
                @foreach($staffs as $staff)
                    <option value="{{ $staff->id }}">{{ $staff->staff_name }}</option>
                @endforeach
            </select>

            <!-- Channel Dropdown -->
            <select wire:model="selectedChannelId" class="border p-1 w-full">
                <option value="">Select Channel</option>
                @foreach($channels as $channel)
                    <option value="{{ $channel->id }}">{{ $channel->name }}</option>
                @endforeach
            </select>

            <button wire:click="assignStaffToChannel"
                    class="bg-blue-600 text-white px-4 py-1 rounded">
                Save
            </button>
        </div>
    </div>

    <!-- CHANNEL LIST -->
    <div>
        <h3 class="font-bold mb-2">Channels</h3>

        <ul>
            @foreach($channels as $channel)
                <li class="border p-2 mb-2">
                    <div class="flex justify-between items-center">
                        <span># {{ $channel->name }}</span>

                        <div class="flex gap-2">
                            <button wire:click="loadChannelStaff({{ $channel->id }})"
                                    class="bg-gray-300 px-2 py-1 rounded">
                                View/Edit
                            </button>

                            <button wire:click="deleteChannel({{ $channel->id }})"
                                    class="text-red-600">
                                🗑
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

                                    <button wire:click="removeStaff({{ $staff->id }})"
                                            class="text-red-600">
                                        🗑 Remove
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
