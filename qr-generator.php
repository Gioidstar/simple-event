<?php
// QR Code Generator - Lite (tanpa library)

// Parameter
$data = isset($_GET['data']) ? trim($_GET['data']) : '';
if ($data === '') {
    die('No data provided.');
}

// Gunakan fungsi hash untuk membuat pola kotak sederhana (tidak full QR Code spek, tapi cukup untuk visual ID)
$hash = md5($data);
$size = 10; // pixel size per "blok"
$cols = 21;
$rows = 21;
$img_size = $size * $cols;

header('Content-Type: image/png');

$image = imagecreate($img_size, $img_size);
$white = imagecolorallocate($image, 255, 255, 255);
$black = imagecolorallocate($image, 0, 0, 0);

// Gambar "QR" kotak kotak berdasarkan hash
for ($y = 0; $y < $rows; $y++) {
    for ($x = 0; $x < $cols; $x++) {
        // Ambil nilai dari hash
        $pos = ($y * $cols + $x) % strlen($hash);
        $char = $hash[$pos];
        if (hexdec($char) % 2 === 0) {
            imagefilledrectangle(
                $image,
                $x * $size,
                $y * $size,
                ($x + 1) * $size,
                ($y + 1) * $size,
                $black
            );
        }
    }
}

// Optional: Tambahkan teks di bawah
imagestring($image, 2, 5, $img_size - 15, $data, $black);

imagepng($image);
imagedestroy($image);
