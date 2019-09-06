# Image.php
A class for image manipulation.

Written for small tasks after a file upload.

```php
include "Image.php";

$img = new Image("win_xp_transparent.png");
$img->convert();            // to jpeg
$img->square();             // horizontal|vertical -> square
$img->resize(256, 256);
$img->show();               // send to client
$img->save("squared_resized.png");
$img->destroy();
```