<?php 
namespace App\Modules\Litigation\Controllers\ActMaster;
use App\Helpers\MailHelper;
use App\Http\Controllers\Controller;
use App\Modules\Litigation\Models\Cases;
use App\Modules\Litigation\Models\Notice;
use App\Modules\Litigation\Models\ActMaster;
use App\Modules\Litigation\Models\Defaults\LegalCategory;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Validator;
class ActMasterController extends Controller
{
	public function __construct(){
		$this->middleware('admin');
	}
	
	public function index(Request $request){
		try{
			$data=array();
			$ActMaster = new ActMaster();
			$data["groupList"] = $MatterGroup->getMatterGroup();
			return view('Litigation::MatterMaster.view',$data);
		}catch(\Exception $e){
			Log::error('MatterMasterController-index: '.$e->getMessage()); 		
			return view('error.home');									
		}
	}

	public function actSubmit(Request $request){
		try{
			$company_id=Crypt::decrypt($request->company_id);
			$rules=[
				$company_id => 'numeric',
				'legal_category' => 'required',
				'act_name' => 'required|string|unique:act_master,name,NULL,id,company_id,' . $company_id,
				'short_name' => 'required|string|unique:act_master,short_name,NULL,id,company_id,' . $company_id,
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}

			$ActMaster = new ActMaster();
			$insArr['name'] = $request->act_name;
			$insArr['short_name'] = $request->short_name;
			$insArr['legal_category'] = Crypt::decrypt($request->legal_category);
			$insArr['company_id'] = $company_id;
			$ActMaster->insArr($insArr);

			$response["status"]=1;
			$response["msg"]="Act added Successfully";
			return response()->json($response);// response as json	
		}catch(\Exception $e){
			Log::error('ActMasterController-actSubmit: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err page
		}
	}

	public function getActList(Request $request){
		try{
			$company_id=Crypt::decrypt($request->id);
			$rules=[
				$company_id => 'numeric',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}
			$ActMaster = new ActMaster();
			$data['actList'] = $ActMaster->getAllActsByCompanyId($company_id);
			return view('Litigation::ActMaster.acttable',$data)->render();
		}catch(\Exception $e){
			Log::error('ActMasterController-getActList: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err page
		}
	}

	public function editAct(Request $request){
		try{
			$id=Crypt::decrypt($request->id);
			$rules=[
				$id => 'numeric',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}
			$ActMaster = new ActMaster();
			$LegalCategory = new LegalCategory();
			$data['act'] = $ActMaster->getActById($id);
			$data['categoryList'] = $LegalCategory->getLegalCategory();
			return view('Litigation::ActMaster.editact',$data)->render();
		}catch(\Exception $e){
			Log::error('ActMasterController-getActList: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err page
		}
	}

	public function updateAct(Request $request){
		try{
			$act_id=Crypt::decrypt($request->act_id);
			$company_id=Crypt::decrypt($request->company_id);
			$rules=[
				$act_id => 'numeric',
				$company_id => 'numeric',
				'act_name' => 'required|string|unique:act_master,name,'.$act_id.',id,company_id,' . $company_id,
				'short_name' => 'required|string|unique:act_master,short_name,'.$act_id.',id,company_id,' . $company_id,
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}

			$ActMaster = new ActMaster();
			$updArr['name'] = $request->act_name;
			$updArr['short_name'] = $request->short_name;
			$ActMaster->updateAct($updArr,$act_id);

			$response["status"]=1;
			$response["msg"]="Act Updated Successfully";
			return response()->json($response);// response as json	
		}catch(\Exception $e){
			Log::error('ActMasterController-updateAct: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err page
		}
	}

	public function exportexcel(Request $request){
		try{
			$response=array();
			$LegalCategory = new LegalCategory();
			$result = $LegalCategory->getLegalCategory();
			if(empty($result)){
				$response["status"]=0;
				$response["msg"]="Category not found!";
				return response()->json($response);
			}
			$response["status"]=1;
			$response["msg"]="Excel Created Successfully";
			return response()->json($response);
		}catch(\Exception $e){
			Log::error('ActMasterController-exportexcel: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}

	public function downloadexcel(Request $request){
		try{
			$LegalCategory = new LegalCategory();
			$result = $LegalCategory->getLegalCategory();
			$cat_arr = $result->pluck('name')->toArray();

			$cat_name = implode(',', $result->pluck('name')->toArray());

			//excel cretaion process start here

			$sheet_active=1;
			$spreadsheet = new Spreadsheet();
			$spreadsheet->setActiveSheetIndex(0)->mergeCells('A1:C1');
			$spreadsheet->setActiveSheetIndex(0)->setCellValue('A1', 'GENERAL INSTRUCTIONS - ACT NAME');
			$spreadsheet->getActiveSheet()->getStyle("A1")->getFont()->setSize(16);
			$spreadsheet->getActiveSheet()->getStyle('A1')->getFill()->applyFromArray(array(
					'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
					'startColor' => array(
						'argb' => '005392'
					)
			));
			$spreadsheet->getActiveSheet()->getStyle('A3')->getFill()->applyFromArray(array(
					'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
					'startColor' => array(
						'argb' => 'D3D3D3'
					)
			));
			$spreadsheet->getActiveSheet()->getStyle('B3')->getFill()->applyFromArray(array(
					'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
					'startColor' => array(
						'argb' => 'D3D3D3'
					)
			));
			$spreadsheet->getActiveSheet()->getStyle('C3')->getFill()->applyFromArray(array(
					'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
					'startColor' => array(
						'argb' => 'D3D3D3'
					)
			));

			$spreadsheet->setActiveSheetIndex(0)->setCellValue('A3', 'S.No');
			$spreadsheet->setActiveSheetIndex(0)->setCellValue('B3', 'Type');
			$spreadsheet->setActiveSheetIndex(0)->setCellValue('C3', 'Instructions');

			$spreadsheet->setActiveSheetIndex(0)->setCellValue('A4', '1');
			$spreadsheet->setActiveSheetIndex(0)->setCellValue('B4', 'General');
			$spreadsheet->setActiveSheetIndex(0)->setCellValue('C4', '1. Act sheet is to be made and uploaded by the Lawrbit Legal Matter Management Solution’s Admin for each legal entity separately (In case company has chosen to implement separately for each legal entity)
				2. Legal Category and Act Name are to be chosen by users while reporting a notice or litigation / case in software
				3. These columns will help club cases / notices by categories and will be helpful in analysis, hence its important to assign the Legal Category carefully to Acts / Laws');

			$spreadsheet->setActiveSheetIndex(0)->setCellValue('A5', '2');
			$spreadsheet->setActiveSheetIndex(0)->setCellValue('B5', 'Column - A');
			$spreadsheet->setActiveSheetIndex(0)->setCellValue('C5', 'Serial Number of the line items (Acts)');

			$spreadsheet->setActiveSheetIndex(0)->setCellValue('A6', '3');
			$spreadsheet->setActiveSheetIndex(0)->setCellValue('B6', 'Column-B: "Legal Category"');
			$spreadsheet->setActiveSheetIndex(0)->setCellValue('C6', '1. To be selected from drop down list only
				2. Is predefined by Lawrbit basis legal categories of laws from its exhaustive global Regulatory Intelligence
				3. The legal categories has impact of dashboards; hence can’t be changed / modified');

			$spreadsheet->setActiveSheetIndex(0)->setCellValue('A7', '4');
			$spreadsheet->setActiveSheetIndex(0)->setCellValue('B7', 'Column-C: Act Name');
			$spreadsheet->setActiveSheetIndex(0)->setCellValue('C7', '1. Define full name of the Act / Law under which notices / cases are filed by or against the entity
				2. Mention full name of the Act (e.g., The Employee Provident Fund Act and Miscellaneous Schemes Act, 1952)
				3. Avoid writing Rules to enable data consolidation and analysis (e.g., Securities and Exchange Board of India Act, 1992 should cover all regulations of SEBI; unless most notices / cases are filed under a specific rule of SEBI) Component of minimum 1 legal category
				4. Avoid using a name twice (e.g Goods and Service Tax Act, 2017 and Central Goods and Service Tax Act)
				5. Complete name of the act including year and state (if it is a state act)');

			$spreadsheet->setActiveSheetIndex(0)->setCellValue('A8', '5');
			$spreadsheet->setActiveSheetIndex(0)->setCellValue('B8', 'Column-D: Act Short Name');
			$spreadsheet->setActiveSheetIndex(0)->setCellValue('C8', '1. Common name of each act that user can relate with ((e.g., The Employee Provident Fund Act and Miscellaneous Schemes Act, 1952 can be written as “Provident Fund Act)
				2. It should be a unique name and explanatory, avoid using abbreviations.3. In notice and case reports, this will be a column to chose from');

			$spreadsheet->setActiveSheetIndex(0)->setCellValue('A9', 'In case you want to add new Acts / Law names, use fresh sheet; otherwise it would create duplicate names');

			$spreadsheet->setActiveSheetIndex(0)->mergeCells('A9:C9');

			$spreadsheet->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
			$spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth("100");

			$spreadsheet->getActiveSheet()->getStyle('A1')
				->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
			$spreadsheet->getActiveSheet()->getStyle('C3:C9')->getAlignment()->setWrapText(true); 

			$spreadsheet->getActiveSheet()->getStyle('A1:C9')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
			$spreadsheet->getActiveSheet()->getStyle("A1:C9")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
			$spreadsheet->getActiveSheet()->getStyle("C3:C9")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
			$spreadsheet->getActiveSheet()->getStyle("B3:B9")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
			$spreadsheet->getActiveSheet()->getStyle("A3:A9")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

			$cell_st =[
				'font' =>['bold' => true],
				'alignment' =>['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
				'borders'=>['bottom' =>['style'=> \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM]]
			];

			$sheet_title = "Instruction Sheet";
			$spreadsheet->getActiveSheet()->setTitle($sheet_title);

			for($i = 1; $i < 2; $i++){
				if($sheet_active>0){
					$spreadsheet->createSheet();
				}

				$spreadsheet->setActiveSheetIndex($sheet_active)
				->setCellValue('A1', 'SNO');

				$spreadsheet->setActiveSheetIndex($sheet_active)
				->setCellValue('B1', 'CATEGORY NAME');

				$spreadsheet->setActiveSheetIndex($sheet_active)
				->setCellValue('C1', 'ACT NAME');

				$spreadsheet->setActiveSheetIndex($sheet_active)
				->setCellValue('D1', 'SHORT NAME');

				$count = 1;
				for($k = 0; $k < count($cat_arr); $k++){
					$spreadsheet->setActiveSheetIndex($sheet_active)
					->setCellValue('Z'.$count, $cat_arr[$k]);
					$count++;
				}

				$spreadsheet->addNamedRange( new \PhpOffice\PhpSpreadsheet\NamedRange('category', $spreadsheet->setActiveSheetIndex($sheet_active), 'Z1:Z'.$count) );

				$spreadsheet->getActiveSheet()->getStyle('A1:D1')->getFill()->applyFromArray(array(
					'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
					'startColor' => array(
						'argb' => '3f6797'
					)
				));

				$spreadsheet->getActiveSheet()->getStyle('A1:D1')
				->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);

				$spreadsheet->getActiveSheet()->getStyle('A1:D1')->applyFromArray($cell_st);

				$spreadsheet->setActiveSheetIndex($sheet_active)
				->setCellValue('A2', '1');

				$validation = $spreadsheet->getActiveSheet()->getCell('B2')->getDataValidation();
				$validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
				$validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
				$validation->setAllowBlank(false);
				$validation->setShowInputMessage(true);
				$validation->setShowErrorMessage(true);
				$validation->setShowDropDown(true);
				$validation->setErrorTitle('Input error');
				$validation->setError('Value is not in list.');
				$validation->setPromptTitle('Pick from list');
				$validation->setPrompt('Please pick a value from the drop-down list.');
				// $validation->setFormula1('"'.$cat_name.'"'); // Make sure to put the list items between " and " if your list is simply a comma-separated list of values !!!
				$validation->setFormula1("=category"); // Make sure to put the list items between " and " if your list is simply a comma-separated list of values !!!
				// $spreadsheet->getActiveSheet()->getCell('B2')->setDataValidation(clone $validation);
				$spreadsheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
				$spreadsheet->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
				$spreadsheet->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
				$spreadsheet->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);

				$sheet_title = 'Act Sheet';
				$spreadsheet->getActiveSheet()->setTitle($sheet_title);

				$sheet_active++;
			}

			$spreadsheet->setActiveSheetIndex(0);
			$fxls ='legal_category.xlsx';
			$writer = new Xlsx($spreadsheet);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition:attachment;filename="'.$fxls.'"');
			header('Cache-Control: max-age=0');
			$writer->save("php://output");	

			//excel creation process end here
		}catch(\Exception $e){
			Log::error('ActMasterController-downloadexcel: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}

	public function excelsubmit(Request $request){
		try{
			$company_id = Crypt::decrypt($request->company_id);
			$rules=[
				$company_id => 'numeric',
				'excel_file' => 'required|mimes:xlsx,xls',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}

			$ActMaster=new ActMaster();
			$LegalCategory=new LegalCategory();
			$valid_cat=$LegalCategory->getLegalCategory()->pluck('id')->toArray();

			$field_array=array("SNO","CATEGORY NAME","ACT NAME","SHORT NAME");

			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
			$spreadsheet = $reader->load($request->file('excel_file')->getRealPath());
			// $sheetCount = $spreadsheet->getSheetCount(); 
			$responseError=array();

			for($i = 1; $i < 2; $i++) 
			{
    			$sheetData = $spreadsheet->getSheet($i)->toArray();

    			$sheet_title = 'Act Sheet';

    			foreach($sheetData as $va=>$key)
    			{ 
    				if ($va <= 0) { 
                    	continue;
                	}
                	elseif($key[0]==null)
                	{

                	}
                	else
                	{
                		$rules=array();
                		$key[0]=(int) $key[0];
                		$rules['0']='numeric';
                		$rules['1']='required';
                		$rules['2']='required|string|unique:act_master,name,NULL,id,company_id,' . $company_id;
                		$rules['3']='required|string|unique:act_master,short_name,NULL,id,company_id,' . $company_id;


                		$messages=array();
		                foreach($field_array as $kk=>$kkvalue)
		                {
		                	$msgva=$va+1;
		                  	$messages[$kk.".numeric"]="Sheet Row".$msgva." - "."$kkvalue must be numeric";
		                  	$messages[$kk.".required"]="Sheet Row".$msgva." - "."$kkvalue is required";
		                  	$messages[$kk.".string"]="Sheet Row".$msgva." - "."$kkvalue must be string";
		                  	$messages[$kk.".unique"]="Sheet Row".$msgva." - "."$kkvalue must be unique inside an entity";
		                }

	                    $arr['name'] = $key[1];
	                    $cat_detail=$LegalCategory->getResult($arr,'count');
	                    if($cat_detail == 0){
	                    	$msgva=$va+1;
	                    	$messages['ACT NAME'] = "Sheet Row".$msgva." - "."Act name not found!";
	                    }

	                    $validator = Validator::make($key,$rules,$messages);
	                    if($validator->fails()){
	                       	$errors = $validator->errors()->all(); 
	                       	$responseError[$sheet_title][]=$errors;
	                    }
                	}
    			}
				
			}

			if ($responseError!=null) {
              	$response['msg']= view('Litigation::ExcelErrors.errorMessage',compact('responseError'))->render();
          
              	$response["status"]=0;
              	return response()->json($response);// response as json
            }else{

            	for($i = 1; $i < 2; $i++) 
					{
						$sheetData = $spreadsheet->getSheet($i)->toArray();
						// $cat_id=(int) $sheetData[0][3];

						foreach($sheetData as $va=>$key)
						{ 
							if ($va <= 0) { 
			                	continue;
			            	}
			            	elseif($key[0]==null)
			            	{

			            	}
			            	else
			            	{
			            		$arr['name'] = $key[1];
			            		$cat_detail=$LegalCategory->getResult($arr,'byname');
			            		if(count(array_filter((array)$cat_detail))>0){
			            			$insArr=array();
									$insArr["company_id"]=$company_id;
									$insArr["legal_category"]=$cat_detail->id;
									$insArr["name"]=$key[2];
									$insArr["short_name"]=$key[3];
									$ActMaster->insArr($insArr);
			            		}
			            	}
						}
					}

				$response["status"]=1;
				$response['msg']="Act uploaded successful.";
      			return response()->json($response);
            }


		}catch(\Exception $e){
			Log::error('ActMasterController-excelsubmit: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}

}

