<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Notification;
class DeleteOldSystemRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-old-system-records';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date = Carbon::now()->subDays(30);
        $data = Notification::where('type','admin')->where('created_at','<',$date)->delete();
        $this->info('Đã xóa ' . $data . ' bản ghi');
    }
}
