<?php
namespace MapasCulturais\AuthProviders;

use MapasCulturais\App;

class OpauthOpenId extends \MapasCulturais\AuthProvider{
    protected $opauth;

    protected function _init() {
        $app = App::i();

        $config = array_merge(array(
            'timeout' => '24 hours',
            'salt' => 'LT_SECURITY_SALT_SECURITY_SALT_SECURITY_SALT_SECURITY_SALT_SECU',
            'login_url' => 'https://www.google.com/accounts/o8/id',
            'path' => preg_replace('#^https?\:\/\/[^\/]*(/.*)#', '$1', $app->createUrl('auth'))

        ), $this->_config);

        $opauth_config = array(
            'Strategy' => array(
                'OpenID' => array(
                    'identifier_form' => THEMES_PATH . 'active/views/auth-form.php',
                    'url' => $config['login_url']
                )
            ),
            'security_salt' => $config['salt'],
            'security_timeout' => $config['timeout'],
            'path' => $config['path'],
            'callback_url' => $app->createUrl('auth','response')
        );

        $opauth = new \Opauth($opauth_config, false );

        $this->opauth = $opauth;

        if($config['logout_url']){
            $app->hook('auth.logout:after', function() use($app, $config){
                $app->redirect($config['logout_url'] . '?next=' . $app->baseUrl);
            });
        }


        // add actions to auth controller
        $app->hook('GET(auth.index)', function () use($app){
            $app->redirect($this->createUrl('openid'));
        });

        $app->hook('<<GET|POST>>(auth.openid)', function () use($opauth, $config){
            $_POST['openid_url'] = $config['login_url'];
            $opauth->run();
        });

        $app->hook('GET(auth.response)', function () use($app){
            $app->auth->processResponse();

            if($app->auth->isUserAuthenticated()){
                $app->redirect ($app->auth->getRedirectPath());
            }else{
                $app->redirect ($this->createUrl(''));
            }
        });
    }

    public function _cleanUserSession() {
        unset($_SESSION['opauth']);
    }

    /**
     * Returns the URL to redirect after authentication
     * @return string
     */
    public function getRedirectPath(){
        $path = key_exists('mapasculturais.auth.redirect_path', $_SESSION) ?
                    $_SESSION['mapasculturais.auth.redirect_path'] : App::i()->createUrl('site','');

        unset($_SESSION['mapasculturais.auth.redirect_path']);

        return $path;
    }

    /**
     * Returns the Opauth authentication response or null if the user not tried to authenticate
     * @return array|null
     */
    protected function _getResponse(){
        $app = App::i();
        /**
        * Fetch auth response, based on transport configuration for callback
        */
        $response = null;

        switch($this->opauth->env['callback_transport']) {
            case 'session':
                $response = key_exists('opauth', $_SESSION) ? $_SESSION['opauth'] : null;
                break;
            case 'post':
                $response = unserialize(base64_decode( $_POST['opauth'] ));
                break;
            case 'get':
                $response = unserialize(base64_decode( $_GET['opauth'] ));
                break;
            default:
                $app->log->error('Opauth Error: Unsupported callback_transport.');
                break;
        }
        return $response;
    }


    /**
     * Check if the Opauth response is valid. If it is valid, the user is authenticated.
     * @return boolean
     */
    protected function _validateResponse(){
        $app = App::i();

        $reason = '';

        $response = $this->_getResponse();

        $valid = false;

        // o usuário ainda não tentou se autenticar
        if(!is_array($response))
            return false;

        // verifica se a resposta é um erro
        if (array_key_exists('error', $response)) {
            $app->flash('auth error', 'Opauth returns error auth response');
        } else {
            /**
            * Auth response validation
            *
            * To validate that the auth response received is unaltered, especially auth response that
            * is sent through GET or POST.
            */
            if (empty($response['auth']) || empty($response['timestamp']) || empty($response['signature']) || empty($response['auth']['provider']) || empty($response['auth']['uid'])) {
                $app->flash('auth error', 'Invalid auth response: Missing key auth response components.');
            } elseif (!$this->opauth->validate(sha1(print_r($response['auth'], true)), $response['timestamp'], $response['signature'], $reason)) {
                $app->flash('auth error', "Invalid auth response: {$reason}");
            } else {
                $valid = true;
            }
        }

        return $valid;
    }


    public function _getAuthenticatedUser() {
        $user = null;
        if($this->_validateResponse()){
            $app = App::i();
            $response = $this->_getResponse();
            $auth_uid = $response['auth']['uid'];
            $auth_provider = $app->getRegisteredAuthProviderId('OpenId');
            $user = $app->repo('User')->getByAuth($auth_provider, $auth_uid);

            return $user;

        }else{
            return null;
        }
    }


    /**
     * Process the Opauth authentication response and creates the user if it not exists
     * @return boolean true if the response is valid or false if the response is not valid
     */
    public function processResponse(){
        // se autenticou
        if($this->_validateResponse()){
            // e ainda não existe um usuário no sistema
            $user = $this->_getAuthenticatedUser();
            if(!$user){
                $response = $this->_getResponse();

                $user = App::i()->repo('User')->createByAuthResponse($response);
                $profile = $user->profile;
                $this->_setRedirectPath($profile->editUrl);

            }
            $this->_setAuthenticatedUser($user);

            App::i()->applyHook('auth.successful');
            return true;
        } else {
            $this->_setAuthenticatedUser();
            App::i()->applyHook('auth.failed');
            return false;
        }
    }
}