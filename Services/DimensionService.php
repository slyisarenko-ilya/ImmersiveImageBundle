<?php
namespace ImmersiveImageBundle\Services;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class DimensionService{
	const DEVICE_WIDTH = "DEVICE_WIDTH";
	const DEVICE_HEIGHT = "DEVICE_HEIGHT";
	const LOG_PATH = '../../log/liquid-crystal.log';
	 
	private $logger;
	
	
	private function getLogger(): Logger{
		if($this->logger == null){
			$this->logger = new Logger(get_class());
			$this->logger->pushHandler(new StreamHandler(self::LOG_PATH, Logger::DEBUG));
		}
		return $this->logger;
	}
	private $session = null; 
	
	const DEFAULT_WIDTH = 1920;
	const DEFAULT_HEIGHT = 1280;
	
	public function rememberDimensions(Request $request){
		//store session object
		$cookies = $request->cookies;
		if ($cookies->has(self::DEVICE_WIDTH) && $cookies->has(self::DEVICE_HEIGHT)){
			$this->getSession()->set(self::DEVICE_WIDTH, $cookies->get(self::DEVICE_WIDTH));
			$this->getSession()->set(self::DEVICE_HEIGHT, $cookies->get(self::DEVICE_HEIGHT));
			
			$this->getLogger()->debug("remember dimensions: ".$cookies->get(self::DEVICE_WIDTH).":".$cookies->get(self::DEVICE_HEIGHT));
		}
	}
	
	public function getDeviceWidth(){
		if($this->getSession() == null){
			return self::DEFAULT_WIDTH;
		}
		return $this->getSession()->get(self::DEVICE_WIDTH);	
	}
	
	public function getDeviceHeight(){
		if($this->getSession() == null){
			return self::DEFAULT_HEIGHT;
		}
		return $this->getSession()->get(self::DEVICE_HEIGHT);
	}
	
	public function getSession(){
		if($this->session == null){
			$this->session = new Session();
// 			$this->getLogger()->debug("new Session: ". $this->session->getId());
// 			if(!$this->session->isStarted()){
// 				$this->session->start();
// 			}
		}/*  else{
			$this->getLogger()->debug("reuse Session: ". $this->session->getId());
		} */
		return $this->session;
	}
	
	public function isInitialized(){
		return $this->getDeviceHeight() !== null && $this->getDeviceWidth() !== null; 
	}
}