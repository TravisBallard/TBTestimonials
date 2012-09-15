<?php
    /**
    *   TB-Testimonial Template Object
    */
    class Testimonial_Output_Template
    {
        protected $_name;
        protected $_syntax;
        protected $_description;

        /**
        * magic
        *
        * @param mixed $name
        * @param mixed $description
        * @param mixed $syntax
        * @return Testimonial_Output_Template
        */
        public function __construct( $name, $description = '', $syntax = '' )
        {
            $this->set_name( $name );
            $this->set_description( $description );
            $this->set_syntax( $syntax );
        }

        /**
        * get template syntax
        *
        */
        public function get(){
            return $this->_syntax;
        }

        /**
        * set template syntax
        *
        * @param mixed $syntax
        */
        public function set_syntax( $syntax ){
            $this->_syntax = $syntax;
        }

        /**
        * get template name
        *
        */
        public function name(){
            return $this->_name;
        }

        /**
        * set template name;
        *
        * @param mixed $name
        */
        public function set_name( $name ){
            $this->_name = $name;
        }

        /**
        * get template description
        *
        */
        public function description(){
            return $this->_description;
        }

        /**
        * set template description
        *
        * @param mixed $description
        */
        public function set_description( $description ){
            $this->_description = $description;
        }
    }