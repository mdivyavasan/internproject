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

class UserController extends Controller
{
  public function __construct()
  {    
 
    
  }
  
  
  public function fnassignsubscription(Request $request)
  {
		   $arrReturn = [];		
		   $data=$request->all();
		   		   
		   try{
				
					$input = [
					    'subscription_id' => isset($data['subscription_id']) ? $data['subscription_id'] : 0,
						'user_id' => isset($data['user_id']) ? $data['user_id'] : '',						
						'paid_amount' => isset($data['paid_amount']) ? $data['paid_amount'] : '',
						'discount_amount' =>isset($data['discount_amount']) ? $data['discount_amount'] : 0,
						'discount_code' => isset($data['discount_code']) ? $data['discount_code'] : '',
						'payment_method' => isset($data['payment_method']) ? $data['payment_method'] : '',
						'admin_userid' => isset($data['admin_userid']) ? $data['admin_userid'] : 0,	
						];	
											
					$rules = array(
					   'subscription_id'  => 'required',
					   'user_id' => 'required',
					   'paid_amount' => 'required',
					   'payment_method' => 'required',
					   'admin_userid' =>'required',					  		  
					);
					
					
					 $checkValid = Validator::make($input,$rules);

					if ($checkValid->fails()) {					
						$arrErr = $checkValid->errors()->all();
						$arrReturn['status'] = 'failed';
						$arrReturn['message'] = $arrErr;                
						$returnRes=json_encode($arrReturn);
						return $returnRes;
					}else{				
						
						 $subsid = $input['subscription_id'];
						 $subscriptiondata = DB::table('subscription_plans')->where('id', $subsid)->first();
						 
						 if($subscriptiondata){
							 $this->assignsubscriptiontouser($input,$subscriptiondata); 						 
						     $arrReturn['status'] = "success";
							 $arrReturn['message'] = "user assigned successfully";
						 }else{
							 $arrReturn['status'] = "failed";
							 $arrReturn['message'] = "Invalid Subscription";
						 }
					}
			
			
			}catch(\Exception $e){	
			    $msg= $e->getMessage();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
        return $returnRes;
	}
  
  
   
  
  public function fnManagesubscriptons(Request $request)
    {
		   $arrReturn = [];		
		   $data=$request->all();
		   		   
		   try{
				
					$input = [
					    'subs_id' => isset($data['subs_id']) ? $data['subs_id'] : 0,
						'subscription_title' => isset($data['subscription_title']) ? $data['subscription_title'] : '',
						'short_description' => isset($data['short_description']) ? $data['short_description'] : '',
						'subscription_description' => isset($data['subscription_description']) ? $data['subscription_description'] : '',
						'subscription_price' => isset($data['subscription_price']) ? $data['subscription_price'] : '',
						'subs_interval' =>isset($data['subs_interval']) ? $data['subs_interval'] : '',
						'subs_intervaltype' => isset($data['subs_intervaltype']) ? $data['subs_intervaltype'] : '',
						'subcripiton_status' => isset($data['subcripiton_status']) ? $data['subcripiton_status'] : '',
						'subs_capacity' => isset($data['subs_capacity']) ? $data['subs_capacity'] : '',						
						'subs_modifiedby' => isset($data['subs_modifiedby']) ? $data['subs_modifiedby'] : '',					          					
						'admin_user_id' => isset($data['admin_user_id']) ? $data['admin_user_id'] : 0,
						'partner_id' => isset($data['course_partner']) ? $data['course_partner'] : 0,						
						];									
						
					$rules = array(
					   'subs_id'  => 'required',
					   'subscription_title' => 'required',
					   'short_description' => 'required',
					   'subscription_description' => 'required',
					   'subscription_price' => 'required',
					   'subs_interval' =>'required',
					   'subs_intervaltype' =>'required',
					   'subcripiton_status' =>'required',
					   'subs_capacity' =>'required',					   
					   'subs_modifiedby' =>'required',			  
					   'admin_user_id' =>'required',					  		  
					);
					
					
					 $checkValid = Validator::make($input,$rules);

					if ($checkValid->fails()) {
					
						$arrErr = $checkValid->errors()->all();
						$arrReturn['status'] = 'failed';
						$arrReturn['message'] = $arrErr;                
						$returnRes=json_encode($arrReturn);
						return $returnRes;
					}else{
						
						
					$subsid = $input['subs_id'];					
				     $inputdata = [					        
					        'subscription_title' =>  $input['subscription_title'],
							'short_description' =>  $input['short_description'],
                            'subscription_description' =>  $input['subscription_description'], 
                            'subscription_price' =>  $input['subscription_price'],
                            'subs_interval' =>  $input['subs_interval'],
                            'subs_intervaltype' =>  $input['subs_intervaltype'],
                            'subcripiton_status' =>  $input['subcripiton_status'],
                            'subs_capacity' =>  $input['subs_capacity'],
                            'subs_modifiedby' =>  $input['subs_modifiedby'],
							'partner_id' =>  $input['partner_id'],							
                           ];	
					 $inputdata['subs_lastmodifieddatetime'] = date('Y-m-d H:i:s');
								
							if($subsid == 0){
								  $inputdata['subs_createddatetime'] = date('Y-m-d H:i:s'); 						  					  
								  $inputdata['admin_user_id'] = $input['admin_user_id']; 						  					  
								  $insertUser = DB::table('subscription_plans')->insertGetId($inputdata);					  
								  $arrReturn['status'] = "success";
								  $arrReturn['message'] = "Subscription created successfully";							
							}else{
								
									$verifyupdatedata = DB::table('subscription_plans')->where('id', $subsid)->first();						
									if($verifyupdatedata){
										 $updateUser = DB::table('subscription_plans')->where('id', $subsid)->update($inputdata);	
										 $arrReturn['status'] = "success";
										 $arrReturn['message'] = "Subscription updated successfully";
									}else{
										$arrReturn['status'] = "failed";
										$arrReturn['message'] = "invalid Subscription record id";
									}
							}
						
					}
			
			
			}catch(\Exception $e){	
			    $msg= $e->getMessage();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
        return $returnRes;
	}
  
  
  
  public function fnupdatestudent(Request $request)
    {
		   $arrReturn = [];		
		   $data=$request->all();
		   
		   
		   try{
				
			$input = [
			        'user_id' => isset($data['user_id']) ? $data['user_id'] : '',
					'salutation' => isset($data['salutation']) ? $data['salutation'] : '',
					'firstname' => isset($data['firstname']) ? $data['firstname'] : '',
					'lastname' => isset($data['lastname']) ? $data['lastname'] : '',
					'email' =>isset($data['email']) ? $data['email'] : '',
					'phone_number' => isset($data['phone_number']) ? $data['phone_number'] : '',
					'address_line1' => isset($data['address_line1']) ? $data['address_line1'] : '',
					'address_line2' => isset($data['address_line2']) ? $data['address_line2'] : '',
					'city' => isset($data['city']) ? $data['city'] : '',
					'state' => isset($data['state']) ? $data['state'] : '',
					'post_code' => isset($data['post_code']) ? $data['post_code'] : '',					
					'gender' => isset($data['gender']) ? $data['gender'] : '', 					
					'user_category' => isset($data['user_category']) ? $data['user_category'] : '',	
					'user_country' => isset($data['user_country']) ? $data['user_country'] : '',
					'company_name' => isset($data['company_name']) ? $data['company_name'] : '',
					'vendor_id' => isset($data['vendor_id']) ? $data['vendor_id'] : '',	
					'invoice_day' => isset($data['invoice_day']) ? $data['invoice_day'] : '1',
					'alternate_number' => isset($data['alternate_number']) ? $data['alternate_number'] : '',	
					'dateofbirth' => isset($data['dateofbirth']) ? $data['dateofbirth'] : '',	
					'reference' => isset($data['reference']) ? $data['reference'] : '',	
					'caddress_line1' => isset($data['caddress_line1']) ? $data['caddress_line1'] : '',	
					'caddress_line2' => isset($data['caddress_line2']) ? $data['caddress_line2'] : '',	
					'ccity' => isset($data['ccity']) ? $data['ccity'] : '',	
					'cstate' => isset($data['cstate']) ? $data['cstate'] : '',	
					'cpost_code' => isset($data['cpost_code']) ? $data['cpost_code'] : '',	
					'invoice_email' => isset($data['invoice_email']) ? $data['invoice_email'] : '',
					'invoice_whatsapp' => isset($data['invoice_whatsapp']) ? $data['invoice_whatsapp'] : '',
					'invoice_type' => isset($data['invoice_type']) ? $data['invoice_type'] : '',
					'gst_no' => isset($data['gst_no']) ? $data['gst_no'] : '',
					'rental_mode' => isset($data['rental_mode']) ? $data['rental_mode'] : '',					
				];				

					
				
			$rules = array(
			  'user_id' => 'required',             
              'firstname' => 'required',
              'lastname' => 'required',
			  'email' =>'required | email',
			  'phone_number' =>'required',			 
            );
			
			
			 $checkValid = Validator::make($input,$rules);

            if ($checkValid->fails()) {            
				$arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{
				
				$userid = $input['user_id'];
				
				
				if($userid == 0){
										
					$usepassword =  rand();					
					$inputdata = [					        
					        'firstname' =>  $input['firstname'],
							'lastname' => $input['lastname'],
                            'email' =>  $input['email'],                            
         					'password' =>  $usepassword,
							'role' =>  'client',							
							'admin_userid' =>  0, 	
                           ];
						   
						   
					$rtdata = $this->fnuserregisternew($inputdata);
					
					$udetails = DB::table('users')->where('email', $input['email'])->first();
					
					if($udetails){
						
						$inputdata = [					        
					        'salutation' =>  $input['salutation'],							
                            'firstname' =>  $input['firstname'], 
							'lastname' =>  $input['lastname'], 
							'email' =>  $input['email'], 
							'phone_number' =>  $input['phone_number'], 
							'address_line1' =>  $input['address_line1'], 
							'address_line2' =>  $input['address_line2'], 
							'city' =>  $input['city'], 
							'state' =>  $input['state'], 
							'post_code' =>  $input['post_code'],
							'gender' =>  $input['gender'],
							'user_category' =>  $input['user_category'],
							'country' =>  $input['user_country'],
							'company_name' =>  $input['company_name'],
							'vendor_id' =>  $input['vendor_id'],
							'user_id' =>  $udetails->ID,
							'user_role' =>  "client",
							'invoice_day' =>  $input['invoice_day'],
							'alternate_number' =>  $input['alternate_number'],
							'dateofbirth' =>  $input['dateofbirth'],
							'reference' =>  $input['reference'],
							'caddress_line1' =>  $input['caddress_line1'],
							'caddress_line2' =>  $input['caddress_line2'],
							'ccity' =>  $input['ccity'],
							'cstate' =>  $input['cstate'],
							'cpost_code' =>  $input['cpost_code'],
							'invoice_email' =>  $input['invoice_email'],
							'invoice_whatsapp' =>  $input['invoice_whatsapp'],
							'gst_no' =>  $input['gst_no'],
							'rental_mode' =>  $input['rental_mode'],
                           ];
						$insertUser = DB::table('user_profiles')->insertGetId($inputdata);
					}			
					
						$arrReturn['status'] = "success";
	       	        $arrReturn['message'] = "student details updated successfully";		
					
				}else{
					
					
				$verifystudentda = DB::table('user_profiles')->where('user_id', $userid)->first();				
				if($verifystudentda){
					
					$inputdata = [					        
					        'salutation' =>  $input['salutation'],							
                            'firstname' =>  $input['firstname'], 
							'lastname' =>  $input['lastname'], 
							'email' =>  $input['email'], 
							'phone_number' =>  $input['phone_number'], 
							'address_line1' =>  $input['address_line1'], 
							'address_line2' =>  $input['address_line2'], 
							'city' =>  $input['city'], 
							'state' =>  $input['state'], 
							'post_code' =>  $input['post_code'],
							'gender' =>  $input['gender'],
							'user_category' =>  $input['user_category'],
							'country' =>  $input['user_country'],
							'company_name' =>  $input['company_name'],
							'vendor_id' =>  $input['vendor_id'],
							'invoice_day' =>  $input['invoice_day'],
							'alternate_number' =>  $input['alternate_number'],
							'dateofbirth' =>  $input['dateofbirth'],
							'reference' =>  $input['reference'],
							'caddress_line1' =>  $input['caddress_line1'],
							'caddress_line2' =>  $input['caddress_line2'],
							'ccity' =>  $input['ccity'],
							'cstate' =>  $input['cstate'],
							'cpost_code' =>  $input['cpost_code'],
							'invoice_email' =>  $input['invoice_email'],
							'invoice_whatsapp' =>  $input['invoice_whatsapp'],
							'invoice_type' =>  $input['invoice_type'],
							'gst_no' =>  $input['gst_no'],
							'rental_mode' =>  $input['rental_mode'],								
                           ];
						   
					$updateUser = DB::table('user_profiles')->where('id', $verifystudentda->id)->update($inputdata);	   
					
					//update core table
					$inputdata2 = [					        
					        'name' =>  $input['firstname']." ".$input['lastname'],							
                            'email' =>  $input['email'], 
                           ];
					$updateUser = DB::table('users')->where('id', $verifystudentda->user_id)->update($inputdata2);
					$arrReturn['status'] = "success";
	       	        $arrReturn['message'] = "student details updated successfully";		
				}
					
					
					
				}
				
				
				
				
				
				
				
				
				/*
				$verifystudentda = DB::table('users')->where('ID', $userid)->first();	
				if($verifystudentda){
					
					$inputdata = [					        
					        'name' =>  $input['firstname']." ".$input['lastname'],							
                            'email' =>  $input['email'], 
                           ];
					$updateUser = DB::table('users')->where('id', $verifystudentda->ID)->update($inputdata);
					$rtprodata = $this->fnuserProfileDetails($input['email'],$input);
					$arrReturn['status'] = "success";
	       	        $arrReturn['message'] = "student details updated successfully";				
					
				}else{
					$arrReturn['status'] = "failed";
	       	        $arrReturn['message'] = "Invalid Record id";	
				}
				
				*/
			}
			
			
			}catch(\Exception $e){	
			    $msg= $e->getMessage();				
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
        return $returnRes;
	}
  
  
  public function fnaddclient(Request $request)
    {
		   $arrReturn = [];		
		   $data=$request->all();
		   
		   
		   try{
				
			$input = [					
					'firstname' => isset($data['firstname']) ? $data['firstname'] : '',
					'lastname' => isset($data['lastname']) ? $data['lastname'] : '',
					'email' =>isset($data['email']) ? $data['email'] : '',
					'phone_number' => isset($data['phone_number']) ? $data['phone_number'] : '',					
					'user_role' => isset($data['user_role']) ? $data['user_role'] : 'client',	
					'user_category' => isset($data['user_category']) ? $data['user_category'] : 'individual',
					'partner_id' => isset($data['partner_id']) ? $data['partner_id'] : 0,
					'admin_user_id' => isset($data['admin_user_id']) ? $data['admin_user_id'] : 0,
					'password' => isset($data['password']) ? $data['password'] : '',
					'usergroup' => isset($data['usergroup']) ? $data['usergroup'] : '',					
				];
								
				
			$rules = array(             
              'firstname' => 'required',
              'lastname' => 'required',
			  'email' =>'required | email',
			  'phone_number' =>'required',			 
			  'user_role' =>'required',			  
            );
			
			
			 $checkValid = Validator::make($input,$rules);

            if ($checkValid->fails()) {
            
				$arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{
				
				$useremail = $input['email'];
				
				
				
				$verifystudentda = DB::table('users')->where('email', $useremail)->first();	
				if($verifystudentda){
					$arrReturn['status'] = "failed";
	       	        $arrReturn['message'] = "client (".$useremail.") already exists";
				}else{
					
					$usepassword =  rand();
					if($input['password']){
						$usepassword =  $input['password'];
					}
					
					$inputdata = [					        
					        'firstname' =>  $input['firstname'],
							'lastname' => $input['lastname'],
                            'email' =>  $input['email'],                            
         					'password' =>  $usepassword,
							'role' =>  $input['user_role'],
							'partner_id' =>  $input['partner_id'],
							'admin_userid' =>  $input['admin_user_id'], 	
                           ];
						   
						   
					$rtdata = $this->fnuserregisternew($inputdata);
					
					$inputdata2 = [					        
					        'firstname' =>  $input['firstname'],
							'lastname' => $input['lastname'],
                            'email' =>  $input['email'],
							'phone_number' =>  $input['phone_number'],
							'user_role' =>  $input['user_role'],
							'user_category' =>  $input['user_category'],
							'partner_id' =>  $input['partner_id'],
							'admin_userid' =>  $input['admin_user_id'], 	
                           ];
					
					$rtprodata = $this->fnuserProfileDetails($input['email'],$inputdata2);
					
					//update user group mapping
					$groupid = $input['usergroup'];
					if($groupid != 0){
						$this->fnusergroupmapping($input['email'],$groupid);
					}
					
					$arrReturn['status'] = "success";
	       	        $arrReturn['message'] = "client created successfully";
				}
			}
			
			
			}catch(\Exception $e){	
			    $msg= $e->getMessage();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
        return $returnRes;
	}
	
	
	
	public function fnusergroupmapping($email,$gid)
    {
		try{	
				
			$verifystudentda = DB::table('users')->where('email', $email)->first();				
			$inputdata = [	
							'user_id' => $verifystudentda->ID,
                            'group_id' =>  $gid, 
                           ];
			
			
			$verifyUser = DB::table('user_group_mapping')->where('group_id', $gid)->where('user_id', $verifystudentda->ID)->first();
            if($verifyUser == ''){				                     		   
				$insertUser = DB::table('user_group_mapping')->insertGetId($inputdata);
            }
		}catch(\Exception $e){	
            $msg= $e->getMessage(); 
			
        }
		
	}
	
	public function fnuserProfileDetails($email,$data)
    {
		try{	
				
			$verifystudentda = DB::table('users')->where('email', $email)->first();				
			$inputdata = [	
							'firstname' => $data['firstname'],
                            'lastname' =>  $data['lastname'],                            
         					'email' =>  $data['email'],
							'phone_number' =>  $data['phone_number'], 							
							'user_role' =>  $data['user_role'],	
							'user_category' =>  $data['user_category'],
							'user_id' =>  $verifystudentda->ID,
							'partner_id' =>  $data['partner_id'],
							'admin_user_id' =>  $data['admin_userid'],							
                           ];
			
			
			$verifyUser = DB::table('user_profiles')->where('email', $email)->first();
            if($verifyUser == ''){				                     		   
				$insertUser = DB::table('user_profiles')->insertGetId($inputdata);
            }else{
				$updateUser = DB::table('user_profiles')->where('id', $verifyUser->id)->update($inputdata);
            }
		}catch(\Exception $e){	
            $msg= $e->getMessage(); 
			
        }
		
	}
	

    public function fnuserregisternew($udata){
		$arrReturn =[];
		
		try{
			
			$user = new User;
            $user->name = $udata['firstname']." ".$udata['lastname'];
            $user->email = $udata['email'];
            $plainPassword = $udata['password'];
            $user->password = app('hash')->make($plainPassword);
            $user->role = $udata['role'];	
            $user->save();
			
			
		}catch(\Exception $e){	
            $msg= $e->getMessage();
            $arrReturn['status'] = 'failed';
            $arrReturn['message'] = $msg;
        }
		
	}			
			
			

    public function fnUserRegister(Request $request)
    {
		try{
		$data=$request->all();
		
		  $input = [
               'username' => isset($data['username']) ? $data['username'] : '',
               'email' => isset($data['email']) ? $data['email'] : '',
               'password' => isset($data['password']) ? $data['password'] : '',
			   'confirmpassword' =>isset($data['confirmpassword']) ? $data['confirmpassword'] : '',
			   'role' => isset($data['role']) ? $data['role'] : '',
			   
            ];
			
			     $rules = array(
               'username' => 'required',
              'email' => 'required|email',
              'password' => 'required',
			  'confirmpassword' =>'required',
			  'role' =>'required',
            );
            
			 $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;
                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{
				
				  if($input['password']==$input['confirmpassword'])
             {
                $useremail = $input['email'];

                $verifyUser = DB::table('dt_user')->where('user_email', $useremail)->where('trash', '0')->first();
                if($verifyUser == ''){
				  $input['password'] = app('hash')->make($input['password']);
                    $input['role']=$input['role'];
                   // $user = User::create($input);    
                     $currenttime = Carbon::now();
				   
	                $inputdata = [
					        'user_name' =>  $input['username'],
                            'user_email' =>  $input['email'],
                            'user_pass' => $input['password'],
                            'role'=> $input['role'],
							'created_at'=>$currenttime->toDateTimeString(),
							'updated_at'=>$currenttime->toDateTimeString(),
                           ];
						   
				 $insertUser = DB::table('dt_user')->insertGetId($inputdata);
				 
				  $arrReturn['user_id']=$insertUser;
				  $arrReturn['status'] = "success";
	       	      $arrReturn['message'] = "Your account has been successfully created";
                }else{
			      $arrReturn['status'] = "failed";
	       	      $arrReturn['message'] = "User Already Register.";
                }
              
			 
                
             }else{
				  $arrReturn['status'] = "failed";
	       	      $arrReturn['message'] = "Should be Password and Confirm password must same.";
                  //return $arrreturn;               
			}
			}
		                        
    }catch(\Exception $e){	
           $msg= $e->getMessage();
            $arrReturn['status'] = 'failed';
            $arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}
	
	
	
	
  	
      public function fnDeleteUser(Request $request)
    {
		try{
		$data=$request->all();
		
		 $input = [
		       'user_id' => isset($data['userid']) ? $data['userid'] : '',
               'email' => isset($data['email']) ? $data['email'] : '',
			   'deleted_by' => isset($data['deleted_by']) ? $data['deleted_by'] : '',
            ];
			
			     $rules = array(
					'user_id' => 'required',					
					);
            
			 $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;
                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{

                $useremail = $input['email'];
				$user_id = $input['user_id'];

                $verifyUser = DB::table('users')->where('ID', $user_id)->where('email', $useremail)->first();
			   if($verifyUser){	

					$verifyprofiledata = DB::table('user_profiles')->where('user_id', $user_id)->first();
					
					$arrdelete = [];
					$arrdelete['userlogin'] = $verifyUser;
					$arrdelete['usrprofiledata'] = $verifyprofiledata;
															
					$inputdata = [					        
					        'data_flag' =>  'userdelete',
							'deleted_data' => json_encode($arrdelete),
                            'deleted_by' =>  $input['deleted_by'], 
                            'deleted_date_time' =>  date('Y-m-d H:i:s'),                            
                           ];
						   
						   //insert logs for deleted data
				 //$insertUser = DB::table('trash_data')->insertGetId($inputdata);			   
		   
				 $insertUser = DB::table('users')->where('ID', $user_id)->where('email', $useremail)->delete();
				 $insertUser = DB::table('user_profiles')->where('user_id', $user_id)->delete();
				 
				  $userRes['user_details']='';
				  $arrReturn['status'] = "success";
	       	      $arrReturn['message'] = "User deleted successfully";
                }else{
			      $arrReturn['status'] = "failed";
	       	      $arrReturn['message'] = "User not found";
                }

			}
		                        
    }catch(\Exception $e){	
           $msg= $e->getMessage();
            $arrReturn['status'] = 'failed';
            $arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}
	
	
	public function fnGetUserDetailsbyId(Request $request)
    {
		try{
		$data=$request->all();
		
		 $input = [
		        'userid' => isset($data['userid']) ? $data['userid'] : '',               			   
            ];
			
			     $rules = array(
               'userid' => 'required',
                			   
            );
            
			 $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{

                $userid = $input['userid'];				
                $userdetails = DB::table('user_profiles')->where('id', $userid)->first();
				
				if($userdetails){
					$userrawadata = DB::table('users')->where('ID', $userdetails->user_id)->first();
					$userdetails->userfullname = $userdetails->firstname." ".$userdetails->lastname;
					$userdetails->userjoinedon = date('d-M-Y',strtotime($userrawadata->created_at));
					
					$arrprofimages = [];
					$getfileslist = DB::table('client_documents')->where('client_id', $userdetails->user_id)->get();
					if($getfileslist){	
					    $incr =1;
						foreach($getfileslist as  $glist){
							$objprofimage = new stdClass();
							$objprofimage->uid = "-".$incr;
							$objprofimage->fileid = $glist->ID;
							$objprofimage->name = $glist->document_name;
							$objprofimage->status = "done";
							$objprofimage->ftype = $glist->doucment_type;
							$objprofimage->url = 'https://www.boovantech.com/rental/public/upload/'.$glist->uploaded_name;
							$incr++;
							$arrprofimages[] = $objprofimage;
						}						
					}
					
				}
				
				$arrReturn['status'] = "success";
	       	    $arrReturn['userdetails'] = $userdetails ;
				$arrReturn['profiledouments'] = $arrprofimages;
		     }
		                        
    }catch(\Exception $e){	
           $msg= $e->getMessage();
            $arrReturn['status'] = 'failed';
            $arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}
	
	
	public function fnGetallUsers(Request $request)
    {
		try{
		$data=$request->all();
		$userslist = [];
		
		 $input = [				
		       'role' => isset($data['role']) ? $data['role'] : '',               
			   'searchinput' =>	isset($data['searchinput']) ? $data['searchinput'] : '',			   
            ];
			
			     $rules = array(
                      			   
            );
            
			 $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;
                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{
				
						$search_string = "";						
										
						if( $input['role'] != '') {
							$search_string .= " user_role = '".$input['role']."' AND ";
						}						
						
						if( $input['searchinput'] != '') {
							$svalue = $input['searchinput'];
							$search_string .= " firstname like  '%".$svalue."%' OR email like  '%".$svalue."%' OR  phone_number like '%".$svalue."%' ";	
						}
						
						$search_string = $search_string;
						
						if(!empty($search_string )){
						$search_string = rtrim( $search_string, 'AND ' );
						}
						if($search_string){
						$userslist =  DB::select("SELECT * from user_profiles WHERE ". $search_string." order by id desc");
						}else{ 
						 $userslist = DB::table('user_profiles')->get();
						}
							
						$arrReturn['status'] = "success";
						$arrReturn['userslist'] = $userslist;
		}
		                        
    }catch(\Exception $e){	
           $msg= $e->getMessage();
            $arrReturn['status'] = 'failed';
            $arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}

	
	
	public function fnGetUserslists(Request $request)
    {
		try{
		$data=$request->all();
		$userslist = [];
		
		 $input = [		        	
				'searchinput' => isset($data['searchinput']) ? $data['searchinput'] : '',				
            ];
			
			     $rules = array(
                      			   
            );
            
			 $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;
                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{
				
						$search_string = "";
						$search_stringval="";
						$i=0;
						
						if( !isset($input['searchinput']) || empty($input['searchinput'])) {
						
						}else{ 
					      $svalue = $input['searchinput'];
							$search_string .= " first_name like  '%".$svalue."%' OR first_name like  '%".$svalue."%' OR user_email like  '%".$svalue."%' OR PLF_ref like '%".$svalue."%' OR  user_phone like '%".$svalue."%' ";							
						}

						$userslist =  DB::select("SELECT * from dt_specimenprofiles WHERE ". $search_string);
						
						if(!empty($search_string )){
						$search_string = rtrim( $search_string, 'AND ' );
						}
						if($search_string){
						$specimenslist =  DB::select("SELECT user_id from dt_specimenprofiles WHERE ". $search_string);					
						//DB::select("SELECT * from users WHERE ". $search_string);
						}
						$spclist = array_unique($specimenslist,SORT_REGULAR);
					    $userslist = array();
						$udatalist = [];
                         if($spclist){
							foreach($spclist as $ulist){
							$userdtls = DB::table('users')->where('ID', $ulist->user_id)->where('role','user' )->first();	
							$totrptcounts = DB::table('dt_specimenprofiles')->where('user_id', $ulist->user_id)->count();	
							if($userdtls !=''){
								$userdtls->totreportcounts=$totrptcounts;
								$userslist[] =$userdtls;
							}
							
							}
						 }else{
                        
							$search_stringval .= "name like  '%".$svalue."%' OR email like  '%".$svalue."%'";
                            $udatalist = DB::select("SELECT * from users WHERE ". $search_stringval);
							if($udatalist){
                               
								foreach($udatalist as $ulist){
									$totrptcounts = DB::table('dt_specimenprofiles')->where('user_id', $ulist->ID)->count();	
									$ulist->totreportcounts = $totrptcounts;
									$userslist[] = $ulist;
								}

							}

						 };	
					
					
						$arrReturn['status'] = "success";
						$arrReturn['userslist'] = $userslist;
		}
		                        
    }catch(\Exception $e){	
           $msg= $e->getMessage();
            $arrReturn['status'] = 'failed';
            $arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}
	
	   public function fnaddSpecimenProfiles(Request $request)
    {
		try{

			
		$data=$request->all();
		
		 $input = [
		       'specimenid' => isset($data['specimenid']) ? $data['specimenid'] : '',
		       'userid' => isset($data['user_id']) ? $data['user_id'] : '',
			   'usertype' => isset($data['test_for']) ? $data['test_for'] : '',
               'firstname' => isset($data['first_name']) ? $data['first_name'] : '',
			   'lastname' => isset($data['last_name']) ? $data['last_name'] : '',
			   'gender' => isset($data['gender']) ? $data['gender'] : '',
			   'dob' => isset($data['dob']) ? $data['dob'] : '',
               'email' => isset($data['email']) ? $data['email'] : '',
               'mobileno' => isset($data['phone']) ? $data['phone'] : '',
			   'alt_mobileno' => isset($data['alt_mobileno']) ? $data['alt_mobileno'] : '',
			   'doorno' => isset($data['doorno']) ? $data['doorno'] : '',
			   'street' => isset($data['street']) ? $data['street'] : '',
			   'address1' => isset($data['address1']) ? $data['address1'] : '',
			   'address2' => isset($data['address2']) ? $data['address2'] : '',			   
			   'city' => isset($data['city']) ? $data['city'] : '',
			   'country' => isset($data['country']) ? $data['country'] : '',
			   'state' => isset($data['state']) ? $data['state'] : '',
			   'postcode' => isset($data['postcode']) ? $data['postcode'] : '',
			   'dateofarrival' => isset($data['dateofarrival']) ? $data['dateofarrival'] : '',
			   'plf_ref' => isset($data['plf_ref']) ? $data['plf_ref'] : '',
			   'vacination_status'=>isset($data['vacination_status']) ? $data['vacination_status'] : '',
			   'flight_no'=>isset($data['flight_no']) ? $data['flight_no'] : '',
			   'ethnicity'=>isset($data['ethnicity']) ? $data['ethnicity'] : '',
			   'country_zone'=>isset($data['country_zone']) ? $data['country_zone'] : '',
			   'passport_no'=>isset($data['passport_no']) ? $data['passport_no'] : '',
			   'remarks' => isset($data['remarks']) ? $data['remarks'] : '',
			   'report_status'=>isset($data['report_status']) ? $data['report_status'] : '',
			   'review_status'=>isset($data['review_status']) ? $data['review_status'] : '',
			   'submit_status'=>isset($data['submit_status']) ? $data['submit_status'] : '',
			   
            ];
			
			     $rules = array(
              'userid' => 'required',
			  'usertype'=>'required',
              'email' => 'required|email',
              'mobileno' => 'required',
            );
            
			 $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;
                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{

                $userid = $input['userid'];
				if($input['specimenid']){
					$specimenid = $input['specimenid'];
			}else{
				$specimenid = 0;
			}
				
				   $inputdata = [
					         'user_id' =>  $input['userid'],
							 'user_type' =>  $input['usertype'],
					        'first_name' =>  $input['firstname'],
							'last_name' =>  $input['lastname'],
							'gender' =>  $input['gender'],
							'dob' =>  $input['dob'],
                            'user_email' =>  $input['email'], 
                            'user_phone' =>  $input['mobileno'],
                            'user_altnumber' =>  $input['alt_mobileno'],
                            'user_doorno' =>  $input['doorno'],
                            'user_street' =>  $input['street'],
                            'user_address1' =>  $input['address1'],
                            'user_address2' =>  $input['address2'],
                            'user_city' =>  $input['city'],
                            'user_state' =>  $input['state'],
							'user_country' =>  $input['country'],
                            'user_postcode' =>  $input['postcode'],
							'Date_of_arrival' =>  $input['dateofarrival'],
							'PLF_ref' =>  $input['plf_ref'],
							'vacination_status' =>  $input['vacination_status'],
							'flight_no' =>  $input['flight_no'],
							'ethnicity' =>  $input['ethnicity'],
							'country_zone' =>  $input['country_zone'],
							'passport_no'=>$input['passport_no'],
                            'user_remarks' =>  $input['remarks'],
                            'report_status'	=> $input['report_status'],
							'review_status'	=> $input['review_status'],
							'submitted_status'	=> $input['submit_status'],						
                           ];
      
						   $verifyUser = DB::table('dt_specimenprofiles')->where('ID', $specimenid )->first();
					$currenttime = Carbon::now();
					if($verifyUser == ''){		
					  $getplf = DB::table('dt_specimenprofiles')->where('PLF_ref',$input['plf_ref'] )->first();
					  if($getplf == ''){
					  $inputdata['created_at']  = $currenttime->toDateTimeString();
					  $insertUser = DB::table('dt_specimenprofiles')->insertGetId($inputdata);			 
					  $arrReturn['user_details']=$insertUser;
					  $arrReturn['status'] = "success";
					  $arrReturn['message'] = "Specimen details has been successfully added";

						}else{
							$arrReturn['status'] = 'failed';
							$arrReturn['plferror'] = 'error';
							$arrReturn['message'] = 'PLF Ref already exist'; 
						  }
					}else{
						$inputdata['updated_at']  = $currenttime->toDateTimeString();
					    $updateUser = DB::table('dt_specimenprofiles')->where('ID', $specimenid )->update($inputdata);
						if($input['submit_status'] == 'Submitted'){
							$getuser = DB::table('users')->where('ID',  $input['userid'] )->first();

							$maildata = [
								'PLF_ref' => $input['plf_ref'],
								'name'=>$getuser->name,
								'email'=>$getuser->email,
								'specimenname'=>$input['firstname'],
								'specimenemail'=>$input['email'],	
								'phoneno'=>$input['mobileno'],							
							];
							
						$emailstatus = $this->submissionuseremail($maildata);
						$adminemail = $this->submissionadminemail($maildata);
						$arrReturn['email_status']= $emailstatus;
						}
					    $arrReturn['user_details']= $specimenid;
					    $arrReturn['status'] = "success";
					    $arrReturn['message'] = "Specimen details updated successfully";
					}

                          

			
			}
		                        
    }catch(\Exception $e){	
           $msg= $e->getMessage();
            $arrReturn['status'] = 'failed';
            $arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}
	
	
	
	
	
	    public function fnGetSpecimenList(Request $request)
    {
		try{
		$data=$request->all();
		
		 $input = [
		        'userid' => isset($data['userid']) ? $data['userid'] : '',
				'useremail' => isset($data['useremail']) ? $data['useremail'] : '',
				'usertype' => isset($data['usertype']) ? $data['usertype'] : '',
				'start_date' => isset($data['start_date']) ? $data['start_date'] : '',					
				'end_date' => isset($data['end_date']) ? $data['end_date'] : '',
				'status' => isset($data['status']) ? $data['status'] : '',							
				'state' => isset($data['state']) ? $data['state'] : '',
            ];
			
			     $rules = array(
               'userid' => 'required',			 		   
            );
            
			 $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;
                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{
		
				/* $userid = $input['userid'];
				if($input['userid'] != ''){
				$alluser = DB::table('dt_specimenprofiles')->where('user_id', $userid)->get();
				$specimenlist = json_decode($alluser);	
				} */
				
				         
			$search_string = "";$i=0;
			foreach( $input as $skey=>$svalue ){
			
			if( !isset($input[$skey]) || empty($input[$skey])) {
			
			}else{ 
			if($skey =="userid"){
			$search_string .= " user_id = '".$svalue."' AND ";
			}else if($skey =="state"){
			$search_string .= " user_state = '".$svalue."' AND ";
			}else if($skey =="usertype"){
			$search_string .= " user_type = '".$svalue."' AND ";
			}else if($skey =="status"){
			$search_string .= " report_status = '".$svalue."' AND ";
			}else if($skey =="useremail"){
			$search_string .= " user_email like  '%".$svalue."%' AND ";
			}else if($skey =="start_date" && $input['end_date'] != ''){
			$search_string .= " created_at >=  '".$svalue."' AND created_at <= '".$input['end_date']."' AND ";
			}else if($skey =="start_date" && $input['end_date'] == ''){
			$search_string .= " created_at =  '".$svalue."' AND";
			}else if($skey =="end_date" && $input['start_date'] == ''){
			$search_string .= " created_at =  '".$svalue."' AND";
			}
			else 
			$search_string .= "";
			}
			}

			if(!empty($search_string )){
			$search_string = rtrim( $search_string, 'AND ' );
			}

			if($search_string){
			$specimenlist =  DB::select("SELECT * from dt_specimenprofiles WHERE ". $search_string." order by ID DESC");
			}else{
			 $specimenlist = DB::table('dt_specimenprofiles')->orderby('ID','DESC')->get();
			}
				
				
                			
				if($specimenlist){				           				  
				  $arrReturn['status'] = "success";
	       	      $arrReturn['specimenlist'] = $specimenlist;
                }else{
				  $arrReturn['status'] = "success";
	       	      $arrReturn['message'] = "No users found";
                }
		}
		                        
    }catch(\Exception $e){	
           $msg= $e->getMessage();
            $arrReturn['status'] = 'failed';
            $arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}
	
	
	 public function fnGetSpecimenDetails(Request $request)
    {
		try{
		$data=$request->all();
		$arrprofimages = [];
		$arrspecimenprofimages=[];
		$list=[];
		$userdetails = [];
		 $input = [
		        'specimenid' => isset($data['specimenid']) ? $data['specimenid'] : '',
            ];
			
			     $rules = array(
               'specimenid' => 'required',			 		   
            );
            
			 $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrifErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;
                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{
	

				$specimenid = $input['specimenid'];
                $specimendtls = DB::table('dt_specimenprofiles')->where('ID', $specimenid)->first();
				
				if($specimendtls){
					$getfileslist = DB::table('dt_profiledocuments')->where('user_id', $specimendtls->user_id)->where('specimen_id', $specimenid)->get();
					$userdetails = DB::table('users')->where('ID', $specimendtls->user_id)->first();	
					$specimendtls->dateofbirth = $specimendtls->dob;			    				    
					$specimendtls->submitteddate =  date("d/m/Y H:i:s", strtotime($specimendtls->updated_at));
					$specimendtls->dob =  date("d/m/Y", strtotime($specimendtls->dob));
					$specimendtls->report_gen_datetime =  date("d/m/Y H:i:s", strtotime($specimendtls->report_gen_datetime));
					$specimendtls->report_send_datetime =  date("d/m/Y H:i:s", strtotime($specimendtls->report_send_datetime));
					
					if($getfileslist){	
					    $incr =1;
						foreach($getfileslist as  $glist){
							$objprofimage = new stdClass();
							$objprofimage->uid = "-".$incr;
							$objprofimage->fileid = $glist->ID;
							$objprofimage->name = $glist->document_name;
							$objprofimage->status = "done";
							$objprofimage->url = 'https://rapidtest.haleclinic.com/apiservice/public/upload/'.$glist->uploaded_name;
							$incr++;
							$arrprofimages[] = $objprofimage;
						}						
					}

					$getspecimenfileslist = DB::table('dt_speficimendocuments')->where('user_id', $specimendtls->user_id)->where('specimen_id', $specimenid)->get();				    
					if($getspecimenfileslist){	
					    $incrr =1;
						foreach($getspecimenfileslist as  $glist){
							$objspeciprofimage = new stdClass();
							$objspeciprofimage->uid = "-".$incrr;
							$objspeciprofimage->fileid = $glist->ID;
							$objspeciprofimage->name = $glist->document_name;
							$objspeciprofimage->status = "done";
							$objspeciprofimage->url = 'https://rapidtest.haleclinic.com/apiservice/public/upload/'.$glist->uploaded_name;
							$incr++;
							$arrspecimenprofimages[] = $objspeciprofimage;
						}						
					}


					$commentslist = DB::table('dt_reviewtstaus')->where('specimen_id', $specimenid)->where('user_id', $specimendtls->user_id)->orderby('id','DESC')->get();
			         	$list = json_decode($commentslist);	
 				        $i=0;		
                foreach($list as $date){
	                   $fdate =$date->created_at;
	                  $tdate = Carbon::now();
	                  $datetime1 = new DateTime($fdate);
	                  $datetime2 = new DateTime($tdate);
	                  $interval = $datetime1->diff($datetime2);
	                 $days = $interval->format('%a');//now do whatever you like with $days
                     if($days == 0){
	                	$day = 'Today';
	                   }elseif($days == 1){
	                 	$day  = '1 day ago';
	                   }else{
		                 $day  = $days." days ago";
	                }
	                     $list[$i]->days = $day;	
                        $i++;
                         }
					
				}
				//get specimen profile documents
				
				
				
				//$specimenlist = json_decode($alluser);			
				if($specimendtls){				           				  
				  $arrReturn['status'] = "success";
	       	      $arrReturn['specimendetails'] = $specimendtls;			 
				  $arrReturn['profiledouments'] = $arrprofimages;
				  $arrReturn['specimenprofiledocuemnts'] = $arrspecimenprofimages;
				  $arrReturn['reportcomments'] = $list;
				  $arrReturn['userdetails'] = $userdetails;
                }else{
				  $arrReturn['status'] = "success";
	       	      $arrReturn['message'] = "user not found";
                }
		}
		                        
    }catch(\Exception $e){	
           $msg= $e->getMessage();
            $arrReturn['status'] = 'failed';
            $arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}
  	
	
	
	
 public function fnAdduserProfileDoc(Request $request)
{
  try{
  $data = $request->all();
  if ($request->hasFile('file')) {
			$input = [
               'client_id' => isset($data['client_id']) ? $data['client_id'] : '',               			   
            ];
	 	 
		 
			     $rules = array(
					'client_id' => 'required',	                		   
				);
			
			 $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;
                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{
			
		 if($request->file('file')) {
		  $file = $request->file('file');
		  $size = $request->file('file')->getSize();
		  $type = $request->file->extension();
				if($type == 'jpg' || $type == 'png' || $type == 'pdf'){
		  
	  
      $filename = $request->file('file')->getClientOriginalName();
	  $random = Str::random(10);
	  $uploadname = 'userprofile_doc_'.$random.'.'.$type;
      // $menuimagename ='Tango_'. str_random(10) . '.png';
	  
				if($size< 10000000){	
					$path =$request->file('file')->move($this->public_path("upload/"),$uploadname);
					$itemurl=url(("/upload/").$uploadname);	
					$currenttime = Carbon::now();
 
				   $inputdata = [
					         'client_id' =>  $input['client_id'],											      
							 'document_name' =>  $filename,
                             'uploaded_name' =>  $uploadname, 
                             'created_at' =>  $currenttime->toDateTimeString(),
                             'doucment_type' => $type,                           					
                           ];	

				 
						   
				  $insertDocument = DB::table('client_documents')->insertGetId($inputdata);			  
				   
				  
				  $arrReturn['fileid'] = $insertDocument;
				  $arrReturn['status'] = "success";
	       	      $arrReturn['message'] = "Document uploaded successfully";
	  }else{
		  
		  $arrReturn['status'] = "failed";
		$arrReturn['message'] = "Please try uploading a smaller size file. Max file size limit is 10MB";
	  }
	  
	  }else{
		  $arrReturn['status'] = "failed";
		$arrReturn['message'] = "Invalid file";
	  }
  }	  
  }
  }else{
		  
		  $arrReturn['status'] = "failed";
		$arrReturn['message'] = "Please attach the document";
	  }
}catch(\Exception $e){	
           $msg= $e->getMessage();
            $arrReturn['status'] = 'failed';
            $arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	} 


	 public function fnAddspecimenProfileDoc(Request $request)
{
  try{
  $data = $request->all();
  if ($request->hasFile('file')) {
	  
	   $input = [
               'userid' => isset($data['userid']) ? $data['userid'] : '',
               'specimenid' => isset($data['specimenid']) ? $data['specimenid'] : '',			   
            ];
	 	 
		 
			     $rules = array(
                'userid' => 'required',	
                'specimenid' => 'required',			   
            );
			
			 $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;
                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{
			
     if($request->file('file')) {
	  $file = $request->file('file');
	  $size = $request->file('file')->getSize();
	  $type = $request->file->extension();
  if($type == 'jpg' || $type == 'png' || $type == 'pdf'){
		  
	  
      $filename = $request->file('file')->getClientOriginalName();
	  $random = Str::random(10);
	  $uploadname = 'specimenprofile_doc_'.$random.'.'.$type;
      // $menuimagename ='Tango_'. str_random(10) . '.png';
	  
	    if($size< 10000000){	
    $path =$request->file('file')->move($this->public_path("upload/"),$uploadname);
    $itemurl=url(("/upload/").$uploadname);	
	$currenttime = Carbon::now();
				   $inputdata = [
					         'user_id' =>  $input['userid'],
							 'specimen_id' =>  $input['specimenid'],					      
							'document_name' =>  $filename,
                            'uploaded_name' =>  $uploadname, 
                            'created_at' =>  $currenttime->toDateTimeString(),                       					
                           ];				                     		   
				  $insertDocument = DB::table('dt_speficimendocuments')->insertGetId($inputdata);
				  $arrReturn['fileid'] = $insertDocument;
				  $arrReturn['status'] = "success";
	       	      $arrReturn['message'] = "Document uploaded successfully";
	  }else{
		  
		  $arrReturn['status'] = "failed";
		$arrReturn['message'] = "Please try uploading a smaller size file. Max file size limit is 10MB";
	  }
	  
	  }else{
		  $arrReturn['status'] = "failed";
		$arrReturn['message'] = "Invalid file";
	  }
  }	  
  }
  }else{
		  
		  $arrReturn['status'] = "failed";
		$arrReturn['message'] = "Please attach the document";
	  }
}catch(\Exception $e){	
           $msg= $e->getMessage();
            $arrReturn['status'] = 'failed';
            $arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}
	
	
	
public function public_path($path=null)
{

return rtrim(app()->basePath('/public/'.$path), '/');
}	


 public function fnDeleteProfileDoc(Request $request)
{
  try{
  $data = $request->all(); 
	   $input = [
               'document_id' => isset($data['document_id']) ? $data['document_id'] : '',               	   
            ];
	 	 
		 
			     $rules = array(
                'document_id' => 'required',				   
            );
			
			 $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;
                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{
				
	  $document = DB::table('client_documents')->where('ID', $input['document_id'])->get();
	    if($document){				                     		   
				  DB::table('client_documents')->where('ID', $input['document_id'])->delete();
				  $arrReturn['status'] = "success";
	       	      $arrReturn['message'] = "Document deleted successfully";
	  }else{
		  
		  $arrReturn['status'] = "failed";
		$arrReturn['message'] = "File not found";
	  }
    
  }

}catch(\Exception $e){	
           $msg= $e->getMessage();
            $arrReturn['status'] = 'failed';
            $arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	} 
	
	
	
	 public function fnDeleteSpecimenDoc(Request $request)
{
  try{
  $data = $request->all(); 
	   $input = [
               'document_id' => isset($data['document_id']) ? $data['document_id'] : '',               	   
            ];
	 	 
		 
			     $rules = array(
                'document_id' => 'required',				   
            );
			
			 $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;
                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{
				
	  $document = DB::table('dt_speficimendocuments')->where('ID', $input['document_id'])->get();
	    if($document){				                     		   
				  DB::table('dt_speficimendocuments')->where('ID', $input['document_id'])->delete();
				  $arrReturn['status'] = "success";
	       	      $arrReturn['message'] = "Document deleted successfully";
	  }else{
		  
		  $arrReturn['status'] = "failed";
		$arrReturn['message'] = "File not found";
	  }
    
  }

}catch(\Exception $e){	
           $msg= $e->getMessage();
            $arrReturn['status'] = 'failed';
            $arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	} 
	
	
	
	
	 public function fnaddReviewstatus(Request $request)
    {
		try{
		$data=$request->all();
		
		 $input = [
		       'userid' => isset($data['userid']) ? $data['userid'] : '',
               'specimenid' => isset($data['specimenid']) ? $data['specimenid'] : '',
			   'status' => isset($data['status']) ? $data['status'] : '',
               'comments' => isset($data['comments']) ? $data['comments'] : '',
			   'added_roletype' => isset($data['added_roletype']) ? $data['added_roletype'] : '',
               'added_by' => isset($data['added_by']) ? $data['added_by'] : '',			   
            ];
			
			     $rules = array(
               'userid' => 'required',
              'specimenid' => 'required',
              'status' => 'required',
			  'added_by' => 'required',
            );
            
			 $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;
                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{
            $currenttimetime = Carbon::now();
                $userid = $input['userid'];
				   $inputdata = [
					        'user_id' =>  $input['userid'],
					        'specimen_id' =>  $input['specimenid'],
							'status' =>  $input['status'],
                            'comments' =>  $input['comments'], 
                            'added_by' =>  $input['added_by'],
							'added_role_type' =>  $input['added_roletype'],
                            'created_at' => $currenttimetime->toDateTimeString(),							
                           ];    			                     		   
				  $insertReview = DB::table('dt_reviewtstaus')->insertGetId($inputdata);
				  
				  //admin log update
				  
				  if($input['added_roletype'] == "admin"){
						$updateData = ["review_status" => $input['status']];
						$updupdaterece = DB::table('dt_specimenprofiles')->where('ID', $input['specimenid'])->update($updateData);
						
						$revestatus = $input['status'];
						$getspecimen =  DB::table('dt_specimenprofiles')->where('ID', $input['specimenid'])->first();
						if($revestatus == "Query"){
							$maildata = [
								'PLF_no' => $getspecimen->PLF_ref,
								'name'=> $getspecimen->first_name,								
								'query'=> $input['comments'],	
								'userid'=> $getspecimen->user_id,								
							];
						    $this->adminqueryemailtouser($maildata);
							
						}						
						//adminqueryemailtouser
						
				  }

				  //getplfno
				  if($input['added_roletype'] == "user"){
					$getspecimen =  DB::table('dt_specimenprofiles')->where('ID', $input['specimenid'])->first();
					$querydetails = DB::table('dt_reviewtstaus')->where('specimen_id', $input['specimenid'])->where('added_role_type', 'admin')->get();
				
					if(count($querydetails) != 0){
						$querylist = json_decode($querydetails); 
					    $adminquery = $querylist[count($querylist)-1];
						$queryval = $adminquery->comments;
   
					}else{
						$queryval = '';  
					}
							
					$maildata = [
						'PLF_no' => $getspecimen->PLF_ref,
						'name'=>$getspecimen->first_name,
						'reply'=>$input['comments'],
						'query'=>$queryval,
						'email'=>$input['added_by'],
					];
  
			  $senemil = $this->userqueryrplyemail($maildata);	
				  }
				  
				  
       $logupdate = [
					        'user_id' =>  $input['userid'],				        
							'status' =>  $input['status'],                             
                            'added_by' =>  $input['added_by'],
							'table' => 'dt_reviewtstaus',
							'record_id'=> $insertReview,
                           ];  				  
                  $updatelog = $this->fnlogstatusupdate($logupdate);
				  
				  $arrReturn['status'] = "success";
	       	      $arrReturn['message'] = "review status successfully added";
             		
			}
		                        
    }catch(\Exception $e){	
           $msg= $e->getMessage();
            $arrReturn['status'] = 'failed';
            $arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}
  	
	
	public function fnlogstatusupdate($updatedetails)
    {
		try{
			
				$currenttimetime = Carbon::now();
		 $updatedata = [
		'user_id' => $updatedetails['user_id'],
		'action' => $updatedetails['status'],  
        'table'=>$updatedetails['table'],
        'record_id'=>$updatedetails['record_id'],		
		'action_datetime' =>$currenttimetime->toDateTimeString(),
		];
		$insertUser = DB::table('dt_logs')->insertGetId($updatedata);
		 $arrReturn['status'] = "success";	
          
		 
		}catch(\Exception $e){	
           $msg= $e->getMessage();
            $arrReturn['status'] = 'failed';
            $arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}


	public function fnGetreviewlist(Request $request)
    {
		try{
		$data=$request->all();

		
		 $input = [
		        'specimenid' => isset($data['specimenid']) ? $data['specimenid'] : '',
				'userid' => isset($data['userid']) ? $data['userid'] : '',
            ];
			
			     $rules = array(
					'userid' => 'required',
               'specimenid' => 'required',
			   			 		   
            );
            
			 $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;
                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{


				$specimenid = $input['specimenid'];
				$userid = $input['userid'];
                $commentslist = DB::table('dt_reviewtstaus')->where('specimen_id', $specimenid)->where('user_id', $userid)->orderby('id','DESC')->get();
				$list = json_decode($commentslist);	
 				$i=0;		
foreach($list as $date){
	$fdate =$date->created_at;
	$tdate = Carbon::now();
	$datetime1 = new DateTime($fdate);
	$datetime2 = new DateTime($tdate);
	$interval = $datetime1->diff($datetime2);
	$days = $interval->format('%a');//now do whatever you like with $days
     if($days == 0){
		$day = 'Today';
	 }elseif($days == 1){
		$day  = '1 day ago';
	 }else{
		$day  = $days." days ago";
	 }
	 $list[$i]->days = $day;	
$i++;
}

				if($list){				           				  
				  $arrReturn['status'] = "success";
	       	      $arrReturn['commentslist'] = $list;
                }else{
				  $arrReturn['status'] = "success";
	       	      $arrReturn['message'] = "No comments found";
                }
		}
		                        
    }catch(\Exception $e){	
           $msg= $e->getMessage();
            $arrReturn['status'] = 'failed';
            $arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}
  	
	
	
   public function sendtestemail(Request $request){
		
		$returnRes='';
		$arrReturn=[]; 	
		$data = $request->all();
		try {	
		
		   $emailbody = "
		    Dear [NAME],
			<p>Thanks for choosing HaleClinic as your test partner.<br />
			Below are the guidelines and instructions for testing your specimen.<br />
			We assure you that all details provided by you will be kept confidential.<br />
			Stay Safe!<br />
			Thanks,<br />
			HaleClinic
			</p>";	
			
		   $mailTo = "san5835@gmail.com";
		   $mailFrom = "s.sankar@mercuryminds.com";
		   $mailFromName = "HaleClinic";
		   $mailSubject = "HaleClinic Rapidtest Welcomes you!";
		   

           $emailsend =  Mail::html($emailbody, function($message) use ($mailTo,$mailFrom,$mailFromName,$mailSubject) {
				$message->to($mailTo)
				->subject($mailSubject);
				$message->from($mailFrom,$mailFromName);
			});		
			
            $arrReturn['status']='success';		  
		}
		catch ( \Exception $ex ) {
			$msg= $ex->getMessage();
			$arrReturn['status']= $msg;		
		}
		$returnRes = json_encode($arrReturn);
		return $returnRes; 
  
  }
  
  
  
  public function fnGetSpecimenListcustom(Request $request)
  {
		  try{
				  $data=$request->all();
				
				  $input = [
						'userid' => isset($data['userid']) ? $data['userid'] : '',
						'useremail' => isset($data['useremail']) ? $data['useremail'] : '',
						'usertype' => isset($data['usertype']) ? $data['usertype'] : '',
						'start_date' => isset($data['start_date']) ? $data['start_date'] : '',					
						'end_date' => isset($data['end_date']) ? $data['end_date'] : '',
						'status' => isset($data['status']) ? $data['status'] : '',							
						'state' => isset($data['state']) ? $data['state'] : '',
					];								 
					

					$specimenlist =  DB::select("SELECT * from dt_specimenprofiles WHERE review_status not in('completed') and submitted_status = 'submitted'");
					$cntnewrequests = DB::table('dt_specimenprofiles')->where('review_status', 'new')->count();	
					$cntinprocrequests = DB::table('dt_specimenprofiles')->where('review_status', 'inprogress')->count();
					$cntquteyrequests = DB::table('dt_specimenprofiles')->where('review_status', 'query')->count();					
					
					$objstats = new stdClass();
					$objstats->newrequests = $cntnewrequests;
					$objstats->reviewrequests = $cntinprocrequests;
					$objstats->queryrequests = $cntquteyrequests;
					
					
					if($specimenlist){				           				  
						  $arrReturn['status'] = "success";
						  $arrReturn['specimenlist'] = $specimenlist;
						  $arrReturn['requeststats'] = $objstats;
					}else{
						  $arrReturn['status'] = "success";
						  $arrReturn['message'] = "No users found";
					}
				
										
				}catch(\Exception $e){	
							$msg= $e->getMessage();
							$arrReturn['status'] = 'failed';
						$arrReturn['message'] = $msg;
				}
		 
					$returnRes=json_encode($arrReturn);
					 return $returnRes;
	}
	
	public function fnResetpwd(Request $request)
    {
		try{
		$data=$request->all();
		
		 $input = [
		       'userid' => isset($data['userid']) ? $data['userid'] : '',
               'email' => isset($data['email']) ? $data['email'] : '',
			   'password' => isset($data['password']) ? $data['password'] : '',			   
            ];
			
			     $rules = array(
               'userid' => 'required',
              'email' => 'required|email',
			  'password' => 'required',
            );
            
			 $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;
                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{

                $useremail = $input['email'];
				$userid = $input['userid'];
                $password = app('hash')->make($input['password']);

                $verifyUser = DB::table('users')->where('ID', $userid)->where('email', $useremail)->first();
			   if($verifyUser){	
				$currenttimetime = Carbon::now();			                     
				$inputdata = [
					'password' =>  $password,
					'updated_at'=> $currenttimetime->toDateTimeString(),
				   ];
				 $insertUser = DB::table('users')->where('ID', $userid)->where('email', $useremail)->update($inputdata);
				 
				  $userRes['user_details']='';
				  $arrReturn['status'] = "success";
	       	      $arrReturn['message'] = "Password updated successfully";
                }else{
			      $arrReturn['status'] = "failed";
	       	      $arrReturn['message'] = "User not found";
                }

			}
		                        
    }catch(\Exception $e){	
           $msg= $e->getMessage();
            $arrReturn['status'] = 'failed';
            $arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}

	   

		
      
			public function submissionuseremail($regdata)
    {
		$returnRes='';
		$arrReturn=[]; 	
        try {
			
			$emailconfig = DB::table('dt_appconfigurations')->where('config_key','emailconfig')->first();				
			$emailconfigdta = json_decode($emailconfig->config_params);
			
			$emailtemplates = DB::table('dt_emailtemplates')->where('email_flag','usersubmitreview')->first();		
            
			$emailbody = $emailtemplates->email_text;
			$replaces = array();
			$replaces['NAME'] = $regdata['name'];
			$replaces['PLF'] = $regdata['PLF_ref'];		
			
			foreach ($replaces as $key => $value)
			{
			$key = strtoupper($key);
			$emailbody = str_replace("[$key]", $value, $emailbody);
			}
			$emailsubject = $emailtemplates->email_subject;
			$emailsubject2 = str_replace('[PLF]', $regdata['PLF_ref'],$emailsubject);

			$mailTo=$regdata['email'];
			$mailFrom= $emailconfigdta->adminemail;
			$mailFromName= $emailconfigdta->admin_fromname;
		    $mailSubject = $emailsubject2;		   

            $emailsend =  Mail::html($emailbody, function($message) use ($mailTo,$mailFrom,$mailFromName,$mailSubject) {
				$message->to($mailTo)
				->subject($mailSubject);
				$message->from($mailFrom,$mailFromName);
			});		
			

            $arrReturn['status']=$emailsend;
			
			
		} catch (\Exception $e) {
            
        }
       
    }



    public function adminqueryemailtouser($regdata)
    {
		$returnRes='';
		$arrReturn=[]; 	
        try {
			
						
			$emailconfig = DB::table('dt_appconfigurations')->where('config_key','emailconfig')->first();				
			$emailconfigdta = json_decode($emailconfig->config_params);
			
			$emailtemplates = DB::table('dt_emailtemplates')->where('email_flag','adminquerytouser')->first();		            
			$emailbody = $emailtemplates->email_text;
			$replaces = array();
			$replaces['Name'] = $regdata['name'];
			$replaces['Query'] = $regdata['query'];
						
			foreach ($replaces as $key => $value)
			{			
				$emailbody = str_replace("[$key]", $value, $emailbody);
			}
			$emailsubject = $emailtemplates->email_subject;
			$emailsubject2 = str_replace('[PLF]', $regdata['PLF_no'],$emailsubject);

            $userdetails = DB::table('users')->where('ID',$regdata['userid'])->first();	

			
			$mailTo= $userdetails->email;
			$mailFrom = $emailconfigdta->adminemail;
			$mailFromName= $emailconfigdta->admin_fromname;
		    $mailSubject = $emailsubject2;		   

            $emailsend =  Mail::html($emailbody, function($message) use ($mailTo,$mailFrom,$mailFromName,$mailSubject) {
				$message->to($mailTo)
				->subject($mailSubject);
				$message->from($mailFrom,$mailFromName);
			});		
			

            $arrReturn['status']=$emailsend;
			
			
		} catch (\Exception $e) {
            
        }
       
    }

	public function userqueryrplyemail($regdata)
    {
		$returnRes='';
		$arrReturn=[]; 	
        try {
			
			$emailconfig = DB::table('dt_appconfigurations')->where('config_key','emailconfig')->first();				
			$emailconfigdta = json_decode($emailconfig->config_params);
			
			$emailtemplates = DB::table('dt_emailtemplates')->where('email_flag','userqueryrply')->first();		            
			$emailbody = $emailtemplates->email_text;
			$replaces = array();
			$replaces['NAME'] = $regdata['name'];
			$replaces['PLF'] = $regdata['PLF_no'];
			$replaces['ADMIN'] = $emailconfigdta->admin_fromname;
			$replaces['QUERY'] = $regdata['query'];
			$replaces['REPLY'] = $regdata['reply'];		
			
			foreach ($replaces as $key => $value)
			{
			$key = strtoupper($key);
			$emailbody = str_replace("[$key]", $value, $emailbody);
			}
			$emailsubject = $emailtemplates->email_subject;
			$emailsubject2 = str_replace('[PLF]', $regdata['PLF_no'],$emailsubject);


			$mailTo= $emailconfigdta->adminemail;
			$mailFrom = $emailconfigdta->adminemail;
			$mailFromName= $emailconfigdta->admin_fromname;
		    $mailSubject = $emailsubject2;		   

            $emailsend =  Mail::html($emailbody, function($message) use ($mailTo,$mailFrom,$mailFromName,$mailSubject) {
				$message->to($mailTo)
				->subject($mailSubject);
				$message->from($mailFrom,$mailFromName);
			});		
			

            $arrReturn['status']=$emailsend;
			
			
		} catch (\Exception $e) {
            
        }
       
    }


	public function submissionadminemail($regdata)
    {
		$returnRes='';
		$arrReturn=[]; 	
        try {
			
			$emailconfig = DB::table('dt_appconfigurations')->where('config_key','emailconfig')->first();				
			$emailconfigdta = json_decode($emailconfig->config_params);
			
			$emailtemplates = DB::table('dt_emailtemplates')->where('email_flag','adminsubmitreview')->first();		
			$emailbody = $emailtemplates->email_text;
			// $emailbody2 = str_replace('[PLFNO]', $regdata['PLF_ref'],$emailbody);
			$replaces = array();
			$replaces['USERNAME'] = $regdata['specimenname'];
			$replaces['PLFNO'] = $regdata['PLF_ref'];
			$replaces['EMAIL'] = $regdata['specimenemail'];	
			$replaces['MOBILE'] = $regdata['phoneno'];		
			
			foreach ($replaces as $key => $value)
			{
			$key = strtoupper($key);
			$emailbody = str_replace("[$key]", $value, $emailbody);
			}

			$emailsubject = $emailtemplates->email_subject;
			$emailsubject2 = str_replace('[PLF]', $regdata['PLF_ref'],$emailsubject);

			

			$mailTo= $emailconfigdta->adminemail;
			$mailFrom= $emailconfigdta->adminemail;
			$mailFromName= $emailconfigdta->admin_fromname;
		    $mailSubject = $emailsubject2;		   

            $emailsend =  Mail::html($emailbody, function($message) use ($mailTo,$mailFrom,$mailFromName,$mailSubject) {
				$message->to($mailTo)
				->subject($mailSubject);
				$message->from($mailFrom,$mailFromName);
			});		
			

            $arrReturn['status']=$emailsend;
			
			
		} catch (\Exception $e) {
            
        }
       
    }	
	
	
	public function fngetsubscriptionbytype(Request $request)
    {
		try{
		$data=$request->all();
		$userslist = [];
		
		    $input = [				
		       'subcripiton_status' => isset($data['subcripiton_status']) ? $data['subcripiton_status'] : 'All', 
            ];
			
			$rules = array(
                      			   
            );
            
			 $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;
                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{
				
						$search_string = "";					
						
						
						if( $input['subcripiton_status'] != 'All' && $input['subcripiton_status'] != '') {
							$search_string .= " subcripiton_status = '".$input['subcripiton_status']."' AND ";
						}
						
						$search_string = $search_string;
						
						if(!empty($search_string )){
							$search_string = rtrim( $search_string, 'AND ' );
						}						
												
						if($search_string){
							$subslist =  DB::select("SELECT * from subscription_plans WHERE ". $search_string." order by id desc ");
						}else{ 
							$subslist = DB::select("SELECT * from subscription_plans order by id desc ");
						}
						
						$flsubslist = array();
						
						if($subslist){
							foreach($subslist as $ulist){
								
								$totrptcounts = DB::table('user_subscriptions')->where('subscription_id', $ulist->id)->count();	
								$ulist->totreportcounts=$totrptcounts;
								$flsubslist[] = $ulist;
														
							}
						 }
						 
						 
						 
							
						$arrReturn['status'] = "success";
						$arrReturn['sublists'] = $flsubslist;
		}
		                        
    }catch(\Exception $e){	
           $msg= $e->getMessage();
            $arrReturn['status'] = 'failed';
            $arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}
	
	
	
	public function fnSubscriptionDetailsbyId(Request $request)
    {
		try{
		$data=$request->all();
		
		    $input = [
		        'subid' => isset($data['subid']) ? $data['subid'] : '',               			   
            ];
			
		    $rules = array(
               'subid' => 'required',                			   
            );
            
			 $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{

                $subsrecid = $input['subid'];				
                $subsdetails = DB::table('subscription_plans')->where('id', $subsrecid)->first();				
				$arrReturn['status'] = "success";
	       	    $arrReturn['subsdetails'] = $subsdetails ;
		     }
		                        
    }catch(\Exception $e){	
           $msg= $e->getMessage();
            $arrReturn['status'] = 'failed';
            $arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}
	
	
	public function fngetsubscriberslists(Request $request)
    {
		try{
			
			$subsdetails = [];
			$subscriperslist = [];
		    $data=$request->all();
			$totsubscount = 0;
			$totactivesubs = 0;
		
		    $input = [
		        'subid' => isset($data['subid']) ? $data['subid'] : '',
				'subscription_status' => isset($data['subscription_status']) ? $data['subscription_status'] : '1',				
            ];
			
		    $rules = array(
               'subid' => 'required',                			   
            );
            
			 $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{

                $subsrecid = $input['subid'];				
                $subsdetails = DB::table('subscription_plans')->where('id', $subsrecid)->first();
				if($subsdetails){
					
					$totsubscount = DB::table('user_subscriptions')->where('subscription_id', $subsdetails->id)->count();
					$totactivesubs = DB::table('user_subscriptions')->where('subscription_id', $subsdetails->id)->where('subscription_status', 1)->count();
					$subscriperslistnew =  DB::select("SELECT * from user_subscriptions WHERE subscription_id='".$subsdetails->id."' order by id desc ");
					foreach($subscriperslistnew as $slist)  {
						$studentdetails = DB::table('user_profiles')->where('user_id', $slist->user_id)->first();
						if($studentdetails){
							$slist->userfullname = $studentdetails->firstname." ".$studentdetails->lastname;
							$slist->email = $studentdetails->email;
						}						
						$slist->subsperiod = date("d-M-Y",strtotime($slist->subscription_startdate));						    
						$slist->next_renewal_date = date("d-M-Y",strtotime($slist->next_renewal_date));
						$slist->activesttext = ($slist->subscription_status == 1) ?  ((date("d-M-Y") < $slist->subscription_endate) ? 'Active' : 'Completed') : 'Completed';
						$subscriperslist[] = $slist;
					}
				}
				
				$arrReturn['status'] = "success";
	       	    $arrReturn['subsdetails'] = $subsdetails;
				$arrReturn['subscriberslists'] = $subscriperslist;
				$arrReturn['totsubscount'] = $totsubscount;
				$arrReturn['totactivesubs'] = $totactivesubs;
				
		     }
		                        
    }catch(\Exception $e){	
           $msg= $e->getMessage();
            $arrReturn['status'] = 'failed';
            $arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}
	
	
	
	
	public function fnSearchstudentsdetails(Request $request)
    {
		try{
			$data=$request->all();
			$userslist = [];
			$arruserdata = [];
		
			 $input = [ 
						'searchinput' =>	isset($data['searchinput']) ? $data['searchinput'] : ''			   
						];
				
			$rules = array(
				'searchinput' => 'required',  
			);
            
			 $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;
                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{
				
						$search_string = "";						
						
						if( $input['searchinput'] != '') {
							$svalue = $input['searchinput'];
							$search_string .= " firstname like  '%".$svalue."%' OR email like  '%".$svalue."%' OR  phone_number like '%".$svalue."%' ";	
						}						
						$search_string = $search_string;						
						$userslist =  DB::select("SELECT * from user_profiles WHERE user_role in ('student') and ". $search_string);	
						
						if($userslist){
							$arruserdata = $userslist[0];
						}
						
						$arrReturn['status'] = "success";
						$arrReturn['userdata'] = $arruserdata;
		}
		                        
    }catch(\Exception $e){	
           $msg= $e->getMessage();
            $arrReturn['status'] = 'failed';
            $arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}
	
	
	
  public function fnaddsubscriberstoplan(Request $request)
  {
		   $arrReturn = [];		
		   $data=$request->all();
		   $userdata = [];
		   		   
		   try{
				
					$input = [
							'subscription_id' => isset($data['subscription_id']) ? $data['subscription_id'] : 0,							
							'firstname' => isset($data['firstname']) ? $data['firstname'] : '',
							'lastname' => isset($data['lastname']) ? $data['lastname'] : '',
							'email' => isset($data['email']) ? $data['email'] : '',
							'phone_number' => isset($data['phone_number']) ? $data['phone_number'] : '',							
							'paid_amount' => isset($data['paid_amount']) ? $data['paid_amount'] : '',						
							'payment_method' => isset($data['payment_method']) ? $data['payment_method'] : '',
							'admin_userid' => isset($data['admin_userid']) ? $data['admin_userid'] : 0,
							'substartdate' => isset($data['stdate']) ? $data['stdate'] : 0,
							'user_category' => isset($data['user_category']) ? $data['user_category'] : 0,	
						];	
						
					$rules = array(
					   'subscription_id'  => 'required',					  
					   'firstname' => 'required',
					   'lastname' => 'required',
					   'email' =>'required',					   
					   'paid_amount' =>'required',
					   'payment_method' =>'required',
					   'admin_userid' =>'required',
					   'substartdate' =>'required',	
					   'user_category' =>'required',	
					);
					
					
					 $checkValid = Validator::make($input,$rules);

					if ($checkValid->fails()) {					
						$arrErr = $checkValid->errors()->all();
						$arrReturn['status'] = 'failed';
						$arrReturn['message'] = $arrErr;                
						$returnRes=json_encode($arrReturn);
						return $returnRes;
					}else{				
						
						$subsid = $input['subscription_id'];						
						$subscriptiondata = DB::table('subscription_plans')->where('id', $subsid)->first();
						 
						$useremail = $input['email'];
						$verifystudentda = DB::table('users')->where('email', $useremail)->first();	
						if($verifystudentda){
							
							//assign to suscription
							$userdata["subscription_id"] = $subsid;
							$userdata['user_id'] = $verifystudentda->ID;
							$userdata['paid_amount'] = $input['paid_amount'];
							$userdata['discount_amount'] = 0;
							$userdata['discount_code'] = "";
							$userdata['payment_method'] = $input['payment_method'];
							$userdata['substartdate'] = $input['substartdate'];
							$userdata['admin_userid'] = $input['admin_userid'];
							
						}else{
							
							$inputdata = [					        
									'firstname' =>  $input['firstname'],
									'lastname' => $input['lastname'],
									'email' =>  $input['email'],                            
									'password' =>  rand(),
									'role' =>  'Student',		
								   ];
							$rtdata = $this->fnuserregisternew($inputdata);
							
							$inputdata2 = [	
								'firstname' => $input['firstname'],
								'lastname' =>  $input['lastname'],                            
								'email' =>  $input['email'],
								'phone_number' =>  $input['phone_number'], 							
								'user_role' =>  'Student',	
								'user_category' =>  $input['user_category'],
								'admin_userid' =>  $input['admin_userid'],
								'user_category' =>  $input['user_category'],
								'partner_id' =>  0,								
                            ];							
							$rtprodata = $this->fnuserProfileDetails($input['email'],$inputdata2);
							
							$verifystudentda = DB::table('users')->where('email', $useremail)->first();	
							
							//assign to suscription
							$userdata["subscription_id"] = $subsid;
							$userdata['user_id'] = $verifystudentda->ID;
							$userdata['paid_amount'] = $input['paid_amount'];
							$userdata['discount_amount'] = 0;
							$userdata['discount_code'] = "";
							$userdata['payment_method'] = $input['payment_method'];
							$userdata['admin_userid'] = $input['admin_userid'];
							
						}	

//						$usersubscriptiondata =  DB::select("SELECT * from user_subscriptions where user_id = ".$verifystudentda->ID." AND ('".$userdata['substartdate']."' BETWEEN subscription_startdate and subscription_endate OR '".$userdata['subscription_endate']."' BETWEEN subscription_startdate and subscription_endate)");
						$usersubscriptiondata =  DB::select("SELECT * from user_subscriptions where user_id = ".$verifystudentda->ID." AND subscription_id = ".$subsid."");
						 if($usersubscriptiondata){
							 $arrReturn['status'] = "failed";
							 $arrReturn['message'] = "Subscription already exists";
				             $returnRes=json_encode($arrReturn);
							return $returnRes;
						 }
						
						if($subscriptiondata){
							 $this->assignsubscriptiontouser($userdata,$subscriptiondata); 						 
						     $arrReturn['status'] = "success";
							 $arrReturn['message'] = "user assigned successfully";
						 }else{
							 $arrReturn['status'] = "failed";
							 $arrReturn['message'] = "Invalid Subscription";
						 }
					}
			
			
			}catch(\Exception $e){	
			    $msg= $e->getMessage();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
        return $returnRes;
	}
	
	
   
   public function assignsubscriptiontouser($udata,$subscriptiondata){
  
	    try{
				$subsid = $udata['subscription_id'];					
				$subamount = $subscriptiondata->subscription_price;
				$subsstdate ='';
				$subsenddate ='';				
				$subsinterval = 1;
				$paid_amount = $udata['paid_amount'];
				$total_amount = $paid_amount;
				$subsintervaltype = $subscriptiondata->subs_intervaltype;
				
				
				$subsstdate = $udata['substartdate'];
				$subsstdatetimestamp = strtotime($subsstdate);
				if($subsintervaltype == "Monthly"){
					$subsenddate = date('Y-m-d', strtotime('+1 month', $subsstdatetimestamp));
					$subsinterval = 1;
				}
				
				if($subsintervaltype == "Quarterly"){
					$subsenddate = date('Y-m-d', strtotime('+3 month', $subsstdatetimestamp));
					$subsinterval = 3;
					$paid_amount = $paid_amount/3;
				}
				
				if($subsintervaltype == "Yearly"){
					$subsenddate = date('Y-m-d', strtotime('+1 year', $subsstdatetimestamp));
					$subsinterval = 12;
					$paid_amount = $paid_amount/12;
				}
				$subsenddate = date('Y-m-d', strtotime('-1 day', strtotime($subsenddate)));
				$current_time = date('Y-m-d H:i:s');
				$inputdata = [					        
						'user_id' =>  $udata['user_id'],
						'subscription_id' => $subsid,
						'subscription_amount' =>  $subamount, 
						'paid_amount' =>  $udata['paid_amount'],
						'discount_amount' =>  $udata['discount_amount'],
						'discount_code' =>  $udata['discount_code'],
						'payment_method' =>  $udata['payment_method'],
						'subscription_startdate' =>  $subsstdate,
						'subscription_endate' =>  $subsenddate,
						'next_renewal_date' =>  $subsenddate,
						'subscription_status' =>  1,
						'subscribed_datedtime' => $current_time,
						'admin_userid' =>  $udata['admin_userid'],							
                           ];					
				$insertUser = DB::table('user_subscriptions')->insertGetId($inputdata);			  
				$enddate = date("Y-m-t", strtotime($subsstdate));
				
				$user_id = $udata['user_id'];

				while($subsstdate < $subsenddate){
					if($enddate>$subsenddate){
						$enddate = $subsenddate;
					}
					$date1 = new DateTime($subsstdate);
					$date2 = new DateTime($enddate);
					$days  = $date2->diff($date1)->format('%a')+1;
					$monthDays = date("t", strtotime($enddate));

					$month_amount = 0;
					if($enddate==$subsenddate){
						$month_amount = $total_amount;
					}elseif(date("j", strtotime($subsstdate)) != 1){
						$usersubscriptiondata =  DB::select("SELECT * from user_subscriptions_payment where user_id = ".$user_id." AND DATE_ADD(subscription_enddate,INTERVAL 1 DAY) ='".$subsstdate."' AND subscription_id = ".$subsid."");
						if($usersubscriptiondata){
							$month_amount = $paid_amount - $usersubscriptiondata[0]->paid_amount;
							$total_amount = $total_amount - $month_amount;
						}else{
							$month_amount = $paid_amount*$days/$monthDays;
							$total_amount = $total_amount - $month_amount;
						}
					}else{
						$month_amount = $paid_amount*$days/$monthDays;
						$total_amount = $total_amount - $month_amount;
					}
					
					$inputdata = [					        
							'user_id' =>  $udata['user_id'],
							'subscription_id' => $subsid,
							'subscription_amount' =>  $subamount, 
							'paid_amount' =>  $month_amount,
							'discount_amount' =>  $udata['discount_amount'],
							'discount_code' =>  $udata['discount_code'],
							'payment_method' =>  $udata['payment_method'],
							'subscription_startdate' =>  $subsstdate,
							'subscription_enddate' =>  $enddate,
							'subscription_status' =>  1,
							'payment_date' =>  $current_time,
							'admin_userid' =>  $udata['admin_userid'],							
							   ];					
					$insertUser = DB::table('user_subscriptions_payment')->insertGetId($inputdata);			  
					$subsstdate = date('Y-m-01', strtotime($subsstdate));
					$nextMonth = strtotime('+1 month', strtotime($subsstdate));
					$subsstdate = date('Y-m-01', $nextMonth);
					$enddate = date("Y-m-t", strtotime($subsstdate));
				}
		  
		}catch(\Exception $e){	
			    $msg = $e->getMessage();				
        }
	  
	  
  }
  
  
  
  public function fngetcourseparams(Request $request)
    {
		try{
		$data=$request->all();
		$userslist = [];
		
		    $input = [				
		       'active_status' => isset($data['active_status']) ? $data['active_status'] : 'All', 
            ];
			
			$rules = array(
                      			   
            );
            
			 $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;
                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{
				
						$search_string = "";					
						$department =  DB::select("SELECT * from course_departments order by id desc ");
						$subjects =  DB::select("SELECT * from course_subjects order by id desc ");
						$courselevels =  DB::select("SELECT * from course_levels order by id desc ");	
						$coursepartners =  DB::select("SELECT * from course_partners where active_status=1 order by id asc ");						
						$courselocations =  DB::select("SELECT * from class_locations where location_status = 1 order by id asc ");
						$coursetutors =  DB::select("SELECT * from user_profiles where user_role = 'teacher' order by id asc ");
						 							
						$arrReturn['status'] = "success";
						$arrReturn['department'] = $department;
						$arrReturn['subjects'] = $subjects;
						$arrReturn['courselevels'] = $courselevels;
						$arrReturn['partners'] = $coursepartners;
						$arrReturn['locations'] = $courselocations;
						$arrReturn['tutors'] = $coursetutors;
		}
		                        
    }catch(\Exception $e){	
           $msg= $e->getMessage();
            $arrReturn['status'] = 'failed';
            $arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}
	
	
	public function fngetcourseclassesbyid(Request $request)
    {
		
		$classcounts = 0;
		$totstudents = 0;
		$classdata = [];
		
		try{
			
			 $data=$request->all();		
			 $input = [
					'courseid' => isset($data['courseid']) ? $data['courseid'] : '',               			   
				];
				
			 $rules = array(
				   'courseid' => 'required',								   
				);
            
			 $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{

                $course_id = $input['courseid'];				
                $coursedetails = DB::table('courses')->where('id', $course_id)->first();

				if($coursedetails){
					
					$crsstdate = date("d-M-Y",strtotime($coursedetails->course_start_date));
					$crseddate = date("d-M-Y",strtotime($coursedetails->course_end_date));
					$coursedetails->course_start_date = $crsstdate;
					$coursedetails->course_end_date = $crseddate;
					
				}
					
				
				$courseclasses =  DB::select("SELECT * from course_classes where course_id='".$course_id ."' order by id asc ");
				if($courseclasses){
					$classcounts = count($courseclasses);
					foreach($courseclasses as $classes){
						
						$tutors_id = '';
						$location_id = '';
						
						$tutorsdata = json_decode($classes->class_tutors);													
						$tutors_id = ($tutorsdata) ?  implode(",",$tutorsdata) : '';						
						
						$locationdata = json_decode($classes->class_location);
						$location_id = ($locationdata)?  implode(",",$locationdata) : '';

						
						$arrlocationtext = [];						
						if($location_id){
							$locationdetails =  DB::select("SELECT * from class_locations where id in (".$location_id.")");
							if($locationdetails){
								
								foreach($locationdetails as $locdata){
									$arrlocationtext[] = $locdata->name;
								}
								
							}
						}	
						
						$locationtextg = (count($arrlocationtext) > 0) ? implode(",",$arrlocationtext) : '';
						
												
						$arrtutortext = [];						
						if($tutors_id){
							$tutordeatils =  DB::select("SELECT * from user_profiles where user_id in (".$tutors_id.")");
							if($tutordeatils){
								
								foreach($tutordeatils as $tutor){
									$arrtutortext[] = $tutor->firstname;
								}
								
							}
						}
						
						$tutordislaytext = (count($arrtutortext) > 0) ? implode(",",$arrtutortext) : '';
						
						$totalstudents = DB::table('user_class_mapping')->where('class_id', $classes->id)->where('course_id', $classes->course_id)->count();
						
						$classes->stdatetext = date("d-M-Y",strtotime($classes->class_date)).", ".date("D",strtotime($classes->class_date))." ".$classes->class_start_time. " to ".$classes->class_end_time.",".$classes->class_duration." Minutes";
						$classes->tutortext = $tutordislaytext;
						$classes->locationtext = $locationtextg;
						$classes->totalstudents = $totalstudents;
						$classes->classstatus = ($classes->class_status == 1 ) ? 'Yet to start' : 'Completed';
						$classdata[] = $classes;
						
					}
				}				
				
				$totstudents = DB::table('user_class_mapping')->where('course_id', $course_id)->distinct('user_id')->count('user_id');
				
				$arrReturn['status'] = "success";
				$arrReturn['classcounts'] = $classcounts;
				$arrReturn['totalstudents'] = $totstudents;
	       	    $arrReturn['coursedetails'] = $coursedetails;
				$arrReturn['classes'] = $classdata;
		     }
		                        
    }catch(\Exception $e){	
           $msg= $e->getMessage();
            $arrReturn['status'] = 'failed';
            $arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}
	
	
	
	public function fngetassignedstudentsbyclass(Request $request)
    {
		
		$classcounts = 0;
		$totstudents = 0;
		$classdata = [];
		$arrReturn = [];
		$coursedetails =[];
		$assignedstudents = [];
		
		try{
			
			 $data=$request->all();		
			 $input = [
					'classid' => isset($data['classid']) ? $data['classid'] : '',               			   
				];
				
			 $rules = array(
				   'classid' => 'required',								   
				);
            
			 $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{			

                $classid = $input['classid'];
				$classdetails = DB::table('course_classes')->where('id', $classid)->first();

				if($classdetails){
					
					$tutors_id = '';
						$location_id = '';
						
						$tutorsdata = json_decode($classdetails->class_tutors);													
						$tutors_id = ($tutorsdata) ?  implode(",",$tutorsdata) : '';						
						
						$locationdata = json_decode($classdetails->class_location);
						$location_id = ($locationdata)?  implode(",",$locationdata) : '';

						
						$arrlocationtext = [];						
						if($location_id){
							$locationdetails =  DB::select("SELECT * from class_locations where id in (".$location_id.")");
							if($locationdetails){
								
								foreach($locationdetails as $locdata){
									$arrlocationtext[] = $locdata->name;
								}
								
							}
						}	
						
						$locationtextg = (count($arrlocationtext) > 0) ? implode(",",$arrlocationtext) : '';
						
												
						$arrtutortext = [];						
						if($tutors_id){
							$tutordeatils =  DB::select("SELECT * from user_profiles where user_id in (".$tutors_id.")");
							if($tutordeatils){
								
								foreach($tutordeatils as $tutor){
									$arrtutortext[] = $tutor->firstname;
								}
								
							}
						}
						
						$tutordislaytext = (count($arrtutortext) > 0) ? implode(",",$arrtutortext) : '';
						
						$classdetails->stdatetext = date("d-M-Y",strtotime($classdetails->class_date)).", ".date("D",strtotime($classdetails->class_date))." ".$classdetails->class_start_time. " to ".$classdetails->class_end_time.",".$classdetails->class_duration." Minutes";
						$classdetails->tutortext = $tutordislaytext;
						$classdetails->locationtext = $locationtextg;
						$classdetails->totalstudents = 0;
						$classdetails->classstatus = ($classdetails->class_status == 1 ) ? 'Yet to start' : 'Completed';
						
						
						$course_id = $classdetails->course_id;				
						$coursedetails = DB::table('courses')->where('id', $course_id)->first();

						if($coursedetails){							
							$classdetails->coursename = $coursedetails->course_title;							
						}
						
						$assignedstudentdeatils =  DB::select("SELECT * from user_class_mapping where course_id='".$course_id."' and class_id='".$classdetails->id."'");
						
						
						
						if($assignedstudentdeatils){
							foreach($assignedstudentdeatils as $astudents){
								
								$studentdata = DB::table('user_profiles')->where('user_id', $astudents->user_id)->first();
								if($studentdata){
									$objasstudent = new stdClass();
									$objasstudent->name = $studentdata->firstname." ".$studentdata->lastname;
									$objasstudent->email = $studentdata->email;
									$objasstudent->phonenumber = $studentdata->phone_number;
									$objasstudent->attendclass = $astudents->attend_class;
									$objasstudent->recid = $astudents->id;
									$assignedstudents[] = $objasstudent;		
								}
								
							}
						}

						$classdetails->totalstudents = count($assignedstudentdeatils);						
						
					
					
				}
				
				
				$arrReturn['status'] = "success";
				$arrReturn['classdetails'] = $classdetails;
				$arrReturn['assignedstudents'] = $assignedstudents;
			
				
                
		     }
		                        
    }catch(\Exception $e){	
           $msg= $e->getMessage();
            $arrReturn['status'] = 'failed';
            $arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}
	
	
	
  public function fnassignstutoclass(Request $request)
  {
		   $arrReturn = [];		
		   $data=$request->all();
		   $userdata = [];
		   		   
		   try{
				
					$input = [
							'class_id' => isset($data['class_id']) ? $data['class_id'] : 0,							
							'firstname' => isset($data['firstname']) ? $data['firstname'] : '',
							'lastname' => isset($data['lastname']) ? $data['lastname'] : '',
							'email' => isset($data['email']) ? $data['email'] : '',
							'phone_number' => isset($data['phone_number']) ? $data['phone_number'] : '',							
							'paid_amount' => isset($data['paid_amount']) ? $data['paid_amount'] : '',						
							'payment_method' => isset($data['payment_method']) ? $data['payment_method'] : '',
							'admin_userid' => isset($data['admin_userid']) ? $data['admin_userid'] : 0,							
							'user_category' => isset($data['user_category']) ? $data['user_category'] : '',
							'attend_class' => isset($data['attend_class']) ? $data['attend_class'] : 0,								
						];
						
					$rules = array(
					   'class_id'  => 'required',					  
					   'firstname' => 'required',
					   'lastname' => 'required',
					   'email' =>'required',					  
					   'paid_amount' =>'required',
					   'payment_method' =>'required',
					   'admin_userid' =>'required',	
					);
					
					
					 $checkValid = Validator::make($input,$rules);

					if ($checkValid->fails()) {					
						$arrErr = $checkValid->errors()->all();
						$arrReturn['status'] = 'failed';
						$arrReturn['message'] = $arrErr;                
						$returnRes=json_encode($arrReturn);
						return $returnRes;
					}else{				
						
						$classid = $input['class_id'];
						$useremail = $input['email'];
						
						$classdetails = DB::table('course_classes')->where('id', $classid)->first();
						$courseid = $classdetails->course_id;
						
						$verifystudentda = DB::table('users')->where('email', $useremail)->first();	
						if($verifystudentda){
							
							//assign to suscription
							$userdata["class_id"] = $classid;
							$userdata["course_id"] = $courseid;
							$userdata['user_id'] = $verifystudentda->ID;
							$userdata['paid_amount'] = $input['paid_amount'];
							$userdata['discount_amount'] = 0;
							$userdata['discount_code'] = "";
							$userdata['payment_method'] = $input['payment_method'];
							$userdata['admin_userid'] = $input['admin_userid'];
							$userdata['attend_class'] = $input['attend_class'];
							
						}else{
							
							$inputdata = [					        
									'firstname' =>  $input['firstname'],
									'lastname' => $input['lastname'],
									'email' =>  $input['email'],                            
									'password' =>  rand(),
									'role' =>  'student',		
								   ];
							$rtdata = $this->fnuserregisternew($inputdata);
							
							$inputdata2 = [	
								'firstname' => $input['firstname'],
								'lastname' =>  $input['lastname'],                            
								'email' =>  $input['email'],
								'phone_number' =>  $input['phone_number'], 							
								'user_role' =>  'student',	
								'user_category' =>  $input['user_category'],
								'admin_userid' =>  $input['admin_userid'],
								'partner_id' =>  0,
                            ];							
							$rtprodata = $this->fnuserProfileDetails($input['email'],$inputdata2);
							
							$verifystudentda = DB::table('users')->where('email', $useremail)->first();	
							
							//assign to suscription
							$userdata["class_id"] = $classid;
							$userdata["course_id"] = $courseid;
							$userdata['user_id'] = $verifystudentda->ID;
							$userdata['paid_amount'] = $input['paid_amount'];
							$userdata['discount_amount'] = 0;
							$userdata['discount_code'] = "";
							$userdata['payment_method'] = $input['payment_method'];
							$userdata['admin_userid'] = $input['admin_userid'];
							$userdata['attend_class'] = $input['attend_class'];
							
						}
						
						   $this->assignusertoclass($userdata);
						   $arrReturn['status'] = "success";
						   $arrReturn['message'] = "user assigned successfully";						   
						
						
					}
					
					
			
			
			}catch(\Exception $e){	
			    $msg= $e->getMessage();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
        return $returnRes;
	}
	
	
    public function assignusertoclass($data)
    {
		try{
				
			$inputdata = [	
							'user_id' => $data['user_id'],
                            'course_id' =>  $data['course_id'],                            
         					'class_id' =>  $data['class_id'],
							'paid_amount' =>  $data['paid_amount'], 							
							'payment_source' =>  $data['payment_method'],	
							'admin_userid' =>  $data['admin_userid'],
							'attend_class' =>  $data['attend_class'],							
                           ];		
			
			
			$verifyUser = DB::table('user_class_mapping')->where('course_id', $inputdata['course_id'])->where('class_id', $inputdata['class_id'])->where('user_id', $inputdata['user_id'])->first();
            if($verifyUser == ''){				                     		   
				$insertUser = DB::table('user_class_mapping')->insertGetId($inputdata);
            }else{
				$updateUser = DB::table('user_class_mapping')->where('id', $verifyUser->id)->update($inputdata);
            }
		}catch(\Exception $e){	
            $msg= $e->getMessage();              	
        }
		
	}
	
	
	
	public function fnassignstutocourse(Request $request)
  {
		   $arrReturn = [];		
		   $data=$request->all();
		   $userdata = [];
		   		   
		   try{
				
					$input = [
							'course_id' => isset($data['course_id']) ? $data['course_id'] : '',
							'firstname' => isset($data['firstname']) ? $data['firstname'] : '',
							'lastname' => isset($data['lastname']) ? $data['lastname'] : '',
							'email' => isset($data['email']) ? $data['email'] : '',
							'phone_number' => isset($data['phone_number']) ? $data['phone_number'] : '',							
							'paid_amount' => isset($data['paid_amount']) ? $data['paid_amount'] : '',						
							'payment_method' => isset($data['payment_method']) ? $data['payment_method'] : '',
							'admin_userid' => isset($data['admin_userid']) ? $data['admin_userid'] : 0,							
							'user_category' => isset($data['user_category']) ? $data['user_category'] : '',
							'attend_class' => isset($data['attend_class']) ? $data['attend_class'] : 0,	
							'class_ids' => isset($data['class_ids']) ? $data['class_ids'] : '',
							'allclassflag' => isset($data['allclassflag']) ? $data['allclassflag'] : 0,
						];
						
					$rules = array(
					   'course_id' => 'required',				
					   'firstname' => 'required',
					   'lastname' => 'required',
					   'email' =>'required',					   
					   'paid_amount' =>'required',
					   'payment_method' =>'required',
					   'admin_userid' =>'required',					  
					   'user_category' =>'required',	
					);
					
					
					 $checkValid = Validator::make($input,$rules);

					if ($checkValid->fails()) {					
						$arrErr = $checkValid->errors()->all();
						$arrReturn['status'] = 'failed';
						$arrReturn['message'] = $arrErr;                
						$returnRes=json_encode($arrReturn);
						return $returnRes;
					}else{							
						$useremail = $input['email'];
						$course_id = $input['course_id'];
						
						
						$verifystudentda = DB::table('users')->where('email', $useremail)->first();	
						if($verifystudentda){
							
							//assign to suscription
							
							
							$userdata['course_id'] = $course_id;
							$userdata['user_id'] = $verifystudentda->ID;
							$userdata['paid_amount'] = $input['paid_amount'];
							$userdata['discount_amount'] = 0;
							$userdata['discount_code'] = "";
							$userdata['payment_method'] = $input['payment_method'];
							$userdata['admin_userid'] = $input['admin_userid'];
							$userdata['class_ids'] = $input['class_ids'];
							$userdata['allclassflag'] = $input['allclassflag'];
							
						}else{
							
							$inputdata = [					        
									'firstname' =>  $input['firstname'],
									'lastname' => $input['lastname'],
									'email' =>  $input['email'],                            
									'password' =>  rand(),
									'role' =>  'student',		
								   ];
							$rtdata = $this->fnuserregisternew($inputdata);
							
							$inputdata2 = [	
								'firstname' => $input['firstname'],
								'lastname' =>  $input['lastname'],                            
								'email' =>  $input['email'],
								'phone_number' =>  $input['phone_number'], 							
								'user_role' =>  'student',	
								'user_category' =>  $input['user_category'],
								'partner_id' =>  0,	
								'admin_userid' =>  $input['admin_userid'],
                            ];

							
							$rtprodata = $this->fnuserProfileDetails($input['email'],$inputdata2);
							
							$verifystudentda = DB::table('users')->where('email', $useremail)->first();	
							
							//assign to suscription	
							$userdata['course_id'] = $course_id;							
							$userdata['user_id'] = $verifystudentda->ID;
							$userdata['paid_amount'] = $input['paid_amount'];
							$userdata['discount_amount'] = 0;
							$userdata['discount_code'] = "";
							$userdata['payment_method'] = $input['payment_method'];
							$userdata['admin_userid'] = $input['admin_userid'];
							$userdata['class_ids'] = $input['class_ids'];
							$userdata['allclassflag'] = $input['allclassflag'];
							
							
						}					
						 
						   
						   $this->assignusertocourse($userdata);						   
						   
						   $arrReturn['status'] = "success";
						   $arrReturn['message'] = "user assigned successfully";						   
						
						
					}
					
					
			
			
			}catch(\Exception $e){	
			    $msg= $e->getMessage();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
        return $returnRes;
	}
	
	
	
	public function assignusertocourse($data)
    {
		try{			
			$course_id = $data['course_id'];
			$allflag = $data['allclassflag'];	
			$scheduledactivecourse = [];
			
			if($allflag == 1){
				$scheduledactivecourse =  DB::select("SELECT * from course_classes where course_id='".$course_id."' and class_status = 1");
			}else{				
				$selclasses = ($data['class_ids']) ? json_decode($data['class_ids']) : [];
				if(count($selclasses) > 0){
					$selclasses  = implode(",",$selclasses);					
					$scheduledactivecourse =  DB::select("SELECT * from course_classes where course_id='".$course_id."' and class_status = 1 and id in (".$selclasses.")");
				}else{
					$scheduledactivecourse =  DB::select("SELECT * from course_classes where course_id='".$course_id."' and class_status = 1");
				}			
			}			
			
			foreach($scheduledactivecourse as $class){
				
				$userassigndata = [	
							'user_id' => $data['user_id'],
                            'course_id' =>  $data['course_id'],                            
         					'class_id' =>  $class->id,
							'paid_amount' =>  $data['paid_amount'], 							
							'payment_method' =>  $data['payment_method'],	
							'admin_userid' =>  $data['admin_userid'],
							'attend_class' =>  0,							
                           ];		
				$this->assignusertoclass($userassigndata);
			}				
			
		}catch(\Exception $e){	
            $msg= $e->getMessage();
			
        }
		
	}
	
	
	public function fngetcoursedetailsbyid(Request $request)
    {
		
		$classcounts = 0;
		$totstudents = 0;
		$classdata = [];
		
		try{
			
			 $data=$request->all();		
			 $input = [
					'courseid' => isset($data['course_id']) ? $data['course_id'] : '',               			   
				];
				
			 $rules = array(
				   'courseid' => 'required',								   
				);
            
			 $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{

                $course_id = $input['courseid'];				
                $coursedetails = DB::table('courses')->where('id', $course_id)->first();

				if($coursedetails){					
					$crsstdate = date("Y-m-d",strtotime($coursedetails->course_start_date));
					$crseddate = date("Y-m-d",strtotime($coursedetails->course_end_date));
					$coursedetails->course_start_date = $crsstdate;
					$coursedetails->course_end_date = $crseddate;	
					$coursedetails->course_location = json_decode($coursedetails->course_location);
					$coursedetails->course_tutors = json_decode($coursedetails->course_tutors);
					$coursedetails->course_intervalday = json_decode($coursedetails->course_intervalday);					
				}
				
				$arrReturn['status'] = "success";
	       	    $arrReturn['coursedetails'] = $coursedetails;
				
		     }
		                        
    }catch(\Exception $e){	
           $msg= $e->getMessage();
            $arrReturn['status'] = 'failed';
            $arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}
	
	
	
	public function fnassignattendance(Request $request)
    {
		
		$classcounts = 0;
		$totstudents = 0;
		$classdata = [];
		$arrReturn = [];
		try{
			
			 $data=$request->all();		
			 $input = [
					'recid' => isset($data['recid']) ? $data['recid'] : '',
					'attend_flag' => isset($data['attend_flag']) ? $data['attend_flag'] : '', 	
				];
				
			 $rules = array(
				   'recid' => 'required',	
				   'attend_flag' => 'required',				   
				);
            
			 $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{
				$updaterecid = $input['recid'];
                $uprecdata = ['attend_class' => $input['attend_flag']];	
				$updateUser = DB::table('user_class_mapping')->where('id', $updaterecid)->update($uprecdata);
				$arrReturn['status'] = "success";
				$arrReturn['message'] = "attendance updated successfully";
		     }
		                        
			}catch(\Exception $e){	
				$msg= $e->getMessage();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}
	
	
	
	public function fnsearchclassebyfilter(Request $request)
    {
		
		$classcounts = 0;
		$totstudents = 0;
		$classdata = [];
		$arrReturn = [];
		$courseclasses = [];
		try{
			
			 $data=$request->all();		
			 $input = [
					'classstatus' => isset($data['classstatus']) ? $data['classstatus'] : 1,
					'classstartdate' => isset($data['classstartdate']) ? $data['classstartdate'] : '',
					'classenddate' => isset($data['classenddate']) ? $data['classenddate'] : '',
					'tutor_id' => isset($data['tutor_id']) ? $data['tutor_id'] : 0,					
				];				
				
				
			 $rules = array(
				   'classstatus' => 'required',	
				   'classstartdate' => 'required',
				   'classenddate' => 'required',				   
				);
            
			 $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{
				
				$classtdate = $input['classstartdate'];
				$classenddate = $input['classenddate'];
				$tutor_id = $input['tutor_id'];
							
				
				$courseclasses =  DB::select("SELECT * FROM course_classes WHERE class_date >= '".$classtdate."' AND class_date <= '".$classenddate."' ORDER BY class_date ASC");
				
				if($courseclasses){
					$classcounts = count($courseclasses);
					foreach($courseclasses as $classes){
						
						$tutors_id = '';
						$location_id = '';
						
						$tutorsdata = json_decode($classes->class_tutors);													
						$tutors_id = ($tutorsdata) ?  implode(",",$tutorsdata) : '';						
						
						$locationdata = json_decode($classes->class_location);
						$location_id = ($locationdata)?  implode(",",$locationdata) : '';

						
						$arrlocationtext = [];						
						if($location_id){
							$locationdetails =  DB::select("SELECT * from class_locations where id in (".$location_id.")");
							if($locationdetails){
								
								foreach($locationdetails as $locdata){
									$arrlocationtext[] = $locdata->name;
								}
								
							}
						}	
						
						$locationtextg = (count($arrlocationtext) > 0) ? implode(",",$arrlocationtext) : '';
						
												
						$arrtutortext = [];						
						if($tutors_id){
							$tutordeatils =  DB::select("SELECT * from user_profiles where user_id in (".$tutors_id.")");
							if($tutordeatils){
								
								foreach($tutordeatils as $tutor){
									$arrtutortext[] = $tutor->firstname;
								}
								
							}
						}
						
						$tutordislaytext = (count($arrtutortext) > 0) ? implode(",",$arrtutortext) : '';
						
						$totalstudents = DB::table('user_class_mapping')->where('class_id', $classes->id)->where('course_id', $classes->course_id)->count();
						
						$classes->stdatetext = date("d-M-Y",strtotime($classes->class_date)).", ".date("D",strtotime($classes->class_date))." ".$classes->class_start_time. " to ".$classes->class_end_time.",".$classes->class_duration." Minutes";
						$classes->tutortext = $tutordislaytext;
						$classes->locationtext = $locationtextg;
						$classes->totalstudents = $totalstudents;
						$classes->classstatus = ($classes->class_status == 1 ) ? 'Yet to start' : 'Completed';
							
						$coursedetails = DB::table('courses')->where('id', $classes->course_id)->first();
						
						if($coursedetails){
							$classes->coursename = $coursedetails->course_title;
						}else{
							$classes->coursename = "";
						}	

						if($tutor_id  != 0){								
							$claasstutors = ($classes->class_tutors) ? json_decode($classes->class_tutors) : '';									
							if(in_array($tutor_id,$claasstutors)){
								$classdata[] = $classes;
							}
								
						}else{
								$classdata[] = $classes;
						}		
						
						
						
					}
				}
				
				
				$arrReturn['status'] = "success";
	       	    $arrReturn['classlist'] = $classdata;			
								
		     }
		                        
			}catch(\Exception $e){	
				$msg= $e->getMessage();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}
	
	
	
	public function fngetassignedstudentsbycourse(Request $request)
    {
		
		$classcounts = 0;
		$totstudents = 0;
		$classdata = [];
		$arrReturn = [];
		$assignedstudents =[];
		
		try{
			
			 $data=$request->all();		
			 $input = [
					'course_id' => isset($data['course_id']) ? $data['course_id'] : '',					
				];
				
			 $rules = array(
				   'course_id' => 'required',
				);
            
			 $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{
				
						$course_id = $input['course_id'];				
						$assignedstudentdeatils =  DB::select("SELECT distinct(user_id) as user_id from user_class_mapping where course_id='".$course_id."'");						
											
						if($assignedstudentdeatils){
							foreach($assignedstudentdeatils as $astudents){
								
								$studentdata = DB::table('user_profiles')->where('user_id', $astudents->user_id)->first();
								if($studentdata){
									$totalclassbooked = DB::table('user_class_mapping')->where('user_id', $astudents->user_id)->where('course_id', $course_id)->count();
									$totclassattended = DB::table('user_class_mapping')->where('attend_class', 1)->where('user_id', $astudents->user_id)->where('course_id', $course_id)->count();
									
									$objasstudent = new stdClass();
									$objasstudent->name = $studentdata->firstname." ".$studentdata->lastname;
									$objasstudent->email = $studentdata->email;
									$objasstudent->phonenumber = $studentdata->phone_number;
									$objasstudent->totclassbooked = $totalclassbooked;
									$objasstudent->totclassattended = $totclassattended;
									$objasstudent->userid = $astudents->user_id;									
									$assignedstudents[] = $objasstudent;		
								}
								
							}						
							
						}
						
						$arrReturn['status'] = "success";
						$arrReturn['assignedstudents'] = $assignedstudents;
				
		     }
		                        
			}catch(\Exception $e){	
				$msg= $e->getMessage();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}
	
	
	
	public function fnattendancereportbyfilter(Request $request)
    {
		
		$classcounts = 0;
		$totstudents = 0;
		$classdata = [];
		$arrReturn = [];
		$assignedstudents =[];
		$stdata = [];
		$attendclass = [];
		$notattendedclass = [];
		
		try{
			
			 $data=$request->all();		
			 $input = [
					'searchterm' => isset($data['searchterm']) ? $data['searchterm'] : '',
					'stdatefrom' => isset($data['stdatefrom']) ? $data['stdatefrom'] : '',
					'stdateto' => isset($data['stdateto']) ? $data['stdateto'] : '',
				];
				
			 $rules = array(
				   'searchterm' => 'required',
				);
            
			 $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{
				
				$searchterm = $input['searchterm'];
				$classtdate = $input['stdatefrom'];
				$classenddate = $input['stdateto'];
				
				$studentdata = DB::select("SELECT * from user_profiles WHERE email ='".$searchterm."' or phone_number='".$searchterm."'");
						
				if($studentdata){					
					$stdata = $studentdata[0];	
					$userid = $stdata->user_id;										
					
				$courseclasses =  DB::select("SELECT * FROM course_classes WHERE class_date >= '".$classtdate."' AND class_date <= '".$classenddate."' ORDER BY class_date ASC;");
				if($courseclasses){
					$classcounts = count($courseclasses);
					foreach($courseclasses as $classes){
						
						$tutors_id = '';
						$location_id = '';
						
						$tutorsdata = json_decode($classes->class_tutors);													
						$tutors_id = ($tutorsdata) ?  implode(",",$tutorsdata) : '';						
						
						$locationdata = json_decode($classes->class_location);
						$location_id = ($locationdata)?  implode(",",$locationdata) : '';

						
						$arrlocationtext = [];						
						if($location_id){
							$locationdetails =  DB::select("SELECT * from class_locations where id in (".$location_id.")");
							if($locationdetails){
								
								foreach($locationdetails as $locdata){
									$arrlocationtext[] = $locdata->name;
								}
								
							}
						}	
						
						$locationtextg = (count($arrlocationtext) > 0) ? implode(",",$arrlocationtext) : '';
						
												
						$arrtutortext = [];						
						if($tutors_id){
							$tutordeatils =  DB::select("SELECT * from user_profiles where user_id in (".$tutors_id.")");
							if($tutordeatils){
								
								foreach($tutordeatils as $tutor){
									$arrtutortext[] = $tutor->firstname;
								}
								
							}
						}
						
						$tutordislaytext = (count($arrtutortext) > 0) ? implode(",",$arrtutortext) : '';
						
						//$totalstudents = DB::table('user_class_mapping')->where('class_id', $classes->id)->where('course_id', $classes->course_id)->count();
						
						$classes->stdatetext = date("d-M-Y",strtotime($classes->class_date)).", ".date("D",strtotime($classes->class_date))." ".$classes->class_start_time. " to ".$classes->class_end_time.",".$classes->class_duration." Minutes";
						$classes->tutortext = $tutordislaytext;
						$classes->locationtext = $locationtextg;
						//$classes->totalstudents = $totalstudents;
						
						$clsdate = strtotime($classes->class_date);
						$nowdate = strtotime(date('Y-m-d'));
						
						if($nowdate > $clsdate){
							$classes->classstatus = 'Completed';
						}else{
							$classes->classstatus = ($classes->class_status == 1 ) ? 'Yet to start' : 'Completed';
						}						
							
						$coursedetails = DB::table('courses')->where('id', $classes->course_id)->first();
						
						if($coursedetails){
							$classes->coursename = $coursedetails->course_title;
						}else{
							$classes->coursename = "";
						}					
						
						$classattenedstatus = DB::table('user_class_mapping')->where('attend_class', 1)->where('user_id', $userid)->where('class_id', $classes->id)->where('course_id', $classes->course_id)->count();
						//echo $classattenedstatus; die;
						if($classattenedstatus == 0){
							$classes->attendedflag = 0;
							$notattendedclass[] = $classes;
						}else{
							$classes->attendedflag = 1;
							$attendclass[] = $classes;
						}						
						
					}
				}						
					
				}
				
				$arrReturn['status'] = "success";
				$arrReturn['studentdetails'] = $stdata;
				$arrReturn['attendedclass'] = $attendclass;
				$arrReturn['notattendedclass'] = $notattendedclass;
				$arrReturn['attendclascount'] = count($attendclass);
				$arrReturn['notattendclascount'] = count($notattendedclass);
				
		     }
		                        
			}catch(\Exception $e){	
				$msg= $e->getMessage();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}
	
	
	public function fnuserassignattendance(Request $request)
    {
		
		$classcounts = 0;
		$totstudents = 0;
		$classdata = [];
		$arrReturn = [];
		try{
			
			 $data=$request->all();		
			 $input = [
					'userid' => isset($data['userid']) ? $data['userid'] : '',
					'classid' => isset($data['classid']) ? $data['classid'] : '',
					'attend_flag' => isset($data['attend_flag']) ? $data['attend_flag'] : '',
					'admin_userid' => isset($data['admin_userid']) ? $data['admin_userid'] : 0,	
				];			
				
			 $rules = array(
				   'userid' => 'required',	
				   'classid' => 'required',
				   'attend_flag' => 'required',				   
				);
            
			 $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{			
				
				$classid = $input['classid'];				
                $classdetails = DB::table('course_classes')->where('id', $classid)->first();
				if($classdetails){
					
                     $courseid = $classdetails->course_id;
					 
					 
					 $inputdata = [	
							'user_id' => $input['userid'],
                            'course_id' =>  $courseid,                            
         					'class_id' =>  $classid,
							'paid_amount' =>  $classdetails->class_price, 							
							'payment_method' =>  'Manual',	
							'admin_userid' =>  $input['admin_userid'],
							'attend_class' =>  $input['attend_flag'],							
                           ];		
					 
					$this->assignusertoclass($inputdata);
					$arrReturn['status'] = "success";
					$arrReturn['message'] = "user assigned successfully";		
						
				}
				
		     }
		                        
			}catch(\Exception $e){	
				$msg= $e->getMessage();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}
	
	
	public function fngetuserbookedclasses(Request $request)
    {
		
		$classcounts = 0;
		$totstudents = 0;
		$classdata = [];
		$arrReturn = [];
		$assignedstudents =[];
		$stdata = [];
		$attendclass = [];
		$notattendedclass = [];
		
		try{
			
			 $data=$request->all();		
			 $input = [
					'searchterm' => isset($data['searchterm']) ? $data['searchterm'] : '',					
				];
				
			 $rules = array(
				   'searchterm' => 'required',
				);
            
			 $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{
				
				$searchterm = $input['searchterm'];
				
				
				$studentdata = DB::select("SELECT * from user_profiles WHERE email ='".$searchterm."' or phone_number='".$searchterm."'");
						
				if($studentdata){					
					$stdata = $studentdata[0];	
					$userid = $stdata->user_id;										
					
				$courseclasses =  DB::select("SELECT * FROM user_class_mapping WHERE user_id = '".$userid."' ORDER BY id ASC;");
				if($courseclasses){
					
					foreach($courseclasses as $clsnew){
						
						
						$classes = DB::table('course_classes')->where('id', $clsnew->class_id)->first();
						$tutors_id = '';
						$location_id = '';
						
						$tutorsdata = json_decode($classes->class_tutors);													
						$tutors_id = ($tutorsdata) ?  implode(",",$tutorsdata) : '';						
						
						$locationdata = json_decode($classes->class_location);
						$location_id = ($locationdata)?  implode(",",$locationdata) : '';

						
						$arrlocationtext = [];						
						if($location_id){
							$locationdetails =  DB::select("SELECT * from class_locations where id in (".$location_id.")");
							if($locationdetails){
								
								foreach($locationdetails as $locdata){
									$arrlocationtext[] = $locdata->name;
								}
								
							}
						}	
						
						$locationtextg = (count($arrlocationtext) > 0) ? implode(",",$arrlocationtext) : '';
						
												
						$arrtutortext = [];						
						if($tutors_id){
							$tutordeatils =  DB::select("SELECT * from user_profiles where user_id in (".$tutors_id.")");
							if($tutordeatils){
								
								foreach($tutordeatils as $tutor){
									$arrtutortext[] = $tutor->firstname;
								}
								
							}
						}
						
						$tutordislaytext = (count($arrtutortext) > 0) ? implode(",",$arrtutortext) : '';
						
						//$totalstudents = DB::table('user_class_mapping')->where('class_id', $classes->id)->where('course_id', $classes->course_id)->count();
						
						$classes->stdatetext = date("d-M-Y",strtotime($classes->class_date)).", ".date("D",strtotime($classes->class_date))." ".$classes->class_start_time. " to ".$classes->class_end_time.",".$classes->class_duration." Minutes";
						$classes->tutortext = $tutordislaytext;
						$classes->locationtext = $locationtextg;
						//$classes->totalstudents = $totalstudents;
						
						$clsdate = strtotime($classes->class_date);
						$nowdate = strtotime(date('Y-m-d'));
						
						if($nowdate > $clsdate){
							$classes->classstatus = 'Completed';
						}else{
							$classes->classstatus = ($classes->class_status == 1 ) ? 'Yet to start' : 'Completed';
						}						
							
						$coursedetails = DB::table('courses')->where('id', $classes->course_id)->first();
						
						if($coursedetails){
							$classes->coursename = $coursedetails->course_title;
						}else{
							$classes->coursename = "";
						}					
						$classes->attendedflag = $clsnew->attend_class;
						$attendclass[] = $classes;					
						
					}
				}						
					
				}
				
				$arrReturn['status'] = "success";				
				$arrReturn['classes'] = $attendclass;
				
				
		     }
		                        
			}catch(\Exception $e){	
				$msg= $e->getMessage();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}
	
	
	
	public function fndataimport(Request $request)
    {
		
		$classcounts = 0;
		$totstudents = 0;
		$classdata = [];
		$arrReturn = [];
		$assignedstudents =[];
		$stdata = [];
		$attendclass = [];
		$notattendedclass = [];
		
		try{
			
			 $data=$request->all();		
							
			 $input = [];				
             $rules = array();
			 
			 $checkValid = Validator::make($input,$rules);			 
			

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{				
				
				$studentdata = DB::select("SELECT * from tnam_wc_customer_lookup");
				
				
				foreach($studentdata as $stdata){
					
					$verifystudentda = DB::table('users')->where('email', $stdata->email)->first();	
						if($verifystudentda){
							
						}else{
							
							
							
							$inputdata = [					        
					        'firstname' =>  $stdata->first_name,
							'lastname' => $stdata->last_name,
                            'email' =>  $stdata->email,                            
         					'password' =>  rand(),
							'role' =>  'student',
							'partner_id' =>  0,
							'admin_userid' =>  0, 	
                           ];

							$rtdata = $this->fnuserregisternew($inputdata);		
							
							$role = DB::table('tnam_usermeta')->where('meta_key', 'role')->where('user_id', $stdata->user_id)->first();
							
							$roletextkey = "Leader";
							if($role ){
								$roletextkey = $role->meta_value;
							}
							
							
							$inputdata2 = [					        
									'firstname' =>  $stdata->first_name,
									'lastname' => $stdata->last_name,
									'email' =>  $stdata->email,
									'phone_number' =>  '',
									'user_role' =>  'student',
									'user_category' =>  $roletextkey,
									'partner_id' =>  0,
									'admin_userid' =>  0, 	
								   ];

							
						   $rtprodata = $this->fnuserProfileDetails($stdata->email,$inputdata2);
						    
						  
							
						}
								   
				}
				
				
				/*				
				$inputdata = [					        
					        'firstname' =>  $input['firstname'],
							'lastname' => $input['lastname'],
                            'email' =>  $input['email'],                            
         					'password' =>  $usepassword,
							'role' =>  $input['user_role'],
							'partner_id' =>  $input['partner_id'],
							'admin_userid' =>  $input['admin_user_id'], 	
                           ];
						   
						   
				$rtdata = $this->fnuserregisternew($inputdata);
					
				$inputdata2 = [					        
					        'firstname' =>  $input['firstname'],
							'lastname' => $input['lastname'],
                            'email' =>  $input['email'],
							'phone_number' =>  $input['phone_number'],
							'user_role' =>  $input['user_role'],
							'user_category' =>  $input['user_category'],
							'partner_id' =>  $input['partner_id'],
							'admin_userid' =>  $input['admin_user_id'], 	
                           ];
					
				$rtprodata = $this->fnuserProfileDetails($input['email'],$inputdata2);
				
				*/
					
				print_r($studentdata); die;		
				
				
				$arrReturn['status'] = "success";				
				$arrReturn['classes'] = $attendclass;
				
				
		     }
		                        
			}catch(\Exception $e){	
				$msg= $e->getMessage();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}
	
	
	public function fngetusergroups(Request $request)
    {
		try{
		$data=$request->all();
		$groups = [];
		
		    $input = [				
		       'active_status' => isset($data['active_status']) ? $data['active_status'] : 'All', 
            ];
			
			$rules = array(
                      			   
            );
            
			 $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;
                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{
				
						$search_string = "";					
						$grouplist =  DB::select("SELECT * from user_groups order by id desc ");	
						foreach($grouplist as $glist){
							$totrptcounts = DB::table('user_group_mapping')->where('group_id', $glist->id)->count();
							$glist->totstudents = $totrptcounts;
							$groups[] = $glist;
						}	
						
						 							
						$arrReturn['status'] = "success";
						$arrReturn['grouplists'] = $groups;						
		}
		                        
    }catch(\Exception $e){	
           $msg= $e->getMessage();
            $arrReturn['status'] = 'failed';
            $arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}
	
	
	
	public function fngetusersbygroup(Request $request)
    {
		try{
		$data=$request->all();
		$userslist = [];
		
		    $input = [				
		       'group_id' => isset($data['group_id']) ? $data['group_id'] : 'All', 
			   'class_id' => isset($data['class_id']) ? $data['class_id'] : 0, 
            ];
			
			$rules = array(
                      			   
            );
            
			 $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;
                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{
				
						$groupid = $input["group_id"];
						$classid = $input["class_id"];	
						
						$groupmappeddata =  DB::select("SELECT * from user_group_mapping where group_id = ".$groupid);						
						$groupdata = DB::table('user_groups')->where('id', $groupid)->first();
						$totcount = 0;
						
						foreach($groupmappeddata as $gdata){

						
                           if($classid == 0){
							   
							   $studata = DB::table('user_profiles')->where('user_id', $gdata->user_id)->first();
								if($studata){
									$studata->recid = $gdata->id;
									$userslist[] = $studata;
								}							   
							   
						   }else{
							   
								$clasmappdata = DB::table('user_class_mapping')->where('class_id', $classid)->where('user_id', $gdata->user_id)->first();
								if($clasmappdata){
									
								}else{
									$studata = DB::table('user_profiles')->where('user_id', $gdata->user_id)->first();
									if($studata){
										$studata->recid = $gdata->id;
										$userslist[] = $studata;
									}
								}							   
						   }							
							
							
						}
						$totcount = count($userslist);						 							
						$arrReturn['status'] = "success";
						$arrReturn['userlist'] = $userslist;
						$arrReturn['groupdata'] = $groupdata;
						$arrReturn['totcountusers'] = $totcount;						
		}
		                        
    }catch(\Exception $e){	
           $msg= $e->getMessage();
            $arrReturn['status'] = 'failed';
            $arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}
	
	
	
	public function fnaddnewgroup(Request $request)
    {
		try{
		$data=$request->all();
		$userslist = [];
		
		    $input = [				
		       'groupname' => isset($data['groupname']) ? $data['groupname'] : '', 
			  
            ];
			
			$rules = array(
				   'groupname' => 'required',
				);
		
		
		  $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;
                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{
					$groupname = $input['groupname'];						
					$inputdata = ['group_name' => $groupname];						
					$verifydata = DB::table('user_groups')->where('group_name', $groupname)->first();
					if($verifydata == ''){				                     		   
						$insertUser = DB::table('user_groups')->insertGetId($inputdata);
					}						
						 							
					$arrReturn['status'] = "success";
					$arrReturn['message'] = 'Group Created successfully';						
		}
		                        
    }catch(\Exception $e){	
           $msg= $e->getMessage();
            $arrReturn['status'] = 'failed';
            $arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}
	
	
	public function fnassignusertogroup(Request $request)
    {
		try{
		$data=$request->all();
		$userslist = [];
		
		    $input = [				
		       'user_id' => isset($data['user_id']) ? $data['user_id'] : '', 
			   'group_id' => isset($data['group_id']) ? $data['group_id'] : '',
			  
            ];
			
			$rules = array(
				   'user_id' => 'required',
				   'group_id' => 'required',
				);
		
		
		  $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;
                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{
					$userid = $input['user_id'];
                    $groupid = $input['group_id'];
					
					$inputdata = ['user_id' => $userid, 'group_id' => $groupid];						
					$verifydata = DB::table('user_group_mapping')->where('user_id', $userid)->where('group_id', $groupid)->first();
					if($verifydata == ''){				                     		   
						$insertUser = DB::table('user_group_mapping')->insertGetId($inputdata);
					}						
						 							
					$arrReturn['status'] = "success";
					$arrReturn['message'] = 'assigned successfully';						
		}
		                        
    }catch(\Exception $e){	
           $msg= $e->getMessage();
            $arrReturn['status'] = 'failed';
            $arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}
	
	
	public function fndeleteusergroup(Request $request)
    {
		try{
		$data=$request->all();
		$userslist = [];
		
		    $input = [				
		       'rec_id' => isset($data['rec_id']) ? $data['rec_id'] : '', 			  
			  
            ];
			
			$rules = array(
				   'rec_id' => 'required',				   
				);
		
		
		  $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;
                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{
					$rec_id = $input['rec_id'];                   
					$insertUser = DB::table('user_group_mapping')->where('id', $rec_id)->delete();						 							
					$arrReturn['status'] = "success";
					$arrReturn['message'] = 'Deleted successfully';						
		}
		                        
    }catch(\Exception $e){	
           $msg= $e->getMessage();
            $arrReturn['status'] = 'failed';
            $arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}
	
	
	public function fnclonecourse(Request $request)
    {
		try{
			
			$data=$request->all();
			$userslist = [];
			$arrReturn = [];
		
		    $input = [				
		       'course_id' => isset($data['course_id']) ? $data['course_id'] : '', 			  
			  
            ];
			
			$rules = array(
				   'course_id' => 'required',				   
				);
		
		
		  $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;
                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{
					$c_id = $input['course_id']; 
					$coursedetails = DB::table('courses')->where('id', $c_id)->first();	
					
					
					
					//$courseid = $input['course_id'];				 					 
					 					 
					
				     $inputdata = [
					        'course_partner' =>  $coursedetails->course_partner,
					        'course_code' =>  $coursedetails->course_code,
					        'course_title' =>  $coursedetails->course_title,
							'course_short_description' =>  $coursedetails->course_short_description,
                            'course_descriptions' =>  $coursedetails->course_descriptions, 
                            'course_department' =>  $coursedetails->course_department,
                            'course_subject' =>  $coursedetails->course_subject,
                            'course_level' =>  $coursedetails->course_level,
                            'course_start_date' =>  $coursedetails->course_start_date,
                            'course_end_date' =>  $coursedetails->course_end_date,
                            'course_start_time' =>  $coursedetails->course_start_time,
                            'course_end_time' =>  $coursedetails->course_end_time,
                            'course_duration' =>  $coursedetails->course_duration,
                            'course_price' =>  $coursedetails->course_price,
                            'subscription_price' =>  $coursedetails->subscription_price,
                            'class_price' =>  $coursedetails->class_price,
                            'class_subscription_price' =>  $coursedetails->class_subscription_price,
                            'course_capacity' =>  $coursedetails->course_capacity,
							'course_tutors' =>  $coursedetails->course_tutors,
							'course_location' =>  $coursedetails->course_location,
							'course_type' =>  $coursedetails->course_type,
							'course_status' =>  $coursedetails->course_status,							
							'course_modified_datetime' =>  date('Y-m-d H:i:s'),
							'lastmodified_by' =>  $coursedetails->lastmodified_by,
							'course_intervalday' =>  $coursedetails->course_intervalday,
                           ];
					$inputdata['course_created_datetime'] = date('Y-m-d H:i:s'); 
					$inputdata['course_modified_datetime'] = date('Y-m-d H:i:s');
					$inputdata['admin_user_id'] = $coursedetails->lastmodified_by;
					$insertUser = DB::table('courses')->insertGetId($inputdata);					
					$this->createcourseclasses($insertUser);
					///$insertUser = DB::table('user_group_mapping')->where('id', $rec_id)->delete();						 							
					$arrReturn['status'] = "success";
					$arrReturn['newcourseid'] = $insertUser;
					$arrReturn['message'] = 'Course cloned successfully';						
		}
		                        
    }catch(\Exception $e){	
           $msg= $e->getMessage();
            $arrReturn['status'] = 'failed';
            $arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}
	
	public function fnStudentSubscriptions(Request $request)
    {
		
		$returnRes = [];
		$arrReturn = [];
		
		try{		
		
			$data=$request->all();
			
			$input = [			  
		       'subscription_id' => isset($data['subscription_id']) ? $data['subscription_id'] : 0,			   
		       'student_id' => isset($data['student_id']) ? $data['student_id'] : 0,			   
            ];			
			$rules = array(
			   'subscription_id' => 'required',
			   'student_id' => 'required',
            );
			
			$checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;                 
            }else{
					$subscription_id = $input['subscription_id'];
					$student_id = $input['student_id'];
					$date_format = "%d-%b-%Y";
					$student_plan = DB::select("SELECT firstname, lastname, email, phone_number, subscription_title, subscription_description, subscription_amount, paid_amount
												, IF(subscription_status = 1, IF(CURDATE()<subscription_endate, IF(cancel_status=0, 'ACTIVE', CONCAT('CANCELLED. Ends on ', DATE_FORMAT(subscription_endate, '".$date_format."'))), 'INACTIVE'), 'INACTIVE') subscription_status
												, DATE_FORMAT(subscription_startdate, '".$date_format."') subscription_startdate, DATE_FORMAT(next_renewal_date, '".$date_format."') next_renewal_date
												FROM user_profiles
												JOIN user_subscriptions ON user_subscriptions.user_id = user_profiles.user_id
												JOIN subscription_plans ON subscription_plans.id = user_subscriptions.subscription_id
												WHERE user_profiles.user_id = ".$student_id."");

					$student_plan_history = DB::select("SELECT subscription_description, subscription_amount, A.paid_amount, A.subscription_startdate, A.subscription_enddate,A.payment_method,A.payment_date
														FROM user_subscriptions 
														JOIN subscription_plans ON subscription_plans.id = user_subscriptions.subscription_id
														JOIN (SELECT user_id, subscription_id, payment_date, SUM(paid_amount) paid_amount, MIN(subscription_startdate) subscription_startdate, MAX(subscription_enddate) subscription_enddate,GROUP_CONCAT(DISTINCT payment_method) payment_method														
														FROM user_subscriptions_payment WHERE subscription_id = ".$subscription_id." GROUP BY user_id, subscription_id, payment_date) A ON user_subscriptions.user_id = A.user_id AND user_subscriptions.subscription_id = A.subscription_id
														WHERE user_subscriptions.user_id = ".$student_id."");
					

					$studentpaymentlogs = [];
					$incr = 1;
					foreach($student_plan_history as $sthistory){
						
						$sthistory->paiddate =  date('d-M-Y',strtotime($sthistory->payment_date));
						if($incr == 1) {
							$sthistory->disptext = "First payment of the Subscription on  ".$sthistory->paiddate;
						}else{
							$sthistory->disptext = "Renewed on ".$sthistory->paiddate;
						}
						$studentpaymentlogs[] = $sthistory;
						$incr++;
					}	
					
					$arrReturn['status'] = "success";
					$arrReturn['student_plan'] = $student_plan;
					$arrReturn['student_plan_history'] = $student_plan_history;
					
			}
        }catch(\Exception $e){	
				    $msg= $e->getMessage();
					$arrReturn['status'] = 'failed';
					$arrReturn['message'] = $msg;
              }
 
        $returnRes=json_encode($arrReturn);
        return $returnRes; 
	}
	
  public function fnRenewsubscriptons(Request $request)
    {
		   $arrReturn = [];		
		   $data=$request->all();
		   		   
			try{
				
				$input = [
					'subs_id' => isset($data['subs_id']) ? $data['subs_id'] : '',
					'user_id' => isset($data['user_id']) ? $data['user_id'] : '',
					'renewal_date' => isset($data['renewal_date']) ? $data['renewal_date'] : '',
					'payment_method' => isset($data['payment_method']) ? $data['payment_method'] : '',
					];									

				$rules = array(
				   'subs_id'  => 'required',
				   'user_id' => 'required',
				   'renewal_date' => 'required',
				   'payment_method' => 'required',
				);

				$checkValid = Validator::make($input,$rules);

				if ($checkValid->fails()) {
				
					$arrErr = $checkValid->errors()->all();
					$arrReturn['status'] = 'failed';
					$arrReturn['message'] = $arrErr;                
					$returnRes=json_encode($arrReturn);
					return $returnRes;
				}else{

					$subsid = $input['subs_id'];
					$user_id = $input['user_id'];
					$subsstdate = $input['renewal_date'];
					$subsstdatetimestamp = strtotime($subsstdate);
					
					$renewsubscriptiondata = $this->renewsubscription($subsid, $user_id, $subsstdate, $input['payment_method']);

					$arrReturn['status'] = "success";
					$arrReturn['message'] = "Subscription renewed successfully";
				}
			}catch(\Exception $e){	
			    $msg= $e->getMessage();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $msg;
            }
        $returnRes=json_encode($arrReturn);
        return $returnRes;
	}

  public function fnAutoRenewSubscriptons(Request $request)
    {
		   $arrReturn = [];		
		   $data=$request->all();
		   		   
			try{
				
				$input = [
					'renewal_date' => isset($data['renewal_date']) ? $data['renewal_date'] : '',
					];									

				$rules = array(
				   'renewal_date' => 'required',
				);

				$checkValid = Validator::make($input,$rules);

				if ($checkValid->fails()) {
				
					$arrErr = $checkValid->errors()->all();
					$arrReturn['status'] = 'failed';
					$arrReturn['message'] = $arrErr;                
					$returnRes=json_encode($arrReturn);
					return $returnRes;
				}else{


					$renewal_date = $input['renewal_date'];

					$subscription_list = DB::select("SELECT user_subscriptions.* FROM user_subscriptions 
														LEFT JOIN (SELECT * FROM user_subscriptions WHERE DATE_ADD('".$renewal_date."',INTERVAL 1 DAY) BETWEEN subscription_startdate AND subscription_endate) A ON user_subscriptions.user_id=A.user_id AND user_subscriptions.subscription_id = A.subscription_id
														WHERE user_subscriptions.next_renewal_date = '".$renewal_date."' 
														AND user_subscriptions.cancel_status =0
														AND A.ID IS NULL");
					foreach($subscription_list as $subscriptionlist){
						$renewsubscriptiondata = $this->renewsubscription($subscriptionlist->subscription_id, $subscriptionlist->user_id, $renewal_date, 'Stripe');
					}
				}
				$arrReturn['status'] = "success";
				$arrReturn['message'] = "Subscription renewed successfully";
			}catch(\Exception $e){	
			    $msg= $e->getMessage();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $msg;
            }
        $returnRes=json_encode($arrReturn);
        return $returnRes;
	}

  public function renewsubscription($subsid, $user_id, $renewal_date, $payment_method)
    {
		   $arrReturn = [];		
		   		   
			try{
				
				$subsstdate = $renewal_date;				
				
				$subsstdatetimestamp = strtotime($subsstdate);
				
				$user_subcripiton_details = DB::table('user_subscriptions')->where('user_id', $user_id)->where('subscription_id', $subsid)->first();	
				$subcripiton_details = DB::table('subscription_plans')->where('id', $user_subcripiton_details->subscription_id)->first();	
				$subsintervaltype = $subcripiton_details->subs_intervaltype;
				$paid_amount = $user_subcripiton_details->paid_amount;
				$total_amount = $paid_amount;
				if($subsintervaltype == "Monthly"){
					$subsenddate = date('Y-m-d', strtotime('+1 month', $subsstdatetimestamp));
					$subsinterval = 1;
				}
				
				if($subsintervaltype == "Quarterly"){
					$subsenddate = date('Y-m-d', strtotime('+3 month', $subsstdatetimestamp));
					$subsinterval = 3;
					$paid_amount = $paid_amount/3;
				}
				
				if($subsintervaltype == "Yearly"){
					$subsenddate = date('Y-m-d', strtotime('+1 year', $subsstdatetimestamp));
					$subsinterval = 12;
					$paid_amount = $paid_amount/12;
				}
				$subsenddate = date('Y-m-d', strtotime('-1 day', strtotime($subsenddate)));
				$current_time = date('Y-m-d H:i:s');

				$inputdata = [					        
					'payment_method' =>  $payment_method,
					'subscription_endate' =>  $subsenddate,
					'next_renewal_date' =>  $subsenddate,
					'subscription_status' =>  1,
					   ];					

				$updateUser = DB::table('user_subscriptions')->where('user_id', $user_id)->where('subscription_id', $subsid)->update($inputdata);
				$enddate = date("Y-m-t", strtotime($subsstdate));
				while($subsstdate < $subsenddate){
					if($enddate>$subsenddate){
						$enddate = $subsenddate;
					}
					$date1 = new DateTime($subsstdate);
					$date2 = new DateTime($enddate);
					$days  = $date2->diff($date1)->format('%a')+1;
					$monthDays = date("t", strtotime($enddate));
					$month_amount = 0;
					if($enddate==$subsenddate){
						$month_amount = $total_amount;
					}elseif(date("j", strtotime($subsstdate))!=1){
						$usersubscriptiondata =  DB::select("SELECT * from user_subscriptions_payment where user_id = ".$user_id." AND DATE_ADD(subscription_enddate,INTERVAL 1 DAY) ='".$subsstdate."' AND subscription_id = ".$subsid."");
						if($usersubscriptiondata){
							$month_amount = $paid_amount - $usersubscriptiondata[0]->paid_amount;
							$total_amount = $total_amount - $month_amount;
						}else{
							$month_amount = $paid_amount*$days/$monthDays;
							$total_amount = $total_amount - $month_amount;
						}
					}else{
						$month_amount = $paid_amount*$days/$monthDays;
						$total_amount = $total_amount - $month_amount;
					}
					$inputdata = [					        
							'user_id' =>  $user_id,
							'subscription_id' => $subsid,
							'subscription_amount' =>  $user_subcripiton_details->subscription_amount, 
							'paid_amount' =>  $month_amount,
							'discount_amount' =>  $user_subcripiton_details->discount_amount,
							'discount_code' =>  $user_subcripiton_details->discount_code,
							'payment_method' =>  $payment_method,
							'subscription_startdate' =>  $subsstdate,
							'subscription_enddate' =>  $enddate,
							'subscription_status' =>  1,
							'payment_date' =>  $current_time,
							'admin_userid' =>  $user_subcripiton_details->admin_userid,							
							   ];					
					$insertUser = DB::table('user_subscriptions_payment')->insertGetId($inputdata);			  
					$subsstdate = date('Y-m-01', strtotime($subsstdate));
					$nextMonth = strtotime('+1 month', strtotime($subsstdate));
					$subsstdate = date('Y-m-01', $nextMonth);
					$enddate = date("Y-m-t", strtotime($subsstdate));
				}
				$arrReturn['status'] = "success";
				$arrReturn['message'] = "Subscription renewed successfully";
			}catch(\Exception $e){	
			    $msg= $e->getMessage();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $msg;				
            }
        $returnRes=json_encode($arrReturn);
        return $returnRes;
	}


  public function fnCancelsubscriptons(Request $request)
    {
		   $arrReturn = [];		
		   $data=$request->all();
		   		   
			try{
				
				$input = [
					'subs_id' => isset($data['subs_id']) ? $data['subs_id'] : 0,
					'user_id' => isset($data['user_id']) ? $data['user_id'] : '',
					'admin_userid' => isset($data['admin_userid']) ? $data['admin_userid'] : '',
					];									

				$rules = array(
				   'subs_id'  => 'required',
				   'user_id' => 'required',
				);

				$checkValid = Validator::make($input,$rules);

				if ($checkValid->fails()) {
				
					$arrErr = $checkValid->errors()->all();
					$arrReturn['status'] = 'failed';
					$arrReturn['message'] = $arrErr;                
					$returnRes=json_encode($arrReturn);
					return $returnRes;
				}else{

					$subsid = $input['subs_id'];
					$user_id = $input['user_id'];

					DB::statement("UPDATE user_subscriptions SET next_renewal_date = NULL, cancel_status = 1, cancel_by = ".$input['admin_userid'].", cancel_date = CURDATE() where user_id = ".$user_id." AND subscription_id = ".$subsid."");
/*					DB::statement("DELETE FROM user_subscriptions_payment where user_id = ".$user_id." AND subscription_id = ".$subsid." AND subscription_startdate > '".$input['cancel_date']."'");
					DB::statement("UPDATE user_subscriptions_payment SET subscription_enddate = '".$input['cancel_date']."' where user_id = ".$user_id." AND subscription_id = ".$subsid." AND  ('".$input['cancel_date']."' BETWEEN subscription_startdate AND subscription_enddate)");
*/				}
				$arrReturn['status'] = "success";
				$arrReturn['message'] = "Subscription cancelled successfully";
			}catch(\Exception $e){	
			    $msg= $e->getMessage();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $msg;
            }
        $returnRes=json_encode($arrReturn);
        return $returnRes;
	}
	
	
	public function fngetstudentdetailsbyid(Request $request)
    {
		try{
		$data=$request->all();
		
		 $input = [
		        'userid' => isset($data['userid']) ? $data['userid'] : '',               			   
            ];
			
			     $rules = array(
               'userid' => 'required',
                			   
            );
            
			 $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{

                $userid = $input['userid'];				
                $userdetails = DB::table('user_profiles')->where('user_id', $userid)->first();
				
				if($userdetails){
					$userrawadata = DB::table('users')->where('ID', $userdetails->user_id)->first();
					$userdetails->userfullname = $userdetails->firstname." ".$userdetails->lastname;
					$userdetails->userjoinedon = date('d-M-Y',strtotime($userrawadata->created_at));
					
				}
				
				$arrReturn['status'] = "success";
	       	    $arrReturn['userdetails'] = $userdetails ;
		     }
		                        
    }catch(\Exception $e){	
           $msg= $e->getMessage();
            $arrReturn['status'] = 'failed';
            $arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}
	
	
	public function fngetsubsdetails(Request $request)
    {
		
		$returnRes = [];
		$arrReturn = [];
		
		try{		
		
			$data=$request->all();
			
			$input = [			  
		       'subscription_id' => isset($data['subscription_id']) ? $data['subscription_id'] : 0,			   
		       'student_id' => isset($data['student_id']) ? $data['student_id'] : 0,			   
            ];			
			$rules = array(
			   'subscription_id' => 'required',
			   'student_id' => 'required',
            );
			
			$checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;                 
            }else{
					$subscription_id = $input['subscription_id'];
					$student_id = $input['student_id'];
					$date_format = "%d-%b-%Y";				
					
					$student_plan = DB::select("SELECT firstname, lastname, email, phone_number, subscription_plans.partner_id as subspartnerid, subscription_plans.id as subscriptionid, subscription_title, subscription_description, subscription_amount, paid_amount
												, IF(subscription_status = 1, IF(CURDATE()<subscription_endate, IF(cancel_status=0, 'ACTIVE', CONCAT('CANCELLED. Ends on ', DATE_FORMAT(subscription_endate, '".$date_format."'))), 'INACTIVE'), 'INACTIVE') subscription_status
												, DATE_FORMAT(subscription_startdate, '".$date_format."') subscription_startdate,next_renewal_date as nxtrenewaldate, DATE_FORMAT(next_renewal_date, '".$date_format."') next_renewal_date
												FROM user_profiles
												JOIN user_subscriptions ON user_subscriptions.user_id = user_profiles.user_id
												JOIN subscription_plans ON subscription_plans.id = user_subscriptions.subscription_id
												WHERE user_profiles.user_id = ".$student_id." and user_subscriptions.subscription_id='".$subscription_id."'");

					$student_plan_history = DB::select("SELECT subscription_description, subscription_amount, A.paid_amount, A.subscription_startdate, A.subscription_enddate,A.payment_method,A.payment_date
														FROM user_subscriptions 
														JOIN subscription_plans ON subscription_plans.id = user_subscriptions.subscription_id
														JOIN (SELECT user_id, subscription_id, payment_date, SUM(paid_amount) paid_amount, MIN(subscription_startdate) subscription_startdate, MAX(subscription_enddate) subscription_enddate,GROUP_CONCAT(DISTINCT payment_method) payment_method														
														FROM user_subscriptions_payment WHERE subscription_id = ".$subscription_id." GROUP BY user_id, subscription_id, payment_date) A ON user_subscriptions.user_id = A.user_id AND user_subscriptions.subscription_id = A.subscription_id
														WHERE user_subscriptions.user_id = ".$student_id."");
					

					$studentpaymentlogs = [];
					$incr = 1;
					foreach($student_plan_history as $sthistory){
						
						$sthistory->paiddate =  date('d-M-Y',strtotime($sthistory->payment_date));
						if($incr == 1) {
							$sthistory->disptext = "First payment of the Subscription on  ".$sthistory->paiddate;
						}else{
							$sthistory->disptext = "Renewed on ".$sthistory->paiddate;
						}
						$studentpaymentlogs[] = $sthistory;
						$incr++;
					}	
					
					$subuserdetails = ($student_plan) ? $student_plan[0] : [];
					
					if($subuserdetails->subscription_status == "ACTIVE"){
						$subuserdetails->posnextrenewaldate  = date("d-m-Y" , strtotime($subuserdetails->nxtrenewaldate)); 
					}else{
						$subuserdetails->posnextrenewaldate  = date("d-m-Y");
					}
					
					$arrReturn['status'] = "success";
					$arrReturn['subs_plan'] = $subuserdetails;
					$arrReturn['student_plan_history'] = $student_plan_history;
					
			}
        }catch(\Exception $e){	
				    $msg= $e->getMessage();
					$arrReturn['status'] = 'failed';
					$arrReturn['message'] = $msg;
              }
 
        $returnRes=json_encode($arrReturn);
        return $returnRes; 
	}
	
	
	public function fngetemailvalidate(Request $request)
    {
		try{
		$data=$request->all();
		
		 $input = [
		        'email' => isset($data['email']) ? $data['email'] : '',               			   
            ];
			
			     $rules = array(
               'email' => 'required',
                			   
            );
            
			 $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{

                $usermail = $input['email'];	
				
				$userdetails = DB::table('users')->where('email', $usermail)->first();
				
				$userstatus = 0;
				$errormessage = '';
				if($userdetails){
					$userstatus = 1;
					$errormessage = $usermail.' - Email Id already exists';
				}
				
				/*				
                $userdetails = DB::table('user_profiles')->where('user_id', $userid)->first();
				
				if($userdetails){
					$userrawadata = DB::table('users')->where('ID', $userdetails->user_id)->first();
					$userdetails->userfullname = $userdetails->firstname." ".$userdetails->lastname;
					$userdetails->userjoinedon = date('d-M-Y',strtotime($userrawadata->created_at));
					
				}
				*/
				$arrReturn['status'] = "success";
	       	    $arrReturn['userstatus'] = $userstatus ;
				$arrReturn['message'] = $errormessage;
		     }
		                        
    }catch(\Exception $e){	
           $msg= $e->getMessage();
            $arrReturn['status'] = 'failed';
            $arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}
	
	public function fnattendancereportbyMonth(Request $request)
    {
		
		$classcounts = 0;
		$totstudents = 0;
		$classdata = [];
		$arrReturn = [];
		$assignedstudents =[];
		$stdata = [];
		$attendclass = [];
		$notattendedclass = [];
		
		try{
			
			 $data=$request->all();		
			 $input = [
					'email' => isset($data['email']) ? $data['email'] : '',
					'month' => isset($data['month']) ? $data['month'] : '',
				];
				
			 $rules = array(
				   'email' => 'required',
				   'month' => 'required',
				);
            
			 $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{
				
				$email = $input['email'];
				$classdate = $input['month'];
				
				$studentdata = DB::select("SELECT * from user_profiles WHERE email ='".$email."'");
						
				if($studentdata){					
					$stdata = $studentdata[0];	
					$userid = $stdata->user_id;										
					
				$courseclasses =  DB::select("SELECT course_classes.* FROM course_classes 
												JOIN user_class_mapping on course_classes.id = user_class_mapping.class_id
												WHERE MONTH(class_date) = MONTH('".$classdate."') AND YEAR(class_date) = YEAR('".$classdate."')
												AND attend_class = 1 AND user_id = ".$userid."
												ORDER BY class_date ASC;");
				if($courseclasses){
					$classcounts = count($courseclasses);
					foreach($courseclasses as $classes){
						
						$tutors_id = '';
						$location_id = '';
						
						$tutorsdata = json_decode($classes->class_tutors);													
						$tutors_id = ($tutorsdata) ?  implode(",",$tutorsdata) : '';						
						
						$locationdata = json_decode($classes->class_location);
						$location_id = ($locationdata)?  implode(",",$locationdata) : '';

						
						$arrlocationtext = [];						
						if($location_id){
							$locationdetails =  DB::select("SELECT * from class_locations where id in (".$location_id.")");
							if($locationdetails){
								
								foreach($locationdetails as $locdata){
									$arrlocationtext[] = $locdata->name;
								}
								
							}
						}	
						
						$locationtextg = (count($arrlocationtext) > 0) ? implode(",",$arrlocationtext) : '';
						
												
						$arrtutortext = [];						
						if($tutors_id){
							$tutordeatils =  DB::select("SELECT * from user_profiles where user_id in (".$tutors_id.")");
							if($tutordeatils){
								
								foreach($tutordeatils as $tutor){
									$arrtutortext[] = $tutor->firstname;
								}
								
							}
						}
						
						$tutordislaytext = (count($arrtutortext) > 0) ? implode(",",$arrtutortext) : '';
						
						//$totalstudents = DB::table('user_class_mapping')->where('class_id', $classes->id)->where('course_id', $classes->course_id)->count();
						
						$classes->stdatetext = date("d-M-Y",strtotime($classes->class_date)).", ".date("D",strtotime($classes->class_date))." ".$classes->class_start_time. " to ".$classes->class_end_time.",".$classes->class_duration." Minutes";
						$classes->tutortext = $tutordislaytext;
						$classes->locationtext = $locationtextg;
						//$classes->totalstudents = $totalstudents;
						
						$clsdate = strtotime($classes->class_date);
						$nowdate = strtotime(date('Y-m-d'));
						
						if($nowdate > $clsdate){
							$classes->classstatus = 'Completed';
						}else{
							$classes->classstatus = ($classes->class_status == 1 ) ? 'Yet to start' : 'Completed';
						}						
							
						$coursedetails = DB::table('courses')->where('id', $classes->course_id)->first();
						
						if($coursedetails){
							$classes->coursename = $coursedetails->course_title;
						}else{
							$classes->coursename = "";
						}					
						
						$classattenedstatus = DB::table('user_class_mapping')->where('attend_class', 1)->where('user_id', $userid)->where('class_id', $classes->id)->where('course_id', $classes->course_id)->count();
						//echo $classattenedstatus; die;
						if($classattenedstatus == 0){
							$classes->attendedflag = 'No';
							$notattendedclass[] = $classes;
						}else{
							$classes->attendedflag = 'Yes';
							$attendclass[] = $classes;
						}						
						
					}
				}						
					
				}
				
				$arrReturn['status'] = "success";
				$arrReturn['studentdetails'] = $stdata;
				$arrReturn['attendedclass'] = $attendclass;
				$arrReturn['attendclascount'] = count($attendclass);
				
		     }
		                        
			}catch(\Exception $e){	
				$msg= $e->getMessage();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}
	
	public function fnclassreportbyDay(Request $request)
    {
		
		$classcounts = 0;
		$totstudents = 0;
		$classdata = [];
		$arrReturn = [];
		$assignedstudents =[];
		$stdata = [];
		$attendclass = [];
		$notattendedclass = [];
		
		try{
			
			 $data=$request->all();		
			 $input = [
					'email' => isset($data['email']) ? $data['email'] : '',
					'month' => isset($data['month']) ? $data['month'] : '',
				];
				
			 $rules = array(
				   'email' => 'required',
				   'month' => 'required',
				);
            
			 $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{
				
				$email = $input['email'];
				$classdate = $input['month'];
				
				$studentdata = DB::select("SELECT * from user_profiles WHERE email ='".$email."'");
						
				if($studentdata){					
					$stdata = $studentdata[0];	
					$userid = $stdata->user_id;										
					
				$courseclasses =  DB::select("SELECT course_classes.* FROM course_classes 
												JOIN user_class_mapping on course_classes.id = user_class_mapping.class_id
												WHERE class_date = '".$classdate."'
												AND user_id = ".$userid."
												ORDER BY class_date ASC;");
				if($courseclasses){
					$classcounts = count($courseclasses);
					foreach($courseclasses as $classes){
						
						$tutors_id = '';
						$location_id = '';
						
						$tutorsdata = json_decode($classes->class_tutors);													
						$tutors_id = ($tutorsdata) ?  implode(",",$tutorsdata) : '';						
						
						$locationdata = json_decode($classes->class_location);
						$location_id = ($locationdata)?  implode(",",$locationdata) : '';

						
						$arrlocationtext = [];						
						if($location_id){
							$locationdetails =  DB::select("SELECT * from class_locations where id in (".$location_id.")");
							if($locationdetails){
								
								foreach($locationdetails as $locdata){
									$arrlocationtext[] = $locdata->name;
								}
								
							}
						}	
						
						$locationtextg = (count($arrlocationtext) > 0) ? implode(",",$arrlocationtext) : '';
						
												
						$arrtutortext = [];						
						if($tutors_id){
							$tutordeatils =  DB::select("SELECT * from user_profiles where user_id in (".$tutors_id.")");
							if($tutordeatils){
								
								foreach($tutordeatils as $tutor){
									$arrtutortext[] = $tutor->firstname;
								}
								
							}
						}
						
						$tutordislaytext = (count($arrtutortext) > 0) ? implode(",",$arrtutortext) : '';
						
						//$totalstudents = DB::table('user_class_mapping')->where('class_id', $classes->id)->where('course_id', $classes->course_id)->count();
						
						$classes->stdatetext = date("d-M-Y",strtotime($classes->class_date)).", ".date("D",strtotime($classes->class_date))." ".$classes->class_start_time. " to ".$classes->class_end_time.",".$classes->class_duration." Minutes";
						$classes->tutortext = $tutordislaytext;
						$classes->locationtext = $locationtextg;
						//$classes->totalstudents = $totalstudents;
						
						$clsdate = strtotime($classes->class_date);
						$nowdate = strtotime(date('Y-m-d'));
						
						if($nowdate > $clsdate){
							$classes->classstatus = 'Completed';
						}else{
							$classes->classstatus = ($classes->class_status == 1 ) ? 'Yet to start' : 'Completed';
						}						
							
						$coursedetails = DB::table('courses')->where('id', $classes->course_id)->first();
						
						if($coursedetails){
							$classes->coursename = $coursedetails->course_title;
						}else{
							$classes->coursename = "";
						}					
						
						$classattenedstatus = DB::table('user_class_mapping')->where('user_id', $userid)->where('class_id', $classes->id)->where('course_id', $classes->course_id)->count();
						//echo $classattenedstatus; die;
						if($classattenedstatus == 0){
							$classes->attendedflag = 0;
							$notattendedclass[] = $classes;
						}else{
							$classes->attendedflag = 1;
							$attendclass[] = $classes;
						}						
						
					}
				}						
					
				}
				
				$arrReturn['status'] = "success";
				$arrReturn['studentdetails'] = $stdata;
				$arrReturn['attendedclass'] = $attendclass;
				$arrReturn['attendclascount'] = count($attendclass);
				
		     }
		                        
			}catch(\Exception $e){	
				$msg= $e->getMessage();
				$arrReturn['status'] = 'failed';
				$arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}
	
	public function getcountrylists(Request $request)
    {
		try{
				$data=$request->all();
		
				$input = [];
			
			     $rules = array();
            
			 $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{

                $countrylist = DB::table('countries')->orderby('orderby','asc')->get();
				$arrReturn['status'] = "success";
	       	    $arrReturn['countrylists'] = $countrylist ;
				$arrReturn['message'] = '';
		     }
		                        
    }catch(\Exception $e){	
           $msg= $e->getMessage();
            $arrReturn['status'] = 'failed';
            $arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}
	
	public function fngetclientlists(Request $request)
    {
		try{
		$data=$request->all();
		$userslist = [];
		
		 $input = [				
		       'role' => isset($data['role']) ? $data['role'] : '',               
			   'searchinput' =>	isset($data['searchinput']) ? $data['searchinput'] : '',			   
            ];
			
			     $rules = array(
                      			   
            );
            
			 $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;
                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{
				
						$search_string = "";						
										
						if( $input['role'] != '') {
							$search_string .= " user_role = '".$input['role']."' AND archieve_status = 0 AND";
						}						
						
						if( $input['searchinput'] != '') {
							$svalue = trim($input['searchinput']);
							$search_string .= " firstname like  '%".$svalue."%' OR email like  '%".$svalue."%' OR  phone_number like '%".$svalue."%' ";	
						}
						
						$search_string = $search_string;
						
						if(!empty($search_string )){
						$search_string = rtrim( $search_string, 'AND ' );
						}
												
						if($search_string){
						$userslist =  DB::select("SELECT * from user_profiles WHERE ". $search_string." order by id desc");
						}else{ 
						 $userslist = DB::table('user_profiles')->get();
						}
						
						$arrClients = [];
						$totalAmount = 0;
						$normalclients = 0;
						$gstclients = 0;
						foreach($userslist as $ulist){
							$clientid = $ulist->user_id;
							//$clienttotdata = DB::select("SELECT IFNULL(SUM(item_total),0) AS totalamount FROM client_systems WHERE client_id = '".$clientid."'");
							$totalrentalamount = DB::table('client_systems')->where('client_id', $clientid)->sum('item_total');
							
				            $ulist->invoiceamount = $totalrentalamount;
							$totalAmount += $ulist->invoiceamount;
							
							if($ulist->invoice_type == "Normal"){
								$normalclients++;
							}
							
							if($ulist->invoice_type == "GST"){
								$gstclients++;
							}
							
							$arrClients[] =  $ulist;
						}
						
						$totclients = DB::table('user_profiles')->where('archieve_status', 0)->where('user_role', 'client')->count();	
						
						$arrReturn['status'] = "success";
						$arrReturn['userslist'] = $arrClients;
						$arrReturn['totalcollection'] = $totalAmount;
						$arrReturn['totnclients'] = $normalclients;
						$arrReturn['totgstclients'] = $gstclients;
						$arrReturn['totclients'] = $totclients;
		}
		                        
    }catch(\Exception $e){	
           $msg= $e->getMessage();
            $arrReturn['status'] = 'failed';
            $arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}
	
	
	public function fngetclientlexport(Request $request)
    {
		try{
		$data=$request->all();
		$userslist = [];
		
		 $input = [				
		       'role' => isset($data['role']) ? $data['role'] : '',               
			   'searchinput' =>	isset($data['searchinput']) ? $data['searchinput'] : '',			   
            ];
			
			     $rules = array(
                      			   
            );
            
			 $checkValid = Validator::make($input,$rules);

               if ($checkValid->fails()) {
                $arrErr = $checkValid->errors()->all();
                $arrReturn['status'] = 'failed';
                $arrReturn['message'] = $arrErr;
                
                $returnRes=json_encode($arrReturn);
                return $returnRes;
            }else{
				
				        $input['role']  = "client";
						$search_string = "";						
										
						if( $input['role'] != '') {
							$search_string .= " user_role = '".$input['role']."' AND archieve_status = 0 AND";
						}						
						
						if( $input['searchinput'] != '') {
							$svalue = trim($input['searchinput']);
							$search_string .= " firstname like  '%".$svalue."%' OR email like  '%".$svalue."%' OR  phone_number like '%".$svalue."%' ";	
						}
						
						$search_string = $search_string;
						
						if(!empty($search_string )){
						$search_string = rtrim( $search_string, 'AND ' );
						}
						
						
						if($search_string){
						$userslist =  DB::select("SELECT * from user_profiles WHERE ". $search_string." order by id desc");
						}else{ 
						 $userslist = DB::table('user_profiles')->get();
						}
						
						$arrClients = [];
						$totalAmount = 0;
						$normalclients = 0;
						$gstclients = 0;
						
						$tableprint = "<table border='1' cellpadding='5' cellspacing='0'>
						<tr>
						<th>S.No</th>
						<th>Name</th>
						<th>Company </th>
						<th>Phone No</th>
						<th>Email</th>
						<th>Client Type</th>
						<th>Rent Mode</th>
						<th>Amount</th>
						<th>Invoice Day</th>
						<th>Invoice Email</th>
						<th>Invoice Whatsapp</th>
						</tr>";
						
						$incr2 = 1;
						foreach($userslist as $ulist){							
							$clientid = $ulist->user_id;
							$clienttotdata = DB::select("SELECT IFNULL(SUM(item_total),0) AS totalamount FROM client_systems WHERE client_id = '".$clientid."'");
				            $ulist->invoiceamount = ($clienttotdata) ? $clienttotdata[0]->totalamount : 0;							
							$tableprint .= "<tr>";
							$tableprint .= "<td>".$incr2."</td>";
							$tableprint .= "<td>".$ulist->firstname." ".$ulist->lastname."</td>";
							$tableprint .= "<td>".$ulist->company_name."</td>";
							$tableprint .= "<td>".$ulist->phone_number."</td>";
							$tableprint .= "<td>".$ulist->email."</td>";
							$tableprint .= "<td>".$ulist->invoice_type."</td>";
							$tableprint .= "<td>".$ulist->rental_mode."</td>";
							$tableprint .= "<td align='right'>".$ulist->invoiceamount."</td>";
							$tableprint .= "<td align='center'>".$ulist->invoice_day."</td>";
							$tableprint .= "<td>".$ulist->invoice_email."</td>";
							$tableprint .= "<td>".$ulist->invoice_whatsapp."</td>";
							$tableprint .= "</tr>";
							$incr2++;
							$arrClients[] =  $ulist;
						}
						
						$tableprint .= "</table>";
						
						header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
						header("Content-Disposition: attachment;filename=\"clientlist.xls\"");
						header("Cache-Control: max-age=0");
						echo $tableprint; 
						
						die;
						
						$totclients = DB::table('user_profiles')->where('archieve_status', 0)->where('user_role', 'client')->count();	
						
						$arrReturn['status'] = "success";
						$arrReturn['userslist'] = $arrClients;
						$arrReturn['totalcollection'] = $totalAmount;
						$arrReturn['totnclients'] = $normalclients;
						$arrReturn['totgstclients'] = $gstclients;
						$arrReturn['totclients'] = $totclients;
		}
		                        
    }catch(\Exception $e){	
           $msg= $e->getMessage();
            $arrReturn['status'] = 'failed';
            $arrReturn['message'] = $msg;
              }
 
            $returnRes=json_encode($arrReturn);
             return $returnRes;
	}
	

}