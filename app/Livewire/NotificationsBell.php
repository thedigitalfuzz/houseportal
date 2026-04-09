<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use App\Helpers\NotificationHelper;

class NotificationsBell extends Component
{
    public $notifications = [];
    public $isOpen = false;

    protected $listeners = [
        'refreshNotifications' => 'loadNotifications',
    ];

    public function mount()
    {
        $user = $this->currentUser();
        $this->notifications = \App\Models\Notification::where('notifiable_id', $user->id)
            ->where('notifiable_type', $user instanceof \App\Models\User ? 'App\Models\User' : 'App\Models\Staff')
            ->where('is_read', '!=', 2)
            ->orderByDesc('created_at')
            ->get();
    }

    // Use this to support both admin and staff
    protected function currentUser()
    {
        return Auth::guard('web')->user() ?? Auth::guard('staff')->user();
    }

    public function loadNotifications()
    {
        $user = $this->currentUser();
        if (!$user) return;

        $this->notifications = Notification::where('notifiable_id', $user->id)
            ->where('notifiable_type', get_class($user))
            ->where('is_read', '!=', 2)
            ->orderByDesc('created_at')
            ->get();
    }

    public function toggleDropdown()
    {
        $this->isOpen = !$this->isOpen;
        if ($this->isOpen) {
            $this->markAllAsRead();
        }
    }

    public function markAllAsRead()
    {
        $user = $this->currentUser();
        if (!$user) return;

        Notification::where('notifiable_id', $user->id)
            ->where('notifiable_type', get_class($user))
            ->where('is_read', 0)
            ->update(['is_read' => 1]);

        $this->loadNotifications();
    }

    public function notifyWalletAdded($message)
    {
        $this->createNotification($message);
    }

    public function notifyWalletDisabled($message)
    {
        $this->createNotification($message);
    }

    public function notifyWalletActive($message)
    {
        $this->createNotification($message);
    }

    protected function createNotification($message)
    {
        $user = $this->currentUser();
        if (!$user) return;

        Notification::create([
            'type' => 'wallet',
            'message' => $message,
            'notifiable_type' => get_class($user),
            'notifiable_id' => $user->id,
            'is_read' => 0,
        ]);

        $this->loadNotifications();
    }

    public function markAsRead($id)
    {
        $user = $this->currentUser();

        $notification = Notification::where('id', $id)
            ->where('notifiable_id', $user->id)
            ->where('notifiable_type', get_class($user))
            ->first();

        if (!$notification) return;

        $notification->update(['is_read' => 1]);

        // 🔥 dispatch redirect to browser
        if ($notification->redirect_to) {
            $this->dispatch('redirect-to', [
                'url' => $notification->redirect_to
            ]);
        }

        $this->loadNotifications();
    }
    public function deleteNotification($id)
    {
       // $notification = Notification::find($id);
        $user = $this->currentUser();

        $notification = Notification::where('id', $id)
            ->where('notifiable_id', $user->id)
            ->where('notifiable_type', get_class($user))
            ->first();

        if ($notification) {
            // Only mark deleted for THIS user (not remove from DB)
            $notification->update(['is_read' => 2]); // 2 = deleted (your custom state)
        }

        $this->dispatch('notification-deleted', [
            'message' => 'Notification deleted'
        ]);

        $this->loadNotifications();
    }
    public function render()
    {
        return view('livewire.notifications-bell');
    }
}
