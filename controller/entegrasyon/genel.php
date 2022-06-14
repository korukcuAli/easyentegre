<?php

class ControllerEntegrasyonGenel extends Controller
{



    public function currencies()
    {

        $currencies = array();
        $currencies[] = '';
        /* $currencies[] = 'TRY';
         $currencies[] = 'USD';
         $currencies[] = 'EUR';
        */


        foreach ($this->db->query("select code from ".DB_PREFIX."currency")->rows as $currency) {

            $currencies[]=$currency['code'];
        }

        echo json_encode($currencies);

    }


    public function getjson()
    {


        $filter = $this->request->get['filter_name'];
        $filter = str_replace(' ', '%20', $filter);
        $code = $this->request->get['code'];
        $page = $this->request->get['page'];
        $which=0;
        if (isset($_GET['category_code'])) {

            $category_code = explode('|',$this->request->get['category_code']);
            $url = "https://www.opencart.gen.tr/index.php?route=api/search/" . $code . "_" . $page . "&category_code=$category_code[0]&filter_name=" . $filter;
            $which=0;
        } else {

            $url = "https://www.opencart.gen.tr/index.php?route=api/search/" . $code . "_" . $page . "&filter_name=" . $filter;
            $which=1;

        }





        if (strlen($filter) >= 2) {
            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $result = curl_exec($ch);

            curl_close($ch);
            echo $result;

        }
    }


    public function membership()
    {

        $filter = $this->request->get['filter_name'];
        $filter = str_replace(' ', '%20', $filter);
        $code = $this->request->get['code'];
        $page = $this->request->get['page'];
        if (isset($_GET['category_code'])) {

            $category_code = $this->request->get['category_code'];
            $url = "http://www.opencart.gen.tr/index.php?route=api/membership/" . $code . "_" . $page . "&category_code=$category_code&filter_name=" . $filter;

        } else {

            $url = "http://www.opencart.gen.tr/index.php?route=api/membership/" . $code . "_" . $page . "&filter_name=" . $filter;

        }

        if (strlen($filter) >= 3) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            $result = curl_exec($ch);

            curl_close($ch);
            echo $result;

        }
    }


    public function n11_shipping_templates()
    {
        $post_data['request_data'] = array();


        $result = $this->entegrasyon->clientConnect($post_data, 'shipping_templates', 'n11',false);



        echo json_encode($result['result']);

    }

    public function get_ty_addreses()
    {
        $post_data['request_data'] = array();

        $result = $this->entegrasyon->clientConnect($post_data, 'shipping_templates', 'ty',false);

        $adresses=array();
        foreach ($result['result']['supplierAddresses'] as $supplierAddress) {
            if($this->request->get['type'] == $supplierAddress['addressType'] ){
                $adresses[]=array('value'=>$supplierAddress['id'],'text'=>mb_substr($supplierAddress['address'],0,30).'...');

            }
        }


        echo json_encode($adresses);

    }


    public function save_setting($arg=array())
    {



        $message = '';
        $alert_type = 0;
        $solution_url = '';
        $code = $arg?$arg['code']:$this->request->get['code'];
        $primary_id =$arg?$arg['primary_id']:$this->request->post['pk'];
        $name =$arg?$arg['name']: $this->request->post['name'];


        if ($this->config->get('easy_setting_auto_update_price')){
            if ($name == $code.'_product_sale_price'){

                $marketplace_data = $this->entegrasyon->getMarketPlaceProductForMarket($primary_id, $code);
                if ($marketplace_data){
                    //ÜRÜNÜ GÜNCELLE

                }

                //   $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product WHERE product_id = $primary_id");

                //     $post_data = $query->row;

                //   $model = $query->row['model'];

                //  $query2 = $this->db->query("SELECT * FROM " . DB_PREFIX . "es_product_to_marketplace WHERE product_id = $primary_id");
                //  $post_data = $query2->row;

                // if ($query2->row['' . $code . '']){

                //    $result = $this->entegrasyon->clientConnect($post_data, 'update_basic', $code, false, false);
                //  print_r($result['status']);

                // }

            }

        }
        $value =$arg?$arg['value'] : $this->request->post['value'];
        $controller = $arg?$arg['controller'] : $this->request->get['controller'];
        $action = isset($this->request->get['action']) ? $this->request->get['action'] : false;
        $attribute_result = array();
        $json_data = array();
        if ($name == "selected_attributes" ){
            foreach ($value as $data) {
                $data['value'] = preg_replace("/\s+/", " ", $data['value']);
                $data['value'] = trim($data['value']);
                if ($data['value']){
                    $values[]=$data;
                }

            }
        }


        //  print_r($code.'+'.$primary_id.'+'.$name.'+'.$value.'+'.$controller);
        // return;


//Komisyon oranı değiştiğinde Kullacıyı Fiyat Güncelleme için uyar.
        if (($controller == "category" || $controller == 'manufacturer') && $name == $code . '_commission' && $value) {

            $this->load->model('entegrasyon/product');
            $filter_data = array(
                'filter_marketplace' => $code,
                'filter_marketplace_do' => 1,
                'filter_' . $controller => $primary_id

            );


            $getProducts = $this->model_entegrasyon_product->getProducts($filter_data);



            if ($getProducts) {
                $alert_type = 2;
                $solution_url = 'index.php?route=entegrasyon/product/update_bulk&total=' . count($getProducts) . '&commission=' . $value . '&controller=' . $controller . '&code=' . $code . '&' . $controller . '=' . $primary_id;
                $message .= 'Fiyat artış oranı güncellendi. ' . count($getProducts) . ' Adet Ürünün Fiyatını Şimdi Güncellemek İstermisiniz ?';

            }

        }
        //Komisyon Uyarısı Sonu

        if (($controller == "category" && $action == 'category_match' && $value != '')) {

            $is_exists = strpos($value, '|');
            if (!$is_exists) {

                $json_data = array('status' => false, 'message' => 'Sadece listedeki kategorilerden birini seçmelisiniz!', 'alert_type' => 2);

                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($json_data));
                return;
            }
        }

        /*
                if (($controller == "manufacturer" &&  $value != '')) {

                    $is_exists = strpos($value, '|');
                    if (!$is_exists) {

                        $json_data = array('status' => false, 'message' => 'Sadece listedeki markalardan birini seçmelisiniz!', 'alert_type' => 2);
                        $this->response->addHeader('Content-Type: application/json');
                        $this->response->setOutput(json_encode($json_data));
                        return;
                    }
                }
        */

        if ($controller == "category" && $action == 'category_match') {


            if ($value) {


                $this->load->model('entegrasyon/category');
                $attribute_result = $this->model_entegrasyon_category->getAttributes(explode('|', $value)[0], $code);


                if($code=='gg' || $code=='ty' || $code=='hb' || $code=='cs') {
                    if ($attribute_result['result']['variants']) {
                        $val_arr = explode('|', $value);
                        $category_id = $val_arr[0];
                        $json_data['option'] = $attribute_result['result']['variants'];
                        $json_data['category_id'] = $category_id;
                        $message .= 'Seçenekleri eşleştirmeniz gerekli';

                        //$this->response->addHeader('Content-Type: application/json');
                        //$this->response->setOutput(json_encode($json_data));

                    }
                }

            }else {

                if($name==$code.'_category_id'){

                    $this->deleteAllProductsAndCategorySettingByMatchedCategory($code,$primary_id);

                    // return;
                    $json_data = array('status' => true, 'message' => 'Kategoriye bağlı tüm ayarlar silindi!', 'alert_type' => 2);

                    $this->response->addHeader('Content-Type: application/json');
                    $this->response->setOutput(json_encode($json_data));
                    return;
                }

            }
        }


        try {


            $query = $this->db->query("select * from " . DB_PREFIX . "es_" . $controller . " where " . $controller . "_id='" . $primary_id . "'");

            if ($query->num_rows) {
                $row = $query->row;

                $variable = $row['' . $code . ''];


                if ($variable) {
                    $settings = unserialize($variable);

                    $temp=isset($settings[$name])?$settings[$name]:0;
                    $settings[$name] = $value;

                    if (!$value) {

                        unset($settings[$name]);
                    }


                    try {
                        if ($settings) {


                            $this->db->query("update " . DB_PREFIX . "es_" . $controller . " SET $code='" . $this->db->escape(serialize($settings)) . "', date_modified=NOW() where " . $controller . "_id='" . $primary_id . "' ");
                            $message .= 'Güncellendi';

                        } else {

                            $this->db->query("update " . DB_PREFIX . "es_" . $controller . " SET $code='', date_modified=NOW() where " . $controller . "_id='" . $primary_id . "' ");
                            $message .= 'Güncellendi';
                        }




                        if($controller == "category" && $name==$code.'_category_id' ){



                            $cat_info=explode('|',$temp);
                            $sql="DELETE FROM ".DB_PREFIX."es_attribute where category_id='".$cat_info[0]."' and  `code`='".$code."'";

                            $this->db->query($sql);

                        }


                    } catch (Exception $exception) {

                        $message .= $exception->getMessage();

                        echo $message;

                    }

                } else {

                    if(isset($this->request->post['name'])){
                        $insert_data = array($this->request->post['name'] => $this->request->post['value']);

                        $this->db->query("update " . DB_PREFIX . "es_" . $controller . " SET $code='" . $this->db->escape(serialize($insert_data)) . "',date_modified=NOW() where " . $controller . "_id='" . $primary_id . "' ");
                        $message .= 'Güncellendi';
                    }else {

                        $message .= 'Güncellenemedi';
                    }

                }


            } else {
                //Yeni Kayıt Oluştur;
                $insert_data = array(isset($this->request->post['name'])?$this->request->post['name']:"" => isset($this->request->post['value'])?$this->request->post['value']:"");
                $this->db->query("insert into " . DB_PREFIX . "es_" . $controller . " SET $code='" . $this->db->escape(serialize($insert_data)) . "', " . $controller . "_id='" . $primary_id . "', date_added=NOW() ");
                $message .= 'Eklendi';
            }
        } catch (Exception $exception) {


            echo $exception->getMessage();

        }


        $json_data['status'] = true;
        $json_data['message'] = $message;
        $json_data['alert_type'] = $alert_type;
        if ($solution_url) $json_data['url'] = $solution_url;
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json_data));

    }


    public function deleteCategory_attributes($code, $category_id)
    {

        // $code='gg';

        //$category_id=504;

        $query =  $this->db->query("select * from ".DB_PREFIX."es_category where category_id='".$category_id."'");
        $market_category_info=unserialize($query->row[$code]);

        if(isset($market_category_info[$code.'_category_id'])){

            $final_category_id=explode('|',$market_category_info[$code.'_category_id'])[0];

            $this->db->query("delete from ".DB_PREFIX."es_attribute  where category_id='".$final_category_id."'");

        }


    }

    private function deleteAllProductsAndCategorySettingByMatchedCategory($code,$primary_id)
    {
        $filter_data = array(
            // 'filter_marketplace' => $code,
            'filter_category' => $primary_id

        );

        $this->load->model('entegrasyon/product');

        $getProducts = $this->model_entegrasyon_product->getProducts($filter_data);



        if ($getProducts) {

            foreach ($getProducts as $product) {



                $query = $this->db->query("select * from " . DB_PREFIX . "es_product  where product_id='" . $product['product_id'] . "'");

                if ($query->num_rows) {
                    $row = $query->row;

                    $variable = $row['' . $code . ''];


                    if ($variable) {
                        $settings = unserialize($variable);

                        if (isset($settings['selected_attributes'])) {

                            unset($settings['selected_attributes']);
                            $this->db->query("update " . DB_PREFIX . "es_product set  $code ='" . $this->db->escape(serialize($settings)) . "' where product_id='" . $product['product_id'] . "' ");

                        }

                    }


                }}

        }


        $this->deleteCategory_attributes($code,$primary_id);
        $this->db->query("update ".DB_PREFIX."es_category set ".$code."='' where category_id='".$primary_id."' ");


    }


}





