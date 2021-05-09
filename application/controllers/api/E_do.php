<?php

//  header('Access-Control-Allow-Origin: *');
//  header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");

use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');


// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
//To Solve File REST_Controller not found
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';
require APPPATH . 'libraries/JWT.php';
use \Firebase\JWT\JWT;
use Spipu\Html2Pdf\Html2Pdf;

// use \Firebase\JWT\SignatureInvalidException;

// use Restserver\Libraries\REST_Controller;
/**
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array
 *
 * @package         CodeIgniter
 * @subpackage      Rest Server
 * @category        Controller
 * @author          Phil Sturgeon, Chris Kacerguis
 * @license         MIT
 * @link            https://github.com/chriskacerguis/codeigniter-restserver
 * 
 */


class E_do extends REST_Controller {

    private $secret = 'KairosSCLEDO';

    function __construct()
    {
     
        parent::__construct();
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        $method = $_SERVER['REQUEST_METHOD'];
        if($method == "OPTIONS") {
        die();
        }

        $this->load->model('Crud');
        $this->load->library('ciqrcode');	  

    }
   


    public function login_post(){
        $date = new DateTime();
        
        $jsonArray = json_decode($this->input->raw_input_stream, true);
        $password = $jsonArray['password'];
        
        $query = $this->db->query("SELECT * FROM users where email ='".$jsonArray['email']."'")->row_array();
        // var_dump($query['password']);
        if($query){
            $hash= $query['password'];

            // var_dump(password_verify($password, $hash));die();
            if(password_verify($password, $hash)){
                $is_valid = true;
            }else{
                $is_valid = false;
            }
    
    
            if($is_valid == false){
                $this->response([
                    'success' => false,
                    'message' => 'wrong email or password'
                ], REST_Controller::HTTP_NOT_FOUND);
    
              
            }else{
                // $payload['id'] = $query['user_id'];  
                // $payload['email'] = $query['email'];  
                // $payload['name'] = $query['name'];  
                // $payload['role'] = $query['role'];
                // $payload['iat'] = $date->getTimestamp();
                // $payload['exp'] = $date->getTimestamp() +60*60*8;

                // $output['id_token'] = JWT::encode($payload, $this->secret); 
                // $this->response($output);

                $this->set_response(['status'=>'success','data'=>$query], REST_Controller::HTTP_OK); // CREATED (201) being the HTTP response code


            }
        }else{
            $this->response([
                'success' => false,
                'message' => 'email not found'
            ], REST_Controller::HTTP_NOT_FOUND);

          
        }
     

        
    }
    public function checktoken_get(){
   
        
        $jwt = $this->input->get_request_header('Authorization');
        try{
            $decoded = JWT::decode($jwt,$this->secret,array('HS256'));
       
            return $decoded;
        }catch( error $e){
           
            $this->response(['success' => false,'message' => 'Expired token'], REST_Controller::HTTP_NOT_FOUND);

        }
       
    }

    // super admin scl dan admin scl getdata and count edo
    public function index_get($id=NULL)
    {
   

            $countEDO= $this->db->query("SELECT COUNT(edo_id) as jumlah_edo from e_do")->row_array();
            $countEDOrequested= $this->db->query("SELECT COUNT(edo_id) as jumlah_edo_requested from e_do where status='UNPAID'")->row_array();
            $countEDOapproved= $this->db->query("SELECT COUNT(edo_id) as jumlah_edo_approved from e_do where status='PAID'")->row_array();
            $countEDOrejected= $this->db->query("SELECT COUNT(edo_id) as jumlah_edo_rejected from e_do where status='REJECTED'")->row_array();
            $countEDOpicked_up= $this->db->query("SELECT COUNT(edo_id) as jumlah_edo_picked_up from e_do where status='RELEASED'")->row_array();
            $countEDOreissued= $this->db->query("SELECT COUNT(edo_id) as jumlah_edo_onhold from e_do where status='ON HOLD'")->row_array();
    
     
            if($id === NULL){

                $selectEDO = $this->db->query("SELECT edo_id,edo_number,shipper_name,consignee_name,shipper_address,consignee_email,notify,house_bl_number,mbl_number, DATE_FORMAT(arrival_date,GET_FORMAT(DATE,'ISO')) AS arrival_date,ocean_vessel,voyage_number,container_seal_number,port_of_loading,port_of_discharges,final_destination,description_of_goods,gross_weight,measurment,package,marks_and_number, DATE_FORMAT(created_at,GET_FORMAT(DATETIME,'ISO')) AS created_at,created_by,picked_up_by,status, DATE_FORMAT(updated_at,GET_FORMAT(DATETIME,'ISO')) AS updated_at, DATE_FORMAT(paid_at,GET_FORMAT(DATETIME,'ISO')) AS paid_at, DATE_FORMAT(rejected_at,GET_FORMAT(DATETIME,'ISO')) AS rejected_at, DATE_FORMAT(reissued_at,GET_FORMAT(DATETIME,'ISO')) AS reissued_at, DATE_FORMAT(released_at,GET_FORMAT(DATETIME,'ISO')) AS released_at,send_mail,barcode_image,printed,shipper_email,consignee_address,DATE_FORMAT(house_bl_date,GET_FORMAT(DATE,'ISO')) AS house_bl_date,number_of_package,notify_address,status_description,place_of_receipt,number_of_quantity FROM e_do ORDER BY edo_id DESC");     
                // $selectEDO = $this->db->query("SELECT * FROM e_do ORDER BY edo_id DESC");
            }else{
                // $whereId = [
                    //     'edo_id'=>$id
                    // ];
                // $selectEDO = $this->db->query("SELECT * FROM e_do where edo_id='".$id."' ORDER BY edo_id DESC");
                $selectEDO = $this->db->query("SELECT edo_id,edo_number,shipper_name,consignee_name,shipper_address,consignee_email,notify,house_bl_number,mbl_number, DATE_FORMAT(arrival_date,GET_FORMAT(DATE,'ISO')) AS arrival_date,ocean_vessel,voyage_number,container_seal_number,port_of_loading,port_of_discharges,final_destination,description_of_goods,gross_weight,measurment,package,marks_and_number, DATE_FORMAT(created_at,GET_FORMAT(DATETIME,'ISO')) AS created_at,created_by,picked_up_by,status, DATE_FORMAT(updated_at,GET_FORMAT(DATETIME,'ISO')) AS updated_at, DATE_FORMAT(paid_at,GET_FORMAT(DATETIME,'ISO')) AS paid_at, DATE_FORMAT(rejected_at,GET_FORMAT(DATETIME,'ISO')) AS rejected_at, DATE_FORMAT(reissued_at,GET_FORMAT(DATETIME,'ISO')) AS reissued_at, DATE_FORMAT(released_at,GET_FORMAT(DATETIME,'ISO')) AS released_at,send_mail,barcode_image,printed,shipper_email,consignee_address,DATE_FORMAT(house_bl_date,GET_FORMAT(DATE,'ISO')) AS house_bl_date,number_of_package,notify_address,status_description,place_of_receipt,number_of_quantity FROM e_do where edo_id='".$id."'");    
                // $selectEDO = $this->db->query("SELECT edo_id,edo_number,shipper_name,consignee_name,shipper_address,consignee_email,notify,house_bl_number,mbl_number, DATE_FORMAT(arrival_date,'%d/%m/%Y') AS arrival_date,ocean_vessel,voyage_number,container_seal_number,port_of_loading,port_of_discharges,final_destination,description_of_goods,gross_weight,measurment,package,marks_and_number, DATE_FORMAT(created_at,'%d/%m/%Y') AS created_at,created_by,picked_up_by,status, DATE_FORMAT(updated_at,'%d/%m/%Y') AS updated_at, DATE_FORMAT(paid_at,'%d/%m/%Y') AS paid_at, DATE_FORMAT(rejected_at,'%d/%m/%Y') AS rejected_at, DATE_FORMAT(reissued_at,'%d/%m/%Y') AS reissued_at, DATE_FORMAT(released_at,'%d/%m/%Y') AS released_at,send_mail,barcode_image,printed,shipper_email,consignee_address,DATE_FORMAT(house_bl_date,'%d/%m/%Y') AS house_bl_date,number_of_package,notify_address,status_description,place_of_receipt
                // FROM e_do where edo_id = '".$id."'"); 


            }

            if($selectEDO->num_rows() > 0)
            {
                $data= $selectEDO->result_array();
                $this->response(['status'=>'success','total'=>$countEDO['jumlah_edo'],'unpaid'=>$countEDOrequested['jumlah_edo_requested'],'paid'=>$countEDOapproved['jumlah_edo_approved'],'rejected'=>$countEDOrejected['jumlah_edo_rejected'],'released'=>$countEDOpicked_up['jumlah_edo_picked_up'],'hold_on'=>$countEDOreissued['jumlah_edo_onhold'],'data'=>$data], REST_Controller::HTTP_OK);
            }else{
                $this->response(['data'=>'Data not found'], REST_Controller::HTTP_NOT_FOUND);
            }  
        // }else{
        //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

        // }      
    }
    public function total_e_do_get()
    { 
        
   
        // $role_user = $this->checktoken_get();

        // if($role_user->role === 'admin'|| $role_user->role === 'dokumen' || $role_user->role === 'supervisor' ){
         
   
        $countEDO= $this->db->query("SELECT COUNT(edo_id) as jumlah_edo from e_do")->row_array();
        $countEDOrequested= $this->db->query("SELECT COUNT(edo_id) as jumlah_edo_requested from e_do where status='UNPAID'")->row_array();
        $countEDOapproved= $this->db->query("SELECT COUNT(edo_id) as jumlah_edo_approved from e_do where status='PAID'")->row_array();
        $countEDOrejected= $this->db->query("SELECT COUNT(edo_id) as jumlah_edo_rejected from e_do where status='REJECTED'")->row_array();
        $countEDOpicked_up= $this->db->query("SELECT COUNT(edo_id) as jumlah_edo_picked_up from e_do where status='RELEASED'")->row_array();
        $countEDOreissued= $this->db->query("SELECT COUNT(edo_id) as jumlah_edo_onhold from e_do where status='ON HOLD'")->row_array();

 

            if($countEDO){
                    $this->response(['status'=>'success','total'=>$countEDO['jumlah_edo'],'unpaid'=>$countEDOrequested['jumlah_edo_requested'],'paid'=>$countEDOapproved['jumlah_edo_approved'],'rejected'=>$countEDOrejected['jumlah_edo_rejected'],'released'=>$countEDOpicked_up['jumlah_edo_picked_up'],'hold_on'=>$countEDOreissued['jumlah_edo_onhold']], REST_Controller::HTTP_OK);
            }else{
                    $this->response(['data'=>'Data not found'], REST_Controller::HTTP_NOT_FOUND);
            }  
        // }else{
        //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

        // }      
    }

    // admin scl
    public function index_post(){ 
        
   

        $role_user = $this->checktoken_get();

        // if($role_user->role === 'dokumen'){
            $edo_number = $role_user->id.date('Y').date('m').date('d').date('His');

            // var_dump($edo_number);die();
            $data = [
                'edo_number' => $edo_number,
                'shipper_name' => $this->post('shipper_name'),
                'shipper_email' => $this->post('shipper_email'),
                'consignee_name' => $this->post('consignee_name'),
                'shipper_address' => $this->post('shipper_address'),
                'consignee_email' => $this->post('consignee_email'),
                'notify' => $this->post('notify'),
                'house_bl_number' => $this->post('house_bl_number'),
                'mbl_number' => $this->post('mbl_number'),
                'arrival_date' => $this->post('arrival_date'),
                'ocean_vessel' => $this->post('ocean_vessel'),
                'voyage_number' => $this->post('voyage_number'),
                'container_seal_number' => $this->post('container_seal_number'),
                'port_of_loading' => $this->post('port_of_loading'),
                'port_of_discharges' => $this->post('port_of_discharges'),
                'final_destination' => $this->post('final_destination'),
                'description_of_goods' => $this->post('description_of_goods'),
                'gross_weight' => $this->post('gross_weight'),
                'measurment' => $this->post('measurment'),
                'package' => $this->post('package'),
                'marks_and_number' => $this->post('marks_and_number'),
                'created_at' => date("Y-m-d H:i:s"),
                'created_by' => $role_user->name,
                'status' => 'UNPAID',
                'barcode_image'=> $edo_number.'.png',
                'consignee_address' => $this->post('consignee_address'),
                'house_bl_date' => $this->post('house_bl_date'),
                'number_of_package' => $this->post('number_of_package'),
                'notify_address' => $this->post('notify_address'),
                'place_of_receipt' => $this->post('place_of_receipt'),
                'number_of_quantity' => $this->post('number_of_quantity')
            ];


            $createUser =  $this->Crud->createData('e_do',$data);

            if($createUser){
                // // generate qrcode
                // $config['cacheable']	= true; //boolean, the default is true
                // $config['cachedir']		= './assets/qrcode/'; //string, the default is application/cache/
                $config['imagedir']		= './assets/qrcode/'; //string, the default is application/cache/
                $config['quality']		= true; //boolean, the default is true
                $config['size']			= '1024'; //interger, the default is 1024
                $config['black']		= array(224,255,255); // array, default is array(255,255,255)
                $config['white']		= array(70,130,180); // array, default is array(0,0,0)
                $this->ciqrcode->initialize($config);


                $params['data'] = $edo_number;
                $params['level'] = 'H';
                $params['size'] = 10;
                $params['savename'] = FCPATH.$config['imagedir'].''.$edo_number.'.png';

                $this->ciqrcode->generate($params);

                $this->set_response(['status'=>'Success created e-DO!','data'=>$data], REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
            }else{
                $this->response(['status'=>'Failed created e-DO!'], REST_Controller::HTTP_BAD_REQUEST); 
            }

        // }else{
        //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

        // }
    }

    // admin scl edit edo
    public function index_put($edo_id){
        
   

        // $role_user = $this->checktoken_get();

        // if($role_user->role === 'dokumen'){
            // var_dump($this->put('edo_number'));die();
            
            $whereId = [
                'edo_id'=>$edo_id
            ];
            $selectEDO = $this->Crud->readData('*','e_do',$whereId); 

            $data = [
                'shipper_name' => $this->put('shipper_name'),
                'consignee_name' => $this->put('consignee_name'),
                'shipper_email' => $this->put('shipper_email'),
                'shipper_address' => $this->put('shipper_address'),
                'consignee_email' => $this->put('consignee_email'),
                'notify' => $this->put('notify'),
                'house_bl_number' => $this->put('house_bl_number'),
                'mbl_number' => $this->put('mbl_number'),
                'arrival_date' => $this->put('arrival_date'),
                'ocean_vessel' => $this->put('ocean_vessel'),
                'voyage_number' => $this->put('voyage_number'),
                'container_seal_number' => $this->put('container_seal_number'),
                'port_of_loading' => $this->put('port_of_loading'),
                'port_of_discharges' => $this->put('port_of_discharges'),
                'final_destination' => $this->put('final_destination'),
                'description_of_goods' => $this->put('description_of_goods'),
                'gross_weight' => $this->put('gross_weight'),
                'measurment' => $this->put('measurment'),
                'package' => $this->put('package'),
                'marks_and_number' => $this->put('marks_and_number'),
                'updated_at' => date("Y-m-d H:i:s"),
                'consignee_address' => $this->put('consignee_address'),
                'house_bl_date' => $this->put('house_bl_date'),
                'number_of_package' => $this->put('number_of_package'),
                'notify_address' => $this->put('notify_address'),
                'place_of_receipt' => $this->put('place_of_receipt'),
                'number_of_quantity' => $this->put('number_of_quantity')
            ];

            if($selectEDO->num_rows() > 0)
            {
                $selectstatus= $selectEDO->row_array();
                if($selectstatus['paid_at'] == null && $selectstatus['rejected_at'] == null){
                    // var_dump($edo_number);die();
                    $updateEDO =  $this->Crud->updateData('e_do',$data,$whereId);

                    if($updateEDO){
                        $this->set_response(['status'=>'Success Updated e-DO!'], REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
                    }else{
                        $this->response(['status'=>'Failed Updated e-DO!'], REST_Controller::HTTP_BAD_REQUEST); 
                    }
                }else if($selectstatus['status'] == 'REJECTED'){
                    $data['status']='UNPAID';
                    $data['rejected_at']=NULL;
                    $updateEDO =  $this->Crud->updateData('e_do',$data,$whereId);
                    if($updateEDO){
                        $this->set_response(['status'=>'Success Updated e-DO!'], REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
                    }else{
                        $this->response(['status'=>'Failed Updated e-DO!'], REST_Controller::HTTP_BAD_REQUEST); 
                    }
                }else{
                    $this->response(['status'=>'Failed Updated e-DO , e-DO has been approved/ rejected!'], REST_Controller::HTTP_BAD_REQUEST); 
                }
              
        
            }else{
                $this->response(['status'=>'Failed e-DO id not found!'], REST_Controller::HTTP_BAD_REQUEST); 

            }
        // }else{
        //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

        // }

    }     

    // admin scl delete edo
    public function index_delete($edo_id){
        
   

        // $role_user = $this->checktoken_get();

        // if($role_user->role === 'dokumen'){
          
            $whereId = [
                'edo_id'=>$edo_id
            ];
            $selectEDO = $this->Crud->readData('*','e_do',$whereId); 

            if($selectEDO->num_rows() > 0)
            {
                $deleteEDO =  $this->Crud->deleteData('e_do',$whereId);

                if($deleteEDO){
                    $this->set_response(['status'=>'Success Delete e-DO!'], REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
                }else{
                    $this->response(['status'=>'Failed Delete e-DO!'], REST_Controller::HTTP_BAD_REQUEST); 

                }
            }else{
                $this->response(['status'=>'Failed e-DO id not found!'], REST_Controller::HTTP_BAD_REQUEST); 

            }
        // }else{
        //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

        // }

    }

// supperadmin scl approve
    public function approve_put($edo_id){
   
        // $role_user = $this->checktoken_get();

        // if($role_user->role === 'admin'){
          
        
            $bl_number = $this->put('house_bl_number');
            if(empty($bl_number)){
               return $this->response(['status'=>'Failed silahkan masukan house bl number!'], REST_Controller::HTTP_BAD_REQUEST); 
            }

            $whereId = [
                'edo_id'=>$edo_id
            ];

            // $selectEDO = $this->db->query("SELECT * FROM e_do where edo_id='".$id."' AND house_bl_number='".$bl_number."'");
            $selectEDO = $this->db->query("SELECT edo_id,edo_number,shipper_name,consignee_name,shipper_address,consignee_email,notify,house_bl_number,mbl_number, DATE_FORMAT(arrival_date,GET_FORMAT(DATE,'ISO')) AS arrival_date,ocean_vessel,voyage_number,container_seal_number,port_of_loading,port_of_discharges,final_destination,description_of_goods,gross_weight,measurment,package,marks_and_number, DATE_FORMAT(created_at,GET_FORMAT(DATETIME,'ISO')) AS created_at,created_by,picked_up_by,status, DATE_FORMAT(updated_at,GET_FORMAT(DATETIME,'ISO')) AS updated_at, DATE_FORMAT(paid_at,GET_FORMAT(DATETIME,'ISO')) AS paid_at, DATE_FORMAT(rejected_at,GET_FORMAT(DATETIME,'ISO')) AS rejected_at, DATE_FORMAT(reissued_at,GET_FORMAT(DATETIME,'ISO')) AS reissued_at, DATE_FORMAT(released_at,GET_FORMAT(DATETIME,'ISO')) AS released_at,send_mail,barcode_image,printed,shipper_email,consignee_address,DATE_FORMAT(house_bl_date,GET_FORMAT(DATE,'ISO')) AS house_bl_date,number_of_package,notify_address,status_description,place_of_receipt FROM e_do where edo_id = '".$edo_id."' AND house_bl_number='".$bl_number."'");  


            // $selectEDO = $this->db->query("SELECT edo_id,edo_number,shipper_name,consignee_name,shipper_address,consignee_email,notify,house_bl_number,mbl_number, DATE_FORMAT(arrival_date,'%d/%m/%Y') AS arrival_date,ocean_vessel,voyage_number,container_seal_number,port_of_loading,port_of_discharges,final_destination,description_of_goods,gross_weight,measurment,package,marks_and_number, DATE_FORMAT(created_at,'%d/%m/%Y') AS created_at,created_by,picked_up_by,status, DATE_FORMAT(updated_at,'%d/%m/%Y') AS updated_at, DATE_FORMAT(paid_at,'%d/%m/%Y') AS paid_at, DATE_FORMAT(rejected_at,'%d/%m/%Y') AS rejected_at, DATE_FORMAT(reissued_at,'%d/%m/%Y') AS reissued_at, DATE_FORMAT(released_at,'%d/%m/%Y') AS released_at,send_mail,barcode_image,printed,shipper_email,consignee_address,DATE_FORMAT(house_bl_date,'%d/%m/%Y') AS house_bl_date,number_of_package,notify_address,status_description,place_of_receipt
            // FROM e_do where edo_id = '".$edo_id."' AND house_bl_number='".$bl_number."'"); 

            $data = [
            
                'status' => 'PAID',
                'paid_at' => date("Y-m-d H:i:s"),
                'rejected_at' => null,
            ];

            if($selectEDO->num_rows() > 0)
            {
                $selectstatus= $selectEDO->row_array();

               
                // var_dump($selectstatus['paid_at']);die();

                if($selectstatus['rejected_at'] == null){
                // var_dump($edo_number);die();
                    $updateEDO =  $this->Crud->updateData('e_do',$data,$whereId);

                    if($updateEDO){
                        $this->set_response(['status'=>'Success Approved e-DO!'], REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
                    }else{
                        $this->response(['status'=>'Failed Approve e-DO!'], REST_Controller::HTTP_BAD_REQUEST); 
                    }
                }else{
                    $this->response(['status'=>'Failed to Approve! E-do has been rejected!'], REST_Controller::HTTP_BAD_REQUEST); 

                }
            }else{
                $this->response(['status'=>'Failed e-DO id not found or wrong house bl number!'], REST_Controller::HTTP_BAD_REQUEST); 

            }
        // }else{
        //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

        // }

    }     

// admin scl rejected
    public function reject_put($edo_id){
   
        // $role_user = $this->checktoken_get();

        // if($role_user->role === 'admin'){
          
            $bl_number = $this->put('house_bl_number');
            if(empty($bl_number)){
                return $this->response(['status'=>'Failed silahkan masukan house bl number!'], REST_Controller::HTTP_BAD_REQUEST); 
             }
            $whereId = [
                'edo_id'=>$edo_id
            ];
            // $selectEDO = $this->db->query("SELECT edo_id,edo_number,shipper_name,consignee_name,shipper_address,consignee_email,notify,house_bl_number,mbl_number, DATE_FORMAT(arrival_date,'%d/%m/%Y') AS arrival_date,ocean_vessel,voyage_number,container_seal_number,port_of_loading,port_of_discharges,final_destination,description_of_goods,gross_weight,measurment,package,marks_and_number, DATE_FORMAT(created_at,'%d/%m/%Y') AS created_at,created_by,picked_up_by,status, DATE_FORMAT(updated_at,'%d/%m/%Y') AS updated_at, DATE_FORMAT(paid_at,'%d/%m/%Y') AS paid_at, DATE_FORMAT(rejected_at,'%d/%m/%Y') AS rejected_at, DATE_FORMAT(reissued_at,'%d/%m/%Y') AS reissued_at, DATE_FORMAT(released_at,'%d/%m/%Y') AS released_at,send_mail,barcode_image,printed,shipper_email,consignee_address,DATE_FORMAT(house_bl_date,'%d/%m/%Y') AS house_bl_date,number_of_package,notify_address,status_description,place_of_receipt
            // FROM e_do where edo_id = '".$edo_id."' AND house_bl_number='".$bl_number."'"); 

            $selectEDO = $this->db->query("SELECT edo_id,edo_number,shipper_name,consignee_name,shipper_address,consignee_email,notify,house_bl_number,mbl_number, DATE_FORMAT(arrival_date,GET_FORMAT(DATE,'ISO')) AS arrival_date,ocean_vessel,voyage_number,container_seal_number,port_of_loading,port_of_discharges,final_destination,description_of_goods,gross_weight,measurment,package,marks_and_number, DATE_FORMAT(created_at,GET_FORMAT(DATETIME,'ISO')) AS created_at,created_by,picked_up_by,status, DATE_FORMAT(updated_at,GET_FORMAT(DATETIME,'ISO')) AS updated_at, DATE_FORMAT(paid_at,GET_FORMAT(DATETIME,'ISO')) AS paid_at, DATE_FORMAT(rejected_at,GET_FORMAT(DATETIME,'ISO')) AS rejected_at, DATE_FORMAT(reissued_at,GET_FORMAT(DATETIME,'ISO')) AS reissued_at, DATE_FORMAT(released_at,GET_FORMAT(DATETIME,'ISO')) AS released_at,send_mail,barcode_image,printed,shipper_email,consignee_address,DATE_FORMAT(house_bl_date,GET_FORMAT(DATE,'ISO')) AS house_bl_date,number_of_package,notify_address,status_description,place_of_receipt,number_of_quantity FROM e_do where edo_id = '".$edo_id."' AND house_bl_number='".$bl_number."'");  

            $data = [
                'status_description'=>$this->put('description'),
                'status' => 'REJECTED',
                'rejected_at' => date("Y-m-d H:i:s"),
                'paid_at' => null,
            ];

            if($selectEDO->num_rows() > 0)
            {
                $selectstatus= $selectEDO->row_array();

                // var_dump($selectstatus['paid_at']);die();

                if($selectstatus['paid_at'] == null || $selectstatus['reissued_at'] != null ){

                    // var_dump($edo_number);die();
                    $updateEDO =  $this->Crud->updateData('e_do',$data,$whereId);

                    if($updateEDO){
                        $this->set_response(['status'=>'Success Rejected e-DO!'], REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
                    }else{
                        $this->response(['status'=>'Failed Rejected e-DO!'], REST_Controller::HTTP_BAD_REQUEST); 
                    }
                }else{
                    $this->response(['status'=>'Failed to reject! E-do has been approved!'], REST_Controller::HTTP_BAD_REQUEST); 

                }
            }else{
                $this->response(['status'=>'Failed e-DO id not found or wrong house bl number!'], REST_Controller::HTTP_BAD_REQUEST); 

            }
        // }else{
        //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

        // }

    }     

// reissued
    public function reissued_put($edo_id){
   
        // $role_user = $this->checktoken_get();

        // if($role_user->role === 'admin'){
          
        
            $bl_number = $this->put('house_bl_number');
            if(empty($bl_number)){
                return $this->response(['status'=>'Failed silahkan masukan house bl number!'], REST_Controller::HTTP_BAD_REQUEST); 
             }
            $whereId = [
                'edo_id'=>$edo_id
            ];
            // $selectEDO = $this->db->query("SELECT edo_id,edo_number,shipper_name,consignee_name,shipper_address,consignee_email,notify,house_bl_number,mbl_number, DATE_FORMAT(arrival_date,'%d/%m/%Y') AS arrival_date,ocean_vessel,voyage_number,container_seal_number,port_of_loading,port_of_discharges,final_destination,description_of_goods,gross_weight,measurment,package,marks_and_number, DATE_FORMAT(created_at,'%d/%m/%Y') AS created_at,created_by,picked_up_by,status, DATE_FORMAT(updated_at,'%d/%m/%Y') AS updated_at, DATE_FORMAT(paid_at,'%d/%m/%Y') AS paid_at, DATE_FORMAT(rejected_at,'%d/%m/%Y') AS rejected_at, DATE_FORMAT(reissued_at,'%d/%m/%Y') AS reissued_at, DATE_FORMAT(released_at,'%d/%m/%Y') AS released_at,send_mail,barcode_image,printed,shipper_email,consignee_address,DATE_FORMAT(house_bl_date,'%d/%m/%Y') AS house_bl_date,number_of_package,notify_address,status_description,place_of_receipt
            // FROM e_do where edo_id = '".$edo_id."' AND house_bl_number='".$bl_number."'");  

            $selectEDO = $this->db->query("SELECT edo_id,edo_number,shipper_name,consignee_name,shipper_address,consignee_email,notify,house_bl_number,mbl_number, DATE_FORMAT(arrival_date,GET_FORMAT(DATE,'ISO')) AS arrival_date,ocean_vessel,voyage_number,container_seal_number,port_of_loading,port_of_discharges,final_destination,description_of_goods,gross_weight,measurment,package,marks_and_number, DATE_FORMAT(created_at,GET_FORMAT(DATETIME,'ISO')) AS created_at,created_by,picked_up_by,status, DATE_FORMAT(updated_at,GET_FORMAT(DATETIME,'ISO')) AS updated_at, DATE_FORMAT(paid_at,GET_FORMAT(DATETIME,'ISO')) AS paid_at, DATE_FORMAT(rejected_at,GET_FORMAT(DATETIME,'ISO')) AS rejected_at, DATE_FORMAT(reissued_at,GET_FORMAT(DATETIME,'ISO')) AS reissued_at, DATE_FORMAT(released_at,GET_FORMAT(DATETIME,'ISO')) AS released_at,send_mail,barcode_image,printed,shipper_email,consignee_address,DATE_FORMAT(house_bl_date,GET_FORMAT(DATE,'ISO')) AS house_bl_date,number_of_package,notify_address,status_description,place_of_receipt,number_of_quantity FROM e_do where edo_id = '".$edo_id."' AND house_bl_number='".$bl_number."'");  
            $data = [
                'status_description'=>$this->put('description'),
                'reissued_at' => date("Y-m-d H:i:s")
            ];

            if($selectEDO->num_rows() > 0)
            {
                $selectstatus= $selectEDO->row_array();

                // var_dump($selectstatus['paid_at']);die();

                if($selectstatus['paid_at'] != null && $selectstatus['reissued_at'] == null){
                // var_dump($edo_number);die();
                    $data['status'] = 'ON HOLD';
                    $data['reissued_at'] = date("Y-m-d H:i:s");
                    $updateEDO =  $this->Crud->updateData('e_do',$data,$whereId);
                   

                    if($updateEDO){
                        $this->set_response(['status'=>'Success REISSUED e-DO! status ON HOLD'], REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
                    }else{
                        $this->response(['status'=>'Failed REISSUED e-DO!'], REST_Controller::HTTP_BAD_REQUEST); 
                    }
                }else if($selectstatus['paid_at'] != null && $selectstatus['reissued_at'] != null){
                    $data['status'] = 'PAID';
                    $data['reissued_at'] = null;
                    $updateEDO =  $this->Crud->updateData('e_do',$data,$whereId);
                   

                    if($updateEDO){
                        $this->set_response(['status'=>'Success REISSUED e-DO! status PAID'], REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
                    }else{
                        $this->response(['status'=>'Failed REISSUED e-DO!'], REST_Controller::HTTP_BAD_REQUEST); 
                    }
                }
                
                else{
                    $this->response(['status'=>'Failed to REISSUED! E-do has been REISSUED!'], REST_Controller::HTTP_BAD_REQUEST); 

                }
            }else{
                $this->response(['status'=>'Failed e-DO id not found or wrong house bl number!'], REST_Controller::HTTP_BAD_REQUEST); 

            }
        // }else{
        //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

        // }

    }


    // reissued
    // public function reissued_put($edo_id){
   
    //     // $role_user = $this->checktoken_get();

    //     // if($role_user->role === 'admin'){
          
        
    //         $bl_number = $this->put('house_bl_number');
    //         if(empty($bl_number)){
    //             return $this->response(['status'=>'Failed silahkan masukan house bl number!'], REST_Controller::HTTP_BAD_REQUEST); 
    //          }
    //         $whereId = [
    //             'edo_id'=>$edo_id
    //         ];
    //         // $selectEDO = $this->db->query("SELECT edo_id,edo_number,shipper_name,consignee_name,shipper_address,consignee_email,notify,house_bl_number,mbl_number, DATE_FORMAT(arrival_date,'%d/%m/%Y') AS arrival_date,ocean_vessel,voyage_number,container_seal_number,port_of_loading,port_of_discharges,final_destination,description_of_goods,gross_weight,measurment,package,marks_and_number, DATE_FORMAT(created_at,'%d/%m/%Y') AS created_at,created_by,picked_up_by,status, DATE_FORMAT(updated_at,'%d/%m/%Y') AS updated_at, DATE_FORMAT(paid_at,'%d/%m/%Y') AS paid_at, DATE_FORMAT(rejected_at,'%d/%m/%Y') AS rejected_at, DATE_FORMAT(reissued_at,'%d/%m/%Y') AS reissued_at, DATE_FORMAT(released_at,'%d/%m/%Y') AS released_at,send_mail,barcode_image,printed,shipper_email,consignee_address,DATE_FORMAT(house_bl_date,'%d/%m/%Y') AS house_bl_date,number_of_package,notify_address,status_description,place_of_receipt
    //         // FROM e_do where edo_id = '".$edo_id."' AND house_bl_number='".$bl_number."'");  

    //         $selectEDO = $this->db->query("SELECT edo_id,edo_number,shipper_name,consignee_name,shipper_address,consignee_email,notify,house_bl_number,mbl_number, DATE_FORMAT(arrival_date,'%d-%m-%Y') AS arrival_date,ocean_vessel,voyage_number,container_seal_number,port_of_loading,port_of_discharges,final_destination,description_of_goods,gross_weight,measurment,package,marks_and_number, DATE_FORMAT(created_at,'%d-%m-%Y %H:%i:%s') AS created_at,created_by,picked_up_by,status, DATE_FORMAT(updated_at,'%d-%m-%Y %H:%i:%s') AS updated_at, DATE_FORMAT(paid_at,'%d-%m-%Y %H:%i:%s') AS paid_at, DATE_FORMAT(rejected_at,'%d-%m-%Y %H:%i:%s') AS rejected_at, DATE_FORMAT(reissued_at,'%d-%m-%Y %H:%i:%s') AS reissued_at, DATE_FORMAT(released_at,'%d-%m-%Y %H:%i:%s') AS released_at,send_mail,barcode_image,printed,shipper_email,consignee_address,DATE_FORMAT(house_bl_date,'%d-%m-%Y') AS house_bl_date,number_of_package,notify_address,status_description,place_of_receipt
    //         FROM e_do where edo_id = '".$edo_id."' AND house_bl_number='".$bl_number."'");  
    //         $data = [
    //             'status_description'=>$this->put('description'),
    //             'status' => 'REJECTED',
    //             'reissued_at' => date("Y-m-d H:i:s"),
    //             'rejected_at' => date("Y-m-d H:i:s"),
    //         ];

    //         if($selectEDO->num_rows() > 0)
    //         {
    //             $selectstatus= $selectEDO->row_array();

    //             // var_dump($selectstatus['paid_at']);die();

    //             if($selectstatus['paid_at'] != null){
    //             // var_dump($edo_number);die();
    //                 $updateEDO =  $this->Crud->updateData('e_do',$data,$whereId);

    //                 if($updateEDO){
    //                     $this->set_response(['status'=>'Success REISSUED e-DO!'], REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
    //                 }else{
    //                     $this->response(['status'=>'Failed REISSUED e-DO!'], REST_Controller::HTTP_BAD_REQUEST); 
    //                 }
    //             }else{
    //                 $this->response(['status'=>'Failed to REISSUED! E-do has been REISSUED!'], REST_Controller::HTTP_BAD_REQUEST); 

    //             }
    //         }else{
    //             $this->response(['status'=>'Failed e-DO id not found or wrong house bl number!'], REST_Controller::HTTP_BAD_REQUEST); 

    //         }
    //     // }else{
    //     //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

    //     // }

    // }

    // admin scl, admin scl, admin spl dan kasir search by edo number
    public function search_get(){
   
        // $role_user = $this->checktoken_get();

        // if($role_user->role === 'admin'|| $role_user->role === 'dokumen'  || $role_user->role === 'adminspl' || $role_user->role === 'kasir' || $role_user->role === 'supervisor' ){
            
            $edo_number = $this->get('e_do_number');
        
            // $whereId = [
            //     'edo_number'=>$edo_number
            // ];
            $selectEDO = $this->db->query("SELECT edo_id,edo_number,shipper_name,consignee_name,shipper_address,consignee_email,notify,house_bl_number,mbl_number, DATE_FORMAT(arrival_date,GET_FORMAT(DATE,'ISO')) AS arrival_date,ocean_vessel,voyage_number,container_seal_number,port_of_loading,port_of_discharges,final_destination,description_of_goods,gross_weight,measurment,package,marks_and_number, DATE_FORMAT(created_at,GET_FORMAT(DATETIME,'ISO')) AS created_at,created_by,picked_up_by,status, DATE_FORMAT(updated_at,GET_FORMAT(DATETIME,'ISO')) AS updated_at, DATE_FORMAT(paid_at,GET_FORMAT(DATETIME,'ISO')) AS paid_at, DATE_FORMAT(rejected_at,GET_FORMAT(DATETIME,'ISO')) AS rejected_at, DATE_FORMAT(reissued_at,GET_FORMAT(DATETIME,'ISO')) AS reissued_at, DATE_FORMAT(released_at,GET_FORMAT(DATETIME,'ISO')) AS released_at,send_mail,barcode_image,printed,shipper_email,consignee_address,DATE_FORMAT(house_bl_date,GET_FORMAT(DATE,'ISO')) AS house_bl_date,number_of_package,notify_address,status_description,place_of_receipt,number_of_quantity FROM e_do where edo_number = '".$edo_number."'"); 

            if($selectEDO->num_rows() > 0)
            {
                $data= $selectEDO->result_array();
                $this->response(['status'=>'success','data'=>$data], REST_Controller::HTTP_OK);
            }else{
                $this->response(['data'=>'Data not found'], REST_Controller::HTTP_NOT_FOUND);
            }     
        // }else{
        //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

        // }

    }

// admin spl picked up
    public function picked_up_put($edo_id){
   
        // $role_user = $this->checktoken_get();

        // if($role_user->role === 'adminspl'){
          
            $whereId = [
                'edo_id'=>$edo_id
            ];
            $selectEDO = $this->db->query("SELECT * FROM e_do where edo_id = '".$edo_id."'");  


            $data = [
                // 'picked_up_by' => $role_user->name,
                'status' => 'RELEASED',
                'released_at' => date("Y-m-d H:i:s")
            ];

            if($selectEDO->num_rows() > 0)
            {
                $selectstatus= $selectEDO->row_array();

                // var_dump($selectstatus['paid_at']);die();

                if($selectstatus['paid_at'] != null ){

                    // var_dump($edo_number);die();
                    $updateEDO =  $this->Crud->updateData('e_do',$data,$whereId);

                    if($updateEDO){
                        $this->set_response(['status'=>'Success Picked Up e-DO!'], REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
                    }else{
                        $this->response(['status'=>'Failed Picked Up e-DO!'], REST_Controller::HTTP_BAD_REQUEST); 
                    }
                }else{
                    $this->response(['status'=>'Failed to picked up! E-do not approved yet!'], REST_Controller::HTTP_BAD_REQUEST); 

                }
            }else{
                $this->response(['status'=>'Failed e-DO id not found!'], REST_Controller::HTTP_BAD_REQUEST); 

            }
        // }else{
        //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

        // }
    }



    // endpoint user admin scl

    public function users_get($id=NULL){
   

        // $role_user = $this->checktoken_get();

        // if($role_user->role === 'superadmin' || $role_user->role === 'admin'){
            $selectUser= $this->Crud->readData('*','users');
            $countUser= $this->db->query("SELECT COUNT(user_id) as jumlah_user from users")->row_array();

            if($id === NULL){
                $selectUser = $this->Crud->readData('*','users'); 
                
            }else{
                $whereId = [
                    'user_id'=>$id
                ];
                $selectUser = $this->Crud->readData('*','users',$whereId); 

            }

            if($selectUser->num_rows() > 0)
            {
                $data= $selectUser->result_array();
                $this->response(['status'=>'success','total users'=>$countUser['jumlah_user'],'data'=>$data], REST_Controller::HTTP_OK);
            }else{
                $this->response(['data'=>'Data not found'], REST_Controller::HTTP_NOT_FOUND);
            }
        // }else{
        //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

        // }
    }


    // admin scl create user
    public function users_post(){
   
        
        // $role_user = $this->checktoken_get();

        // if($role_user->role === 'superadmin' || $role_user->role === 'admin'){
            $email = $this->post('email');
            $whereId = [
                'email'=>$email
            ];
            $selectUser = $this->Crud->readData('*','users',$whereId)->row_array(); 
            // var_dump($selectUser);die();
            if($selectUser){

                $this->response(['status'=>'Failed created user, email already exist!'], REST_Controller::HTTP_BAD_REQUEST); 

            }else{
                $data = [
                    'email' => $email,
                    'name' => $this->post('name'),
		    'branch_office' => $this->post('branch_office'),
                    'no_telp' => $this->post('no_telp'),
                    'status' => $this->post('status'),
                    'role' => $this->post('role'),
                    'password' => password_hash($this->post('password'),PASSWORD_DEFAULT),
                    'created_at' => date("Y-m-d H:i:s")
                ];


                $createUser =  $this->Crud->createData('users',$data);

                // var_dump($createUser);die();
                if($createUser){
                    $this->set_response(['status'=>'Success created user','data'=>$data], REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
                }else{
                    $this->response(['status'=>'Failed created user!'], REST_Controller::HTTP_BAD_REQUEST); 
                }
            }
        // }else{
        //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

        // }

    }
//    admin scl
    public function users_put($user_id){
   

        // var_dump($this->put('edo_number'));die();
        // $role_user = $this->checktoken_get();

        // if($role_user->role === 'superadmin' || $role_user->role === 'admin'){
            $whereId = [
                'user_id'=>$user_id
            ];
            $selectUser = $this->Crud->readData('*','users',$whereId); 

            $data = [
                'email' => $this->put('email'),
                'name' => $this->put('name'),
		'branch_office' => $this->put('branch_office'),
                'no_telp' => $this->put('no_telp'),
                'status' => $this->put('status'),
                'password'=>password_hash($this->put('password'),PASSWORD_DEFAULT),
                'role' => $this->put('role'),
                'updated_at' => date("Y-m-d H:i:s")
            ];

            if($selectUser->num_rows() > 0)
            {

                // var_dump($edo_number);die();
                $updateUser =  $this->Crud->updateData('users',$data,$whereId);

                if($updateUser){
                    $this->set_response(['status'=>'Success Updated user!'], REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
                }else{
                    $this->response(['status'=>'Failed Updated user!'], REST_Controller::HTTP_BAD_REQUEST); 
                }
        
            }else{
                $this->response(['status'=>'Failed user id not found!'], REST_Controller::HTTP_BAD_REQUEST); 

            }
        // }else{
        //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

        // }

    }  
    

    //admin scl search user by name and email
    public function search_user_get(){
   
        // $role_user = $this->checktoken_get();

        // if($role_user->role === 'superadmin' || $role_user->role === 'admin'){

            $user_name = $this->get('name');
            $user_email = $this->get('email');
            
            // var_dump(isset($user_name) || isset($user_email));die();
            if(isset($user_name)){
            $selectUser = $this->db->query("SELECT * FROM users WHERE name='".$user_name."'");
            // var_dump($selectUser);die();
            }else if(isset($user_email)){
                $selectUser = $this->db->query("SELECT * FROM users WHERE email='".$user_email."'"); 
            }else{
                $this->response(['data'=>'Data not found'], REST_Controller::HTTP_NOT_FOUND);
            }



            if($selectUser->num_rows() > 0)
            {
                $data= $selectUser->row_array();
                $this->response(['status'=>'success','data'=>$data], REST_Controller::HTTP_OK);
            }else{
                $this->response(['data'=>'Data not found'], REST_Controller::HTTP_NOT_FOUND);
            }     
        // }else{
        //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

        // }

    }

    //admin scl delete user
    public function users_delete($user_id){
   
        // $role_user = $this->checktoken_get();

        // if($role_user->role === 'superadmin' || $role_user->role === 'admin'){

            $whereId = [
                'user_id'=>$user_id
            ];
            $selectUser = $this->Crud->readData('*','users',$whereId); 

            if($selectUser->num_rows() > 0)
            {
                $deleteUser =  $this->Crud->deleteData('users',$whereId);

                if($deleteUser){
                    $this->set_response(['status'=>'Success Delete user!'], REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
                }else{
                    $this->response(['status'=>'Failed Delete user!'], REST_Controller::HTTP_BAD_REQUEST); 

                }
            }else{
                $this->response(['status'=>'Failed user id not found!'], REST_Controller::HTTP_BAD_REQUEST); 

            }
        // }else{
        //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

        // }

    }

// all user edit passsword
    public function edit_password_put($user_id){

   
        $role_user = $this->checktoken_get();

        // if($role_user->role === 'superadmin'|| $role_user->role === 'admin'|| $role_user->role === 'dokumen'  || $role_user->role === 'adminspl' || $role_user->role === 'kasir' || $role_user->role === 'superadmin' ){
            $password= $this->put('password');
            $whereId = [
                'user_id'=>$user_id
            ];
            $selectUser = $this->Crud->readData('*','users',$whereId); 

                $data = [
                    'password' => password_hash($password,PASSWORD_DEFAULT)
                ];
        
                if($selectUser->num_rows() > 0)
                {
                    if($user_id == $role_user->id){
                        // var_dump($edo_number);die();
                        $updateUser =  $this->Crud->updateData('users',$data,$whereId);
                                
                        if($updateUser){
                            $this->set_response(['status'=>'Success Updated password!'], REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
                        }else{
                            $this->response(['status'=>'Failed Updated password!'], REST_Controller::HTTP_BAD_REQUEST); 
                        }
                    }else{
                        $this->response(['status'=>'Failed Updated password, user id not match with token!'], REST_Controller::HTTP_BAD_REQUEST); 
                    }
                   
            
                }else{
                    $this->response(['status'=>'Failed user id not found!'], REST_Controller::HTTP_BAD_REQUEST); 
        
                }
       
        // }else{
        //         $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);
    
        // }


    }
    // admin and admin scl
    public function print_get($edo_id){
   
        // $role_user = $this->checktoken_get();

        // if($role_user->role === 'admin'|| $role_user->role === 'dokumen' ){

        $whereId = [
           'edo_id'=>$edo_id
        ];
        $selectEDO = $this->db->query("SELECT * FROM e_do where edo_id =".$edo_id." AND printed = 0"); 

        // var_dump($selectEDO);die();
        if($selectEDO->num_rows() > 0)
        {
            $dataStatus = [
                'printed' => 1
            ];
            $this->Crud->updateData('e_do',$dataStatus,$whereId);
            $data= $selectEDO->result_array();
            $this->response(['status'=>'success','data'=>$data], REST_Controller::HTTP_OK);
        }else{
            $this->response(['data'=>'Data not found or already print!'], REST_Controller::HTTP_NOT_FOUND);
        }  

        // }else{
        //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

        // }
    }
// admin scl send to consignee
    public function send_to_consignee_get($edo_id){
   
/*
email : halo@clevara.id
password: clevara123abc
Outgoing Server: mail.clevara.id
SMTP Port: 465
*/

        // $role_user = $this->checktoken_get();

        // if($role_user->role === 'admin' ){

            // $toEmail = $this->post('emailReceipt');
            // $subject = $this->post('subject');
            // $bodyEmail = $this->post('message');

            // $edo_id = $this->get('edo_id');
           

            $selectEDO = $this->db->query("SELECT * FROM e_do where edo_id =".$edo_id." AND send_mail=0")->row_array(); 

            if(empty($selectEDO)){
                $this->response(['message'=>'Data not found or already sent!'], REST_Controller::HTTP_NOT_FOUND);
            }else{
                $html2pdf = new Html2Pdf();
                $html = "<table style=\"width: 700px;\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\">
                <tbody border=>
                <tr style=\"height: 66px;\">
                <td style=\"width: 355px; height: 126px; vertical-align: top;padding-left: 5px;\" rowspan=\"2\">
                <p style=\"margin-top: 5px;color: #424242;\">Shipper</p>
                <p>".$selectEDO['shipper_name']."</p>
                </td>
                <td style=\"width: 345px; height: 66px;\">
                <table style=\"width: 340px\">
                <tbody>
                <tr style=\"height: 27px;\">
                <td  style=\"width: 170px; height: 27px; color: #424242;padding-left: 5px;\">D/O No.</td>
                <td  style=\"width: 170px; height: 27px; color: #424242;padding-left: 5px;\">Print Date</td>
                </tr>
                <tr style=\"height: 28px;\">
                <td style=\"width: 170px; height: 28px;padding-left: 5px;\">".$selectEDO['edo_number']."</td>
                <td style=\"width: 170px; height: 28px;padding-left: 5px;\">".date('d/m/Y')."</td>
                </tr>
                </tbody>
                </table>
                </td>
                </tr>
                <tr style=\"height: 60px;\">
                <td style=\"width: 345px; height: 60px;\">
                <table style=\"width: 340px;\">
                <tbody>
                <tr>
                <td style=\"width: 170px;color: #424242;padding-left: 5px;\">House B/L No.</td>
                <td style=\"width: 170px;color: #424242;padding-left: 5px;\">MB/L No.</td>
                </tr>
                <tr>
                <td style=\"width: 99px;padding-left: 5px;\">".$selectEDO['house_bl_number']."</td>
                <td style=\"width: 130px;padding-left: 5px;\">".$selectEDO['mbl_number']."</td>
                </tr>
                </tbody>
                </table>
                </td>
                </tr>
                <tr style=\"height: 220px;\">
                <td style=\"width: 355px; height: 200px; vertical-align: top;padding-left: 5px;\">
                    <p style=\"margin-top: 5px;color: #424242\">Consignee</p>
                    <p>".$selectEDO['consignee_name']."<br>".$selectEDO['consignee_address']."</p></td>
                <td style=\"width: 345px; height: 260px;\" rowspan=\"2\" align=\"center\">Image Not Found <br> Image Not Found</td>
                </tr>
                <tr style=\"height: 20px;\">
                <td style=\"width: 355px; height: 30px;vertical-align: top;padding-left: 5px;\">
                <p style=\"margin-top: 5px;color: #424242\">Notify</p>
                <p>".$selectEDO['notify_address']."</p>
                </td>
                </tr>
                </tbody>
                </table>
                <table style=\"width: 700px\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\">
                <tbody>
                <tr style=\"height: 18px;\">
                <td style=\"height: 18px;vertical-align: top;padding-left: 5px;width:232px;\">
                <p style=\"margin-top: 5px;color: #424242\">Pre-carriage by</p>
                <p>-</p>
                </td>
                <td style=\"height: 18px;vertical-align: top;padding-left: 5px;width:233px;\">
                <p style=\"margin-top: 5px;color: #424242\">Place Of Receipt</p>
                <p>".$selectEDO['place_of_receipt']."</p>
                </td>
                <td style=\"height: 18px;vertical-align: top;padding-left: 5px;width:232px;\">
                <p style=\"margin-top: 5px;color: #424242\">Arrival Date</p>
                <p>".$selectEDO['arrival_date']."</p>
                </td>
                </tr>
                <tr style=\"height: 18px;\">
                <td style=\"height: 18px;vertical-align: top;padding-left: 5px;\">
                <p style=\"margin-top: 5px;color: #424242\">Ocean Vessel</p>
                <p>".$selectEDO['ocean_vessel']."</p>
                </td>
                <td style=\"height: 18px;vertical-align: top;padding-left: 5px;\">
                    <p style=\"margin-top: 5px;color: #424242\">Voyage No.</p>
                <p>".$selectEDO['voyage_number']."</p>
                </td>
                <td style=\"height: 18px;vertical-align: top;padding-left: 5px;\">
                    <p style=\"margin-top: 5px;color: #424242\">Container/Seal No.</p>
                <p>".$selectEDO['container_seal_number']."</p>
                </td>
                </tr>
                <tr style=\"height: 18px;\">
                    <td style=\"height: 18px;vertical-align: top;padding-left: 5px;\">
                     <p style=\"margin-top: 5px;color: #424242\">Port Of Lading</p>
                <p>".$selectEDO['port_of_loading']."</p>
                </td>
                <td style=\"height: 18px;vertical-align: top;padding-left: 5px;\">
                    <p style=\"margin-top: 5px;color: #424242\">Port Of Discharges</p>
                <p>".$selectEDO['port_of_discharges']."</p>
                </td>
                <td style=\"height: 18px;vertical-align: top;padding-left: 5px;\">
                    <p style=\"margin-top: 5px;color: #424242\">Final Destination</p>
                <p>".$selectEDO['final_destination']."</p>
                </td>
                </tr>
                <tr style=\"height: 76px;\">
                <td style=\"height: 229px; vertical-align: top;padding-left: 5px;\" colspan=\"2\">
                <p style=\"margin-top: 5px;color: #424242\">Description Of Goods</p>
                <p>".$selectEDO['description_of_goods']."</p>
                </td>
                <td style=\"height: 80px; vertical-align: top;padding-left: 5px;\">
                    <p style=\"margin-top: 5px;margin-bottom:5px;color: #424242\">Gross Weight</p>
                    <p style=\"margin-top: 0px;\">".$selectEDO['gross_weight']."</p>
                    <p style=\"margin-top: 32px;margin-bottom:5px;color: #424242\">Measurement</p>
                    <p style=\"margin-top: 0px;\">".$selectEDO['measurment']."</p>
                    <p style=\"margin-top: 32px;margin-bottom:5px;color: #424242\">Package</p>
                    <p style=\"margin-top: 0px;\">".$selectEDO['package']."</p>
                </td>
                </tr>
               
                <tr style=\"height: 8px;\">
                <td style=\"height: 8px;vertical-align: top;padding-left: 5px;\" colspan=\"3\">
                <p style=\"margin-top: 5px;color: #424242\">Marks & Numbers</p>
                <p>".$selectEDO['marks_and_number']."</p>
                </td>
                </tr>
                </tbody>
                </table>";
                $pdfname = $selectEDO['edo_number'].".pdf";
                $pdfFilePath = FCPATH."/assets/pdf/".$pdfname; //tentukan nama file dan lokasi report yang akan kita buat 
                $html2pdf->WriteHTML($html);
                $html2pdf->Output($pdfFilePath, 'F');
                
                $whereId = [
                    'edo_id'=>$edo_id
                ];

                $data = [
                    'send_mail'=>1,
                    'pdf_file'=>$pdfname
                ];


                    // $this->response(['status'=>'success'], REST_Controller::HTTP_OK);
                    $config = Array(  
                    'protocol' => 'smtp',  
                    // 'smtp_host' => 'ssl://smtp.gmail.com',  
                    'smtp_host' => 'ssl://mail.clevara.id', 
                    'smtp_user' => 'halo@clevara.id',
                    'smtp_pass' => 'clevara123abc',
                    'smtp_port' => 465,  
                    'smtp_timeout' => 3000,
                    'mailtype' => 'html',   
                    'charset' => 'utf-8',
                    'newline' => '\r\n'
                    );  
                    $this->load->library('email');  
                    $this->email->initialize($config);
                    $this->email->set_newline("\r\n");  
                    $this->email->from('halo@clevara.id');   
                    $this->email->to($selectEDO['consignee_email']);   
                    $this->email->subject('tes');   
                    $this->email->message('
                    <!doctype html>
                    <html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
                        <head>
                            <!-- NAME: SELL PRODUCTS -->
                            <!--[if gte mso 15]>
                            <xml>
                                <o:OfficeDocumentSettings>
                                <o:AllowPNG/>
                                <o:PixelsPerInch>96</o:PixelsPerInch>
                                </o:OfficeDocumentSettings>
                            </xml>
                            <![endif]-->
                            <meta charset="UTF-8">
                            <meta http-equiv="X-UA-Compatible" content="IE=edge">
                            <meta name="viewport" content="width=device-width, initial-scale=1">
                            <title>*|MC:SUBJECT|*</title>
                            
                        <style type="text/css">
                            p{
                                margin:10px 0;
                                padding:0;
                            }
                            table{
                                border-collapse:collapse;
                            }
                            h1,h2,h3,h4,h5,h6{
                                display:block;
                                margin:0;
                                padding:0;
                            }
                            img,a img{
                                border:0;
                                height:auto;
                                outline:none;
                                text-decoration:none;
                            }
                            body,#bodyTable,#bodyCell{
                                height:100%;
                                margin:0;
                                padding:0;
                                width:100%;
                            }
                            .mcnPreviewText{
                                display:none !important;
                            }
                            #outlook a{
                                padding:0;
                            }
                            img{
                                -ms-interpolation-mode:bicubic;
                            }
                            table{
                                mso-table-lspace:0pt;
                                mso-table-rspace:0pt;
                            }
                            .ReadMsgBody{
                                width:100%;
                            }
                            .ExternalClass{
                                width:100%;
                            }
                            p,a,li,td,blockquote{
                                mso-line-height-rule:exactly;
                            }
                            a[href^=tel],a[href^=sms]{
                                color:inherit;
                                cursor:default;
                                text-decoration:none;
                            }
                            p,a,li,td,body,table,blockquote{
                                -ms-text-size-adjust:100%;
                                -webkit-text-size-adjust:100%;
                            }
                            .ExternalClass,.ExternalClass p,.ExternalClass td,.ExternalClass div,.ExternalClass span,.ExternalClass font{
                                line-height:100%;
                            }
                            a[x-apple-data-detectors]{
                                color:inherit !important;
                                text-decoration:none !important;
                                font-size:inherit !important;
                                font-family:inherit !important;
                                font-weight:inherit !important;
                                line-height:inherit !important;
                            }
                            .templateContainer{
                                max-width:600px !important;
                            }
                            a.mcnButton{
                                display:block;
                            }
                            .mcnImage,.mcnRetinaImage{
                                vertical-align:bottom;
                            }
                            .mcnTextContent{
                                word-break:break-word;
                            }
                            .mcnTextContent img{
                                height:auto !important;
                            }
                            .mcnDividerBlock{
                                table-layout:fixed !important;
                            }
                        /*
                        @tab Page
                        @section Heading 1
                        @style heading 1
                        */
                            h1{
                                /*@editable*/color:#222222;
                                /*@editable*/font-family:Helvetica;
                                /*@editable*/font-size:40px;
                                /*@editable*/font-style:normal;
                                /*@editable*/font-weight:bold;
                                /*@editable*/line-height:150%;
                                /*@editable*/letter-spacing:normal;
                                /*@editable*/text-align:center;
                            }
                        /*
                        @tab Page
                        @section Heading 2
                        @style heading 2
                        */
                            h2{
                                /*@editable*/color:#222222;
                                /*@editable*/font-family:Helvetica;
                                /*@editable*/font-size:34px;
                                /*@editable*/font-style:normal;
                                /*@editable*/font-weight:bold;
                                /*@editable*/line-height:150%;
                                /*@editable*/letter-spacing:normal;
                                /*@editable*/text-align:left;
                            }
                        /*
                        @tab Page
                        @section Heading 3
                        @style heading 3
                        */
                            h3{
                                /*@editable*/color:#444444;
                                /*@editable*/font-family:Helvetica;
                                /*@editable*/font-size:22px;
                                /*@editable*/font-style:normal;
                                /*@editable*/font-weight:bold;
                                /*@editable*/line-height:150%;
                                /*@editable*/letter-spacing:normal;
                                /*@editable*/text-align:left;
                            }
                        /*
                        @tab Page
                        @section Heading 4
                        @style heading 4
                        */
                            h4{
                                /*@editable*/color:#949494;
                                /*@editable*/font-family:Georgia;
                                /*@editable*/font-size:20px;
                                /*@editable*/font-style:italic;
                                /*@editable*/font-weight:normal;
                                /*@editable*/line-height:125%;
                                /*@editable*/letter-spacing:normal;
                                /*@editable*/text-align:left;
                            }
                        /*
                        @tab Header
                        @section Header Container Style
                        */
                            #templateHeader{
                                /*@editable*/background-color:#F7F7F7;
                                /*@editable*/background-image:none;
                                /*@editable*/background-repeat:no-repeat;
                                /*@editable*/background-position:center;
                                /*@editable*/background-size:cover;
                                /*@editable*/border-top:0;
                                /*@editable*/border-bottom:0;
                                /*@editable*/padding-top:45px;
                                /*@editable*/padding-bottom:45px;
                            }
                        /*
                        @tab Header
                        @section Header Interior Style
                        */
                            .headerContainer{
                                /*@editable*/background-color:transparent;
                                /*@editable*/background-image:none;
                                /*@editable*/background-repeat:no-repeat;
                                /*@editable*/background-position:center;
                                /*@editable*/background-size:cover;
                                /*@editable*/border-top:0;
                                /*@editable*/border-bottom:0;
                                /*@editable*/padding-top:0;
                                /*@editable*/padding-bottom:0;
                            }
                        /*
                        @tab Header
                        @section Header Text
                        */
                            .headerContainer .mcnTextContent,.headerContainer .mcnTextContent p{
                                /*@editable*/color:#757575;
                                /*@editable*/font-family:Helvetica;
                                /*@editable*/font-size:16px;
                                /*@editable*/line-height:150%;
                                /*@editable*/text-align:left;
                            }
                        /*
                        @tab Header
                        @section Header Link
                        */
                            .headerContainer .mcnTextContent a,.headerContainer .mcnTextContent p a{
                                /*@editable*/color:#007C89;
                                /*@editable*/font-weight:normal;
                                /*@editable*/text-decoration:underline;
                            }
                        /*
                        @tab Body
                        @section Body Container Style
                        */
                            #templateBody{
                                /*@editable*/background-color:#FFFFFF;
                                /*@editable*/background-image:none;
                                /*@editable*/background-repeat:no-repeat;
                                /*@editable*/background-position:center;
                                /*@editable*/background-size:cover;
                                /*@editable*/border-top:0;
                                /*@editable*/border-bottom:0;
                                /*@editable*/padding-top:36px;
                                /*@editable*/padding-bottom:45px;
                            }
                        /*
                        @tab Body
                        @section Body Interior Style
                        */
                            .bodyContainer{
                                /*@editable*/background-color:transparent;
                                /*@editable*/background-image:none;
                                /*@editable*/background-repeat:no-repeat;
                                /*@editable*/background-position:center;
                                /*@editable*/background-size:cover;
                                /*@editable*/border-top:0;
                                /*@editable*/border-bottom:0;
                                /*@editable*/padding-top:0;
                                /*@editable*/padding-bottom:0;
                            }
                        /*
                        @tab Body
                        @section Body Text
                        */
                            .bodyContainer .mcnTextContent,.bodyContainer .mcnTextContent p{
                                /*@editable*/color:#757575;
                                /*@editable*/font-family:Helvetica;
                                /*@editable*/font-size:16px;
                                /*@editable*/line-height:150%;
                                /*@editable*/text-align:left;
                            }
                        /*
                        @tab Body
                        @section Body Link
                        */
                            .bodyContainer .mcnTextContent a,.bodyContainer .mcnTextContent p a{
                                /*@editable*/color:#007C89;
                                /*@editable*/font-weight:normal;
                                /*@editable*/text-decoration:underline;
                            }
                        /*
                        @tab Footer
                        @section Footer Style
                        */
                            #templateFooter{
                                /*@editable*/background-color:#ffffff;
                                /*@editable*/background-image:none;
                                /*@editable*/background-repeat:no-repeat;
                                /*@editable*/background-position:center;
                                /*@editable*/background-size:cover;
                                /*@editable*/border-top:0;
                                /*@editable*/border-bottom:0;
                                /*@editable*/padding-top:45px;
                                /*@editable*/padding-bottom:63px;
                            }
                        /*
                        @tab Footer
                        @section Footer Interior Style
                        */
                            .footerContainer{
                                /*@editable*/background-color:transparent;
                                /*@editable*/background-image:none;
                                /*@editable*/background-repeat:no-repeat;
                                /*@editable*/background-position:center;
                                /*@editable*/background-size:cover;
                                /*@editable*/border-top:0;
                                /*@editable*/border-bottom:0;
                                /*@editable*/padding-top:0;
                                /*@editable*/padding-bottom:0;
                            }
                        /*
                        @tab Footer
                        @section Footer Text
                        */
                            .footerContainer .mcnTextContent,.footerContainer .mcnTextContent p{
                                /*@editable*/color:#FFFFFF;
                                /*@editable*/font-family:Helvetica;
                                /*@editable*/font-size:12px;
                                /*@editable*/line-height:150%;
                                /*@editable*/text-align:center;
                            }
                        /*
                        @tab Footer
                        @section Footer Link
                        */
                            .footerContainer .mcnTextContent a,.footerContainer .mcnTextContent p a{
                                /*@editable*/color:#FFFFFF;
                                /*@editable*/font-weight:normal;
                                /*@editable*/text-decoration:underline;
                            }
                        @media only screen and (min-width:768px){
                            .templateContainer{
                                width:600px !important;
                            }
                    
                    }	@media only screen and (max-width: 480px){
                            body,table,td,p,a,li,blockquote{
                                -webkit-text-size-adjust:none !important;
                            }
                    
                    }	@media only screen and (max-width: 480px){
                            body{
                                width:100% !important;
                                min-width:100% !important;
                            }
                    
                    }	@media only screen and (max-width: 480px){
                            .mcnRetinaImage{
                                max-width:100% !important;
                            }
                    
                    }	@media only screen and (max-width: 480px){
                            .mcnImage{
                                width:100% !important;
                            }
                    
                    }	@media only screen and (max-width: 480px){
                            .mcnCartContainer,.mcnCaptionTopContent,.mcnRecContentContainer,.mcnCaptionBottomContent,.mcnTextContentContainer,.mcnBoxedTextContentContainer,.mcnImageGroupContentContainer,.mcnCaptionLeftTextContentContainer,.mcnCaptionRightTextContentContainer,.mcnCaptionLeftImageContentContainer,.mcnCaptionRightImageContentContainer,.mcnImageCardLeftTextContentContainer,.mcnImageCardRightTextContentContainer,.mcnImageCardLeftImageContentContainer,.mcnImageCardRightImageContentContainer{
                                max-width:100% !important;
                                width:100% !important;
                            }
                    
                    }	@media only screen and (max-width: 480px){
                            .mcnBoxedTextContentContainer{
                                min-width:100% !important;
                            }
                    
                    }	@media only screen and (max-width: 480px){
                            .mcnImageGroupContent{
                                padding:9px !important;
                            }
                    
                    }	@media only screen and (max-width: 480px){
                            .mcnCaptionLeftContentOuter .mcnTextContent,.mcnCaptionRightContentOuter .mcnTextContent{
                                padding-top:9px !important;
                            }
                    
                    }	@media only screen and (max-width: 480px){
                            .mcnImageCardTopImageContent,.mcnCaptionBottomContent:last-child .mcnCaptionBottomImageContent,.mcnCaptionBlockInner .mcnCaptionTopContent:last-child .mcnTextContent{
                                padding-top:18px !important;
                            }
                    
                    }	@media only screen and (max-width: 480px){
                            .mcnImageCardBottomImageContent{
                                padding-bottom:9px !important;
                            }
                    
                    }	@media only screen and (max-width: 480px){
                            .mcnImageGroupBlockInner{
                                padding-top:0 !important;
                                padding-bottom:0 !important;
                            }
                    
                    }	@media only screen and (max-width: 480px){
                            .mcnImageGroupBlockOuter{
                                padding-top:9px !important;
                                padding-bottom:9px !important;
                            }
                    
                    }	@media only screen and (max-width: 480px){
                            .mcnTextContent,.mcnBoxedTextContentColumn{
                                padding-right:18px !important;
                                padding-left:18px !important;
                            }
                    
                    }	@media only screen and (max-width: 480px){
                            .mcnImageCardLeftImageContent,.mcnImageCardRightImageContent{
                                padding-right:18px !important;
                                padding-bottom:0 !important;
                                padding-left:18px !important;
                            }
                    
                    }	@media only screen and (max-width: 480px){
                            .mcpreview-image-uploader{
                                display:none !important;
                                width:100% !important;
                            }
                    
                    }	@media only screen and (max-width: 480px){
                        /*
                        @tab Mobile Styles
                        @section Heading 1
                        @tip Make the first-level headings larger in size for better readability on small screens.
                        */
                            h1{
                                /*@editable*/font-size:30px !important;
                                /*@editable*/line-height:125% !important;
                            }
                    
                    }	@media only screen and (max-width: 480px){
                        /*
                        @tab Mobile Styles
                        @section Heading 2
                        @tip Make the second-level headings larger in size for better readability on small screens.
                        */
                            h2{
                                /*@editable*/font-size:26px !important;
                                /*@editable*/line-height:125% !important;
                            }
                    
                    }	@media only screen and (max-width: 480px){
                        /*
                        @tab Mobile Styles
                        @section Heading 3
                        @tip Make the third-level headings larger in size for better readability on small screens.
                        */
                            h3{
                                /*@editable*/font-size:20px !important;
                                /*@editable*/line-height:150% !important;
                            }
                    
                    }	@media only screen and (max-width: 480px){
                        /*
                        @tab Mobile Styles
                        @section Heading 4
                        @tip Make the fourth-level headings larger in size for better readability on small screens.
                        */
                            h4{
                                /*@editable*/font-size:18px !important;
                                /*@editable*/line-height:150% !important;
                            }
                    
                    }	@media only screen and (max-width: 480px){
                        /*
                        @tab Mobile Styles
                        @section Boxed Text
                        @tip Make the boxed text larger in size for better readability on small screens. We recommend a font size of at least 16px.
                        */
                            .mcnBoxedTextContentContainer .mcnTextContent,.mcnBoxedTextContentContainer .mcnTextContent p{
                                /*@editable*/font-size:14px !important;
                                /*@editable*/line-height:150% !important;
                            }
                    
                    }	@media only screen and (max-width: 480px){
                        /*
                        @tab Mobile Styles
                        @section Header Text
                        @tip Make the header text larger in size for better readability on small screens.
                        */
                            .headerContainer .mcnTextContent,.headerContainer .mcnTextContent p{
                                /*@editable*/font-size:16px !important;
                                /*@editable*/line-height:150% !important;
                            }
                    
                    }	@media only screen and (max-width: 480px){
                        /*
                        @tab Mobile Styles
                        @section Body Text
                        @tip Make the body text larger in size for better readability on small screens. We recommend a font size of at least 16px.
                        */
                            .bodyContainer .mcnTextContent,.bodyContainer .mcnTextContent p{
                                /*@editable*/font-size:16px !important;
                                /*@editable*/line-height:150% !important;
                            }
                    
                    }	@media only screen and (max-width: 480px){
                        /*
                        @tab Mobile Styles
                        @section Footer Text
                        @tip Make the footer content text larger in size for better readability on small screens.
                        */
                            .footerContainer .mcnTextContent,.footerContainer .mcnTextContent p{
                                /*@editable*/font-size:14px !important;
                                /*@editable*/line-height:150% !important;
                            }
                    
                    }</style></head>
                        <body>
                            <!--*|IF:MC_PREVIEW_TEXT|*-->
                            <!--[if !gte mso 9]><!----><span class="mcnPreviewText" style="display:none; font-size:0px; line-height:0px; max-height:0px; max-width:0px; opacity:0; overflow:hidden; visibility:hidden; mso-hide:all;">*|MC_PREVIEW_TEXT|*</span><!--<![endif]-->
                            <!--*|END:IF|*-->
                            <center>
                                <table align="center" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="bodyTable">
                                    <tr>
                                        <td align="center" valign="top" id="bodyCell">
                                            <!-- BEGIN TEMPLATE // -->
                                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                <tr>
                                                    <td align="center" valign="top" id="templateHeader" data-template-container>
                                                        <!--[if (gte mso 9)|(IE)]>
                                                        <table align="center" border="0" cellspacing="0" cellpadding="0" width="600" style="width:600px;">
                                                        <tr>
                                                        <td align="center" valign="top" width="600" style="width:600px;">
                                                        <![endif]-->
                                                        <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" class="templateContainer">
                                                            <tr>
                                                                <td valign="top" class="headerContainer"><table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnImageBlock" style="min-width:100%;">
                        <tbody class="mcnImageBlockOuter">
                                <tr>
                                    <td valign="top" style="padding:9px" class="mcnImageBlockInner">
                                        <table align="left" width="100%" border="0" cellpadding="0" cellspacing="0" class="mcnImageContentContainer" style="min-width:100%;">
                                            <tbody><tr>
                                                <td class="mcnImageContent" valign="top" style="padding-right: 9px; padding-left: 9px; padding-top: 0; padding-bottom: 0; text-align:center;">
                                                    
                                                        
                                                            <img align="center" alt="" src="https://mcusercontent.com/a205eb95be859eea67c49c326/images/14665a30-6689-4979-b208-03cf74f04072.png" width="208.68" style="max-width:1550px; padding-bottom: 0; display: inline !important; vertical-align: bottom;" class="mcnImage">
                                                        
                                                    
                                                </td>
                                            </tr>
                                        </tbody></table>
                                    </td>
                                </tr>
                        </tbody>
                    </table><table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnTextBlock" style="min-width:100%;">
                        <tbody class="mcnTextBlockOuter">
                            <tr>
                                <td valign="top" class="mcnTextBlockInner" style="padding-top:9px;">
                                      <!--[if mso]>
                                    <table align="left" border="0" cellspacing="0" cellpadding="0" width="100%" style="width:100%;">
                                    <tr>
                                    <![endif]-->
                                    
                                    <!--[if mso]>
                                    <td valign="top" width="600" style="width:600px;">
                                    <![endif]-->
                                    <table align="left" border="0" cellpadding="0" cellspacing="0" style="max-width:100%; min-width:100%;" width="100%" class="mcnTextContentContainer">
                                        <tbody><tr>
                                            
                                            <td valign="top" class="mcnTextContent" style="padding-top:0; padding-right:18px; padding-bottom:9px; padding-left:18px;">
                                            
                                                <h1>Electronic Delivery Order</h1>
                    
                                            </td>
                                        </tr>
                                    </tbody></table>
                                    <!--[if mso]>
                                    </td>
                                    <![endif]-->
                                    
                                    <!--[if mso]>
                                    </tr>
                                    </table>
                                    <![endif]-->
                                </td>
                            </tr>
                        </tbody>
                    </table></td>
                                                            </tr>
                                                        </table>
                                                        <!--[if (gte mso 9)|(IE)]>
                                                        </td>
                                                        </tr>
                                                        </table>
                                                        <![endif]-->
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td align="center" valign="top" id="templateBody" data-template-container>
                                                        <!--[if (gte mso 9)|(IE)]>
                                                        <table align="center" border="0" cellspacing="0" cellpadding="0" width="600" style="width:600px;">
                                                        <tr>
                                                        <td align="center" valign="top" width="600" style="width:600px;">
                                                        <![endif]-->
                                                        <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" class="templateContainer">
                                                            <tr>
                                                                <td valign="top" class="bodyContainer"><table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnTextBlock" style="min-width:100%;">
                        <tbody class="mcnTextBlockOuter">
                            <tr>
                                <td valign="top" class="mcnTextBlockInner" style="padding-top:9px;">
                                      <!--[if mso]>
                                    <table align="left" border="0" cellspacing="0" cellpadding="0" width="100%" style="width:100%;">
                                    <tr>
                                    <![endif]-->
                                    
                                    <!--[if mso]>
                                    <td valign="top" width="600" style="width:600px;">
                                    <![endif]-->
                                    <table align="left" border="0" cellpadding="0" cellspacing="0" style="max-width:100%; min-width:100%;" width="100%" class="mcnTextContentContainer">
                                        <tbody><tr>
                                            
                                            <td valign="top" class="mcnTextContent" style="padding-top:0; padding-right:18px; padding-bottom:9px; padding-left:18px;">
                                            
                                                <h3>Subject<br>
                    &nbsp;</h3>
                    Message message&nbsp;Message message&nbsp;Message message&nbsp;Message message&nbsp;Message message&nbsp;Message message&nbsp;Message message&nbsp;Message message&nbsp;Message message&nbsp;Message message&nbsp;Message message&nbsp;Message message&nbsp;Message message&nbsp;Message message&nbsp;Message message&nbsp;Message message&nbsp;
                                            </td>
                                        </tr>
                                    </tbody></table>
                                    <!--[if mso]>
                                    </td>
                                    <![endif]-->
                                    
                                    <!--[if mso]>
                                    </tr>
                                    </table>
                                    <![endif]-->
                                </td>
                            </tr>
                        </tbody>
                    </table><table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnButtonBlock" style="min-width:100%;">
                        <tbody class="mcnButtonBlockOuter">
                            <tr>
                                <td style="padding-top:0; padding-right:18px; padding-bottom:18px; padding-left:18px;" valign="top" align="center" class="mcnButtonBlockInner">
                                    <table border="0" cellpadding="0" cellspacing="0" class="mcnButtonContentContainer" style="border-collapse: separate !important;border-radius: 3px;background-color: #009FC7;">
                                        <tbody>
                                            <tr>
                                                <td align="center" valign="middle" class="mcnButtonContent" style="font-family: Helvetica; font-size: 18px; padding: 18px;">
                                                    <a class="mcnButton " title="Download E-DO" href="'.$pdfFilePath.' target="_blank" style="font-weight: bold;letter-spacing: -0.5px;line-height: 100%;text-align: center;text-decoration: none;color: #FFFFFF;">Download E-DO</a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table><table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnDividerBlock" style="min-width:100%;">
                        <tbody class="mcnDividerBlockOuter">
                            <tr>
                                <td class="mcnDividerBlockInner" style="min-width:100%; padding:18px;">
                                    <table class="mcnDividerContent" border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;">
                                        <tbody><tr>
                                            <td>
                                                <span></span>
                                            </td>
                                        </tr>
                                    </tbody></table>
                    <!--            
                                    <td class="mcnDividerBlockInner" style="padding: 18px;">
                                    <hr class="mcnDividerContent" style="border-bottom-color:none; border-left-color:none; border-right-color:none; border-bottom-width:0; border-left-width:0; border-right-width:0; margin-top:0; margin-right:0; margin-bottom:0; margin-left:0;" />
                    -->
                                </td>
                            </tr>
                        </tbody>
                    </table></td>
                                                            </tr>
                                                        </table>
                                                        <!--[if (gte mso 9)|(IE)]>
                                                        </td>
                                                        </tr>
                                                        </table>
                                                        <![endif]-->
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td align="center" valign="top" id="templateFooter" data-template-container>
                                                        <!--[if (gte mso 9)|(IE)]>
                                                        <table align="center" border="0" cellspacing="0" cellpadding="0" width="600" style="width:600px;">
                                                        <tr>
                                                        <td align="center" valign="top" width="600" style="width:600px;">
                                                        <![endif]-->
                                                        <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" class="templateContainer">
                                                            <tr>
                                                                <td valign="top" class="footerContainer"></td>
                                                            </tr>
                                                        </table>
                                                        <!--[if (gte mso 9)|(IE)]>
                                                        </td>
                                                        </tr>
                                                        </table>
                                                        <![endif]-->
                                                    </td>
                                                </tr>
                                            </table>
                                            <!-- // END TEMPLATE -->
                                        </td>
                                    </tr>
                                </table>
                            </center>
                        </body>
                    </html>
                    ');  
                    $this->email->attach($pdfFilePath);  

                    if (!$this->email->send()) {  
                        $this->response(['status'=>'failed'], REST_Controller::HTTP_NOT_FOUND);
                    }else{  
                        
                        $this->Crud->updateData('e_do',$data,$whereId);
                        $this->response(['status'=>'success'], REST_Controller::HTTP_OK);
                    } 
                

               
            }
            
        // }else{
        //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

        // }
    }

// scan barcode adminspl dan kasir
    public function scan_barcode_get(){
   
        // $role_user = $this->checktoken_get();

        // if($role_user->role === 'adminspl' || $role_user->role === 'kasir' ){
            
            $edo_number = $this->get('e_do_number');
        
            $whereId = [
                'edo_number'=>$edo_number
            ];
            $selectEDO = $this->Crud->readData('*','e_do',$whereId); 


            if($selectEDO->num_rows() > 0)
            {
                $data= $selectEDO->result_array();
                $this->response(['status'=>'success scan e-DO','data'=>$data], REST_Controller::HTTP_OK);
            }else{
                $this->response(['data'=>'Data not found'], REST_Controller::HTTP_NOT_FOUND);
            }     
        // }else{
        //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

        // }
    }


    // consignee

      // endpoint user admin scl

      public function consignee_get($id=NULL){
   
        // $role_user = $this->checktoken_get();

        // if($role_user->role === 'admin'){
        //     $selectUser= $this->Crud->readData('*','consignee');
            // $countUser= $this->db->query("SELECT COUNT(user_id) as jumlah_user from users")->row_array();

            if($id === NULL){
                $selectUser = $this->Crud->readData('*','consignee'); 
                
            }else{
                $whereId = [
                    'id'=>$id
                ];
                $selectUser = $this->Crud->readData('*','consignee',$whereId); 

            }

            if($selectUser->num_rows() > 0)
            {
                $data= $selectUser->result_array();
                $this->response(['status'=>'success','data'=>$data], REST_Controller::HTTP_OK);
            }else{
                $this->response(['data'=>'Data not found'], REST_Controller::HTTP_NOT_FOUND);
            }
        // }else{
        //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

        // }
    }


    // admin scl create consignee
    public function consignee_post(){
   
        // $role_user = $this->checktoken_get();

        // if($role_user->role === 'dokumen'){
            $email = $this->post('consignee_email');
            $whereId = [
                'consignee_email'=>$email
            ];
            $selectConsignee = $this->Crud->readData('*','consignee',$whereId)->row_array(); 
            // var_dump($selectConsignee);die();
            if($selectConsignee){

                $this->response(['status'=>'Failed created Cosignee, already exist!'], REST_Controller::HTTP_BAD_REQUEST); 

            }else{
                $data = [
                    'consignee_email' => $email,
                    'consignee_name' => $this->post('consignee_name'),
                    'consignee_address' => $this->post('consignee_address')
                ];


                $createConsignee =  $this->Crud->createData('consignee',$data);

                // var_dump($createUser);die();
                if($createConsignee){
                    $this->set_response(['status'=>'Success created consignee','data'=>$data], REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
                }else{
                    $this->response(['status'=>'Failed created consignee!'], REST_Controller::HTTP_BAD_REQUEST); 
                }
            }
        // }else{
        //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

        // }

    }
//    admin scl
    public function consignee_put($id){
   
        // var_dump($this->put('edo_number'));die();
        // $role_user = $this->checktoken_get();

        // if($role_user->role === 'dokumen'){
            $whereId = [
                'id'=>$id
            ];
            $selectUser = $this->Crud->readData('*','consignee',$whereId); 

            $data = [
                'consignee_email' => $this->put('consignee_email'),
                'consignee_name' => $this->put('consignee_name'),
                'consignee_address' => $this->put('consignee_address')
            ];

            if($selectUser->num_rows() > 0)
            {

                // var_dump($edo_number);die();
                $updateUser =  $this->Crud->updateData('consignee',$data,$whereId);

                if($updateUser){
                    $this->set_response(['status'=>'Success Updated consignee!'], REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
                }else{
                    $this->response(['status'=>'Failed Updated consignee!'], REST_Controller::HTTP_BAD_REQUEST); 
                }
        
            }else{
                $this->response(['status'=>'Failed consignee id not found!'], REST_Controller::HTTP_BAD_REQUEST); 

            }
        // }else{
        //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

        // }

    }  
    

    //admin scl search user by name and email
   

    //admin scl delete user
    public function consignee_delete($id){
   
        // $role_user = $this->checktoken_get();

        // if($role_user->role === 'dokumen'){

            $whereId = [
                'id'=>$id
            ];
            $selectUser = $this->Crud->readData('*','consignee',$whereId); 

            if($selectUser->num_rows() > 0)
            {
                $deleteUser =  $this->Crud->deleteData('consignee',$whereId);

                if($deleteUser){
                    $this->set_response(['status'=>'Success Delete consignee!'], REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
                }else{
                    $this->response(['status'=>'Failed Delete consignee!'], REST_Controller::HTTP_BAD_REQUEST); 

                }
            }else{
                $this->response(['status'=>'Failed consignee id not found!'], REST_Controller::HTTP_BAD_REQUEST); 

            }
        // }else{
        //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

        // }

    }


    // port of lading
    
    public function port_of_lading_get($id=NULL){
   
        // $role_user = $this->checktoken_get();

        // if($role_user->role === 'admin'){
        //     $selectUser= $this->Crud->readData('*','port_of_lading');
            // $countUser= $this->db->query("SELECT COUNT(user_id) as jumlah_user from users")->row_array();

            if($id === NULL){
                $selectUser = $this->Crud->readData('*','port_of_lading'); 
                
            }else{
                $whereId = [
                    'id'=>$id
                ];
                $selectUser = $this->Crud->readData('*','port_of_lading',$whereId); 

            }

            if($selectUser->num_rows() > 0)
            {
                $data= $selectUser->result_array();
                $this->response(['status'=>'success','data'=>$data], REST_Controller::HTTP_OK);
            }else{
                $this->response(['data'=>'Data not found'], REST_Controller::HTTP_NOT_FOUND);
            }
        // }else{
        //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

        // }
    }


    // admin scl create pol
    public function port_of_lading_post(){
   
        // $role_user = $this->checktoken_get();

        // if($role_user->role === 'dokumen'){
         
                $data = [
                    'name' => $this->post('name')
                ];


                $createPOL =  $this->Crud->createData('port_of_lading',$data);

                // var_dump($createUser);die();
                if($createPOL){
                    $this->set_response(['status'=>'Success created port of loading'], REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
                }else{
                    $this->response(['status'=>'Failed created port of loading!'], REST_Controller::HTTP_BAD_REQUEST); 
                }
            // }
        // }else{
        //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

        // }

    }
//    admin scl
    public function port_of_lading_put($id){
   
        // var_dump($this->put('edo_number'));die();
        // $role_user = $this->checktoken_get();

        // if($role_user->role === 'dokumen'){
            $whereId = [
                'id'=>$id
            ];
            $selectUser = $this->Crud->readData('*','port_of_lading',$whereId); 

            $data = [
                'name' => $this->put('name')
            ];

            if($selectUser->num_rows() > 0)
            {

                // var_dump($edo_number);die();
                $updateUser =  $this->Crud->updateData('port_of_lading',$data,$whereId);

                if($updateUser){
                    $this->set_response(['status'=>'Success Updated port_of_lading!'], REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
                }else{
                    $this->response(['status'=>'Failed Updated port_of_lading!'], REST_Controller::HTTP_BAD_REQUEST); 
                }
        
            }else{
                $this->response(['status'=>'Failed port_of_lading id not found!'], REST_Controller::HTTP_BAD_REQUEST); 

            }
        // }else{
        //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

        // }

    }  
    


    // //admin scl delete pol
    public function port_of_lading_delete($id){
   
        // $role_user = $this->checktoken_get();

        // if($role_user->role === 'dokumen'){

            $whereId = [
                'id'=>$id
            ];
            $selectUser = $this->Crud->readData('*','port_of_lading',$whereId); 

            if($selectUser->num_rows() > 0)
            {
                $deleteUser =  $this->Crud->deleteData('port_of_lading',$whereId);

                if($deleteUser){
                    $this->set_response(['status'=>'Success Delete port_of_lading!'], REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
                }else{
                    $this->response(['status'=>'Failed Delete port_of_lading!'], REST_Controller::HTTP_BAD_REQUEST); 

                }
            }else{
                $this->response(['status'=>'Failed port_of_lading id not found!'], REST_Controller::HTTP_BAD_REQUEST); 

            }
        // }else{
        //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

        // }

    }

     // port of discharge
    
     public function port_of_discharge_get($id=NULL){
   
        // $role_user = $this->checktoken_get();

        // if($role_user->role === 'admin'){
        //     $selectUser= $this->Crud->readData('*','port_of_discharge');
            // $countUser= $this->db->query("SELECT COUNT(user_id) as jumlah_user from users")->row_array();

            if($id === NULL){
                $selectUser = $this->Crud->readData('*','port_of_discharge'); 
                
            }else{
                $whereId = [
                    'id'=>$id
                ];
                $selectUser = $this->Crud->readData('*','port_of_discharge',$whereId); 

            }

            if($selectUser->num_rows() > 0)
            {
                $data= $selectUser->result_array();
                $this->response(['status'=>'success','data'=>$data], REST_Controller::HTTP_OK);
            }else{
                $this->response(['data'=>'Data not found'], REST_Controller::HTTP_NOT_FOUND);
            }
        // }else{
        //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

        // }
    }


    // admin scl create pod
    public function port_of_discharge_post(){
   
        // $role_user = $this->checktoken_get();

        // if($role_user->role === 'dokumen'){
         
                $data = [
                    'name' => $this->post('name')
                ];


                $createPOL =  $this->Crud->createData('port_of_discharge',$data);

                // var_dump($createUser);die();
                if($createPOL){
                    $this->set_response(['status'=>'Success created port_of_discharge'], REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
                }else{
                    $this->response(['status'=>'Failed created port_of_discharge!'], REST_Controller::HTTP_BAD_REQUEST); 
                }
            // }
        // }else{
        //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

        // }

    }
//    admin scl
    public function port_of_discharge_put($id){
   
        // var_dump($this->put('edo_number'));die();
        // $role_user = $this->checktoken_get();

        // if($role_user->role === 'dokumen'){
            $whereId = [
                'id'=>$id
            ];
            $selectUser = $this->Crud->readData('*','port_of_discharge',$whereId); 

            $data = [
                'name' => $this->put('name')
            ];

            if($selectUser->num_rows() > 0)
            {

                // var_dump($edo_number);die();
                $updateUser =  $this->Crud->updateData('port_of_discharge',$data,$whereId);

                if($updateUser){
                    $this->set_response(['status'=>'Success Updated port_of_discharge!'], REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
                }else{
                    $this->response(['status'=>'Failed Updated port_of_discharge!'], REST_Controller::HTTP_BAD_REQUEST); 
                }
        
            }else{
                $this->response(['status'=>'Failed port_of_discharge id not found!'], REST_Controller::HTTP_BAD_REQUEST); 

            }
        // }else{
        //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

        // }

    }  
    


    // //admin scl delete pod
    public function port_of_discharge_delete($id){
   
        // $role_user = $this->checktoken_get();

        // if($role_user->role === 'dokumen'){

            $whereId = [
                'id'=>$id
            ];
            $selectUser = $this->Crud->readData('*','port_of_discharge',$whereId); 

            if($selectUser->num_rows() > 0)
            {
                $deleteUser =  $this->Crud->deleteData('port_of_discharge',$whereId);

                if($deleteUser){
                    $this->set_response(['status'=>'Success Delete port_of_discharge!'], REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
                }else{
                    $this->response(['status'=>'Failed Delete port_of_discharge!'], REST_Controller::HTTP_BAD_REQUEST); 

                }
            }else{
                $this->response(['status'=>'Failed port_of_discharge id not found!'], REST_Controller::HTTP_BAD_REQUEST); 

            }
        // }else{
        //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

        // }

    }

     // final destination
    
    public function final_destination_get($id=NULL){
   
        // $role_user = $this->checktoken_get();

        // if($role_user->role === 'admin'){
        //     $selectUser= $this->Crud->readData('*','final_destination');
            // $countUser= $this->db->query("SELECT COUNT(user_id) as jumlah_user from users")->row_array();

            if($id === NULL){
                $selectUser = $this->Crud->readData('*','final_destination'); 
                
            }else{
                $whereId = [
                    'id'=>$id
                ];
                $selectUser = $this->Crud->readData('*','final_destination',$whereId); 

            }

            if($selectUser->num_rows() > 0)
            {
                $data= $selectUser->result_array();
                $this->response(['status'=>'success','data'=>$data], REST_Controller::HTTP_OK);
            }else{
                $this->response(['data'=>'Data not found'], REST_Controller::HTTP_NOT_FOUND);
            }
        // }else{
        //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

        // }
    }


    // admin scl create pol
    public function final_destination_post(){
   
        // $role_user = $this->checktoken_get();

        // if($role_user->role === 'dokumen'){
         
                $data = [
                    'name' => $this->post('name')
                ];


                $createPOL =  $this->Crud->createData('final_destination',$data);

                // var_dump($createUser);die();
                if($createPOL){
                    $this->set_response(['status'=>'Success created final_destination'], REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
                }else{
                    $this->response(['status'=>'Failed created final_destination!'], REST_Controller::HTTP_BAD_REQUEST); 
                }
            // }
        // }else{
        //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

        // }

    }
//    admin scl
    public function final_destination_put($id){
   
        // var_dump($this->put('edo_number'));die();
        // $role_user = $this->checktoken_get();

        // if($role_user->role === 'dokumen'){
            $whereId = [
                'id'=>$id
            ];
            $selectUser = $this->Crud->readData('*','final_destination',$whereId); 

            $data = [
                'name' => $this->put('name')
            ];

            if($selectUser->num_rows() > 0)
            {

                // var_dump($edo_number);die();
                $updateUser =  $this->Crud->updateData('final_destination',$data,$whereId);

                if($updateUser){
                    $this->set_response(['status'=>'Success Updated final_destination!'], REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
                }else{
                    $this->response(['status'=>'Failed Updated final_destination!'], REST_Controller::HTTP_BAD_REQUEST); 
                }
        
            }else{
                $this->response(['status'=>'Failed final_destination id not found!'], REST_Controller::HTTP_BAD_REQUEST); 

            }
        // }else{
        //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

        // }

    }  
    


    // //admin scl delete pol
    public function final_destination_delete($id){
   
        // $role_user = $this->checktoken_get();

        // if($role_user->role === 'dokumen'){

            $whereId = [
                'id'=>$id
            ];
            $selectUser = $this->Crud->readData('*','final_destination',$whereId); 

            if($selectUser->num_rows() > 0)
            {
                $deleteUser =  $this->Crud->deleteData('final_destination',$whereId);

                if($deleteUser){
                    $this->set_response(['status'=>'Success Delete final_destination!'], REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
                }else{
                    $this->response(['status'=>'Failed Delete final_destination!'], REST_Controller::HTTP_BAD_REQUEST); 

                }
            }else{
                $this->response(['status'=>'Failed final_destination id not found!'], REST_Controller::HTTP_BAD_REQUEST); 

            }
        // }else{
        //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

        // }

    }
    
    // filter by house bl number

    public function house_bl_number_get(){
   
        // $role_user = $this->checktoken_get();

        // if($role_user->role === 'admin'|| $role_user->role === 'dokumen'){


            $selectUser = $this->db->query("SELECT * FROM e_do ORDER BY house_bl_number DESC")->result_array();
        

            // if($selectUser->num_rows() > 0)
            // {
                // $data= $selectUser->row_array();
            $this->response(['status'=>'success','data'=>$selectUser], REST_Controller::HTTP_OK);
            // }else{
            //     $this->response(['data'=>'Data not found'], REST_Controller::HTTP_NOT_FOUND);
            // }     
        // }else{
        //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

        // }

    }


    public function ocean_vessel_get(){
   
        // $role_user = $this->checktoken_get();

        // if($role_user->role === 'admin'|| $role_user->role === 'dokumen'){


            $selectUser = $this->db->query("SELECT * FROM e_do ORDER BY ocean_vessel DESC")->result_array();
        

            // if($selectUser->num_rows() > 0)
            // {
                // $data= $selectUser->row_array();
            $this->response(['status'=>'success','data'=>$selectUser], REST_Controller::HTTP_OK);
            // }else{
            //     $this->response(['data'=>'Data not found'], REST_Controller::HTTP_NOT_FOUND);
            // }     
        // }else{
        //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

        // }

    }

    public function voyage_number_get(){
   
        // $role_user = $this->checktoken_get();

        // if($role_user->role === 'admin'|| $role_user->role === 'dokumen'){


            $selectUser = $this->db->query("SELECT * FROM e_do ORDER BY voyage_number DESC")->result_array();
        

            // if($selectUser->num_rows() > 0)
            // {
                // $data= $selectUser->row_array();
            $this->response(['status'=>'success','data'=>$selectUser], REST_Controller::HTTP_OK);
            // }else{
            //     $this->response(['data'=>'Data not found'], REST_Controller::HTTP_NOT_FOUND);
            // }     
        // }else{
        //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

        // }

    }
    public function consignee_name_get(){
   
        // $role_user = $this->checktoken_get();

        // if($role_user->role === 'admin'|| $role_user->role === 'dokumen'){


            $selectUser = $this->db->query("SELECT * FROM e_do ORDER BY consignee_name DESC")->result_array();
        

            // if($selectUser->num_rows() > 0)
            // {
                // $data= $selectUser->row_array();
            $this->response(['status'=>'success','data'=>$selectUser], REST_Controller::HTTP_OK);
            // }else{
            //     $this->response(['data'=>'Data not found'], REST_Controller::HTTP_NOT_FOUND);
            // }     
        // }else{
        //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

        // }

    }

    // get status paid
    public function paid_get(){
   
        // $role_user = $this->checktoken_get();

        // if($role_user->role === 'admin'){
        //     $selectUser= $this->Crud->readData('*','consignee');
            // $countUser= $this->db->query("SELECT COUNT(user_id) as jumlah_user from users")->row_array();

            $selectPaid = $this->db->query("select * from e_do where status='PAID' order by edo_id DESC");
                
            if($selectPaid->num_rows() > 0)
            {
                $data= $selectPaid->result_array();
                $this->response(['status'=>'success','data'=>$data], REST_Controller::HTTP_OK);
            }else{
                $this->response(['data'=>'Data not found'], REST_Controller::HTTP_NOT_FOUND);
            }
        // }else{
        //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

        // }
    }

    public function unpaid_get(){
   
        // $role_user = $this->checktoken_get();

        // if($role_user->role === 'admin'){
        //     $selectUser= $this->Crud->readData('*','consignee');
            // $countUser= $this->db->query("SELECT COUNT(user_id) as jumlah_user from users")->row_array();

            $selectPaid = $this->db->query("select * from e_do where status='UNPAID' order by edo_id DESC");
                
            if($selectPaid->num_rows() > 0)
            {
                $data= $selectPaid->result_array();
                $this->response(['status'=>'success','data'=>$data], REST_Controller::HTTP_OK);
            }else{
                $this->response(['data'=>'Data not found'], REST_Controller::HTTP_NOT_FOUND);
            }
        // }else{
        //     $this->response(['status'=>false,'data'=>'Failed token'], REST_Controller::HTTP_NOT_FOUND);

        // }
    }
  }
