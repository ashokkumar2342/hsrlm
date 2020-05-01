<?php
namespace App\Helpers;
use Illuminate\Support\Facades\Request; 
use Illuminate\Support\Facades\Crypt;
use App\Modules\Compliance\Models\Country;
use Illuminate\Support\Facades\Log;
class calendarHelper
{
function show($month,$year,$datearray) {
  try{
     
     $daysOfWeek = array('SUN','MON','TUE','WED','THU','FRI','SAT');
     // What is the first day of the month in question?

     $firstDayOfMonth = mktime(0,0,0,$month,1,$year);
     $mmmdty=date('Y-m-01', strtotime("".$year."/".$month."/01"));
     $day_array = '2018-05-02,2018-05-05';
    // print_r($day_array);
     // How many days does this month contain?
     $numberDays = date('t',$firstDayOfMonth);
     // Retrieve some information about the first day of the

     // month in question.
     $dateComponents = getdate($firstDayOfMonth);

     // What is the name of the month in question?
    $monthName = $dateComponents['month'];

     // What is the index value (0-6) of the first day of the
     // month in question.
     $dayOfWeek = $dateComponents['wday'];

     // Create the table tag opener and day headers
     $calendar = "<table class='table table-responsive table-hover table-condensed calendar table-bordered small' style='margin:0px; width:100%; height:100%;'>";

     $calendar .= "<thead class=''><tr>";
     
     // Create the calendar headers
     foreach($daysOfWeek as $day) {
          $calendar .= "<th class='calendercss' style='background-color: #00a859;'><h5 style='color: white;'>$day</h5></th>";
     } 
    // Create the rest of the calendar

     // Initiate the day counter, starting with the 1st.
     $currentDay = 1;
     $calendar .= "</thead></tr><tr>";
     // The variable $dayOfWeek is used to

     // ensure that the calendar

     // display consists of exactly 7 columns.
    if ($dayOfWeek > 0) { 

          $calendar .= "<td colspan='$dayOfWeek' class='bg-white calendercss'>&nbsp;</td>"; 

     }
    $month = str_pad($month, 2, "0", STR_PAD_LEFT);
     while ($currentDay <= $numberDays) {
          // Seventh column (Saturday) reached. Start a new row.
          if ($dayOfWeek == 7) {
             $dayOfWeek = 0;
              $calendar .= "</tr><tr>";
          }
          $currentDayRel = str_pad($currentDay, 2, "0", STR_PAD_LEFT);
          $date = "$year-$month-$currentDayRel";
          
      $style="background-color:#5e5e5e";
      $style2="color:#fff;font-weight:bolder;font-size:18px";


          $todate= date('Y-m-d');
      if(in_array($date, $datearray))
      {
        $date1 = ($date);
        $url=url("user/calender/popup/".Crypt::encrypt($date1));
        $calendar .= "<td Style='".$style."' class='active calendercss' datatable-view='true' rel='$date' onclick=\"callPopupLarge(this,'$url')\" >
        <h6 Style='".$style2."'>$currentDay</h6></br>
        </td>";
      }
          elseif($date==$todate && !in_array($date, $datearray)) {
              $date1 = ($date);
        $calendar .= " <td class='active calendercss' rel='$date' >
        <h6>$currentDay</h6></br>
        </td>";
          }else {
              $date1 = ($date);
              $calendar .= " <td rel='$date' class='calendercss' >
              <h6>$currentDay</h6></br>
              </td>";
          }
          // Increment counters
          $currentDay++;
          $dayOfWeek++;
    }
     // Complete the row of the last week in month, if necessary
     if ($dayOfWeek != 7) { 
         $remainingDays = 7 - $dayOfWeek;
          $calendar .= "<td colspan='$remainingDays' class='calendercss'>&nbsp;</td>"; 
     }
     $calendar .= "</tr>";
    $calendar .= "</table>";
    return $calendar;
    }catch(\Exception $e){
            Log::error('calendarHelper-show: '.$e->getMessage());        // making log in file
            throw $e;               // throw the err
        }
}

        function currency_convertor($value)
        {
          try{
            if(is_numeric($value))
            {
              setlocale(LC_MONETARY, 'en_US');
              // $output['full_value']=money_format('%!.0n', $value);
              $output['full_value']=$value;
            }
            else
            {
              $output['full_value']=$value;
            }
  
            if($value>=1 && $value<100)
            {
                $output['value']=round($value);
                $output['key']='';
            }
            elseif($value>=100 && $value<1000)
            {
                $conv=$value/100;
                $output['value']=round($conv);
                $output['key']="h";
            }
            elseif($value>=1000 && $value<1000000)
            {
                $conv=$value/1000;
                $output['value']=round($conv);
                 $output['key']="k";
            }
            elseif($value>=1000000 && $value<1000000000)
            {
                $conv=$value/1000000;
                $output['value']=round($conv);
                 $output['key']="mn";
            }
            elseif($value>=1000000000 && $value<1000000000000)
            {
                $conv=$value/1000000000;
                $output['value']=round($conv);
                 $output['key']="tr";
            }
            elseif($value>=1000000000000 && $value<100000000000000000)
            {
                $conv=$value/1000000000000;
                $output['value']=round($conv);
                 $output['key']="qu";
            }
            else
            {
                 $output['value']=$value;
                 $output['key']="";
            }
            
            
            return $output;
            }catch(\Exception $e){
            Log::error('calendarHelper-currency_convertor: '.$e->getMessage());        // making log in file
            throw $e;               // throw the err
        }

        }

    function dateconvertforchart($value)
    {
      try{
        if((strpos($value, '-01-') !== false))
        {
            $output=str_replace("-01-","-00-",$value);
        }
        elseif((strpos($value, '-02-') !== false))
        {
            $output=str_replace("-02-","-01-",$value);
        }
        elseif((strpos($value, '-03-') !== false))
        {
            $output=str_replace("-03-","-02-",$value);
        }
        elseif((strpos($value, '-04-') !== false))
        {
            $output=str_replace("-04-","-03-",$value);
        }
        elseif((strpos($value, '-05-') !== false))
        {
            $output=str_replace("-05-","-04-",$value);
        }
        elseif((strpos($value, '-06-') !== false))
        {
            $output=str_replace("-06-","-05-",$value);
        }
        elseif((strpos($value, '-07-') !== false))
        {
            $output=str_replace("-07-","-06-",$value);
        }
        elseif((strpos($value, '-08-') !== false))
        {
            $output=str_replace("-08-","-07-",$value);
        }
        elseif((strpos($value, '-09-') !== false))
        {
            $output=str_replace("-09-","-08-",$value);
        }
        elseif((strpos($value, '-10-') !== false))
        {
            $output=str_replace("-10-","-09-",$value);
        }
        elseif((strpos($value, '-11-') !== false))
        {
            $output=str_replace("-11-","-10-",$value);
        }
        elseif((strpos($value, '-12-') !== false))
        {
            $output=str_replace("-12-","-11-",$value);
        }
        else
        {
            $value=$output;
        }

        return $output;
        }catch(\Exception $e){
            Log::error('calendarHelper-dateconvertforchart: '.$e->getMessage());        // making log in file
            throw $e;               // throw the err
        }
    }

    function currency_exchange($value)
        {
          try{
            $Country = new Country();
            $detail=array();
            $count_check=array_column($value, 'country_id');
            if(count(array_unique($count_check))==1)
            {   

                if(count($value)==1)
                {
                  $c_detail=$Country->getcountryDetail($value[0]['country_id']);
                  $detail['sign']=$c_detail->currency_html!='' ? $c_detail->currency_html : $c_detail->currency_code;
                  $detail['currency']=$this->currency_convertor($value[0]['sum']);
                  return $detail;
                }
                else
                {
                    $money=0;
                    foreach($value as $v)
                    {
                      $c_detail=$Country->getcountryDetail($v['country_id']);
                      $str=$v["currency_code"]."_".$c_detail->currency_code;
                      $json=file_get_contents("https://api.currconv.com/api/v6/convert?q=".$str."&compact=ultra&apiKey=5dd1e136-43a6-4722-aa0f-9983aeed4a79");
                      $url_res=json_decode($json,true);
                      $money=($url_res[$str]*$v['sum'])+$money;
                    }
                    $detail['sign']=$c_detail->currency_html!='' ? $c_detail->currency_html : $c_detail->currency_code;
                    $detail['currency']=$this->currency_convertor($money);
                    return $detail;
                }

            }
            else
            {
                $money=0;
                foreach($value as $v)
                {
                  $str=$v["currency_code"]."_USD";
                  $json=file_get_contents("https://api.currconv.com/api/v6/convert?q=".$str."&compact=ultra&apiKey=5dd1e136-43a6-4722-aa0f-9983aeed4a79");
                  $url_res=json_decode($json,true);
                  $money=($url_res[$str]*$v['sum'])+$money;
                }
                $detail['sign']='&#36;';
                $detail['currency']=$this->currency_convertor($money);
                return $detail;

            }
            }catch(\Exception $e){
            Log::error('calendarHelper-currency_exchange: '.$e->getMessage());        // making log in file
            throw $e;               // throw the err
        }
        }

        function currencySymbolByCountry($id)
        {
          try{
              $Country = new Country();
              $c_detail=$Country->getcountryDetail($id);
              return $c_detail->currency_html!='' ? $c_detail->currency_html : $c_detail->currency_code;
            }catch(\Exception $e){
            Log::error('calendarHelper-currencySymbolByCountry: '.$e->getMessage());        // making log in file
            throw $e;               // throw the err
            }
            
        }

        function currencySymbolByCountryCode($code)
        {
          try{
            if($code=='NA')
              return '';
            $Country = new Country();
            $c_detail=$Country->currencySymbolByCountryCode($code);
            return $c_detail->currency_html!='' ? $c_detail->currency_html : $c_detail->currency_code;
            }catch(\Exception $e){
            Log::error('calendarHelper-currencySymbolByCountryCode: '.$e->getMessage());        // making log in file
            throw $e;               // throw the err
            }
        }




}