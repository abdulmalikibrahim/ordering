<?php
$so_number = $this->input->get('so_number');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?= base_url("assets/favicon.ico"); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.0.1/css/bootstrap.min.css" integrity="sha512-Ez0cGzNzHR1tYAv56860NLspgUGuQw16GiOOp/I2LuTmpSK9xDXlgJz3XN4cnpXWDmkNBKXR/VDMTCnAaEooxA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Print SO</title>
    <style>
        /* Tampilkan semua elemen saat print, termasuk yang disembunyikan oleh Bootstrap */
        @media print {
            /* Background dan warna tetap tampil */
            * {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
        }
    </style>

</head>
<body onafterprint="window.close();">
    <table class="table table-borderless">
        <thead class="text-center">
            <tr>
                <td rowspan="4" style="width: 112px; height: 94px;">
                    <div class="d-flex justify-content-center align-items-center" style="width: 100%; height: 100%;">
                        <img src="<?= base_url("assets/logo-square.webp") ?>" style="width: 100%; height: 80px; border-radius: 0px;">
                    </div>
                </td>
            </tr>
            <tr>
                <td rowspan="3" style="width: 500px; height: 94px;">
                    <div class="d-flex justify-content-center align-items-center" style="width: 100%; height: 100%;">
                        <h4>FORM REQUEST SPECIAL ORDER</h4>
                    </div>
                </td>
                <td class="border border-dark bg-secondary text-light" style="font-size: 10pt;">Received By</td>
                <td class="border border-dark bg-secondary text-light" style="font-size: 10pt;">Approve By</td>
                <td class="border border-dark bg-secondary text-light" style="font-size: 10pt;">Checked By</td>
                <td class="border border-dark bg-secondary text-light" style="font-size: 10pt;">Request By</td>
            </tr>
            <tr>
                <td class="border border-dark">
                    <div id="qrcode-ttd_release_sign" class="d-flex justify-content-center"></div>
                </td>
                <td class="border border-dark">
                    <div id="qrcode-ttd_mng_sign" class="d-flex justify-content-center"></div>
                </td>
                <td class="border border-dark">
                    <div id="qrcode-spv_sign" class="d-flex justify-content-center"></div>
                </td>
                <td class="border border-dark">
                    <div id="qrcode-ttd_pic" class="d-flex justify-content-center"></div>
                </td>
            </tr>
            <tr>
                <td class="border border-dark" style="font-size: 8pt;"><?= $release_sign; ?></td>
                <td class="border border-dark" style="font-size: 8pt;"><?= $mng_sign; ?></td>
                <td class="border border-dark" style="font-size: 8pt;"><?= $spv_sign; ?></td>
                <td class="border border-dark" style="font-size: 8pt;"><?= $pic; ?></td>
            </tr>
        </thead>
    </table>

    <table class="table table-borderless">
        <thead>
            <tr>
                <td class="d-flex align-items-top">
                    <table class="table table-borderless">
                        <thead>
                            <tr>
                                <td style="width: 150px;">Registration Code</td>
                                <td>: <?= $data_so->so_number ?></td>
                            </tr>
                            <tr>
                                <td>Date Request</td>
                                <td>: <?= date("d-M-Y H:i:s",strtotime($data_so->created_time)) ?></td>
                            </tr>
                            <tr>
                                <td>Status</td>
                                <td>
                                    : <?= !empty($data_so->release_sign) ? '<span class="badge bg-success">Accepted</span>' : (!empty($data_so->reject_date) ? '<span class="badge bg-danger">Reject</span>' : '<span class="badge bg-warning">Under Approval</span>') ?> 
                                    
                                </td>
                            </tr>
                        </thead>
                    </table>
                </td>
                <td>
                    <table class="table table-borderless">
                        <thead>
                            <tr>
                                <td style="width: 150px;">PIC Request</td>
                                <td>: <?= $pic; ?></td>
                            </tr>
                            <tr>
                                <td>Shop</td>
                                <td>: <?= $data_so->shop_code; ?></td>
                            </tr>
                            
                            <?php
                            if(!empty($data_so->reject_date)){
                                ?>
                                <tr>
                                    <td>Reject Date</td>
                                    <td>: <?= date("d-M-Y H:i:s",strtotime($data_so->reject_date)); ?></td>
                                </tr>
                                <tr>
                                    <td>Reject By</td>
                                    <td>: <?= $reject_by; ?></td>
                                </tr>
                                <tr>
                                    <td>Reject Reason</td>
                                    <td>: <?= $data_so->reject_reason; ?></td>
                                </tr>
                                <?php
                            }else{
                                ?>
                                <tr>
                                    <td>Release Date</td>
                                    <td>: <?= !empty($data_so->release_sign_time) ? date("d-M-Y H:i:s",strtotime($data_so->release_sign_time)) : "-"; ?></td>
                                </tr>
                                <?php
                            }
                            ?>
                        </thead>
                    </table>
                </td>
            </tr>
        </thead>
    </table>
    
    <table class="table table-borderless">
        <thead>
            <tr class="text-center">
                <td class="border border-dark" style="font-size: 8pt;">No</td>
                <td class="border border-dark" style="font-size: 8pt;">Job. No</td>
                <td class="border border-dark" style="font-size: 8pt;">Part No</td>
                <td class="border border-dark" style="font-size: 8pt;">Part Name</td>
                <td class="border border-dark" style="font-size: 8pt;">Supplier</td>
                <td class="border border-dark" style="font-size: 8pt;">Price/Pcs</td>
                <td class="border border-dark" style="font-size: 8pt;">Qty/Kbn</td>
                <td class="border border-dark" style="font-size: 8pt;">Req. Kbn</td>
                <td class="border border-dark" style="font-size: 8pt;">Req. Pcs</td>
                <td class="border border-dark" style="font-size: 8pt;">Total Price</td>
                <td class="border border-dark" style="font-size: 8pt;">Remarks</td>
            </tr>
        </thead>
        <tbody>
            <?php
            $total_req_kanban = 0;
            $total_req_pcs = 0;
            $total_price_all = 0;
            if(!empty($detail_part_so)){
                $no = 1;
                foreach ($detail_part_so as $key => $value) {
                    $total_price = $value->price*$value->qty_packing;
                    $total_req_kanban += $value->qty_kanban;
                    $total_req_pcs += $value->qty_packing;
                    $total_price_all += $total_price;
                    ?>
                    <tr class="text-center">
                        <td class="border border-dark" style="font-size: 8pt;"><?= $no++; ?></td>
                        <td class="border border-dark" style="font-size: 8pt;"><?= $value->job_no; ?></td>
                        <td class="border border-dark" style="font-size: 8pt;"><?= $value->part_number; ?></td>
                        <td class="border border-dark" style="font-size: 8pt;"><?= $value->part_name; ?></td>
                        <td class="border border-dark" style="font-size: 8pt;"><?= $value->vendor_name; ?></td>
                        <td class="border border-dark" style="font-size: 8pt;"><?= number_format($value->price,0,"",","); ?></td>
                        <td class="border border-dark" style="font-size: 8pt;"><?= number_format($value->std_qty,0,"",","); ?></td>
                        <td class="border border-dark" style="font-size: 8pt;"><?= number_format($value->qty_kanban,0,"",","); ?></td>
                        <td class="border border-dark" style="font-size: 8pt;"><?= number_format($value->qty_packing,0,"",","); ?></td>
                        <td class="border border-dark" style="font-size: 8pt;"><?= number_format($total_price,0,"",","); ?></td>
                        <td class="border border-dark" style="font-size: 8pt;"></td>
                    </tr>
                    <?php
                }
            }
            ?>
            <tr class="text-center">
                <td class="border border-dark" colspan="7" style="font-size: 8pt;">
                    <h6 class="m-0">Total Part Release Order</h6></td>
                <td class="border border-dark" style="font-size: 8pt;">
                    <h6 class="m-0"><?= number_format($total_req_kanban,0,"",","); ?></h6></td>
                <td class="border border-dark" style="font-size: 8pt;">
                    <h6 class="m-0"><?= number_format($total_req_pcs,0,"",","); ?></h6></td>
                <td class="border border-dark" style="font-size: 8pt;">
                    <h6 class="m-0"><?= number_format($total_price_all,0,"",","); ?></h6></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    // Generate QR codes for signatures
    new QRCode(document.getElementById("qrcode-ttd_release_sign"), {
        text: "<?= $ttd_release_sign; ?>",
        width: 60,
        height: 60,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H // Tingkat koreksi kesalahan: L, M, Q, H
    });
    new QRCode(document.getElementById("qrcode-ttd_mng_sign"), {
        text: "<?= $ttd_mng_sign; ?>",
        width: 60,
        height: 60,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H // Tingkat koreksi kesalahan: L, M, Q, H
    });
    new QRCode(document.getElementById("qrcode-spv_sign"), {
        text: "<?= $ttd_spv_sign; ?>",
        width: 60,
        height: 60,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H // Tingkat koreksi kesalahan: L, M, Q, H
    });
    new QRCode(document.getElementById("qrcode-ttd_pic"), {
        text: "<?= $ttd_pic; ?>",
        width: 60,
        height: 60,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H // Tingkat koreksi kesalahan: L, M, Q, H
    });
</script>
<?php
if(empty($this->input->get("download"))){
    ?>
    <script>
        setTimeout(function() {
            window.print();
        }, 1000);
    </script>
    <?php
}
?>