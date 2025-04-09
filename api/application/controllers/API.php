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

        $data = [
            "id_user" => $validation->id,
            "level" => $validation->level,
            "dept" => $validation->dept,
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
            $fb = ["statusCode" => 500, "res" => "Session kosong, silahkan login", "id_user" => $this->session->userdata("id_user")];
            $this->fb($fb);
        }
        
        $fb = ["statusCode" => 200, "res" => "Session masih ada", "id_user" => $this->session->userdata("id_user")];
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
            $data = $this->model->query_exec("
                SELECT 
                    a.id,
                    a.username, 
                    a.name,
                    a.dept,
                    a.level,
                    a.spv,
                    a.mng,
                    spv_acc.name AS spv_name, 
                    mng_acc.name AS mng_name
                FROM account a
                LEFT JOIN account spv_acc ON a.spv = spv_acc.id
                LEFT JOIN account mng_acc ON a.mng = mng_acc.id
                WHERE a.id IS NOT NULL",
                "result"
            );
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
                        "spv" => $row->spv_name ?? '', // Hindari error jika NULL
                        "mng" => $row->mng_name ?? '', // Hindari error jika NULL
                        "id_spv" => $row->spv ?? '', // Hindari error jika NULL
                        "id_mng" => $row->mng ?? '', // Hindari error jika NULL
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

        function get_superior($level)
        {
            $superior = $this->model->gd("account","id,name","level = '$level'","result_array");
            $this->fb($superior);    
        }

        function save_account()
        {
            $mode = $this->input->post('mode');
            $this->form_validation
                ->set_rules("name","Nama","required|trim")
                ->set_rules("username","Username","required|trim")
                ->set_rules("dept","Departement","required|trim|integer")
                ->set_rules("level","Level","required|trim|integer")
                ->set_rules("spv","Supervisor","integer|trim")
                ->set_rules("mng","Manager","integer|trim")
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
            $spv = $this->input->post('spv');
            $mng = $this->input->post('mng');
            $id_update = $this->input->post('id_update');

            $dataSubmit = [
                "name" => $name,
                "username" => $username,
                "dept" => $dept,
                "level" => $level,
                "spv" => $spv,
                "mng" => $mng,
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
            $type = $this->input->post("type");
            $data = $this->model->gd("data_order","*","created_by = '".$this->id_user."' AND type='$type'");
            $newData = [];
            if (!empty($data)) {
                foreach ($data as $row) {
                    $account = $this->model->gd("account", "name, (SELECT name FROM departement WHERE id=dept) as name_dept", "id = '$row->created_by'", "row");
                    $spv_sign = '-';
                    $spv_sign_time = '-';
                    if(!empty($row->spv_sign)){
                        $data_spv = $this->model->gd("account", "name", "id = '$row->spv_sign'", "row");
                        $spv_sign = $data_spv->name;
                        $spv_sign_time = date("d-M-Y H:i:s",strtotime($row->spv_sign_time));
                    }
                    
                    $mng_sign = '-';
                    $mng_sign_time = '-';
                    if(!empty($row->mng_sign)){
                        $data_spv = $this->model->gd("account", "name", "id = '$row->mng_sign'", "row");
                        $mng_sign = $data_spv->name;
                        $mng_sign_time = date("d-M-Y H:i:s",strtotime($row->mng_sign_time));
                    }

                    $total_part = $this->model->gd("data_part_order","COUNT(*) as total","so_number = '$row->so_number'","row");

                    $newData[] = [
                        "id" => $row->id,
                        "created_time" => date("d-M-Y H:i:s",strtotime($row->created_time)),
                        "creator" => $account->name ?? '',
                        "dept_creator" => $account->name_dept ?? '', // Hindari error jika dept NULL
                        "so_number" => $row->so_number,
                        "shop_code" => $row->shop_code,
                        "spv_sign" => $spv_sign,
                        "spv_sign_time" => $spv_sign_time,
                        "mng_sign" => $mng_sign,
                        "mng_sign_time" => $mng_sign_time,
                        "total_part" => $total_part->total,
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
                        'ta' => $sheet->getCell('A' . $row)->getValue(),
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
    //DATA SO

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
}
