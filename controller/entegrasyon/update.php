<?php

class ControllerEntegrasyonUpdate extends Controller
{


    public function orders()
    {

        /*
                                     $this->load->model('entegrasyon/order/hb');
                                     $res =$this->model_entegrasyon_order_hb->getOrders();
                                print_r($res);
                                return;
        */

        $stokUpdateData = array();
        $total_new = 0;

        $order_temp = 0;
        $ordered_product_temp = 0;
        $defined_ordered_product_temp = 0;
        $this->load->model('entegrasyon/general');
        $this->load->model('entegrasyon/order');
        $marketplaces = $this->model_entegrasyon_general->getMarketPlaces();
        $orders = array();

        foreach ($marketplaces as $marketplace) {
            if ($marketplace['status']) {
                $this->load->model('entegrasyon/order/' . $marketplace['code']);
                $orders[$marketplace['code']] = $this->{'model_entegrasyon_order_' . $marketplace['code']}->getOrders();
            }

        }

        $total_order_count = array();
        foreach ($orders as $code => $orderpatch) {


            $order_count = 0;
            foreach ($orderpatch as $order) {

                $order_temp++;

                if (!$this->model_entegrasyon_order->getOrder($order['order_id'])) {

                    //Satılan Ürünleri Tespit Edip Satılan Adedi kaydediyoruz.
                    foreach ($order['products'] as $product) {

                        $ordered_product_temp += $product['quantity'];
                        // echo  $product['model'].'--'.$product['quantity'].'<br>';
                        if (array_key_exists($product['model'], $stokUpdateData)) {
                            //echo $product['model'].'Zaten var';
                            $stokUpdateData[$product['model']] += $product['quantity'];

                            $defined_ordered_product_temp += $product['quantity'];

                        } else {
                            $defined_ordered_product_temp += $product['quantity'];
                            $stokUpdateData[$product['model']] = $product['quantity'];

                        }
                    }
                    //Stok Belirleme Sonu

                    //Siparişleri Veritabanına Yazdırıyoruz
                    $this->model_entegrasyon_order->addOrder($order, $code);
                    $order_count++;
                    $total_new++;

                }


            }

            $total_order_count[$code] = $order_count;


        }


        // echo 'Toplam Sipariş Adedi=' . $order_temp . '<br>' . ' SatılanToplam ürün Adedi=' . $ordered_product_temp . '<br>' . ' Belirlenebilen Sipariş Adedi' . $defined_ordered_product_temp;
        //print_r($stokUpdateData);

        //$stokUpdateData=array('AYK00010'=>5,'product-23'=>3,'PNT00138764372864tf'=>7,'product-22'=>2);
        //Satılan Ürünlerin Ürün kodu üzerinden Stok Güncellemesi Yapıyoruz
        foreach ($stokUpdateData as $model => $stok) {
            //$model='PNT00138764372864tf';
            $product_info = $this->entegrasyon->getProductByModel($model);
            if ($product_info) {

                //echo $model.' Satılan Adet='.$stok.' Mevcut Stok='.$product_info['quantity'].'<br>';
                $product_info['quantity'] -= $stok;
                //  echo 'Şimdiki Stok'.$product_info['quantity'].'<br>';

                $this->entegrasyon->updateStock($product_info);

            }
        }
        //Stok Güncellemesi Sonu

        //  $this->stock();
        echo json_encode(array('total' => $total_new, 'markets' => $total_order_count));

    }

 

    public function product()
    {
        date_default_timezone_set('Europe/Istanbul');
        $product_count = 0;

        $this->load->model("entegrasyon/general");
        // $last_update_date='2020-05-26 15:30:50';//$this->model_entegrasyon_general->getLastUpdateSession(1);
        $last_update_date = $this->config->get('module_entegrasyon_last_update');//$this->model_entegrasyon_general->getLastUpdateSession(1);
        $last_update_session_timestamp = strtotime($last_update_date);
        $marketPlaces = $this->model_entegrasyon_general->getMarketPlaces();

        //  echo "Son Güncelleme Tarihi=".$last_update_date.'<br>';

        $marketplace_products = $this->model_entegrasyon_general->getUpdatableProducts();

        if ($marketplace_products) {

            foreach ($marketplace_products as $marketplace_product) {
                //  $product_modified_date=strtotime($marketplace_product['date_modified']);
                $product = $this->entegrasyon->getProduct($marketplace_product['product_id']);

                //Son Güncelleme Tarihi ile sıradaki ürününn son güncelleme tarihini karşılaştır.
                //Update edilebilir en az 1 ürün vasa yeni bir update session Oluştur.
                // $update_session_id=$this->model_entegrasyon_general->createUpdateSession(1);
                foreach ($marketPlaces as $marketPlace) {
                    if ($marketPlace['status']) {

                        if ($marketplace_product[$marketPlace['code']]) {

                            // echo $product['name'].'-'.$marketPlace['name'].' de Güncellenebilir';
                            $product_info = $this->entegrasyon->getProductForUpdate($marketPlace['code'], $product);

                            $post_data['request_data'] = $product_info;
                            $post_data['market'] = $this->model_entegrasyon_general->getMarketPlace($marketPlace['code']);
                            $result = $this->entegrasyon->clientConnect($post_data, 'update_basic', $marketPlace['code']);

                            if ($result['status']) {

                                $marketplace_data = unserialize($product_info[$marketPlace['code']]);
                                $data = array('commission' => $product_info['defaults']['commission'], 'product_id' => $marketplace_data['product_id'], 'price' => $product_info['sale_price'], 'url' => $marketplace_data['url']);
                                $this->entegrasyon->addMarketplaceProduct($product_info['product_id'], $data, $marketPlace['code']);


                            } else {

                                $error = $this->entegrasyon->getError($product_info['product_id'], $marketPlace['code']);
                                if ($error) {
                                    $this->entegrasyon->updateError($product_info['product_id'], $marketPlace['code'], 3, $result['message']);
                                } else {
                                    $this->entegrasyon->addError($product_info['product_id'], $marketPlace['code'], 3, $result['message']);
                                }

                            }


                            echo $marketPlace['name'] . ' : ' . $product_info['name'] . 'Güncellendi<br>';
                            //print_r($result).'<br>';

                            //$variants =$this->entegrasyon->n11_variants($marketplace_product['product_id']);


                            // return;
                            //print_r($options);


                        }
                    } else {


                    }


                }


            }


            //Güncelleme yapabiriz;


        }else {


            echo 'Güncellenecek Ürün Bulunamadı! Güncelleme yapılabilemsi için en az bir pazaryerinde bulunan bir ürününüz olmalı ve bu ürünün özellileri Admin/katalog/product sayfasında değiştirilmiş olmalıdır.';

        }

        // echo 'hohoho';
        $query = $this->db->query("select Now() as last_date");
        $last_date = $query->row['last_date'];
        $this->entegrasyon->editSettingValue('module_entegrasyon', 'module_entegrasyon_last_update', $last_date);


    }


}

