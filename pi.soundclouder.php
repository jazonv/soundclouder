<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Soundclouder Class
*
* @package     ExpressionEngine
* @category    Plugin
* @author      Jason Varga
* @copyright   Copyright (c) 2011, Jason Varga
* @link        http://jvdesigns.com.au/
*/

$plugin_info = array(
  'pi_name'         => 'SoundClouder',
  'pi_version'      => '1.0',
  'pi_author'       => 'Jason Varga',
  'pi_author_url'   => 'http://jvdesigns.com.au/',
  'pi_description'  => 'Takes a SoundCloud URL and outputs a player or API URL.',
  'pi_usage'        => Soundclouder::usage()
);

class Soundclouder {

  public function __construct()
  {
    
    $this->EE =& get_instance();
    
    // get parameters
    $entered_url = $this->EE->TMPL->fetch_param('url');
    $show_player = $this->EE->TMPL->fetch_param('show_player');
    $player_type = $this->EE->TMPL->fetch_param('type');
    $player_height = ($this->EE->TMPL->fetch_param('height')) ? $this->EE->TMPL->fetch_param('height') : 180;
    $auto_play = ($this->EE->TMPL->fetch_param('auto_play')) ? $this->EE->TMPL->fetch_param('auto_play') : false;
    $show_artwork = ($this->EE->TMPL->fetch_param('show_artwork')) ? $this->EE->TMPL->fetch_param('show_artwork') : false;
    $color = $this->EE->TMPL->fetch_param('color');
    $player_class = $this->EE->TMPL->fetch_param('class');
    $api_key = ($this->EE->TMPL->fetch_param('api_key')) ? $this->EE->TMPL->fetch_param('api_key') : "apigee";
    
    // resolve url
    $api_url = $this->resolveURL($entered_url, $api_key);
    
    if ($show_player == true) {
      if ($player_type == "html5") {
        // build html5 player code
        $player_code = 
          '<iframe '.
            'width="100%" height="'.$player_height.'" class="'.$player_class.'" scrolling="no" frameborder="no" '.
            'src="http://w.soundcloud.com/player/?url='.$api_url.'&amp;auto_play='.$auto_play.'&amp;show_artwork='.$show_artwork.'&amp;color='.$color.'&amp;download=false">'.
          '</iframe>';
      }
      else {
        // build flash player embed code
        $player_code =
          '<div class='.$player_class.'>'.
            '<object height="225" width="100%">'.
              '<param name="movie" value="https://player.soundcloud.com/player.swf?url='.$api_url.'&secret_url=true"></param> '.
              '<param name="allowscriptaccess" value="always"></param> '.
              '<embed allowscriptaccess="always" height="225" src="https://player.soundcloud.com/player.swf?url='.$api_url.'&secret_url=true" type="application/x-shockwave-flash" width="100%"></embed> '.
            '</object>';
          '</div>';
      }
      $this->return_data = $player_code;
    }
    else {
      // just return the url
      $this->return_data = $api_url; 
    }
    
  }
  
  public function resolveURL($url, $api_key) {
    // set url structure
    $resolver = 'http://api.soundcloud.com/resolve.json?url=';
    $params = 'format=json&consumer_key=' . $api_key;
    // build url
    $resolver_api_url = $resolver . $url . '&' . $params;
    // get the headers for the URL, it's going to be a 302 redirect    
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $resolver_api_url);
    curl_setopt($curl, CURLOPT_NOBODY, true);
    curl_setopt($curl, CURLOPT_HEADER, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $headers = curl_exec($curl);
    curl_close($curl);
    // parse the headers and get the 'Location' part
    $parsed_headers = $this->http_parse_headers($headers);
    return $parsed_headers['Location'];
  }
  
  public function http_parse_headers($header)
  {
      $retVal = array();
      $fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header));
      foreach( $fields as $field ) {
          if( preg_match('/([^:]+): (.+)/m', $field, $match) ) {
              $match[1] = preg_replace('/(?<=^|[\x09\x20\x2D])./e', 'strtoupper("\0")', strtolower(trim($match[1])));
              if( isset($retVal[$match[1]]) ) {
                  $retVal[$match[1]] = array($retVal[$match[1]], $match[2]);
              } else {
                  $retVal[$match[1]] = trim($match[2]);
              }
          }
      }
      return $retVal;
  }
  
  // Usage
  public static function usage()
  {
    ob_start();
  ?>
  
This will accept a SoundCloud URL and return the API equivalent for you.
{exp:soundclouder url="http://soundcloud.com/matas/hobnotropic"}
will return http://api.soundcloud.com/tracks/49931.

{exp:soundclouder
  url="http://soundcloud.com/matas/hobnotropic"
  player="yes"
  type="html5"
  height="500"
  color="0ff"}
will return you a red, 500 pixel high, HTML5 version of the player.

  
  <?php
    $buffer = ob_get_contents();
    ob_end_clean();
    return $buffer;
  }

}

/* End of file pi.soundclouder.php */
/* Location: ./system/expressionengine/third_party/soundclouder/pi.soundclouder.php */