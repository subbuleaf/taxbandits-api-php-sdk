<?php


namespace App\Http\Controllers;
use App\Http\Resources\ProjectResource;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\JWTController;
use App\Http\Controllers\BusinessController;
use Eastwest\Json\Facades\Json;
use App\Models\Business;
use App\Models\ForeignAddress;
use App\Models\USAddress;
use App\Models\SigningAuthority;
use App\Models\Form1099NECCreateRequest;
use App\Models\ReturnHeader;
use App\Models\SubmissionManifest;
use App\Models\ScheduleFiling;
use App\Models\ReturnData;
use App\Models\Recipient;
use App\Models\NECFormData;
use App\Models\States;

class Form1099NecController extends Controller
{
   # Returns list of all the businesses
    public function get_all_business_list()
    {
        $businessController= new BusinessController();

        return view('form_1099_nec_list',['businesses'=>$businessController->getBusinessList()['Businesses']]);
        
    }

    # Lists all Form 1099-NEC returns created and transmitted on the account for a particular Submission or Payer. 
    # Form 1099-NEC returns will be listed based on the filters sent in the Request.
    # Method: Form1099NEC/List (GET)
    public function get_nec_list_by_business_id($business_id)
    {
        $jwtController= new JwtController();

        $accessToken = $jwtController->get_access_token();

        error_log($accessToken);

        $response= Http::withHeaders([
           
            'Authorization' =>  $accessToken
         ])->get( env('TBS_BASE_URL').'Form1099NEC/List', [
            'BusinessId' =>$business_id,
            'Page' =>1,
            'PageSize' => 100,
            'FromDate' => '03/01/2021',
            'ToDate' => '12/31/2021',
        ]);

       
      return $response;
        
    }

    # Returns NEC List of specific business Id  
    public function get_all_form_1099_nec_list_by_business_id(Request $request)
    {   
        return $this->get_nec_list_by_business_id($request->BusinessId);
    }


   # Get Business list for form 1099-NEC
    public function create_form_1099_nec()
    {
        $businessController= new BusinessController();

        return view('create_form_1099_nec',['businesses'=>$businessController->getBusinessList()['Businesses']]);

    }

    
    # Returns Recipient List of specific business Id  
    public function get_recipient_by_business_id(Request $request)
    {
        return $this->get_nec_list_by_business_id($request->BusinessId);
    }


    # Creates Form 1099-NEC returns in TaxBandits. You can send multiple 1099-NEC forms in a single request for the same Payer. 
    # In response, a SubmissionId is created which is further used as a reference for all other methods of Form 1099 NEC API.
    # Method: Form1099NEC/Create (POST)
    public function save_form_1099_nec(Request $request)
    {

    
        $form1099NECCreateRequest =  array(

            "ReturnHeader" => array(

                "Business"=>array(

                    "BusinessId"=>(request('business_list'))
                )
            ),

            "SubmissionManifest"=> array(
                "TaxYear"  => 2020,
                "IsFederalFiling" => true,
                "IsStateFiling"  => true,
                "IsPostal"  => true,
                "IsOnlineAccess"  => true,
                "IsTinMatching"  => true,
                "IsScheduleFiling"  => true,

                "ScheduleFiling"  =>   array(
                    "EfileDate"=> date('m/d/Y')
                )
            ),

            
            "ReturnData"=> array(
                
                array(

                    "SequenceId"  => 1,

                    "Recipient"=> array(
                        "RecipientId"  => (request('recipientsDropDown') != '-1') ? request('recipientsDropDown') : null,
                        "TINType"  => "EIN",
                        "TIN"  => request('rTIN'),
                        "FirstPayeeNm"  => request('rName'),
                        "SecondPayeeNm"  => "",
                        "IsForeign"  => false,
                        "USAddress"  => array(
                            "Address1"  => request('address1'),
                            "Address2"       => request('address2'),
                            "City"  => request('city'),
                            "State"  => request('state_drop_down'),
                            "ZipCd"  => request('zip_cd')
                        ),
                        "Email"  => "subbu+php@spanllc.com",
                        "Fax"  => "1234567890",
                        "Phone"  => "1234567890"
                    ),                                                                                                              

                    "NECFormData"=> array(

                        "B1NEC"  => request('amount'),
                        "B4FedTaxWH"  => 54.12,
                        "IsFATCA"  =>true,
                        "Is2ndTINnot"  => true,
                        "AccountNum"  => "20123130000009000001",
                        "States"=> array(
                            array(
                            "StateCd"  =>"PA",
                            "StateWH"  =>15,
                            "StateIdNum"  =>"99999999",
                            "StateIncome"  =>16),
                            array(
                                "StateCd"  =>"AZ",
                                "StateWH"  =>14,
                                "StateIdNum"  =>"99-9999999",
                                "StateIncome"  =>16)
                        ),
                    
                    )
                )
            )
        );
     
        $jwtController= new JwtController();

        $accessToken = $jwtController->get_access_token();

        error_log($accessToken);

        $response= Http::withHeaders([
            'Authorization' =>  $accessToken
         ])->post( env('TBS_BASE_URL').'Form1099NEC/Create', 
           $form1099NECCreateRequest
        );

        error_log($response);
            
        return $response;
    }

    

    

    
    
}
