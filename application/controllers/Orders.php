<?php
defined('BASEPATH') or exit('no direct script allowed here');
require APPPATH . '/libraries/REST_Controller.php';

use RestServer\Libraries\REST_Controller;

class Orders extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
    }

    // Menamplikan data
    public function index_get()
    {
        $id = $this->get('id');
        $orders = [];
        if ($id == '') {
            $data = $this->db->get('order_details')->result();
            foreach ($data as $row => $key) :
                $orders[] = [
                    "odID" => $key->odID,
                    "_links" => [
                        (object)[
                            "href" => "orders/{$key->OrderID}",
                            "rel" => "orders",
                            "type" => "GET"
                        ],
                        (object)[
                            "href" => "products/{$key->ProductID}",
                            "rel" => "products",
                            "type" => "GET"
                        ],
                        "UnitPrice" => $key->UnitPrice,
                        "Quantity" => $key->Quantity,
                    ]
                ];
            endforeach;

            $etag = hash('sha256', time());
            $this->cache->save($etag, $orders, 300);
            $this->output->set_header('ETag:' . $etag);
            $this->output->set_header('Cache-Control: must-revalidate');
            if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $etag) {
                $this->output->set_header('HTTP/1.1 304 Not Modified');
            } else {
                $result = [
                    "took" => $_SERVER['REQUEST_TIME_FLOAT'],
                    "code" => 200,
                    "message" => "Response successfully",
                    "data" => $orders,
                ];
                $this->response($result, 200);
            }
        } else {
            $this->db->where('orderID', $id);
            $data = $this->db->get('order_details')->result();
            $orders[] = [
                "odID" => $data[0]->odID,
                "_links" => [
                    (object)[
                        "href" => "orders/{$data[0]->OrderID}",
                        "rel" => "orders",
                        "type" => "GET"
                    ],
                    (object)[
                        "href" => "products/{$data[0]->ProductID}",
                        "rel" => "products",
                        "type" => "GET"
                    ]
                ],
                "UnitPrice" => $data[0]->UnitPrice,
                "Quantity" => $data[0]->Quantity,
            ];

            $etag = hash('sha256', time());
            $this->cache->save($etag, $orders, 300);
            $this->output->set_header('ETag:' . $etag);
            $this->output->set_header('Cache-Control: must-revalidate');
            if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $etag) {
                $this->output->set_header('HTTP/1.1 304 Not Modified');
            } else {
                $result = [
                    "took" => $_SERVER['REQUEST_TIME_FLOAT'],
                    "code" => 200,
                    "message" => "Response successfully",
                    "data" => $orders,
                ];
                $this->response($result, 200);
            }
        }
    }

    // Menambahkan data
    public function index_post()
    {
        $data = array(
            "OrderID" => $this->post('OrderID'),
            "ProductID" => $this->post('ProductID'),
            "UnitPrice" => $this->post('UnitPrice'),
            "Quantity" => $this->post('Quantity'),
            "Discount" => $this->post('Discount'),
        );
        $this->db->where('ProductID', $this->post('ProductID'));
        $this->db->where('Quantity', $this->post('Quantity'));
        $check = $this->db->get("order_details")->num_rows();
        if ($check == 0) :
            $insert = $this->db->insert("order_details", $data);
            if ($insert) {
                $result = [
                    "took" => $_SERVER['REQUEST_TIME_FLOAT'],
                    "code" => 201,
                    "message" => "Data has successfully added",
                    "data" => $data,
                ];
                $this->response($result, 201);
            } else {
                $result = [
                    "took" => $_SERVER['REQUEST_TIME_FLOAT'],
                    "code" => 502,
                    "message" => "Failed adding data",
                    "data" => null,
                ];
                $this->response($result, 502);
            }
        else :
            $result = [
                "took" => $_SERVER['REQUEST_TIME_FLOAT'],
                "code" => 304,
                "message" => "Data already added",
                "data" => $data,
            ];
            $this->response($result, 304);
        endif;
    }

    // Update data
    public function index_put()
    {
        $id = $this->put('id');
        $data = array(
            "ProductID" => $this->put('ProductID'),
            "UnitPrice" => $this->put('UnitPrice'),
            "Quantity" => $this->put('Quantity'),
            "Discount" => $this->put('Discount'),
        );
        $this->db->where('odID', $id);
        $update = $this->db->update('order_details', $data);
        if ($update) {
            $this->response($id, 200);
        } else {
            $this->response(array("status" => "fail", 502));
        }
    }

    // Menghapus data
    public function index_delete()
    {
        $id = $this->delete('id');
        $this->db->where('odID', $id);
        $check = $this->db->get('order_details')->num_rows();
        if ($check == 0) :
            $this->output->set_header('HTTP/1.1 304 Not Modified');
        else :
            $this->db->where('odID', $id);
            $delete = $this->db->delete('order_details');
            if ($delete) {
                $this->response(array("status" => "success", 200));
            } else {
                $this->response(array("status" => "fail", 502));
            }
        endif;
    }
}
