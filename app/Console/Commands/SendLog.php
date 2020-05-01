<?php

namespace App\Console\Commands;
use MailHelper;
use Log;

use Illuminate\Console\Command;

class SendLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for send log file everyday on mail';

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
        try {
            $MailHelper = new MailHelper();
            $mail=domainConfig();   
            $up_u['mail']['logo']=$mail->domain.$mail->mail_logo;
            $up_u['mail']['keyword']=$mail->mail_keyword;
            $up_u['mail']['footer']=$mail->mail_footer;
            $sender_name=env('MAIL_NAME');
            $subject='Log File '.$mail->domain;
            $to=['admin@lawrbit.com','nilesh.sharma@lawrbit.com','ashok.kumar@lawrbit.com'];
            $from=env('MAIL_EMAIL');
            $date = date('Y-m-d',strtotime("-1 days"));
            $filename = 'laravel-'.$date.'.log';
            $url = storage_path().'/logs/'.$filename;
            if (file_exists($url)){
                $MailHelper->mailsendwithattachment('emails.sendlog',$up_u,$sender_name,$subject,$to,$from,'5',$url);  
            }
        }catch(\Exception $e){
            Log::error('SendLog-handle Command: '.$e->getMessage());   // making log in file
            return view('error.home');  
        }
    }
}
