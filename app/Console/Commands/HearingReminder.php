<?php

namespace App\Console\Commands;

use App\Helpers\MailHelper;
use Illuminate\Console\Command;

class HearingReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hearing:reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hearing Reminder - Updacoming Hearing Send Mail';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $MailHelper = new MailHelper();
        $MailHelper->hearingReminder();
        
    }
}
