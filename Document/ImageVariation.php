<?php
namespace ImmersiveImageBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\EmbeddedDocument
 */
class ImageVariation {
	/**
	 * @MongoDB\Field(type="float")
	 */
	private $width;
 
	
	/**
	 * @MongoDB\Field(type="float")
	 */
	private $height;

	function __construct($variationName){
		list($this->width, $this->height) = $this->parseVariation($variationName);
	}
	

    public function getWidth(){
        return $this->width;
    }

    public function getHeight(){
        return $this->height;
    }
    
    
    
    private function parseVariation($variationName){
    	$matches = array();
    	preg_match("/([^-]+)-(\d+)x(\d+)/", $variationName, $matches);
    	$name = $matches[1];
    	$w = $matches[2];
    	$h = $matches[3];
    	assert(is_numeric($w));
    	assert(is_numeric($h));
    	return array($w, $h);
    }
}