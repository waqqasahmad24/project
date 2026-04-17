<?php

namespace App\Jobs;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\BookingStatusUpdated;

class SendBookingNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Booking $booking,
        public string $action
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // 1. Send Email Notification
        Mail::to($this->booking->user->email)->send(new BookingStatusUpdated($this->booking, $this->action));

        // 2. Mock SMS Notification (Log)
        Log::info("SMS Mock sent to user ID {$this->booking->user_id}: Your booking (ID: {$this->booking->id}) status has been updated to '{$this->action}'.");
    }
}
