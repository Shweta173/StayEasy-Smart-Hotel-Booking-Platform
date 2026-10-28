<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BookingHold;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ExpireBookingHolds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:expire-holds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire booking holds that have passed their expiration time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredHolds = BookingHold::where('status', 'pending')
            ->where('expires_at', '<=', Carbon::now())
            ->get();

        $count = $expiredHolds->count();

        if ($count > 0) {
            foreach ($expiredHolds as $hold) {
                $hold->update(['status' => 'expired']);
                
                Log::info('Booking hold expired', [
                    'hold_id' => $hold->id,
                    'room_type_id' => $hold->room_type_id,
                    'guest_id' => $hold->guest_id,
                    'check_in' => $hold->check_in,
                    'check_out' => $hold->check_out,
                ]);
            }

            $this->info("Expired {$count} booking hold(s)");
        } else {
            $this->info("No expired booking holds found");
        }

        return Command::SUCCESS;
    }
}
