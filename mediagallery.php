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

 $doc = JFactory::getDocument();
 $baseUrl = JUri::base();

 $doc->addScript($baseUrl.'/plugins/content/mediagallery/libs/jquery-3.4.1.min.js');
 $doc->addStyleSheet($baseUrl.'/plugins/content/mediagallery/libs/mediagallery.css');
 $doc->addScript($baseUrl.'/plugins/content/mediagallery/libs/mediagallery.js');

 
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

     /**
     * 
     * Get params 
     * 
     */
    
  private function parsPageToArticle($matches, $article) {   

    foreach( $matches as $match ) {

      $url = $this->getUrl($match[0], "url"); // получаем url

      $url = $this->parseUrl($url);

      $appendHtml = '';

      if ($url != '') {

        $html = file_get_html( $url ); // создание объекта парсера и получение HTML   

        $appendHtml = $appendHtml . '<h4>'. urldecode($url) . '</h4>' . '<div class="mediagallery__container">';

        foreach ( $html -> find ('a') as $element ) { // находим все ссылки 

          $filename = urldecode( $element->href );

            if ( preg_match('/^.*\.(jpg|jpeg|png|gif|bmp)$/i', $filename) ) {

              $appendHtml = $appendHtml . 
              '<div class="mediagallery__item"><img class="mediagallery__image" src="' .
              urldecode($url) .
              $filename . 
              '"' . 
              '></div>'; 

            } else {
                    
              if ( preg_match('/^.*\.(mp4|webm|ogg|avi|mpeg|mpg|mkv|mov|webm|wmv)$/i', $filename) ) {

                $path_info = pathinfo($filename);
                $expansion = $path_info['extension'];
                $appendHtml = $appendHtml .
                '<div class="mediagallery__item"><video class="mediagallery__video" controls controlsList="nodownload">' .
                '<source type="video/' .
                $expansion . 
                '"' . 
                ' src="' . 
                urldecode($url) . 
                $filename . 
                '"></video></div>';

              }                
            }
        } 
      }
      
      $appendHtml = $appendHtml . '</div>';
      $article->text = preg_replace($this->regex, $appendHtml, $article->text, 1);         
    }        
  }


  function parseUrl($url) {

    $url = parse_url($url);

    $arr = explode('/', $url['path']);

    $coded = array_map('rawurlencode', $arr);

    $restored = 'http://' . $url['host'] . implode('/', $coded);

    $url = $restored;

    return $url; 

  }

  function getUrl($url = '', $key = '', $value = '') {

    if (preg_match("/". $key . "\s*=\s*(\"(.*?)\")|('(.*?)')/si", $url, $matches)) {

     $value = $matches[2];

    }

     return $value;

  }

}

?>