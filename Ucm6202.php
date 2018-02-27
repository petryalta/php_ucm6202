<?php

/*
 * Copyright by Petr Ivanov (petr.yrs@gmail.com)
 * 
 * Get CDR information from Grandstream UCM6202
 */

class Ucm6202 {
    /*
     * Host name or IP of PBX
     */

    private $host;

    /*
     * API port. Default 8443
     */
    private $port;

    /*
     * User name for access to API. Default 'cdrapi'
     */
    private $user;

    /*
     * User password for access to API. Default 'cdrapi123'
     */
    private $pass;

    /*
     * CURL handler
     */
    private $curl;

    /**
     * Initial connection to PBX API
     * @param string $host
     * @param string $user
     * @param string $pass
     * @param string $port 
     */
    public function __construct($host, $user = 'cdrapi', $pass = 'cdrapi123', $port = '8443') {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->pass = $pass;

        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->curl, CURLOPT_USERPWD, $this->user . ":" . $this->pass);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, 'cookie.txt');
        curl_setopt($this->curl, CURLOPT_COOKIEFILE, "cookie.txt");
        curl_setopt($this->curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:58.0) Gecko/20100101 Firefox/58.0');
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 50);
    }

    /**
     * Find records from CDR
     * @param array $params
     * @return array|false
     */
    public function find($params = []) {
        $url = '/cdrapi?format=json';
        $urlBuf = array_filter($params, function($item) {
            if (is_null($item)) {
                return false;
            } else {
                return true;
            }
        });
        $resBuf = [];
        foreach ($urlBuf as $key => $value) {
            $resBuf[] = $key . '=' . $value;
        }
        $urlParams = implode('&', $resBuf);
        if (strlen($urlParams) > 0) {
            $url .= '&' . $urlParams;
        }
        $url = $this->host . ':' . $this->port . $url;
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($this->curl);
        if ($result) {
            $res = CJSON::decode($result);
            $cdrRoot = $res['cdr_root'];
            $cdrRoot2 = [];
            foreach ($cdrRoot as $item) {
                if (!isset($item['start'])) {
                    if (isset($item['sub_cdr_3'])) {
                        $item['sub_cdr_3']['calldate'] = $item['sub_cdr_3']['start'];
                        $item['sub_cdr_3']['cdr_pkey'] = $item['sub_cdr_3']['AcctId'];
                        $cdrRoot2[] = $item['sub_cdr_3'];
                    } else {
                        continue;
                    }
                } else {
                    $item['calldate'] = $item['start'];
                    $item['cdr_pkey'] = $item['AcctId'];
                    $cdrRoot2[] = $item;
                }
            }
            return $cdrRoot2;
        } else {
            return FALSE;
        }
    }

}
