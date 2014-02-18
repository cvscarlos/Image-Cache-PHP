<?php
/*
* @author: Carlos Vinicius
* @version 1.3 2012-09-07
*
* This work is licensed under the Creative Commons Attribution 3.0 Unported License. To view a copy of this license, 
* visit http://creativecommons.org/licenses/by/3.0/ or send a letter to Creative Commons, 444 Castro Street, Suite 900, Mountain View, California, 94041, USA.
*
* @Description: Classe para criar cache de imagens a partir de uma URL, ideal para site que usam imagens de terceiros como por exemplo do Flickr / Picasa
*
* @usage:   $img = new ImageCache("cache", "cache"); // Definindo o local físico do cache e a URL até ele
* @usage:   <img src = "<?php $img->printUrl("http://www.exemple.com/img.jpg", 72, 80, 80, true);? > " alt = "imagem" / > // Exemplo imagem redimensionada p/ 80 x 80 (cortada se necessário)
* @usage:   <img src = "<?php $img->printUrl("http://www.exemple.com/img.jpg");? > " alt = "imagem" / > // Exemplo imagem em tamanho original
*
*/

class ImageCache
{
	private $url;
	private $outUrl;
	private $width;
	private $height;
	private $imageGd2;
	private $crop;
	private $cacheDir;
	private $cacheUrl;
	private $newFileName;
	private $resolution;
	private $imageType;
	
	function __construct($cacheDir, $cacheUrl)
	{
		$this->cacheDir = $cacheDir;
		$this->cacheUrl = $cacheUrl;
	}
	
	public function printUrl($url, $resolution = 72, $width = 1600, $height = 1600, $crop = false)
	{
		$this->crop = $crop;
		echo $this->getUrl($url, $resolution, $width, $height, $crop);
	}
	
	public function getImgData()
	{
		return getimagesize($this->cacheDir."/".$this->newFileName);
	}
	
	public function getUrl($url, $resolution = 72, $width = 1600, $height = 1600, $crop = false)
	{
		$this->resolution = $resolution;
		$this->newFileName = sha1($url.$resolution.$width.$height.$crop).".jpg";
		$this->outUrl = $this->cacheUrl."/".$this->newFileName;
		if(!file_exists($this->cacheDir."/".$this->newFileName))
		{
			$this->crop = $crop;
			$this->width = $width;
			$this->height = $height;
			$this->url = $url;
			$this->resize();
		}
		return $this->outUrl;
	}

	// Este método faz o redimensionamento da imagem e retorna uma imagem ao browser
	public function outputImg($url, $resolution = 72, $width = 1600, $height = 1600, $crop = false)
	{
		$this->resolution = $resolution;
		$this->crop = $crop;
		$this->width = $width;
		$this->height = $height;
		$this->url = $url;
		$this->resize(false);

		if($this->imageType == 3)
		{
			header("Content-type: image/png");
			return imagepng($this->imageGd2);
		}
		elseif($this->imageType == 3)
		{
			header("Content-type: image/gif");
			return imagegif($this->imageGd2);
		}
		else
		{
			// Retornando no formato JPEG
			header("Content-type: image/jpeg");
			return imagejpeg($this->imageGd2, null, $this->resolution);
		}
	}
	
	private function resize($saveImg = true)
	{
		if($this->crop)
			$this->resizeCrop();
		else
			$this->resizeProportionally();

		if($saveImg)
			$this->saveImage();
	}
	
	private function resizeProportionally()
	{
		list($width_orig, $height_orig, $imageType) = getimagesize($this->url);
		$ratio_orig = $width_orig/$height_orig;

		// Compartilhando a informação do tipo de imagem com os demais métodos
		$this->imageType = $imageType;

		if($this->width < $width_orig || $this->height < $height_orig)
		{
			if ($this->width/$this->height > $ratio_orig)
				$this->width = $this->height*$ratio_orig;
			else
				$this->height = $this->width/$ratio_orig;
		}
		else
		{
			$this->width = $width_orig;
			$this->height = $height_orig;
		}

		$image_p = imagecreatetruecolor($this->width, $this->height);

		// Mantendo as definições de trasnparências da imagem
		if($imageType == 1 || $imageType == 3)
		{
			imagealphablending($image_p, false);
			imagesavealpha($image_p,true);
		}
		
		switch($imageType)
		{
			case 1:
				$image = imagecreatefromgif($this->url);
				break;
			case 2:
				$image = imagecreatefromjpeg($this->url);
				break;
			case 3:
				$image = imagecreatefrompng($this->url);
				break;
		}
		imagecopyresampled($image_p, $image, 0, 0, 0, 0, $this->width, $this->height, $width_orig, $height_orig);
		$this->imageGd2 = $image_p;
	}
	
	private function resizeCrop()
	{
		$width_crop = $this->width;
		$height_crop = $this->height;
		list($width_orig, $height_orig, $imageType) = getimagesize($this->url);
		$tmpSize = ($width_orig > $height_orig?$width_orig:$height_orig)*2; //para que W ou H (o que tiver o menor valor) seja igual a dimensão definida para o quadrado ($width_crop)
		
		if($width_orig > $height_orig)
		{
			$width = $tmpSize;
			$height = $width_crop;
		}
		elseif($width_orig == $height_orig)
		{
			$width = $width_crop;
			$height = $width_crop;
		}
		else
		{
			$width = $width_crop;
			$height = $tmpSize;
		}

		$ratio_orig = $width_orig/$height_orig;

		if ($width/$height > $ratio_orig)
			$width = $height*$ratio_orig;
		else
			$height = $width/$ratio_orig;

		$image_p = imagecreatetruecolor($width_crop, $height_crop);
		switch($imageType)
		{
			case 1:
				$image = imagecreatefromgif($this->url);
				break;
			case 2:
				$image = imagecreatefromjpeg($this->url);
				break;
			case 3:
				$image = imagecreatefrompng($this->url);
				break;
		}

		if($width_orig > $height_orig)
		{
			$width_crop_end = -($width-$height)/2;
			$height_crop_end = 0;
		}
		elseif($width_orig == $height_orig)
		{
			$width_crop_end = 0;
			$height_crop_end = 0;
		}
		else
		{
			$width_crop_end = 0;
			$height_crop_end = -($height-$width)/2;
		}
		imagecopyresampled($image_p, $image, $width_crop_end, $height_crop_end, 0, 0, $width, $height, $width_orig, $height_orig);
		$this->imageGd2 = $image_p;
	}
	
	private function saveImage()
	{
		imagejpeg($this->imageGd2, $this->cacheDir."/".$this->newFileName, $this->resolution);
	}
}