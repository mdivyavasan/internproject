<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use DB;
use DateTime;
use Carbon\Carbon;
use App\Cart;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use \stdClass;
use Session;
use Redirect;
use URL;
use Input;
use App\User;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\UserController;

class DemoController extends Controller
{
    /**
     * Store a new user.
     *
     * @param  Request  $request
     * @return Response
     */
	
				
public function fndemofuncdiva(Request $request)
				
	{

			$returnRes = [];
			$arrReturn = [];
		
		try
		
		{
		
				$data=$request->all();	
			   
					$input = [
						'recid' => isset($data['recid']) ? $data['recid'] : '0',
						'client_id' => isset($data['client_id']) ? $data['client_id'] : '',
						'client_name' => isset($data['client_name']) ? $data['client_name'] : '',
						'client_address1' => isset($data['client_address1']) ? $data['client_address1'] : '',
						'client_city' => isset($data['client_city']) ? $data['client_city'] : '',
						'client_state' => isset($data['client_state']) ? $data['client_state'] : '',
						'client_postcode' => isset($data['client_postcode']) ? $data['client_postcode'] : '',
						'schedule_type' => isset($data['schedule_type']) ? $data['schedule_type'] : '',
						'schedule_description' => isset($data['schedule_description']) ? $data['schedule_description'] : '',
						'client_phone' => isset($data['client_phone']) ? $data['client_phone'] : '',
			
					];
			   
					$rules = array(			  
						'client_name' => 'required',
						'client_address1' =>'required',
						'schedule_type' => 'required',
						'schedule_description' =>'required',
						'client_phone' =>'required',
				 
					);
			   
				$checkValid = Validator::make($input,$rules);
		
			if ($checkValid->fails())
		
			{
				$arrErr = $checkValid->errors()->all();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $arrErr;                 
				
			}
		
			else
		
			{
					
				$uprectid = $input['recid'];					
				
				$arrinsert = [
				"client_id" =>  $input['client_id'],
				"client_name" =>  $input['client_name'],
				"client_address1" =>  $input['client_address1'],
				"client_city" =>  $input['client_city'],
				"client_state" =>  $input['client_state'],
				"client_postcode" =>  $input['client_postcode'],
				"schedule_type" =>  $input['schedule_type'],
				"schedule_description" =>  $input['schedule_description'],
				"client_phone" =>  $input['client_phone'],
			];
				
		
				if($uprectid == 0)
				
				{
						$cart_item_id = DB::table('daily_schedules')->insertGetId($arrinsert);
						$arrReturn['message'] = 'record inserted successfully';
				}
				
				else
		
				{
						$cart_item_id = DB::table('daily_schedules')->where('id',$uprectid)->update($arrinsert);
						$arrReturn['message'] = 'record updated successfully';
		
				}						
					$arrReturn['status'] = "success";
				
			}
		
		}
				
			catch(\Exception $e)
					
			{	
			    $msg= $e->getMessage();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $msg;	
		
			}
			
			$returnRes=json_encode($arrReturn);
			return $returnRes; 
	}
				


public function fndemodelete(Request $request)
				
	{

			$returnRes = [];
			$arrReturn = [];
		
		try
		
		{
		
				$data=$request->all();	
			   
					$input = [
					
						'recid' => isset($data['recid']) ? $data['recid'] : '',
				
					];
			   
					$rules = array(			  
					
						'recid' => 'required',	
			
					);
			   
				$checkValid = Validator::make($input,$rules);
			   
			if ($checkValid->fails()) 
		
			{
				$arrErr = $checkValid->errors()->all();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $arrErr;                 
				
			}
		
			else
		
			{
					
				$uprectid = $input['recid'];
				$cart_item_id = DB::table('daily_schedules')->where('id',$uprectid)->delete();													
					
				$arrReturn['status'] = "success";
				$arrReturn['message'] = "record deleted successfully";
				    
			}
		
		}
		
			catch(\Exception $e)
		
			{	
				$msg= $e->getMessage();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $msg;
			}
			
			$returnRes=json_encode($arrReturn);
			return $returnRes; 
			
	}	
	
	
	
public function fngetschedulebyid(Request $request)
	
	{

			$returnRes = [];
			$arrReturn = [];
			$arrSdata = [];
		
		try
		{
		
				$data=$request->all();	
			   
					$input = [
						'id' => isset($data['id']) ? $data['id'] : '',
					];
			   
					$rules = array(			  
						'id' => 'required',	
					);
			   
				$checkValid = Validator::make($input,$rules);
			   
			if ($checkValid->fails()) 
				
			{
				$arrErr = $checkValid->errors()->all();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $arrErr;                 
			
			}
				
			else
			{	
                    
				$recid =  $input['id'];
					
				$scheduledata = DB::table('daily_schedules')->where('id', $recid)->first();

				$arrReturn['status'] = "success";
				$arrReturn['sdata'] = $scheduledata;
				$arrReturn['message'] = "record get  successfully";
				
			}
				
				
		}
		catch(\Exception $e)
		{	
				$msg= $e->getMessage();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $msg;
				
		}
 
		$returnRes=json_encode($arrReturn);
		return $returnRes; 
	}
	
	
public function fndemoget(Request $request)
				
	{

			$returnRes = [];
			$arrReturn = [];
			$arrSdata = [];
		
		try
		{
		
				$data=$request->all();	
			   
					$input = [
						'schedule_type' => isset($data['schedule_type']) ? $data['schedule_type'] : '',
					];
			   
					$rules = array(			  
						'schedule_type' => 'required',	
					);
			   
				$checkValid = Validator::make($input,$rules);
			   
			if ($checkValid->fails()) 
				
			{
				$arrErr = $checkValid->errors()->all();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $arrErr;                 
			}
			else
			{	
                    
				$stype =  $input['schedule_type'];
					
					if($stype == 'all')
					{
						$arrSdata = DB::select("SELECT * FROM daily_schedules");	
					}
					else
					{
						$arrSdata = DB::select("SELECT * FROM daily_schedules WHERE schedule_type = '".$stype."'");
					}	
				$arrReturn['status'] = "success";
				$arrReturn['sdata'] = $arrSdata;
				$arrReturn['message'] = "record print successfully";
								
			}
				
		}
		catch(\Exception $e)
			{	
				$msg= $e->getMessage();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $msg;
				
			}
 
		$returnRes=json_encode($arrReturn);
		return $returnRes; 
	}
				
public function fndemostatusupdate(Request $request)
				
	{

			$returnRes = [];
			$arrReturn = [];
		
		try
		
		{
		
				$data=$request->all();	
			   
				
					$input = [
				
						'recid' => isset($data['recid']) ? $data['recid'] : '',
						'assigned_to' => isset($data['assigned_to']) ? $data['assigned_to'] : '',
						'schedule_status' => isset($data['schedule_status']) ? $data['schedule_status'] : '',
						'schedule_date' => isset($data['schedule_date']) ? $data['schedule_date'] : '',
						'schedule_comments' => isset($data['schedule_comments']) ? $data['schedule_comments'] : '',
						'client_address2'  => isset($data['client_address2']) ? $data['client_address2'] : '',
						'created_datetime'  => isset($data['created_datetime']) ? $data['created_datetime'] : '',
						'assigned_datetime'  => isset($data['assigned_datetime']) ? $data['assigned_datetime'] : '',
						'completed_datetime'  => isset($data['completed_datetime']) ? $data['completed_datetime'] : '',
			
					];
			   
					$rules = array(			  
				
						'recid' => 'required',
						'assigned_to' =>'required',
						'schedule_status' => 'required',
						'schedule_date' =>'required',
						'schedule_comments' =>'required',
						'client_address2'=>'required',
			
					);
			   
				$checkValid = Validator::make($input,$rules);
			   
			if ($checkValid->fails())
				
			{
				
				$arrErr = $checkValid->errors()->all();
				$arrReturn['status'] = 'failed';					
				$arrReturn['message'] = $arrErr;                 
				
			}
				
			else
				
			{	
				$uprectid = $input['recid'];
					
				$arrinsert = [				
							
					"assigned_to" =>  $input['assigned_to'],
					"schedule_status" =>  $input['schedule_status'],
					"schedule_date" =>  $input['schedule_date'],
					"schedule_comments" =>  $input['schedule_comments'],
					"client_address2"  =>  $input['client_address2'],
					"created_datetime"  =>  $input['created_datetime'],
					"assigned_datetime"  =>  $input['assigned_datetime'],
					"completed_datetime"  =>  $input['completed_datetime'],
				
				];
				
				if($uprectid==0)
			
				{	
				
					$arrReturn['message']="record not updated";
				}	
		
				else
			
				{
				
					$cart_item_id = DB::table('daily_schedules')->where('id', $uprectid)->update($arrinsert); 
					$arrReturn['message'] = 'record updated successfully';
				
				}						
					
					$arrReturn['status'] = "success";
				    
			}
			
		}
		
		catch(\Exception $e)
				
		{	
				$msg= $e->getMessage();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $msg;
					
		}
		
		$returnRes=json_encode($arrReturn);
		return $returnRes; 
		
	}
	
//client services

public function fndemoinsert(Request $request)
				
				
	{

			$returnRes = [];
			$arrReturn = [];
		
		try
		
		{
		
				$data=$request->all();	
	
			
					$input = [
			
						'id' => isset($data['id']) ? $data['id'] : '0',				  
						'client_id' => isset($data['client_id']) ? $data['client_id'] : '0',
						'client_name' => isset($data['client_name']) ? $data['client_name'] : '',
						'client_address' => isset($data['client_address']) ? $data['client_address'] : '',
						'client_phoneno' => isset($data['client_phoneno']) ? $data['client_phoneno'] : '',
						'service_description' => isset($data['service_description']) ? $data['service_description'] : '',				  
						'service_status' => isset($data['service_status']) ? $data['service_status'] : 'pending',
						'assigned_to' => isset($data['assigned_to']) ? $data['assigned_to'] : '0',					
						'client_comments' => isset($data['client_comments']) ? $data['client_comments'] : '0',
						'archive_status' => isset($data['archive_status']) ? $data['archive_status'] : '0',
						'service_remarks' => isset($data['service_remarks']) ? $data['service_remarks'] : '0',
					];
					
					$rules = array(			  
		
						'client_name' => 'required',
						'client_address' =>'required',
						'client_phoneno' => 'required',
						'service_description' =>'required',
				 
					);
			   
				$checkValid = Validator::make($input,$rules);
			   
			
			if ($checkValid->fails()) 
					
			{
			
					$arrErr = $checkValid->errors()->all();
					$arrReturn['status'] = 'failed';
					$arrReturn['message'] = $arrErr;                 
					
			}
			
			else
			
			{
					
				$recid = $input['id'];
					
					
					$arrinsert = [
							
						"client_id" =>  $input['client_id'],
						"client_name" =>  $input['client_name'],
						"client_phoneno" =>  $input['client_phoneno'],
						"service_description" =>  $input['service_description'],							
						"service_status" =>  $input['service_status'],
						"assigned_to" =>  $input['assigned_to'],							
						"client_comments" =>  $input['client_comments'],
						"archive_status" =>  $input['archive_status'],
						"service_remarks" =>  $input['service_remarks'],
					];
					
			
					if($recid ==0)
					
					{
						$cart_item_id = DB::table('client_services')->insertGetId($arrinsert);
						$arrReturn['message'] = 'record inserted successfully';
					}
					
					else
				
					{
						$cart_item_id = DB::table('client_services')->where('id',$recid)->update($arrinsert);
						$arrReturn['message'] = 'record updated successfully';
					}						
					
					$arrReturn['status'] = "success";
				    
			}
			
		}
					
		catch(\Exception $e)
		
		{	
				$msg= $e->getMessage();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $msg;
					
		}
		
		$returnRes=json_encode($arrReturn);
		return $returnRes; 
	}	
					
				
					
public function fndemodeletenew(Request $request)
					
	{

			$returnRes = [];
			$arrReturn = [];
	
		try
		
		{
		
				$data=$request->all();	
			   
					$input = [
			       
						'id' => isset($data['id']) ? $data['id'] : '',
					
					];
			   
					$rules = array(			  
			
						'id' => 'required',	
					
					);
			   
			
				$checkValid = Validator::make($input,$rules);
			   

			if ($checkValid->fails())

			{
					
				$arrErr = $checkValid->errors()->all();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $arrErr;                 
		
			}
		
			else
		
			{
					
				$uprectid = $input['id'];
				$cart_item_id = DB::table('client_services')->where('id',$uprectid)->delete();													
					
				$arrReturn['status'] = "success";
				$arrReturn['message'] = "record deleted successfully";
				    
			}
				
			
		}
					
		catch(\Exception $e)
		
		{	
			    $msg= $e->getMessage();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $msg;
					
		
		}
 
				$returnRes=json_encode($arrReturn);
				return $returnRes; 
	}
	
	
									
public function fndemogetclient(Request $request)
					
	{
			$returnRes = [];
			$arrReturn = [];
			$arrSdata = [];		

		try
		
		{
		
				$data=$request->all();	
			   
					$input = [
							
							'client_id' => isset($data['client_id']) ? $data['client_id'] : '',
						];
			   
					$rules = array(			  
								
							'client_id' => 'required',	
						);
			   
				$checkValid = Validator::make($input,$rules);
			   
			if ($checkValid->fails())

			{
				$arrErr = $checkValid->errors()->all();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $arrErr;                 
				
			}
				
			else
			{	            
				$ctype =  $input['client_id'];
					
					if($ctype == 'all')
			
					{
						$arrSdata = DB::select("SELECT * FROM client_services");	
			
					}
					
					else	
					
					{
					
					$arrSdata = DB::select("SELECT * FROM client_services WHERE client_id = '".$ctype."'");

					}	
						
				$arrReturn['status'] = "success";
				$arrReturn['sdata'] = $arrSdata;
				$arrReturn['message'] = "record print successfully";	
				
			}
				
				
        }
		
		catch(\Exception $e)
		
		{	
			$msg= $e->getMessage();
			$arrReturn['status'] = 'failed';
			$arrReturn['message'] = $msg;
        }
 
		$returnRes=json_encode($arrReturn);
		return $returnRes; 
	}

	
public function fndemogetclientbystatus(Request $request)
					
	{
			$returnRes = [];
			$arrReturn = [];
			$arrSdata = [];
		
		try
		
		{
		
				$data=$request->all();	
			   
					$input = [
							
						'service_status' => isset($data['service_status']) ? $data['service_status'] : '',
					];
			   
					$rules = array(			  
					
						'service_status' => 'required',	
					);
			   
				$checkValid = Validator::make($input,$rules);
			   
			if ($checkValid->fails()) 
				
			{
				$arrErr = $checkValid->errors()->all();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $arrErr;                 
			}
		
			else
			{	
                    
				$ctype =  $input['service_status'];
					
					if($ctype == 'all')
					{
						$arrSdata = DB::select("SELECT * FROM client_services");	
					}
					else
					{
						 $arrSdata = DB::select("SELECT * FROM client_services WHERE service_status = '".$ctype."'");
					}					
						
				$arrReturn['status'] = "success";
				$arrReturn['sdata'] = $arrSdata;
				$arrReturn['message'] = "record print successfully";
		
			}
				
				
        }
		catch(\Exception $e)
		{		    
			$msg= $e->getMessage();
			$arrReturn['status'] = 'failed';
			$arrReturn['message'] = $msg;
        
		}
 
		$returnRes=json_encode($arrReturn);
		return $returnRes; 
	}
	

public function fndemogetclientbyassignto(Request $request)

	{

			$returnRes = [];
			$arrReturn = [];
			$arrSdata = [];
		
		try
		{
		
				$data=$request->all();	
			   
					$input = [
						'assigned_to' => isset($data['assigned_to']) ? $data['assigned_to'] : '',
						'service_status' => isset($data['service_status']) ? $data['service_status'] : '',
					];
			   
					$rules = array(			  
						'assigned_to' => 'required',	
						'service_status' =>  'required',
					);
			   
				$checkValid = Validator::make($input,$rules);
	
			if($checkValid->fails()) 

			{
				$arrErr = $checkValid->errors()->all();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $arrErr;                 
				
			}
			
			else
			{	
                    
				$assign =  $input['assigned_to'];
				$service =  $input['service_status'];	
				
			    $scheduledata = DB::table('client_services')->where('assigned_to', $assign)->where('service_status', $service)->first();					
				
				$arrReturn['status'] = "success";
				$arrReturn['sdata'] = $scheduledata;
				$arrReturn['message'] = "record get successfully";
			}
				
		}
		
		catch(\Exception $e)
		
		{	
			$msg= $e->getMessage();
			$arrReturn['status'] = 'failed';
			$arrReturn['message'] = $msg;
		}
 
		$returnRes=json_encode($arrReturn);
		return $returnRes; 
	}

//users

public function fndemogetnew(Request $request)


    {
			$returnRes = [];
			$arrReturn = [];
			$arrSdata = [];
		
		try
			
		{
		
			   $data=$request->all();	
			   
					$input = [
					
						'role' => isset($data['role']) ? $data['role'] :' ',
					];
			   
					$rules = array(			  
			     
						'role' => 'required',	
					);
			   
			   $checkValid = Validator::make($input,$rules);
			   
			    
			if ($checkValid->fails()) 
			
			{
				$arrErr = $checkValid->errors()->all();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $arrErr;                 
			
			}
				
			else
				
			{	
                    
				$utype =  $input['role'];
					
					if($utype == 'all')
			
					{
					
						$arrSdata = DB::select("SELECT * FROM users");	

					}
					
					else
			
					{
					
						$arrSdata = DB::select("SELECT * FROM users WHERE role = '".$utype."'");
					
					}					
						$arrReturn['status'] = "success";
						$arrReturn['sdata'] = $arrSdata;
						$arrReturn['message'] = "record print successfully";
					
			}
				
        
		}
		
		catch(\Exception $e)
		
		{	
			$msg= $e->getMessage();
			$arrReturn['status'] = 'failed';
			$arrReturn['message'] = $msg;
        
		}
 
		$returnRes=json_encode($arrReturn);
		return $returnRes; 
	
	}
	
	
//client lead
	
public function fndemoinsertlead(Request $request)
				
	{

			$returnRes = [];
			$arrReturn = [];
		
		try
		
		{
		
				$data=$request->all();	
			   
					$input = [
				
						'id' => isset($data['id']) ? $data['id'] : '0',
						'call_attendedby' => isset($data['call_attendedby']) ? $data['call_attendedby'] : '',
						'client_id' => isset($data['client_id']) ? $data['client_id'] : '0',
						'client_name' => isset($data['client_name']) ? $data['client_name'] : '',
						'client_address' => isset($data['client_address']) ? $data['client_address'] : '',
						'client_requirements' => isset($data['client_requirements']) ? $data['client_requirements'] : '',
						'lead_status' => isset($data['lead_status']) ? $data['lead_status'] : '',
						'lead_assigneto' => isset($data['lead_assigneto']) ? $data['lead_assigneto'] : '',
						'arcive_status' => isset($data['arcive_status']) ? $data['arcive_status'] : '0',
						'enquiry_date' => isset($data['enquiry_date']) ? $data['enquiry_date'] : '',
					];
			   
					$rules = array(			  
						'call_attendedby' => 'required',
						'client_name' =>'required',
						'client_address' => 'required',
						'client_requirements' =>'required',
					);
			   
				$checkValid = Validator::make($input,$rules);
			   
			if ($checkValid->fails()) 
					
			{
				$arrErr = $checkValid->errors()->all();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $arrErr;                 
			}
			
			else
			{
					
				$recid = $input['id'];
							
					$arrinsert = [
						"call_attendedby" =>  $input['call_attendedby'],
						"client_id" =>  $input['client_id'],
						"client_name" =>  $input['client_name'],
						"client_address" =>  $input['client_address'],
						"client_requirements" =>  $input['client_requirements'],
						"lead_status" =>  $input['lead_status'],
						"lead_assigneto" =>  $input['lead_assigneto'],
						"arcive_status" =>  $input['arcive_status'],
						"enquiry_date" =>  $input['enquiry_date'],
					];
					
				if($recid ==0)
				{
					$cart_item_id = DB::table('client_leads')->insertGetId($arrinsert);
					$arrReturn['message'] = 'record inserted successfully';
				}
				else
				{
					$cart_item_id = DB::table('client_leads')->where('id',$recid)->update($arrinsert);
					$arrReturn['message'] = 'record updated successfully';
				}						
					
				$arrReturn['status'] = "success";
				    
			}
			
		}
		catch(\Exception $e)
		{	
		    $msg= $e->getMessage();
			$arrReturn['status'] = 'failed';
			$arrReturn['message'] = $msg;
					
		}
		
		$returnRes=json_encode($arrReturn);
		return $returnRes; 
					
	}	


public function fndemodeletelead(Request $request)
									
	{
			
			$returnRes = [];
			$arrReturn = [];
		
		try
		{
		
				$data=$request->all();	
			   
					$input = [
						'id' => isset($data['id']) ? $data['id'] : '',
					];
			   
					$rules = array(			  
						'id' => 'required',	
					);
			   
				$checkValid = Validator::make($input,$rules);
			   
			if ($checkValid->fails()) 
			{
				$arrErr = $checkValid->errors()->all();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $arrErr;                 
			
			}
			else
			{
					
				$uprectid = $input['id'];
				$cart_item_id = DB::table('client_leads')->where('id',$uprectid)->delete();													
				
				$arrReturn['status'] = "success";
				$arrReturn['message'] = "record deleted successfully";
				    
			}
		}
		catch(\Exception $e)
		{	
			    $msg= $e->getMessage();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $msg;
					
		}
 
		$returnRes=json_encode($arrReturn);
		return $returnRes; 
					
	}
	
	
public function fndemogetlead(Request $request)

    {
		$returnRes = [];
		$arrReturn = [];
		$arrSdata = [];
		
		try
		{
				$crdate = date("Y-m-d");
				$data=$request->all();	
			   
					$input = [
						'lead_status' => isset($data['lead_status']) ? $data['lead_status'] :' ',
						'filter_date' => isset($data['filter_date']) ? $data['filter_date'] : $crdate,	
					];
			   
					$rules = array(			  
						'lead_status' => 'required',	
					);
			   
				$checkValid = Validator::make($input,$rules);
			   
			if ($checkValid->fails()) 
			{
				$arrErr = $checkValid->errors()->all();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $arrErr;                 
			
			}
				
			else
			{  
				$ltype =  $input['lead_status'];
				$enquirydate  = $input['filter_date']; 
			
					if($ltype == 'all')
					{
						$arrSdata = DB::select("SELECT * FROM client_leads where MONTH(enquiry_date) >= MONTH('".$enquirydate."') AND MONTH(enquiry_date) <= MONTH('".$enquirydate."')");	
					}
					else
					{
						$arrSdata = DB::select("SELECT * FROM client_leads WHERE lead_status = '".$ltype."' and MONTH(enquiry_date) >= MONTH('".$enquirydate."') AND MONTH(enquiry_date) <= MONTH('".$enquirydate."')");
					}						
								
				$arrReturn['status'] = "success";
				$arrReturn['sdata'] = $arrSdata;
				$arrReturn['message'] = "record print successfully";	
			}
				
				
        }
		catch(\Exception $e)
		{	
				$msg= $e->getMessage();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $msg;
		}
 
			$returnRes=json_encode($arrReturn);
			return $returnRes; 
	}
	

public function fndemogetleadbyid(Request $request)
	
 {
		$returnRes = [];
		$arrReturn = [];
		$arrSdata = [];
		
		try
		{
		
				$data=$request->all();	
					
					$input = [
						'id' => isset($data['id']) ? $data['id'] : '',
					];
			   
					$rules = array(			  
						'id' => 'required',	
					);
			   
				$checkValid = Validator::make($input,$rules);
			   
			if ($checkValid->fails()) 
			
			{
					$arrErr = $checkValid->errors()->all();
					$arrReturn['status'] = 'failed';
					$arrReturn['message'] = $arrErr;                 
			}
			else
			{	
                    
					$lead =  $input['id'];
					
					$leaddata = DB::table('client_leads')->where('id', $lead)->first();

					$arrReturn['status'] = "success";
					$arrReturn['sdata'] = $leaddata;
					$arrReturn['message'] = "record get successfully";
				
			}	
		
		}
		
			catch(\Exception $e)
			
			{	
				    $msg= $e->getMessage();
					$arrReturn['status'] = 'failed';
					$arrReturn['message'] = $msg;
			}
 
			$returnRes=json_encode($arrReturn);
			return $returnRes; 
	
	}	

//client activity

public function fndemoinsertupdateactivity(Request $request)

	{

			$returnRes = [];
			$arrReturn = [];
		
		try
		
		{
				
				$data=$request->all();				   
					$input = [
						'id' => isset($data['id']) ? $data['id'] : '0',				  
						'service_id' => isset($data['service_id']) ? $data['service_id'] : '0',
						'client_id' => isset($data['client_id']) ? $data['client_id'] : '',
						'technician_id' => isset($data['technician_id']) ? $data['technician_id'] : '',
						'action_date' => isset($data['action_date']) ? $data['action_date'] : '',
						'service_comments' => isset($data['service_comments']) ? $data['service_comments'] : '',				  
						'service_status' => isset($data['service_status']) ? $data['service_status'] : 'pending',
						'activity_datetime' => isset($data['activity_datetime']) ? $data['activity_datetime'] : '',					
						'completed_date' => isset($data['completed_date']) ? $data['completed_date'] : '',
						
					
					];
			   
					$rules = array(			  
						'service_id' => 'required',
						'technician_id' =>'required',
						'action_date' => 'required',
						'service_comments' =>'required',
						'service_status' =>'required',
					);
			   
				$checkValid = Validator::make($input,$rules);
			   
			if ($checkValid->fails()) 
		
			{
					$arrErr = $checkValid->errors()->all();
					$arrReturn['status'] = 'failed';
					$arrReturn['message'] = $arrErr;                 
					
			}
					
			else
			
			{
					
					$recid = $input['id'];
					$sStatus = $input['service_status'];
					
					$arrinsert = [							
							"service_id" =>  $input['service_id'],
							"client_id" =>  $input['client_id'],
							"technician_id" =>  $input['technician_id'],
							"action_date" =>  $input['action_date'],
							"service_comments" =>  $input['service_comments'],							
							"service_status" =>  $input['service_status'],
							"activity_datetime" =>  $input['activity_datetime'],							
							"completed_date" =>  $input['completed_date'],					
						];
					
						if($recid ==0)						
						{	
							$cart_item_id = DB::table('client_service_activity')->insertGetId($arrinsert);
							$arrReturn['message'] = 'record inserted successfully';
						}						
						else
						{
							$cart_item_id = DB::table('client_service_activity')->where('id',$recid)->update($arrinsert);
							$arrReturn['message'] = 'record updated successfully';
						}						
					
					    if($sStatus == "completed"){
							
								$srecid = $input['service_id'];
								$arrSupdate = [							
								"service_status" =>  $input['service_status'],
								"completed_date" =>  $input['completed_date'],	
						    ];
							$cart_item_id = DB::table('client_services')->where('id',$srecid)->update($arrSupdate);
						}
						else{
							
							$srecid = $input['service_id'];
							$arrSupdate = [							
								"service_status" =>  $input['service_status'],										
						    ];
							$cart_item_id = DB::table('client_services')->where('id',$srecid)->update($arrSupdate);
						}							
						
					$arrReturn['status'] = "success";
				    
			}
			
		}
			
			catch(\Exception $e)
			
			{	
				$msg= $e->getMessage();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $msg;
			}
		
		$returnRes=json_encode($arrReturn);
		return $returnRes; 
	}


public function fndemodeleteactivity(Request $request)
				
	{

			$returnRes = [];
			$arrReturn = [];
		
		try
		
		{
		
				$data=$request->all();	
			   
					$input = [
					
							'id' => isset($data['id']) ? $data['id'] : '',
					];
			   
					$rules = array(			  
							'id' => 'required',	
					);
			   
					$checkValid = Validator::make($input,$rules);
			   
			if ($checkValid->fails()) 
			{
					
					$arrErr = $checkValid->errors()->all();
					$arrReturn['status'] = 'failed';
					$arrReturn['message'] = $arrErr;                 
					
			}
			else
			{
					
					$uprectid = $input['id'];
					$cart_item_id = DB::table('client_service_activity')->where('id',$uprectid)->delete();													
					
					$arrReturn['status'] = "success";
					$arrReturn['message'] = "record deleted successfully";
				    
			}
				
			
		}
			
			catch(\Exception $e)
			{	
			    $msg= $e->getMessage();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $msg;
			}
 
			$returnRes=json_encode($arrReturn);
			return $returnRes; 
	}


public function fngetactivitybyserviceid(Request $request)

    {
		$returnRes = [];
		$arrReturn = [];
		$arrSdata = [];
		
		try
		{
		
				$data=$request->all();	
			   
					$input = [
						'service_id' => isset($data['service_id']) ? $data['service_id'] :' ',
					];
			   
					$rules = array(			  
						'service_id' => 'required',	
					);
			   
				$checkValid = Validator::make($input,$rules);
			   
			if ($checkValid->fails()) 
			{
				$arrErr = $checkValid->errors()->all();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $arrErr;                 
			
			}
				
			else
			{	
                    
				$ltype =  $input['service_id'];
					
					if($ltype == 'all')
					{
						$arrSdata = DB::select("SELECT * FROM client_service_activity");	
					}
					else
					{
						$arrSdata = DB::select("SELECT * FROM client_service_activity WHERE service_id = '".$ltype."'");
					}					
				$arrReturn['status'] = "success";
				$arrReturn['sdata'] = $arrSdata;
				$arrReturn['message'] = "record print successfully";	
			}
				
				
        }
		catch(\Exception $e)
		{	
			$msg= $e->getMessage();
			$arrReturn['status'] = 'failed';
			$arrReturn['message'] = $msg;
        }
 
		$returnRes=json_encode($arrReturn);
		return $returnRes; 
	}
		


public function fngetclientservicesbystatus(Request $request)
	{
		$returnRes = [];
		$arrReturn = [];
		$arrSdata = [];
		
		try
		{
		
		        $crdate = date("Y-m-d");
				$data=$request->all();	
			   
					$input = [
							
							'service_status' => isset($data['service_status']) ? $data['service_status'] : '',
							'filterdate' => isset($data['filterdate']) ? $data['filterdate'] : $crdate,							
					];
			   
					$rules = array(			  
					
							'service_status' => 'required',	
					);
			   
					$checkValid = Validator::make($input,$rules);
			   
			if ($checkValid->fails()) 				
			{
					$arrErr = $checkValid->errors()->all();
					$arrReturn['status'] = 'failed';
					$arrReturn['message'] = $arrErr;                 
			}
			else
			{	
                    
					$ctype =  $input['service_status'];
					$rptdate  = $input['filterdate'];
					
					if($ctype == 'all')
					{
						$arrSdata = DB::select("SELECT * FROM client_services where MONTH(reported_date) >= MONTH('".$rptdate."') AND MONTH(reported_date) <= MONTH('".$rptdate."')");	
					}
					else
					{
						$arrSdata = DB::select("SELECT * FROM client_services WHERE service_status = '".$ctype."' and MONTH(reported_date) >= MONTH('".$rptdate."') AND MONTH(reported_date) <= MONTH('".$rptdate."')");
					}					
					    
						
					$arrfdata = [];
						if($arrSdata)
						{	
							foreach($arrSdata as $adata)
							{								
								$assingedto = $adata->assigned_to;																
								$userdata = DB::table('users')->where('ID', $assingedto)->first();
								
								if($userdata)
								{								
									$adata->assingedname = $userdata->name;								
								}	
								$arrfdata[] = $adata;								
							}
						}
					$arrReturn['status'] = "success";
					$arrReturn['sdata'] = $arrfdata;
					$arrReturn['message'] = "record print successfully";
			}
				
				
        }
			catch(\Exception $e)
			{	
				    
					$msg= $e->getMessage();
					$arrReturn['status'] = 'failed';
					$arrReturn['message'] = $msg;
			}
 
			$returnRes=json_encode($arrReturn);
			return $returnRes; 
	}	

	
}