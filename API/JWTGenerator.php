<?php

    date_default_timezone_set('Europe/Rome');

    /**
    * Class JWTGenerator
    * implements https://scotch.io/tutorials/the-anatomy-of-a-json-web-token
     */
    class JWTGenerator {

        /**
         * @var string Secret Key, max length 32 chars
         */
        private $secret_key = "This is my secret key";

        /**
         * @var string Expriging date, check strtotime
         */
        private $expiring_after = "+1 hour";

        public function __construct($secret_key, $expiring_after) {
            $this->secret_key = $secret_key;
            $this->expiring_after = $expiring_after;
        }

        /**
         * Validate the token
         * @param $token
         * @return bool
         */
        public function is_token_valid($token) {
            try {
                $parts = explode(".", $token);
                if (sizeof($parts) < 3) return false;

                $signatureValid = $this->base64URLEncode($this->encrypt($parts[0].".".$parts[1])) == $parts[2];
                $payload = json_decode($this->base64URLDecode($parts[1]));
                $intime = strtotime("now") <= $payload->exp;
                return $signatureValid && $intime;
            } catch (Exception $e) {}
            return false;
        }

        /**
         * Encode data into Base64 Url Safe string
         *
         * @param $data string to encode
         * @param bool $use_padding
         * @return string
         */
        public static function base64URLEncode($data, $use_padding = false) {
            $encoded = strtr(base64_encode($data), '+/', '-_');
            return true === $use_padding ? $encoded : rtrim($encoded, '=');
        }

        /**
         * Decode data from Base64 Url Safe string
         * @param $data string to decode
         * @return string
         */
        public static function base64URLDecode($data) {
            return base64_decode(strtr($data, '-_', '+/'));
        }

        /**
         * Create a new token in JWT style
         * @param $info additional information
         * @return string token
         */
        public function generate_token($info) {
            $header = $this->base64URLEncode(json_encode(array("typ" => "JWT", "alg" => "HS256")));
            $payload = $this->base64URLEncode(json_encode(array_merge($info, array("exp" => strtotime($this->expiring_after)))));
            $encoded = $header.".".$payload;
            $signature = $this->base64URLEncode($this->encrypt($encoded));
            return $encoded.".".$signature;
        }

        /**
         * Encrypts the supplied string
         * @param $text
         * @return string
         */
        private function encrypt($text) {
            return hash_hmac('sha256', $text, $this->secret_key);
        }

    }
?>