<?php
session_start();

// Generate a random CAPTCHA code
$captchaCode = substr(str_shuffle('abcdefghjkmnpqrstuvwxyz23456789'), 0, 5);
$_SESSION['captcha'] = $captchaCode;

// Create an image
$image = imagecreatetruecolor(180, 80);

// Set colors
$backgroundColor = imagecolorallocate($image, 255, 255, 255);
$textColor = imagecolorallocate($image, 0, 0, 0);
$noiseColor = imagecolorallocate($image, 100, 100, 100);

// Fill the background
imagefilledrectangle($image, 0, 0, 180, 80, $backgroundColor);

// Add noise (dots)
for ($i = 0; $i < 1000; $i++) {
    imagesetpixel($image, rand(0, 180), rand(0, 80), $noiseColor);
}

// Add the CAPTCHA text with larger font size
$fontFile = __DIR__ . '/css/ARIAL.TTF'; // Path to your TTF font file
$fontSize = 25; // Font size
$x = 30; // X-coordinate for text
$y = 55; // Y-coordinate for text
imagettftext($image, $fontSize, 0, $x, $y, $textColor, $fontFile, $captchaCode);

// Output the image
header("Content-type: image/png");
imagepng($image);
imagedestroy($image);
?>
