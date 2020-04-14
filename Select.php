<?php
defined('BASEPATH') or exit('No direct script access allowed');

Class Select extends CI_Controller
{


    function __construct(){

        parent::__construct();

    }


    function index(){


        $data['propinsi']=$this->model_select->propinsi();

        echo "hai";

    }

    function ambil_data(){

    }

}