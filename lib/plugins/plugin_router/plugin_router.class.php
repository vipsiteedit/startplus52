<?php
/**
 * @copyright EDGESTILE
 */ 
 
 
class plugin_router {
	
	private static $instance = null;
	private $routes = array();

    private $host;

    private $patterns = array(
        'num' => '[0-9]+',
        'str' => '[a-zA-Z\.\-_%]+',
        'all' => '[a-zA-Z0-9\.\-_%]+',
    );

	public function __construct($option = array()) {
		$this->host = $this->getHost();
	}
	
	private function getHost() {
		$schema = _HTTP_;
		//$schema = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
		
		$host = strtolower($schema . trim($_SERVER['HTTP_HOST']));
		
		return $host;
	}
	
	private function getRequestUri() {
		$uri = $_SERVER['REQUEST_URI'];
		
		if ($pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
		
		//$uri = trim($uri, '/');
		
		return $uri;
	}
	
	public static function getInstance($option = array()) {
		if (is_null(self::$instance)) {
			self::$instance = new self($option);
		}
		return self::$instance;
	}
	
	public function register($type, $pattern) {
		$match = '';
		
		if ($pattern) {
			$pattern = seMultiDir() . $pattern;
			
			$match = str_replace('?', '\?', $pattern);
			
			$match = preg_replace('#\[([^\]]+)\]#', '(?:$1)?', $match);
			
			$match = preg_replace('#\$(\w+)#', '(?<$1>[a-zA-Z0-9\-_%]+)', $match);
			
			$match = '#^' . $match . '#s';
		}
		
		$params = array();
		
		$this->routes[$type] = array(
			'pattern' => $pattern,
			'match' => $match,
			'params' => $params
		);
	}
	
	public function getCanonical($url = '') {
		//print_r($this->routes);
		if (!$url) {
			$url = $this->getRequestUri();
		}
		
		foreach ($this->routes as $route) {
			if (preg_match($route['match'], $url, $m)) {
				$params = array();
				foreach ($m as $key => $val) {
					$params['$' . $key] = $val; 
				}
				$url = strtr($route['pattern'], $params);
				
				$url = preg_replace('#(\[.*\$[a-zA-Z0-9\-_%]+\])|([\[\]]*)#s', '', $url);
				break;
			}
		}
		
		return $this->host . $url;
	}
	
	public function createUrl($type, $params = array(), $full = false) {
		
		if (!isset($this->routes[$type])) 
			return;
		
		$route = $this->routes[$type];
		
		if (preg_match_all('#\$(\w+)#', $route['pattern'], $m)) {
			$replace = array();
			foreach ($m[1] as $key => $val) {
				if (isset($params[$val])) {
					$replace['$' . $val] = $params[$val];
					unset($params[$val]);
				}
			}
			$url = strtr($route['pattern'], $replace);
		}
		
		$url = preg_replace('#(\[[a-zA-Z0-9\-_%/]*\$[a-zA-Z0-9\-_%]+\])|([\[\]]*)#s', '', $url);
		
		if (!empty($params)) {
			$url .= '?' . http_build_query($params);
		}
		
		if (!$full)
			$url = $this->host . $url;
		
		return $url;
	}
	
	
}