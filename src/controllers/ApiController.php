<?php
/**
 * File "ApiController.php"
 * @author Thomas Bourrely
 * 20/07/2017
 */

namespace clientApp\controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use clientApp\controllers\BaseController;

/**
 * Class ApiController
 *
 * @package clientApp\controllers
 */
class ApiController extends BaseController
{

    /**
     * Authorization server URL
     *
     * @var string
     */
    private $auth_url = "http://oauth2-poc-server.local";

    /**
     * client id
     *
     * @var string
     */
    private $client_id = '5970a1bd0933e';

    /**
     * client secret
     *
     * @var string
     */
    private $client_secret = '73c12bfb3e8c4d672c7fea81a50ae807';

    /**
     * Ask authorization code to the Authorization server
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param $args
     * @return static
     */
    public function login( RequestInterface $request, ResponseInterface $response, $args )
    {
        $target =  $this->auth_url . "/authorize";
        $redirect_uri = $request->getUri()->getScheme() . '://' . $request->getUri()->getHost() . $this->container->get('router')->pathFor('API_code.get');

        $state = md5(uniqid(rand(), true));
        $_SESSION['csrf_token'] = $state; // CSRF protection

        $data = array(
            'response_type' => 'code',
            'client_id'     => $this->client_id,
            'redirect_uri'  => $redirect_uri,
            'scope'         => 1, // testing purpose
            'state'         => $state
        );


        $query = http_build_query($data);

        return $response->withStatus(302)->withHeader('Location', $target . '?' . $query);

    }

    /**
     * Receive the authorization code
     * Use the authorization code to get an access token
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param $args
     */
    public function code( RequestInterface $request, ResponseInterface $response, $args )
    {
        $params = $request->getParams();

        if ( !empty( $params['code'] )  && !empty( $_SESSION['csrf_token'] ) && !empty( $params['state'] ) ) {

            if ( $params['state'] === $_SESSION['csrf_token'] ) {

                $target = $this->auth_url . '/access_token';
                $redirect_uri = $request->getUri()->getScheme() . '://' . $request->getUri()->getHost() . $this->container->get('router')->pathFor('API_code.get');

                $data = array(
                    'grant_type'    => 'authorization_code',
                    'client_id'     => $this->client_id,
                    'client_secret' => $this->client_secret,
                    'redirect_uri'  => $redirect_uri,
                    'code'          => $params['code']
                );


                $curl = curl_init();
                curl_setopt( $curl, CURLOPT_POST, 1 );
                curl_setopt( $curl, CURLOPT_POSTFIELDS, $data );
                curl_setopt( $curl, CURLOPT_URL, $target );
                curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
                $result = curl_exec( $curl );
                curl_close( $curl );

                if ( !empty( $result ) ) {
                    $result = json_decode($result);

                    $time = time();
                    $max_time = $time + $result->expires_in;

                    $result->expire_time = $max_time;
                    $_SESSION['token'] = $result;
                }

                return $this->redirect( $response, 'home' );
            }

        }

    }

    /**
     * Remove the access token from the session
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param $args
     * @return static
     */
    public function logout( RequestInterface $request, ResponseInterface $response, $args )
    {
        if ( !empty( $_SESSION['token'] ) ) {
            unset( $_SESSION['token'] );
        }

        return $this->redirect( $response, 'home' );
    }
}