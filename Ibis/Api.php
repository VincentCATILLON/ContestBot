<?php

namespace Devotion\VoteBot\Ibis;

class Api {
    const BASE_API = 'https://www.ibisstylesbyme.fr/api';
    const CSRF_TOKEN_NAME = 'XSRF-TOKEN';

    private $proxy;
    private $creation;

    public function __construct($proxy = null)
    {
        $this->proxy = $proxy;
    }

    private function getCurlInstance($api, $customHeaders = [])
    {
        $ch = curl_init($api);

        $headers = array_merge([
            'Content-Type: application/json'
        ], $customHeaders);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);

        if ($this->proxy) {
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
            curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
        }

        return $ch;
    }

    private function getResponseContent($ch, $result)
    {
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $body = substr($result, $headerSize);

        return json_decode($body);
    }

    private function getCreationResponse($creationId)
    {
        $ch = $this->getCurlInstance(self::BASE_API.'/GetCreation');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{"CreationId": "'.$creationId.'"}');

        $result = curl_exec($ch);
        $this->creation = $this->getResponseContent($ch, $result);

        return $result;
    }

    private function getCSRFToken($creationId)
    {

        $result = $this->getCreationResponse($creationId);
        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $result, $matches);
        $cookies = [];
        foreach($matches[1] as $item) {
            parse_str($item, $cookie);
            $cookies = array_merge($cookies, $cookie);
        }

        if (!array_key_exists(self::CSRF_TOKEN_NAME, $cookies)) {
            print_r($result);
            exit(1);
        }

        return $cookies[self::CSRF_TOKEN_NAME];
    }

    public function addVoteToCreation($creationId)
    {
        $CSRFHeader = sprintf("%s=%s", self::CSRF_TOKEN_NAME, $this->getCSRFToken($creationId));
        $ch = $this->getCurlInstance(self::BASE_API.'/Vote', [
            "Cookie: _gat=1; $CSRFHeader"
        ]);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{"CreationId": "'.$creationId.'"}');

        $result = curl_exec($ch);
        $content = $this->getResponseContent($ch, $result);

        if (!$content || $content->Error !== 0) {
            print_r($content);
            exit(1);
        }

        $this->getCreationResponse($creationId);

        return $this->creation;
    }

    public function getRanking($creationId)
    {
        $ch = $this->getCurlInstance(self::BASE_API.'/SearchCreation');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{Start: 1, Number: 100, Thema: "", Sort: 3, FirstName: ""}');

        $result = curl_exec($ch);
        $content = $this->getResponseContent($ch, $result);

        $position = '-';
        foreach ($content->Creations as $index => $creation) {
            if ($creation->Creation->CreationId === intval($creationId)) {
                $position = ($index + 1);
                break;
            }
        }

        return $position.'/'.$content->Total;
    }
}
