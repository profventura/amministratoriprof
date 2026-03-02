<?php
$file = 'public/images/logos/ap_logo.jpg';
if (file_exists($file)) {
    echo base64_encode(file_get_contents($file));
} else {
    echo "FILE_NOT_FOUND";
}
?>