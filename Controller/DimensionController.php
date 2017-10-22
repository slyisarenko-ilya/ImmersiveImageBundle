<?php

namespace ImmersiveImageBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;


class DimensionController extends Controller{
	
	const LOG_PATH = '../../log/liquid-crystal.log';
	  
	private $logger;
    private function getLogger(): Logger{
    	if($this->logger == null){
    		$this->logger = new Logger(get_class());
    		$this->logger->pushHandler(new StreamHandler(self::LOG_PATH, Logger::WARNING));
    	}
    	return $this->logger;
    }
    
    /**
     * @Route("/dimensionajax", name="dimensionajax")
     */
     public function dimensionAjaxAction(Request $request): Response{
     	if($request->isXmlHttpRequest()){
     		$deviceWidth = $request->query->get('deviceWidth');
	     	$deviceHeight = $request->query->get('deviceHeight');
	     	$dimensionService = $this->get('immersiveImage.services.dimensionService');
	     	$dimensionService->setSession($request->getSession());
	     	$dimensionService->setDeviceWidth($deviceWidth);
	     	$dimensionService->setDeviceHeight($deviceHeight);
	     	$result = array("status"=>"success");
	    	return new JsonResponse($result);
     	} else{
     		$errmsg = 'Cannot execute ajax request directly';
     		throw $this->createNotFoundException($errmsg);
     	}
     }
	    
}
