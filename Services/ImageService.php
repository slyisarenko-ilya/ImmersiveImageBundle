<?php

namespace ImmersiveImageBundle\Services;

use ImmersiveImageBundle\Document\Image;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class ImageService{
	private $mongoService;
	private $dimensionService;
	private $kernelRootDir;

	const LOG_PATH = '../../log/liquid-crystal.log';
	 
	private $logger;
	
	private function getLogger(): Logger{
		if($this->logger == null){
			$this->logger = new Logger(get_class());
			$this->logger->pushHandler(new StreamHandler(self::LOG_PATH, Logger::DEBUG));
		}
		return $this->logger;
	}
	
	public function __construct($mongoService, $dimensionService, $kernelRootDir){
		
		$this->mongoService = $mongoService;
		$this->dimensionService = $dimensionService;
		$this->kernelRootDir = $kernelRootDir;
	}
	
	//$this->get('kernel')->getRootDir()."../web/"...;
	public function wrap($image){
		$this->recalcIndistincts($image);
		return $image;
	}
	
	public function get($name, $domain){
		
		//load from mongo
		$image = null;
		//$image = $this->findInDb($name, $domain);
// 		if($image != null){
// 			$this->recalcIndistincts($image);
// 			return $image;
// 		}
		$image = $this->findInFilesystem($name, $domain);
		$this->recalcIndistincts($image);
		
// 		if($image != null){
// 			//store in database
// 			$this->save($image);
// 			return $image;
// 		}
		return $image;
		throw new \Exception("No image found");
	}
	
	
	private function findInDb($name, $domain){
		$images = $this->mongoService
		->getRepository('AppBundle:Image')
		->findBy(
		    array('name' => $name),
		    array('domain' => $domain)
		);
		if(count($images) > 0){
			return $images[0];
		}
		return null;
	}

	private function findInFilesystem($name, $domain){
	 	$path = $this->kernelRootDir."/../web/".$domain.'/.';
	 	$ext = null;
	 	$variations = array();
	 	$im = new Image();
	 	$im->setName($name);
	 	$im->setDomain($domain);
	 	 
	 	foreach (new \DirectoryIterator($path) as $file) {
	 		if($file->isDot()) continue;
	 		$fname = $file->getFilename();
	 		//$this->getLogger()->debug($file);
	 		
	 		//detect fname.ext, without variation part
	 		if($this->startsWith($fname, $name.".")){
	 			$im->setOriginal($name); 			
	 			$parts = pathinfo($fname);
	 			$ext = $parts['extension'];
	 		} else
		 		if($this->startsWith($fname, $name)){
	 				if($this->isVariation($fname, $name)){
						$variations[] = $fname;
						$parts = pathinfo($fname);
						$ext = $parts['extension'];
	 				}
		 		}
	 	}
	 	 
	 	//assign variations
	 	if(count($variations) > 0){
	 		$im->setExtension($ext);
	 		for($i = 0; $i < count($variations); $i++){
	 			$parts = pathinfo($variations[$i]);
	 			$im->addVariation($parts['filename']);
	 		}
	 		return $im;
	 	}
	 	 if($im->getOriginal() != null){
	 		$im->setExtension($ext);
	 		return $im;
	 	}
	 	 return null;
	}
	
	function startsWith($haystack, $needle)	{
		$length = strlen($needle);
		return (substr($haystack, 0, $length) === $needle);
	}
	
	function isVariation($fileName, $testedName)	{
		$matched = preg_match('/'.$testedName.'[-]\d+[x]\d+[.]\w+/', $fileName, $matches, PREG_OFFSET_CAPTURE);
		
		return $matched != 0;
	}
	/**
	 * Сохраняет экземпляр изображения в базе данных.
	 * Если экземпляр с таким именем существует, то существующий будет перезаписан.
	 */
	public function save(Image $image){
		$dm = $this->mongoService->getManager();
		$dm->persist($image);
		$dm->flush();
	}
	
	/**
	 * Служебный метод. 
	 * Разбирает путь к изображению на составные части.
	 * Возвращает массив.
	 */
	public  function parsePath($imagePath){
		$parts = pathinfo($imagePath);
		return array($parts['dirname'], $parts['filename'], $parts['extension']);
	} 
	
	
	/**
	 * Calc indistincts getters accortingly with current dimensions.
	 **/
	public function recalcIndistincts(Image $image){
		$grades = $image->getGrades(); 
		$variations = $image->getVariations();
		if(count($variations) > 0){ //don't start if no variations 
			$deviceWidth = $this->dimensionService->getDeviceWidth();
			$deviceHeight = $this->dimensionService->getDeviceHeight();

			$this->getLogger()->debug("imn: ".$image->getName().", is: w:".$deviceWidth.",h:".$deviceHeight);
			
			//fill points with approriate for small, medium and other indirect metrics 
			$gradesCount = count($grades);
			$j = 1;
			foreach($grades as $gradeName=>$gradeValue){
				$x = $deviceWidth / $gradesCount * $j;
				$y = $deviceHeight / $gradesCount * $j;
				//now find points with min distances for every point
				$minDist = null;
				$minVariationPoint = null;
				foreach($variations as $t=>$variation){
					
					$vx = $variation->getWidth();
					$vy = $variation->getHeight();
					$dist = sqrt(($x-$vx)*($x-$vx) + ($y-$vy)*($y-$vy));
					if($minDist == null){
						$minDist = $dist;
						$minVariation = $variation;
					} else{
						if($minDist > $dist){
							$minDist = $dist;
							$minVariation = $variation; 
						}
					}
				}
				$grades[$gradeName] = $image->findVariation($minVariation->getWidth(), $minVariation->getHeight());
				$j ++;
			}
			$image->setGrades($grades);
		}
	}
}