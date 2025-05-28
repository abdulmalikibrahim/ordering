<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?= base_url("assets/favicon.ico"); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.0.1/css/bootstrap.min.css" integrity="sha512-Ez0cGzNzHR1tYAv56860NLspgUGuQw16GiOOp/I2LuTmpSK9xDXlgJz3XN4cnpXWDmkNBKXR/VDMTCnAaEooxA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Approval SO</title>
</head>
<body class="bg-info">
    <div class="container">
        <div class="row d-flex justify-content-center align-items-center min-vh-100">
            <div class="col-lg-12 text-center">
                <h1 class="text-light" id="status-approval">Processing Approval</h1>
                <p class="text-light" id="attr-loading">Mohon tunggu sampai proses selesai</p>
                <div class="mt-4">
                    <i class="fas fa-spinner fa-spin text-light" id="icon-approval" style="font-size:9rem;"></i>
                </div>
                <p class="text-light mt-4 fw-bold" id="attr-success"></p>
                <p class="text-danger mt-4 fw-bold" id="attr-error"></p>
                <button class="btn btn-sm btn-light" onclick="close_page()" id="btn-close-page">Tutup Halaman</button>
            </div>
        </div>
    </div>
</body>
</html>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    function exec() {
        $("#btn-close-page").hide();
        setTimeout(() => {
            $.ajax({
                url:'<?= base_url("exec_direct_approve?p=".$this->input->get("p")); ?>',
                dataType:"JSON",
                success:function(r){
                    d = JSON.parse(JSON.stringify(r));
                    $("#attr-loading").hide();
                    if(d.statusCode == 200){
                        $("#status-approval").text("Approval Berhasil");
                        $("#icon-approval").removeClass("fa-spinner fa-spin").addClass("fa-check-circle");
                        $("#attr-success").text(d.res);
                    } else if(d.statusCode == 400) {
                        $("#status-approval").text("Approval Gagal");
                        $("#icon-approval").removeClass("fa-spinner fa-spin").addClass("fa-exclamation-circle");
                        $("#attr-error").text(d.res);
                    }
                    $("#btn-close-page").show();
                },
                error:function(xhr, status, error){
                    $("#attr-loading").hide();
                    $("#status-approval").text("Approval Gagal");
                    $("#icon-approval").removeClass("fa-spinner fa-spin").addClass("fa-xmark");
                    if(xhr.responseJSON){
                        $("#attr-error").text(xhr.responseJSON.res || "Terjadi kesalahan saat memproses permintaan.");
                    }else{
                        $("#attr-error").text(xhr.responseText || "Terjadi kesalahan saat memproses permintaan.");
                    }
                    $("#btn-close-page").show();
                }
            });
        }, 1000);
    }

    exec();

    function close_page() {
        window.close();
    }
</script>