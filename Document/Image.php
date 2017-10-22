<?php
namespace ImmersiveImageBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document
 */
class Image {
	const DOMAIN_UPLOADS = 'uploads';
	const DOMAIN_IMAGES = 'images'; 

	/**
	 * Variation grades. Dimensions will be attached(by ImageService) in this order
	 */
	private $grades = ['tiny'=>'', 'smaller'=>'', 'small'=>'', 'medium'=>'', 'normal'=>'', 'large'=>'', 'great'=>'', 'greatest'=>''];
	//will: tiny=>imgname-250x300.jpg, small=>300x500.jpg ... accordingly with current screen dimensions
	
	
	// если есть простая картинка вроде imagename.jpg, то будет храниться здесь. 
	//если нет, то будет подставляться максимально подходящая вариация (getGreat(), например). Последняя в списке.
	/**
	 * @MongoDB\Field(type="string")
	 */
	private $original = null; 
	
	/**
	 * @MongoDB\Id(strategy="INCREMENT")
	 */
	private $id; 
   /**
	* @MongoDB\Field(type="string")
 	*/
	private $name; //имя файла (например, file1);
	
   /**
	* @MongoDB\Field(type="string")
	*/
	private $extension; //расширение файла (например, jpg);
	
   /**
	* @MongoDB\Field(type="string")
	*/
	private $domain; //example: uploads, images. фактически - директория с изображениями	

	/**
	 * @MongoDB\EmbedMany(targetDocument="ImageVariation") 
	 */
	private $variations = array();

	public function setVariations($variations){
		$this->variations = $variations;
	}
	
	public function getVariations(){
		return $this->variations;
	}
	
	//ex: img1-200x150
	function addVariation($variationName){
		$this->variations[] = new ImageVariation($variationName);
	}
	
	function setName($name){
		$this->name = $name;
	}
	
	function getName(){
		return $this->name;
	}
	
	function setExtension($ext){
		$this->extension = $ext;
	}
	
	function getExtension(){
		return $this->extension;
	}
	
	function setDomain($domain){
		$this->domain = $domain;
	}
	
	function getDomain(){
		return $this->domain;
	}
	
	public function getGrades(){
		return $this->grades;
	}
	
	public function setGrades($grades){
		$this->grades = $grades;
	}
	
	
	public function getTiny(){
		return $this->getPath($this->grades['tiny']);
	}
	
	public function getSmaller(){
		return $this->getPath($this->grades['smaller']);
	}
	
	public function getSmall(){
		return $this->getPath($this->grades['small']);
	}
	
	public function getMedium(){
		return $this->getPath($this->grades['medium']);
	}
	
	public function getLarge(){
		return $this->getPath($this->grades['large']);
	}
	
	public function getGreat(){
		return $this->getPath($this->grades['great']);
	}
	
	public function getCommon(){
		return $this->getPath($this->grades['common']);
	}
	
	public function getGreatest(){
		return $this->getPath($this->grades['greatest']);
	}
	

	public function getOriginal(){
		if($this->original != null){
			return $this->getPath(null);
		} else{
			return $this->getGreat(); //maximum for device from variations
		}
	}
	public function getByGrade($grade){
		if($grade != null){
			$path  = $this->getPath($this->grades[$grade]);
		} else{
			$path  = $this->getOriginal();
		}
		return $path;
	}
	
	public function findVariation($width, $height){
		foreach($this->variations as $i=>$variation){
			if($variation->getWidth() == $width && $variation->getHeight() == $height){
				return $variation;
			}
		}
		return null;
	}
	
	public function getPrecise($width, $height){
		$variation = $this->findVariation($width, $height);
		if($variation != null){
			return $this->getPath($variation);
		}
		throw new \Exception("No variation for image ".$this->getName()." with dimensions(".$width.",".$height.") found");
	}

	
	private function getPath($variation){
		if($variation == null){
			return $this->getDomain()."/".$this->getName().".".$this->getExtension();
		} else{
			return $this->getDomain()."/".$this->getName()."-".$variation->getWidth()."x".$variation->getHeight().".".$this->getExtension();
		}
	}
	
	
	public function setOriginal($original){
		$this->original = $original;
	}
	
	public function __toString()
	{
		return $this->getOriginal(); //return maximum of available image for given screen
	}
}