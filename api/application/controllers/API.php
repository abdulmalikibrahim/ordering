<?php
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
require_once FCPATH . 'vendor/autoload.php';

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
                    'std_qty' => $sheet->getCell('H' . $row)->getValue(),
                    'price' => $sheet->getCell('I' . $row)->getValue(),
                    'remark' => $sheet->getCell('J' . $row)->getValue()
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
        $validation = $this->model->gd("account","*,(SELECT name FROM departement WHERE id = dept) AS dept, dept as dept_id","username = '$username'","row");
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
            "level_name" => $level_name,
            "username" => $validation->username,
            "dept_id" => $validation->dept_id,
            "email" => $validation->email,
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
            "username" => $this->username,
            "level_name" => $this->level_name, 
            "dept_name" => $this->dept_name,
            "dept_id" => $this->dept_id,
            "email" => $this->email,
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
                    $dept = $this->model->gd("departement", "name", "id IN (".str_replace(["[","]"],"",$row->dept).")", "result_array");
					$dataDept = array_column($dept, 'name');
					$dataDept = implode(",",$dataDept);
                    
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
                        "email" => $row->email,
                        "dept" => $dataDept ?? 'Unknown', // Hindari error jika dept NULL
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
                ->set_rules("dept","Departement","required|trim")
                ->set_rules("level","Level","required|trim|integer")
                ->set_rules("email","Email","required|trim|valid_email")
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
			$deptInput = json_decode($this->input->post('dept'),TRUE);
			if (!is_array($deptInput)) {
				$fb = ["statusCode" => 400, "res" => "Departemen wajib diisi minimal 1".$deptInput];
				$this->fb($fb);
			}
			$dept = json_encode($deptInput);

            $email = $this->input->post('email');
            $level = $this->input->post('level');
            $id_update = $this->input->post('id_update');

            $dataSubmit = [
                "name" => $name,
                "username" => $username,
                "dept" => $dept,
                "level" => $level,
                "email" => $email,
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
            header("Content-Type: application/json");
            $tipe = $this->input->get("tipe");
            $level = $this->level;
            if($tipe == "release_so"){
                $data = $this->model->gd(
                    "data_order",
                    "*", 
                    $level == "1" 
                    ? "deleted_date IS NULL AND release_sign != '' AND release_sign_time != '' ORDER BY created_time DESC" 
                    : "deleted_date IS NULL AND pic = '".$this->id_user."' AND release_sign != '' AND release_sign_time != '' ORDER BY created_time DESC",
                    "result"
                );
            }else if($tipe == "delete_so"){
                $data = $this->model->gd(
                    "data_order",
                    "*", 
                    $level == "1" 
                    ? "deleted_date IS NOT NULL AND id != '' ORDER BY created_time DESC" 
                    : "deleted_date IS NOT NULL AND pic = '".$this->id_user."' AND id != '' ORDER BY created_time DESC",
                    "result"
                );
            }else if($tipe == "need_release"){
                $data = $this->model->gd(
                    "data_order",
                    "*", 
                    "deleted_date IS NULL AND spv_sign_time IS NOT NULL AND mng_sign_time IS NOT NULL AND reject_date IS NULL AND release_sign IS NULL AND id != '' ORDER BY created_time DESC",
                    "result"
                );
            }else if($tipe == "reject_so"){
                $data = $this->model->gd(
                    "data_order",
                    "*", 
                    $level == "1" 
                    ? "deleted_date IS NULL AND reject_by IS NOT NULL AND id != '' ORDER BY created_time DESC" 
                    : "deleted_date IS NULL AND reject_by IS NOT NULL AND pic = '".$this->id_user."' AND id != '' ORDER BY created_time DESC",
                    "result"
                );
            }else{
                $data = $this->model->gd(
                    "data_order",
                    "*", 
                    $level == "1" 
                    ? "deleted_date IS NULL AND tipe = '$tipe' ORDER BY created_time DESC" 
                    : "deleted_date IS NULL AND pic = '".$this->id_user."' AND tipe='$tipe' ORDER BY created_time DESC",
                    "result"
                );
            }
            $newData = [];
            if (!empty($data)) {
                foreach ($data as $row) {
                    $account = $this->model->gd("account", "name, (SELECT name FROM departement WHERE id=dept) as name_dept", "id = '$row->created_by'", "row");
                    $spv_sign = '-';
                    $spv_sign_time = '-';
                    $status_so = '';
                    $reject_reason = '';
                    $reject_by = '';
                    $reject_date = '';
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

                    if(!empty($row->reject_date)){
                        $account_reject = $this->model->gd("account", "name,level", "id = '$row->reject_by'", "row");
                        $reject_by = $account_reject->name;
                        $reject_date = date("d-M-Y",strtotime($row->reject_date));
                        if($account_reject->level == "3"){
                            $spv_sign = $reject_by;
                            $spv_sign_time = "reject, ".$reject_date;
                        }else if($account_reject->level == "4"){
                            $mng_sign = $reject_by;
                            $mng_sign_time = "reject, ".$reject_date;
                        }
                        $status_so = 'reject';
                        $reject_reason = $row->reject_reason;
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
                        "detailing_part" => $detailing_part,
                        "status_so" => $status_so,
                        "reject_by" => $reject_by,
                        "reject_date" => $reject_date,
                        "reject_reason" => $reject_reason,
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
            $grand_total = 0;
            $so_number = $this->input->get("so_number");
            $data = $this->model->join3_data("data_part_order a","master b","data_order c","a.part_number=b.part_number AND a.vendor_code=b.vendor_code","a.so_number=c.so_number","a.*,b.part_name,b.vendor_site_alias,b.job_no,b.std_qty,(a.qty_kanban* b.std_qty*b.price) as total_price,c.spv_sign_time,c.mng_sign_time,c.release_sign_time,c.reject_date,c.reject_reason", "a.deleted_date IS NULL AND a.so_number = '$so_number'" ,"result");
            $newData = [];
            if (!empty($data)) {
                foreach ($data as $row) {
                    $newData["data"][] = [
                        "id" => $row->id,
                        "so_number" => $row->so_number,
                        "part_number" => $row->part_number,
                        "part_name" => $row->part_name,
                        "vendor_code" => $row->vendor_code,
                        "vendor_name" => $row->vendor_site_alias,
                        "total_qty" => $row->std_qty*$row->qty_kanban,
                        "total_price" => $row->total_price,
                        "qty_kanban" => $row->qty_kanban,
                        "job_no" => $row->job_no,
                    ];
                    $grand_total += $row->total_price;
                }

                $status_so = '';
                $reason_reject = '';
                if(empty($row->spv_sign_time)){
                    $status_so = 'Waiting SPV';
                }
                if(!empty($row->spv_sign_time)){
                    $status_so = 'Waiting MNG';
                }
                if(!empty($row->mng_sign_time)){
                    $status_so = 'Waiting Release';
                }
                if(!empty($row->release_sign_time)){
                    $status_so = 'Release';
                }
                if(!empty($row->reject_date)){
                    $status_so = 'Reject';
                    $reason_reject = $row->reject_reason;
                }
                $newData["tgl_delivery"] = empty($row->tgl_delivery) ? "" : date("d-M-Y",strtotime($row->tgl_delivery));
                $newData["shop_code"] = $row->shop_code;
                $newData["grandTotal"] = $grand_total;
                $newData["status_so"] = $status_so;
                $newData["reason_reject"] = $reason_reject;
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
            $account = $this->model->gd("account","dept,(SELECT shop_code FROM departement WHERE id=account.dept) as shop_code","id = '$pic'","row");
			$search_dept = str_replace(["[","]"],"",$account->dept);
            $shop_code = $this->model->gd("departement","shop_code","id IN (".$search_dept.")","row")->shop_code ?? null;
            $spv_sign = $this->model->gd("account","id,email,name","status = '1' AND level = '3' AND dept LIKE '%".$search_dept."%'","row");
            $mng_sign = $this->model->gd("account","id,email,name","status = '1' AND level = '4' AND dept LIKE '%".$search_dept."%'","row");

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
                        'shop_code' => $shop_code,
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
                "shop_code" => $shop_code,
            ];
            $this->model->insert("data_order",$data_input);
            $insert = $this->model->insert_batch("data_part_order",$data);
            if($insert){
                $spv_sign = $this->model->gd("account","id,email,name","status = '1' AND level = '3' AND dept LIKE '%".$search_dept."%'","result");
                if(empty($spv_sign)){
                    $fb = ["statusCode" => 500, "res" => "Upload gagal karena PIC Departement belum memiliki account SPV"];
                    $this->fb($fb);
                }

                foreach ($spv_sign as $spv_sign) {
                    //KIRIM EMAIL
                    $detail_so = $this->get_detail_so("array",$so_number);
                    $to = $spv_sign->email;
                    $subject = "Confirmation SO Number $so_number";
                    $table = $this->table_data_so($detail_so);
                    $id_approve = $so_number."#".$spv_sign->id."#3";
                    $body = '<h4>Dear '.$spv_sign->name.",</h4>
                    Terlampir adalah SO Number yang telah di input oleh ".$this->name.'<br>
                    SO Number : <b>'.$so_number.'</b>
                    <br>
                    <br>
                    '.$table.'
                    <br>
                    <br>
                    Mohon untuk bisa konfirmasi SO Number ini dengan login melalui link di bawah ini.
                    <br>
                    <a href="'.FRONTEND_URL.'">Login Ordering Apps</a>
                    <br>
                    Atau anda bisa approve melalui link dibawah ini.
                    <br>
                    <a href="'.base_url("direct_approve?p=".urlencode(encrypt($id_approve))).'">Approve SO</a>
                    <br>
                    <br>
                    Terimakasih atas kerjasamanya.
                    <br>
                    <i>Ordering Apps PCD KAP</i>';
                    $this->exec_send_email($to,$subject,$body);
                }
                
                $fb = ["statusCode" => 200, "res" => "Upload success", "email" => ["to" => $to, "subject" => $subject, "body" => $body]];
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
            
            //KIRIM EMAIL
            $detail_so = $this->get_detail_so("array",$so_number);
            $detail_next = $this->model->gd("account","id,email,name","id = ".$detail_so["data_so"]->pic,"row");

            if(empty($detail_next)){
                $fb = ["statusCode" => 500, "res" => "Tidak ada akun yang bisa dihubungi untuk level selanjutnya"];
                $this->fb($fb);
            }
            
            $to = $detail_next->email;
            $subject = "Release SO Number $so_number";
            $table = $this->table_data_so($detail_so);
            $body = '<h4>Dear '.$detail_next->name.',</h4>
            SO Number '.$so_number.' telah di release oleh <b>'.$this->name.'</b>
            <br>
            <br>
            '.$table.'
            <br>
            <br>
            Anda bisa login melalui link di bawah ini.
            <br>
            <a href="'.FRONTEND_URL.'">Login Ordering Apps</a>
            <br>
            <br>
            Terimakasih atas kerjasamanya.
            <br>
            <i>Ordering Apps PCD KAP</i>';
            $this->exec_send_email($to,$subject,$body);

            $data = [ "release_sign" => $id_user, "release_sign_time" => date("Y-m-d H:i:s") ];
            $this->model->update("data_order","so_number = '$so_number'",$data);
            $fb = ["statusCode" => 200, "res" => "Release berhasil", "email" => ["to" => $to, "subject" => $subject, "body" => $body]];
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
                        ? "deleted_date IS NULL AND reject_by IS NULL AND spv_sign_time IS NULL AND mng_sign_time IS NULL AND spv_sign $equal '$id_user'" 
                        : "deleted_date IS NULL AND reject_by IS NULL AND spv_sign_time IS NOT NULL AND mng_sign_time IS NULL AND mng_sign $equal '$id_user'" ,
                    "result"
                );
            }else if($tipe == "approved"){
                $data = $this->model->gd(
                    "data_order",
                    "*", 
                    $level == "3" 
                        ? "deleted_date IS NULL AND reject_by IS NULL AND spv_sign_time IS NOT NULL AND spv_sign $equal '$id_user'" 
                        : "deleted_date IS NULL AND reject_by IS NULL AND mng_sign_time IS NOT NULL AND mng_sign $equal '$id_user'" ,
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

            //KIRIM EMAIL
            $detail_so = $this->get_detail_so("array",$so_number);
			$mng_sign = $detail_so["data_so"]->mng_sign;
			$id_account = $level == "3" ? $mng_sign : "check";
            $level_next_sign = $level == "3" ? "4" : "1";

			if($id_account == "check"){
				$detail_next = $this->model->gd("account","id,email,name","status = '1' AND level = '1'","result"); //KHUSUS UNTUK LEVEL MNG APPROVE DAN LARI KE ADMIN
			}else{
				$detail_next = $this->model->gd("account","id,email,name","status = '1' AND id = '$id_account'","result");
			}

            if(empty($detail_next)){
                $fb = ["statusCode" => 500, "res" => "Tidak ada akun yang bisa dihubungi untuk level selanjutnya"];
                $this->fb($fb);
            }
			
            foreach ($detail_next as $detail_next) {
                $to = $detail_next->email;
                $subject = "Confirmation SO Number $so_number";
                $table = $this->table_data_so($detail_so);
                $id_approve = $so_number."#".$detail_next->id."#".$level_next_sign;
                $body = '<h4>Dear '.$detail_next->name.',</h4>
                Terlampir adalah SO Number yang telah di input oleh <b>'.$detail_so["pic"].'</b> dan di setujui oleh <b>'.$this->name.'</b><br>
                SO Number : <b>'.$so_number.'</b>
                <br>
                <br>
                '.$table.'
                <br>
                <br>
                Mohon untuk bisa konfirmasi SO Number ini dengan login melalui link di bawah ini.
                <br>
                <a href="'.FRONTEND_URL.'">Login Ordering Apps</a>
                <br>
                Atau anda bisa approve melalui link dibawah ini.
                <br>
                <a href="'.base_url("direct_approve?p=".urlencode(encrypt($id_approve))).'">Approve SO</a>
                <br>
                <br>
                Terimakasih atas kerjasamanya.
                <br>
                <i>Ordering Apps PCD KAP</i>';
                $this->exec_send_email($to,$subject,$body);
            }

            $data = [ $level == "3" ? "spv_sign_time" : "mng_sign_time" => date("Y-m-d H:i:s") ];
            if(!empty($other)){
                $data[$level == "3" ? "spv_sign" : "mng_sign"] = $this->id_user;
            }
            $this->model->update("data_order","so_number = '$so_number'",$data);
            $fb = ["statusCode" => 200, "res" => "Approval berhasil", "email" => ["to" => $to, "subject" => $subject, "body" => $body]];
            $this->fb($fb);
        }

        function count_remain_approve()
        {
            $level = $this->level;
            $id_user = $this->id_user;
    
            if(empty($level)){
                $fb = ["statusCode" => 400, "res" => "Sesi login anda telah berakhir, refresh halaman dan login kembali"];
                $this->fb($fb);
            }
    
            if($level == "3"){
                $data = $this->model->gd("data_order","COUNT(*) as total","deleted_date IS NULL AND reject_by IS NULL AND spv_sign_time IS NULL AND mng_sign_time IS NULL AND spv_sign = '$id_user'","row");
            }else{
                $data = $this->model->gd("data_order","COUNT(*) as total","deleted_date IS NULL AND reject_by IS NULL AND spv_sign_time IS NOT NULL AND mng_sign_time IS NULL AND mng_sign = '$id_user'","row");
            }
            $fb = ["statusCode" => 200, "res" => $data->total];
            $this->fb($fb);
        }
    //REMAIN SO

    private function table_data_so($detail_so)
    {
        $table = '';
        if(!empty($detail_so["detail_part_so"])){
            $detail_part_so = $detail_so["detail_part_so"];
            $table .= '
            <table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse;">
                <tr>
                    <th style="text-align: center;">No</th>
                    <th style="text-align: center;">Job No</th>
                    <th style="text-align: center;">Part Number</th>
                    <th style="text-align: center;">Part Name</th>
                    <th style="text-align: center;">Supplier</th>
                    <th style="text-align: center;">Price/Pcs</th>
                    <th style="text-align: center;">Qty/Kbn</th>
                    <th style="text-align: center;">Req. Kbn</th>
                    <th style="text-align: center;">Req. Pcs</th>
                    <th style="text-align: center;">Total Price</th>
                    <th style="text-align: center;">Remarks</th>
                </tr>';
            $no = 1;
            foreach ($detail_part_so as $data) {
                $table .= '
                <tr>
                    <td style="text-align: center;">'.$no++.'</td>
                    <td style="text-align: center;">'.$data->job_no.'</td>
                    <td style="text-align: center;">'.$data->part_number.'</td>
                    <td style="text-align: center;">'.$data->part_name.'</td>
                    <td style="text-align: center;">'.$data->vendor_name.'</td>
                    <td style="text-align: center;">'.number_format($data->price,0,"",",").'</td>
                    <td style="text-align: center;">'.number_format($data->std_qty,0,"",",").'</td>
                    <td style="text-align: center;">'.number_format($data->qty_kanban,0,"",",").'</td>
                    <td style="text-align: center;">'.number_format($data->qty_packing,0,"",",").'</td>
                    <td style="text-align: center;">'.number_format($data->total_price,0,"",",").'</td>
                    <td style="text-align: center;">'.$data->remark_part.'</td>
                </tr>';
            }
            
            $table .= '
            <tr>
                <td style="text-align: center;" colspan="7"><b>Total Part Release Order</b></td>
                <td style="text-align: center;"><b>'.number_format($detail_so["grand_total_req_kbn"],0,"",",").'</b></td>
                <td style="text-align: center;"><b>'.number_format($detail_so["grand_total_req_pcs"],0,"",",").'</b></td>
                <td style="text-align: center;"><b>'.number_format($detail_so["grand_total_price"],0,"",",").'</b></td>
            </tr>';
            $table .= '</table>';
        }
        return $table;
    }

    function direct_approve()
    {
        $this->load->view("admin/direct_approve");
    }

    function exec_direct_approve()
    {
        $p = $this->input->get("p");
        if(empty($p)){
            $fb = ["statusCode" => 400, "res" => "Parameter tidak ditemukan"];
            $this->fb($fb);
        }
        $decrypt = decrypt($p);
        if(empty($decrypt)){
            $fb = ["statusCode" => 400, "res" => "Parameter tidak valid"];
            $this->fb($fb);
        }
        $data = explode("#", $decrypt);
        $so_number = $data[0];
        $id_user = $data[1];
        $level = $data[2];

        //CHECK STATUS SO
        $check_so = $this->model->gd("data_order","spv_sign,spv_sign_time,mng_sign_time,mng_sign,release_sign_time,release_sign,reject_date,reject_reason,reject_by","so_number = '$so_number'","row");
        if(empty($check_so)){
            $fb = ["statusCode" => 400, "res" => "SO Number tidak ditemukan"];
            $this->fb($fb);
        }

        if(!empty($check_so->reject_date)){
            $account_reject = $this->model->gd("account","name","id = '$check_so->reject_by'","row");
            $fb = ["statusCode" => 400, "res" => "SO Number ini sudah di reject oleh ".$account_reject->name." pada ".date("d-M-Y H:i",strtotime($check_so->reject_date))." dengan alasan :<br>".$check_so->reject_reason];
            $this->fb($fb);
        }

        if($level == "4" || $level == "1"){
            //CHECK APAKAH SUDAH DI APPROVE OLEH SPV
            if(empty($check_so->spv_sign_time)){
                $fb = ["statusCode" => 400, "res" => "SO Number ini belum di approve oleh SPV"];
                $this->fb($fb);
            }
        }
        
        $so_already_approve = "no";
        $id_approve = "";
        if(!empty($check_so->spv_sign_time) && $level == "3"){ //SPV
            $id_approve = $check_so->spv_sign;
            $so_already_approve = "yes";
        }

        if(!empty($check_so->mng_sign_time) && $level == "4"){ //MNG
            $id_approve = $check_so->mng_sign;
            $so_already_approve = "yes";
        }
        
        if(!empty($check_so->release_sign_time) && $level == "1"){ //ADMIN
            $id_approve = $check_so->release_sign;
            $so_already_approve = "yes";
        }
        
        if($so_already_approve == "yes"){
            $account_approve = $this->model->gd("account","name","id = '$id_approve'","row");
            $fb = ["statusCode" => 400, "res" => "SO Number ini sudah di ".($level == "1" ? "release" : "approve")." oleh ".(!empty($account_approve->name) ? $account_approve->name : "Unknown Database")." pada ".date("d-M-Y H:i",strtotime($check_so->mng_sign_time))];
            $this->fb($fb);
        }

        if($level == "3"){
            $data = [ "spv_sign" => $id_user, "spv_sign_time" => date("Y-m-d H:i:s") ];
        }else if($level == "4"){
            $data = [ "mng_sign" => $id_user, "mng_sign_time" => date("Y-m-d H:i:s") ];
        }else if($level == "1"){
            $data = [ "release_sign" => $id_user, "release_sign_time" => date("Y-m-d H:i:s") ];
        }

        //KIRIM EMAIL
        $detail_so = $this->get_detail_so("array",$so_number);
		$mng_sign = $detail_so["data_so"]->mng_sign;
		$id_account = $level == "3" ? $mng_sign : "check";
		$level_next_sign = $level == "3" ? "4" : "1";

		if($id_account == "check"){
			$detail_next = $this->model->gd("account","id,email,name","status = '1' AND level = '1'","result"); //KHUSUS UNTUK LEVEL MNG APPROVE DAN LARI KE ADMIN
		}else{
			$detail_next = $this->model->gd("account","id,email,name","status = '1' AND id = '$id_account'","result");
		}

        if(empty($detail_next)){
            $fb = ["statusCode" => 500, "res" => "Tidak ada akun yang bisa dihubungi untuk level selanjutnya"];
            $this->fb($fb);
        }

        $name_approve = $this->model->gd("account","name","id = '$id_user'","row");
        if($level_next_sign == "pic"){
            $to = $detail_next->email;
            $subject = "Release SO Number $so_number";
            $table = $this->table_data_so($detail_so);
            $body = '<h4>Dear '.$detail_next->name.',</h4>
            SO Number '.$so_number.' telah di release oleh <b>'.$name_approve->name.'</b>
            <br>
            <br>
            '.$table.'
            <br>
            <br>
            Anda bisa login melalui link di bawah ini.
            <br>
            <a href="'.FRONTEND_URL.'">Login Ordering Apps</a>
            <br>
            <br>
            Terimakasih atas kerjasamanya.
            <br>
            <i>Ordering Apps PCD KAP</i>';
            $this->exec_send_email($to,$subject,$body);
        }else{
            foreach ($detail_next as $detail_next) {
                $to = $detail_next->email;
                $subject = "Confirmation SO Number $so_number";
                $table = $this->table_data_so($detail_so);
                $id_approve = $so_number."#".$detail_next->id."#".$level_next_sign;
                $body = '<h4>Dear '.$detail_next->name.",</h4>
                Terlampir adalah SO Number yang telah di input oleh <b>".$detail_so["pic"]."</b> dan di setujui oleh <b>".$name_approve->name.'</b><br>
                SO Number : <b>'.$so_number.'</b>
                <br>
                <br>
                '.$table.'
                <br>
                <br>
                Mohon untuk bisa konfirmasi SO Number ini dengan login melalui link di bawah ini.
                <br>
                <a href="'.FRONTEND_URL.'">Login Ordering Apps</a>
                <br>
                Atau anda bisa approve melalui link dibawah ini.
                <br>
                <a href="'.base_url("direct_approve?p=".urlencode(encrypt($id_approve))).'">Approve SO</a>
                <br>
                <br>
                Terimakasih atas kerjasamanya.
                <br>
                <i>Ordering Apps PCD KAP</i>';
                $this->exec_send_email($to,$subject,$body);
            }
        }

        $this->model->update("data_order","so_number = '$so_number'",$data);
        $fb = ["statusCode" => 200, "res" => "SO Number $so_number berhasil di ".($level != "1" ? "approve" : "release")];
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
            "deleted_date IS NULL AND reject_by IS NULL AND spv_sign_time IS NOT NULL AND mng_sign_time IS NOT NULL AND reject_date IS NULL AND release_sign IS NULL",
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
        $pic = $this->model->gd("account","name,id,dept","level = '2'","result");
        $return_data = [];
        if(!empty($pic)){
            foreach ($pic as $pic) {
				$dept = $this->model->gd("departement", "name", "id IN (".str_replace(["[","]"],"",$pic->dept).")", "result_array");
				$dataDept = array_column($dept, 'name');
				$dataDept = implode(",",$dataDept);

                $return_data[] = [
                    "name" => $pic->name,
                    "id" => $pic->id,
                    "name_dept" => $dataDept
                ];
            }
        }
        $this->fb($return_data);
    }

    function get_data_graph()
    {
        $month = ($this->input->post("month")+1);
        $year = $this->input->post("year");
        $type = $this->input->post("type");
        $pic = $this->input->post("pic");
        $periode = $year."-".sprintf("%02d",$month)."-";
        
        $pic_filter = $pic == "all" ? "" : "AND pic = '$pic'";
        $pic_filter_price = $pic == "all" ? "" : "AND a.pic = '$pic'";
        $data_bar = [];
        $data_price = [];
        $total_price = 0;
        
        $query = "deleted_date IS NULL AND reject_date IS NULL AND created_time LIKE '%".$periode."%' AND tipe = '$type' $pic_filter";
        $data = $this->model->gd(
            "data_order",
            "COUNT(CASE WHEN spv_sign_time IS NULL THEN 1 END) AS spv_pending,
            COUNT(CASE WHEN mng_sign_time IS NULL THEN 1 END) AS mng_pending,
            COUNT(CASE WHEN release_sign_time IS NULL THEN 1 END) AS release_pending",
            $query,
            "row"
        );

        $data_status = [
            [
                "name" => "Supervisor",
                "value" => intval($data->spv_pending),
            ],
            [
                "name" => "Manager",
                "value" => intval($data->mng_pending),
            ],
            [
                "name" => "Release",
                "value" => intval($data->release_pending),
            ]
        ];

        for ($i=1; $i <=31 ; $i++) {
            $query = "deleted_date IS NULL AND created_time LIKE '%".$periode.sprintf("%02d",$i)."%' AND tipe = '$type' $pic_filter";
            $data = $this->model->gd("data_order","COUNT(*) as total",$query,"row");
            
            $query_price = "deleted_date IS NULL AND created_time LIKE '%".$periode.sprintf("%02d",$i)."%' AND tipe = '$type' $pic_filter";
            $get_price = $this->model->join3_data(
                "data_part_order b",
                "data_order a",
                "master c",
                "b.so_number=a.so_number",
                "b.part_number=c.part_number AND b.vendor_code=c.vendor_code",
                "SUM(b.qty_kanban * c.std_qty * c.price) as total_price",
                "a.deleted_date IS NULL AND a.reject_date IS NULL AND a.created_time LIKE '%".$periode.sprintf("%02d",$i)."%' AND a.tipe = '$type' $pic_filter GROUP BY b.so_number",
                "row"
            );
            
            
            $data_bar[] = [
                "name" => "$i",
                "value" => intval($data->total),
            ];
            
            $total_price += !empty($get_price->total_price) ? intval($get_price->total_price) : 0;
            $data_price[] = [
                "name" => "$i",
                "value" => $total_price,
            ];
        }

        $reject = $this->model->gd("data_order","COUNT(*) as total","deleted_date IS NULL AND reject_by IS NOT NULL AND MONTH(created_time) = '$month' AND YEAR(created_time) = '$year' AND tipe = '$type' $pic_filter","row");
        $approve = $this->model->gd("data_order","COUNT(*) as total","deleted_date IS NULL AND reject_by IS NULL AND spv_sign IS NOT NULL AND release_sign IS NULL AND MONTH(created_time) = '$month' AND YEAR(created_time) = '$year' AND tipe = '$type' $pic_filter","row");
        $release = $this->model->gd("data_order","COUNT(*) as total","deleted_date IS NULL AND release_sign IS NOT NULL AND MONTH(created_time) = '$month' AND YEAR(created_time) = '$year' AND tipe = '$type' $pic_filter","row");
        
        $data_pie = [
            [
                "name" => "Reject",
                "value" => intval($reject->total),
                "fill" => "#f55656"
            ],
            [
                "name" => "Appproval",
                "value" => intval($approve->total),
                "fill" => "#5693f5"
            ],
            [
                "name" => "Release",
                "value" => intval($release->total),
                "fill" => "#56f5b3"
            ]
        ];

        $fb = ["statusCode" => 200, "res" => ["bar" => $data_bar, "price" => $data_price, "pie" => $data_pie, "status" => $data_status]];
        $this->fb($fb);
    }

    function cancel_approve_so()
    {
        $this->form_validation
            ->set_rules("id_account","Account","required|trim")
            ->set_rules("so_number","SO Number","required|trim")
            ->set_rules("keterangan","Katerangan","required|trim");
        
        if($this->form_validation->run() === FALSE){
            $fb = ["statusCode" => 500, "res" => validation_errors()];
            $this->fb($fb);
        }

        $id_account = $this->input->post("id_account");
        $so_number = $this->input->post("so_number");
        $keterangan = htmlentities($this->input->post("keterangan"));
        
        $data_input = [
            "reject_by" => $id_account,
            "reject_date" => date("Y-m-d"),
            "reject_reason" => $keterangan
        ];
        
        $detail_so = $this->get_detail_so("array",$so_number);
        $detail_next = $this->model->gd("account","id,email,name","id = ".$detail_so["data_so"]->pic,"row");
        $to = $detail_next->email;
        $subject = "Cancel SO Number $so_number";
        $table = $this->table_data_so($detail_so);
        $body = '<h4>Dear '.$detail_next->name.',</h4>
        '.$so_number.' telah di cancel oleh <b>'.$this->name.'</b>
        <br>
        Alasan cancel :
        <br>
        <b>'.str_replace("\n","<br>",$keterangan).'</b>
        <br>
        <br>
        '.$table.'
        <br>
        <br>
        Anda bisa login melalui link di bawah ini.
        <br>
        <a href="'.FRONTEND_URL.'">Login Ordering Apps</a>
        <br><br>
        Terimakasih atas kerjasamanya.
        <br>
        <i>Ordering Apps PCD KAP</i>';
        $this->exec_send_email($to,$subject,$body);

        $this->model->update("data_order","so_number = '$so_number'",$data_input);
        $fb = ["statusCode" => 200, "res" => "Proses berhasil di lakukan"];
        $this->fb($fb);
    }

    function export_master()
    {
        $data_master = $this->model->gd("master","*","id !=","result");

        // Buat objek Spreadsheet baru
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Isi data contoh
        $sheet->setCellValue('A1', 'Part Number');
        $sheet->setCellValue('B1', 'Part Name');
        $sheet->setCellValue('C1', 'Vendor Code');
        $sheet->setCellValue('D1', 'Vendor Name');
        $sheet->setCellValue('E1', 'Vendor Site');
        $sheet->setCellValue('F1', 'Vendor Site Alias');
        $sheet->setCellValue('G1', 'Job No');
        $sheet->setCellValue('H1', 'Qty Packing');
        $sheet->setCellValue('I1', 'Price');
        $sheet->setCellValue('J1', 'Remark');
        
        $row = 2;
        foreach ($data_master as $data) {
            $sheet->setCellValue('A' . $row, $data->part_number);
            $sheet->setCellValue('B' . $row, $data->part_name);
            $sheet->setCellValue('C' . $row, $data->vendor_code);
            $sheet->setCellValue('D' . $row, $data->vendor_name);
            $sheet->setCellValue('E' . $row, $data->vendor_site);
            $sheet->setCellValue('F' . $row, $data->vendor_site_alias);
            $sheet->setCellValue('G' . $row, $data->job_no);
            $sheet->setCellValue('H' . $row, $data->std_qty);
            $sheet->setCellValue('I' . $row, $data->price);
            $sheet->setCellValue('J' . $row, $data->remark);
            $row++;
        }

        // STYLE HEADER (baris 1)
        $headerRange = 'A1:J1';
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFC000');

        // STYLE kolom A (selain header) jadi center
        $lastRow = $row - 1;
        $sheet->getStyle("A2:J$lastRow")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("B2:B$lastRow")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("D2:D$lastRow")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // BORDER di semua sel terisi (A1:C lastRow)
        $allRange = "A1:J$lastRow";
        $sheet->getStyle($allRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // AUTO WIDTH kolom berdasarkan isi
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Header agar browser mendownload file Excel
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Data_Master.xlsx"');
        header('Cache-Control: max-age=0');

        // Buat writer dan output ke php://output supaya langsung terdownload
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    function export_detail_part()
    {
        header('Content-Type: application/json');
        $so = $this->input->get("so");
        $data_part = $this->model->join_data(
            "data_part_order a",
            "master b",
            "a.part_number=b.part_number AND a.vendor_code=b.vendor_code",
            "a.shop_code,a.tgl_delivery,a.qty_kanban,a.remarks as remark_part,b.*",
            "a.deleted_date IS NULL AND a.so_number = '$so'",
            "result"
        );

        // Buat objek Spreadsheet baru
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Isi data contoh
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Shop Code');
        $sheet->setCellValue('C1', 'Tgl Delivery');
        $sheet->setCellValue('D1', 'Part Number');
        $sheet->setCellValue('E1', 'Part Name');
        $sheet->setCellValue('F1', 'Vendor Code');
        $sheet->setCellValue('G1', 'Vendor Name');
        $sheet->setCellValue('H1', 'Job No');
        $sheet->setCellValue('I1', 'Qty');
        $sheet->setCellValue('J1', 'Qty Packing');
        $sheet->setCellValue('K1', 'Price');
        $sheet->setCellValue('L1', 'Remark');
        
        $row = 2;
        $total_price = 0;
        foreach ($data_part as $data) {
            $sheet->setCellValue('A' . $row, $row - 1);
            $sheet->setCellValue('B' . $row, $data->shop_code);
            $sheet->setCellValue('C' . $row, $data->tgl_delivery);
            $sheet->setCellValue('D' . $row, $data->part_number);
            $sheet->setCellValue('E' . $row, $data->part_name);
            $sheet->setCellValue('F' . $row, $data->vendor_code);
            $sheet->setCellValue('G' . $row, $data->vendor_name);
            $sheet->setCellValue('H' . $row, $data->job_no);
            $sheet->setCellValue('I' . $row, $data->qty_kanban);
            $sheet->setCellValue('J' . $row, ($data->qty_kanban * $data->std_qty));
            $sheet->setCellValue('K' . $row, ($data->qty_kanban * $data->std_qty * $data->price));
            $sheet->setCellValue('L' . $row, $data->remark_part);
            $total_price += ($data->qty_kanban * $data->std_qty * $data->price);
            $row++;
        }
        $sheet->setCellValue('J' . $row, "TOTAL PRICE");
        $sheet->setCellValue('K' . $row, $total_price);

        // STYLE HEADER (baris 1)
        $headerRange = 'A1:L1';
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFC000');

        // STYLE kolom A (selain header) jadi center
        $lastRow = $row - 1;
        $lastRow1 = $row + 1;
        $sheet->getStyle("A2:L$lastRow1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("E2:E$lastRow1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // BORDER di semua sel terisi (A1:C lastRow)
        $allRange = "A1:L$lastRow";
        $sheet->getStyle($allRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        
        // FORMAT CURRENCY untuk kolom K
        $sheet->getStyle("K2:K$lastRow1")
            ->getNumberFormat()
            ->setFormatCode('"Rp "#,##0');

        // AUTO WIDTH kolom berdasarkan isi
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Header agar browser mendownload file Excel
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$so.'.xlsx"');
        header('Cache-Control: max-age=0');

        // Buat writer dan output ke php://output supaya langsung terdownload
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    function send_email()
    {
        $to = $this->input->post("to");
        $subject = $this->input->post("subject");
        $body = $this->input->post("body");
        $file = $this->input->post("file");
        
        $curl = curl_init();

        $data = [
            "sender" => [
                "name" => EMAIL_NAME,
                "email" => EMAIL_SENDER
            ],
            "to" => [[
                "email" => $to
            ]],
            "subject" => $subject,
            "htmlContent" => $body
        ];

        curl_setopt_array($curl, [
            CURLOPT_URL => EMAIL_API_URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                "accept: application/json",
                "api-key: ".EMAIL_API_KEY,
                "content-type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            $fb = ["statusCode" => 500, "res" => $err];
        } else {
            $fb = ["statusCode" => 200, "timesend" => date("Y-m-d H:i:s"), "to" => $to, "subject" => $subject, "res" => "Kirim email berhasil"];
        }
        $this->fb($fb);
    }
    
    function test_email()
    {
        $to = EMAIL_TO_TEST;
        $subject = "Test";
        $body = "Ini test email";
        
        $curl = curl_init();

        $data = [
            "sender" => [
                "name" => EMAIL_NAME,
                "email" => EMAIL_SENDER
            ],
            "to" => [[
                "email" => $to
            ]],
            "subject" => $subject,
            "htmlContent" => $body
        ];

        curl_setopt_array($curl, [
            CURLOPT_URL => EMAIL_API_URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                "accept: application/json",
                "api-key: ".EMAIL_API_KEY,
                "content-type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            $fb = ["statusCode" => 500, "res" => $err];
        } else {
            $fb = ["statusCode" => 200, "timesend" => date("Y-m-d H:i:s"), "to" => $to, "subject" => $subject, "res" => "Kirim email berhasil"];
        }
        $this->fb($fb);
    }

    private function exec_send_email($to,$subject,$body)
    {
        $curl = curl_init();

        $data = [
            "sender" => [
                "name" => EMAIL_NAME,
                "email" => EMAIL_SENDER
            ],
            "to" => [[
                "email" => $to
            ]],
            "subject" => $subject,
            "htmlContent" => $body
        ];

        curl_setopt_array($curl, [
            CURLOPT_URL => EMAIL_API_URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                "accept: application/json",
                "api-key: ".EMAIL_API_KEY,
                "content-type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            $fb = ["statusCode" => 500, "res" => $err];
        } else {
            $fb = ["statusCode" => 200, "timesend" => date("Y-m-d H:i:s"), "to" => $to, "subject" => $subject, "res" => "Kirim email berhasil"];
        }
        return $fb;
    }

    function get_detail_so($return = "json", $so_number = "")
    {
        if(empty($so_number)){
            $so_number = $this->input->get("so_number");
        }
        $data_so = $this->model->gd("data_order","*","so_number = '$so_number'","row");
        $created_by = $this->model->gd("account","name","id = '".$data_so->created_by."'","row");
        $pic = $this->model->gd("account","name","id = '".$data_so->pic."'","row");
        $spv_sign = $this->model->gd("account","name","id = '".$data_so->spv_sign."'","row");
        $mng_sign = $this->model->gd("account","name","id = '".$data_so->mng_sign."'","row");
        $reject_by = $this->model->gd("account","name","id = '".$data_so->reject_by."'","row");
        
        if(!empty($data_so->release_sign)){
            $release_sign = $this->model->gd("account","name","id = '".$data_so->release_sign."'","row");
        }else{
            $release_sign = (object) [
                "name" => ""
            ];
        }
        $detail_part_so = $this->model->join_data(
            "data_part_order a",
            "master b",
            "a.part_number=b.part_number AND a.vendor_code=b.vendor_code",
            "a.shop_code,a.tgl_delivery,a.qty_kanban,a.remarks as remark_part,b.*,(a.qty_kanban * b.std_qty) as qty_packing,(a.qty_kanban * b.std_qty * b.price) as total_price",
            "a.deleted_date IS NULL AND so_number = '$so_number'",
            "result"
        );

        $grand_total_price = 0;
        $grand_total_req_kbn = 0;
        $grand_total_req_pcs = 0;
        if(!empty($detail_part_so)){
            foreach ($detail_part_so as $part) {
                $grand_total_price += $part->total_price;
                $grand_total_req_kbn += $part->qty_kanban;
                $grand_total_req_pcs += ($part->qty_kanban * $part->std_qty);
            }
        }

        $data_res = [
            "data_so" => $data_so,
            "detail_part_so" => $detail_part_so,
            "created_by" => $created_by->name,
            "pic" => $pic->name,
            "spv_sign" => $spv_sign->name,
            "mng_sign" => $mng_sign->name,
            "release_sign" => $release_sign->name,
            "reject_by" => !empty($reject_by->name) ? $reject_by->name : "",
            "ttd_pic" => !empty($data_so->created_time) ? ($pic->name.",".$data_so->created_time) : "",
            "ttd_spv_sign" => !empty($data_so->spv_sign_time) ? ($spv_sign->name.",".$data_so->spv_sign_time) : "",
            "ttd_mng_sign" => !empty($data_so->mng_sign_time) ? ($mng_sign->name.",".$data_so->mng_sign_time) : "",
            "ttd_release_sign" => !empty($data_so->release_sign_time) ? ($release_sign->name.",".$data_so->release_sign_time) : "",
            "grand_total_price" => $grand_total_price,
            "grand_total_req_kbn" => $grand_total_req_kbn,
            "grand_total_req_pcs" => $grand_total_req_pcs
        ];

        if($return == "json"){
            $this->fb($data_res); 
        }else{
            return $data_res;
        }
    }

    function print_so()
    {
        $data_so = $this->get_detail_so("array");
        if(empty($data_so)){
            $fb = ["statusCode" => 404, "res" => "Data SO tidak ditemukan"];
            $this->fb($fb);
        }

        $html = $this->load->view("admin/print_so", $data_so, true);
        echo $html;
    }

    function update_profile()
    {
        if(empty($this->id_user)){
            $fb = ["statusCode" => 500, "res" => "Sesi login anda telah berakhir"];
            $this->fb($fb);
        }

        $this->form_validation
            ->set_rules('level','Level','required|trim|integer')
            ->set_rules('name','Nama','required|trim')
            ->set_rules('username','Username','required|trim')
            ->set_rules('dept','Departement','required|trim')
            ->set_rules('email','Email','required|trim|valid_email');

        if($this->form_validation->run() === FALSE){
            $fb = ["statusCode" => 500, "res" => validation_errors()];
            $this->fb($fb);
        }

        $name = $this->input->post("name");
        $level = $this->input->post("level");
        $username = $this->input->post("username");
		$deptInput = json_decode($this->input->post('dept'),TRUE);
		if (!is_array($deptInput)) {
			$fb = ["statusCode" => 400, "res" => "Departemen wajib diisi minimal 1".$deptInput];
			$this->fb($fb);
		}
		$dept = json_encode($deptInput);
        $email = $this->input->post("email");
        $id_user = $this->id_user;

        $data_update = [
            "name" => $name,
            "level" => $level,
            "username" => $username,
            "dept" => $dept,
            "email" => $email,
        ];

        $this->model->update("account","id = '$id_user'",$data_update);
        
        $validation = $this->model->gd("account","*,(SELECT name FROM departement WHERE id = dept) AS dept, dept as dept_id","id = '$id_user'","row");
        if(empty($validation)){
            $fb = ["statusCode" => 500, "res" => "Akun belum di daftarkan"];
            $this->fb($fb);
        }

        if($validation->status == "0"){
            $fb = ["statusCode" => 500, "res" => "Akun sudah di non aktifkan"];
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
            "level_name" => $level_name,
            "username" => $validation->username,
            "dept_id" => $validation->dept_id,
            "email" => $validation->email,
        ];
        $this->session->set_userdata($data);

        $fb = ["statusCode" => 200, "res" => "Profile berhasil di rubah"];
        $this->fb($fb);
    }

    function change_password()
    {
        if(empty($this->id_user)){
            $fb = ["statusCode" => 500, "res" => "Sesi login anda telah berakhir"];
            $this->fb($fb);
        }

        $this->form_validation
            ->set_rules('password','Password','required|trim')
            ->set_rules('passwordconfirm','Password Confirmation','required|trim|matches[password]');

        if($this->form_validation->run() === FALSE){
            $fb = ["statusCode" => 500, "res" => validation_errors()];
            $this->fb($fb);
        }

        $password = password_hash($this->input->post("password"),PASSWORD_DEFAULT);
        $update_password = ["password" => $password];
        $this->model->update("account","id = '".$this->id_user."'",$update_password);
        $fb = ["statusCode" => 200, "res" => "Password berhasil di rubah"];
        $this->fb($fb);
    }
}
