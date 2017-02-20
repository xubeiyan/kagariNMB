<?php
class ImageThumb {
	// 请求$name值的图像
	public static function request($name) {
		global $config;
		$path = $config['thumbDir'] . '//' . $name;
		if (file_exists($path)) {
			$nameArray = explode('.', $name);
			self::imageType($nameArray[1]);
			echo file_get_contents($path);
			exit();
		}
		
		
		// 不存在时则需要从后台图片地址读取
		$nameArray = explode('.', $name);
		if (!isset($nameArray[1])) {
			$nameArray[1] = 'jpg';
		}
		if ($nameArray[1] == 'jpg' || $nameArray[1] == 'jpeg') {
			$image = imagecreatefromjpeg($config['imgURI'] . $name);
		} else if ($nameArray[1] == 'png') {
			$image = imagecreatefrompng($config['imgURI'] . $name);
		} else if ($nameArray[1] == 'gif') {
			$image = imagecreatefromgif($config['imgURI'] . $name);
		}
		
		if (!$image) {
			die('Seem not to be able to open image on "' . $config['imgURI'] . $name . '"');
		}
		
		self::imageType($nameArray[1]);
		$width = imagesx($image);
		$height = imagesy($image);
		if ($width < $height) {
			$newWidth = $config['thumbSize'] * $width / $height;
			$newHeight = $config['thumbSize'];
		} else {
			$newWidth = $config['thumbSize'];
			$newHeight = $config['thumbSize'] * $height / $width;
		}
		// print_r($path);
		// exit();
		$newImage = imagecreatetruecolor($newWidth, $newHeight);
		imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
		
		if ($nameArray[1] == 'jpg' || $nameArray[1] == 'jpeg') {
			imagejpeg($newImage, $path);
		} else if ($nameArray[1] == 'png') {
			imagepng($newImage, $path);
		} else if ($nameArray[1] == 'gif') {
			imagegif($newImage, $path);
		}
		imagedestroy($image);
		imagedestroy($newImage);
		echo file_get_contents($path);
		
		exit();
	}
	
	private static function imageType($extname) {
		if ($extname == 'jpg' || $extname == 'jpeg') {
			header('Content-Type:image/jpeg');
		} else if ($extname == 'png') {
			header('Content-Type:image/png');
		} else if ($extname == 'gif') {
			header('Content-Type:image/gif');
		}
	}
	
}
?>