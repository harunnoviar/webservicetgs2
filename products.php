<?php
$url = 'http://kuliah.local:81/webservice/api/products';
function get_web_page($url)
{
    $options = [
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'accept: application/json',
        ],
    ];
    $ch = curl_init($url);
    curl_setopt_array($ch, $options);
    $content = curl_exec($ch);
    curl_close($ch);
    return $content;
}
$products = get_web_page($url);
// var_dump($products);
// die();
$data = json_decode($products);
echo "<pre>";
print_r($data);
