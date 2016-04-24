<?php

    require __DIR__.'/WebServices.php';
    require __DIR__.'/utils/custom_file_get_contents.php';
    require __DIR__.'/inc/config.inc.php';

    class CustomWS extends WebServices {

        private $authenticator;
        private $endpoints;
        private $tokenMatchDB = TRUE;

        public function __construct($authenticator, $endpoints) {
            parent::__construct($endpoints);

            $this->authenticator = $authenticator;
            $this->endpoints = $endpoints;
        }

        /**
         * Login function
         */
        public function login() {
			//you need add something for check if the user is allowed and the password is correct
			//the user name and password may be in query string... $_SERVER['QUERY_STRING']
			$user = array("user_id", "name");
			
            //controllare se è stato trovato l'utente (of course in this example is true)
            if ($user) {
            	//call the token generator
                $token = $this->authenticator->generate_token($user);
				
                if ($this->tokenMatchDB) {
                	//if you want write the token somewhere (for example in db)
                }
				
                //response (token) and code 200
                $this->response(array("token" => $token), 200);
            } else {
            	//response code 403... unauthorized
                $this->response("", 403);
            }
        }
		
        /**
         * check authentication
         */
        public function check_auth() {
            
            //get token and check if is valid
        	$token = isset($this->params['token']) ? $this->params['token'] : null;
            $valid = $token != NULL && $this->authenticator->is_token_valid($token);
			
            //here it's possible add another check
            //(for example check if the token is stored in the db)
            if ($this->tokenMatchDB && $valid) {
            }
            
            //return the validation result
            return $valid;
        }

        public function get_availables_endpoints() {
            $ctx = $this;
            return array_map(function($ele) use (&$ctx) {
                $url = $ctx->base.$ctx->root.$ele;
                return array("name" => $ele, "url" => $url, "example" => $url."?token=".$ctx->params['token']);
            }, array_keys($ctx->endpoints));
        }
        
        //function for the "invocation" of endpoints
        public function handle() {
            if ($this->endpoint == "login") {
            	//special endpoint for login
                $this->login();

            } else if ($this->check_auth()) {

                if ($this->method == "OPTIONS" || $this->endpoint == "") {
                	//return the list of endpoints
                    $this->response($this->get_availables_endpoints());

                } else if  (!$this->is_valid_endpoint()) {
                	//endpoint non found
                    $this->response("", 404);

                } else {
                    //execution of a sigle endpoint
                    $this->response(custom_file_get_contents($this->endpoints[$this->endpoint] . "?" . $_SERVER['QUERY_STRING']));
                }

            } else {
                $this->response("", 401);
            }
        }

    }

?>