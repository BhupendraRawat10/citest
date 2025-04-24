<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Search extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper('form');
    }

    public function index() {
        // Load the search form view
        $this->load->view('search');
    }

    public function search_images() {
        // Get the search query
        $query = $this->input->post('query');
        
        // Your Pixabay API key
        $api_key = '49905554-669a212d5cd2e1f67827d3520';
        
        // Make an API request to Pixabay using the search query
        $url = "https://pixabay.com/api/?key=$api_key&q=$query&image_type=photo";
        $response = file_get_contents($url);
        
        // Decode the JSON response from Pixabay
        $data['results'] = json_decode($response)->hits;

        // Load the results view and pass the image results
        $this->load->view('search_results', $data);
    }
}
