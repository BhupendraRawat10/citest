<?php

class Udf
{
    private $ci;
    public $_get_msg = '';

    public function __construct()
    {
        $this->ci = &get_instance();
		$this->ci->load->helper('cookie');
        if(isset($this->ci->session->markettype)){
		    $this->market_type  = $this->ci->session->markettype;			
		}		
    }
	
	public function get_country_id() {
		if(get_cookie('country_id') !==NULL){
			$this->country_id = get_cookie('country_id');		
		}else{		
				// $ip = "132.154.250.45";
				$ip = IP_ADDRESS;
				$output = $this->ip_info($ip, $purpose = "location", $deep_detect = TRUE);
				if(isset($output['country_code']) && !empty($output)){
					$location = $this->getcountryByShortName($output['country_code']);
					if(isset($location[0]['country_id']) && $location[0]['country_id'] !=''){
						$expire = 6*30*24*3600;
						set_cookie('country_name',$location[0]['country_name'], time() + ($expire), "/");					
						set_cookie('country_id',$location[0]['country_id'], time() + ($expire), "/");							
						$this->country_id = $location[0]['country_id'];		
						$this->country_name = $location[0]['country_name']; 						
					}else{
					   redirect('/change-country');
					}
					
				}else{
				    redirect('/change-country');
			    }									
		}
        return $this->country_id;		
	}	
	
function ip_info($ip = NULL, $purpose = "location", $deep_detect = TRUE) {
    $output = NULL;
    if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
        $ip = IP_ADDRESS;
        if ($deep_detect) {
            if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
                $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
    }
    if (filter_var($ip, FILTER_VALIDATE_IP)) {
		// $ipdat = @json_decode(file_get_contents("https://www.geoplugin.net/json.gp?ip=" . $ip));
		// $details = @json_decode(file_get_contents("https://ipinfo.io/{$ip}/json"));
        $url = "https://api.ipgeolocation.io/ipgeo?apiKey=" . API_KEY . "&ip=". $ip;
        $details = @json_decode(file_get_contents($url));
		// if(isset($ipdat) && !empty($ipdat)){
			// $output = array(
				// "city"           => @$ipdat->geoplugin_city,
				// "state"          => @$ipdat->geoplugin_regionName,
				// "country"        => @$ipdat->geoplugin_countryName,
				// "country_code"   => @$ipdat->geoplugin_countryCode,
				// "continent"      => @$continents[strtoupper($ipdat->geoplugin_continentCode)],
				// "continent_code" => @$ipdat->geoplugin_continentCode
			// );
           // return $output;			
		// }else 
		if(isset($details) && !empty($details)){			
			$output = array(
				"city"           => '',
				"state"          => '',
				"country"        => @$details->country_name,
				"country_code"   => @$details->country_code2,
				"continent"      => '',
				"continent_code" => ''
			);
            return $output;						
		}else{
			$output = array(
				"city"           => '',
				"state"          => '',
				"country"        => '',
				"country_code"   => '',
				"continent"      => '',
				"continent_code" => ''
			);
            return false;				
		}		
    }

}
	
/**Goods Products**/
    public function getLimitedmarketGoods($subcategoryID,$limit) {
        $res = $this->ci->db->select('g_id,g_mrp,g_price,g_image,g_title')->get_where('goods', array('goods_sub_category' => $subcategoryID, 'status' => 1), $limit);
        $res2 = $res->result_array();
		// echo $this->ci->db->last_query();
		// die();
		return $res2;
    }
	
/**Immigration Payment info by card id and user id**/
    public function getImmigrationPaymentInfo($id,$card_id) {
			if(isset($card_id)){
				$where = "common_orders.user_id = {$id} AND common_orders.payment_status = 1 AND common_orders.product_id ={$card_id} AND common_orders.product_type ='immigration_cards'";
			}else{
				$where = "common_orders.user_id = {$id} AND common_orders.payment_status = 1";
			}	
			$arr = $this->ci->db->select('common_orders.*,payments.*')
			->from('common_orders')
			->join('payments', 'common_orders.order_id = payments.order_id')
			->where($where)
			->get()->result_array(); 
			return $arr;
    }		
	
/**Section Contents**/
    public function getSectionsByPageName($page_name) {
		$query="select * from pages 			
		where pages.page_name = '{$page_name}'";		   		   
        $res = $this->ci->db->query($query)->result_array();
        $page_id = $res[0]['page_id'];
		if(isset($page_id)){
			$query="select * from sections 			
			where sections.page_id = {$page_id}";
            return $this->ci->db->query($query)->result_array();			
		}else{
			return false;
		}		   		   
    }	
	
	
/**Questions**/
    public function gettotalquestions($exam_id) {
		$this->ci->db->where('exam_id', $exam_id);
		$query = $this->ci->db->get('exam_questions');
		return $query->num_rows();
		// echo $this->ci->db->last_query();
		// die();
		// return $query;
    }
    public function getLmarketProducts($params= array(),$subcategoryID=NULL,$page=NULL,$numPerPage=10) {
        if ($page == 0) {
            $skip = 0;
        }else{
            $skip = ($page-1) * $numPerPage;
        }
        // echo $subcategoryID;
		// die();
        $limits = "LIMIT $skip,$numPerPage";		

		if($params['count'] == 'total'){
			$res = $this->ci->db->select('product_id,mrp,price,brand,p_image,title')->get_where('market_products', array('sub_category_id' => $subcategoryID, 'status' => 1));	
			return count($res->result_array());
			
		}else{
			// $res = $this->ci->db->select('g_id,g_mrp,g_price,g_image,g_title')->get_where('goods', array('goods_sub_category' => $subcategoryID, 'status' => 1), $limits);
          $query="select product_id, mrp,price, p_image,brand, title from market_products where status = 1 AND sub_category_id = $subcategoryID ". $limits."";		
          return $this->ci->db->query($query)->result_array();	
		}
        
    }	
    public function getLimitedmarketGoods2($params= array(),$subcategoryID=NULL,$page=NULL,$numPerPage=10) {
        if ($page == 0) {
            $skip = 0;
        }else{
            $skip = ($page-1) * $numPerPage;
        }
        // echo $subcategoryID;
		// die();
        $limits = "LIMIT $skip,$numPerPage";		

		if($params['count'] == 'total'){
			$res = $this->ci->db->select('g_id,g_mrp,g_price,g_image,g_title')->get_where('goods', array('goods_sub_category' => $subcategoryID, 'status' => 1));	
			return count($res->result_array());
			
		}else{
			// $res = $this->ci->db->select('g_id,g_mrp,g_price,g_image,g_title')->get_where('goods', array('goods_sub_category' => $subcategoryID, 'status' => 1), $limits);
          $query="select g_id, g_mrp,g_price, g_image, g_title from goods where status = 1 AND goods_sub_category = $subcategoryID ". $limits."";		
          return $this->ci->db->query($query)->result_array();	
		}
        
    }	
/**Ends Goods Products**/
    public function getLimitedmarketProducts($subcategoryID,$limit) {
        $res = $this->ci->db->select('product_id, mrp,price, p_image, title')->get_where('market_products', array('market_type' => $this->market_type ,'market_category_id' => $subcategoryID, 'status' => 1), $limit);
        return $res->result_array();
    }
	
    public function fetch_qbank_subscription_by_id($id=NULL)
    {
        if(isset($this->ci->session->userdata['UserLoggedin'])){
		    $user_id = $this->ci->session->userdata['UserLoggedin']['user_id'];	
			$product_type = "exams";
			$res = $this->ci->db->select('*')->get_where('subscriptions', array('product_id' => $id, 'user_id' => $user_id,'product_type' => $product_type,'payment_status' => 1));
        return $res->result_array();			
		
		}else{
			return false;
		}
    }

	public function fetch_courses_subscription_by_id($id=NULL)
    {
        if(isset($this->ci->session->userdata['UserLoggedin'])){
		    $user_id = $this->ci->session->userdata['UserLoggedin']['user_id'];	
			$product_type = "courses";
			$res = $this->ci->db->select('*')->get_where('subscriptions', array('product_id' => $id, 'user_id' => $user_id,'product_type' => $product_type,'payment_status' => 1));
        return $res->result_array();			
		
		}else{
			return false;
		}
    }
	
    public function fetch_common_orders_payment_status_by_id($id=NULL,$product_type=NULL)
    {
        if(isset($this->ci->session->userdata['UserLoggedin'])){
		    $user_id = $this->ci->session->userdata['UserLoggedin']['user_id'];	
			$res = $this->ci->db->select('*')->get_where('common_orders', array('product_id' => $id, 'user_id' => $user_id,'product_type' => $product_type,'payment_status' => 1));
            return $res->result_array();					
		}else{
			return false;
		}
    }	

    public function fetch_subscription_by_id_and_type($product_id=NULL,$product_type=NULL)
    {
        if(isset($this->ci->session->userdata['UserLoggedin'])){
			if($product_type == "exams"){
				$res = $this->ci->db->select('*')->get_where('exams', array('id' => $product_id));
                return $res->result_array();
			}
			if($product_type == "dating_subscription_info"){
				$res = $this->ci->db->select('*')->get_where('dating_subscription_info', array('id' => $product_id));
                return $res->result_array();
			}
			if($product_type == "business_plans"){
				$res = $this->ci->db->select('*')->get_where('business_plans', array('id' => $product_id));
                return $res->result_array();
			}
			if($product_type == "professional_collaboration_plans"){
				$res = $this->ci->db->select('*')->get_where('professional_collaboration_plans', array('id' => $product_id));
                return $res->result_array();
			}			
			if($product_type == "student_plans"){
				$res = $this->ci->db->select('*')->get_where('student_plans', array('student_plans_id' => $product_id));
                return $res->result_array();
			}						
			if($product_type == "courses"){
				$res = $this->ci->db->select('*')->get_where('courses', array('course_id' => $product_id));
                return $res->result_array();
			}
		}else{
			return false;
		}
    }
	
/*Start of footer data*/		
	function getLatestJobs($params= array(), $page=null, $numPerPage=5){
		      $this->country_id = $this->get_country_id();
		      $where = "jobs.status = 1 AND jobs.country_id={$this->country_id} ";
		      $limitss = "LIMIT $numPerPage";
			  $query="select jobs.*,job_category.name as cat_name from jobs 
			  join job_category on jobs.job_category_id = job_category.id
			  where ".$where." ORDER BY id DESC ". $limitss ."";		
			  $arr = $this->ci->db->query($query)->result_array();	
			  return $arr;	
	}	
	function getLatestMarketCategory($params= array(), $page=null, $numPerPage=5){
		$this->country_id = $this->get_country_id();
	   $this->ci->db->order_by('m_cat_id', 'DESC');
	   $res =$this->ci->db->select('m_cat_id, m_cat_name')->get_where('market_primary_category', array('m_cat_status' => 1,'country_id'=>$this->country_id),$numPerPage);
	   return $res->result_array();
	}
	function getLatestGoodsCategory($params= array(), $page=null, $numPerPage=5){
		$this->country_id = $this->get_country_id();
	   $this->ci->db->order_by('id', 'DESC');
	   $res =$this->ci->db->select('id, name')->get_where('goods_sub_category', array('status' => 1,'country_id'=>$this->country_id),$numPerPage);
	   return $res->result_array();
	}	

/*End of footer data*/	

	public function getWebsiteSettings(){
            $query = $this->ci->db->get('settings');
           return $query->result_array();			
	}
		
    public function getLimitedmarketProducts2($params= array(),$subcategoryID=NULL,$page=NULL,$numPerPage=10) {
        if ($page == 0) {
            $skip = 0;
        }else{
            $skip = ($page-1) * $numPerPage;
        }
        
        $limits = "LIMIT $skip,$numPerPage";		

		if($params['count'] == 'total'){
			$res = $this->ci->db->select('product_id, mrp,price, p_image, title')->get_where('market_products', array('market_type' => $this->market_type ,'market_category_id' => $subcategoryID, 'status' => 1));	
			return count($res->result_array());
			
		}else{
			$res = $this->ci->db->select('product_id, mrp,price, p_image, title')->get_where('market_products', array('market_type' => $this->market_type ,'market_category_id' => $subcategoryID, 'status' => 1), $limits);
          $query="select product_id, mrp,price, p_image, title from market_products where status = 1 AND market_type = $this->market_type  AND market_category_id = $subcategoryID ". $limits."";		
          return $this->ci->db->query($query)->result_array();	
		}
        
    }
    public function getLimitedprofessionProducts($params= array(),$subcategoryID=NULL,$page=NULL,$numPerPage=10) {
        if ($page == 0) {
            $skip = 0;
        }else{
            $skip = ($page-1) * $numPerPage;
        }
        // echo $subcategoryID;
		// die();
        $limits = "LIMIT $skip,$numPerPage";		

		if($params['count'] == 'total'){
			$res = $this->ci->db->select('product_id, mrp,price, p_image, title')->get_where('market_products', array('market_category_id' => $subcategoryID, 'status' => 1));	
			return count($res->result_array());
			
		}else{
			$res = $this->ci->db->select('product_id, mrp,price, p_image, title')->get_where('market_products', array('market_category_id' => $subcategoryID, 'status' => 1), $limits);
          $query="select product_id, mrp,price, p_image, title from market_products where status = 1 AND market_category_id = $subcategoryID ". $limits."";		
          return $this->ci->db->query($query)->result_array();	
		}
        
    }	
	public function fetch_user_items_from_cart($user_id=NULL){
            
			// join goods on cart.product_id = goods.g_id
			$query="select * from cart 			
			where cart.user_id = {$user_id}";		   		   
           return $this->ci->db->query($query)->result_array();			
	}

	public function fetch_comment_reply_data($comment_id=NULL,$user_id=NULL,$blog_id=NULL){
            
		$query="select blog_comment.*,users.first_name,users.email,users.profile_pic from blog_comment 
			join users ON users.id = blog_comment.user_id		
			where blog_comment.blog_id = {$blog_id} AND blog_comment.comment_id = {$comment_id}";		   		   
           return $this->ci->db->query($query)->result_array();		
	}

	public function getcountries($country_id=NULL){
		if(isset($country_id) && $country_id !=NULL){
			$condition = "country_status = 1 AND country_id ={$country_id}";
		}else{
			$condition = "country_status = 1";
		}
            $query="select * from countries 
			where {$condition}";		   		   
           return $this->ci->db->query($query)->result_array();			
	}
	public function getcountryByShortName($country_short_name){
            $condition = "country_short_name = '{$country_short_name}'";
            $query="select * from countries 
			where {$condition}";		   		   
           return $this->ci->db->query($query)->result_array();			
	}	

	public function getimmigration($id=NULL){
		if(isset($id) && $id !=NULL){
			$condition = "status = 1 AND status = 0 AND id ={$id}";
		}else{
			$condition = "status = 1";
		}
            $query="select * from immigration_cards 
			where {$condition}";		   		   
           return $this->ci->db->query($query)->result_array();			
	}

	public function fetch_average_rating($service_id = null){

		$query="select SUM(rating)/COUNT(rating) as average_rating from rating	
			where rating.service_id = {$service_id}";   		   
           return $this->ci->db->query($query)->result_array();	

	}
}