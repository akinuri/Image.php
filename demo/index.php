<?php

include "../src/Image.php";

$img = new Image("cornell-box.jpg");
$img->convert();
$img->square();
$img->resize(256, 256);
// $img->fit(256, 100);
// $img->fill(256, 100);
$img->show();
$img->save("squared_resized.jpg");
$img->destroy();

// print_r($img);
