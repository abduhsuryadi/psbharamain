<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('Select_model');
    }

    public function login()
    {        
        if ($this->session->userdata('notelpon')) {
            redirect('user');
        }
        echo $this->session->userdata('notelpon');
        $this->form_validation->set_rules('notelpon', 'notelpon', 'trim|required');
        $this->form_validation->set_rules('password', 'Password', 'trim|required');

        if ($this->form_validation->run() == false) {
            $data['title'] = 'Login Page';
            $this->load->view('templates/auth_header', $data);
            $this->load->view('auth/login');
            $this->load->view('templates/auth_footer');
        } else {
            // validasinya success
            $this->_login();
        }
    }

    public function suksesdaftar(){
        $data['title']="BELUM AKTIFASI";
        $data['notelpon']=$this->session->userdata('verifikasi');
        $this->load->view('templates/auth_header',$data);
        $this->load->view('auth/suksesdaftar',$data);
        $this->load->view('templates/auth_footer');
    }

    public function index(){
        $data['title']="HALAMAN UTAMA";
        $data['keterangan']=GetNilai('Web Uraian Pembuka');
        $this->load->view('templates/auth_header',$data);
        $this->load->view('auth/halamanutama');
        $this->load->view('templates/auth_footer');
    }

    private function _login()
    {
        $notelpon = $this->input->post('notelpon');
        $password = $this->input->post('password');
        
        $user = $this->db->get_where('user', ['notelpon' => $notelpon])->row_array();
        echohr($password);
        echohr($user['password']);
        echohr(password_verify($password, $user['password']));
        //echo $user['is_active'];

        // jika usernya ada
        if ($user) {
            // jika usernya aktif
            if ($user['is_active'] == 1) {
                // cek password
                if (password_verify($password, $user['password'])) {
                    $data = [
                        'notelpon' => $user['notelpon'],
                        'role_id' => $user['role_id']
                    ];
                    echohr("password benar",1);
                    $this->session->set_userdata($data);
                    if ($user['role_id'] == 1) {
                        redirect('admin');
                    } else {
                        redirect('user');
                    }
                } else {
                    echohr("Password salah",1);
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Password salah!</div>');
                    redirect('auth/login');
                }
            } else { 
                echohr("User tidak aktif",1);
                redirect('auth/suksesdaftar');
            }
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Nomor telpon belum terdaftar!</div>');
            redirect('auth/login');
        }
    }


    public function registration()
    {
        if ($this->session->userdata('notelpon')) {
            redirect('user');
        }

        $this->form_validation->set_rules('nama', 'Name', 'required|trim',[
            'required'=>'Nama harus diisi'
        ]);
        $this->form_validation->set_rules('notelpon', 'Handphone', 'required|trim|is_unique[user.notelpon]|min_length[10]|max_length[13]|numeric',[
            'is_unique'=>'Nomor Handphone telah terdaftar. Silahkan gunakan yang lain',
            'required' => 'Nomor Handphone harus diisi',
            'min_length'=>'Nomor Handphone minimal 10 digit',
            'max_length'=>'Nomor Handphone maksimal 13 digit',
            'numeric'=>'Nomor Handphone hanya boleh angka'
        ]);
        $this->form_validation->set_rules('tingkat', 'Tingkat', 'required|trim',[
            'required'=>'Silahkan pilih tingkat sekolah daftar'
        ]);
        $this->form_validation->set_rules('propinsi', 'Province', 'required|trim',[
            'required'=>'Propinsi harus diisi'
        ]);
        $this->form_validation->set_rules('kabupaten', 'Regency (Kabupaten)', 'required|trim',[
            'required'=>'Kabupaten harus diisi'
        ]);
        $this->form_validation->set_rules('kecamatan', 'District (Kecamatan)', 'required|trim',[
            'required'=>'Kecamatan harus diisi'
        ]);
        $this->form_validation->set_rules('desa', 'Village (Desa)', 'required|trim',[
            'required'=>'Desa harus diisi'
        ]);
        $this->form_validation->set_rules('password1', 'Password', 'required|trim|min_length[3]|matches[password2]', [
            'matches' => 'Konfirmasi password tidak sama!',
            'min_length' => 'Password terlalu pendek!',
            'required'=>'Password harus diisi'
        ]);
        
        $this->form_validation->set_rules('password2', 'Password', 'required|trim|matches[password1]');

        if ($this->form_validation->run() == false) {
            $data['title'] = 'REGISTRASI';
            $data['propinsi']=$this->Select_model->propinsi();

            $this->load->view('templates/auth_header', $data);
            $this->load->view('auth/registration');
            $this->load->view('templates/auth_footer');
        } else {
            $notelpon=$this->input->post('notelpon');
            $data = [ 
                'nama' => htmlspecialchars($this->input->post('nama', true)),
                'tingkat' => htmlspecialchars($this->input->post('tingkat', true)),
                'notelpon' => htmlspecialchars($this->input->post('notelpon', true)),
                'propinsi' => $this->input->post('propinsi', true),
                'kabupaten' => $this->input->post('kabupaten', true),
                'kecamatan' => $this->input->post('kecamatan', true),
                'desa' => $this->input->post('desa', true),
                'image' => 'default.jpg',
                'password' => password_hash($this->input->post('password1'), PASSWORD_DEFAULT),
                'role_id' => 2,
                'is_active' => 0,
                'date_created' => time()
            ];

            $hasil=$this->db->insert('user', $data);
            if(!$hasil){
                $this->session->set_flashdata('message', '<div class="alert alert-failed" role="alert">Registrasi gagal, silahkan ulangi lagi!</div>');
                redirect('auth');
            } else {
                $sesi=array(
                    'verifikasi'=> $notelpon
                );
                $this->session->set_userdata($sesi);
                redirect('auth/suksesdaftar');
            }
        }
    }


    private function _sendnotelpon($token, $type)
    {
        $config = [
            'protocol'  => 'smtp',
            'smtp_host' => 'ssl://smtp.googlnotelpon.com',
            'smtp_user' => 'wpunpas@gmail.com',
            'smtp_pass' => '1234567890',
            'smtp_port' => 465,
            'mailtype'  => 'html',
            'charset'   => 'utf-8',
            'newline'   => "\r\n"
        ];

        $this->notelpon->initialize($config);

        $this->notelpon->from('wpunpas@gmail.com', 'Web Programming UNPAS');
        $this->notelpon->to($this->input->post('notelpon'));

        if ($type == 'verify') {
            $this->notelpon->subject('Account Verification');
            $this->notelpon->message('Click this link to verify you account : <a href="' . base_url() . 'auth/verify?notelpon=' . $this->input->post('notelpon') . '&token=' . urlencode($token) . '">Activate</a>');
        } else if ($type == 'forgot') {
            $this->notelpon->subject('Reset Password');
            $this->notelpon->message('Click this link to reset your password : <a href="' . base_url() . 'auth/resetpassword?notelpon=' . $this->input->post('notelpon') . '&token=' . urlencode($token) . '">Reset Password</a>');
        }

        if ($this->notelpon->send()) {
            return true;
        } else {
            echo $this->notelpon->print_debugger();
            die;
        }
    }


    public function verify()
    {
        $notelpon = $this->input->get('notelpon');
        $token = $this->input->get('token');

        $user = $this->db->get_where('user', ['notelpon' => $notelpon])->row_array();

        if ($user) {
            $user_token = $this->db->get_where('user_token', ['token' => $token])->row_array();

            if ($user_token) {
                if (time() - $user_token['date_created'] < (60 * 60 * 24)) {
                    $this->db->set('is_active', 1);
                    $this->db->where('notelpon', $notelpon);
                    $this->db->update('user');

                    $this->db->delete('user_token', ['notelpon' => $notelpon]);

                    $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">' . $notelpon . ' has been activated! Please login.</div>');
                    redirect('auth');
                } else {
                    $this->db->delete('user', ['notelpon' => $notelpon]);
                    $this->db->delete('user_token', ['notelpon' => $notelpon]);

                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Account activation failed! Token expired.</div>');
                    redirect('auth');
                }
            } else {
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Account activation failed! Wrong token.</div>');
                redirect('auth');
            }
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Account activation failed! Wrong notelpon.</div>');
            redirect('auth');
        }
    }


    public function logout()
    {
        $this->session->unset_userdata('notelpon');
        $this->session->unset_userdata('role_id');
        $this->session->unset_userdata('verifikasitelpon');

        $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">You have been logged out!</div>');
        redirect('auth');
    }


    public function blocked()
    {
        $this->load->view('auth/blocked');
    }


    public function forgotPassword()
    {
        $this->form_validation->set_rules('notelpon', 'notelpon', 'trim|required|valid_notelpon');

        if ($this->form_validation->run() == false) {
            $data['title'] = 'Lupa Password';
            $this->load->view('templates/auth_header', $data);
            $this->load->view('auth/forgot-password');
            $this->load->view('templates/auth_footer');
        } else {
            $notelpon = $this->input->post('notelpon');
            $user = $this->db->get_where('user', ['notelpon' => $notelpon, 'is_active' => 1])->row_array();

            if ($user) {
                $token = base64_encode(random_bytes(32));
                $user_token = [
                    'notelpon' => $notelpon,
                    'token' => $token,
                    'date_created' => time()
                ];

                $this->db->insert('user_token', $user_token);
                $this->_sendnotelpon($token, 'forgot');

                $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Please check your notelpon to reset your password!</div>');
                redirect('auth/forgotpassword');
            } else {
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">notelpon is not registered or activated!</div>');
                redirect('auth/forgotpassword');
            }
        }
    }


    public function resetPassword()
    {
        $notelpon = $this->input->get('notelpon');
        $token = $this->input->get('token');

        $user = $this->db->get_where('user', ['notelpon' => $notelpon])->row_array();

        if ($user) {
            $user_token = $this->db->get_where('user_token', ['token' => $token])->row_array();

            if ($user_token) {
                $this->session->set_userdata('reset_notelpon', $notelpon);
                $this->changePassword();
            } else {
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Reset password failed! Wrong token.</div>');
                redirect('auth');
            }
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Reset password failed! Wrong notelpon.</div>');
            redirect('auth');
        }
    }


    public function changePassword()
    {
        if (!$this->session->userdata('reset_notelpon')) {
            redirect('auth');
        }

        $this->form_validation->set_rules('password1', 'Password', 'trim|required|min_length[3]|matches[password2]');
        $this->form_validation->set_rules('password2', 'Repeat Password', 'trim|required|min_length[3]|matches[password1]');

        if ($this->form_validation->run() == false) {
            $data['title'] = 'Ubah Password';
            $this->load->view('templates/auth_header', $data);
            $this->load->view('auth/change-password');
            $this->load->view('templates/auth_footer');
        } else {
            $password = password_hash($this->input->post('password1'), PASSWORD_DEFAULT);
            $notelpon = $this->session->userdata('reset_notelpon');

            $this->db->set('password', $password);
            $this->db->where('notelpon', $notelpon);
            $this->db->update('user');

            $this->session->unset_userdata('reset_notelpon');

            $this->db->delete('user_token', ['notelpon' => $notelpon]);

            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Password has been changed! Please login.</div>');
            redirect('auth');
        }
    }

    public function ambilalamat(){
        
        $modul=$this->input->post('modul');
        $id=$this->input->post('id');
        
        if($modul=="kabupaten"){
            echo "kabupaten";
            echo $this->Select_model->kabupaten($id);
        }
        else if($modul=="kecamatan"){
            echo $this->Select_model->kecamatan($id);
        }
        else if($modul=="desa"){
            echo $this->Select_model->desa($id);
        }
    }


}
