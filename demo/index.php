<?php

include "Image.php";

$img = new Image("win_xp_transparent.png");
$img->convert();
$img->square();
$img->resize(256, 256);
$img->show();
$img->save("squared_resized.jpg");
$img->destroy();

// print_r($img);
