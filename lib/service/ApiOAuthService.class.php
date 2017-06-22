<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ApiOAuthService
 *
 * @author Glenn Cavarlé <glenn.cavarle@libre-informatique.fr>
 * @author Baptiste SIMON <baptiste.simon@libre-informatique.fr>
 */
class ApiOAuthService extends EvenementService
{

    /**
     * @var OsToken
     * */
    protected $token = null;

    /**
     *
     * @param sfWebRequest $request
     * @return boolean
     */
    public function authenticate()
    {
        $headerValue = $this->getAuthorizationHeader();
        if (!$headerValue) {
            throw new liApiAuthException('API Key not provided');
        }

        $apiKey = preg_replace('/^Bearer\s+/', '', $headerValue);
        $this->token = $this->findRegisteredTokenByApiKey($apiKey);

        if (null === $this->token || !$this->token instanceof OsToken) {
            throw new liApiAuthException('Invalid API authentication');
        }
        return true;
    }


    protected function getAuthorizationHeader()
    {
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            return $headers['Authorization'];
        }
        return false;
    }
    /**
     *
     * @return OsToken
     * */
    public function getToken()
    {
        return $this->token;
    }

    /**
     *
     * @param string $key
     * @return OsToken | null
     */
    public function findRegisteredTokenByApiKey($key)
    {
        $q = Doctrine::getTable('OsToken')->createQuery('ot')
            ->andWhere('ot.token = ?', $key)
            ->andWhere('expires_at > ?', date('Y-m-d H:i:s'))
        ;

        $token = $q->fetchOne();

        if (!$token) {
            return null;
        }

        return $token;
    }

    /**
     *
     * @param string $client_id
     * @param string $client_secret
     * @return OsApplication | null
     */
    public function findApplication($client_id, $client_secret)
    {
        $q = Doctrine::getTable('OsApplication')->createQuery('app')
            ->leftJoin('app.User u')
            ->andWhere('app.identifier = ?', $client_id)
            ->andWhere('app.secret     = ?', $this->encryptSecret($client_secret))
            ->andWhere('app.expires_at IS NULL OR app.expires_at > NOW()')
        ;

        $app = $q->fetchOne();
        if (!$app) {
            return null;
        }

        return $app;
    }

    public function createToken(OsApplication $app)
    {
        $token = new OsToken();

        $token->token = $this->generateToken();
        $token->refresh_token = $this->generateToken();
        $token->expires_at = $this->getExpirationTime();
        $token->os_application_id = $app->id;
        $token->Transaction = new Transaction;
        $token->save();

        return $token;
    }

    public function refreshToken($refresh, OsApplication $app)
    {
        $q = Doctrine::getTable('OsToken')->createQuery('ot')
            ->andWhere('ot.refresh_token = ?', $refresh)
            ->andWhere('ot.os_application_id = ?', $app->id)
            ->andWhere('ot.created_at > ?', date('Y-m-d H:i:s', strtotime('24 hours ago')))
        ;

        $token = $q->fetchOne();
        if (!$token instanceof OsToken) {
            throw new liApiAuthException('Refresh token not found.');
        }
        $token->token = $this->generateToken();
        $token->refresh_token = $this->generateToken();
        $token->expires_at = $this->getExpirationTime();
        $token->save();

        return $token;
    }

    public function encryptSecret($secret)
    {
        $salt = sfConfig::get('project_eticketting_salt', '123456789azerty');
        return md5($secret . $salt);
    }

    protected function generateToken()
    {
        $date = str_replace('-', 'T', date('Ymd-HisP'));
        return $this->encryptSecret($date . '-' . rand(1000000, 9999999));
    }

    public function getTokenLifetime()
    {
        return ini_get('session.gc_maxlifetime');
    }

    protected function getExpirationTime()
    {
        return date('Y-m-d H:i:s', time() + $this->getTokenLifetime());
    }
}
