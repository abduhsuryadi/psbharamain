<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        is_logged_in();
        $this->load->library('form_validation');
    }

    public function index()
    {
        $data['title'] = 'My Profile';
        $data['user'] = $this->db->get_where('user', ['notelpon' => $this->session->userdata('notelpon')])->row_array();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('user/index', $data);
        $this->load->view('templates/footer');
    }


    public function edit()
    {
        $data['title'] = 'Edit Profile';
        $data['user'] = $this->db->get_where('user', ['notelpon' => $this->session->userdata('notelpon')])->row_array();

        $this->form_validation->set_rules('alamat', 'Alamat', 'required|trim',[
            'required'=>'Alamat harus diisi'
        ]);
        $this->form_validation->set_rules('tempatlahir', 'Tempat Lahir', 'required|trim',[
            'required'=>'Tempat Lahir harus diisi'
        ]);
        $this->form_validation->set_rules('tanggallahir', 'Tanggal Lahir', 'required|trim|valid_date',[
            'required'=>'Tanggal lahir harus diisi'
        ]);
        $this->form_validation->set_rules('jenjangsekolahasal', 'Jenjang Sekolah asal', 'required|trim',[
            'required'=>'Jenjang sekolah asal harus diisi'
        ]);
        $this->form_validation->set_rules('nik', 'NIK Calon Pelajar', 'required|trim',[
            'required'=>'NIK Calon Pelajar harus diisi'
        ]);
        $this->form_validation->set_rules('nokk', 'Nomor Kartu Keluarga', 'required|trim',[
            'required'=>'Nomor Kartu Keluarga harus diisi'
        ]);
        $this->form_validation->set_rules('namasekolahasal', 'Nama Sekolah Asal', 'required|trim',[
            'required'=>'Nama Sekolah asal harus diisi'
        ]);
        $this->form_validation->set_rules('namaayah', 'Nama Ayah', 'required|trim',[
            'required'=>'Nama ayah harus diisi'
        ]);
        $this->form_validation->set_rules('namaibu', 'Nama Ibu', 'required|trim',[
            'required'=>'Nama ibu harus diisi'
        ]);
        $this->form_validation->set_rules('namawali', 'Nama Wali', 'required|trim',[
            'required'=> 'Nama wali harus diisi'
        ]);

        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/edit', $data);
            $this->load->view('templates/footer');
        } else {
            $notelpon =$this->session->userdata('notelpon');
            $data=array(
                'nisn' => htmlspecialchars($this->input->post('nisn')),
                'kodepos' => htmlspecialchars($this->input->post('kodepos')),
                'alamat' => htmlspecialchars($this->input->post('alamat')),
                'tempatlahir'=> htmlspecialchars($this->input->post('tempatlahir')),
                'tanggallahir'=> convertToDate($this->input->post('tanggallahir')),
                'nik'=> htmlspecialchars($this->input->post('nik')),
                'nokk'=> htmlspecialchars($this->input->post('nokk')),
                'jenjangsekolahasal' => $this->input->post('jenjangsekolahasal'),
                'namasekolahasal'=> htmlspecialchars($this->input->post('namasekolahasal')),
                'namaayah'=> htmlspecialchars($this->input->post('namaayah')),
                'nikayah'=> htmlspecialchars($this->input->post('nikayah')),
                'pekerjaanayah'=> htmlspecialchars($this->input->post('pekerjaanayah')),
                'pendidikanayah'=> htmlspecialchars($this->input->post('pendidikanayah')),
                'namaibu'=> htmlspecialchars($this->input->post('namaibu')),
                'nikibu'=> htmlspecialchars($this->input->post('nikibu')),
                'pekerjaanibu'=> htmlspecialchars($this->input->post('pekerjaanibu')),
                'pendidikanibu' => htmlspecialchars($this->input->post('pendidikanibu')),
                'namawali'=> htmlspecialchars($this->input->post('namawali')),
                'nikwali'=> htmlspecialchars($this->input->post('nikwali')),
                'pekerjaanwali'=> htmlspecialchars($this->input->post('pekerjaanwali')),
                'pendidikanwali'=> htmlspecialchars($this->input->post('pendidikanwali')),
                'statuswali'=>$this->input->post('statuswali')
            );

            $this->db->where('notelpon', $notelpon);
            $this->db->update('user',$data);

            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Your profile has been updated!</div>');
            redirect('user');
        }
    }


    public function changePassword()
    {
        $data['title'] = 'Change Password';
        $data['user'] = $this->db->get_where('user', ['notelpon' => $this->session->userdata('notelpon')])->row_array();

        $this->form_validation->set_rules('current_password', 'Current Password', 'required|trim');
        $this->form_validation->set_rules('new_password1', 'New Password', 'required|trim|min_length[3]|matches[new_password2]');
        $this->form_validation->set_rules('new_password2', 'Confirm New Password', 'required|trim|min_length[3]|matches[new_password1]');

        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/changepassword', $data);
            $this->load->view('templates/footer');
        } else {
            $current_password = $this->input->post('current_password');
            $new_password = $this->input->post('new_password1');
            if (!password_verify($current_password, $data['user']['password'])) {
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Wrong current password!</div>');
                redirect('user/changepassword');
            } else {
                if ($current_password == $new_password) {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">New password cannot be the same as current password!</div>');
                    redirect('user/changepassword');
                } else {
                    // password sudah ok
                    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

                    $this->db->set('password', $password_hash);
                    $this->db->where('notelpon', $this->session->userdata('notelpon'));
                    $this->db->update('user');

                    $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Password changed!</div>');
                    redirect('user/changepassword');
                }
            }
        }
    }

    function checkDateFormat($date) {
        if (preg_match("/[0-31]{2}/[0-12]{2}/[0-9]{4}/", $date)) {
        if(checkdate(substr($date, 3, 2), substr($date, 0, 2), substr($date, 6, 4)))
            return true;
        else
            return false;
        } else {
            return false;
        }
    } 

    function uploadfoto(){
        // cek jika ada gambar yang akan diupload
        $data['user'] = $this->db->get_where('user', ['notelpon' => $this->session->userdata('notelpon')])->row_array();
        $upload_image = $_FILES['image']['name'];
        if ($upload_image) {
            echoalert("sukses ambil file");
            $config['allowed_types'] = 'gif|jpg|png';
            $config['max_size']      = '2048';
            $config['upload_path'] = './assets/img/profile/';
            $config['file_name'] =$data['user']['notelpon']; 

            $this->load->library('upload', $config);

            if ($this->upload->do_upload('image')) {
                $old_image = $data['user']['image'];
                if ($old_image != 'default.jpg') {
                    unlink(FCPATH . 'assets/img/profile/' . $old_image);
                }
                $new_image = $this->upload->data('file_name');
                $this->db->set('image', $new_image);
                $this->db->where('notelpon', $data['user']['notelpon']);
                $this->db->update('user');
                $this->session->set_flashdata('message', '<div class="alert alert-primary alert-dismissible fade show" role="alert">Foto Anda telah diperbaharui!
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span></button></div>');
            } else {
                $this->session->set_flashdata('message', '<div class="alert alert-warning alert-dismissible fade show" role="alert">Foto salah format!
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span></button></div>');
                echo $this->upload->display_errors();
            }
        }

        redirect('user');

    }

    function getNomorDaftar(){
        $data['user'] = $this->db->get_where('user', ['notelpon' => $this->session->userdata('notelpon')])->row_array();
        $tingkat=$data['user']['tingkat'];
        $notelpon=$data['user']['notelpon'];
        $nomordaftar=$data['user']['nomordaftar'];

        if(!$nomordaftar){

            if($tingkat=="MA"){
                $kode="C2020-";
            } else if($tingkat=="MTs") {
                $kode="B2020-";
            } else if($tingkat=="MI"){
                $kode="A2020-";
            }
    
            $this->db->select_max('nomordaftar');
            $this->db->where('tingkat',$tingkat);
            $result = $this->db->get('user')->row();  
            $maxno=$result->nomordaftar;
    
            $nomor=0;
    
            if($maxno){
                $nomor=intval(substr($maxno,6,3));
            } 
            $nomor += 1;
    
            $nomorbaru=$kode.str_pad($nomor,3,'0',STR_PAD_LEFT);
            echo $nomorbaru;
    
            $this->db->where('notelpon',$notelpon);
            $this->db->set('nomordaftar',$nomorbaru);
            $this->db->update('user');    

            echoalert("SELAMAT, ANDA TELAH BERHASIL MENJADI CALON PELAJAR DENGAN NOMOR: $nomorbaru");
        }
        redirect('user');
    }
}
