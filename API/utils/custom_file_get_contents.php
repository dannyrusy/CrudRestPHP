<?php

if (function_exists('custom_file_get_contents')) return;

function file_get_contents_curl($url, $context) {
    $ch = curl_init();
    $context = $context?: array();

    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

    if (!empty($context) && isset($context['http'])) {
        $http = $context['http'];

        //imposta il METHOD
        if (isset($http['method'])) {
            $method = strtoupper($http['method']);

            if ($method == 'POST') {
                $fields = $http['content'];

                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            }
        }

        //imposta gli HEADERS
        if (isset($http['header'])) {
            $headers = explode("\r\n", $http['header']);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        //imposta il TIMEOUT -1 = infinito
        if (isset($http['timeout'])) {
            $timeout = $http['timeout'] > -1 ?: 0;
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        }

        //imposta il PROXY
        if (isset($http['proxy'])) {
            $parsed = parse_url($http['proxy']);
            $port = $parsed['port'];
            $path = str_replace(':' . $port, '', $http['proxy']);

            curl_setopt($ch, CURLOPT_PROXY, $path);
            curl_setopt($ch, CURLOPT_PROXYPORT, $port);
        }
    }

    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
}

function custom_file_get_contents($filename, $flags = null, $context = null, $maxlen = null)
{
    $allowUrlFopen = preg_match('/1|yes|on|true/i', ini_get('allow_url_fopen'));

    return file_get_contents_curl($filename, $context);
    /*
    if ($allowUrlFopen == FALSE && preg_match("@^https?://@", $filename)) {
        return file_get_contents_curl($filename, $context);
    } else {
        return file_get_contents($filename, $flags, stream_context_create($context), $maxlen);
    }
    */
}

function custom_stream_context_create($context) {
    return $context;
}

function custom_streamfile($url) {

    $ch = curl_init ($url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
}

?>