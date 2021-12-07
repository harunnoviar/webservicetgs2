<?php
defined('BASEPATH') or exit('no direct script allowed here');
require APPPATH . '/libraries/REST_Controller.php';
require "vendor/autoload.php";

use RestServer\Libraries\REST_Controller;
use \Firebase\JWT\JWT;

class Customers extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
    }

    // Menampilkan data
    public function index_get()
    {
        $authHeader = $this->input->get_request_header('Authorization');
        $arr = explode(" ", $authHeader);
        $jwt = isset($arr[1]) ? $arr[1] : "";
        $secretkey = base64_encode("gampang");
        if ($jwt) {
            try {
                $decoded = JWT::decode($jwt, $secretkey, array('HS256'));

                $id = $this->get('id');
                if ($id == '') {
                    $data = $this->db->get('customers')->result();
                } else {
                    $this->db->where('CustomerID', $id);
                    $data = $this->db->get('customers')->result();
                }
                $result = [
                    "took" => $_SERVER['REQUEST_TIME_FLOAT'],
                    "code" => 200,
                    "message" => "Response Successfully",
                    "data" => $data,
                ];
                $this->response($result, 200);
            } catch (Exception $e) {
                $result = [
                    "took" => $_SERVER['REQUEST_TIME_FLOAT'],
                    "code" => 401,
                    "message" => "Access denied",
                    "data" => null,
                ];
                $this->response($result, 401);
            }
        } else {
            $result = [
                "took" => $_SERVER['REQUEST_TIME_FLOAT'],
                "code" => 401,
                "message" => "Access denied",
                "data" => null,
            ];
            $this->response($result, 401);
        }
    }

    public function index_post()
    {
        $data = array(
            "CustomerID" => $this->post('CustomerID'),
            "CompanyName" => $this->post('CompanyName'),
            "ContactName" => $this->post('ContactName'),
            "ContactTitle" => $this->post('ContactTitle'),
            "Address" => $this->post('Address'),
            "City" => $this->post('City'),
            "Region" => $this->post('Region'),
            "PostalCode" => $this->post('PostalCode'),
            "Country" => $this->post('Country'),
            "Phone" => $this->post('Phone'),
            "Fax" => $this->post('Fax'),
        );

        $insert = $this->db->insert('customers', $data);
        if ($insert) {
            $result = [
                'took' => $_SERVER['REQUEST_TIME_FLOAT'],
                'code' => 201,
                'message' => 'Data has successfully added',
                'data' => $data,
            ];
            $this->response($result, 201);
        } else {
            $result = [
                'took' => $_SERVER['REQUEST_TIME_FLOAT'],
                'code' => 502,
                'message' => 'Failed adding data',
                'data' => null,
            ];
            $this->response($result, 502);
        }
    }

    public function index_put()
    {
        $id = $this->put('id');
        $data = array(
            "CustomerID" => $this->put('CustomerID'),
            "CompanyName" => $this->put('CompanyName'),
            "ContactName" => $this->put('ContactName'),
            "ContactTitle" => $this->put('ContactTitle'),
            "Address" => $this->put('Address'),
            "City" => $this->put('City'),
            "Region" => $this->put('Region'),
            "PostalCode" => $this->put('PostalCode'),
            "Country" => $this->put('Country'),
            "Phone" => $this->put('Phone'),
            "Fax" => $this->put('Fax'),
        );
        $this->db->where('CustomerID', $id);
        $update = $this->db->update('customers', $data);
        if ($update) {
            $this->response($data, 200);
        } else {
            $this->response(array('status' => 'fail'), 200);
        }
    }

    public function index_delete()
    {
        $id = $this->delete('id');
        $delete = $this->db->where('CustomerID', $id)->delete('customers');
        if ($delete) {
            $this->response(array('status' => 'Success'), 201);
        } else {
            $this->response(array('status' => 'Fail'), 502);
        }
    }
}
