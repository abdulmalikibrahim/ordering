<?php
if(!function_exists('encrypt')){
    function encrypt($plainText) {
        $ciphering = "AES-128-CBC";
        $iv_length = openssl_cipher_iv_length($ciphering);
        $encryption_iv = openssl_random_pseudo_bytes($iv_length);
        $encryption_key = 'Ordering_SO';

        $encrypted = openssl_encrypt($plainText, $ciphering, $encryption_key, 0, $encryption_iv);

        // Gabungin IV sama hasil enkripsi
        $final_output = base64_encode($encryption_iv . $encrypted);
        return $final_output;
    }

}

if(!function_exists('decrypt')){
    function decrypt($data) {
        $ciphering = "AES-128-CBC";
        $encryption_key = 'Ordering_SO';

        $decoded = base64_decode($data);
        $iv_length = openssl_cipher_iv_length($ciphering);

        // Ambil IV dari awal data
        $decryption_iv = substr($decoded, 0, $iv_length);
        $encrypted_data = substr($decoded, $iv_length);

        $decrypted = openssl_decrypt($encrypted_data, $ciphering, $encryption_key, 0, $decryption_iv);

        return $decrypted;
    }
}

// if(!function_exists('encrypt')){
//     function encrypt($value)
//     {
//         $ciphering = "AES-128-CBC";
//         $iv_length = openssl_cipher_iv_length($ciphering);
//         $options = 0;
//         $encryption_iv = '1234567891011121';
//         $encryption_key = 'Ordering_SO';
//         $encryption = bin2hex(openssl_encrypt($value, $ciphering, $encryption_key, $options, $encryption_iv));
//         return $encryption;
//     }

// }

// if(!function_exists('decrypt')){
//     function decrypt($value)
//     {
//         $ciphering = "AES-128-CBC";
//         $iv_length = openssl_cipher_iv_length($ciphering);
//         $options = 0;
//         $decryption_iv = '1234567891011121';
//         $decryption_key = 'Ordering_SO';
// 		if (ctype_xdigit($value) && strlen($value) % 2 == 0) {
// 			$decryption = openssl_decrypt(hex2bin($value), $ciphering, $decryption_key, $options, $decryption_iv);
// 		} else {
// 			$decryption = "Decryption error";
// 		}
//         return $decryption;
//     }
// }