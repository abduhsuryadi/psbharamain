<?php
defined('BASEPATH') or exit('No direct script access allowed');

Class Test extends CI_Controller
{


    function __construct(){

        parent::__construct();

    }


    function index(){
        $str="03-01-2020";
        echo FCPATH;
        // /echo convertToDate($str);
    }
    
}