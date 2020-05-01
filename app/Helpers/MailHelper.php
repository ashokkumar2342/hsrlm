<?php
namespace App\Helpers;
use App\Jobs\SendEmail;
use App\Jobs\SendEmailAttach;
use App\Modules\Litigation\Models\Cases;
use App\Modules\Litigation\Models\Company;
use App\Modules\Litigation\Models\Hearing;
use App\Modules\Litigation\Models\LawyerMaster;
use App\Modules\Litigation\Models\Notice;
use App\Modules\Litigation\Models\Opponents;
use App\Modules\Login\Models\AppUsers;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MailHelper 
{
	

	/*public function mailsend($template,$data,$sender_name,$subject,$to,$from){
		Mail::to($template, $data, function ($message) use($sender_name,$subject,$to,$from) {
			$message->from($from, $sender_name);
			$message->subject($subject);
			$message->to($to);
		})->send(new SendEmailMailable());
	}*/

	public function mailsend($template,$data_mail,$sender_name,$subject,$to,$from,$delaytime){
		try{

			$array=array();
			$array['template']=$template;
			$array['data_mail']=$data_mail;
			$array['sender_name']=$sender_name;
			$array['subject']=$subject;
			$array['to']=$to;
			$array['from']=$from;

			$job=(new SendEmail($array))->delay(now()->addSeconds($delaytime));
			dispatch($job); 
		}catch(\Exception $e){
            Log::error('MailHelper-mailsend: '.$e->getMessage());        // making log in file
            return view('error.home');
        }

    }

    public function mailsendwithattachment($template,$data_mail,$sender_name,$subject,$to,$from,$delaytime,$url){
    	try{

    		$array=array();
    		$array['template']=$template;
    		$array['data_mail']=$data_mail;
    		$array['sender_name']=$sender_name;
    		$array['subject']=$subject;
    		$array['to']=$to;
    		$array['from']=$from;
    		$array['attach']=$url;

    		$job=(new SendEmailAttach($array))->delay(now()->addSeconds($delaytime));
    		dispatch($job); 
    	}catch(\Exception $e){
            Log::error('MailHelper-mailsendwithattachment: '.$e->getMessage());        // making log in file
            return view('error.home');
        }

    }

    public function activationmail($user_id){
    	try{
    		$AppUsers=new AppUsers();
    		$up_u=array();
    		$up_u['token'] = str_random(64);
    		$up_u['otp'] = mt_rand(100000,999999);
    		$AppUsers->updateuserdetail($up_u,$user_id);
    		$u_detail=$AppUsers->getdetailbyuserid($user_id);
    		$up_u['name']=$u_detail->name;
    		$user=$u_detail->email;
    		$up_u['otp']=$u_detail->otp;
    		$mail=domainConfig();
    		$up_u['mail']['logo']=$mail->domain.$mail->mail_logo;
    		$up_u['mail']['keyword']=$mail->mail_keyword;
    		$up_u['mail']['footer']=$mail->mail_footer;
    		$up_u['link']=$mail->domain."passwordreset/".$u_detail->token;


    		$this->mailsend('emails.userActivation',$up_u,'No-Reply','Account Activation',$user,env('MAIL_EMAIL'),20);
    	}catch(\Exception $e){
            Log::error('MailHelper-activationmail: '.$e->getMessage());        // making log in file
            return view('error.home');
        }
    } 

    public function forgetmail($email){
    	try{
    		$AppUsers=new AppUsers();
    		$u_detail=$AppUsers->getdetailbyemail($email);
    		$up_u=array();
    		$up_u['token'] = str_random(64);
    		$up_u['otp'] = mt_rand(100000,999999);
    		$AppUsers->updateuserdetail($up_u,$u_detail->user_id);
    		
    		
    		$up_u['name']=$u_detail->name;
    		$up_u['email']=$u_detail->email;
    		$user=$u_detail->email;
    		$up_u['otp']=$up_u['otp'];
    		$mail=domainConfig();
    		$up_u['mail']['logo']=$mail->domain.$mail->mail_logo;
    		$up_u['mail']['keyword']=$mail->mail_keyword;
    		$up_u['mail']['footer']=$mail->mail_footer;
    		$up_u['link']=$mail->domain."passwordreset/".$up_u['token'];


    		$this->mailsend('emails.forgotPassword',$up_u,'No-Reply','Forget Password',$user,env('MAIL_EMAIL'),20);
    	}catch(\Exception $e){
            Log::error('MailHelper-forgetmail: '.$e->getMessage());        // making log in file
            return view('error.home');
        }
    }

    public function resetnotification($user_id){
    	try{
    		$AppUsers=new AppUsers();
    		$u_detail=$AppUsers->getdetailbyuserid($user_id);

    		$up_u=array();
    		$up_u['name']=$u_detail->name;
    		$mail=domainConfig();
    		$up_u['mail']['logo']=$mail->domain.$mail->mail_logo;
    		$up_u['mail']['keyword']=$mail->mail_keyword;
    		$up_u['mail']['footer']=$mail->mail_footer;
    		$user=$u_detail->email;
    		$this->mailsend('emails.passwordChanged',$up_u,'No-Reply','Password Reset',$user,env('MAIL_EMAIL'),20);
    	}catch(\Exception $e){
            Log::error('MailHelper-resetnotification: '.$e->getMessage());        // making log in file
            return view('error.home');
        }
    }

    

    public function securedevicealways($id){
    	try{
    		$AppUsers=new AppUsers();
    		$u_detail=$AppUsers->getdetailbyuserid($id);
    		
    		$up_u=array();

    		if($u_detail->device_token!='')
    		{
    			$up_u['device_token'] = $u_detail->device_token;
    			$AppUsers->updateuserdetail($up_u,$u_detail->user_id);
    		}
    		else
    		{
    			$up_u['device_token'] = mt_rand(100000,999999);
    			$AppUsers->updateuserdetail($up_u,$u_detail->user_id);
    		}
    		
    		
    		$up_u['name']=$u_detail->name;
    		$up_u['email']=$u_detail->email;
    		$user=$u_detail->email;
    		$up_u['otp']=$up_u['device_token'];
    		$mail=domainConfig();
    		$up_u['mail']['logo']=$mail->domain.$mail->mail_logo;
    		$up_u['mail']['keyword']=$mail->mail_keyword;
    		$up_u['mail']['footer']=$mail->mail_footer;

    		$detail=$AppUsers->getdetailbyuserid($id);
    		$Attempt_array=array();
    		$Attempt_array['mfa_attempt']=isset($detail['mfa_attempt']) && $detail['mfa_attempt']>0 ? $detail['mfa_attempt']+1:1;
    		$AppUsers->updateuserdetail($Attempt_array,$id);

    		$this->mailsend('emails.securedevicealways',$up_u,'No-Reply','New Device',$user,env('MAIL_EMAIL'),5);
    	}catch(\Exception $e){
            Log::error('MailHelper-securedevicealways: '.$e->getMessage());        // making log in file
            return view('error.home');
        }
    }

    public function securedevice($id,$array){
    	try{
    		$AppUsers=new AppUsers();
    		$u_detail=$AppUsers->getdetailbyuserid($id);
    		
    		$up_u=array();

    		if($u_detail->device_token!='')
    		{
    			$up_u['device_token'] = $u_detail->device_token;
    			$AppUsers->updateuserdetail($up_u,$u_detail->user_id);
    		}
    		else
    		{
    			$up_u['device_token'] = mt_rand(100000,999999);
    			$AppUsers->updateuserdetail($up_u,$u_detail->user_id);
    		}
    		
    		
    		$up_u['name']=$u_detail->name;
    		$up_u['browser']=$array['browser_name'];
    		$up_u['os']=$array['platform_name'];
    		$up_u['email']=$u_detail->email;
    		$user=$u_detail->email;
    		$up_u['otp']=$up_u['device_token'];
    		$mail=domainConfig();
    		$up_u['mail']['logo']=$mail->domain.$mail->mail_logo;
    		$up_u['mail']['keyword']=$mail->mail_keyword;
    		$up_u['mail']['footer']=$mail->mail_footer;

    		$detail=$AppUsers->getdetailbyuserid($id);
    		$Attempt_array=array();
    		$Attempt_array['mfa_attempt']=isset($detail['mfa_attempt']) && $detail['mfa_attempt']>0 ? $detail['mfa_attempt']+1:1;
    		$AppUsers->updateuserdetail($Attempt_array,$id);

    		$this->mailsend('emails.securedevice',$up_u,'No-Reply','New Device',$user,env('MAIL_EMAIL'),5);
    	}catch(\Exception $e){
            Log::error('MailHelper-securedevice: '.$e->getMessage());        // making log in file
            return view('error.home');
        }
    }

    public function noticetrackeradd($arr){
        try{
             $up_u=array();
             $up_u['entity_name'] = $arr['entity_name'];
             $up_u['location_name'] = $arr['location_name'];
             $up_u['date'] = $arr['date']; 
             $mail=domainConfig();
             $up_u['mail']['logo']=$mail->domain.$mail->mail_logo;
             $up_u['mail']['keyword']=$mail->mail_keyword;
             $up_u['mail']['footer']=$mail->mail_footer; 
               $up_u['support_name'] = $arr['support_name'];
            foreach ($arr['users'] as $user) { 
               $up_u['name'] = $user->name; 
               $this->mailsend('emails.noticeTrackerAdd',$up_u,'No-Reply','New Notice added for your review and action',$user->email,env('MAIL_EMAIL'),20);  
            }
            
        }catch(\Exception $e){
            Log::error('MailHelper-noticetrackeradd: '.$e->getMessage());        // making log in file
            return view('error.home');
        }
    }

    public function noticeadd($arr){
        try{
             $up_u=array();
            $legalTeams = explode(',', $arr['legal_team_id']);
            $lawyers = explode(',', $arr['lawyers']);
             $generalHelper =new generalHelper();
             $location_name= !empty($generalHelper->getReletionFieldData('location_id',$arr['location_id']))?$generalHelper->getReletionFieldData('location_id',$arr['location_id']):'-' ;
             $party_type= !empty($generalHelper->getReletionFieldData('notice_category_id',$arr['notice_category_id']))?$generalHelper->getReletionFieldData('notice_category_id',$arr['notice_category_id']):'-' ;
             $notice_type= !empty($generalHelper->getReletionFieldData('notice_type_id',$arr['notice_type_id']))?$generalHelper->getReletionFieldData('notice_type_id',$arr['notice_type_id']):'-' ;
             $owner= !empty($generalHelper->getReletionFieldData('owner_id',$arr['owner_id']))?$generalHelper->getReletionFieldData('owner_id',$arr['owner_id']):'-' ;

            $up_u['title'] = $arr['title'];    
            $Opponent =new Opponents();
            $Opponents=$Opponent->getOpponentByRefIdOrLegalId($arr['notice_id'],2,1)->pluck('name')->toArray();  
            $Company = new Company(); 
            $up_u['entity_name'] = $Company->getDetail($arr['company_id'])->name; 

            $user = new AppUsers(); 
            $legal_team_except_id =remove_element($legalTeams,$arr['owner_id']);
            $user_name = $user->getUserByArrId($legal_team_except_id)->pluck('name')->toArray();
            $up_u['legal_team'] = implode(',', $user_name);    

            $up_u['party_name'] =implode(',', $Opponents);
             $up_u['notice_type'] = $notice_type;
            $up_u['notice_date'] = $arr['notice_date'];
            $up_u['party_type'] = $party_type;
             
            $up_u['received_at'] =$location_name;
            $up_u['owner'] =$owner;
    
             $mail=domainConfig();
             $up_u['mail']['logo']=$mail->domain.$mail->mail_logo;
             $up_u['mail']['keyword']=$mail->mail_keyword;
             $up_u['mail']['footer']=$mail->mail_footer; 
           
            foreach ($legal_team_except_id as $legal_team_id) { 
                $AppUsers =new AppUsers();
                $user= $AppUsers->getUserDataById($legal_team_id);
                $up_u['name'] = $user->name; 
                $this->mailsend('emails.noticeAdd',$up_u,'No-Reply','Notice Adminstration Assigned',$user->email,env('MAIL_EMAIL'),20);  
            }
            foreach ($lawyers as $lawyer_id) {
                $LawyerMaster =new LawyerMaster();
                $user= $LawyerMaster->getListById($lawyer_id);
                $up_u['name'] = $user->name; 
                $this->mailsend('emails.noticeAdd',$up_u,'No-Reply','Notice Adminstration Assigned',$user->email,env('MAIL_EMAIL'),20); 
            }
            
        }catch(\Exception $e){
            Log::error('MailHelper-noticeadd: '.$e->getMessage());        // making log in file
            return view('error.home');
        }
    }

    public function nextAction($arr){
        try{
             $up_u=array(); 
             $generalHelper =new generalHelper();
             $Cases =new Cases();    
             $category= !empty($generalHelper->getReletionFieldData('next_action_category_id',$arr['category_id']))?$generalHelper->getReletionFieldData('next_action_category_id',$arr['category_id']):'-' ;

             if ($arr['legal_type_id']==2) {
               $notice =new Notice();   
               $notices=$notice->getNoticeById($arr['reference_id']);   
                $up_u['title'] = $notices->title;
                $up_u['ref_no'] = null;
                $up_u['legal_name'] ='Notice';
                $owner_id =$notices->owner_id;
                $owner= !empty($generalHelper->getReletionFieldData('owner_id',$notices->owner_id))?$generalHelper->getReletionFieldData('owner_id',$notices->owner_id):'-' ;
             }elseif ($arr['legal_type_id']==1) {
               
               $caseDetails=$Cases->getCaseById($arr['reference_id']);   
                $up_u['title'] = $caseDetails->title;
                $up_u['ref_no'] =$caseDetails->case_no;
                $up_u['legal_name'] ='Case';  
               
                $owner_id =$caseDetails->owner_id;
                $owner= !empty($generalHelper->getReletionFieldData('owner_id',$caseDetails->owner_id))?$generalHelper->getReletionFieldData('owner_id',$caseDetails->owner_id):'-' ;
                $court_name= !empty($generalHelper->getReletionFieldData('court_id',$caseDetails->court_id))?$generalHelper->getReletionFieldData('court_id',$caseDetails->court_id):'-' ;
                $up_u['court_name'] =$court_name; 
             }elseif ($arr['legal_type_id']==4) {
                $Hearing =new Hearing();   
                $Hearings=$Hearing->getHearingById($arr['reference_id']);   
                $caseDetails=$Cases->getCaseById($Hearings->case_id);  
                 $up_u['title'] = $Hearings->title;
                 
                 $up_u['legal_name'] ='Hearing';
                 $owner_id =$caseDetails->owner_id;
                $owner= !empty($generalHelper->getReletionFieldData('owner_id',$caseDetails->owner_id))?$generalHelper->getReletionFieldData('owner_id',$caseDetails->owner_id):'-' ;
                 $court_name= !empty($generalHelper->getReletionFieldData('court_id',$caseDetails->court_id))?$generalHelper->getReletionFieldData('court_id',$caseDetails->court_id):'-' ;
                 $up_u['ref_no'] =$caseDetails->case_no; 
                 $up_u['court_name'] =$court_name; 

             } 
            $up_u['category'] = $category;              
            $up_u['target_date'] =$arr['target_date'];         
    
             $mail=domainConfig();
             $up_u['mail']['logo']=$mail->domain.$mail->mail_logo;
             $up_u['mail']['keyword']=$mail->mail_keyword;
             $up_u['mail']['footer']=$mail->mail_footer;  
            $AppUsers =new AppUsers();
            $user= $AppUsers->getUserDataById($arr['person_responsible']);
            $up_u['name'] = $user->name; 
            $up_u['owner'] =$owner;    
            if ($owner_id != $arr['person_responsible']) {
             $this->mailsend('emails.nextActionAdd',$up_u,'No-Reply',$up_u['legal_name'].' Task Assigned to you',$user->email,env('MAIL_EMAIL'),20);  
             notificationCenter($user->user_id,$owner_id,3,$arr['reference_id'],'has assigned Category "'.$category.'" task to you under Ref id "'.$arr['reference_id'].'" , titled "'.$up_u['title'].'" ',$arr['legal_type_id']);  
            }        
            
        }catch(\Exception $e){
            Log::error('MailHelper-nextAction: '.$e->getMessage());        // making log in file
            return view('error.home');
        }
    }

    public function nextActionTaskCompleted($arr,$data){
        try{ 
             $up_u=array(); 
             $generalHelper =new generalHelper();
             $Cases =new Cases(); 
             $category= !empty($generalHelper->getReletionFieldData('next_action_category_id',$data->category_id))?$generalHelper->getReletionFieldData('next_action_category_id',$data->category_id):'-' ;

             if ($data->legal_type_id==2) {
               $notice =new Notice();   
               $notices=$notice->getNoticeById($data->reference_id);   
                $up_u['title'] = $notices->title;
                $up_u['ref_no'] = null;
                $up_u['legal_name'] ='Notice';
                $owner_id =$notices->owner_id;
                $owner= !empty($generalHelper->getReletionFieldData('owner_id',$notices->owner_id))?$generalHelper->getReletionFieldData('owner_id',$notices->owner_id):'-' ;
             }elseif ($data->legal_type_id==1) {
               
               $caseDetails=$Cases->getCaseById($data->reference_id);   
                $up_u['title'] = $caseDetails->title;
                $up_u['ref_no'] =$caseDetails->case_no;
                $up_u['legal_name'] ='Case';  
               
                $owner_id =$caseDetails->owner_id;
                $owner= !empty($generalHelper->getReletionFieldData('owner_id',$caseDetails->owner_id))?$generalHelper->getReletionFieldData('owner_id',$caseDetails->owner_id):'-' ;
                $court_name= !empty($generalHelper->getReletionFieldData('court_id',$caseDetails->court_id))?$generalHelper->getReletionFieldData('court_id',$caseDetails->court_id):'-' ;
                $up_u['court_name'] =$court_name; 
             }elseif ($data->legal_type_id==4) {
                $Hearing =new Hearing();   
                $Hearings=$Hearing->getHearingById($data->reference_id);   
                $caseDetails=$Cases->getCaseById($Hearings->case_id);  
                 $up_u['title'] = $Hearings->title;
                 
                 $up_u['legal_name'] ='Hearing';
                 $owner_id =$caseDetails->owner_id;
                $owner= !empty($generalHelper->getReletionFieldData('owner_id',$caseDetails->owner_id))?$generalHelper->getReletionFieldData('owner_id',$caseDetails->owner_id):'-' ;
                 $court_name= !empty($generalHelper->getReletionFieldData('court_id',$caseDetails->court_id))?$generalHelper->getReletionFieldData('court_id',$caseDetails->court_id):'-' ;
                 $up_u['ref_no'] =$caseDetails->case_no; 
                 $up_u['court_name'] =$court_name; 

             } 
            $up_u['category'] = $category;              
            $up_u['completion_date'] =$arr['completion_date'];         
    
             $mail=domainConfig();
             $up_u['mail']['logo']=$mail->domain.$mail->mail_logo;
             $up_u['mail']['keyword']=$mail->mail_keyword;
             $up_u['mail']['footer']=$mail->mail_footer;  
            $AppUsers =new AppUsers();
            $user= $AppUsers->getUserDataById($data->person_responsible);
            $up_u['name'] = $user->name; 
            $up_u['owner'] =$owner;    
            if ($owner_id != $data->person_responsible) { 
             $this->mailsend('emails.nextActionTaskCompleted',$up_u,'No-Reply',$up_u['legal_name'].' Task Completed by the Assignee',$user->email,env('MAIL_EMAIL'),20);    
            }        
            
        }catch(\Exception $e){
            Log::error('MailHelper-nextActionTaskCompleted: '.$e->getMessage());        // making log in file
            return view('error.home');
        }
    }

    public function noticeStatusChange($notices){
        try{ 
             $up_u=array(); 
             $generalHelper =new generalHelper(); 
             $notice =new Notice();   
             
             $up_u['title'] = $notices->title; 
             $up_u['notice_status'] = $notices->getStatus->name or ''; 
             $owner_id =$notices->owner_id;
             $owner= !empty($generalHelper->getReletionFieldData('owner_id',$notices->owner_id))?$generalHelper->getReletionFieldData('owner_id',$notices->owner_id):'-' ; 

             $mail=domainConfig();
             $up_u['mail']['logo']=$mail->domain.$mail->mail_logo;
             $up_u['mail']['keyword']=$mail->mail_keyword;
             $up_u['mail']['footer']=$mail->mail_footer;  
            
            $up_u['owner'] =$owner;    
            $legalTeams = explode(',', $notices->legal_team_id);
            $lawyers = explode(',', $notices->lawyers); 
            foreach ($legalTeams as $legal_team_id) { 
                $AppUsers =new AppUsers();
                $user= $AppUsers->getUserDataById($legal_team_id);
                $up_u['name'] = $user->name; 
                $this->mailsend('emails.noticeStatusChange',$up_u,'No-Reply','Notice changed in to the "'.$up_u['notice_status'].'"',$user->email,env('MAIL_EMAIL'),20);  
            }
            foreach ($lawyers as $lawyer_id) {
                $LawyerMaster =new LawyerMaster();
                $user= $LawyerMaster->getListById($lawyer_id);
                $up_u['name'] = $user->name; 
                $this->mailsend('emails.noticeStatusChange',$up_u,'No-Reply','Notice changed in to the "'.$up_u['notice_status'].'"',$user->email,env('MAIL_EMAIL'),20); 
            }
                   
            
        }catch(\Exception $e){
            Log::error('MailHelper-noticeStatusChange: '.$e->getMessage());        // making log in file
            return view('error.home');
        }
    }
    public function caseStatusChange($case){
        try{ 
             $up_u=array(); 
             $generalHelper =new generalHelper();  
             $up_u['title'] = $case->title; 
             $up_u['case_status'] = $case->getStatus->name or ''; 
             $owner_id =$case->owner_id;
             $owner= !empty($generalHelper->getReletionFieldData('owner_id',$case->owner_id))?$generalHelper->getReletionFieldData('owner_id',$case->owner_id):'-' ; 

             $mail=domainConfig();
             $up_u['mail']['logo']=$mail->domain.$mail->mail_logo;
             $up_u['mail']['keyword']=$mail->mail_keyword;
             $up_u['mail']['footer']=$mail->mail_footer;  
            
            $up_u['owner'] =$owner;    
            $legalTeams = explode(',', $case->legal_team_id);
            $lawyers = explode(',', $case->lawyers); 
            foreach ($legalTeams as $legal_team_id) { 
                $AppUsers =new AppUsers();
                $user= $AppUsers->getUserDataById($legal_team_id);
                $up_u['name'] = $user->name; 
                $this->mailsend('emails.caseStatusChange',$up_u,'No-Reply','Case - Updated Status in LMMS',$user->email,env('MAIL_EMAIL'),20);  
            }
            foreach ($lawyers as $lawyer_id) {
                $LawyerMaster =new LawyerMaster();
                $user= $LawyerMaster->getListById($lawyer_id);
                $up_u['name'] = $user->name; 
                $this->mailsend('emails.caseStatusChange',$up_u,'No-Reply','Case - Updated Status in LMMS',$user->email,env('MAIL_EMAIL'),20); 
            }
                   
            
        }catch(\Exception $e){
            Log::error('MailHelper-noticeStatusChange: '.$e->getMessage());        // making log in file
            return view('error.home');
        }
    }

    public function caseAdd($arr){
        try{
            $Cases = new Cases();
            $generalHelper =new generalHelper(); 
            $caseDetails=$Cases->getCaseById($arr['case_id']); 
            $court_name= !empty($generalHelper->getReletionFieldData('court_id',$caseDetails->court_id))?$generalHelper->getReletionFieldData('court_id',$caseDetails->court_id):'-' ;
            $up_u=array();
            $legalTeams = explode(',', $arr['legal_team_id']);
            $lawyers = explode(',', $arr['lawyers']);
             
             $owner= !empty($generalHelper->getReletionFieldData('owner_id',$arr['owner_id']))?$generalHelper->getReletionFieldData('owner_id',$arr['owner_id']):'-' ;

            $up_u['title'] = $arr['title'];    
            $Opponent =new Opponents();
            $Opponents=$Opponent->getOpponentByRefIdOrLegalId($arr['case_id'],2,1)->pluck('name')->toArray();  
            $Company = new Company(); 
            $up_u['case_no'] = $caseDetails->case_no; 
            $up_u['entity_name'] = $Company->getDetail($arr['company_id'])->name; 
            $up_u['court_name'] = $court_name; 
            $up_u['status'] = $caseDetails->getStatus->name or ''; 
            $up_u['appearing_field'] = $caseDetails->appearing_field; 
            $up_u['appearing_model_as'] = $caseDetails->appearing_model_as; 

            $user = new AppUsers(); 
            $legal_team_except_id =remove_element($legalTeams,$arr['owner_id']);
            $user_name = $user->getUserByArrId($legal_team_except_id)->pluck('name')->toArray();
            $up_u['legal_team'] = implode(',', $user_name);    

            $up_u['party_name'] =implode(',', $Opponents);
            
            
            $up_u['owner'] =$owner;
    
             $mail=domainConfig();
             $up_u['mail']['logo']=$mail->domain.$mail->mail_logo;
             $up_u['mail']['keyword']=$mail->mail_keyword;
             $up_u['mail']['footer']=$mail->mail_footer; 
           
            foreach ($legal_team_except_id as $legal_team_id) { 
                $AppUsers =new AppUsers();
                $user= $AppUsers->getUserDataById($legal_team_id);
                $up_u['name'] = $user->name; 
                $this->mailsend('emails.caseAdd',$up_u,'No-Reply','New Case Administration Assigned',$user->email,env('MAIL_EMAIL'),20);  
            }
            foreach ($lawyers as $lawyer_id) {
                $LawyerMaster =new LawyerMaster();
                $user= $LawyerMaster->getListById($lawyer_id);
                $up_u['name'] = $user->name; 
                $this->mailsend('emails.caseAdd',$up_u,'No-Reply','New Case Administration Assigned',$user->email,env('MAIL_EMAIL'),20); 
            }
            
        }catch(\Exception $e){
            Log::error('MailHelper-caseAdd: '.$e->getMessage());        // making log in file
            return view('error.home');
        }
    }

    public function hearingStatusChange($hearing_id){
        try{ 
             $up_u=array(); 
             $generalHelper =new generalHelper();  
             $Cases = new Cases();
             $Hearing =new Hearing();   
             $Hearings=$Hearing->getHearingById($hearing_id);   
             $caseDetails=$Cases->getCaseById($Hearings->case_id);  
             $up_u['title'] = $caseDetails->title; 
             $up_u['case_no'] = $caseDetails->case_no; 
              $court_name= !empty($generalHelper->getReletionFieldData('court_id',$caseDetails->court_id))?$generalHelper->getReletionFieldData('court_id',$caseDetails->court_id):'-' ;
             $up_u['court_name'] = $court_name; 
            
             $owner_id =$caseDetails->owner_id;
             $owner= !empty($generalHelper->getReletionFieldData('owner_id',$caseDetails->owner_id))?$generalHelper->getReletionFieldData('owner_id',$caseDetails->owner_id):'-' ; 
            $hearing_status= !empty($generalHelper->getReletionFieldData('hearing_status_id',$Hearings->hearing_status_id))?$generalHelper->getReletionFieldData('hearing_status_id',$Hearings->hearing_status_id):'-' ; 
             $up_u['hearing_status'] = $hearing_status; 
             $up_u['last_hearing_date'] = $Hearings->hearing_date; 
             $mail=domainConfig();
             $up_u['mail']['logo']=$mail->domain.$mail->mail_logo;
             $up_u['mail']['keyword']=$mail->mail_keyword;
             $up_u['mail']['footer']=$mail->mail_footer;  
            
            $up_u['owner'] =$owner;    
            $legalTeams = explode(',', $Hearings->legal_team_id);
            $lawyers = explode(',', $caseDetails->lawyers); 
            foreach ($legalTeams as $legal_team_id) { 
                $AppUsers =new AppUsers();
                $user= $AppUsers->getUserDataById($legal_team_id);
                $up_u['name'] = $user->name; 
                $this->mailsend('emails.hearingStatusChange',$up_u,'No-Reply','Case Concluded',$user->email,env('MAIL_EMAIL'),20);  
            }
            foreach ($lawyers as $lawyer_id) {
                $LawyerMaster =new LawyerMaster();
                $user= $LawyerMaster->getListById($lawyer_id);
                $up_u['name'] = $user->name; 
                $this->mailsend('emails.hearingStatusChange',$up_u,'No-Reply','Case Concluded',$user->email,env('MAIL_EMAIL'),20); 
            }
                   
            
        }catch(\Exception $e){
            Log::error('MailHelper-noticeStatusChange: '.$e->getMessage());        // making log in file
            return view('error.home');
        }
    }

    public function hearingReminder(){
        try{   
             $up_u=array(); 
             $generalHelper =new generalHelper();  
             $Cases = new Cases();
             $Hearing =new Hearing();   
             $arr=array(); 
             $date = date( "Y-m-d", strtotime( "+7 day" ) ); 
             $arr['date'] = $date; 
             $HearingsDetails=$Hearing->getResult($arr,'hearing_reminder');
             
             foreach ($HearingsDetails as  $Hearings) {
                 $caseDetails=$Cases->getCaseById($Hearings->case_id);  
                 $up_u['title'] = $caseDetails->title; 
                 $up_u['case_no'] = $caseDetails->case_no; 
                  $court_name= !empty($generalHelper->getReletionFieldData('court_id',$caseDetails->court_id))?$generalHelper->getReletionFieldData('court_id',$caseDetails->court_id):'-' ;
                 $up_u['court_name'] = $court_name; 
                 $up_u['court_detail'] = $Hearings->court_detail; 
                 $up_u['coram_detail'] = $Hearings->coram_detail; 
                
                 $owner_id =$caseDetails->owner_id;
                 $owner= !empty($generalHelper->getReletionFieldData('owner_id',$caseDetails->owner_id))?$generalHelper->getReletionFieldData('owner_id',$caseDetails->owner_id):'-' ; 
                $hearing_status= !empty($generalHelper->getReletionFieldData('hearing_status_id',$Hearings->hearing_status_id))?$generalHelper->getReletionFieldData('hearing_status_id',$Hearings->hearing_status_id):'-' ; 
                 $up_u['hearing_status'] = $hearing_status; 
                 $up_u['hearing_date'] = $Hearings->hearing_date; 
                 $mail=domainConfig();
                 $up_u['mail']['logo']=$mail->domain.$mail->mail_logo;
                 $up_u['mail']['keyword']=$mail->mail_keyword;
                 $up_u['mail']['footer']=$mail->mail_footer;  
                
                $up_u['owner'] =$owner;    
                $legalTeams = explode(',', $Hearings->notify_id);
                $lawyers = explode(',', $caseDetails->lawyers); 

                foreach ($legalTeams as $legal_team_id) { 
                    $AppUsers =new AppUsers();
                    $user= $AppUsers->getUserDataById($legal_team_id);
                    $up_u['name'] = $user->name; 

                    $this->mailsend('emails.hearingReminder',$up_u,'No-Reply','Case - Upcoming Hearing Information/Reminde',$user->email,env('MAIL_EMAIL'),20);  
                }
               
                    foreach ($lawyers as $lawyer_id) { 
                        $LawyerMaster =new LawyerMaster();
                        if (!empty($lawyer_id)) {
                           $user= $LawyerMaster->getListById($lawyer_id);
                            $up_u['name'] = $user->name; 
                            $this->mailsend('emails.hearingReminder',$up_u,'No-Reply','Case - Upcoming Hearing Information/Reminde',$user->email,env('MAIL_EMAIL'),20);  
                        }
                        
                    }
               
                
             }
              
             
                   
            
        }catch(\Exception $e){
            Log::error('MailHelper-hearingReminder: '.$e->getMessage());        // making log in file
            return view('error.home');
        }
    }

}