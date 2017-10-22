<?php

namespace ImmersiveImageBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ImmersiveImageBundle\Services\DimensionService;

class DefaultController extends Controller
{

// при помощи .htaccess , либо аналогичного механизма нужно подменять url обычных изображений 
// на путь к контроллеру обработки изображений. 
// и то только в том случае, если модуль включён. 
// Т.е., возможно, необходимо парсить и изменять .htaccess файл в зависимости от активности этого модуля.
// хотя поискать другие способы тоже не помешает 

	/**
	 *
	 * @Route("/", name="homepage")
	 */
	public function indexAction(Request $request): Response {
		// 		1. кукисам назначаю срок истекания - неск секунд. Ну, страница не предполагается к перезагрузке.
		// 		2. проверяю кукисы. если ещё свежие, значит только что перечитывали размеры
		// 		   если не свежие, то запоминаю размеры экрана заново и перенаправляю на эту же страницу
		 
		// 		3*. если в браузере отключены кукисы то не допустить бесконечного релоада.
		// 		   например, сохранять в другом доступном хранилище браузера.
		//Ну и метод homeAction убрать. перенести всё в этот обработчик.
		// 		die('prevent infinite reloading');
		$cookies = $request->cookies;
		if($cookies->get(DimensionService::DEVICE_WIDTH) && $cookies->get(DimensionService::DEVICE_HEIGHT)){
			//для этого метода необходимо чтобы кукисы были сохранены уже клиентской стороной (dimension-save.html.twig шаблон делает чудо).
			$this->get('immersiveImage.services.dimensionService')->rememberDimensions($request);
			//     	echo("device width: " .$this->get('app.services.dimensionService')->getDeviceWidth());
			//     	echo("device height: " .$this->get('app.services.dimensionService')->getDeviceHeight());

			//параметры передаём дальше, нормальному контроллеру
			$parameters = array();//$this->getRequest()->query->all();
			//передать как параметр ? этот хоме хандлер
			$response = $this->forward('AppBundle:Default:home', $parameters);
			
			// ... further modify the response or return it directly
			
			return $response;
			
			//а всё остальное перенести в ImmersiveImageHandler
		} else{
			return $this->grabDimensions($request);
		}
	}
	
	
	public function grabDimensions(Request $request): Response {
		return $this->render('@ImmersiveImageBundle/Resources/views/service/dimensions-save.html.twig');
	}
}
