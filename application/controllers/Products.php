<?php
defined('BASEPATH') or exit('no direct script allowed here');
require APPPATH . '/libraries/REST_Controller.php';

use RestServer\Libraries\REST_Controller;

class Products extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    public function index_get()
    {
        $id = $this->get('id');
        $products = [];
        if ($id == '') {
            $data = $this->db->get('products')->result();
            // var_dump($data);
            // die();
            foreach ($data as $row => $key) :
                $products[] = [
                    "ProductID" => $key->ProductID,
                    "ProductName" => $key->ProductName,
                    "_links" =>
                    (object) [
                        "href" => "suppliers/{$key->SupplierID}",
                        "rel" => "suppliers",
                        "type" => "GET"
                    ],
                    (object) [
                        "href" => "categories/{$key->CategoryID}",
                        "rel" => "categories",
                        "type" => "GET"
                    ],
                    "QuantityPerUnit" => $key->QuantityPerUnit,
                    "UnitPrice" => $key->UnitPrice,
                    "UnitsInStock" => $key->UnitsInStock,
                    "UnitsOnOrder" => $key->UnitsOnOrder,
                    "ReorderLevel" => $key->ReorderLevel,
                    "Discontinued" => $key->Discontinued,
                ];
            endforeach;
        } else {
            $this->db->where('ProductID', $id);
            $data = $this->db->get('products')->result();
            $products = [
                "ProductID" => $data[0]->ProductID,
                "ProductName" => $data[0]->ProductName,
                "_links" => [
                    (object)[
                        "href" => "suppliers/{$data[0]->SupplierID}",
                        "rel" => "suppliers",
                        "type" => "GET"
                    ],
                    (object)[
                        "href" => "categories/{$data[0]->CategoryID}",
                        "rel" => "suppliers",
                        "type" => "GET"
                    ]
                ],
                "QuantityPerUnit" => $data[0]->QuantityPerUnit,
                "UnitPrice" => $data[0]->UnitPrice,
                "UnitsInStock" => $data[0]->UnitsInStock,
                "UnitsOnOrder" => $data[0]->UnitsOnOrder,
                "ReorderLevel" => $data[0]->ReorderLevel,
                "Discontinued" => $data[0]->Discontinued,
            ];

            $etag = hash('sha256', $data[0]->LastUpdate);
            $this->cache->save($etag, $products, 300);
            $this->output->set_header('ETag:' . $etag);
            $this->output->set_header('Cache-Control: must-revalidate');
            if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $etag) {
                $this->output->set_header('HTTP/1.1 304 Not Modified');
            } else {
                $result = [
                    "took" => $_SERVER['REQUEST_TIME_FLOAT'],
                    "code" => 200,
                    "message" => "Response successfully",
                    "data" => $products,
                ];
                $this->response($result, 200);
            }
        }
        $result = [
            "took" => $_SERVER['REQUEST_TIME_FLOAT'],
            "code" => 200,
            "message" => "Response successfully",
            "data" => $products,
        ];
        $this->response($result, 200);
    }
}
