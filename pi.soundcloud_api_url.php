<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Soundcloud_api_url Class
*
* @package     ExpressionEngine
* @category    Plugin
* @author      Jason Varga
* @copyright   Copyright (c) 2011, Jason Varga
* @link        http://jvdesigns.com.au/
*/

$plugin_info = array(
  'pi_name'         => 'SoundCloud URL to API converter',
  'pi_version'      => '1.0',
  'pi_author'       => 'Jason Varga',
  'pi_author_url'   => 'http://jvdesigns.com.au/',
  'pi_description'  => 'Accepts a URL and returns the API equivalent',
  'pi_usage'        => Soundcloud_api_url::usage()
);

class Soundcloud_api_url {

  public function __construct()
  {
    $this->EE =& get_instance();
    // get URL from between tags
    $entered_url = $this->EE->TMPL->tagdata;
    // api is hardcoded for now
    $api_key = "apigee";
    // resolve url
    $api_url = $this->resolveURL($entered_url, $api_key);
    // plugin output
    $this->return_data = $api_url; 
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
{exp:soundcloud_api_url}http://soundcloud.com/matas/hobnotropic{/exp:soundcloud_api_url}
Will return http://api.soundcloud.com/tracks/49931
  
  <?php
    $buffer = ob_get_contents();
    ob_end_clean();
    return $buffer;
  }

}

/* End of file pi.soundcloud_api_url.php */
/* Location: ./system/expressionengine/third_party/soundcloud_api_url/pi.soundcloud_api_url.php */