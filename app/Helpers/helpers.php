<?php

if(!function_exists('money')) {
    /**
     * @param int $amount
     * @param string $currency_code
     * @param int $decimals
     * @param string $dec_point
     * @param string $thousands_sep
     *
     * @return string
     */
    function money($amount, $currency_code = '', $decimals = 2, $dec_point = '.', $thousands_sep = ',')
    {
        switch ($currency_code) {
            case 'USD':
            case 'AUD':
            case 'CAD':
                $currency_symbol = '$';
                break;
            case 'EUR':
                $currency_symbol = '€';
                break;
            case 'GBP':
                $currency_symbol = '£';
                break;

            default:
                $currency_symbol = '';
                break;
        }

        return $currency_symbol . number_format($amount, $decimals, $dec_point, $thousands_sep);
    }
}

if(!function_exists('apiSign')){
    function apiSign(array $data, $secret) {
        ksort($data);
        unset($data['sign']);
        $sign = array();
        foreach ($data as $k => $v) {
            $sign[] = $k . '=' . $v;
        }
        $sign = implode('&', $sign);
        return md5(md5($sign) . $secret);
    }
}

if(!function_exists('checkData')){
    function checkData($data){
        if(is_array($data)){
            foreach($data as $key => $v){
                $data[$key] = $this->checkData($v);
            }
        }else{
            $data = trim($data);
            $data = strip_tags($data);
            $data = htmlspecialchars($data);
            $data = addslashes($data);
        }
        return $data;
    }
}
if(!function_exists('jsonBack')) {
    function jsonBack($data)
    {
        if ($_REQUEST['callback']) {
            echo $_REQUEST['callback'] . "(" . json_encode($data) . ")";
        } else {
            echo json_encode($data);
        }
        exit();
    }
}

