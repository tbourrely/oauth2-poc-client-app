<?php

namespace clientApp\helpers;

/**
 * File "Helper.php"
 * @author Thomas Bourrely
 * 20/07/2017
 */

/**
 * Class Helper
 *
 * @package clientApp\helpers
 */
class Helper
{
    /**
     * Call an API endpoint
     *
     * @param $method
     * @param $url
     * @param bool $data
     * @param bool $access_token
     * @return mixed|null
     */
    public static function CallAPI( $method, $url, $data = false, $access_token = false )
    {
        $curl = curl_init();

        switch ( $method )
        {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);

                if ( $data )
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;

            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;

            default:
                break;
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        if ( $access_token ) {
            curl_setopt( $curl, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . $access_token
            ) );
        }

        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    }
}