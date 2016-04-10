<?php

class APIRequest {
    private $clientId;
    
    private $clientSecret;
    
    private $userCode;
    
    private $accessToken;
    
    private $redirectUrl = 'http://localhost';
    
    private $user;
    
    public function __construct($clientId, $clientSecret) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }
    
    public function setUserCode($code) {
        $this->userCode = $code;
        return $this;
    }
    
    public function setRedirectUrl($url) {
        $this->redirectUrl = $url;
        return $this;
    }
    
    public function setAccessToken($token) {
        $this->accessToken = $token;
        return $this;
    }
    
    public function getUser() {
        return $this->user;
    }
    
    public function getAccessToken() {
        if (!$this->accessToken) {
            $this->reloadAccessToken();
        }
        
        return $this->accessToken;
    }
    
    public function getAuthorizationUrl() {
        return "https://api.instagram.com/oauth/authorize/?client_id={$this->clientId}&redirect_uri={$this->redirectUrl}&response_type=code";    
    } 
    
    public function reloadAccessToken() {
        
        $ch = curl_init('https://api.instagram.com/oauth/access_token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $k = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirectUrl,
            'code' => $this->userCode
        ]);
        
        //var_dump($k);
        
        
        $result = curl_exec ($ch); 
        curl_close($ch);
        
        $data = json_decode($result, true);
        
        if (!isset($data['access_token'])) {
            throw new Exception("Unable to retrieve access token");
        }
        
        $this->accessToken = $data['access_token'];
        
        $this->user = $data['user'];
        
        return $this;
    }
    
    public function get($path) {
        
        $ch = curl_init();
        
        curl_setopt_array($ch, $k = array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->buildUrl($path),
            CURLOPT_USERAGENT => 'My Test App'
        ));

        print_r($k);
        
        $response = curl_exec($ch);
        
        if(!curl_exec($ch)){
            throw new Exception ('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
        }
        
        curl_close($ch);
        return json_decode($response, true);
    }
    
    private function buildUrl($path) {
        return sprintf('https://api.instagram.com/v1/%s?access_token=%s', (trim($path, '/') . '/'), $this->accessToken);
    }
}

class InstagramUser {
    
    private $req;
    
    public function __construct(APIRequest $req) {
        $this->req = $req;
    }
    
    public function getUser() {
        return $this->checkResponse($this->req->get('users/self'));
    }
    
    //check is valid response
    private function checkResponse($response) {
        if (!isset($response['meta'])) {
            throw new Exception("Unknown format is returned by api ".json_encode($response));
        }
        
        return $response;
    }
}

