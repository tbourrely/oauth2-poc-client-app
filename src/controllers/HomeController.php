<?php
/**
 * File "HomeController.php"
 * @author Thomas Bourrely
 * 17/07/2017
 */

namespace clientApp\controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use clientApp\helpers\Helper;


/**
 * Class HomeController
 *
 * @package mainApp\controllers
 */
class HomeController extends BaseController
{
    /**
     * Handle request to the homepage
     * Render html
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param $args
     */
    public function home( RequestInterface $request, ResponseInterface $response, $args )
    {
        $render_args = array();

        if ( !empty( $_SESSION['token'] ) ) {

            $time = time();

            // token is expired so it is removed
            if ( $_SESSION['token']->expire_time <= $time ) {
                unset( $_SESSION['token'] );
            }
            // token is still valid
            else {

                $api_url = "http://oauth2-poc-app.local/api";
                $users = Helper::callApi( 'GET', $api_url . '/user_infos', false, $_SESSION['token']->access_token );

                $render_args['is_connected'] = true;
                $render_args['users'] = json_decode( $users );

            }

        }




        return $this->render( $response, 'home', $render_args );
    }

}