Модуль для Symfony 3, работающем под управлением веб-сервера apache, позволяющий автоматически выбирать изображения нужных размеров для различных разрешений экрана 
посредством использования простого формата для имён изображений:

image-name.extension${tiny|smaller|small|medium|normal|large|great|greatest}

например,  image1.png$large или image1.png$small

Если суффикс ($great) не указан, то изображение будет отображаться без каких-либо обработок. 

Изображения должны храниться на диске с именами, например:
background.jpg  
background-116x95.jpg
background-150x100.jpg
background-200x155.jpg
background-900x800.jpg
background-1203x1000.jpg
background-2034x1806.jpg

Для указанного хэш-суффикса ($small, $normal) будет выбираться наиболее подходящее изображение из имеющихся.
 
 
Подключить модуль, добавив в src/AppKernel.php
  $bundles = [
    ....
    new ImmersiveImageBundle\ImmersiveImageBundle(),
  ];

  #в routing.yml
  immersive_image:
    resource: "@ImmersiveImageBundle/Controller/"
    type:     annotation
    prefix:   /

  #в config.yml, если предполагается использование контроллера /img в шаблонах twig 
  imports:
    - { resource: "@ImmersiveImageBundle/Resources/config/services.yml" }
  twig:
    globals:
      imageService: "@immersiveImage.services.imageService"    
    

Необходимо настроить .htaccess-файл, хранящийся в корне сайта/проекта.
В секцию mod_rewrite добавить команды: 

   RewriteEngine On
   #эти команды необходимы для работы модуля
   RewriteCond %{REQUEST_URI}::$1 ^(/.+)/(.*)::\2$
   RewriteRule ^(.*) - [E=BASE:%1]
   RewriteCond %{REQUEST_URI} (images|uploads)(\/.+)*\/(.+)[.](?:png|jpg|jpeg)(?:\$(\w+))
   RewriteRule (.*) %{ENV:BASE}/app.php/img?name=%3&domain=%1%2&grade=%4 [R]
   #stop rewrite processing when /img already approached
   RewriteCond expr "! %{REQUEST_URI} -strmatch '.*/img$'"
   RewriteRule .* - [END]
   #остальные инструкции

Пример работы модуля можно посмотреть на сайте http://liquid-crystal.ru, изменяя размер окна браузера и анализируя ссылки изображений 
