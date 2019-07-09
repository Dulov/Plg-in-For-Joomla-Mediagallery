<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  ContentMediaGallery
 *
 * @copyright   Copyright (C) 2019 Dulov Aleksandr. All rights reserved.
 * @license     http://vgiksp.ru/
 */

 defined('_JEXEC') or die;
 include('/libs/simple_html_dom.php');
/**
 * Plug-in to enable loading modules into content (e.g. articles)
 * This uses the {mediagallery url="URL"} syntax
 *
 * @since  1.5
 */

class PlgContentMediaGallery extends JPlugin {
    /**
     * Affects constructor behavior. If true, language files will be loaded automatically.
     *
     * @var    boolean
     * @since  3.1
     */
    protected $autoloadLanguage = true;
    
    /**
     * Shorcode regex
     *
     * @var    string
     */

    private $regex = "/{mediagallery \s*(.*?)}/si";
    
    public function __construct(&$subject, $config = array()) {
      parent::__construct($subject, $config);
    }

    /**
     * 
     * Get params 
     * 
     */

    function getUrl($url = '', $key = '', $value = '') {
   
      if (preg_match("/". $key . "\s*=\s*(\"(.*?)\")|('(.*?)')/si", $url, $matches)) {
        $value = $matches[2];
      }
        return $value;
    } 
  
    /**
     * Plugin that show hide text within content
     *
     * @param   string   $context   The context of the content being passed to the plugin.
     * @param   object   &$ar*ticle  The article object.  Note $article->text is also available
     * @param   mixed    &$params   The article params
     * @param   integer  $page      The 'page' number
     *
     * @return  mixed   true if there is an error. Void otherwise.
     *
     * @since   1.6
     */
    
    public function onContentPrepare($context, &$article, &$params, $page = 0) {

      preg_match_all($this->regex, $article->text, $matches, PREG_SET_ORDER);
        
        if ($matches) {
            $this->parsPageToArticle($matches, $article);
            return true;
        } else {
          $article->text = preg_replace($this->regex, '', $article->text, 1);
          return true;
          }
    }
    
    private function parsPageToArticle($matches, $article) {   

      foreach( $matches as $match ) {
         $url = $this->getUrl($match[0], "url"); // получаем url
          $appendHtml = '';
          if ($url != '') {
            $html = file_get_html( $url ); // создание объекта парсера и получение HTML   
            $appendHtml = $appendHtml . '<h4>'. urldecode($url) . '</h4>' . '<div class="top-content">';
              foreach ( $html -> find ('a') as $element ) { // находим все ссылки    
                 $filename = urldecode($element -> href );
                  if ( preg_match('/^.*\.(jpg|jpeg|png|gif|bmp)$/i', $filename) ) {
                    $appendHtml = $appendHtml . '<div class="div_content"><img class="image" src="' . urldecode($url) . $filename . '"' . '></div>'; 
                  } else {
                    
                        if (preg_match('/^.*\.(mp4|webm|ogg|avi|mpeg|mpg|mkv|mov|webm|wmv)$/i', $filename)) {
                          $path_info = pathinfo($filename);
                          $expansion = $path_info['extension'];
                          $appendHtml = $appendHtml . '<div class="div_content"><video controls="controls">' . '<source type="video/' . $expansion .'"'. ' src="' . urldecode($url) . $filename . '"></video></div>';
                        }                
                    }
                } 
            }
            $appendHtml = $appendHtml . '</div>';
          $article->text = preg_replace($this->regex, $appendHtml, $article->text, 1);         
        }        
    }
}  
?>

<style type="text/css">
  .top-content {
      text-align: center;
  }

  .div_content {
      display: inline-block;
      margin: 0px 0px 5px 5px;
      min-height: 200px;
      width: 300px;
      text-align: center;
  }

  .div_content video {
      border-radius: 10px;
      width: 300px;
      height: 100%;
  }

  .div_content img {
      border-radius: 10px;
      width: 300px;
      height: 200px;
  }

  .image {
      cursor: pointer;
  }

  .popup {
      position: absolute;
      height: 100%;
      width: 100%;
      top: 0;
      left: 0;
      display: none;
 
  }

  .popup_bg {
      background: rgba(0, 0, 0, 0.4);
      position: fixed;
      z-index: 1;
      height: 100%;
      width: 100%;
  }


  .popup_img {
      position: fixed;
      z-index: 2;
      max-height: 100%;
      max-width: 100%;
      margin: 5% 0% 0% 0%;
  }

  .popup_img img {
      text-align: center;
  }
</style>

<script type="text/javascript">
$(document).ready(function() { // Ждём загрузки страницы

    $(".image").click(function() { // Событие клика на маленькое изображение
        var img = $(this); // Получаем изображение, на которое кликнули
        var src = img.attr('src'); // Достаем из этого изображения путь до картинки
        $("body").append("<div class='popup'>" + //Добавляем в тело документа разметку всплывающего окна
            "<div class='popup_bg'></div>" + // Блок, который будет служить фоном затемненным
            "<img src='" + src + "' class='popup_img' />" + // Само увеличенное фото
            "</div>");
        $(".popup").fadeIn(800); // Медленно выводим изображение
        $(".popup_bg").click(function() { // Событие клика на затемненный фон    
            $(".popup").fadeOut(800); // Медленно убираем всплывающее окно
            setTimeout(function() { // Выставляем таймер
                $(".popup").remove(); // Удаляем разметку всплывающего окна
            }, 800);
        });

        $(".popup_img").click(function() { // Событие клика на картинку   
            $(".popup").fadeOut(800); // Медленно убираем всплывающее окно
            setTimeout(function() { // Выставляем таймер
                $(".popup").remove(); // Удаляем разметку всплывающего окна
            }, 800);
        });
    });

});
</script>
