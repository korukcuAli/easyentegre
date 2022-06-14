<?php

class ControllerEntegrasyonCategory extends Controller
{
    private $error = array();
    private $token_data;

    public function __construct($registry)
    {

        parent::__construct($registry);

        $this->load->model('entegrasyon/general');
        $this->token_data = $this->model_entegrasyon_general->getToken();

        $this->marketplaces = $this->model_entegrasyon_general->getActiveMarkets();


        if (!$this->config->get('mir_login')) {

            $this->response->redirect($this->url->link('entegrasyon/setting/error', '&error=no_user&' . $this->token_data['token_link'], true));

        } else if (!$this->marketplaces) {

            $this->response->redirect($this->url->link('entegrasyon/setting/error', '&error=no_api&' . $this->token_data['token_link'], true));

        } else if (!$this->config->get('module_entegrasyon_status')) {
            $this->response->redirect($this->url->link('entegrasyon/setting/error', '&error=no_module&' . $this->token_data['token_link'], true));

        }

    }


    public function index()
    {
        $this->load->language('entegrasyon/category');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('entegrasyon/category');
        $this->load->model('entegrasyon/general');

        $this->getList();
    }


    protected function getList()
    {

        $data = $this->language->all();
        if (isset($this->request->get['filter_category'])) {
            $filter_category = $this->request->get['filter_category'];
        } else {
            $filter_category = '';
        }

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'name';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $url = '';
        if (isset($this->request->get['filter_category'])) {
            $url .= '&filter_category=' . urlencode(html_entity_decode($this->request->get['filter_category'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $this->model_entegrasyon_general->loadPageRequired();

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $this->token_data['token_link'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('entegrasyon/category', $this->token_data['token_link'] . $url, true)
        );

        $data['easy_visibility'] = $this->config->get('easy_visibility') ? '' : 'hidden';

        $data['categories'] = array();

        $filter_data = array(
            'sort' => $sort,
            'order' => $order,
            'filter_category' => $filter_category,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        );

        $marketplaces = $this->model_entegrasyon_general->getMarketPlaces();
        foreach ($marketplaces as $marketplace) {

            if ($marketplace['status']) {
                $filter_data[$marketplace['code']] = true;
            }

        }
        $data['marketplaces'] = $marketplaces;


        $category_total = $this->model_entegrasyon_category->getTotalCategories($filter_data);

        $results = $this->model_entegrasyon_category->getCategories($filter_data);


        $data['categories'] = $results;


        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        if (isset($this->request->post['selected'])) {
            $data['selected'] = (array)$this->request->post['selected'];
        } else {
            $data['selected'] = array();
        }

        $url = '';

        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }


        if ($filter_category) {
            $this->load->model('entegrasyon/category');
            $category_info = $this->model_entegrasyon_category->getCategory($filter_category);
            $data['filter_category_name'] = $category_info['name'];
        } else {
            $data['filter_category_name'] = '';
        }


        $data['filter_category'] = $filter_category;


        $data['token_link'] = $this->token_data['token_link'];
        $data['sort_name'] = $this->url->link('entegrasyon/category', $this->token_data['token_link'] . '&sort=name' . $url, true);
        $data['sort_sort_order'] = $this->url->link('entegrasyon/category', $this->token_data['token_link'] . '&sort=sort_order' . $url, true);

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $pagination = new Pagination();
        $pagination->total = $category_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('entegrasyon/category', $this->token_data['token_link'] . $url . '&page={page}', true);

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($category_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($category_total - $this->config->get('config_limit_admin'))) ? $category_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $category_total, ceil($category_total / $this->config->get('config_limit_admin')));

        $data['add'] = $this->url->link('entegrasyon/category/add', $this->token_data['token_link'] . $url, true);
        $data['delete'] = $this->url->link('entegrasyon/category/delete', $this->token_data['token_link'] . $url, true);
        $data['repair'] = $this->url->link('entegrasyon/category/repair', $this->token_data['token_link'] . $url, true);


        $data['sort'] = $sort;
        $data['order'] = $order;

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('entegrasyon/category_list', $data));
    }


    public function setting()
    {

        $code = $this->request->get['code'];
        $category_id = $this->request->get['category_id'];
        $data['market_category_id'] = 0;
        $this->load->model("entegrasyon/general");
        $this->load->model("entegrasyon/category");


        $data = $this->entegrasyon->getSettingData($code, 'category', $category_id);

        if (isset($data[$code . '_category_id'])) {

            $val = explode('|', $data[$code . '_category_id']);

            if ($val[0]) {

                $attributes = $this->model_entegrasyon_category->getAttributes($val[0], $code);

                if ($code == 'gg' || $code == 'ty' || $code == 'hb' || $code == 'cs') {
                    $data['having_options'] = $attributes['result']['variants'] ? true : false;

                } else {

                    $data['having_options'] = false;

                }

                $data['market_category_id'] = $val[0];

            }


        } else {
            $data['having_options'] = false;

        }

        $data['token_link'] = $this->token_data['token_link'];
        $data['category_id'] = $category_id;
        $data['easy_setting_store_category'] = $this->config->get('easy_setting_store_category');

        $this->response->setOutput($this->load->view('entegrasyon/category/' . $code, $data));


    }


    public function autocomplete()
    {
        $json = array();

        if (isset($this->request->get['filter_name'])) {
            $this->load->model('entegrasyon/category');

            $filter_data = array(
                'filter_name' => $this->request->get['filter_name'],
                'sort' => 'name',
                'order' => 'ASC',
                'start' => 0,
                'limit' => 5
            );

            $results = $this->model_entegrasyon_category->getCategories($filter_data);

            foreach ($results as $result) {
                $json[] = array(
                    'category_id' => $result['category_id'],
                    'name' => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
                );
            }
        }

        $sort_order = array();

        foreach ($json as $key => $value) {
            $sort_order[$key] = $value['name'];
        }

        array_multisort($sort_order, SORT_ASC, $json);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function is_category_match()
    {
        $oc_category_id = $this->request->post['category_id'];
        $code = $this->request->get['code'];


        $this->load->model('entegrasyon/general');
        $this->load->model('entegrasyon/product');
        $matched_category = $this->entegrasyon->getMatchedCategory($oc_category_id, $code);


        $filter_data = array(

            'filter_category' => $oc_category_id,
            'start' => 0,
            'filter_marketplace' => $code,
            'filter_marketplace_do' => 0,
            'filter_sub_category' => true,

            'limit' => 100000
        );


        $product_total = $this->model_entegrasyon_product->getTotalProducts($filter_data);


        if (isset($matched_category[$code . '_category_id'])) {

            $category_info = explode('|', $matched_category[$code . '_category_id']);
            $category_id = $category_info[0];
            $category_name = $category_info[1];

            echo json_encode(array('status' => true, 'oc_category_id' => $oc_category_id, 'category_id' => $category_id, 'category_name' => $category_name));

        } else {
            echo json_encode(array('status' => false, 'total_products' => $product_total));
        }
    }


    public function reset_categories()
    {
        $this->db->query("TRUNCATE TABLE " . DB_PREFIX . "es_category");
    }

    public function match_option_form()
    {


        $category_id = $this->request->get['category_id'];

        $code = $this->request->get['code'];
        // $this->load->model('entegrasyon/category/' . $code);
        $this->load->model('entegrasyon/category');
        $attributes = $this->model_entegrasyon_category->getAttributes($category_id, $code);

        $data['marketplace_options'] = $attributes['result']['variants'];
        //$this->{'model_entegrasyon_category_' . $code}->getCategoryOptions($category_id);

        $this->load->model('entegrasyon/general');

        $data['marketplace'] = $this->model_entegrasyon_general->getMarketPlace($code);

        $data['oc_options'] = $this->entegrasyon->getOcOptions();

        $data['matched_options'] = $this->entegrasyon->getMatchedOptions($category_id);

        $data['category_id'] = $category_id;

        $data['code'] = $code;
        $data['token_link'] = $this->token_data['token_link'];
        $this->response->setOutput($this->load->view('entegrasyon/category/option_match', $data));


        // print_r($data['marketplace_options']);

    }

    public function match_value_form()
    {

        $market_option_id = $this->request->get['marketplace_option_id'];


        $matched_option_id = $this->request->get['matched_option_id'];
        $category_id = $this->request->get['category_id'];

        $query = $this->db->query("select * from " . DB_PREFIX . "es_option where option_id='" . $matched_option_id . "'");
        $matched_option_info = $query->row;


        $data['market_option_name'] = $matched_option_info['market_option_name'];
        $code = $matched_option_info['code'];


        $data['oc_option_values'] = $this->entegrasyon->getOcOPtionValues($matched_option_info['oc_option_id'], $matched_option_id);


        $this->load->model('entegrasyon/general');

        $data['marketplace'] = $this->model_entegrasyon_general->getMarketPlace($code);

        $this->load->model('entegrasyon/category/' . $code);

        $this->load->model('entegrasyon/category');
        $attributes = $this->model_entegrasyon_category->getAttributes($category_id, $code);

        $data['marketplace_options'] = $attributes['result']['variants'];//$this->{'model_entegrasyon_category_' . $code}->getCategoryOptions($category_id);


        $market_option_values = array();
        foreach ($data['marketplace_options'] as $values) {
            if ($values['id'] == $market_option_id) {
                if ($values['values']) {
                    foreach ($values['values'] as $value) {

                        $market_option_values[] = mb_strtoupper($value['name']);

                    }
                }else {
//seçnek değeri yok o yüzden tüm seçenekleri eşleşmiş sayıyoruz
                    foreach ($data['oc_option_values'] as $oc_option_value) {

                            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "es_option_value where matched_option_id='" . $matched_option_id . "' and oc_option_value_id='" . $oc_option_value['option_value_id'] . "'");
                            if (!$query->num_rows) {

                                $market_option_value_info = array('value_id'=>$oc_option_value['name'],'name'=>$oc_option_value['name'],'order_number'=>999);
                                // print_r($market_option_value_info);
                                $this->db->query("INSERT INTO " . DB_PREFIX . "es_option_value SET matched_option_id='" . $matched_option_id . "', oc_option_value_id='" . $oc_option_value['option_value_id'] . "', market_option_value_id='" . $market_option_value_info['value_id'] . "',market_option_value_name='" . $market_option_value_info['name'] . "',market_option_value_order='" . $market_option_value_info['order_number'] . "'");

                        }


                    }

                    //seçnek değeri yok o yüzden tüm seçenekleri eşleşmiş sayıyoruz- sonu

                }



            }

        }


        foreach ($data['oc_option_values'] as $oc_option_value) {


            if (in_array(mb_strtoupper($oc_option_value['name']), $market_option_values)) {


                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "es_option_value where matched_option_id='" . $matched_option_id . "' and oc_option_value_id='" . $oc_option_value['option_value_id'] . "'");
                if (!$query->num_rows) {

                    $market_option_value_info = $this->findMarketOptionValueInfo($oc_option_value['name'], $data['marketplace_options']);
                    // print_r($market_option_value_info);


                    $this->db->query("INSERT INTO " . DB_PREFIX . "es_option_value SET matched_option_id='" . $matched_option_id . "', oc_option_value_id='" . $oc_option_value['option_value_id'] . "', market_option_value_id='" . $market_option_value_info['value_id'] . "',market_option_value_name='" . $market_option_value_info['name'] . "',market_option_value_order='" . $market_option_value_info['order_number'] . "'");

                }

            }


        }


        $data['matched_option_values'] = $this->entegrasyon->getMatchedOptionValues($matched_option_id);
        $data['oc_option_values'] = $this->entegrasyon->getOcOPtionValues($matched_option_info['oc_option_id'], $matched_option_id);

        //  print_r( $data['oc_option_values']);
        // return;

        // $data['marketplace'] = $this->marketPlaces[$code];


        $data['matched_option_id'] = $matched_option_id;
        $data['marketplace_option_id'] = $matched_option_info['market_option_id'];
        $data['code'] = $code;
        $data['category_id'] = $category_id;
        $data['token_link'] = $this->token_data['token_link'];

        $this->response->setOutput($this->load->view('entegrasyon/category/value_match', $data));

    }

    private function findMarketOptionValueInfo($name, $options)
    {


        foreach ($options as $values) {


            if ($values['values']) {
                foreach ($values['values'] as $value) {

                    if (mb_strtoupper($name) == mb_strtoupper($value['name'])) {
                        return $value;
                    }


                }
            } else {

                return $name;
            }
        }

    }

    public function match_option_value($data)
    {


        $oc_option_value_id = $data['oc_option_value_id'];
        $market_option_value_id = $data['market_option_value_id'];
        $market_option_value_order_number = $data['market_option_value_order_number'];
        $market_option_value_name = $data['market_option_value_name'];
        $matched_option_id = $data['matched_option_id'];

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "es_option_value where matched_option_id='" . $matched_option_id . "' and oc_option_value_id='" . $oc_option_value_id . "'");

        if ($query->num_rows == 0) {

            $this->db->query("INSERT INTO " . DB_PREFIX . "es_option_value SET matched_option_id='" . $matched_option_id . "', oc_option_value_id='" . $oc_option_value_id . "', market_option_value_id='" . $market_option_value_id . "',market_option_value_name='" . $market_option_value_name . "',market_option_value_order='" . $market_option_value_order_number . "'");
            $message = "Seçenek Değeri Eşleştirildi";
            $status = true;
        } else {
            $option_value_id = $query->row;
            $this->db->query("UPDATE " . DB_PREFIX . "es_option_value SET matched_option_id='" . $matched_option_id . "', oc_option_value_id='" . $oc_option_value_id . "', market_option_value_id='" . $market_option_value_id . "',market_option_value_name='" . $market_option_value_name . "',market_option_value_order='" . $market_option_value_order_number . "' WHERE option_value_id='" . $option_value_id['option_value_id'] . "'");
            $message = "Seçenek Değeri Güncellendi";
            $status = false;
        }

        return array('status' => $status, 'message' => $message);

    }

    public function default_attributes()
    {

        $code = $this->request->get['code'];

        if (isset($this->request->get['category_id'])) {
            $category_id = $this->request->get['category_id'];
        } else {
            $category_id = 0;
        }
        $category_setting = $this->entegrasyon->getSettingData($code, 'category', $category_id);
        $data['s_attr'] = array();
        $data['s_attr_name'] = array();


        //print_r($category_setting);
        //return;

        //  $data['is_varianter_product'] = $this->entegrasyon->isVarianterProduct($product_id);

//print_r($category_setting['selected_attributes']);
//return;

        $getOptionsNames = array();
        if (isset($category_setting['selected_attributes'])) {

            foreach ($category_setting['selected_attributes'] as $key => $selected_attribute) {


                if ($code == 'hb') {
                    $data['s_attr'][$key] = str_replace("''", 'x_x', $selected_attribute['name']) . $selected_attribute['value'];
                } else {
                    $data['s_attr'][$key] = str_replace("''", 'x_x', $selected_attribute['value']);

                }
                $data['s_attr_name'][$key] = $selected_attribute['name'];

            }

        }

        //print_r($category_setting['selected_attributes']);return;

        // print_r($data['s_attr']);return;


        $category_name = '';
        $category_source = '';
        $attributes = array();

        /*    if($category_id){

                $this->load->model('entegrasyon/category');
                $category_setting = $this->model_entegrasyon_category->getMarketCategory($category_id,$code);

            }*/

        // $data['marketplace_data']=$this->entegrasyon->getMarketPlaceProductForMarket($product_id,$code);


        if (isset($product_setting[$code . '_category_id'])) {


            $category_info = explode('|', $product_setting[$code . '_category_id']);
            $category_id = $category_info[0];
            $category_name = $category_info[1];
            $category_source = 'Ürüne Özel Kategori';


        } else if (isset($category_setting[$code . '_category_id'])) {

            if ($category_setting[$code . '_category_id']) {
                $category_info = explode('|', $category_setting[$code . '_category_id']);

                $category_id = $category_info[0];
                $category_name = $category_info[1];
                $category_source = 'Kategori Eşlemesi';

            }
        }

        if ($category_id) {



                //$this->load->model('entegrasyon/category/' . $code);

                //$attributes = $this->{'model_entegrasyon_category_' . $code}->getAttributes($category_id);

                $data['category_id'] = $category_id;
                $data['product_id'] = 0;
                $data['category_name'] = $category_name;
                $data['category_source'] = $category_source;
                $data['token_link'] = $this->token_data['token_link'];

                $this->load->model('entegrasyon/category');

                //$is_product_varianter=$this->entegrasyon->isVarianterProduct($product_id);

                /*     if($code=='ty'){

                         $optionNames =  $this->entegrasyon->getOptionNames($product_id);

                         $isexists= $this->config->get('ty_setting_color');
                         if($isexists && $this->entegrasyon->findColor($optionNames)){

                             $data['renk']=true;


                         }


                     }*/
                $data['send_variant'] = $this->config->get($code . '_setting_variant');
               /*
                if ($this->config->get($code . '_setting_variant') && ($code == 'ty' || $code == 'gg' || $code == 'hb' || $code == 'cs')) {

                    $attributes = $this->model_entegrasyon_category->getAttributes($category_id, $code, true);

                }*/

                //else {
                    $attributes = $this->model_entegrasyon_category->getAttributes($category_id, $code, false);




         //   }


            /*
                 $getOptionsNames = $this->entegrasyon->getOptionNames($product_id); */

            if (isset($attributes['result']['required_attributes'])) unset($attributes['result']['required_attributes']);
            if (isset($attributes['result']['variants'])) unset($attributes['result']['variants']);


        } else {

            $this->load->model('entegrasyon/general');
            $marketPlace = $this->model_entegrasyon_general->getMarketPlace($code);
            $data['message'] = 'Lütfen Önce Ürünün bulunduğu kategoriyi eşleştirin yada ürüne özel bir <strong>' . $marketPlace['name'] . '</strong> kategorisi seçin, her ikisinide seçmeniz durumunda ürüne özel kategori dikkate alınacaktır. Ürüne özel kategori belirleme özelliğini aktif etmek için genel ayarlar kısmından <strong>Ürüne özel kategori seçilsin mi ?</strong> özelliğini aktif etmelisiniz.';


        }
        $data['controller'] = 'category';
        $data['primary_id'] = $this->request->get['category_id'];
        $data['attributes'] = $attributes;


        // $data['manufacturer']= $this->entegrasyon->getManufacturerNameByProductId2($product_id);

        if (!$attributes) {

            $this->load->controller('entegrasyon/genel/save_setting', array('code' => $code, 'primary_id' => $category_id, 'name' => 'selected_attributes', 'value' => array(), 'controller' => 'category'));

        }

        $data['selected_attributes'] = isset($category_setting['selected_attributes']) ? $category_setting['selected_attributes'] : array();


        //  print_r($data['selected_attributes']);return;
        $this->response->setOutput($this->load->view('entegrasyon/attributes/' . $code, $data));

    }

    public function attributes()
    {

        $code = $this->request->get['code'];
        $product_id = $this->request->get['product_id'];

        if (isset($this->request->get['category_id'])) {
            $category_id = $this->request->get['category_id'];
        } else {
            $category_id = 0;
        }
        $product_setting = $this->entegrasyon->getSettingData($code, 'product', $product_id);
        $data['s_attr'] = array();
        $data['s_attr_name'] = array();

        $data['is_varianter_product'] = $this->entegrasyon->isVarianterProduct($product_id);

        $getOptionsNames = array();
        if (isset($product_setting['selected_attributes'])) {

            foreach ($product_setting['selected_attributes'] as $key => $selected_attribute) {
                $selected_attribute['value'] = str_replace("d-",'',$selected_attribute['value']);
                if($code=='hb'){
                    $data['s_attr'][$key] = str_replace("''",'x_x',$selected_attribute['name']).$selected_attribute['value'];
                }  else {
                    $data['s_attr'][$key] = str_replace("''",'x_x',$selected_attribute['value']);

                }
                $data['s_attr_name'][$key] = $selected_attribute['name'];
            }

        }


        $category_name = '';
        $category_source = '';
        $attributes = array();

        if ($category_id) {

            $this->load->model('entegrasyon/category');
            $category_setting = $this->model_entegrasyon_category->getMarketCategory($category_id, $code);


        } else {
            $category_setting = $this->entegrasyon->getMarketPlaceCategory($product_id, $code);
        }

        $data['marketplace_data'] = $this->entegrasyon->getMarketPlaceProductForMarket($product_id, $code);


        if (isset($product_setting[$code . '_category_id'])) {


            $category_info = explode('|', $product_setting[$code . '_category_id']);
            $category_id = $category_info[0];
            $category_name = $category_info[1];
            $category_source = 'Ürüne Özel Kategori';


        } else if (isset($category_setting[$code . '_category_id'])) {

            if ($category_setting[$code . '_category_id']) {
                $category_info = explode('|', $category_setting[$code . '_category_id']);

                $category_id = $category_info[0];
                $category_name = $category_info[1];
                $category_source = 'Kategori Eşlemesi';

            }
        }

        if ($category_id) {

            $data['category_id'] = $category_id;
            $data['product_id'] = $product_id;
            $data['category_name'] = $category_name;
            $data['category_source'] = $category_source;
            $data['token_link'] = $this->token_data['token_link'];

            $this->load->model('entegrasyon/category');

            $is_product_varianter = $this->entegrasyon->isVarianterProduct($product_id);

            if ($code == 'ty') {
                $optionNames = $this->entegrasyon->getOptionNames($product_id);
                $isexists = $this->config->get('ty_setting_color');
                if ($isexists && $this->entegrasyon->findColor($optionNames)) {
                    $data['renk'] = true;
                }
            }
            $data['send_variant'] = $this->config->get($code . '_setting_variant');


            if ($this->config->get($code . '_setting_variant') && $is_product_varianter && ($code == 'ty' || $code == 'gg' || $code == 'hb' || $code == 'cs')) {
                $attributes = $this->model_entegrasyon_category->getAttributes($category_id, $code, true);

            } else {
                $attributes = $this->model_entegrasyon_category->getAttributes($category_id, $code, false);

            }


            /*
                 $getOptionsNames = $this->entegrasyon->getOptionNames($product_id); */

            if (isset($attributes['result']['required_attributes'])) unset($attributes['result']['required_attributes']);
            if (isset($attributes['result']['variants'])) unset($attributes['result']['variants']);


        } else {

            $this->load->model('entegrasyon/general');
            $marketPlace = $this->model_entegrasyon_general->getMarketPlace($code);
            $data['message'] = 'Lütfen Önce Ürünün bulunduğu kategoriyi eşleştirin yada ürüne özel bir <strong>' . $marketPlace['name'] . '</strong> kategorisi seçin, her ikisinide seçmeniz durumunda ürüne özel kategori dikkate alınacaktır. Ürüne özel kategori belirleme özelliğini aktif etmek için genel ayarlar kısmından <strong>Ürüne özel kategori seçilsin mi ?</strong> özelliğini aktif etmelisiniz.';


        }


        $data['attributes'] = $attributes;

        $data['primary_id'] = $product_id;

        $data['controller'] = 'product';

        $data['manufacturer'] = $this->entegrasyon->getManufacturerNameByProductId2($product_id);

        if (!$attributes) {

            $this->load->controller('entegrasyon/genel/save_setting', array('code' => $code, 'primary_id' => $product_id, 'name' => 'selected_attributes', 'value' => array(), 'controller' => 'product'));

        }
        // print_r($attributes);return;

        $data['selected_attributes'] = isset($product_setting['selected_attributes']) ? $product_setting['selected_attributes'] : array();


        $this->response->setOutput($this->load->view('entegrasyon/attributes/' . $code, $data));

    }


    public function save_attribute_value()
    {

        $code = $this->request->get['code'];
        $product_id = $this->request->post['pk'];
        $name = $this->request->post['name'];
        $value = $this->request->post['value'];
        $category_id = $this->request->get['category_id'];
        $product_setting = $this->entegrasyon->getSettingData($code, 'product', $product_id);
        $orginal_value = $value;


        if ($code == 'ty' and $value) {
            $attribute_value_id = 0;
            $name2 = $name;

            $this->load->model('entegrasyon/category');
            $attributes_groups = $this->model_entegrasyon_category->getAttributes($category_id, $code)['result'];

            foreach ($attributes_groups as $attributes_group) {


                if (isset($attributes_group['id'])) {
                    if ($attributes_group['id'] == $name) {

                        $name2 = $attributes_group['name'];
                        foreach ($attributes_group['values'] as $value2) {


                            if ($value2['name'] == $value) {


                                $attribute_value_id = $value2['id'];


                            }

                        }

                    }
                }
            }


            $name = $name2;
            $value = $attribute_value_id;


        }


        if (isset($product_setting['selected_attributes'])) {

            foreach ($product_setting['selected_attributes'] as $key => $selected_attribute) {
                if ($selected_attribute['name'] == $name) {
                    // $selected_attribute['name']=$value;
                    unset($product_setting['selected_attributes'][$key]);
                }

            }

        }


        if ($code == 'ty') {
            $product_setting['selected_attributes'][] = array('name' => $name, 'value' => $value, 'orginal_value' => $orginal_value);

        } else {

            $product_setting['selected_attributes'][] = array('name' => $name, 'value' => $value);

        }


        $this->load->controller('entegrasyon/genel/save_setting', array('code' => $code, 'primary_id' => $product_id, 'name' => 'selected_attributes', 'value' => $product_setting['selected_attributes'], 'controller' => 'product'));


        if ($code == 'ty') {
            $result = array('status' => true, 'message' => 'Başarılı', 'value' => $value);

        } else {
            $result = array('status' => true, 'message' => 'Başarılı');

        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($result));
    }

    public function save_option_value()
    {

        $oc_option_value_id = $this->request->post['pk'];
        $market_option_value_name = $this->request->post['value'];
        $oc_option_id = $this->request->get['matched_option_id'];
        $market_option_id = $this->request->get['market_option_id'];
        $category_id = $this->request->get['category_id'];
        $code = $this->request->get['code'];

        if (!$market_option_value_name) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "es_option_value where matched_option_id='" . $oc_option_id . "' and oc_option_value_id='" . $oc_option_value_id . "' ");
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode(array('status' => true, 'message' => 'seçenek eşleştirmesi silindi!')));
            return;
        }


        $market_option_value_info = $this->getMarketOptionInfo($category_id, $market_option_id, $code, $market_option_value_name);
        if ($market_option_value_info) {

            $data = array(
                'oc_option_value_id' => $oc_option_value_id,
                'market_option_value_id' => $market_option_value_info['value_id'],
                'market_option_value_order_number' => $market_option_value_info['order_number'],
                'market_option_value_name' => $market_option_value_name,
                'matched_option_id' => $oc_option_id
            );
            $result = $this->match_option_value($data);

        } else {
            $result = array('status' => false, 'message' => 'Sadece listedeki seçeneklerden birini seçiniz');

        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($result));
    }

    private function getMarketOptionInfo($category_id, $market_option_id, $code, $name)
    {
        $this->load->model('entegrasyon/category');

        $attributes_groups = $this->model_entegrasyon_category->getAttributes($category_id, $code)['result']['variants'];

        foreach ($attributes_groups as $attribute_group) {
            if ($attribute_group['id'] == $market_option_id) {
                foreach ($attribute_group['values'] as $value) {

                    if ($value['name'] == $name) {

                        return $value;
                    }

                }

            }
        }

        return false;
    }

    private function preg_grep_keys_values($pattern, $input, $flags = 0)
    {
        return preg_grep($pattern, $input, $flags);
    }


    public function attribute_search()
    {
        $json = array();

        if (isset($this->request->get['filter_name'])) {
            $filter_name = $this->request->get['filter_name'];
            $category_id = $this->request->get['filter_category_id'];
            $code = $this->request->get['filter_code'];
            $filter_option_id = $this->request->get['filter_attribute_id'];
            $this->load->model('entegrasyon/category');
            $array = array();
            $attributes_groups = $this->model_entegrasyon_category->getAttributes($category_id, $code)['result'];
            $value_id = 'id';
            if ($code == 'n11') {

                $attribute_index = "attribute_id";
                $value_name = 'name';
            } else if ($code == 'ty') {
                $attribute_index = "id";
                $value_name = 'name';

            } else {

                $attribute_index = "id";
                $value_name = 'value';

            }


            $attributes = array();


            foreach ($attributes_groups as $attribute_group) {
                if (isset($attribute_group[$attribute_index])) {
                    if ($attribute_group[$attribute_index] == $filter_option_id) {
                        $attributes = $attribute_group['values'];


                        if ($code == 'n11') {

                            $value_id = $attribute_group['name'];

                        }
                    }
                }
            }


            foreach ($attributes as $attribute) {
                $array[$attribute['id']] = $attribute[$value_name];
            }
            $results = $this->preg_grep_keys_values('~' . $filter_name . '~i', $array);


            foreach ($results as $key => $result) {


                $json[] = array(
                    'value_id' => $key,
                    'name' => strip_tags(html_entity_decode($result, ENT_QUOTES, 'UTF-8'))
                );


            }
        }


        $sort_order = array();

        foreach ($json as $key => $value) {
            $sort_order[$key] = $value['name'];
        }

        array_multisort($sort_order, SORT_ASC, $json);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));

    }


    public function option_search()
    {
        $json = array();

        if (isset($this->request->get['filter_name'])) {
            $filter_name = $this->request->get['filter_name'];
            $category_id = $this->request->get['filter_category_id'];
            $code = $this->request->get['filter_code'];
            $filter_option_id = $this->request->get['filter_option_id'];
            $this->load->model('entegrasyon/category');
            $array = array();
            $attributes_groups = $this->model_entegrasyon_category->getAttributes($category_id, $code)['result']['variants'];

            $attributes = array();

            foreach ($attributes_groups as $attribute_group) {
                if ($attribute_group['id'] == $filter_option_id) {
                    $attributes = $attribute_group['values'];

                }
            }


            foreach ($attributes as $attribute) {
                $array[$attribute['value_id']] = $attribute['name'];
            }
            $results = $this->preg_grep_keys_values('~' . $filter_name . '~i', $array);


            foreach ($results as $key => $result) {
                $json[] = array(
                    'option_vaule_id' => $key,
                    'name' => strip_tags(html_entity_decode($result, ENT_QUOTES, 'UTF-8'))
                );
            }
        }

        $sort_order = array();

        foreach ($json as $key => $value) {
            $sort_order[$key] = $value['name'];
        }

        array_multisort($sort_order, SORT_ASC, $json);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));

    }

    public function unmatch_option()
    {
        $data['token_link'] = $this->token_data['token_link'];
        $code = $this->request->get['code'];
        $category_id = $this->request->get['category_id'];
        $this->db->query("DELETE FROM " . DB_PREFIX . "es_option WHERE category_id='" . $category_id . "' and code='" . $code . "'");
        echo json_encode(array('status' => true));
    }


    public function match_option()
    {
        $data['token_link'] = $this->token_data['token_link'];
        $code = $this->request->get['code'];
        $category_id = $this->request->get['category_id'];
        $oc_option_id = $this->request->get['oc_option_id'];
        $order_number = $this->request->get['order_number'];
        $marketplace_option_id = $this->request->get['marketplace_option_id'];
        $marketplace_option_name = $this->request->get['marketplace_option_name'];

        $query = $this->db->query("select * from " . DB_PREFIX . "es_option where code='" . $code . "'  and market_option_id='" . $marketplace_option_id . "' and category_id='" . $category_id . "' ");

        if ($query->num_rows == 0) {

            $this->db->query("INSERT INTO " . DB_PREFIX . "es_option SET category_id='" . $category_id . "', code='" . $code . "', order_number='" . $order_number . "', oc_option_id='" . $oc_option_id . "', market_option_id='" . $marketplace_option_id . "' ,market_option_name='" . $marketplace_option_name . "'");
            $matched_option_id = $this->db->getLastId();

        } else {

            $option_id = $query->row['option_id'];
            //if oc option_id chanced, we delete mathced option values related with oc_option_id
            if ($query->row['oc_option_id'] != $oc_option_id) {
                $this->db->query("DELETE FROM " . DB_PREFIX . "es_option_value where matched_option_id='" . $option_id . "' ");
            }
            $this->db->query("UPDATE " . DB_PREFIX . "es_option SET category_id='" . $category_id . "', code='" . $code . "', order_number='" . $order_number . "', oc_option_id='" . $oc_option_id . "', market_option_id='" . $marketplace_option_id . "' ,market_option_name='" . $marketplace_option_name . "' where option_id='" . $option_id . "'");
            $matched_option_id = $query->row['option_id'];

        }

        echo json_encode(array('status' => true, 'matched_option_id' => $matched_option_id));
    }


    public function add()
    {
        $this->load->language('catalog/category');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('catalog/category');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_catalog_category->addCategory($this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('entegrasyon/category', $this->token_data['token_link'] . $url, true));
        }

        $this->getForm();
    }

    public function edit()
    {
        $this->load->language('entegrasyon/category');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('entegrasyon/category');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_catalog_category->editCategory($this->request->get['category_id'], $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('entegrasyon/category', $this->token_data['token_link'] . $url, true));
        }

        $this->getForm();
    }

    public function delete()
    {
        $this->load->language('catalog/category');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('entegrasyon/category');


        if (isset($this->request->post['selected']) && $this->validateDelete()) {

            foreach ($this->request->post['selected'] as $category_id) {
                $this->model_entegrasyon_category->deleteCategory($category_id);
            }


            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('entegrasyon/category', $this->token_data['token_link'] . $url, true));
        }

        $this->getList();
    }

    public function repair()
    {
        $this->load->language('catalog/category');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('catalog/category');

        if ($this->validateRepair()) {
            $this->model_catalog_category->repairCategories();

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('entegrasyon/category', $this->token_data['token_link'] . $url, true));
        }

        $this->getList();
    }

    protected function getForm()
    {
        $data['text_form'] = !isset($this->request->get['category_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['name'])) {
            $data['error_name'] = $this->error['name'];
        } else {
            $data['error_name'] = array();
        }

        if (isset($this->error['meta_title'])) {
            $data['error_meta_title'] = $this->error['meta_title'];
        } else {
            $data['error_meta_title'] = array();
        }

        if (isset($this->error['keyword'])) {
            $data['error_keyword'] = $this->error['keyword'];
        } else {
            $data['error_keyword'] = '';
        }

        if (isset($this->error['parent'])) {
            $data['error_parent'] = $this->error['parent'];
        } else {
            $data['error_parent'] = '';
        }

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }


        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $this->token_data['token_link'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('entegrasyon/category', $this->token_data['token_link'] . $url, true)
        );

        if (!isset($this->request->get['category_id'])) {
            $data['action'] = $this->url->link('entegrasyon/category/add', $this->token_data['token_link'] . $url, true);
        } else {
            $data['action'] = $this->url->link('entegrasyon/category/edit', $this->token_data['token_link'] . '&category_id=' . $this->request->get['category_id'] . $url, true);
        }

        $data['cancel'] = $this->url->link('entegrasyon/category', $this->token_data['token_link'] . $url, true);

        if (isset($this->request->get['category_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $category_info = $this->model_catalog_category->getCategory($this->request->get['category_id']);
        }

        $data['token_link'] = $this->token_data['token_link'];

        $this->load->model('localisation/language');


        $data['languages'] = $this->model_localisation_language->getLanguages();

        if (isset($this->request->post['category_description'])) {
            $data['category_description'] = $this->request->post['category_description'];
        } elseif (isset($this->request->get['category_id'])) {
            $data['category_description'] = $this->model_catalog_category->getCategoryDescriptions($this->request->get['category_id']);
        } else {
            $data['category_description'] = array();
        }

        if (isset($this->request->post['path'])) {
            $data['path'] = $this->request->post['path'];
        } elseif (!empty($category_info)) {
            $data['path'] = $category_info['path'];
        } else {
            $data['path'] = '';
        }

        if (isset($this->request->post['parent_id'])) {
            $data['parent_id'] = $this->request->post['parent_id'];
        } elseif (!empty($category_info)) {
            $data['parent_id'] = $category_info['parent_id'];
        } else {
            $data['parent_id'] = 0;
        }

        $this->load->model('catalog/filter');

        if (isset($this->request->post['category_filter'])) {
            $filters = $this->request->post['category_filter'];
        } elseif (isset($this->request->get['category_id'])) {
            $filters = $this->model_catalog_category->getCategoryFilters($this->request->get['category_id']);
        } else {
            $filters = array();
        }


        $data['category_filters'] = array();

        foreach ($filters as $filter_id) {
            $filter_info = $this->model_catalog_filter->getFilter($filter_id);

            if ($filter_info) {
                $data['category_filters'][] = array(
                    'filter_id' => $filter_info['filter_id'],
                    'name' => $filter_info['group'] . ' &gt; ' . $filter_info['name']
                );
            }
        }

        $this->load->model('setting/store');

        $data['stores'] = array();

        $data['stores'][] = array(
            'store_id' => 0,
            'name' => $this->language->get('text_default')
        );

        $stores = $this->model_setting_store->getStores();

        foreach ($stores as $store) {
            $data['stores'][] = array(
                'store_id' => $store['store_id'],
                'name' => $store['name']
            );
        }

        if (isset($this->request->post['category_store'])) {
            $data['category_store'] = $this->request->post['category_store'];
        } elseif (isset($this->request->get['category_id'])) {
            $data['category_store'] = $this->model_catalog_category->getCategoryStores($this->request->get['category_id']);
        } else {
            $data['category_store'] = array(0);
        }

        if (isset($this->request->post['image'])) {
            $data['image'] = $this->request->post['image'];
        } elseif (!empty($category_info)) {
            $data['image'] = $category_info['image'];
        } else {
            $data['image'] = '';
        }

        $this->load->model('tool/image');

        if (isset($this->request->post['image']) && is_file(DIR_IMAGE . $this->request->post['image'])) {
            $data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
        } elseif (!empty($category_info) && is_file(DIR_IMAGE . $category_info['image'])) {
            $data['thumb'] = $this->model_tool_image->resize($category_info['image'], 100, 100);
        } else {
            $data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
        }

        $data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

        if (isset($this->request->post['top'])) {
            $data['top'] = $this->request->post['top'];
        } elseif (!empty($category_info)) {
            $data['top'] = $category_info['top'];
        } else {
            $data['top'] = 0;
        }

        if (isset($this->request->post['column'])) {
            $data['column'] = $this->request->post['column'];
        } elseif (!empty($category_info)) {
            $data['column'] = $category_info['column'];
        } else {
            $data['column'] = 1;
        }

        if (isset($this->request->post['sort_order'])) {
            $data['sort_order'] = $this->request->post['sort_order'];
        } elseif (!empty($category_info)) {
            $data['sort_order'] = $category_info['sort_order'];
        } else {
            $data['sort_order'] = 0;
        }

        if (isset($this->request->post['status'])) {
            $data['status'] = $this->request->post['status'];
        } elseif (!empty($category_info)) {
            $data['status'] = $category_info['status'];
        } else {
            $data['status'] = true;
        }

        if (isset($this->request->post['category_seo_url'])) {
            $data['category_seo_url'] = $this->request->post['category_seo_url'];
        } elseif (isset($this->request->get['category_id'])) {
            $data['category_seo_url'] = $this->model_catalog_category->getCategorySeoUrls($this->request->get['category_id']);
        } else {
            $data['category_seo_url'] = array();
        }

        if (isset($this->request->post['category_layout'])) {
            $data['category_layout'] = $this->request->post['category_layout'];
        } elseif (isset($this->request->get['category_id'])) {
            $data['category_layout'] = $this->model_catalog_category->getCategoryLayouts($this->request->get['category_id']);
        } else {
            $data['category_layout'] = array();
        }


        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('entegrasyon/category/category_form', $data));
    }

    protected function validateForm()
    {
        if (!$this->user->hasPermission('modify', 'entegrasyon/category')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        foreach ($this->request->post['category_description'] as $language_id => $value) {
            if ((utf8_strlen($value['name']) < 1) || (utf8_strlen($value['name']) > 255)) {
                $this->error['name'][$language_id] = $this->language->get('error_name');
            }

        }

        if (isset($this->request->get['category_id']) && $this->request->post['parent_id']) {
            $results = $this->model_catalog_category->getCategoryPath($this->request->post['parent_id']);

            foreach ($results as $result) {
                if ($result['path_id'] == $this->request->get['category_id']) {
                    $this->error['parent'] = $this->language->get('error_parent');

                    break;
                }
            }
        }


        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return !$this->error;
    }

    protected function validateDelete()
    {
        if (!$this->user->hasPermission('modify', 'entegrasyon/category')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    protected function validateRepair()
    {
        if (!$this->user->hasPermission('modify', 'entegrasyon/category')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

}
