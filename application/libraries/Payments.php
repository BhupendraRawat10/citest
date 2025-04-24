<?php

class Payments
{
    private $ci;
    public $_get_msg = '';

    public function __construct()
    {
        $this->ci = &get_instance();	        		
    }
	
	public function fetch_products_from_common_orders($order_id=NULL,$productType=NULL){
         
	   $query="select * from common_orders where order_id = '".$order_id."'";			
	   $order = $this->ci->db->query($query)->result_array();	   
       $data['order'] = $order;
		
		if($productType == $order[0]['product_type']){
			if($productType =="immigration_cards"){
				$query="select * from immigration_cards 
				where id = '".$order[0]['product_id']."'";			
			   $data['exams'] = $this->ci->db->query($query)->result_array();				
			}		
			if($productType =="eureka_user_profile"){
				$query="select * from eureka_user_profile 
				where id = '".$order[0]['product_id']."'";			
			   $data['exams'] = $this->ci->db->query($query)->result_array();				
			}			
		}
        return $data;		
	}
	
	public function updateCommonOrdersStatus($product_type, $order_id){
		$subscription_type_list = array('exams', 'courses', 'dating_subscription_info', 'chess-plan','plan', 'business_plans','professional_collaboration_plans', 'mentorship_plans', 'student_plans', 'employer_plans');
		if(in_array($product_type, $subscription_type_list)){
			if($product_type == 'exams'){
				$this->renew_user_subscription($order_id);
			}else{
				$insertData = array
				(
					'is_active' => 1,
					'payment_status' => 1,
					'updated_at' => date('Y-m-d H:i:s', time())
				);			
				$this->ci->db->where('subscription_id', $order_id);
				$this->ci->db->update('subscriptions', $insertData);
			}
			
            if($product_type == 'plan' || $product_type == 'chess-plan'){
				$user_id = $this->ci->session->userdata['UserLoggedin']['user_id'];	
				$this->ci->load->model('chess/Subscription_chess_model');			
				$order = $this->ci->Subscription_chess_model->fetch_orders($order_id,$product_type);
				$this->ci->load->model('chess/Chess_dashboard_model');	
                $game = $this->ci->Chess_dashboard_model->fetch_user_games($user_id);				
				$total_game = $game + $order['plan'][0]['game'];						
				$data['user'] = $this->ci->Chess_dashboard_model->update_user_games($user_id,$total_game);				
				
			}			
		}
		// || $product_type == 'courses'
		if($product_type == 'immigration_cards' || $product_type == 'eureka_user_profile' || $product_type == 'ebooks' || $product_type == 'admissions_services' || $product_type == 'natural_resources'|| $product_type == 'chess_events'){
			$insertData = array
			(
				'payment_status' => 1,
				'updated_at' => date('Y-m-d H:i:s', time())
			);				
			$this->ci->db->where('order_id', $order_id);
			$this->ci->db->update('common_orders', $insertData);				
		}
		
		if($product_type == 'goods' || $product_type == 'market_products'){
			$insertData = array
			(
				'payment_status' => 1,
				'updated_at' => date('Y-m-d H:i:s', time())
			);				
			$this->ci->db->where('order_id', $order_id);
			$this->ci->db->update('my_orders', $insertData);				
		}			
	}

    // conference 
	public function updateCommonOrdersStatusconference($product_type, $order_id){
		$subscription_type_list = array('conferencing_plans');
		if(in_array($product_type, $subscription_type_list)){
			if($product_type == 'conferencing_plans'){
				$insertData = array
				(
					'is_active' => 1,
					'payment_status' => 1,
					'updated_at' => date('Y-m-d H:i:s', time())
				);			
				$this->ci->db->where('product_id', $order_id);
				$this->ci->db->update('conference_sub', $insertData);
			}
			
           		
		}

		
	}


	function renew_user_subscription($order_id){
		$now = time(); // or your date as well
		$your_date = strtotime($order[0]['end_date']);
		$datediff = $your_date - $now ;
		$total_days = round($datediff / (60 * 60 * 24));
		// echo $end_date =   date('Y-m-d H:i:s', strtotime('1 month'));
		$total_days = ($total_days<0)? 30 : (30 + $total_days);
		$end_date = date('Y-m-d H:i:s', strtotime("+".$total_days. "days"));
		$insertData = array
		( 	   
			'is_active' => 1,
		    'payment_status' => 1,
		   	'start_date' => date('Y-m-d H:i:s', time()),
		   	'end_date' =>$end_date,
		   	'updated_at'		  =>date('Y-m-d H:i:s',time())
		);		
		$this->ci->db->where('subscription_id',$order_id);
		return $this->ci->db->update('subscriptions',$insertData);			
}

	public function fetch_pin_payments_with_order($order_id, $product_type){
		
		$subscription_type_list = array('exams', 'courses', 'dating_subscription_info', 'chess-plan','plan', 'business_plans','professional_collaboration_plans', 'mentorship_plans', 'student_plans', 'employer_plans');
		
		if(in_array($product_type, $subscription_type_list)){
			$query="select subs.*, pay.buyer_name, pay.buyer_email, pay.paid_amount_currency, pay.paid_amount, pay.paid_amount, pay.txn_id, pay.payment_status, pay.payment_time 
			from subscriptions as subs 
			join payments as pay on subs.subscription_id = pay.order_id		  		  		  
			where subs.subscription_id = '".$order_id."'";			
			$subscription = $this->ci->db->query($query)->result_array();											
			
			if(isset($subscription) && !empty($subscription)){
				$subscription[0]['product_info'] = $this->fetch_products_by_id($subscription[0]['product_type'],$subscription[0]['product_id']);							
				return $subscription;
			}else{
				return FALSE;
			}
		}
		//|| $product_type == 'courses' 
		if($product_type == 'immigration_cards' || $product_type == 'eureka_user_profile' || $product_type == 'ebooks' || $product_type == 'admissions_services' || $product_type == 'natural_resources'|| $product_type == 'chess_events'){
			
			$query="select subs.*, pay.buyer_name, pay.paid_amount_currency, pay.buyer_email, pay.paid_amount, pay.paid_amount, pay.txn_id, pay.payment_status, pay.payment_time 
			from common_orders as subs 
			join payments as pay on subs.order_id = pay.order_id		  		  		  
			where subs.order_id = '".$order_id."'";			
			$subscription = $this->ci->db->query($query)->result_array();											
			if(isset($subscription) && !empty($subscription)){
				$subscription[0]['product_info'] = $this->fetch_products_by_id($subscription[0]['product_type'],$subscription[0]['product_id']);							
				return $subscription;
			}else{
				return FALSE;
			}
		}
		
		if($product_type == 'goods' || $product_type == 'market_products'){
			
			$query="select subs.*, pay.buyer_name, pay.buyer_email, pay.paid_amount, pay.paid_amount, pay.txn_id, pay.paid_amount_currency, pay.payment_status, pay.payment_time 
			from my_orders as subs 
			join payments as pay on subs.order_id = pay.order_id		  		  		  
			where subs.order_id = '".$order_id."'";			
			$subscription = $this->ci->db->query($query)->result_array();
			if(isset($subscription) && !empty($subscription)){
				$subscription[0]['product_info'] = $this->fetch_products_by_id($product_type, $subscription[0]['order_id']);							
				return $subscription;
			}else{
				return FALSE;
			}			
		}			
	}		
	public function fetch_pin_payments_with_order_conference_sub($order_id, $product_type){
		$subscription_type_list = array('conferencing_plans');
		if(in_array($product_type, $subscription_type_list)){
			$query="select subs.*, pay.buyer_name, pay.buyer_email, pay.paid_amount_currency, pay.paid_amount, pay.paid_amount, pay.txn_id, pay.payment_status, pay.payment_time 
			from conference_sub as subs 
			join payments as pay on subs.product_id = pay.order_id		  		  		  
			where subs.product_id = '".$order_id."'";			
			$subscription = $this->ci->db->query($query)->result_array();											
			// dd($subscription);
			if(isset($subscription) && !empty($subscription)){
				if ($subscription[0]['product_type'] === "conference_plan") {
					$subscription[0]['product_type'] = "conferencing_plans";
				}

				$subscription[0]['product_info'] = $this->fetch_products_by_id($subscription[0]['product_type'], $subscription[0]['product_id']);
				
				return $subscription;
			}else{
				return FALSE;
			}
		}

	
	}	
	
    public function fetchTransaction($txn_id) {
	   $query="select * from payments where txn_id = '".$txn_id."'";			
	   $payment = $this->ci->db->query($query)->result_array();			
	   if(isset($payment) && !empty($payment)){
	        return TRUE;		   
	   }else{
		   return FALSE;		   
	   }
    }

    public function insertTransaction($transactionData) {
       if(isset($transactionData)){		   
			$insert_status = $this->ci->db->insert('payments', $transactionData);
			if($insert_status){
			   return TRUE;
			}else{
			   return FALSE;	
			}
	   }else{
		   return FALSE;
	   }
    }
    public function updatePinStatus($assigned) {
		
		if(isset($this->ci->session->userdata['UserLoggedin']['user_id']) && !empty($this->ci->session->userdata['UserLoggedin']['user_id'])){
			$user_id = $this->ci->session->userdata['UserLoggedin']['user_id'];	
			$insertData = array
			(
				'status' => 1,
				'used_by' => $user_id,
				'updated_at' => date('Y-m-d H:i:s', time())
			);				
			$this->ci->db->where('id', $assigned->pin_id);
			$this->ci->db->update('payment_pins', $insertData);		
			return TRUE;			
		}else{
			return FALSE;
		}
	 }

	 public function fetch_products_by_id($product_type, $product_id){
		if($product_type === 'chess-plan'){
            $product_type = 'plan';
			$id = "plan_id";
		}
		if($product_type === 'plan'){
            $product_type = 'plan';
			$id = "plan_id";
		}		

		if($product_type === 'courses'){
			$id = "course_id";
		}		

		if($product_type === 'ebooks'){
			$id = "ebook_id";
		}	

		if($product_type === 'student_plans'){
			$id = "student_plans_id";
		}	
		
		if($product_type === 'goods'){
			$id = "g_id";
		}				
		if($product_type === 'market_products'){
			$id = "product_id";
		}	
		if($product_type === 'conferencing_plans'){
			$id = "conferencing_plans_id";
		}		
		$subscription_type_list = array(
			'exams', 
			'dating_subscription_info', 
			'immigration_cards',
			'eureka_user_profile', 
			'admissions_services', 
			'chess_events', 
			'business_plans',
			'professional_collaboration_plans', 
			'mentorship_plans', 
			'natural_resources', 
			'employer_plans'
		);
		if(in_array($product_type, $subscription_type_list)){
			$id = "id";
		}

		if($product_type == 'goods' || $product_type === 'market_products'){
			$arr_data = $this->ci->db->select('*')
			->from('order_items')
			->where('order_id',$product_id)
			->where('product_type',$product_type)
			->get()->result_array(); 
			if(isset($arr_data) && !empty($arr_data)){
				foreach($arr_data as $key=>$arr){					
					$product  = $this->product_goods_or_market_product($product_type, $arr);					
					if(isset($product) && !empty($product)){					
						$arr_data[$key]['product_data'] = $this->product_data($product_type, $product);
					}			
					
     			}
               return $arr_data;
			}else{
				return FALSE;
			}
		}				
		$arr = $this->ci->db->select('*')
		->from($product_type)
		->where($id,$product_id)
		->get()->result_array(); 
		if($arr){
			return $products = $this->product_data($product_type, $arr);	
		}else{
           return FALSE;
		}
    }	 
	 
	 public function product_data($product_type, $arr){		
		$data=[];
		if(isset($arr) && !empty($arr)){
			 
            if($product_type == 'immigration_cards'){
				foreach($arr as $key=>$product){
                   $data[$key]['id'] = $product['id'];
                   $data[$key]['title'] =ucwords($product['card_name'].'(Fee:$'.$product['fees'].')');
                   $data[$key]['price'] =$product['fees'];
                   $data[$key]['description'] =$product['short_description'];
				   $data[$key][$product_type] = $product;
				}			
			}
			
            if($product_type == 'eureka_user_profile'){
				foreach($arr as $key=>$product){
                   $data[$key]['id'] = $product['id'];
                   $data[$key]['title'] =ucwords($product['user_name'].'('.$product['user_email'].')');
                   $data[$key]['price'] =$product['price']??'';
                   $data[$key]['description'] =ucwords($product['user_name'].'('.$product['user_email'].')');				   
				   $data[$key][$product_type] = $product;
				}			
			}

            if($product_type == 'courses'){
				foreach($arr as $key=>$product){
                   $data[$key]['id'] = $product['course_id'];
                   $data[$key]['title'] =ucwords($product['exam_name'].'(Price:$'.$product['cost'].')');
				   $data[$key]['price'] =$product['cost'];
                   $data[$key]['description'] =$product['exam_des'];
				   $data[$key][$product_type] = $product;
				}			
			}
			
            if($product_type == 'natural_resources'){
				foreach($arr as $key=>$product){
                   $data[$key]['id'] = $product['id'];
                   $data[$key]['title'] =ucwords($product['service_name'].'(Fees:$'.$product['fees'].')');
				   $data[$key]['price'] =$product['fees'];
                   $data[$key]['description'] =$product['short_description'];
				   $data[$key][$product_type] = $product;
				}			
			}			
			
            if($product_type == 'admissions_services'){
				foreach($arr as $key=>$product){
                   $data[$key]['id'] = $product['id'];
                   $data[$key]['title'] =ucwords($product['service_name'].'(Fees:$'.$product['fees'].')');
				   $data[$key]['price'] =$product['fees'];
                   $data[$key]['description'] =$product['short_description'];				   
				   $data[$key][$product_type] = $product;				   
				}			
			}
			
            if($product_type == 'ebooks'){
				foreach($arr as $key=>$product){
                   $data[$key]['id'] = $product['ebook_id'];
                   $data[$key]['title'] =ucwords($product['title'].'(Price:$'.$product['cost'].')');
				   $data[$key]['price'] =$product['cost'];
                   $data[$key]['description'] =$product['short_description'];				   
				   $data[$key][$product_type] = $product;
				}			
			}	
			
            if($product_type == 'chess_events'){
				foreach($arr as $key=>$product){
                   $data[$key]['id'] = $product['id'];
                   $data[$key]['title'] =ucwords($product['title'].'(Price:$'.$product['cost'].')');
				   $data[$key]['price'] =$product['cost'];
                   $data[$key]['description'] =$product['description'];					   
				   $data[$key][$product_type] = $product;
				}			
			}	
			
            if($product_type == 'exams'){
				foreach($arr as $key=>$product){
                   $data[$key]['id'] = $product['id'];
                   $data[$key]['title'] =ucwords($product['exam_name'].'(Price:$'.$product['subcription'].')');
				   $data[$key]['price'] =$product['subcription'];
                   $data[$key]['description'] =$product['des'];					   
				   $data[$key][$product_type] = $product;
				}			
			}
			
            if($product_type == 'dating_subscription_info'){
				foreach($arr as $key=>$product){
                   $data[$key]['id'] = $product['id'];
                   $data[$key]['title'] = ucwords($product['name'].'(Price:$'.$product['subscription_cost'].')');
				   $data[$key]['price'] =$product['subscription_cost'];
                   $data[$key]['description'] =$product['description'];					   
				   $data[$key][$product_type] = $product;
				}			
			}	

            if($product_type == 'chess-plan' || $product_type == 'plan'){
				foreach($arr as $key=>$product){					
                   $data[$key]['id'] = $product['plan_id'];
                   $data[$key]['title'] = ucwords($product['plan_name'].'(Subcription:$'.$product['subcription'].')');
				   $data[$key]['price'] =$product['subcription'];
                   $data[$key]['description'] =$product['description'];						   
				   $data[$key][$product_type] = $product;
				}			
			}
			
						
            if($product_type == 'business_plans'){
				foreach($arr as $key=>$product){
                   $data[$key]['id'] = $product['id'];
                   $data[$key]['title'] = ucwords($product['title'].'(Price:$'.$product['cost'].')');
				   $data[$key]['price'] =$product['cost'];
                   $data[$key]['description'] =$product['description'];					   
				   $data[$key][$product_type] = $product;
				}			
			}	
						
            if($product_type == 'professional_collaboration_plans'){
				foreach($arr as $key=>$product){
                   $data[$key]['id'] = $product['id'];
                   $data[$key]['title'] = ucwords($product['title'].'(Price:$'.$product['cost'].')');
				   $data[$key]['price'] =$product['cost'];
                   $data[$key]['description'] =$product['description'];					   
				   $data[$key][$product_type] = $product;
				}			
			}			

            if($product_type == 'mentorship_plans'){	
				foreach($arr as $key=>$product){
                   $data[$key]['id'] = $product['id'];
                   $data[$key]['title'] = ucwords($product['title'].'(Price:$'.$product['cost'].')');
				   $data[$key]['price'] =$product['cost'];
                   $data[$key]['description'] =$product['description'];				   
				   $data[$key][$product_type] = $product;
				}			
			}	
			
            if($product_type == 'student_plans'){	
				foreach($arr as $key=>$product){
                   $data[$key]['id'] = $product['student_plans_id'];
                   $data[$key]['title'] =ucwords($product['plans_name'].'(Subcription:$'.$product['subcription'].')');
				   $data[$key]['price'] =$product['subcription'];
                   $data[$key]['description'] =$product['description'];					   
				   $data[$key][$product_type] = $product;
				}			
			}
						
            if($product_type == 'employer_plans'){	
				foreach($arr as $key=>$product){
                   $data[$key]['id'] = $product['id'];
                   $data[$key]['title'] =ucwords($product['title'].'(Price:$'.$product['cost'].')');
				   $data[$key]['price'] =$product['cost'];
                   $data[$key]['description'] =$product['description'];					   
				   $data[$key][$product_type] = $product;
				}			
			}	
						
            if($product_type == 'goods'){	
				foreach($arr as $key=>$product){
					// dd($product);			
                   $data[$key]['id'] = $product['g_id'];
                   $data[$key]['title'] = ucwords($product['g_title'].'(Price:$'.$product['g_price'].')');
				   $data[$key]['price'] = ($product['g_price'] + $product['gst'] + $product['shipping']);
                   $data[$key]['description'] =$product['g_short_description'];					   
				   $data[$key][$product_type] = $product;
				}			
			}

            if($product_type == 'market_products'){	
				foreach($arr as $key=>$product){
					$data[$key]['id'] = $product['product_id'];
					$data[$key]['title'] = ucwords($product['title'].'(Price:$'.$product['price'].')');
					$data[$key]['price'] = ($product['price'] + $product['gst'] + $product['shipping']);
					$data[$key]['description'] =$product['description'];						
					$data[$key][$product_type] = $product;
				}			
			}		

			if($product_type == 'conferencing_plans'){	
				foreach($arr as $key=>$product){
					$data[$key]['id'] = $product['conferencing_plans_id'];
					$data[$key]['title'] = ucwords($product['plans_name'].'(Subcription:$'.$product['subcription'].')');
					$data[$key]['price'] =$product['subcription'];
					$data[$key]['description'] =$product['description'];					   
					$data[$key][$product_type] = $product;
				}			
			}							

		}else{
			$res['status'] = 'error';
			$res['message'] = 'Data not found';
			$res['data'] = $data;	
		}

		if(isset($data) && !empty($data)){
			$res['status'] = 'success';
			$res['message'] = 'Data found';
			$res['data'] = $data;
		}else{
			$res['status'] = 'error';
			$res['message'] = 'Data not found';
			$res['data'] = $data;			
		}

		return $res;
	}
	public function product_goods_or_market_product($product_type, $arr){
        if($product_type == 'goods'){
            $id = 'g_id';
		}
        if($product_type == 'market_products'){
			$id = 'product_id';
		}
		$new_arr = $this->ci->db->select('*')
		->from($product_type)
		->where($id,$arr['product_id'])
		->get()->result_array(); 
		// echo $this->ci->db->last_query();
		if($new_arr){
			return $new_arr;
		}else{
		   return FALSE;
		}			
		
	}
	
	
}
