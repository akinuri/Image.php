<?php

include "../src/Image.php";

/* new image instance */
$img = new Image("cornell-box.jpg");

/* convert to jpeg */
$img->convert();

/* crop into a square shape (from vertical/horizontal) */
$img->square();

/* resize (e.g. thumbnail) */
$img->resize(256, 256);

/* fit into or fill in a specific area */
// $img->fit(256, 100);
// $img->fill(256, 100);

/* save the current image */
$img->save("squared_resized.jpg");

/* output the image to browser */
$img->show();

/* clear memory */
// $img->destroy();

/* inspect */
// print_r($img);
