<?php
    require_once('utils/array2xml.php');

    class WebServices {

        private $endpoints = array();

        private $codes = array(
            '200' => 'OK',
            '401' => 'Unauthorized',
            '403' => 'Forbidden',
            '404' => 'Not Found',
            '405' => 'Method Not Allowed',
            '500' => 'Internal Server Error',
            '501' => 'Not Implemented',
        );

        public function __construct($endpoints) {
            $this->base = $this->get_base_url();
            $this->endpoints = $endpoints;
            $this->root = $this->get_root();
            $this->endpoint = $this->get_path();
            $this->args = explode('/', rtrim($this->endpoint, '/'));
            $this->method = $this->get_method();
            $this->params = $this->clean_inputs($_REQUEST);
            $this->format = isset($this->params['format']) ? $this->params['format'] : "json";
        }

        private function clean_inputs($data) {
            $clean_input = Array();
            if (is_array($data)) {
                foreach ($data as $k => $v) {
                    $clean_input[$k] = $this->clean_inputs($v);
                }
            } else {
                $clean_input = trim(strip_tags($data));
            }
            return $clean_input;
        }

        private function get_root() {
            $dir = dirname(str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']));
            if ($dir == '.') {
                $dir = '/';
            } else {
                // add a slash at the beginning and end
                if (substr($dir, -1) != '/') $dir .= '/';
                if (substr($dir, 0, 1) != '/') $dir = '/' . $dir;
            }
            return $dir;
        }

        function url_origin($s, $use_forwarded_host=false)
        {
            $ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on') ? true:false;
            $sp = strtolower($s['SERVER_PROTOCOL']);
            $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
            $port = $s['SERVER_PORT'];
            $port = ((!$ssl && $port=='80') || ($ssl && $port=='443')) ? '' : ':'.$port;
            $host = ($use_forwarded_host && isset($s['HTTP_X_FORWARDED_HOST'])) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : null);
            $host = isset($host) ? $host : $s['SERVER_NAME'] . $port;
            return $protocol . '://' . $host;
        }

        function full_url($s, $use_forwarded_host=false) {
            return $this->url_origin($s, $use_forwarded_host) . $s['REQUEST_URI'];
        }

        private function get_base_url() {
            return str_replace($_SERVER['REQUEST_URI'], "", $this->full_url($_SERVER));
        }

        private function get_path() {
            $path = preg_replace('/\?.*$/', '', $_SERVER['REQUEST_URI']);
            if ($this->root) $path = preg_replace('/^' . preg_quote($this->root, '/') . '/', '', $path);
            $path = preg_replace('/\.(\w+)$/i', '', $path);
            return $path;
        }

        private function get_method() {
            $method = $_SERVER['REQUEST_METHOD'];
            $override = isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']) ? $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] : (isset($_GET['method']) ? $_GET['method'] : '');
            if ($method == 'POST' && strtoupper($override) == 'PUT') {
                $method = 'PUT';
            } elseif ($method == 'POST' && strtoupper($override) == 'DELETE') {
                $method = 'DELETE';
            }
            return $method;
        }

        protected function is_valid_endpoint() {
            return array_key_exists($this->endpoint, $this->endpoints);
        }

        public function set_status($code)
        {
            if (function_exists('http_response_code')) {
                http_response_code($code);
            } else {
                $protocol = $_SERVER['SERVER_PROTOCOL'] ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';
                $code .= ' ' . $this->codes[strval($code)];
                header("$protocol $code");
            }
        }

        public function array_to_xml( $data, &$xml_data ) {
            foreach( $data as $key => $value ) {
                if( is_array($value) ) {
                    if( is_numeric($key) ){
                        $key = 'item'.$key; //dealing with <0/>..<n/> issues
                    }
                    $subnode = $xml_data->addChild($key);
                    $this->array_to_xml($value, $subnode);
                } else {
                    $xml_data->addChild("$key",htmlspecialchars("$value"));
                }
            }
        }

        public function response($data, $status = 200) {
            if ($this->format == "xml") {
                $this->response_xml($data, $status);
            } else {
                $this->response_json($data, $status);
            }
        }

        public function response_xml($data, $status = 200) {
            ob_clean(); ob_start();
            header("Content-type: text/xml");
            $this->set_status($status);

            $encode    = is_array($data);
            $options   = array('version'=>'1.0');
            $data      = is_array($data) ? $data : trim($data);
            $php_array = $encode ? $data : json_decode($data, true);

            $xml = Array2XML::createXML('response', $php_array, $options);
            echo $xml->saveXML();
        }

        public function response_json($data, $status = 200, $toXML = false) {
            ob_clean(); ob_start();
            header("Content-type: application/json");
            $this->set_status($status);

            $encode = is_array($data);
            $data = is_array($data) ? $data : trim($data);

            if ($status != 200) {
                echo json_encode(
                    array('error' =>
                        array(
                            'code' => $status,
                            'message' => $data ?: $this->codes[strval($status)]
                        )
                    )
                );
            } else if ($data == "null") {
                echo json_encode(
                    array('error' =>
                        array(
                            'message' => "Object not found"
                        )
                    )
                );
            } else {
                $json = $encode ? json_encode($data) : $data;
                if ($toXML == true) return $json;
                else echo $json;
            }
        }

    }

?>