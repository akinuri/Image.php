<?php

include "Image.php";

// $img = new Image("16-10__2560__4.7M.png");
$img = new Image("win_xp_transparent.png");
$img->convert();
$img->square();
$img->resize(256, 256);
$img->show();
$img->save("squared_resized.png");
$img->destroy();

// print_r($img);
