<?php
    namespace com\grupotoberto\jlmvc;
    use com\grupotoberto\jlweb\WebManager as WM;
    class Router{
        private $controller;
        private $method;
        private $Params;
        private $remainder;

        public function __construct(){
            $this->matchRoute();
        }

        public function matchRoute(){
            WM::setResFolder("/res/");

            $url = explode('/', URL);
            $DEFAULT_CONTROLLER=WM::getString("DEFAULT_CONTROLLER")."";
            $DEFAULT_METHOD=WM::getString("DEFAULT_METHOD")."";

            $this->controller=!empty($url[0])?$url[0].'Controller':$DEFAULT_CONTROLLER;
            $this->method=!empty($url[1])?$url[1]:$DEFAULT_METHOD;

            $i=2;
            $j=0;

            $this->Params=array();
            $this->remainder;

            while(isset($url[$i])){
                $this->Params[$j]=$url[$i];
                $this->remainder.="/".$url[$i];
                $j++; $i++;
            }
            
            $redirection=$this->search($this->controller, $this->method, $this->remainder);

            if(isset($redirection)){
                $this->controller=$redirection->controller;
                $this->method=$redirection->actionResult."";
                
                if($redirection->explode=="true")
                    array_push($this->Params, explode("/", $redirection->Remainder), $redirection->header);
                else
                    $this->Params=[$redirection->Remainder, $redirection->header];
            }
            
            require_once($_SERVER['DOCUMENT_ROOT']."/controllers/".$this->controller.".php");
            $this->controller=WM::getString("mainNamespace")."\\".$this->controller;
            
        }

        public function run(){
            $controller = new $this->controller();
            $method = $this->method;
            $controller->$method($this->Params);
        }

        public function search($controller, $actionResult, $remainder){
            $filename=$_SERVER['DOCUMENT_ROOT']."/res/routes.xml";
			
			if(\file_exists($filename))
			{
				$xml=\simplexml_load_file($filename);
				
				if(!$xml)
					throw new \Exception('Error parsing XML document.');
				
				foreach($xml->route as $route)
				{
                    $url=$route->url;
					if($url->controller==$controller && $url->actionResult==$actionResult){
                        $redirection=$route->redirection; 
                        $redirection->explode=$url->actionResult['explode'];
                        $redirection->Remainder='/'.$remainder; 
                        return $redirection;
                    }	
				}
			}

			return null;
        }
    }