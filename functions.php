<?php
// FB_TOKEN: add_id|app_secret
define('FB_TOKEN', 'access_token=1526653190913520|e59a85a79617cfe65417ea0f6de7f3da');
define('FB_URL', 'https://graph.facebook.com/v2.5/');
define('TWIGL_TEMPLATE_ROOT', 'template');

define('CACHE_ROOT_TEMPLATE_RENDERING', '/cache/template/');
define('CACHE_ROOT_URL_CALL', '/cache/url/');

define('CACHE_TIME_TEMPLATE_RENDERING', NULL);
define('CACHE_TIME_FB_API_CALL', NULL); // NULL, '-6h'

require __DIR__ . '/vendor/autoload.php';

function parse_images($array, $field, $value) {
  $max = $best = $array[0];
  $f = $best[$field] - $value;
  $bestfound = false;
  if ( $f == 0 ) {
    $bestfound = true;
  }

  $c = count($array);
  for ($i = 1; $i < $c; ++$i) {

    $next = $array[$i];
    $nextval = $next[$field];

    // max
    if($nextval > $max[$field]) $max = $next;

    // best
    if($bestfound) continue;
    $nf = $nextval - $value;
    if( $nf == 0 ) {
      $best = $next;
      $f = $nf;
      continue;
    }
    if($f < 0) {
      if($nf > 0 || $nf > $f) {
        $best = $next;
        $f = $nf;
        continue;
      }
    } else {
      if($nf > 0 && $nf < $f) {
        $best = $next;
        $f = $nf;
        continue;
      }
    }
  }
  $result = array();
  $result['fit'] = $best;
  $result['max'] = $max;
  return $result;
}

function filter_objects($array, $field, $strval, $top=1000) {

  function filter_objects_match($line,$exp) {
    $explen = strlen($exp);
    $a1 = $exp[0] === '*';
    $a2 = $exp[$explen - 1] === '*';
    if( $a1 && $a2 ) {
      $exp = substr( $exp, 1, $explen - 2 );
      return (strpos($line, $exp) !== false);
    }
    if( $a2 ) {
      $exp = substr( $exp, 0, $explen - 1 );
      return substr( $line, 0, strlen ( $exp ) ) === $exp;
    }
    if( $a1 ) {
      $exp = substr( $exp, 1, $explen - 1 );
      return substr($line, -strlen( $exp )) == $exp;
    }
    return $line == $exp;
  }

  $result = array();
  $count = 0;
  for ($i = 0; $i < count($array); ++$i) {
    if(isset($array[$i][$field])) {
      $actual = $array[$i][$field];
      if(filter_objects_match($actual, $strval)) {
        $result[] = $array[$i];
        $count++;
        if($count >= $top)
          return $result;
      }
    }
  }
  return $result;
}

function twig_render($template, $params=array(), $cache_time=CACHE_TIME_TEMPLATE_RENDERING) {
  if(!empty($cache_time)) {
    $cache_file = __DIR__.CACHE_ROOT_TEMPLATE_RENDERING.md5($template.'?'.http_build_query($params));
    if(file_exists($cache_file)) {
      if(filectime($cache_file) < strtotime($cache_time)) {
        unlink($cache_file);
      } else {
        return file_get_contents($cache_file);
      }
    }
    $data = twig_render_nocache($template, $params);
    file_put_contents($cache_file, $data);
    return $data;
  } else {
    return twig_render_nocache($template, $params);
  }
}

function twig_render_nocache($template, $params=array()) {
  Twig_Autoloader::register();
  $loader = new Twig_Loader_Filesystem(TWIGL_TEMPLATE_ROOT);
  $twig = new Twig_Environment($loader);

  $twig->addFunction(new Twig_SimpleFunction('fb', function ($path) {
    return fb($path);
  }));
  $twig->addFunction(new Twig_SimpleFunction('json_decode', function ($json) {
    return json_decode($json, true);
  }));
  $twig->addFunction(new Twig_SimpleFunction('fb_json', function ($path) {
    return fb_json($path);
  }));
  $twig->addFunction(new Twig_SimpleFunction('filter_objects', function ($array, $field, $value, $top=1000) {
    return filter_objects($array, $field, $value, $top);
  }));
  $twig->addFunction(new Twig_SimpleFunction('parse_images', function ($array, $field, $value) {
    return parse_images($array, $field, $value);
  }));
  return $twig->render($template, $params);
}

// Facebook Batch API call
function fb_batch($paths, $cache_time=CACHE_TIME_FB_API_CALL) {
  $result = array();
  foreach ($paths as $key => $val) {
    $result[$key] = fb($val, $cache_time);
  }
  return $result;
}

// Facebook API call
function fb($path, $cache_time=CACHE_TIME_FB_API_CALL) {
  return json_decode(fb_json($path,$cache_time), true);
}

// Facebook API call
function fb_json($path, $cache_time=CACHE_TIME_FB_API_CALL) {
  if (strpos($path,'?') !== false) {
    $token = '&'.FB_TOKEN;
  } else {
    $token = '?'.FB_TOKEN;
  }
  return url(FB_URL.$path.$token, $cache_time);
}

// HTTP GET with caching support
// $cache_time='-60 minutes'
function url($url, $cache_time=NULL) {

  if(!empty($cache_time)) {
    $cache_file = __DIR__.CACHE_ROOT_URL_CALL.md5($url);
    if(file_exists($cache_file)) {
      if(filectime($cache_file) < strtotime($cache_time)) {
        unlink($cache_file);
      } else {
        return file_get_contents($cache_file);
      }
    }
    $data = url_nocache($url);
    file_put_contents($cache_file, $data);
    return $data;
  } else {
    return url_nocache($url);
  }
}

// plain HTTP GET
function url_nocache($url) {
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  $data = curl_exec($ch);
  curl_close($ch);
  return $data;
}
?>
