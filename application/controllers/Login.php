<?php
defined('BASEPATH') or exit('no direct script allowed here');
require APPPATH . '/libraries/REST_Controller.php';
require 'vendor/autoload.php';
use RestServer\Libraries\REST_Controller;
use \Firebase\JWT\JWT;

class Login extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
    }

    public function index_post()
    {
        // var_dump($_SERVER);
        // die;
        //password: webservice
        $this->db->where('email', $this->post('email'));
        $data = $this->db->get('users')->result();
        if (isset($data[0])) {
            if (password_verify($this->post('password'), $data[0]->password)) {
                $secret_key = base64_encode("gampang");
                $issuer_claim = "Web service northwind";
                $audience_claim = "harun noviar";
                $issuedate_claim = time();
                $notbefore_claim = $issuedate_claim + 10;
                $expire_claim = $issuedate_claim + 86400;
                $token = array(
                    "iss" => $issuer_claim,
                    "aud" => $audience_claim,
                    "iat" => $issuedate_claim,
                    "exp" => $expire_claim,
                    "data" => array(
                        "id" => $data[0]->id,
                        "firstname" => $data[0]->first_name,
                        "lastname" => $data[0]->last_name,
                        "email" => $data[0]->email,
                    ),
                );

                $jwt = JWT::encode($token, $secret_key);
                $result = [
                    "took" => $_SERVER['REQUEST_TIME_FLOAT'],
                    "code" => 200,
                    "message" => "Login successfull",
                    "token" => $jwt,
                ];
                $this->response($result, 200);
            } else {
                $result = [
                    "took" => $_SERVER['REQUEST_TIME_FLOAT'],
                    "code" => 401,
                    "message" => "Invalid password, Login failed",
                    "token" => null,
                ];
                $this->response($result, 401);
            }
        } else {
            $result = [
                "took" => $_SERVER['REQUEST_TIME_FLOAT'],
                "code" => 401,
                "message" => "Invalid email, Login failed",
                "token" => null,
            ];
            $this->response($result, 401);
        }
    }

}
