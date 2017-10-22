<?php

namespace ImmersiveImageBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class ImageController extends Controller{
	
	const LOG_PATH = '../../log/liquid-crystal.log';
	  
	private $logger;
	
    
    private function getLogger(): Logger{
    	if($this->logger == null){
    		$this->logger = new Logger(get_class());
    		$this->logger->pushHandler(new StreamHandler(self::LOG_PATH, Logger::DEBUG));
    	}
    	return $this->logger;
    }
    
     
    /**
     * @Route("/img", name="image")
     */
    public function indexAction(Request $request): Response {
    	 
    	$baseImagePath = $this->container->get('kernel')->getProjectDir()."/web/";

    	$imgName = $request->get("name");
    	$imgDomain = $request->get("domain");
    	$imgRelPath = $imgDomain."/".$imgName;
    	$grade = $request->get("grade");
    	
    	$imageService = $this->get('immersiveImage.services.imageService');
    	$dimensionService = $this->get('immersiveImage.services.dimensionService');
    	//if(!$dimensionService->isInitialized()){
    	$dimensionService->rememberDimensions($request);
    	//}

    	$this->getLogger()->debug("im:".$imgName.",w:".$dimensionService->getDeviceWidth().",h:".$dimensionService->getDeviceHeight());
    	 
    	$image = $imageService->get($imgName, $imgDomain);
    	$variationPath = $image->getByGrade($grade);
    	$filePath = $baseImagePath.$variationPath;
    	
    	$response = new BinaryFileResponse($variationPath);
     	$response->headers->set('Content-Type', 'image/' . $this->getImageMimeType($variationPath));
     	$response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE);
    	return $response;
    }
    
    private function getImageMimeType($filePath){
    	$mt = exif_imagetype ($filePath);
    	$mimeType = image_type_to_mime_type($mt);
    	return $mimeType;
    }
    
}
