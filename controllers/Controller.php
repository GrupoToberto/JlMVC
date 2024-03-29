<?php
    namespace com\grupotoberto\jlmvc\controllers;
    use com\grupotoberto\jlweb\WebManager as WM;

    class Controller{
        private static $lang;

        protected static function View($lang){
            self::$lang=$lang;
            $Aux=explode("\\", debug_backtrace()[1]['class']);
            $viewGroup=str_replace("Controller", "", $Aux[count($Aux)-1]);
            $view=debug_backtrace()[1]['function'];
            $uri=$_SERVER['DOCUMENT_ROOT']."/views/".$viewGroup."/".$view.".html";

            $file=fopen($uri, "r");
            $template=fread($file,filesize($uri));
            fclose($file);

            return self::fixTemplate($template);
        }

        private static function fixTemplate($template){

            try{
                if(substr_count($template, "@extends")==1){
                    $pos=strpos($template, '@extends'); 
                    $layout=substr($template, $pos+10); 
                    $posf=strpos($layout, "')");
                    $layout=substr($template, $pos+10, $posf); 

                    $uri=$_SERVER['DOCUMENT_ROOT']."/views/".$layout.".html";

                    $file=fopen($uri, "r");
                    $fTemplate=fread($file,filesize($uri));
                    fclose($file);

                    $template=str_replace("@extends('".$layout."')", $fTemplate, $template);
                    
                    $template=self::fixStrings($template);
                    $template=self::fixSections($template);
                    $template=self::fixViews($template);
                }
            }
            catch(Exception $e){

            }

            return $template;
        }

        private static function fixStrings($template){
            $nStrings=substr_count($template, "@string");
            $Strings=array();
            $i=$nStrings;

            $folder=isset(self::$lang)?"/res/".self::$lang."/":"/res/";
            WM::setResFolder($folder);

            try{
                while($i>0){
                    $pos=strpos($template, '@string'); 
                    $Strings[$i]=substr($template, $pos+9); 
                    $posf=strpos($Strings[$i], "')");
                    $Strings[$i]=substr($template, $pos+9, $posf); 
    
                    $template=str_replace("@string('".$Strings[$i]."')", WM::getString($Strings[$i]), $template);
    
                    $i--;
                } 
            }
            catch(Exception $e){

            }

            return $template;
        }

        private static function fixSections($template){
            $nSections=substr_count($template, "@section");
            $Sections=array();
            $Inners=array();
            $i=$nSections;

            try{
                while($i>0){
                    $pos=strpos($template, '@section'); 
                    $Sections[$i]=substr($template, $pos+10); 
                    $posf=strpos($Sections[$i], "')");
                    $Sections[$i]=substr($template, $pos+10, $posf); 
    
                    $posInner=strpos($template, "@section('".$Sections[$i]."')");
                    $Inners[$i]=substr($template, $posInner+strlen("@section('".$Sections[$i]."')")); 
                    $posfInner=strpos($Inners[$i], "@endsection");
                    $Inners[$i]=substr($template, $posInner+strlen("@section('".$Sections[$i]."')"), $posfInner); 
    
                    $template=str_replace("@section('".$Sections[$i]."')", "", $template);
                    $template=str_replace($Inners[$i]."@endsection", "", $template);
    
                    $i--;
                } 
                $template=self::fixContents($template, $Sections, $Inners);
            }
            catch(Exception $e){

            }

            return $template;
        }

        private static function fixContents($template, $Sections, $Inners){

            try{
                for($i=1; $i<=count($Sections); $i++)
                    $template=str_replace("@content('".$Sections[$i]."')", $Inners[$i], $template);
            }
            catch(Exception $e){

            }

            return $template;
        }

        public function getResource($Params){
            $uri=$_SERVER['DOCUMENT_ROOT'].'/res/public/'.$Params[0]; 
            $file=fopen($uri, "r");
            $res=fread($file,filesize($uri)); 
            fclose($file);

            header($Params[count($Params)-1]);
            echo $res;
        }

        private static function fixViews($template){
            $nViews=substr_count($template, "@view");
            $Views=array();
            $i=$nViews;

            try{
                while($i>0){
                    $pos=strpos($template, '@view'); 
                    $Views[$i]=substr($template, $pos+7); 
                    $posf=strpos($Views[$i], "')");
                    $Views[$i]=substr($template, $pos+7, $posf); 
    
                    $template=str_replace("@view('".$Views[$i]."')", self::getView($Views[$i]), $template);
    
                    $i--;
                }
                
                $template=self::fixStrings($template);
                $nViews=substr_count($template, "@view");

                if($nViews>0)
                    self::fixViews($template);
            }
            catch(Exception $e){

            }

            return $template;
        }

        private static function getView($location){
            $uri=$_SERVER['DOCUMENT_ROOT']."/views/".$location.".html";

            $file=fopen($uri, "r");
            $view=fread($file,filesize($uri));
            fclose($file);

            return $view;
        }
    }