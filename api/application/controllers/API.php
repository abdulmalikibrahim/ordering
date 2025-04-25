<?php
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
require_once FCPATH . 'vendor/autoload.php';
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Picqer\Barcode\BarcodeGeneratorHTML;
use Dompdf\Dompdf;

class API extends MY_Controller {

    function upload_master()
    {
        // Konfigurasi upload file
        $config['upload_path']   = './uploads/';
        $config['allowed_types'] = 'xls|xlsx';

        $this->upload->initialize($config);
        if (!$this->upload->do_upload('upload-file')) {
            // Jika upload gagal, tampilkan error
            $error = $this->upload->display_errors();
            $this->fb(["statusCode" => 500, "res" => $error]);
        }
        
        // Jika upload berhasil
        $file_data = $this->upload->data();
        $file_path = $file_data['full_path'];
        // Load PHPExcel
        require 'vendor/autoload.php';
        $objPHPExcel = IOFactory::load($file_path);

        $clear_data = $this->model->delete("master", "id !=");
        // Membaca sheet pertama
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $data = [];
        // Looping untuk membaca data dari setiap baris
        for ($row = 2; $row <= $highestRow; $row++) { // Mulai dari baris ke-2 (baris pertama biasanya header)
            if(!empty(str_replace(" ","",$sheet->getCell('A' . $row)->getValue()))){
                $data[] = [
                    'part_number' => $sheet->getCell('A' . $row)->getValue(),
                    'part_name' => $sheet->getCell('B' . $row)->getValue(),
                    'vendor_code' => $sheet->getCell('C' . $row)->getValue(),
                    'vendor_name' => $sheet->getCell('D' . $row)->getValue(),
                    'vendor_site' => $sheet->getCell('E' . $row)->getValue(),
                    'vendor_site_alias' => $sheet->getCell('F' . $row)->getValue(),
                    'job_no' => $sheet->getCell('G' . $row)->getValue(),
                    'remark' => $sheet->getCell('H' . $row)->getValue()
                ];
            }
        }
        
        $insert = $this->model->insert_batch("master",$data);
        if($insert){
            $fb = ["statusCode" => 200, "res" => "Upload success"];
        }else{
            $fb = ["statusCode" => 500, "res" => "Upload failed"];
        }
        unlink($file_path);
        $this->fb($fb);
    }
    
    function login()
    {
        $username = $this->input->post("username");
        $password = $this->input->post("password");
        $validation = $this->model->gd("account","*,(SELECT name FROM departement WHERE id = dept) AS dept","username = '$username'","row");
        if(empty($validation)){
            $fb = ["statusCode" => 500, "res" => "Akun belum di daftarkan"];
            $this->fb($fb);
        }

        if($validation->status == "0"){
            $fb = ["statusCode" => 500, "res" => "Akun sudah di non aktifkan"];
            $this->fb($fb);
        }

        if(!password_verify($password,$validation->password)){
            $fb = ["statusCode" => 500, "res" => "Password salah"];
            $this->fb($fb);
        }

        $level_name = "Admin";
        if($validation->level == "2"){
            $level_name = "User";
        }else if($validation->level == "3"){
            $level_name = "Supervisor";
        }else if($validation->level == "4"){
            $level_name = "Manager";
        }
        $data = [
            "id_user" => $validation->id,
            "name" => $validation->name,
            "level" => $validation->level,
            "dept" => $validation->dept,
            "level_name" => $level_name
        ];
        
        $this->session->set_userdata($data);
        if($this->session->userdata("id_user")){
            $fb = ["statusCode" => 200, "res" => "Sukses Login"];
        }else{
            $fb = ["statusCode" => 400, "res" => "Gagal login"];
        }
        $this->fb($fb);
    } 

    function checkLogin()
    {
        if(empty($this->session->userdata("id_user"))){
            $fb = ["statusCode" => 202, "res" => "Session kosong, silahkan login", "id_user" => $this->session->userdata("id_user")];
            $this->fb($fb);
        }
        
        $fb = [
            "statusCode" => 200, 
            "res" => "Session masih ada", 
            "id_user" => $this->id_user, 
            "name" => $this->name, 
            "level" => $this->level, 
            "dept" => $this->dept,
            "level_name" => $this->level_name, 
            "dept_name" => $this->dept_name
        ];
        $this->fb($fb);
    }

    function logout()
    {
        $this->session->sess_destroy();
        $fb = ["statusCode" => 200, "res" => "Logout berhasil"];
        $this->fb($fb);
    }

    function get_master()
    {
        $data = $this->model->gd("master","*","id !=","result_array");
        $this->fb($data);
    }

    //ACCUONT
        function get_account()
        {
            $data = $this->model->gd("account","*","id !=","result");
            $newData = [];
            if (!empty($data)) {
                foreach ($data as $row) {  // ✅ Gunakan $row, jangan timpa $data
                    $dept = $this->model->gd("departement", "name", "id = '$row->dept'", "row");
                    
                    // Konversi level ke string yang sesuai
                    $level = "Admin";
                    if ($row->level == "2") {
                        $level = "User";
                    } else if ($row->level == "3") {
                        $level = "Supervisor";
                    } else if ($row->level == "4") {
                        $level = "Manager";
                    }

                    // ✅ Jangan timpa data $row dengan query lain!
                    $newData[] = [
                        "id" => $row->id,
                        "username" => $row->username,
                        "name" => $row->name,
                        "dept" => $dept->name ?? 'Unknown', // Hindari error jika dept NULL
                        "level" => $level,
                        "id_dept" => $row->dept ?? '',
                        "id_level" => $row->level ?? '',
                    ];
                }
            }
            $this->fb($newData);
        }

        function get_dept()
        {
            $dept = $this->model->gd("departement","id,name,shop_code","id !=","result_array");
            $this->fb($dept);    
        }

        function save_account()
        {
            $mode = $this->input->post('mode');
            $this->form_validation
                ->set_rules("name","Nama","required|trim")
                ->set_rules("username","Username","required|trim")
                ->set_rules("dept","Departement","required|trim|integer")
                ->set_rules("level","Level","required|trim|integer")
                ->set_rules("mode","Mode","required|trim");

            if($mode == "add"){
                $this->form_validation->set_rules("password","Password","required|trim");
            }
            
            if ($this->form_validation->run() === FALSE) {
                $fb = ["statusCode" => 400, "res" => validation_errors()];
                $this->fb($fb);
            }

            $name = $this->input->post('name');
            $username = $this->input->post('username');
            $password = $this->input->post('password');
            $dept = $this->input->post('dept');
            $level = $this->input->post('level');
            $id_update = $this->input->post('id_update');

            $dataSubmit = [
                "name" => $name,
                "username" => $username,
                "dept" => $dept,
                "level" => $level,
            ];

            if($mode == "add"){
                $dataSubmit["password"] = password_hash($password, PASSWORD_DEFAULT);
            }

            if($mode == "add"){
                $action = $this->model->insert("account",$dataSubmit);
            }else{
                $action = $this->model->update("account","id = '$id_update'",$dataSubmit);
            }

            if($action){
                $fb = ["statusCode" => 200, "res" => "Data berhasil disimpan"];
            }else{
                $fb = ["statusCode" => 500, "res" => "Data gagal disimpan"];
            }
            $this->fb($fb);
        }
        
        function reset_password()
        {
            $id = $this->input->post('id');

            $dataSubmit = ["password" => password_hash("Astra123",PASSWORD_DEFAULT)];
            $action = $this->model->update("account","id = '$id_update'",$dataSubmit);

            if($action){
                $fb = ["statusCode" => 200, "res" => "Data berhasil disimpan"];
            }else{
                $fb = ["statusCode" => 500, "res" => "Data gagal disimpan"];
            }
            $this->fb($fb);
        }

        function delete_account()
        {
        $id = $this->input->post("id");
        $this->model->delete("account","id = '$id'");
        $fb = ["statusCode" => 200];
        $this->fb($fb); 
        }
    //ACCOUNT

    //DEPARTEMENT
        function save_dept()
        {
            $mode = $this->input->post('mode');
            $this->form_validation
                ->set_rules("name","Nama","required|trim")
                ->set_rules("shop_code","Shop Code","required|trim");
            
            if ($this->form_validation->run() === FALSE) {
                $fb = ["statusCode" => 400, "res" => validation_errors()];
                $this->fb($fb);
            }

            $name = $this->input->post('name');
            $shop_code = $this->input->post('shop_code');
            $id_update = $this->input->post('id_update');

            $dataSubmit = [
                "name" => $name,
                "shop_code" => $shop_code,
            ];

            if($mode == "add"){
                $action = $this->model->insert("departement",$dataSubmit);
            }else{
                $action = $this->model->update("departement","id = '$id_update'",$dataSubmit);
            }

            if($action){
                $fb = ["statusCode" => 200, "res" => "Data berhasil disimpan"];
            }else{
                $fb = ["statusCode" => 500, "res" => "Data gagal disimpan"];
            }
            $this->fb($fb);
        }

        function delete_departement()
        {
        $id = $this->input->post("id");
        $this->model->delete("departement","id = '$id'");
        $fb = ["statusCode" => 200];
        $this->fb($fb); 
        }
    //DEPARTEMENT

    //DATA SO
        function get_data_so()
        {
            $tipe = $this->input->get("tipe");
            $level = $this->level;
            if($tipe == "release_so"){
                $data = $this->model->gd(
                    "data_order",
                    "*", 
                    $level == "1" 
                    ? "deleted_date IS NULL AND release_sign != '' AND release_sign_time != ''" 
                    : "deleted_date IS NULL AND pic = '".$this->id_user."' AND release_sign != '' AND release_sign_time != ''",
                    "result"
                );
            }else if($tipe == "delete_so"){
                $data = $this->model->gd(
                    "data_order",
                    "*", 
                    $level == "1" 
                    ? "deleted_date IS NOT NULL AND id != ''" 
                    : "deleted_date IS NOT NULL AND pic = '".$this->id_user."' AND id != ''",
                    "result"
                );
            }else if($tipe == "need_release"){
                $data = $this->model->gd(
                    "data_order",
                    "*", 
                    "deleted_date IS NULL AND spv_sign_time IS NOT NULL AND mng_sign_time IS NOT NULL AND release_sign IS NULL",
                    "result"
                );
            }else{
                $data = $this->model->gd(
                    "data_order",
                    "*", 
                    $level == "1" 
                    ? "deleted_date IS NULL AND tipe = '$tipe'" 
                    : "deleted_date IS NULL AND pic = '".$this->id_user."' AND tipe='$tipe'",
                    "result"
                );
            }
            $newData = [];
            if (!empty($data)) {
                foreach ($data as $row) {
                    $account = $this->model->gd("account", "name, (SELECT name FROM departement WHERE id=dept) as name_dept", "id = '$row->created_by'", "row");
                    $spv_sign = '-';
                    $spv_sign_time = '-';
                    if(!empty($row->spv_sign)){
                        $data_spv = $this->model->gd("account", "name", "id = '$row->spv_sign'", "row");
                        $spv_sign = $data_spv->name;
                        $spv_sign_time = empty($row->spv_sign_time) ? "" : date("d-M-Y H:i",strtotime($row->spv_sign_time));
                    }
                    
                    $mng_sign = '-';
                    $mng_sign_time = '-';
                    if(!empty($row->mng_sign)){
                        $data_spv = $this->model->gd("account", "name", "id = '$row->mng_sign'", "row");
                        $mng_sign = $data_spv->name;
                        $mng_sign_time = empty($row->mng_sign_time) ? "" : date("d-M-Y H:i",strtotime($row->mng_sign_time));
                    }
                    
                    if(!empty($row->release_sign)){
                        $account = $this->model->gd("account", "name, (SELECT name FROM departement WHERE id=dept) as name_dept", "id = '$row->release_sign'", "row");
                        $release_sign = $account->name;
                    }else{
                        $release_sign = '';
                    }
                    $release_sign_time = !empty($row->release_sign_time) ? date("d-M-Y H:i",strtotime($row->release_sign_time)) : '';

                    $total_part = $this->model->gd("data_part_order","COUNT(*) as total","deleted_date IS NULL AND so_number = '$row->so_number'","row");
                    $detail_part = $this->model->join_data("data_part_order a","master b","a.part_number=b.part_number","a.part_number,a.vendor_code,b.part_name,b.vendor_name,b.job_no","a.deleted_date IS NULL AND so_number = '$row->so_number'","result");
                    $detailing_part = '';
                    if(!empty($detail_part)){
                        foreach ($detail_part as $detail_part) {
                            $detailing_part .= $detail_part->part_number." ".rtrim($detail_part->part_name)." ".$detail_part->vendor_code." ".$detail_part->vendor_name." ".$detail_part->job_no;
                        }
                    }

                    $newData[] = [
                        "id" => $row->id,
                        "created_time" => empty($row->created_time) ? "" : date("d-M-Y H:i:s",strtotime($row->created_time)),
                        "creator" => $account->name ?? '',
                        "dept_creator" => $account->name_dept ?? '', // Hindari error jika dept NULL
                        "so_number" => $row->so_number,
                        "shop_code" => $row->shop_code,
                        "spv_sign" => $spv_sign,
                        "spv_sign_time" => $spv_sign_time,
                        "mng_sign" => $mng_sign,
                        "mng_sign_time" => $mng_sign_time,
                        "release_sign" => $release_sign,
                        "release_sign_time" => $release_sign_time,
                        "total_part" => $total_part->total,
                        "detailing_part" => $detailing_part
                    ];
                }
            }
            $this->fb($newData);
        }

        function data_part_master()
        {
            $detail_part = $this->model->gd("master","part_number,part_name,vendor_code,vendor_name,job_no","id != ''","result");
            $detailing_part = [];
            if(!empty($detail_part)){
                foreach ($detail_part as $detail_part) {
                    $detailing_part[] = $detail_part->part_number." ".rtrim($detail_part->part_name)." ".$detail_part->vendor_code." ".$detail_part->vendor_name." ".$detail_part->job_no;
                }
            }
            $this->fb($detailing_part);
        }

        function get_detail_part_so()
        {
            $so_number = $this->input->get("so_number");
            $data = $this->model->gd("data_part_order","*", "deleted_date IS NULL AND so_number = '$so_number'" ,"result");
            $newData = [];
            if (!empty($data)) {
                foreach ($data as $row) {
                    $detail_part = $this->model->gd("master","part_name,vendor_site_alias,job_no","part_number = '$row->part_number' AND vendor_code = '$row->vendor_code'","row");
                    $newData[] = [
                        "id" => $row->id,
                        "so_number" => $row->so_number,
                        "tgl_delivery" => empty($row->tgl_delivery) ? "" : date("d-M-Y",strtotime($row->tgl_delivery)),
                        "shop_code" => $row->shop_code,
                        "part_number" => $row->part_number,
                        "part_name" => $detail_part->part_name,
                        "vendor_code" => $row->vendor_code,
                        "vendor_name" => $detail_part->vendor_site_alias,
                        "qty_kanban" => $row->qty_kanban,
                        "job_no" => $detail_part->job_no,
                    ];
                }
            }
            $this->fb($newData);   
        }

        function upload_parts()
        {
            // Konfigurasi upload file
            $config['upload_path']   = './uploads/';
            $config['allowed_types'] = 'xls|xlsx';

            $this->upload->initialize($config);
            if (!$this->upload->do_upload('file')) {
                // Jika upload gagal, tampilkan error
                $error = $this->upload->display_errors();
                $this->fb(["statusCode" => 500, "res" => $error]);
            }
            
            // Jika upload berhasil
            $file_data = $this->upload->data();
            $file_path = $file_data['full_path'];
            // Load PHPExcel
            require 'vendor/autoload.php';
            $objPHPExcel = IOFactory::load($file_path);

            // Membaca sheet pertama
            $sheet = $objPHPExcel->getSheet(0);
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();

            $data = [];
            
            //CREATE DATA ORDER
            $pic = $this->input->post("pic");
            $mode_input = $this->input->post("modeInput");
            $check_so_number = $this->model->gd("data_order","COUNT(*) as total","created_time LIKE '%".date("Y-m-")."%' AND tipe = '$mode_input'","row");
            $tipe_so = $mode_input == "upload_so" ? "SO" : ($mode_input == "reduce" ? "REDUCE" : "ADD");
            $so_number = $tipe_so."/PCD/KAP/".date("m/Y/").sprintf("%03d", $check_so_number->total + 1);
            $detail_account = $this->model->gd("account","dept,(SELECT shop_code FROM departement WHERE id=account.dept) as shop_code","id = '$pic'","row");
            $spv_sign = $this->model->gd("account","id","status = '1' AND level = '3' AND dept = '".$detail_account->dept."'","row");
            $mng_sign = $this->model->gd("account","id","status = '1' AND level = '4' AND dept = '".$detail_account->dept."'","row");

            if(empty($spv_sign) || empty($mng_sign)){
                $fb = ["statusCode" => 500, "res" => "Upload gagal karena PIC Departement yang anda pilih belum memiliki account SPV atau MNG"];
                $this->fb($fb);
            }

            // Looping untuk membaca data dari setiap baris
            for ($row = 2; $row <= $highestRow; $row++) { // Mulai dari baris ke-2 (baris pertama biasanya header)
                if(!empty(str_replace(" ","",$sheet->getCell('A' . $row)->getValue()))){
                    $cellValue = $sheet->getCell('B' . $row)->getValue();
                    if (is_numeric($cellValue)) {
                        $timestamp = Date::excelToTimestamp($cellValue);
                        $tgl_delivery = date('Y-m-d', $timestamp);
                    } else {
                        // Jika ternyata string biasa
                        $tgl_delivery = date('Y-m-d', strtotime($cellValue));
                    }

                    $qty_kanban = $sheet->getCell('E' . $row)->getValue();
                    if($mode_input == "reduce" && substr_count($sheet->getCell('E' . $row)->getValue(), "-") <= 0){
                        $qty_kanban = $sheet->getCell('E' . $row)->getValue()*-1;
                    }

                    $data[] = [
                        'so_number' => $so_number,
                        'tgl_delivery' => $tgl_delivery,
                        'shop_code' => $detail_account->shop_code,
                        'part_number' => $sheet->getCell('C' . $row)->getValue(),
                        'vendor_code' => $sheet->getCell('D' . $row)->getValue(),
                        'qty_kanban' => $qty_kanban,
                        'remarks' => $sheet->getCell('F' . $row)->getValue(),
                    ];
                }
            }
            
            //INPUT SO
            $data_input = [
                "created_by" => $this->id_user,
                "pic" => $pic,
                "spv_sign" => $spv_sign->id,
                "mng_sign" => $mng_sign->id,
                "so_number" => $so_number,
                "tipe" => $mode_input,
                "shop_code" => $detail_account->shop_code,
            ];
            $this->model->insert("data_order",$data_input);
            
            $insert = $this->model->insert_batch("data_part_order",$data);
            if($insert){
                $fb = ["statusCode" => 200, "res" => "Upload success"];
            }else{
                $fb = ["statusCode" => 500, "res" => "Upload failed"];
            }
            unlink($file_path);
            $this->fb($fb);
        }

        function delete_so_number()
        {
            if(empty($this->id_user)){
                $fb = ["statusCode" => 500, "res" => "Session kosong"];
                $this->fb($fb);
            }

            $so_number = $this->input->post("so_number");
            $deleteData = [
                "deleted_date" => date("Y-m-d H:i:s"),
            ];
            $this->model->update("data_order","so_number = '$so_number'",$deleteData);
            $this->model->update("data_part_order","so_number = '$so_number'",$deleteData);
            $fb = ["statusCode" => 200, "res" => "Delete Success"];
            $this->fb($fb);
        }

        function release_so()
        {
            $level = $this->level;
            $id_user = $this->id_user;

            if(empty($level)){
                $fb = ["statusCode" => 400, "res" => "Sesi login anda telah berakhir, refresh halaman dan login kembali"];
                $this->fb($fb);
            }

            if($level != "1"){
                $fb = ["statusCode" => 400, "res" => "Anda tidak memiliki hak release"];
                $this->fb($fb);
            }

            $this->form_validation->set_rules('so_number', 'SO Number', 'required|trim');
            if($this->form_validation->run() === FALSE){
                $fb = ["statusCode" => 400, "res" => validation_errors()];
                $this->fb($fb);
            }

            $so_number = $this->input->post("so_number");
            $data = [ "release_sign" => $id_user, "release_sign_time" => date("Y-m-d H:i:s") ];
            $this->model->update("data_order","so_number = '$so_number'",$data);
            $fb = ["statusCode" => 200, "res" => "Release berhasil"];
            $this->fb($fb);
        }

        function delete_part_so()
        {
            if(empty($this->id_user)){
                $fb = ["statusCode" => 500, "res" => "Session kosong"];
                $this->fb($fb);
            }

            $id = $this->input->post("id");
            $deleteData = [
                "deleted_date" => date("Y-m-d H:i:s"),
            ];
            $this->model->update("data_part_order","id = '$id'",$deleteData);
            $fb = ["statusCode" => 200, "res" => "Delete Success"];
            $this->fb($fb);
        }
    //DATA SO

    //REMAIN SO
        function get_data_so_management()
        {
            $level = $this->level;
            $id_user = $this->id_user;
            $tipe = $this->input->get("tipe");
            $equal = !empty($this->input->get("other")) ? "!=" : "=";

            if($tipe == "remain"){
                $data = $this->model->gd(
                    "data_order",
                    "*", 
                    $level == "3" 
                        ? "deleted_date IS NULL AND spv_sign_time IS NULL AND mng_sign_time IS NULL AND spv_sign $equal '$id_user'" 
                        : "deleted_date IS NULL AND spv_sign_time IS NOT NULL AND mng_sign_time IS NULL AND mng_sign $equal '$id_user'" ,
                    "result"
                );
            }else if($tipe == "approved"){
                $data = $this->model->gd(
                    "data_order",
                    "*", 
                    $level == "3" 
                        ? "deleted_date IS NULL AND spv_sign_time IS NOT NULL AND spv_sign $equal '$id_user'" 
                        : "deleted_date IS NULL AND mng_sign_time IS NOT NULL AND mng_sign $equal '$id_user'" ,
                    "result"
                );
            }
            $newData = [];
            if (!empty($data)) {
                foreach ($data as $row) {
                    $account = $this->model->gd("account", "name, (SELECT name FROM departement WHERE id=dept) as name_dept", "id = '$row->created_by'", "row");
                    $spv_sign = '-';
                    $spv_sign_time = '-';
                    if(!empty($row->spv_sign)){
                        $data_spv = $this->model->gd("account", "name", "id = '$row->spv_sign'", "row");
                        $spv_sign = $data_spv->name;
                        $spv_sign_time = empty($row->spv_sign_time) ? "" : date("d-M-Y H:i",strtotime($row->spv_sign_time));
                    }
                    
                    $mng_sign = '-';
                    $mng_sign_time = '-';
                    if(!empty($row->mng_sign)){
                        $data_spv = $this->model->gd("account", "name", "id = '$row->mng_sign'", "row");
                        $mng_sign = $data_spv->name;
                        $mng_sign_time = empty($row->mng_sign_time) ? "" : date("d-M-Y H:i",strtotime($row->mng_sign_time));
                    }
                    
                    if(!empty($row->release_sign)){
                        $account = $this->model->gd("account", "name, (SELECT name FROM departement WHERE id=dept) as name_dept", "id = '$row->release_sign'", "row");
                        $release_sign = $account->name;
                    }else{
                        $release_sign = '';
                    }
                    $release_sign_time = !empty($row->release_sign_time) ? date("d-M-Y H:i",strtotime($row->release_sign_time)) : '';

                    $total_part = $this->model->gd("data_part_order","COUNT(*) as total","so_number = '$row->so_number'","row");

                    $newData[] = [
                        "id" => $row->id,
                        "created_time" => empty($row->created_time) ? "" : date("d-M-Y H:i",strtotime($row->created_time)),
                        "creator" => $account->name ?? '',
                        "dept_creator" => $account->name_dept ?? '', // Hindari error jika dept NULL
                        "so_number" => $row->so_number,
                        "shop_code" => $row->shop_code,
                        "spv_sign" => $spv_sign,
                        "spv_sign_time" => $spv_sign_time,
                        "mng_sign" => $mng_sign,
                        "mng_sign_time" => $mng_sign_time,
                        "release_sign" => $release_sign,
                        "release_sign_time" => $release_sign_time,
                        "total_part" => $total_part->total,
                    ];
                }
            }
            $this->fb($newData);
        }

        function approve_so()
        {
            $level = $this->level;
            $id_user = $this->id_user;
            $other = $this->input->get("other");

            if(empty($level)){
                $fb = ["statusCode" => 400, "res" => "Sesi login anda telah berakhir, refresh halaman dan login kembali"];
                $this->fb($fb);
            }

            $this->form_validation->set_rules('so_number', 'SO Number', 'required|trim');
            if($this->form_validation->run() === FALSE){
                $fb = ["statusCode" => 400, "res" => validation_errors()];
                $this->fb($fb);
            }

            $so_number = $this->input->post("so_number");
            $data = [ $level == "3" ? "spv_sign_time" : "mng_sign_time" => date("Y-m-d H:i:s") ];
            if(!empty($other)){
                $data[$level == "3" ? "spv_sign" : "mng_sign"] = $this->id_user;
            }
            $this->model->update("data_order","so_number = '$so_number'",$data);
            $fb = ["statusCode" => 200, "res" => "Approval berhasil"];
            $this->fb($fb);
        }
    //REMAIN SO

    function count_remain_approve()
    {
        $level = $this->level;
        $id_user = $this->id_user;

        if(empty($level)){
            $fb = ["statusCode" => 400, "res" => "Sesi login anda telah berakhir, refresh halaman dan login kembali"];
            $this->fb($fb);
        }

        if($level == "3"){
            $data = $this->model->gd("data_order","COUNT(*) as total","deleted_date IS NULL AND spv_sign_time IS NULL AND mng_sign_time IS NULL AND spv_sign = '$id_user'","row");
        }else{
            $data = $this->model->gd("data_order","COUNT(*) as total","deleted_date IS NULL AND spv_sign_time IS NOT NULL AND mng_sign_time IS NULL AND mng_sign = '$id_user'","row");
        }
        $fb = ["statusCode" => 200, "res" => $data->total];
        $this->fb($fb);
    }

    function count_remain_release()
    {
        $level = $this->level;
        if(empty($level)){
            $fb = ["statusCode" => 400, "res" => "Sesi login anda telah berakhir, refresh halaman dan login kembali"];
            $this->fb($fb);
        }

        $data = $this->model->gd(
            "data_order",
            "COUNT(*) as total", 
            "deleted_date IS NULL AND spv_sign_time IS NOT NULL AND mng_sign_time IS NOT NULL AND release_sign IS NULL",
            "row"
        );
        $fb = ["statusCode" => 200, "res" => $data->total];
        $this->fb($fb);
    }

    function get_shop_code()
    {
        $shop_code = $this->model->gd("departement","shop_code","id !=","result");
        $return_data = [];
        if(!empty($shop_code)){
            foreach ($shop_code as $shop_code) {
                $return_data[] = $shop_code->shop_code;
            }
        }
        $this->fb($return_data);
    }

    function get_pic_shop()
    {
        $pic = $this->model->gd("account","name,id,(SELECT name FROM departement WHERE id=account.dept) as name_dept","level = '2'","result");
        $return_data = [];
        if(!empty($pic)){
            foreach ($pic as $pic) {
                $return_data[] = [
                    "name" => $pic->name,
                    "id" => $pic->id,
                    "name_dept" => $pic->name_dept
                ];
            }
        }
        $this->fb($return_data);
    }

    function printDN()
    {
        ob_start(); 
        $vendor_code = $this->input->get("vendor_code");
        $vendor_alias = $this->input->get("vendor_alias");
        
        $get_data = $this->model->gd("master","*,SUM(order_kbn) as total_kbn","vendor_code = '$vendor_code' GROUP BY order_no","result");

        if(empty($get_data)){
            $fb = ["statusCode" => 404, "res" => "Data Kosong"];
            $this->fb($fb);
        }
        
        $html = '
        <!DOCTYPE html>
        <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>QR Code dan Barcode</title>
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.0.1/css/bootstrap.min.css1" />
                <style type="text/css">
                    @page {
                        size:A4,
                        margin:3rem 1re 3rem 1rem;
                    }
                    @font-face {
                        font-family: "source_sans_proregular";           
                        src: local("Source Sans Pro"), url("fonts/sourcesans/sourcesanspro-regular-webfont.ttf") format("truetype");
                        font-weight: normal;
                        font-style: normal;

                    }        
                    body{
                        font-family: "source_sans_proregular", Calibri,Candara,Segoe,Segoe UI,Optima,Arial,sans-serif;            
                    }
                    .page_break {
                        page-break-before: always;
                        position:relative;
                        min-height:340px;
                    }
                    .page_break:first-of-type {
                        page-break-before: avoid; /* Prevent break before the first element */
                    }
                </style>
            </head>
            <body style="font-size:10px !important;">';
        $dompdf = new Dompdf();
        foreach ($get_data as $get_data) {
            $listOrder = $this->model->gd("master","*","order_no = '".$get_data->order_no."' AND vendor_code = '$vendor_code'","result");

            $orderNo = $get_data->order_no;

            //BUAT BARCODE
            $barcode_generator = new BarcodeGeneratorHTML();
            $barcodeOrderNo = $barcode_generator->getBarcode($orderNo, $barcode_generator::TYPE_CODE_128,2,30);

            // BUAT QR CODE
            // Create an instance of QROptions to set the QR code settings
            $options = new QROptions([
                'eccLevel' => QRCode::ECC_L, // Error correction level (L, M, Q, H)
                'addQuietzone' => false,
                'scale' => 5, // Scale doesn't affect SVG size
                'imageBase64' => true, // Whether to output as a base64 image
            ]);

            // Create a new QRCode instance with the options
            $qrcode = new QRCode($options);
            $qrCodeOrderNo = $qrcode->render($orderNo);

            $ttd = "(___________________)";
            
            $listPart = "";
            if(!empty($listOrder)){
                $no = 1;
                foreach ($listOrder as $listOrder) {
                    $listPart .= '
                    <tr style="text-align:center; vertical-align:middle;">
                        <td>'.$no++.'</td>
                        <td>'.$listOrder->part_no.'</td>
                        <td>'.$listOrder->job_no.'</td>
                        <td>'.$listOrder->part_name.'</td>
                        <td>'.$listOrder->order_pcs.'</td>
                        <td>'.$listOrder->order_kbn.'</td>
                        <td>'.($listOrder->order_pcs * $listOrder->order_kbn).'</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>';
                }
            }

            $html .= '
            <div class="page_break">
                <table style="width:100%">
                    <tr>
                        <td align="center" style="width:70%; vertical-align:middle !important; font-size:30px; font-weight:bold;">Delivery Notes</td>
                        <td style="width:30%">'.$barcodeOrderNo.'<h4 style="margin:0; font-size:17pt;">'.$get_data->order_no.'</h4></td>
                    </tr>
                </table>

                <table style="width:100%">
                    <tr>
                        <td style="width:33%;">
                            <table>
                                <tbody>
                                    <tr>
                                        <td>Vendor Code</td>
                                        <td>:</td>
                                        <td>'.$get_data->vendor_code.'</td>
                                    </tr>
                                    <tr>
                                        <td>Vendor Name</td>
                                        <td>:</td>
                                        <td>'.$get_data->vendor_name.'</td>
                                    </tr>
                                    <tr>
                                        <td>Vendor Site</td>
                                        <td>:</td>
                                        <td>'.$get_data->vendor_site.'</td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                        <td style="width:33%;">
                        </td>
                        <td style="width:33%; vertical-align:top;">
                            <table>
                                <tbody>
                                    <tr>
                                        <td>Transporter</td>
                                        <td>:</td>
                                        <td>'.$get_data->lp.'</td>
                                    </tr>
                                    <tr>
                                        <td>Group Route</td>
                                        <td>:</td>
                                        <td>'.$get_data->route.'</td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </table>
                
                <table style="width:100%">
                    <tr>
                        <td style="width:33%; vertical-align:top;">
                            <h3 style="font-weight:bold; margin:0;">ORDER</h3>
                            <table>
                                <tbody>
                                    <tr>
                                        <td>Date</td>
                                        <td>:</td>
                                        <td>'.date("d-M-Y",strtotime($get_data->order_date)).'</td>
                                    </tr>
                                    <tr>
                                        <td>Lane No</td>
                                        <td>:</td>
                                        <td>'.$get_data->lane.'</td>
                                    </tr>
                                    <tr>
                                        <td>Delivery / Day</td>
                                        <td>:</td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td>Category</td>
                                        <td>:</td>
                                        <td>'.$get_data->part_category.'</td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                        <td style="width:33%;">
                            <h3 style="font-weight:bold; margin:0;">DELIVERY</h3>
                            <table>
                                <tbody>
                                    <tr>
                                        <td>Shop</td>
                                        <td>:</td>
                                        <td>'.$get_data->shop_code.'</td>
                                    </tr>
                                    <tr>
                                        <td>Date</td>
                                        <td>:</td>
                                        <td>'.date("d-M-Y",strtotime($get_data->del_date)).'</td>
                                    </tr>
                                    <tr>
                                        <td>Del. Cycle</td>
                                        <td>:</td>
                                        <td>'.$get_data->del_time.' / '.$get_data->del_cycle.'</td>
                                    </tr>
                                    <tr>
                                        <td>Plant Site</td>
                                        <td>:</td>
                                        <td>'.$get_data->plant_code.'</td>
                                    </tr>
                                    <tr>
                                        <td>Parking No</td>
                                        <td>:</td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                        <td style="width:33%; position:relative;">
                            <table>
                                <tbody>
                                    <tr>
                                        <td>DN No</td>
                                        <td>:</td>
                                        <td style="padding-right:40px;">'.$get_data->order_no.'</td>
                                    </tr>
                                    <tr>
                                        <td>Page</td>
                                        <td>:</td>
                                        <td>1/1</td>
                                    </tr>
                                    <tr>
                                        <td>PO No</td>
                                        <td>:</td>
                                        <td>'.$get_data->po_number.'</td>
                                    </tr>
                                    <tr>
                                        <td>Total KBN</td>
                                        <td>:</td>
                                        <td>'.$get_data->total_kbn.'</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div style="position:absolute; top:10px; right:20px; width:60px; height:60px; padding:8px; border:1px solid;">
                                <img src="'.$qrCodeOrderNo.'" width="100%">
                            </div>
                        </td>
                    </tr>
                </table>
                
                <table style="margin-top:10px; width:100%; border-collapse: collapse;" border="1">
                    <thead>
                        <tr style="text-align:center;">
                            <th rowspan="2">No</th>
                            <th rowspan="2">Material No</th>
                            <th rowspan="2">Job No</th>
                            <th rowspan="2">Material Name</th>
                            <th rowspan="2">Qty/Box</th>
                            <th rowspan="2">Total Kanban</th>
                            <th rowspan="2">Total Qty (PCS)</th>
                            <th colspan="3">Confirmation Check</th>
                            <th rowspan="2">Remark</th>
                        </tr>
                        <tr style="text-align:center; vertical-align:middle;">
                            <th>Vendor</th>
                            <th>Log Partner</th>
                            <th>ADM</th>
                        </tr>
                    </thead>
                    <tbody>
                    '.$listPart.'
                    </tbody>
                </table>

                <table style="width:100%; margin-top:20px; font-size:8pt; position: absolute; bottom:100px; right:0;">
                    <tr>
                        <td style="width:40%"></td>
                        <td style="width:60%;">
                            <table style="width:100%;">
                                <tbody>
                                    <tr style="font-weight:bold; text-align:center;">
                                        <td colspan="2" style="width:33%">SUPPLIER</td>
                                        <td colspan="2" style="width:33%">TRANSPORTER</td>
                                        <td colspan="2" style="width:33%">PT. ADM</td>
                                    </tr>
                                    <tr style="text-align:center;">
                                        <td>APPROVED</td>
                                        <td>PREPARED</td>
                                        <td>APPROVED</td>
                                        <td>PREPARED</td>
                                        <td>APPROVED</td>
                                        <td>PREPARED</td>
                                    </tr>
                                    <tr style="text-align:center; vertical-align:bottom;">
                                        <td style="height:60px">'.$ttd.'</td>
                                        <td>'.$ttd.'</td>
                                        <td>'.$ttd.'</td>
                                        <td>'.$ttd.'</td>
                                        <td>'.$ttd.'</td>
                                        <td>'.$ttd.'</td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
            ';
        }
        $html .= '
            </body>
        </html>';
        // DomPDF Operations
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        ob_end_clean();
        $dompdf->get_canvas()->get_cpdf()->setEncryption('adm');
        $dompdf->stream($vendor_alias." (".$vendor_code.").pdf", ["Attachment" => false]);
        $updateDate["download_dn"] = "1";
        $data = $this->model->update("master","vendor_code = '$vendor_code'",$updateDate);
        if(empty($data)){
            $fb = ["statusCode" => 401, "res" => "Data kosong"];
            $this->fb($fb);
        }
        exit();
    }

    function get_data_graph()
    {
        $month = ($this->input->post("month")+1);
        $year = $this->input->post("year");
        $type = $this->input->post("type");
        $pic = $this->input->post("pic");
        $periode = $year."-".sprintf("%02d",$month)."-";
        
        $pic_filter = $pic == "all" ? "" : "AND pic = '$pic'";
        $data_bar = [];
        for ($i=1; $i <=31 ; $i++) {
            $query = "deleted_date IS NULL AND created_time LIKE '%".$periode.sprintf("%02d",$i)."%' AND tipe = '$type' $pic_filter";
            $data = $this->model->gd("data_order","COUNT(*) as total",$query,"row");
            $data_bar[] = [
                "name" => "$i",
                "value" => intval($data->total),
                "ket" => $query,
            ];
        }

        $reject = $this->model->gd("data_order","COUNT(*) as total","deleted_date IS NULL AND reject_by IS NOT NULL AND MONTH(created_time) = '$month' AND YEAR(created_time) = '$year' AND tipe = '$type'","row");
        $approve = $this->model->gd("data_order","COUNT(*) as total","deleted_date IS NULL AND spv_sign IS NOT NULL AND release_sign IS NULL AND MONTH(created_time) = '$month' AND YEAR(created_time) = '$year' AND tipe = '$type'","row");
        $release = $this->model->gd("data_order","COUNT(*) as total","deleted_date IS NULL AND release_sign IS NOT NULL AND MONTH(created_time) = '$month' AND YEAR(created_time) = '$year' AND tipe = '$type'","row");
        
        $data_pie = [
            [
                "name" => "Reject",
                "value" => intval($reject->total),
                "fill" => "#f55656"
            ],
            [
                "name" => "Approve",
                "value" => intval($approve->total),
                "fill" => "#5693f5"
            ],
            [
                "name" => "Release",
                "value" => intval($release->total),
                "fill" => "#56f5b3"
            ]
        ];

        $fb = ["statusCode" => 200, "res" => ["bar" => $data_bar, "pie" => $data_pie]];
        $this->fb($fb);
    }
}
