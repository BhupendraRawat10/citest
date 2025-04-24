<?php

class Chess_lib
{
    private $ci;
    public $_get_msg = '';

    public function __construct()
    {
        $this->ci = &get_instance();		
    }
	
	
	public function fetchTicketCategory($id=NULL){
            $query="select * from ticket_category 
			where status=1";		   		   
           return $this->ci->db->query($query)->result_array();			
	}
}