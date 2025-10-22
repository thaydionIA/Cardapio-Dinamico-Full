<?php
class KeyController{

    public static function getPublicKey()
    {
        $data['type'] = "card";
        $curl = curl_init('https://sandbox.api.pagseguro.com/public-keys/');
        curl_setopt($curl,CURLOPT_HTTPHEADER,Array(
            'Content-Type: application/json',
            'Authorization: e7a07e98-4444-4327-806f-d4f71a863b60a17c1e8f43afb7fd0ff6a5683c371ae3c30c-1fc9-4dce-a27b-6ba4fd4cdde2'
        ));
        curl_setopt($curl,CURLOPT_POST,true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        $retorno = curl_exec($curl);
        curl_close($curl);
        return json_decode($retorno)->public_key;
    }
}