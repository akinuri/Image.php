# Image.php
A class for image manipulation.

Written for small tasks after a file upload.

```php
include "Image.php";

$img = new Image("win_xp_transparent.png");
$img->convert();            // to jpeg
$img->square();             // horizontal|vertical -> square
$img->resize(256, 256);
$img->show();               // output to browser
$img->save("squared_resized.jpg");
$img->destroy();
```