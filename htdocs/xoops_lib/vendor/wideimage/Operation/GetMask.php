<?php
/*
	WideImage_for_xoops
	By CHG-WEB
	2012/04/08
	Cédric MONTUY (montuy337513 / black_beard)
	Original author : Gaspar Kozak
*/
if (!defined('XOOPS_ROOT_PATH')) {
	die("XOOPS root path not defined");
}
class WideImage_Operation_GetMask{
	function execute($image){
		$width = $image->getWidth();
		$height = $image->getHeight();
		$mask = WideImage_TrueColorImage::create($width, $height);
		$mask->setTransparentColor(-1);
		$mask->alphaBlending(false);
		$mask->saveAlpha(false);
		for ($i = 0; $i <= 255; $i++)
			$greyscale[$i] = ImageColorAllocate($mask->getHandle(), $i, $i, $i);
		imagefilledrectangle($mask->getHandle(), 0, 0, $width, $height, $greyscale[255]);
		$transparentColor = $image->getTransparentColor();
		$alphaToGreyRatio = 255 / 127;
		for ($x = 0; $x < $width; $x++)
			for ($y = 0; $y < $height; $y++){
				$color = $image->getColorAt($x, $y);
				if ($color == $transparentColor)
					$rgba['alpha'] = 127;
				else
					$rgba = $image->getColorRGB($color);
				imagesetpixel($mask->getHandle(), $x, $y, $greyscale[255 - round($rgba['alpha'] * $alphaToGreyRatio)]);
			}
		return $mask;
	}
}
?>