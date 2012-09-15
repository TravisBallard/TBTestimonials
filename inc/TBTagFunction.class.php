<?php

    require_once( 'twig/lib/Twig/ExtensionInterface.php' );
    require_once( 'twig/lib/Twig/Extension.php' );

    /**
    *   Custom wrapper to add callable functions in Twig_Extension
    */
    class TBTagFunction extends Twig_Extension
    {
        protected $object;
        protected $callable;

        public function __construct( $object, array $callable )
        {
            $this->object = $object;
            $this->callable = $callable;
        }

        public function getObject(){
            return $this->object;
        }

        public function getCallable(){
            return $this->callable;
        }

        public function __call( $name, $arguments )
        {
            if( in_array( $name, $this->callable ) && method_exists( $this->object, $name ) )
            {
                return call_user_func_array( array( $this->object, $name ), $arguments );
            }
        }

        public function getName()
        {
            return 'TBTagFunction';
        }

        public function getFunctions()
        {
            $functions = array();
            foreach( $this->callable as $name )
                    $functions[$name] = new Twig_Function_Method( $this, $name );

            return $functions;
        }
    }