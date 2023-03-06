<?php
    namespace com\grupotoberto\jlmvc;
    use com\grupotoberto\jlweb\WebManager as WM;
    class Router{
        private $controller;
        private $method;
        private $Params=array();

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

            while(isset($url[$i])){
                $this->Params[$j]=$url[$i];
                $j++; $i++;
            }

            require_once($_SERVER['DOCUMENT_ROOT']."/controllers/".$this->controller.".php");

            $this->controller=WM::getString("mainNamespace")."\\".$this->controller;
        }

        public function run(){
            $controller = new $this->controller();
            $method = $this->method;
            $controller->$method($this->Params);
        }
    }