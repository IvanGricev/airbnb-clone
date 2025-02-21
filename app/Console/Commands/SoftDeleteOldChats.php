<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Message;
use Carbon\Carbon;

class SoftDeleteOldChats extends Command
{
    protected $signature = 'chats:soft-delete-old';

    protected $description = 'Soft delete chats 5 days after the rental period ends.';

    public function handle()
    {
        $this->info('Soft deleting old chats...');

        $dateThreshold = Carbon::now()->subDays(5);

        $messages = Message::whereHas('booking', function ($query) use ($dateThreshold) {
            $query->where('end_date', '<', $dateThreshold);
        })->get();

        foreach ($messages as $message) {
            $message->delete();
        }

        $this->info('Old chats soft deleted successfully.');
    }
}
