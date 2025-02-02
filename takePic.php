<?php

require_once('db.php');
require_once('folders.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set ("memory_limit", "400M");
error_reporting(E_ALL);

$my_config = 'my.config.inc.php';
if (file_exists($my_config)) {
	require_once('my.config.inc.php');
} else {
	require_once('config.inc.php');
}

if($config['file_format_date'] == true) {
	$file = date('Ymd_His').'.jpg';
} else {
	$file = md5(time()).'.jpg';
}

$filename_photo = $config['folders']['images'] . DIRECTORY_SEPARATOR . $file;
$filename_keying = $config['folders']['keying'] . DIRECTORY_SEPARATOR . $file;
$filename_tmp = $config['folders']['tmp'] . DIRECTORY_SEPARATOR . $file;
$filename_thumb = $config['folders']['thumbs'] . DIRECTORY_SEPARATOR . $file;


if ($_POST['style'] === 'photo') {
	$filename_orig = $filename_photo;
	$use_filter = false;
} else {
	$filename_orig = $filename_tmp;
	$use_filter = false;
}
if(!empty($_POST) && !($_POST['filter'] === 'imgPlain')) {
	$filename_orig = $filename_tmp;
	$use_filter = true;
	$imgfilter = $_POST['filter'];
}


if($_POST['style'] === 'photo') {

	if($config['dev'] === false) {
		$shootimage = shell_exec(
			sprintf(
				$config['take_picture']['cmd'],
				$filename_orig
				)
			);
		if(strpos($shootimage, $config['take_picture']['msg']) === false) {
				die(json_encode(array('error' => true)));
		}
	} else {
		$devImg = array('resources/img/bg.jpg');
		copy(
			$devImg[array_rand($devImg)],
			$filename_orig
		);
	}

} else {
	if($config['dev'] === false) {
		$collagePhoto = array();
		$i = 0;

		//PIC 1
		$shootimage = shell_exec(
			sprintf(
				$config['take_picture']['cmd'],
				"$filename_orig-$i"
				)
			);
			$collagePhoto[$i] = "$filename_orig-$i";
		if(strpos($shootimage, $config['take_picture']['msg']) === false) {
				die(json_encode(array('error' => true)));
		}
		$i++;

		// PIC 2
		$shootimage = shell_exec(
			sprintf(
				$config['take_picture']['cmd'],
				"$filename_orig-$i"
				)
			);

			$collagePhoto[$i] = "$filename_orig-$i";
		if(strpos($shootimage, $config['take_picture']['msg']) === false) {
				die(json_encode(array('error' => true)));
		}
		$i++;

		// PIC 3
		$shootimage = shell_exec(
			sprintf(
				$config['take_picture']['cmd'],
				"$filename_orig-$i"
				)
			);

			$collagePhoto[$i] = "$filename_orig-$i";
		if(strpos($shootimage, $config['take_picture']['msg']) === false) {
				die(json_encode(array('error' => true)));
		}
		$i++;

		// PIC 4
		$shootimage = shell_exec(
			sprintf(
				$config['take_picture']['cmd'],
				"$filename_orig-$i"
				)
			);

			$collagePhoto[$i] = "$filename_orig-$i";
		if(strpos($shootimage, $config['take_picture']['msg']) === false) {
				die(json_encode(array('error' => true)));
		}
		$i++;

	} else {
		$collagePhoto = array();
                for( $i = 0; $i < 4; $i++ ) {
			$devImg = array('resources/img/bg.jpg');
			copy(
				$devImg[array_rand($devImg)],
				"$filename_orig-$i"
				);
			$collagePhoto[$i] = "$filename_orig-$i";
		sleep(1);
		}

	}
	// make collage
	list($width, $height) = getimagesize($collagePhoto[0]);
	$my_collage_height = $height * 2;
	$my_collage_width = $width * 2;
	$my_collage = imagecreatetruecolor($my_collage_width,$my_collage_height)
			or die("Kann keinen neuen GD-Bild-Stream erzeugen");
	$background = imagecolorallocate( $my_collage, 0, 0, 0);
	imagecolortransparent($my_collage, $background);
	$collage_pic1 = imagecreatefromjpeg($collagePhoto[0]) or die("no imagcreate");
	imagecopy( $my_collage, $collage_pic1,0,0,0,0,$width,$height);
	$collage_pic2 = imagecreatefromjpeg($collagePhoto[1]) or die("no imagcreate");
	imagecopy ( $my_collage, $collage_pic2,$width,0,0,0,$width,$height);
	$collage_pic3 = imagecreatefromjpeg($collagePhoto[2]) or die("no imagcreate");
	imagecopy ( $my_collage, $collage_pic3,0,$height,0,0,$width,$height);
	$collage_pic4 = imagecreatefromjpeg($collagePhoto[3]) or die("no imagcreate");
	imagecopy ( $my_collage, $collage_pic4,$width,$height,0,0,$width,$height);

	if($use_filter == true) {
		imagejpeg($my_collage, $filename_orig);
	} else {
		imagejpeg($my_collage, $filename_photo);
	}
	imagedestroy( $my_collage );
	imagedestroy( $collage_pic1 );
	imagedestroy( $collage_pic2 );
	imagedestroy( $collage_pic3 );
	imagedestroy( $collage_pic4 );
}

// apply filter
if($use_filter == true) {
	$tmp = imagecreatefromjpeg($filename_orig);

	switch($imgfilter) {
		case 'imgAntique':
			imagefilter($tmp, IMG_FILTER_BRIGHTNESS, 0);
			imagefilter($tmp, IMG_FILTER_CONTRAST, -30);
			imagefilter($tmp, IMG_FILTER_COLORIZE, 75, 50, 25);
			break;
		case 'imgAqua':
			imagefilter($tmp, IMG_FILTER_COLORIZE, 0, 70, 0, 30);
			break;
		case 'imgBlue':
			imagefilter($tmp, IMG_FILTER_COLORIZE, 0, 0, 100);
			break;
		case 'imgBlur':
			imagefilter($tmp, IMG_FILTER_GAUSSIAN_BLUR);
			break;
		case 'imgColor':
			imagefilter($tmp, IMG_FILTER_CONTRAST, -40);
			break;
		case 'imgCool':
			imagefilter($tmp, IMG_FILTER_MEAN_REMOVAL);
			imagefilter($tmp, IMG_FILTER_CONTRAST, -50);
			break;
		case 'imgEdge':
			$emboss = array(array(1, 1, 1), array(1, -7, 1), array(1, 1, 1));
			imageconvolution($tmp, $emboss, 1, 0);
			break;
		case 'imgEmboss':
			$emboss = array(array(-2, -1, 0), array(-1, 1, 1), array(0, 1, 2));
			imageconvolution($tmp, $emboss, 1, 0);
			break;
		case 'imgEverglow':
			imagefilter($tmp, IMG_FILTER_BRIGHTNESS, -30);
			imagefilter($tmp, IMG_FILTER_CONTRAST, -5);
			imagefilter($tmp, IMG_FILTER_COLORIZE, 30, 30, 0);
			break;
		case 'imgGrayscale':
			imagefilter($tmp, IMG_FILTER_GRAYSCALE);
			break;
		case 'imgGreen':
			imagefilter($tmp, IMG_FILTER_COLORIZE, 0, 100, 0);
			break;
		case 'imgMean':
			imagefilter($tmp, IMG_FILTER_MEAN_REMOVAL);
			break;
		case 'imgNegate':
			imagefilter($tmp, IMG_FILTER_NEGATE);
			break;
		case 'imgPink':
			imagefilter($tmp, IMG_FILTER_COLORIZE, 50, -50, 50);
			break;
		case 'imgPixelate':
			imagefilter($tmp, IMG_FILTER_PIXELATE, 20);
			break;
		case 'imgRed':
			imagefilter($tmp, IMG_FILTER_COLORIZE, 100, 0, 0);
			break;
		case 'imgRetro':
			imagefilter($tmp, IMG_FILTER_GRAYSCALE);
			imagefilter($tmp, IMG_FILTER_COLORIZE, 100, 25, 25, 50);
			break;
		case 'imgSelectiveBlur':
			imagefilter($tmp, IMG_FILTER_SELECTIVE_BLUR);
			break;
		case 'imgSepiaDark':
			imagefilter($tmp, IMG_FILTER_GRAYSCALE);
			imagefilter($tmp, IMG_FILTER_BRIGHTNESS,-30);
			imagefilter($tmp, IMG_FILTER_COLORIZE, 90, 55, 30);
			break;
		case 'imgSepiaLight':
			imagefilter($tmp, IMG_FILTER_GRAYSCALE);
			imagefilter($tmp, IMG_FILTER_COLORIZE, 90, 60, 40);
			break;
		case 'imgSmooth':
			imagefilter($tmp, IMG_FILTER_SMOOTH, 2);
			break;
		case 'imgSummer':
			imagefilter($tmp, IMG_FILTER_COLORIZE, 0, 150, 0, 50);
			imagefilter($tmp, IMG_FILTER_NEGATE);
			imagefilter($tmp, IMG_FILTER_COLORIZE, 25, 50, 0, 50);
			imagefilter($tmp, IMG_FILTER_NEGATE);
			break;
		case 'imgVintage':
			imagefilter($tmp, IMG_FILTER_BRIGHTNESS, 10);
			imagefilter($tmp, IMG_FILTER_GRAYSCALE);
			imagefilter($tmp, IMG_FILTER_COLORIZE, 40, 10, -15);
			break;
		case 'imgWashed':
			imagefilter($tmp, IMG_FILTER_BRIGHTNESS, 30);
			imagefilter($tmp, IMG_FILTER_NEGATE);
			imagefilter($tmp, IMG_FILTER_COLORIZE, -50, 0, 20, 50);
			imagefilter($tmp, IMG_FILTER_NEGATE );
			imagefilter($tmp, IMG_FILTER_BRIGHTNESS, 10);
			break;
		case 'imgYellow':
			imagefilter($tmp, IMG_FILTER_COLORIZE, 100, 100, -100);
			break;
		default:
			break;
	}
	imagejpeg($tmp, $filename_photo);
	imagedestroy($tmp);
}

if($config['chroma_keying'] == true) {
	$source = imagecreatefromjpeg($filename_photo);
	$source = ResizeJpgImage($source, 1500, 1000);
	imagejpeg($source, $filename_keying, 100);
	imagedestroy($source);
}

// image scale, create thumbnail
list($width, $height) = getimagesize($filename_photo);
$newwidth = 500;
$newheight = $height * (1 / $width * 500);
$source = imagecreatefromjpeg($filename_photo);
$thumb = imagecreatetruecolor($newwidth, $newheight);
imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
imagejpeg($thumb, $filename_thumb);
imagedestroy($source);
imagedestroy($thumb);

// insert into database
$images[] = $file;
file_put_contents('data.txt', json_encode($images));

// send imagename to frontend
echo json_encode(array('success' => true, 'img' => $file));

function ResizeJpgImage($image, $max_width, $max_height)
{
	$old_width  = imagesx($image);
	$old_height = imagesy($image);
	$scale      = min($max_width/$old_width, $max_height/$old_height);
	$new_width  = ceil($scale*$old_width);
	$new_height = ceil($scale*$old_height);
	$new = imagecreatetruecolor($new_width, $new_height);
	imagecopyresampled($new, $image, 0, 0, 0, 0, $new_width, $new_height, $old_width, $old_height);
	return $new;
}
?>
