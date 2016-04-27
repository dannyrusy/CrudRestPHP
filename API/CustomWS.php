<?php

    require __DIR__.'/WebServices.php';
    require __DIR__.'/utils/custom_file_get_contents.php';
    require __DIR__.'/inc/config.inc.php';
	
    /**
     * Class CustomWS (extends of the class WebService)
     * manage the endpoints request
     */
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
		
        /**
         * Return list of Endpoints
         * @return string
         */
        public function get_availables_endpoints() {
            
            $response = array();
            for ($i = 0; $i < count($this->endpoints); $i++) {
            	$singoloEndPoint = $this->endpoints[$i];
            	$name = $singoloEndPoint['name'];
            	$url = $this->base.$this->root.$name;
            	$method = $singoloEndPoint['method'];
            	array_push($response, 
            			array("name" => $name, 
            					"url" => $url, 
            					"method" => $method,
            					"example" => $url."?token=".$this->params['token']));
            }
            return json_encode($response);
        }
        
        /**
         * function for the "invocation" of endpoints
         */
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
					if ($this->method == "GET") {
						$this->response(custom_file_get_contents($this->get_endpoint_url() . "?" . $_SERVER['QUERY_STRING']));
					} else if ($this->method == "POST") {
						$dataPost = file_get_contents("php://input");
						$opts = array('http' =>
								array(
										'method'  => 'POST',
										'header'  => 'Content-Type: application/json',
										'content' => $dataPost //$postdata
								)
						);
						$this->response(custom_file_get_contents($this->get_endpoint_url() . "?" . $_SERVER['QUERY_STRING'], false ,$opts));
					}else if ($this->method == "DELETE") {
						$this->response(custom_file_get_contents($this->get_endpoint_url() . "?" . $_SERVER['QUERY_STRING']));
					} else {
						$this->response("", 501);
					}
                }
                
            } else {
                $this->response("", 401);
            }
        }

    }

?>