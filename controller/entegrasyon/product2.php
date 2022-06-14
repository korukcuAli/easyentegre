<?php

class ControllerEntegrasyonProduct2 extends Controller
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

        $this->load->language('entegrasyon/product');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('entegrasyon/product');
        $this->getList();
    }


    public function delete() {
        $this->load->language('entegrasyon/product');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('entegrasyon/product');

        if (isset($this->request->post['selected'])) {
            foreach ($this->request->post['selected'] as $product_id) {
                $this->model_entegrasyon_product->deleteProduct($product_id);
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['filter_model'])) {
                $url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($this->request->get['filter_name'])) {
                $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
            }


            if (isset($this->request->get['filter_manufacturer'])) {
                $url .= '&filter_manufacturer=' . $this->request->get['filter_manufacturer'];
            }

            if (isset($this->request->get['filter_category'])) {
                $url .= '&filter_category=' . $this->request->get['filter_category'];
            }

            if (isset($this->request->get['filter_status'])) {
                $url .= '&filter_status=' . $this->request->get['filter_status'];
            }


            if (isset($this->request->get['filter_marketplace'])) {
                $url .= '&filter_marketplace=' . $this->request->get['filter_marketplace'];
            }

            if (isset($this->request->get['filter_stock_prefix'])) {
                $url .= '&filter_stock_prefix=' . html_entity_decode($this->request->get['filter_stock_prefix']);
            }

            if (isset($this->request->get['filter_stock'])) {
                $url .= '&filter_stock=' . $this->request->get['filter_stock'];
            }

            if (isset($this->request->get['filter_marketplace_do'])) {
                $url .= '&filter_marketplace_do=' . $this->request->get['filter_marketplace_do'];
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

            $this->response->redirect($this->url->link('entegrasyon/product',$this->token_data['token_link']  . $url, true));
        }

        $this->getList();
    }




    protected function getList()
    {
        $data = $this->language->all();
        // $this->document->addStyle('https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css');
        $this->document->addScript('https://cdnjs.cloudflare.com/ajax/libs/jquery-color/2.1.2/jquery.color.min.js');
        if (isset($this->request->get['filter_model'])) {
            $filter_model = $this->request->get['filter_model'];
        } else {
            $filter_model = '';
        }

        if (isset($this->request->get['filter_name'])) {
            $filter_name = $this->request->get['filter_name'];
        } else {
            $filter_name = '';
        }

        if (isset($this->request->get['filter_status'])) {
            $filter_status = $this->request->get['filter_status'];
        } else {
            $filter_status = '*';
        }

        if (isset($this->request->get['filter_marketplace'])) {
            $filter_marketplace = $this->request->get['filter_marketplace'];
        } else {
            $filter_marketplace = '';
        }

        if (isset($this->request->get['filter_marketplace_do'])) {
            $filter_marketplace_do = $this->request->get['filter_marketplace_do'];
        } else {
            $filter_marketplace_do = '';
        }

        if (isset($this->request->get['filter_stock_prefix'])) {
            $filter_stock_prefix = html_entity_decode($this->request->get['filter_stock_prefix']);
        } else {
            $filter_stock_prefix = '';
        }

        if (isset($this->request->get['filter_stock'])) {
            $filter_stock = $this->request->get['filter_stock'];
        } else {
            $filter_stock = '';
        }

        if (isset($this->request->get['filter_category'])) {
            $filter_category = $this->request->get['filter_category'];
        } else {
            $filter_category = '';
        }

        if (isset($this->request->get['filter_manufacturer'])) {
            $filter_manufacturer = $this->request->get['filter_manufacturer'];
        } else {
            $filter_manufacturer = '';
        }


        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'pd.name';
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




        $this->document->addStyle('view/stylesheet/entegrasyon/bootstrap4.css');


        $data['products'] = array();

        $filter_data = array(
            'filter_category' => $filter_category,
            'filter_manufacturer' => $filter_manufacturer,
            'filter_marketplace' => $filter_marketplace,
            'filter_marketplace_do' => $filter_marketplace_do,
            'filter_name' => $filter_name,
            'filter_model' => $filter_model,
            'filter_status' => $filter_status,
            'filter_stock_prefix' => $filter_stock_prefix,
            'filter_stock' => $filter_stock,
            'sort' => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        );


        $this->load->model('tool/image');

        $product_total = count($this->model_entegrasyon_product->getProducts($filter_data,"for_bulk"));//$this->model_entegrasyon_product->getTotalProducts($filter_data);
        $this->load->model('entegrasyon/category');


        $results = $this->model_entegrasyon_product->getProducts($filter_data);


        if (($filter_model || !$filter_status == "*" || $filter_name || $filter_marketplace || $filter_stock_prefix || $filter_stock || $filter_category || $filter_manufacturer) && (!$filter_model=="*" ||  !$filter_name=="*" || !$filter_marketplace=="*" || !$filter_stock_prefix=="*" || !$filter_stock=="*" || !$filter_category=="*" || !$filter_manufacturer=="*")){
            $filter_products_id_for_bulk_action = $this->model_entegrasyon_product->getProducts($filter_data, 'for_bulk');

            $data['products_ids'] = array();
            foreach ($filter_products_id_for_bulk_action as $item) {

                $data['products_ids'][] = array(
                    'product_id' => $item['product_id']
                );

            }

        }




        foreach ($results as $result) {


            if (is_file(DIR_IMAGE . $result['image'])) {
                $image = $this->model_tool_image->resize($result['image'], 40, 40);
            } else {
                $image = $this->model_tool_image->resize('no_image.png', 40, 40);
            }

            $special = false;
            $special_price = false;

            $product_specials = $this->model_entegrasyon_product->getProductSpecials($result['product_id']);

            foreach ($product_specials as $product_special) {
                if (($product_special['date_start'] == '0000-00-00' || strtotime($product_special['date_start']) < time()) && ($product_special['date_end'] == '0000-00-00' || strtotime($product_special['date_end']) > time())) {


                    $special = $this->currency->format($product_special['price'], $this->config->get('config_currency'));
                    $special_price = $product_special['price'];

                    break;
                }
            }

            //$product_setting=$this->entegrasyon->getSettingData($code,'product',$product_id);

            if (method_exists($this->currency, 'getCodeOrDefault')) {

                $price = $this->currency->format(
                    $this->currency->convert($result['price'], $this->currency->getCodeOrDefault($result['currency_id']), $this->config->get('config_currency')),
                    $this->currency->getCodeOrDefault($result['currency_id'])
                );

                $special = empty($special) ? false : $this->currency->format(
                    $this->currency->convert($special_price, $this->currency->getCodeOrDefault($result['currency_id']), $this->config->get('config_currency')),
                    $this->currency->getCodeOrDefault($result['currency_id'])
                );

            } else {

                $price = $this->currency->format($result['price'], $this->config->get('config_currency'));
            }


            $categories=$this->entegrasyon->getProductCategoryPath($result['product_id']);

            $category_path=null;
            if($categories){
                $category_path=$this->model_entegrasyon_category->getCategory(end($categories));
            }



            $data['products'][] = array(
                'product_id' => $result['product_id'],
                'image' => $image,
                'n11' => unserialize($result['n11']),
                'gg' => unserialize($result['gg']),
                'hb' => unserialize($result['hb']),
                'ty' => unserialize($result['ty']),
                'eptt' => unserialize($result['eptt']),
                'amz' => unserialize($result['amz']),
                'cs' => unserialize($result['cs']),
                'total_options' => $result['total_options'],
                'name' => $result['name'],
                'manufacturer' => $result['manufacturer'],
                'category' =>$category_path ? $category_path['path'].' > '.$category_path['name']:'',
                'manufacturer_id' => $result['manufacturer_id'],
                'model' => $result['model'],
                'price' => $price,
                'special' => $special,
                'quantity' => $result['quantity'],
                'status' => $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')
            );
        }




        $this->load->model('entegrasyon/general');
        $data['edit_button_status'] = false;

        $marketplaces = $this->model_entegrasyon_general->getMarketPlaces();


        foreach ($marketplaces as $marketplace) {

            if ($marketplace['status']) {
                $filter_data[$marketplace['code']] = true;
                $data['edit_button_status'] = true;
            }

        }
        $data['marketplaces'] = $marketplaces;

        $data['easy_visibility'] = $this->config->get('easy_visibility') ? '' : 'hidden';

        $data['token'] = $this->token_data['token_link'];

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

        if (isset($this->request->get['filter_model'])) {
            $url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_name'])) {
            $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
        }


        if (isset($this->request->get['filter_manufacturer'])) {
            $url .= '&filter_manufacturer=' . $this->request->get['filter_manufacturer'];
        }

        if (isset($this->request->get['filter_category'])) {
            $url .= '&filter_category=' . $this->request->get['filter_category'];
        }

        if (isset($this->request->get['filter_status'])) {
            $url .= '&filter_status=' . $this->request->get['filter_status'];
        }


        if (isset($this->request->get['filter_marketplace'])) {
            $url .= '&filter_marketplace=' . $this->request->get['filter_marketplace'];
        }

        if (isset($this->request->get['filter_stock_prefix'])) {
            $url .= '&filter_stock_prefix=' . html_entity_decode($this->request->get['filter_stock_prefix']);
        }

        if (isset($this->request->get['filter_stock'])) {
            $url .= '&filter_stock=' . $this->request->get['filter_stock'];
        }

        if (isset($this->request->get['filter_marketplace_do'])) {
            $url .= '&filter_marketplace_do=' . $this->request->get['filter_marketplace_do'];
        }

        $data['delete'] = $this->url->link('entegrasyon/product/delete', $this->token_data['token_link'] . $url, true);

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $this->token_data['token_link'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('entegrasyon/product', $this->token_data['token_link'] . $url, true)
        );

        if ($filter_manufacturer) {
            $this->load->model('catalog/manufacturer');
            $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($filter_manufacturer);
            $data['filter_manufacturer_name'] = $manufacturer_info['name'];

        } else {

            $data['filter_manufacturer_name'] = '';

        }

        if ($filter_category) {
            $this->load->model('catalog/category');
            $category_info = $this->model_catalog_category->getCategory($filter_category);
            $data['filter_category_name'] = $category_info['name'];
        } else {

            $data['filter_category_name'] = '';

        }


        $pagination = new Pagination();
        $pagination->total = $product_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('entegrasyon/product', $this->token_data['token_link'] . $url . '&page={page}', true);

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($product_total - $this->config->get('config_limit_admin'))) ? $product_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $product_total, ceil($product_total / $this->config->get('config_limit_admin')));
        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }

        $data['sort_name'] = $this->url->link('entegrasyon/product', $this->token_data['token_link'] . '&sort=pd.name' . $url, true);
        $data['sort_price'] = $this->url->link('entegrasyon/product', $this->token_data['token_link'] . '&sort=p.price' . $url, true);
        $data['sort_data_added'] = $this->url->link('entegrasyon/product', $this->token_data['token_link'] . '&sort=p.data_added' . $url, true);
        $data['sort_quantity'] = $this->url->link('entegrasyon/product', $this->token_data['token_link'] . '&sort=p.quantity' . $url, true);
        $data['product_error'] = $this->url->link('entegrasyon/product/error_list', $this->token_data['token_link'], true);


        $data['filter_marketplace'] = $filter_marketplace;
        $data['filter_marketplace_do'] = $filter_marketplace_do;
        $data['filter_model'] = $filter_model;
        $data['filter_name'] = $filter_name;
        $data['filter_manufacturer'] = $filter_manufacturer;
        $data['filter_category'] = $filter_category;
        $data['filter_stock_prefix'] = $filter_stock_prefix;
        $data['filter_stock'] = $filter_stock;
        $data['filter_status'] = $filter_status;

        $this->model_entegrasyon_general->loadPageRequired();
        $data['token_link'] = $this->token_data['token_link'];
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('entegrasyon/product_list2', $data));
    }


    public function unapproved_products()
    {
        $data = $this->language->all();


        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }


        if (isset($this->request->get['filter_model'])) {
            $filter_model = $this->request->get['filter_model'];
        } else {
            $filter_model = '';
        }

        if (isset($this->request->get['filter_name'])) {
            $filter_name = $this->request->get['filter_name'];
        } else {
            $filter_name = '';
        }


        if (isset($this->request->get['filter_marketplace'])) {
            $filter_marketplace = $this->request->get['filter_marketplace'];
        } else {
            $filter_marketplace = '';
        }

        // $this->document->addStyle('https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css');
        $this->document->addScript('https://cdnjs.cloudflare.com/ajax/libs/jquery-color/2.1.2/jquery.color.min.js');


        $this->document->addStyle('view/stylesheet/entegrasyon/bootstrap4.css');

        $this->document->setTitle('Onay bekleyen ürünler');

        $data['products'] = array();

        $code='ty';
        $filter_data = array(
            'code' => $code,
            'filter_name' => $filter_name,
            'filter_model' => $filter_model,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        );




        $this->load->model('entegrasyon/product');
        $product_total = $this->model_entegrasyon_product->getTotalUnApprovedProducts($filter_data);


        $results = $this->model_entegrasyon_product->getUnApprovedProducts($filter_data);




        $data['product_total'] = $product_total;
        $i = 0;
        foreach ($results as $result) {

            $i++;
            $product_info = unserialize($result[$code]);
            $data['products'][] = array(
                'product_id' => $result['product_id'],
                'model' => $result['model'],
                'error'=>$product_info['message'],
                'marketplace' => strlen($code) > 1 ? $this->entegrasyon->marketPlaces[$code] : '',
                'code'=>$code,
                'name' => $result['name'],
                'product_data' => unserialize($result[$code]),
                'date_modified' => $result['date_modified']
            );
        }




        $this->load->model('entegrasyon/general');

        $marketplaces = $this->model_entegrasyon_general->getMarketPlaces();
        foreach ($marketplaces as $marketplace) {

            if ($marketplace['status']) {
                $filter_data[$marketplace['code']] = true;
                $data['edit_button_status'] = true;
            }

        }
        $data['marketplaces'] = $marketplaces;

        $data['easy_visibility'] = $this->config->get('easy_visibility') ? '' : 'hidden';

        $data['token'] = $this->token_data['token_link'];

        $url = '';

        if (isset($this->request->get['filter_model'])) {
            $url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_name'])) {
            $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
        }


        if (isset($this->request->get['filter_marketplace'])) {
            $url .= '&filter_marketplace=' . $this->request->get['filter_marketplace'];
        }


        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $this->token_data['token_link'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => 'Hata Raporları',
            'href' => $this->url->link('entegrasyon/product/error_list', $this->token_data['token_link'], true)
        );


        $pagination = new Pagination();
        $pagination->total = $product_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('entegrasyon/product/error_list', $this->token_data['token_link'] . '&page={page}', true);

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($product_total - $this->config->get('config_limit_admin'))) ? $product_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $product_total, ceil($product_total / $this->config->get('config_limit_admin')));


        $data['filter_marketplace'] = $filter_marketplace;
        $data['filter_model'] = $filter_model;
        $data['filter_name'] = $filter_name;


        $this->model_entegrasyon_general->loadPageRequired();
        $data['token_link'] = $this->token_data['token_link'];

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');


        $this->response->setOutput($this->load->view('entegrasyon/product/unapproved_product_list', $data));
    }
    public function check_status()
    {


        $product_id = $this->request->post['product_id'];
        $code = $this->request->post['code'];

        $marketplace_data = $this->entegrasyon->getMarketPlaceProductForMarket($product_id, $code);
        $marketplace_data['barcode']='zetzeka04041941';
        $this->load->model('entegrasyon/general');
        $post_data['market'] = $this->model_entegrasyon_general->getMarketPlace($code);
        $debug=false;
        if($code=='ty'){
            $post_data['request_data']=array('itemcount'=>1,'page'=>1,'barcode'=>$marketplace_data['barcode'],'approved'=>true);

            $result=$this->entegrasyon->clientConnect($post_data,'get_product','ty',$debug);

            print_r($result);

        }else {

            $post_data['request_data'] = $marketplace_data['request_id'];

            $result = $this->entegrasyon->clientConnect($post_data, 'check_status', $code, $debug);


        }



        print_r($result);

        return;

        if ($result['status']) {

            if ($result['result']['product_status'] == 'Satışa Hazır') {
                $marketplace_data['sale_status'] = 1;
                unserialize($marketplace_data['status']);
                $marketplace_data['approval_status'] = 1;
                $this->entegrasyon->addMarketplaceProduct($product_id, $marketplace_data, 'hb');

            } else {

                $marketplace_data['status'] = $result['result']['product_status'];
                $this->entegrasyon->addMarketplaceProduct($product_id, $marketplace_data, 'hb');


            }

            $error = '';
            if ($result['result']['errors']) {
                $error = implode(',', $result['result']['errors']);
            }


            echo json_encode(array('status' => $result['status'], 'product_status' => $result['result']['product_status'], 'message' => 'Ürün Durumu:' . $result['result']['product_status'] . ' ' . $error));


        }


    }


    public function error_list()
    {
        $data = $this->language->all();


        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }


        if (isset($this->request->get['filter_model'])) {
            $filter_model = $this->request->get['filter_model'];
        } else {
            $filter_model = '';
        }

        if (isset($this->request->get['filter_name'])) {
            $filter_name = $this->request->get['filter_name'];
        } else {
            $filter_name = '';
        }


        if (isset($this->request->get['filter_marketplace'])) {
            $filter_marketplace = $this->request->get['filter_marketplace'];
        } else {
            $filter_marketplace = '';
        }

        // $this->document->addStyle('https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css');
        $this->document->addScript('https://cdnjs.cloudflare.com/ajax/libs/jquery-color/2.1.2/jquery.color.min.js');


        $this->document->addStyle('view/stylesheet/entegrasyon/bootstrap4.css');

        $this->document->setTitle('Ürün Hata Raporları');

        $data['products'] = array();


        $filter_data = array(
            'filter_marketplace' => $filter_marketplace,
            'filter_name' => $filter_name,
            'filter_model' => $filter_model,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        );

        $this->load->model('entegrasyon/product');
        $product_total = $this->model_entegrasyon_product->getTotalProductErrors($filter_data);
        $results = $this->model_entegrasyon_product->getProductErrors($filter_data);

        $data['product_total'] = $product_total;
        $i = 0;
        foreach ($results as $result) {

            $i++;

            $data['products'][] = array(
                'product_id' => $result['product_id'],
                'model' => $result['model'],
                'name' => $result['name'],
                'code' => $result['code'],
                'marketplace' => strlen($result['code']) > 1 ? $this->entegrasyon->marketPlaces[$result['code']] : '',
                'action' => $result['type'] == 1 ? 'Ürün Ekleme' : 'Güncelleme',
                'auto_action' => $result['type'] == 1 ? 'addproduct' : 'update',
                'error' => $result['error'],
                'btn_id' => $i,
                'date_modified' => $result['date_modified']

            );
        }


        $this->load->model('entegrasyon/general');

        $marketplaces = $this->model_entegrasyon_general->getMarketPlaces();
        foreach ($marketplaces as $marketplace) {

            if ($marketplace['status']) {
                $filter_data[$marketplace['code']] = true;
                $data['edit_button_status'] = true;
            }

        }
        $data['marketplaces'] = $marketplaces;

        $data['easy_visibility'] = $this->config->get('easy_visibility') ? '' : 'hidden';

        $data['token'] = $this->token_data['token_link'];

        $url = '';

        if (isset($this->request->get['filter_model'])) {
            $url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_name'])) {
            $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
        }


        if (isset($this->request->get['filter_marketplace'])) {
            $url .= '&filter_marketplace=' . $this->request->get['filter_marketplace'];
        }


        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $this->token_data['token_link'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => 'Hata Raporları',
            'href' => $this->url->link('entegrasyon/product/error_list', $this->token_data['token_link'], true)
        );


        $pagination = new Pagination();
        $pagination->total = $product_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('entegrasyon/product/error_list', $this->token_data['token_link'] . '&page={page}', true);

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($product_total - $this->config->get('config_limit_admin'))) ? $product_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $product_total, ceil($product_total / $this->config->get('config_limit_admin')));


        $data['filter_marketplace'] = $filter_marketplace;
        $data['filter_model'] = $filter_model;
        $data['filter_name'] = $filter_name;


        $this->model_entegrasyon_general->loadPageRequired();
        $data['token_link'] = $this->token_data['token_link'];

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');


        $this->response->setOutput($this->load->view('entegrasyon/product/product_error_list', $data));
    }


    protected function validateForm()
    {
        if (!$this->user->hasPermission('modify', 'catalog/product')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        foreach ($this->request->post['product_description'] as $language_id => $value) {
            if ((utf8_strlen($value['name']) < 1) || (utf8_strlen($value['name']) > 255)) {
                $this->error['name'][$language_id] = $this->language->get('error_name');
            }

            if ((utf8_strlen($value['meta_title']) < 1) || (utf8_strlen($value['meta_title']) > 255)) {
                $this->error['meta_title'][$language_id] = $this->language->get('error_meta_title');
            }
        }

        if ((utf8_strlen($this->request->post['model']) < 1) || (utf8_strlen($this->request->post['model']) > 64)) {
            $this->error['model'] = $this->language->get('error_model');
        }

        /*   if ($this->request->post['product_seo_url']) {
               $this->load->model('design/seo_url');

               foreach ($this->request->post['product_seo_url'] as $store_id => $language) {
                   foreach ($language as $language_id => $keyword) {
                       if (!empty($keyword)) {
                           if (count(array_keys($language, $keyword)) > 1) {
                               $this->error['keyword'][$store_id][$language_id] = $this->language->get('error_unique');
                           }

                           $seo_urls = $this->model_design_seo_url->getSeoUrlsByKeyword($keyword);

                           foreach ($seo_urls as $seo_url) {
                               if (($seo_url['store_id'] == $store_id) && (!isset($this->request->get['product_id']) || (($seo_url['query'] != 'product_id=' . $this->request->get['product_id'])))) {
                                   $this->error['keyword'][$store_id][$language_id] = $this->language->get('error_keyword');

                                   break;
                               }
                           }
                       }
                   }
               }
           } */

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return !$this->error;
    }


    public function edit_product()
    {

        $this->load->language('catalog/product');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('entegrasyon/product');
        $this->load->model('catalog/product');

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

            if ($this->validateForm()) {


                $this->model_entegrasyon_product->editProduct($this->request->get['product_id'], $this->request->post);

                echo json_encode(array('status' => true, 'error' => ''));

            } else {


                echo json_encode(array('status' => false, 'message' => $this->error['warning'], 'error' => $this->error));


            }

            return;
            // $this->response->redirect($this->url->link('catalog/product', $this->token_data['token_link'] . $url, true));
        }

        $this->getForm();
    }

    public function get_product_row()
    {
        $data = $this->language->all();

        $this->load->model('tool/image');
        $this->language->all();

        $product_id = $this->request->get['product_id'];
        $data['product_id'] = $product_id;
        $data['product'] = $this->entegrasyon->getProduct($product_id);


        if (is_file(DIR_IMAGE . $data['product']['image'])) {
            $data['product']['image'] = $this->model_tool_image->resize($data['product']['image'], 40, 40);
        } else {
            $data['product']['image'] = $this->model_tool_image->resize('no_image.png', 40, 40);
        }

        $data['product']['n11'] = unserialize($data['product']['n11']);
        $data['product']['gg'] = unserialize($data['product']['gg']);
        $data['product']['ty'] = unserialize($data['product']['ty']);
        $data['product']['eptt'] = unserialize($data['product']['eptt']);
        $data['product']['hb'] = unserialize($data['product']['hb']);
        $data['product']['cs'] = unserialize($data['product']['cs']);

        $this->load->model('entegrasyon/general');
        $data['edit_button_status'] = false;

        $marketplaces = $this->model_entegrasyon_general->getMarketPlaces();


        foreach ($marketplaces as $marketplace) {

            if ($marketplace['status']) {
                $filter_data[$marketplace['code']] = true;
                $data['edit_button_status'] = true;
            }

        }

        $data['marketplaces'] = $marketplaces;
        $this->response->setOutput($this->load->view('entegrasyon/product/product_row', $data));

    }


    protected function getForm()
    {

        $this->load->language('catalog/product');
        $data = $this->language->all();
        $data['text_form'] = !isset($this->request->get['product_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

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

        if (isset($this->error['model'])) {
            $data['error_model'] = $this->error['model'];
        } else {
            $data['error_model'] = '';
        }

        if (isset($this->error['keyword'])) {
            $data['error_keyword'] = $this->error['keyword'];
        } else {
            $data['error_keyword'] = '';
        }

        $url = '';

        if (isset($this->request->get['filter_name'])) {
            $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_model'])) {
            $url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_price'])) {
            $url .= '&filter_price=' . $this->request->get['filter_price'];
        }

        if (isset($this->request->get['filter_quantity'])) {
            $url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
        }

        if (isset($this->request->get['filter_status'])) {
            $url .= '&filter_status=' . $this->request->get['filter_status'];
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

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $this->token_data['token_link'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('catalog/product', $this->token_data['token_link'] . $url, true)
        );

        if (!isset($this->request->get['product_id'])) {
            $data['action'] = $this->url->link('catalog/product/add', $this->token_data['token_link'] . $url, true);
        } else {
            $data['action'] = $this->url->link('catalog/product/edit', $this->token_data['token_link'] . '&product_id=' . $this->request->get['product_id'] . $url, true);
        }

        $data['cancel'] = $this->url->link('catalog/product', $this->token_data['token_link'] . $url, true);

        if (isset($this->request->get['product_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $product_info = $this->model_catalog_product->getProduct($this->request->get['product_id']);
        }

        $data['token_link'] = $this->token_data['token_link'];
        $this->load->model('localisation/language');

        $data['languages'] = $this->model_localisation_language->getLanguages();

        if (isset($this->request->post['product_description'])) {
            $data['product_description'] = $this->request->post['product_description'];
        } elseif (isset($this->request->get['product_id'])) {
            $data['product_description'] = $this->model_catalog_product->getProductDescriptions($this->request->get['product_id']);
        } else {
            $data['product_description'] = array();
        }

        if (isset($this->request->post['model'])) {
            $data['model'] = $this->request->post['model'];
        } elseif (!empty($product_info)) {
            $data['model'] = $product_info['model'];
        } else {
            $data['model'] = '';
        }

        if (isset($this->request->post['sku'])) {
            $data['sku'] = $this->request->post['sku'];
        } elseif (!empty($product_info)) {
            $data['sku'] = $product_info['sku'];
        } else {
            $data['sku'] = '';
        }

        if (isset($this->request->post['upc'])) {
            $data['upc'] = $this->request->post['upc'];
        } elseif (!empty($product_info)) {
            $data['upc'] = $product_info['upc'];
        } else {
            $data['upc'] = '';
        }

        if (isset($this->request->post['ean'])) {
            $data['ean'] = $this->request->post['ean'];
        } elseif (!empty($product_info)) {
            $data['ean'] = $product_info['ean'];
        } else {
            $data['ean'] = '';
        }

        if (isset($this->request->post['jan'])) {
            $data['jan'] = $this->request->post['jan'];
        } elseif (!empty($product_info)) {
            $data['jan'] = $product_info['jan'];
        } else {
            $data['jan'] = '';
        }

        if (isset($this->request->post['isbn'])) {
            $data['isbn'] = $this->request->post['isbn'];
        } elseif (!empty($product_info)) {
            $data['isbn'] = $product_info['isbn'];
        } else {
            $data['isbn'] = '';
        }

        if (isset($this->request->post['mpn'])) {
            $data['mpn'] = $this->request->post['mpn'];
        } elseif (!empty($product_info)) {
            $data['mpn'] = $product_info['mpn'];
        } else {
            $data['mpn'] = '';
        }

        if (isset($this->request->post['location'])) {
            $data['location'] = $this->request->post['location'];
        } elseif (!empty($product_info)) {
            $data['location'] = $product_info['location'];
        } else {
            $data['location'] = '';
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


        if (isset($this->request->post['product_store'])) {
            $data['product_store'] = $this->request->post['product_store'];
        } elseif (isset($this->request->get['product_id'])) {
            $data['product_store'] = $this->model_catalog_product->getProductStores($this->request->get['product_id']);
        } else {
            $data['product_store'] = array(0);
        }

        if (isset($this->request->post['shipping'])) {
            $data['shipping'] = $this->request->post['shipping'];
        } elseif (!empty($product_info)) {
            $data['shipping'] = $product_info['shipping'];
        } else {
            $data['shipping'] = 1;
        }

        if (isset($this->request->post['price'])) {
            $data['price'] = $this->request->post['price'];
        } elseif (!empty($product_info)) {
            $data['price'] = $product_info['price'];
        } else {
            $data['price'] = '';
        }


        $this->load->model('localisation/tax_class');

        $data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

        if (isset($this->request->post['tax_class_id'])) {
            $data['tax_class_id'] = $this->request->post['tax_class_id'];
        } elseif (!empty($product_info)) {
            $data['tax_class_id'] = $product_info['tax_class_id'];
        } else {
            $data['tax_class_id'] = 0;
        }

        if (isset($this->request->post['date_available'])) {
            $data['date_available'] = $this->request->post['date_available'];
        } elseif (!empty($product_info)) {
            $data['date_available'] = ($product_info['date_available'] != '0000-00-00') ? $product_info['date_available'] : '';
        } else {
            $data['date_available'] = date('Y-m-d');
        }

        if (isset($this->request->post['quantity'])) {
            $data['quantity'] = $this->request->post['quantity'];
        } elseif (!empty($product_info)) {
            $data['quantity'] = $product_info['quantity'];
        } else {
            $data['quantity'] = 1;
        }

        if (isset($this->request->post['minimum'])) {
            $data['minimum'] = $this->request->post['minimum'];
        } elseif (!empty($product_info)) {
            $data['minimum'] = $product_info['minimum'];
        } else {
            $data['minimum'] = 1;
        }

        if (isset($this->request->post['subtract'])) {
            $data['subtract'] = $this->request->post['subtract'];
        } elseif (!empty($product_info)) {
            $data['subtract'] = $product_info['subtract'];
        } else {
            $data['subtract'] = 1;
        }

        if (isset($this->request->post['sort_order'])) {
            $data['sort_order'] = $this->request->post['sort_order'];
        } elseif (!empty($product_info)) {
            $data['sort_order'] = $product_info['sort_order'];
        } else {
            $data['sort_order'] = 1;
        }

        $this->load->model('localisation/stock_status');

        $data['stock_statuses'] = $this->model_localisation_stock_status->getStockStatuses();

        if (isset($this->request->post['stock_status_id'])) {
            $data['stock_status_id'] = $this->request->post['stock_status_id'];
        } elseif (!empty($product_info)) {
            $data['stock_status_id'] = $product_info['stock_status_id'];
        } else {
            $data['stock_status_id'] = 0;
        }

        if (isset($this->request->post['status'])) {
            $data['status'] = $this->request->post['status'];
        } elseif (!empty($product_info)) {
            $data['status'] = $product_info['status'];
        } else {
            $data['status'] = true;
        }

        if (isset($this->request->post['weight'])) {
            $data['weight'] = $this->request->post['weight'];
        } elseif (!empty($product_info)) {
            $data['weight'] = $product_info['weight'];
        } else {
            $data['weight'] = '';
        }

        $this->load->model('localisation/weight_class');

        $data['weight_classes'] = $this->model_localisation_weight_class->getWeightClasses();

        if (isset($this->request->post['weight_class_id'])) {
            $data['weight_class_id'] = $this->request->post['weight_class_id'];
        } elseif (!empty($product_info)) {
            $data['weight_class_id'] = $product_info['weight_class_id'];
        } else {
            $data['weight_class_id'] = $this->config->get('config_weight_class_id');
        }

        if (isset($this->request->post['length'])) {
            $data['length'] = $this->request->post['length'];
        } elseif (!empty($product_info)) {
            $data['length'] = $product_info['length'];
        } else {
            $data['length'] = '';
        }

        if (isset($this->request->post['width'])) {
            $data['width'] = $this->request->post['width'];
        } elseif (!empty($product_info)) {
            $data['width'] = $product_info['width'];
        } else {
            $data['width'] = '';
        }

        if (isset($this->request->post['height'])) {
            $data['height'] = $this->request->post['height'];
        } elseif (!empty($product_info)) {
            $data['height'] = $product_info['height'];
        } else {
            $data['height'] = '';
        }

        $this->load->model('localisation/length_class');

        $data['length_classes'] = $this->model_localisation_length_class->getLengthClasses();

        if (isset($this->request->post['length_class_id'])) {
            $data['length_class_id'] = $this->request->post['length_class_id'];
        } elseif (!empty($product_info)) {
            $data['length_class_id'] = $product_info['length_class_id'];
        } else {
            $data['length_class_id'] = $this->config->get('config_length_class_id');
        }

        $this->load->model('catalog/manufacturer');

        if (isset($this->request->post['manufacturer_id'])) {
            $data['manufacturer_id'] = $this->request->post['manufacturer_id'];
        } elseif (!empty($product_info)) {
            $data['manufacturer_id'] = $product_info['manufacturer_id'];
        } else {
            $data['manufacturer_id'] = 0;
        }

        if (isset($this->request->post['manufacturer'])) {
            $data['manufacturer'] = $this->request->post['manufacturer'];
        } elseif (!empty($product_info)) {
            $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($product_info['manufacturer_id']);

            if ($manufacturer_info) {
                $data['manufacturer'] = $manufacturer_info['name'];
            } else {
                $data['manufacturer'] = '';
            }
        } else {
            $data['manufacturer'] = '';
        }

        // Categories
        $this->load->model('catalog/category');

        if (isset($this->request->post['product_category'])) {
            $categories = $this->request->post['product_category'];
        } elseif (isset($this->request->get['product_id'])) {
            $categories = $this->model_catalog_product->getProductCategories($this->request->get['product_id']);
        } else {
            $categories = array();
        }

        $data['product_categories'] = array();

        foreach ($categories as $category_id) {
            $category_info = $this->model_catalog_category->getCategory($category_id);

            if ($category_info) {
                $data['product_categories'][] = array(
                    'category_id' => $category_info['category_id'],
                    'name' => ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name']
                );
            }
        }

        // Filters
        $this->load->model('catalog/filter');

        if (isset($this->request->post['product_filter'])) {
            $filters = $this->request->post['product_filter'];
        } elseif (isset($this->request->get['product_id'])) {
            $filters = $this->model_catalog_product->getProductFilters($this->request->get['product_id']);
        } else {
            $filters = array();
        }

        $data['product_filters'] = array();

        foreach ($filters as $filter_id) {
            $filter_info = $this->model_catalog_filter->getFilter($filter_id);

            if ($filter_info) {
                $data['product_filters'][] = array(
                    'filter_id' => $filter_info['filter_id'],
                    'name' => $filter_info['group'] . ' &gt; ' . $filter_info['name']
                );
            }
        }

        // Attributes
        $this->load->model('catalog/attribute');

        if (isset($this->request->post['product_attribute'])) {
            $product_attributes = $this->request->post['product_attribute'];
        } elseif (isset($this->request->get['product_id'])) {
            $product_attributes = $this->model_catalog_product->getProductAttributes($this->request->get['product_id']);
        } else {
            $product_attributes = array();
        }

        $data['product_attributes'] = array();

        foreach ($product_attributes as $product_attribute) {
            $attribute_info = $this->model_catalog_attribute->getAttribute($product_attribute['attribute_id']);

            if ($attribute_info) {
                $data['product_attributes'][] = array(
                    'attribute_id' => $product_attribute['attribute_id'],
                    'name' => $attribute_info['name'],
                    'product_attribute_description' => $product_attribute['product_attribute_description']
                );
            }
        }

        // Options
        $this->load->model('catalog/option');

        if (isset($this->request->post['product_option'])) {
            $product_options = $this->request->post['product_option'];
        } elseif (isset($this->request->get['product_id'])) {
            $product_options = $this->model_catalog_product->getProductOptions($this->request->get['product_id']);
        } else {
            $product_options = array();
        }

        $data['product_options'] = array();

        foreach ($product_options as $product_option) {
            $product_option_value_data = array();

            if (isset($product_option['product_option_value'])) {
                foreach ($product_option['product_option_value'] as $product_option_value) {
                    $product_option_value_data[] = array(
                        'product_option_value_id' => $product_option_value['product_option_value_id'],
                        'option_value_id' => $product_option_value['option_value_id'],
                        'quantity' => $product_option_value['quantity'],
                        'subtract' => $product_option_value['subtract'],
                        'price' => $product_option_value['price'],
                        'price_prefix' => $product_option_value['price_prefix'],
                        'points' => $product_option_value['points'],
                        'points_prefix' => $product_option_value['points_prefix'],
                        'weight' => $product_option_value['weight'],
                        'weight_prefix' => $product_option_value['weight_prefix']
                    );
                }
            }

            $data['product_options'][] = array(
                'product_option_id' => $product_option['product_option_id'],
                'product_option_value' => $product_option_value_data,
                'option_id' => $product_option['option_id'],
                'name' => $product_option['name'],
                'type' => $product_option['type'],
                'value' => isset($product_option['value']) ? $product_option['value'] : '',
                'required' => $product_option['required']
            );
        }

        $data['option_values'] = array();

        foreach ($data['product_options'] as $product_option) {
            if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
                if (!isset($data['option_values'][$product_option['option_id']])) {
                    $data['option_values'][$product_option['option_id']] = $this->model_catalog_option->getOptionValues($product_option['option_id']);
                }
            }
        }


        if (isset($this->request->post['product_special'])) {
            $product_specials = $this->request->post['product_special'];
        } elseif (isset($this->request->get['product_id'])) {
            $product_specials = $this->model_catalog_product->getProductSpecials($this->request->get['product_id']);
        } else {
            $product_specials = array();
        }

        $this->load->model('customer/customer_group');

        $data['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups();

        $data['product_specials'] = array();

        foreach ($product_specials as $product_special) {
            $data['product_specials'][] = array(
                'customer_group_id' => $product_special['customer_group_id'],
                'priority' => $product_special['priority'],
                'price' => $product_special['price'],
                'date_start' => ($product_special['date_start'] != '0000-00-00') ? $product_special['date_start'] : '',
                'date_end' => ($product_special['date_end'] != '0000-00-00') ? $product_special['date_end'] : ''
            );
        }

        // Image
        if (isset($this->request->post['image'])) {
            $data['image'] = $this->request->post['image'];
        } elseif (!empty($product_info)) {
            $data['image'] = $product_info['image'];
        } else {
            $data['image'] = '';
        }

        $this->load->model('tool/image');

        if (isset($this->request->post['image']) && is_file(DIR_IMAGE . $this->request->post['image'])) {
            $data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
        } elseif (!empty($product_info) && is_file(DIR_IMAGE . $product_info['image'])) {
            $data['thumb'] = $this->model_tool_image->resize($product_info['image'], 100, 100);
        } else {
            $data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
        }

        $data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

        // Images
        if (isset($this->request->post['product_image'])) {
            $product_images = $this->request->post['product_image'];
        } elseif (isset($this->request->get['product_id'])) {
            $product_images = $this->model_catalog_product->getProductImages($this->request->get['product_id']);
        } else {
            $product_images = array();
        }

        $data['product_images'] = array();

        foreach ($product_images as $product_image) {
            if (is_file(DIR_IMAGE . $product_image['image'])) {
                $image = $product_image['image'];
                $thumb = $product_image['image'];
            } else {
                $image = '';
                $thumb = 'no_image.png';
            }

            $data['product_images'][] = array(
                'image' => $image,
                'thumb' => $this->model_tool_image->resize($thumb, 100, 100),
                'sort_order' => $product_image['sort_order']
            );
        }

        // Downloads
        $this->load->model('catalog/download');

        if (isset($this->request->post['product_download'])) {
            $product_downloads = $this->request->post['product_download'];
        } elseif (isset($this->request->get['product_id'])) {
            $product_downloads = $this->model_catalog_product->getProductDownloads($this->request->get['product_id']);
        } else {
            $product_downloads = array();
        }

        $data['product_downloads'] = array();

        foreach ($product_downloads as $download_id) {
            $download_info = $this->model_catalog_download->getDownload($download_id);

            if ($download_info) {
                $data['product_downloads'][] = array(
                    'download_id' => $download_info['download_id'],
                    'name' => $download_info['name']
                );
            }
        }

        if (isset($this->request->post['product_related'])) {
            $products = $this->request->post['product_related'];
        } elseif (isset($this->request->get['product_id'])) {
            $products = $this->model_catalog_product->getProductRelated($this->request->get['product_id']);
        } else {
            $products = array();
        }

        $data['product_relateds'] = array();


        foreach ($products as $product_id) {
            $related_info = $this->model_catalog_product->getProduct($product_id);

            if ($related_info) {
                $data['product_relateds'][] = array(
                    'product_id' => $related_info['product_id'],
                    'name' => $related_info['name']
                );
            }
        }

        if (isset($this->request->post['points'])) {
            $data['points'] = $this->request->post['points'];
        } elseif (!empty($product_info)) {
            $data['points'] = $product_info['points'];
        } else {
            $data['points'] = '';
        }

        if (isset($this->request->post['product_reward'])) {
            $data['product_reward'] = $this->request->post['product_reward'];
        } elseif (isset($this->request->get['product_id'])) {
            $data['product_reward'] = $this->model_catalog_product->getProductRewards($this->request->get['product_id']);
        } else {
            $data['product_reward'] = array();
        }

        $data['version'] = VERSION;

        if (VERSION >= 3) {

            if (isset($this->request->post['product_seo_url'])) {
                $data['product_seo_url'] = $this->request->post['product_seo_url'];
            } elseif (isset($this->request->get['product_id'])) {
                $data['product_seo_url'] = $this->model_catalog_product->getProductSeoUrls($this->request->get['product_id']);
            } else {
                $data['product_seo_url'] = array();
            }
        }

        if (isset($this->request->post['product_layout'])) {
            $data['product_layout'] = $this->request->post['product_layout'];
        } elseif (isset($this->request->get['product_id'])) {
            $data['product_layout'] = $this->model_catalog_product->getProductLayouts($this->request->get['product_id']);
        } else {
            $data['product_layout'] = array();
        }
        $data['token_link'] = $this->token_data['token_link'];

        $data['product_id'] = $this->request->get['product_id'];
        // $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('entegrasyon/product/product_form', $data));
    }

    public function delete_error()
    {

        $product_id = $this->request->post['product_id'];
        $code = $this->request->post['code'];
        $this->entegrasyon->deleteError($product_id, $code);

        echo json_encode(array('status' => true));

    }

    public function setting()
    {

        //$data['header'] = $this->load->controller('common/header');
        //$this->model_entegrasyon_general->loadPageRequired();
        if (isset($this->request->get['category_id'])) {
            $category_id = $this->request->get['category_id'];
        } else {
            $category_id = 0;
        }

        $product_id = $this->request->get['product_id'];

        $is_varianter_product = $this->entegrasyon->isVarianterProduct($product_id);



        $this->load->model('entegrasyon/general');
        $data['product_id'] = $product_id;
        $data['category_id'] = $category_id;
        $data['token_link'] = $this->token_data['token_link'];
        $data['code'] = $this->request->get['code'];

        if ($is_varianter_product && !$this->config->get($data['code'] . '_setting_variant')) {
            $data['message'] = 'Ürününüz varyantlı bir ürün, Ürününüzü varyantlı olarak göndermek istiyorsanız genel ayarlardan <strong>Seçenek & Varyant Gönderilsin mi ?</strong> aktif etmelisiniz';
            $data['alert_type'] = 'warning';
        }
        $data['manufacturer'] = $this->entegrasyon->getManufacturerNameByProductId2($product_id);


        $this->response->setOutput($this->load->view('entegrasyon/product/setting', $data));

    }

    public function addproduct($product_id = null, $code = null, $bulk = false)
    {

        if(isset($this->request->post['main_model'])){

            $v_main_model =   $this->request->post['main_model'];
            $v_price_plus =   $this->request->post['price_plus'];
            $v_img =   $this->request->post['img'];
            $v_quantity = $this->request->post['quantity'];
            $v_barcode = $this->request->post['barcode'];
        }else{
            $v_main_model =false;
            $v_barcode =false;
            $v_price_plus =false;
            $v_quantity = false;
            $v_img = false;
        }




        $this->load->model("entegrasyon/general");

        if (!$this->model_entegrasyon_general->checkPermission()) {

            if ($bulk) {
                return array('status' => false, 'message' => 'Gerçek Mağaza bilgileri kullanıldığı için Demo versiyonda ürün gönderilmesine izin verilmemektedir.');
            } else {
                echo json_encode(array('status' => false, 'message' => 'Gerçek Mağaza bilgileri kullanıldığı için Demo versiyonda ürün gönderilmesine izin verilmemektedir.'));
            }
            return;
        }


        $message = '';
        $alert_type = 'error';

        // $code='n11';
        // $product_id=15873;


        if (!$product_id) {

            if (isset($this->request->get['product_id'])) {
                $product_id = $this->request->get['product_id'];

            } else if (isset($this->request->post['product_id'])) {
                $product_id = $this->request->post['product_id'];

            }


        }

        if (!$code) {

            if (isset($this->request->get['code'])) {
                $code = $this->request->get['code'];

            } else if (isset($this->request->post['code'])) {
                $code = $this->request->post['code'];

            }


        }


        $debug = false;
        if (isset($this->request->get['debug'])) {
            $debug = true;

        }


        $this->load->model('catalog/product');
        $this->load->model('entegrasyon/product/' . $code);

        $product_info = $this->entegrasyon->getProduct($product_id, $code);
        $category_setting = $this->entegrasyon->getMarketPlaceCategory($product_id, $code);


        $manufacturer_setting = $this->entegrasyon->getMarketPlaceManufacturer($product_info['manufacturer_id'], $code);
        $product_setting = $this->entegrasyon->getSettingData($code, 'product', $product_id);


        if (!isset($product_setting[$code . '_category_id'])) {
            if ($category_setting == 2) {

                $message .= ' Ürününüz hiç bir kategori ile ilişkilendirilmemiş! Önce Ürününüzü bir kategoriye ekleyiniz. ';

            } else {

                if (!isset($category_setting[$code . '_category_id'])) {
                    $message .= 'Kategori Eşletirmesi Yapmalısınız! ';
                }

            }
        }


        $product_data['defaults'] = $this->entegrasyon->getDefaults($category_setting, $manufacturer_setting, $product_setting, $code);


        /*   if(strlen($product_info['description'])<150) {
               $message .= ' Ürün Açıklaması en az 150 karakter olmalıdır!';
           }*/

        if ($code == 'ty' || $code == 'hb') {
            if (!$product_info['manufacturer_id']) {
                $message .= ' Ürününüz bir markaya ait olmalıdır!. Katalog->Ürünler bölümünden ürününüze bir marka ekleyin';

            } else if ($code == 'ty') {

                if (!isset($manufacturer_setting['ty_manufacturer_id'])) {
                    $message .= ' Marka Eşleştirmesi yapmalısınız!.';
                } else {

                    $product_data['manufacturer_id'] = $manufacturer_setting['ty_manufacturer_id'];

                }

            }

        }


        $is_varianter_product = $this->entegrasyon->isVarianterProduct($product_id);


        if ($message) {


            if ($bulk) {
                return array('status' => false, 'message' => $message);
            } else {

                echo json_encode(array('status' => false, 'message' => $message, 'alert_type' => $alert_type));

            }
            return;
        }


        $category_info = isset($product_setting[$code . '_category_id']) ? $product_setting[$code . '_category_id'] : $category_setting[$code . '_category_id'];

        $category_info = explode('|', $category_info);


        $product_data['category_id'] = $category_info[0];
        $product_data['product_setting'] = $product_setting;
        $product_data['category_setting'] = $category_setting;
        $product_data['product_id'] = $product_id;


        if ($this->config->get($code . '_setting_barkod_place')) {

            if ($product_info[$this->config->get($code . '_setting_barkod_place')]) $product_data['product_setting'][$code . '_barcode'] = $product_info[$this->config->get($code . '_setting_barkod_place')];

        }

        if ($this->config->get($code . '_setting_main_product_id')) {

            if ($product_info[$this->config->get($code . '_setting_main_product_id')]) $product_data['product_setting'][$code . '_main_product_id'] = $product_info[$this->config->get($code . '_setting_main_product_id')];

        }




        $product_data['model'] = $this->config->get($code . '_setting_model_prefix') . $product_info['model'];
        $product_info['model'] = $product_data['model'];


        $product_data['quantity'] = $product_info['quantity'];
        $product_data['special'] = $product_info['special'];


        if ($this->config->get("easy_setting_price_place") && $product_info[$this->config->get("easy_setting_price_place")] ) {


            $product_info['price'] = $product_info[$this->config->get("easy_setting_price_place")];
            $product_info['special'] = $product_info[$this->config->get("easy_setting_price_place")];


        }



        $product_data['list_price'] = $this->entegrasyon->calculatePrice($product_info['price'], $product_data['defaults'], $product_info['tax_class_id'], $code, $product_info);


        $product_data['sale_price'] = $product_info['special'] && $product_data['defaults']['product_special'] ? $this->entegrasyon->calculatePrice($product_info['special'], $product_data['defaults'], $product_info['tax_class_id'], $code, $product_info) : $product_data['list_price'];



        $product_data['main_image'] = $product_info['image'];
        $product_data['title'] = $product_info['name'];
        $product_data['description'] = $product_info['description'];
        $product_data['weight'] = $product_info['weight'];
        $product_data['tag'] = $product_info['tag'];


        $manufacturer = $this->entegrasyon->getManufacturer($product_info['manufacturer_id']);
        if ($manufacturer) {

            $product_data['manufacturer'] = $manufacturer['name'];

        }

        //AUTO MANUFACTURER
        $this->load->model('entegrasyon/category');

        $attributes = $this->model_entegrasyon_category->getAttributes($category_info[0], $code, false);



        if ($code == 'n11') {

            foreach ($attributes['result'] as $item) {


                if (isset($item['name'])) {
                    if ($item['name'] == 'Marka' && $attributes['result']['0']['required'] && $manufacturer) {

                        $product_data['manufacturer'] = $manufacturer['name'];


                    } else {

                        $product_data['manufacturer'] = $manufacturer['name'];

                    }
                }
            }


        }


        $product_data['tax_class_id'] = $product_info['tax_class_id'];
        $product_data['kdv'] = $this->entegrasyon->getKdvRange($product_info['tax_class_id']);


        if ($product_data['defaults']['additional_content']) {
            $this->load->model('catalog/information');
            $information = $this->entegrasyon->getInformationDescriptions($product_data['defaults']['additional_content']);
            $product_data['description'] .= $information;

        }

        if (isset($product_setting[$code . '_product_desciption'])) {

            $product_data['description'] = $product_setting[$code . '_product_desciption'];

        }

        if (isset($product_setting[$code . '_product_shipping_time'])) {

            $product_data['shipping_time'] = $product_setting[$code . '_product_shipping_time'];

        }      if (isset($product_setting[$code . '_currency'])) {

        $product_data['currency'] = $product_setting[$code . '_currency'];

    }

        if (isset($product_setting[$code . '_product_title'])) {

            $product_data['title'] = $product_setting[$code . '_product_title'];

        }


        if (isset($product_setting[$code . '_product_sale_price']) || isset($product_setting[$code . '_product_sale_price']) && !isset($product_setting[$code . '_product_list_price'])) {
            $product_data['sale_price'] = $product_setting[$code . '_product_sale_price'];
            if (!isset($product_setting[$code . '_product_list_price'])) {
                $product_info['have_discount'] = false;
                if (isset($product_info['sale_price'])) {
                    $product_data['list_price'] = $product_info['sale_price'];

                }

            }
            if (isset($product_setting[$code . '_product_sale_price']) || !isset($product_setting[$code . '_product_list_price'])) {
                $product_data['list_price'] = $product_setting[$code . '_product_sale_price'];
                $product_data['list_price'] = $product_setting[$code . '_product_sale_price'];
            } else if (!isset($product_setting[$code . '_product_sale_price']) || isset($product_setting[$code . '_product_list_price'])) {

                $product_data['list_price'] = $product_setting[$code . '_product_list_price'];
                $product_data['list_price'] = $product_setting[$code . '_product_list_price'];
            }

        }


        if (isset($product_setting[$code . '_product_list_price']) && isset($product_setting[$code . '_product_sale_price'])) {
            if ($product_setting[$code . '_product_list_price'] > $product_setting[$code . '_product_sale_price']) {
                $product_info['have_discount'] = true;
            }
            $product_data['list_price'] = $product_setting[$code . '_product_list_price'];
        }

        $attributes=$this->entegrasyon->getSelectedAttributes($code,$product_setting,$category_setting);



        if (isset($product_setting[$code . '_product_list_price']) && !isset($product_setting[$code . '_product_sale_price'])) {

            $product_data['list_price'] = $product_setting[$code . '_product_list_price'];
            $product_data['sale_price'] = $product_setting[$code . '_product_list_price'];
        }




        $product_data['product_setting']['selected_attributes'] = $attributes;
        $need_select = $this->entegrasyon->checkRequiredAttributes($category_info[0], $code, $attributes, $product_id);


        if ($need_select) {

            $message = 'Girmeniz Gereken Zorunlu Özellikler:' . implode('-', $need_select);

            if ($bulk) {
                return array('status' => false, 'message' => $message);

            } else {

                echo json_encode(array('status' => false, 'message' => $message, 'alert_type' => $alert_type));
                return;
            }

        }


        if ($v_main_model){
            $is_varianter_product = 1;
        }


        $product_data['variants'] = array();
        if ($this->config->get($code . '_setting_variant')) {

            if ($is_varianter_product) {

                $matched_options = $this->entegrasyon->isOptionsMatched($category_info[0], $code);

                if ($matched_options && $code != 'hb' && $code != 'cs') {

                    $message = 'Eşleştirmeniz gereken Seçenekler Var:' . implode('-', $matched_options);

                    if ($bulk) {
                        return array('status' => false, 'message' => $message);

                    } else {

                        echo json_encode(array('status' => false, 'message' => $message, 'alert_type' => $alert_type));
                        return;
                    }

                }

                $product_variants = $this->entegrasyon->getPoductVariants($product_id);
                $market_variants = $this->entegrasyon->getMarketVariant($product_variants, $code, $category_info[0], $product_id, $product_data['model'], HTTPS_CATALOG, array('tax_class_id' => $product_info['tax_class_id'], 'defaults' => $product_data['defaults']));

                if ($code == 'ty' || $code == 'gg' || $code == 'cs') {

                    $attributes = $this->entegrasyon->deleteIfInAttbutes($market_variants, $attributes);
                }

                if ($market_variants['status']) {
                    if ($v_main_model){
                        foreach ($market_variants['variants'] as $market_variant) {
                            if ($market_variant['barcode'] == $v_barcode ){
                                foreach ($market_variant['attributes'] as $attribute) {
                                    $product_data['product_setting']['selected_attributes'][] = array('name' => $attribute['attributeId'], 'value' => $attribute['name']);
                                }
                            }
                        }
                    }else{
                        $product_data['variants']['variants']['options'] = $market_variants['variants'];

                    }
                    //$message = $market_variants['message'];
                } else {

                    $message = $market_variants['message'];
                    echo json_encode(array('status' => false, 'message' => $message));
                    return;
                }

                // print_r($market_variants['variants']);
            }

        }


        if ($this->config->get('easy_setting_list_price') && $this->config->get($code . '_setting_product_special')) {

            $product_data['list_price'] = $product_data['sale_price'];
            $product_data['special'] = false;

        }




        //$this->config->get('easy_setting_email')
        //print_r($product_data);return;

        if ($v_main_model){
            $product_data['images'] = $this->entegrasyon->getImagesByMarketPlace($product_id, $product_info['image'], $code, HTTPS_CATALOG,$v_img);

            $product_data['quantity'] = $v_quantity;
            $product_data['model'] = $v_barcode;
            $product_data['v_mode'] = $v_barcode;
            $product_data['sale_price'] =  $product_data['sale_price'] + $v_price_plus;
            $product_data['list_price'] =$product_data['list_price'] + $v_price_plus;
            $product_data['product_setting'][$code.'_product_code']=$v_main_model;
            $attributes= $product_data['product_setting']['selected_attributes'];

        }else{

            $product_data['images'] = $this->entegrasyon->getImagesByMarketPlace($product_id, $product_info['image'], $code, HTTPS_CATALOG);

        }

        $product_data['attributes'] = $attributes;

        if(isset($manufacturer_setting[$code.'_manufacturer_id'])){

            $product_data['manufacturer']=$manufacturer_setting[$code.'_manufacturer_id'];

        }


        $result = $this->{"model_entegrasyon_product_" . $code}->sendProduct($product_data, $attributes, $debug);

        if (!$result['status']) {

            $error = $this->entegrasyon->getError($product_id, $code);

            if ($error) {

                $this->entegrasyon->updateError($product_id, $code, 1, $result['message']);

            } else {

                $this->entegrasyon->addError($product_id, $code, 1, $result['message']);
            }

        }

        $logmesage = $product_info['model'] . ' Action:Add Product';;

        $logmesage .= '-Stock - :' . $product_info['quantity'] . ' - Sale Price:' . $product_info['sale_price'] . ' - List Price:' . $product_info['list_price'];

        $logmesage .= '- Result:' . $result['message'];

        $this->entegrasyon->log($code, $logmesage, $bulk);


        if ($bulk) {
            return $result;
        } else {

            $json = json_encode($result);

            echo $json;
        }


    }
    public function send_to_marketplace()
    {

        $this->load->model("entegrasyon/general");

        if (!$this->model_entegrasyon_general->checkPermission()) {

            echo json_encode(array('status' => false, 'message' => 'Gerçek Mağaza bilgileri kullanıldığı için Demo versiyonda ürün gönderilmesine izin verilmemektedir.'));
            return;

        }

        $list = $this->request->post['list'];


        // $reference = $this->request->post['reference'];
        $product_id = $this->request->get['product_id'];
        $oc_category_id = $this->request->get['category_id'];
        $code = $this->request->get['code'];
        $current_key = array_search($product_id, $list);


        $this->load->model('entegrasyon/category');
        $category_setting = $this->model_entegrasyon_category->getMarketCategory($oc_category_id, $code);

        $message = '';
        $this->load->model('entegrasyon/product/' . $code);

        $product_info = $this->entegrasyon->getProduct($product_id);


        if(!$product_info['model']){

            $message='Ürününüze tanımlı model verisi bulunamadı, model kodu olmayan ürünler listelenemez!';

        }


        ///$category_setting=$this->entegrasyon->getMarketPlaceCategory($product_id,$code);
        $manufacturer_setting = $this->entegrasyon->getMarketPlaceManufacturer($product_info['manufacturer_id'], $code);
        $product_setting = $this->entegrasyon->getSettingData($code, 'product', $product_id);

        if (!isset($product_setting[$code . '_category_id'])) {
            if ($category_setting == 2) {

                $message .= ' Ürününüz hiç bir kategori ile ilişkilendirilmemiş! Önce Ürününüzü bir kategoriye ekleyiniz. ';

            } else {

                if (!isset($category_setting[$code . '_category_id'])) {
                    $message .= 'Kategori Eşletirmesi Yapmalısınız! ';
                }

            }
        }

        // print_r($category_setting[$code . '_category_id']);
        // return;

        $product_data['defaults'] = $this->entegrasyon->getDefaults($category_setting, $manufacturer_setting, $product_setting, $code);

        if ($product_data['defaults']['additional_content']) {

            $information = $this->entegrasyon->getInformationDescriptions($product_data['defaults']['additional_content']);
            $product_info['description'] = $product_info['description'] . $information;

        }


        if ($code == 'ty' || $code == 'hb') {
            if (!$product_info['manufacturer_id']) {
                $message .= ' Ürününüz bir markaya ait olmalıdır!. Katalog->Ürünler bölümünden ürününüze bir marka ekleyin';

            } else if ($code == 'ty') {

                if (!isset($manufacturer_setting['ty_manufacturer_id'])) {
                    $message .= ' Marka Eşleştirmesi yapmalısınız!.';
                } else {

                    $product_data['manufacturer_id'] = $manufacturer_setting['ty_manufacturer_id'];

                }

            }

        }


        if ($message) {


            if ($current_key + 1 < count($list)) {


                echo json_encode(array('status' => false, 'next' => true, 'item' => $list[$current_key + 1], 'list' => $list, 'current' => $current_key + 1, 'message' => $message));


            } else {

                echo json_encode(array('status' => false, 'next' => false, 'item' => $list[$current_key], 'list' => $list, 'current' => $current_key + 1, 'message' => $message));

            }
            return;
        }


        $category_info = isset($product_setting[$code . '_category_id']) ? $product_setting[$code . '_category_id'] : $category_setting[$code . '_category_id'];


        $category_info = explode('|', $category_info);

        $product_data['category_id'] = $category_info[0];
        $product_data['product_setting'] = $product_setting;
        $product_data['category_setting'] = $category_setting;
        $product_data['product_id'] = $product_id;


        if ($this->config->get($code . '_setting_barkod_place')) {

            if ($product_info[$this->config->get($code . '_setting_barkod_place')]) $product_data['product_setting'][$code . '_barcode'] = $product_info[$this->config->get($code . '_setting_barkod_place')];

        }
        if ($this->config->get($code . '_setting_main_product_id')) {
            if ($product_info[$this->config->get($code . '_setting_main_product_id')]) $product_data['product_setting'][$code . '_main_product_id'] = $product_info[$this->config->get($code . '_setting_main_product_id')];
        }

        $product_data['model'] = $this->config->get($code . '_setting_model_prefix') . $product_info['model'];
        $product_data['quantity'] = $product_info['quantity'];
        $product_data['special'] = $product_info['special'];

        if ($this->config->get("easy_setting_price_place") && $product_info[$this->config->get("easy_setting_price_place")] ) {

            $product_info['price'] =   $product_info[$this->config->get("easy_setting_price_place")];
            $product_info['spacial'] =   $product_info[$this->config->get("easy_setting_price_place")];

        }

        $product_data['list_price'] = $this->entegrasyon->calculatePrice($product_info['price'], $product_data['defaults'], $product_info['tax_class_id'], $code, $product_info);
        $product_data['sale_price'] = $product_info['special'] && $product_data['defaults']['product_special'] ? $this->entegrasyon->calculatePrice($product_info['special'], $product_data['defaults'], $product_info['tax_class_id'], $code, $product_info) : $product_data['list_price'];

        $product_data['main_image'] = $product_info['image'];
        $product_data['title'] = $product_info['name'];
        $product_data['description'] = $product_info['description'];
        $product_data['weight'] = $product_info['weight'];
        $product_data['tag'] = $product_info['tag'];


        $product_data['tax_class_id'] = $product_info['tax_class_id'];
        $product_data['kdv'] = $this->entegrasyon->getKdvRange($product_info['tax_class_id']);


        $product_data['attributes'] = array();
        /* if ($reference) {
             $product_setting = $this->entegrasyon->getSettingData($code, 'product', $reference);
         }*/




        $attributes=$this->entegrasyon->getSelectedAttributes($code,$product_setting,$category_setting,true);

        $manufacturer = $this->entegrasyon->getManufacturer($product_info['manufacturer_id']);


        if(isset($manufacturer['name'])){

            $product_data['manufacturer'] = $manufacturer['name'];

        }



        $need_select = $this->entegrasyon->checkRequiredAttributes($category_info[0], $code, $attributes, $product_id);

        if ($need_select) {

            $message = 'Girmeniz Gereken Zorunlu Özellikler:' . implode('-', $need_select);

            if ($current_key + 1 < count($list)) {


                echo json_encode(array('status' => false, 'next' => true, 'item' => $list[$current_key + 1], 'list' => $list, 'current' => $current_key + 1, 'message' => $message));


            } else {

                echo json_encode(array('status' => false, 'next' => false, 'item' => $list[$current_key], 'list' => $list, 'current' => $current_key + 1, 'message' => $message));

            }
            return;

        }


        if (!$reference &&  isset($product_setting[$code . '_product_desciption'])) {

            $product_data['description'] = $product_setting[$code . '_product_desciption'];

        }

        if (!$reference &&  isset($product_setting[$code . '_product_title'])) {

            $product_data['title'] = $product_setting[$code . '_product_title'];

        }

        if (!$reference &&  isset($product_setting[$code . '_product_shipping_time'])) {

            $product_data['shipping_time'] = $product_setting[$code . '_product_shipping_time'];

        }



        if (!$reference &&  (isset($product_setting[$code . '_product_sale_price']) || isset($product_setting[$code . '_product_sale_price']) && !isset($product_setting[$code . '_product_list_price']))) {
            $product_info['have_discount'] = false;
            $product_data['sale_price'] = $product_setting[$code . '_product_sale_price'];
            if (!isset($product_setting[$code . '_product_list_price'])) {
                $product_info['have_discount'] = false;
                $product_data['list_price'] = $product_info['sale_price'];

            }

            $product_data['list_price'] = $product_data['sale_price'];
        }
        if ( !$reference &&  isset($product_setting[$code . '_product_list_price'])) {
            if ($product_setting[$code . '_product_list_price'] > $product_setting[$code . '_product_sale_price']) {
                $product_info['have_discount'] = true;
            }
            $product_data['list_price'] = $product_setting[$code . '_product_list_price'];
        }

        $product_data['variants'] = array();


        if ($this->config->get($code . '_setting_variant')) {

            if ($this->entegrasyon->isVarianterProduct($product_id)) {
                $product_variants = $this->entegrasyon->getPoductVariants($product_id);
                $market_variants = $this->entegrasyon->getMarketVariant($product_variants, $code, $category_info[0], $product_id, $product_data['model'], HTTPS_CATALOG, array('tax_class_id' => $product_info['tax_class_id'], 'defaults' => $product_data['defaults']));

                if ($code == 'ty' || $code == 'gg' || $code == 'cs') {

                    $attributes = $this->entegrasyon->deleteIfInAttbutes($market_variants, $attributes);
                }


                if ($market_variants['status']) {
                    $product_data['variants']['variants']['options'] = $market_variants['variants'];

                } else {

                    $message = $market_variants['message'];

                    if ($current_key + 1 < count($list)) {


                        echo json_encode(array('status' => false, 'next' => true, 'item' => $list[$current_key + 1], 'list' => $list, 'current' => $current_key + 1, 'message' => $message));


                    } else {

                        echo json_encode(array('status' => false, 'next' => false, 'item' => $list[$current_key], 'list' => $list, 'current' => $current_key + 1, 'message' => $message));

                    }
                    return;

                }

                // print_r($market_variants['variants']);
            }

        }

        if ($this->config->get('easy_setting_list_price') && $this->config->get($code . '_setting_product_special')) {

            $product_data['list_price'] = $product_data['sale_price'];
            $product_data['special'] = false;

        }

        $product_data['images'] = $this->entegrasyon->getImagesByMarketPlace($product_id, $product_info['image'], $code, HTTPS_CATALOG);



        $debug = false;
        $product_data['attributes'] = $attributes;


        if(isset($manufacturer_setting[$code.'_manufacturer_id'])){

            $product_data['manufacturer']=$manufacturer_setting[$code.'_manufacturer_id'];

        }

        $result = $this->{"model_entegrasyon_product_" . $code}->sendProduct($product_data, $product_data['attributes'], $debug);


        if (!$result['status']) {

            $error = $this->entegrasyon->getError($product_id, $code);
            if ($error) {
                $this->entegrasyon->updateError($product_id, $code, 1, $result['message']);
            } else {
                $this->entegrasyon->addError($product_id, $code, 1, $result['message']);
            }
        }

        $logmesage = $product_info['model'] . ' Action:Add bulk Product ';;

        $logmesage .= '-Stock - :' . $product_info['quantity'] . ' - Sale Price:' . $product_info['sale_price'] . ' - List Price:' . $product_info['list_price'];

        $logmesage .= '- Result:' . $result['message'];

        $this->entegrasyon->log($code, $logmesage, true);


        if ($current_key + 1 < count($list)) {


            echo json_encode(array('status' => $result['status'], 'next' => true, 'item' => $list[$current_key + 1], 'list' => $list, 'current' => $current_key + 1, 'message' => $result['message']));


        } else {

            echo json_encode(array('status' => $result['status'], 'next' => false, 'item' => $list[$current_key], 'list' => $list, 'current' => $current_key + 1, 'message' => $result['message']));

        }


    }


    public function variants()
    {

        $product_id = $this->request->get['product_id'];


        $data['product_id'] = $product_id;
        // $data['options'] = $getOptionsNames;
        $this->load->model('entegrasyon/product');
        $data['total_options'] = $this->model_entegrasyon_product->getTotalOptions($product_id);
        $data['token_link'] = $this->token_data['token_link'];
        $this->response->setOutput($this->load->view('entegrasyon/product/variants', $data));

    }

    public function variant_list()
    {
        $product_id = $this->request->get['product_id'];
        $action = $this->request->get['action'];
        if ($action == 'rebuild') {

            $this->db->query("DELETE FROM " . DB_PREFIX . "es_product_variant where product_id='" . $product_id . "' ");

        }

        $data['product_variants'] = array();

        $getOptions = $this->entegrasyon->getProductOptionTitles($product_id);

        if ($getOptions) {
            $product_variants = $this->entegrasyon->getPoductVariants($product_id);

            foreach ($product_variants as $product_variant) {
                if (is_file(DIR_IMAGE . $product_variant['image'])) {
                    $image = $this->model_tool_image->resize($product_variant['image'], 70, 70);
                } else {
                    $image = $this->model_tool_image->resize('no_image.png', 70, 70);
                }
                $data['product_variants'][] = array(

                    'variant_id' => $product_variant['variant_id'],
                    'name' => $product_variant['name'],
                    'image' => $image,
                    'barcode' => $product_variant['barcode'],
                    'model' => $product_variant['model'],
                    'quantity' => $product_variant['quantity'],
                    'price' => $product_variant['price'],


                );


            }

        } else {

            $this->db->query("DELETE FROM " . DB_PREFIX . "es_product_variant where product_id='" . $product_id . "' ");

        }


        $data['product_id'] = $product_id;
        $data['token_link'] = $this->token_data['token_link'];
        $this->response->setOutput($this->load->view('entegrasyon/product/variant_list', $data));

    }

    public function option_list()
    {

        $this->load->language('catalog/product');
        $data = $this->language->all();

        // Options
        $product_id = $this->request->get['product_id'];


        $product_options = $this->entegrasyon->getProductOptions($product_id);

        $data['product_options'] = array();

        foreach ($product_options as $product_option) {
            $product_option_value_data = array();

            if (isset($product_option['product_option_value'])) {
                foreach ($product_option['product_option_value'] as $product_option_value) {
                    $product_option_value_data[] = array(
                        'product_option_value_id' => $product_option_value['product_option_value_id'],
                        'option_value_id' => $product_option_value['option_value_id'],
                        'quantity' => $product_option_value['quantity'],
                        'subtract' => $product_option_value['subtract'],
                        'price' => $product_option_value['price'],
                        'price_prefix' => $product_option_value['price_prefix'],
                        'points' => $product_option_value['points'],
                        'points_prefix' => $product_option_value['points_prefix'],
                        'weight' => $product_option_value['weight'],
                        'weight_prefix' => $product_option_value['weight_prefix']
                    );
                }
            }

            $data['product_options'][] = array(
                'product_option_id' => $product_option['product_option_id'],
                'product_option_value' => $product_option_value_data,
                'option_id' => $product_option['option_id'],
                'name' => $product_option['name'],
                'type' => $product_option['type'],
                'value' => isset($product_option['value']) ? $product_option['value'] : '',
                'required' => $product_option['required']
            );
        }

        $data['option_values'] = array();

        foreach ($data['product_options'] as $product_option) {
            if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
                if (!isset($data['option_values'][$product_option['option_id']])) {
                    $data['option_values'][$product_option['option_id']] = $this->entegrasyon->getOptionValues($product_option['option_id']);
                }
            }
        }
        $data['token_link'] = $this->token_data['token_link'];
        $data['product_id'] = $product_id;

        $this->response->setOutput($this->load->view('entegrasyon/product/option_list', $data));


    }

    public function update_option()
    {
        $product_id = $this->request->get['product_id'];
        $this->load->model('entegrasyon/product');
        $this->model_entegrasyon_product->updateProductOptions($this->request->post, $product_id);
        echo json_encode(array('status' => true));

    }


    public function update_variant()
    {
        $status = false;

        if ($this->request->post['name'] == 'barcode') {

            $result = $this->entegrasyon->updateProductVariant($this->request->post['name'], $this->request->post['value'], $this->request->post['pk']);
            echo json_encode(array('status' => true));
            return;

        }
        $img = $this->request->post['value'];
        $img_without_domain = explode(HTTP_CATALOG . 'image/', $img);


        if (count($img_without_domain) == 2) {
            $ext = pathinfo($img_without_domain[1], PATHINFO_EXTENSION);

            $img_without_resized = explode($ext, str_replace('cache/', '', $img_without_domain[1]));

            $img_clear_array = explode('-', $img_without_resized[0]);

            unset($img_clear_array[count($img_clear_array) - 1]);

            $status = true;
        } else {
            $status = false;
        }

        $image = implode('-', $img_clear_array) . '.' . $ext;

        if ($status) {

            $result = $this->entegrasyon->updateProductVariant($this->request->post['name'], $image, $this->request->post['pk']);
            if ($result) {
                $status = true;
            }
        }


        echo json_encode(array('status' => $status));


    }


    public function close_for_sale($product_id = null, $code = null, $bulk = false)
    {



        $this->load->model("entegrasyon/general");
        if (!$product_id) {
            $product_id = $this->request->post['product_id'];
        }

        if (!$code) {

            $code = $this->request->post['code'];

        }

        if (!$this->model_entegrasyon_general->checkPermission()) {

            if ($bulk) {
                return array('status' => false, 'message' => 'Gerçek Mağaza bilgileri kullanıldığı için Demo versiyonda ürün işlemlerine izin verilmemektedir.');
            } else {
                echo json_encode(array('status' => false, 'message' => 'Gerçek Mağaza bilgileri kullanıldığı için Demo versiyonda ürün gönderilmesine izin verilmemektedir.'));
            }
            return;
        }


        $product_info = $this->entegrasyon->getProduct($product_id);
        $marketplace_data = $this->entegrasyon->getMarketPlaceProductForMarket($product_id, $code);
        if (!$marketplace_data) {

            if ($bulk) {
                return array('status' => false, 'message' => 'Ürün mağazada bulunamadı, işlem yapabilmek için önce ürünü mağazaya göndermelisiniz.');
            } else {
                echo json_encode(array('status' => false, 'message' => 'Ürün mağazada bulunamadı, işlem yapabilmek için önce ürünü mağazaya göndermelisiniz.'));
            }
            return;

        }

        if ($code == 'hb') {

            $product_data = $this->entegrasyon->getMarketVariants($product_id, $code);

        } else {

            $product_data = $this->entegrasyon->getProductForUpdate($code, $product_info, $marketplace_data['commission'], 0);

        }


        if ($code != 'hb') {
            $post_data['request_data']['product_id'] = isset($marketplace_data['product_id']) ? $marketplace_data['product_id'] : '';
            $post_data['request_data']['model'] = $this->config->get($code . '_setting_model_prefix') . $product_info['model'];
            $post_data['request_data']['quantity'] = $product_data['quantity'];
            $post_data['request_data']['list_price'] = $product_data['list_price'];
            $post_data['request_data']['sale_price'] = $product_data['sale_price'];
            $post_data['request_data']['kdv'] = $this->entegrasyon->getKdvRange($product_data['tax_class_id']);
        }


        if ($code == 'ty' || $code == 'cs' || $code == 'hb') {
            $post_data['request_data'] = $product_data;
        }

        $post_data['market'] = $this->model_entegrasyon_general->getMarketPlace($code);
        $post_data['request_data']['market'] = $marketplace_data;

        $result = $this->entegrasyon->clientConnect($post_data, 'close_for_sale', $code, false);

        if (!isset($product_data['sale_price'])){
            $product_data =  $product_data[0];

        }

        $logmesage = $product_info['model'] . ' Action: Open for sale ';;

        $logmesage .= '-Stock - :' . $product_data['quantity'] . ' - Sale Price:' . $product_data['sale_price'] . ' - List Price:' . $product_data['list_price'];

        $logmesage .= '- Result:' . $result['message'];

        $this->entegrasyon->log($code, $logmesage, $bulk);

        if ($result['status']) {
            $marketplace_data['sale_status'] = 0;
            $marketplace_data['approval_status'] = 1;
            $this->entegrasyon->addMarketplaceProduct($product_id, $marketplace_data, $code);
            $price = $this->currency->format(0, $this->config->get('config_currency'));
            if ($bulk) {

                return array('status' => true, 'price' => $price, 'message' => $product_info['name'] . ' Başarıyla Satışa Kapatılmıştır.');

            } else {

                echo json_encode(array('status' => true, 'price' => $price, 'message' => 'Ürün Başarıyla Satışa Kapatılmıştır.'));

            }

        } else {

            if ($bulk) {

                return array('status' => false, 'message' => $product_info['name'] . '-' . $result['message']);

            } else {

                echo json_encode(array('status' => false, 'message' => $result['message']));

            }

        }


    }

    public function open_for_sale($product_id = null, $code = null, $bulk = false)
    {

        $this->load->model("entegrasyon/general");


        if (!$this->model_entegrasyon_general->checkPermission()) {

            if ($bulk) {
                return array('status' => false, 'message' => 'Gerçek Mağaza bilgileri kullanıldığı için Demo versiyonda ürün işlemlerine izin verilmemektedir.');
            } else {
                echo json_encode(array('status' => false, 'message' => 'Gerçek Mağaza bilgileri kullanıldığı için Demo versiyonda ürün gönderilmesine izin verilmemektedir.'));
            }
            return;
        }


        if (!$product_id) {
            $product_id = $this->request->post['product_id'];

        }

        if (!$code) {

            $code = $this->request->post['code'];

        }


        $product_info = $this->entegrasyon->getProduct($product_id);


        if (!$product_info['quantity']) {

            if ($bulk) {
                return array('status' => false, 'message' => "Ürünü satışa açabilmeniz için ürün stoğu 0'dan büyük olmalıdır.");
            } else {
                echo json_encode(array('status' => false, 'message' => "Ürünü satışa açabilmeniz için ürün stoğu 0'dan büyük olmalıdır."));
            }
            return;
        }

        $marketplace_data = $this->entegrasyon->getMarketPlaceProductForMarket($product_id, $code);


        if (!$marketplace_data) {

            if ($bulk) {
                return array('status' => false, 'message' => 'Ürün mağazada bulunamadı, işlem yapabilmek için önce ürünü mağazaya göndermelisiniz.');
            } else {
                echo json_encode(array('status' => false, 'message' => 'Ürün mağazada bulunamadı, işlem yapabilmek için önce ürünü mağazaya göndermelisiniz.'));
            }
            return;

        }


        if ($code == 'hb') {

            $product_data = $this->entegrasyon->getMarketVariants($product_id, $code);

        } else {

            $product_data = $this->entegrasyon->getProductForUpdate($code, $product_info, $marketplace_data['commission'], 0);

        }

        if ($code != 'hb') {
            $post_data['request_data']['kdv'] = $this->entegrasyon->getKdvRange($product_data['tax_class_id']);
            $post_data['request_data']['product_id'] = isset($marketplace_data['product_id']) ? $marketplace_data['product_id'] : '';
            $post_data['request_data']['model'] = $this->config->get($code . '_setting_model_prefix') . $product_info['model'];
            $post_data['request_data']['quantity'] = $product_data['quantity'];
            $post_data['request_data']['list_price'] = $product_data['list_price'];
            $post_data['request_data']['sale_price'] = $product_data['sale_price'];
        }
        if ($code == 'ty' || $code == 'cs' || $code == 'hb') {
            $post_data['request_data'] = $product_data;
        }
        $post_data['market'] = $this->model_entegrasyon_general->getMarketPlace($code);
        $post_data['request_data']['market'] = $marketplace_data;

        $result = $this->entegrasyon->clientConnect($post_data, 'open_for_sale', $code, false);


        if (!isset($product_data['quantity'])){
            $product_data=$product_data[0];
        }
        $logmesage = $product_info['model'] . ' Action: Close for sale ';;

        $logmesage .= '-Stock - :' . $product_data['quantity'] . ' - Sale Price:' . $product_data['sale_price'] . ' - List Price:' . $product_data['list_price'];

        $logmesage .= '- Result:' . $result['message'];

        $this->entegrasyon->log($code, $logmesage, $bulk);

        if ($result['status']) {
            $marketplace_data['sale_status'] = 1;
            $marketplace_data['price'] = $product_data['list_price'];
            $marketplace_data['sale_price'] = $product_data['sale_price'];


            $this->entegrasyon->addMarketplaceProduct($product_id, $marketplace_data, $code);

            $price = $this->currency->format($product_data['sale_price'], $this->config->get('config_currency'));

            if ($bulk) {

                return array('status' => true, 'price' => $price, 'message' => $product_info['name'] . ' Başarıyla Satışa Açılmıştır.');

            } else {

                echo json_encode(array('status' => true, 'price' => $price, 'message' => 'Ürün Başarıyla Satışa Açılmıştır.'));

            }


        } else {


            if ($bulk) {

                return array('status' => false, 'message' => $result['message']);

            } else {

                echo json_encode(array('status' => false, 'message' => $product_info['name'] . '-' . $result['message']));

            }

        }


    }

    public function deleteproduct($product_id = null, $code = null, $bulk = false)
    {

        if (!$this->model_entegrasyon_general->checkPermission()) {

            if ($bulk) {
                return array('status' => false, 'message' => 'Gerçek Mağaza bilgileri kullanıldığı için Demo versiyonda ürün işlemlerine izin verilmemektedir.');
            } else {
                echo json_encode(array('status' => false, 'message' => 'Gerçek Mağaza bilgileri kullanıldığı için Demo versiyonda ürün gönderilmesine izin verilmemektedir.'));
            }
            return;
        }


        if (!$product_id) {
            $product_id = $this->request->post['product_id'];

        }

        if (!$code) {

            $code = $this->request->post['code'];

        }

        $this->load->model('entegrasyon/general');
        $marketPlace = $this->model_entegrasyon_general->getMarketPlace($code);

        if ($code == 'ty' || $code == 'hb' || $code == 'cs') {

            $this->entegrasyon->deleteMarketplaceProduct($product_id, $code);

            $result = array('status' => true, 'type' => 1, 'message' => $marketPlace["name"] . ' Pazaryerinde ürün silme özelliği mevcut değildir! Ürününüz henüz oyanlanmadıysa ürünü ' . $marketPlace['name'] . ' panelinizden silebilirsiniz. Ürünü tekrar göndermek için ' . $marketPlace['name'] . ' panelinizden silmeniz gerekmektedir.  Ürün Entegrasyon\'dan silindi!');

            if ($bulk) {
                return $result;

            } else {

                echo json_encode($result);

            }
            return;
        }


        if ($code == 'eptt') {

            echo json_encode(array('status' => false, 'type' => 1, 'message' => $marketPlace['name'] . ' Pazaryerinde ürün silme özelliği mevcut değildir! Ancak ürün stoğunu sıfırlayarak satışa kapatabilirsiniz. Stoğu sıfıramak ve satışa kapatmak istermisiniz ?'));
            return;
        }

        $this->load->model('entegrasyon/product/' . $code);
        $result = $this->{"model_entegrasyon_product_" . $code}->deleteProduct($product_id);

        $logmesage = $product_id . ' Action: Delete Product ';;


        $logmesage .= '- Result:' . $result['message'];

        $this->entegrasyon->log($code, $logmesage, $bulk);


        if ($bulk) {
            return $result;

        } else {

            echo json_encode($result);

        }


    }

    public function reset_stock()
    {

        $product_id = $this->request->post['product_id'];
        $code = $this->request->post['code'];
        $product_info = $this->entegrasyon->getProduct($product_id);
        $this->load->model('entegrasyon/product/' . $code);
        $result = $this->{"model_entegrasyon_product_" . $code}->reset_stock($product_info);


        echo json_encode($result);
    }


    public function update_quantity()
    {
        $quantity = $this->request->post['quantity'];
        $product_id = $this->request->post['product_id'];
        $product_info = $this->entegrasyon->getProduct($product_id);

        $this->db->query("update " . DB_PREFIX . "product SET quantity='" . $quantity . "' where product_id='" . $product_id . "'");

        $logmesage = 'By ' . $this->user->getUserName() . ' Product and options stock ' . $product_info['quantity'] . ' to ' . $quantity . ' updated on your OC catalog. Product Model:' . $product_info['model'];
        $this->entegrasyon->log('All', $logmesage, false);
        echo json_encode(array('status', 'result' => $quantity));

    }

    public function update_products_after_modified_page()
    {

        $data['product_id'] = $this->request->get['product_id'];
        $data['token_link'] = $this->token_data['token_link'];
        $this->response->setOutput($this->load->view('entegrasyon/product/update_after_modified', $data));

    }

    public function update_products_after_modified()
    {

        $products[] = $this->request->post['product_id'];
        $results = $this->entegrasyon->updateMarketplaceProdutcsAfterOrder($products, HTTP_CATALOG);
        $json = array();

        foreach ($results as $products) {
            foreach ($products as $code => $result) {

                $json[] = array('status' => $result['status'], 'marketplace' => $this->entegrasyon->marketPlaces[$code], 'message' => $result['status'] ? 'Başarıyla Güncellendi' : $result['message']);

            }



        }

        echo json_encode($json);
    }

    public function update_price()
    {

        $price = $this->request->post['value'];
        $product_id = $this->request->post['pk'];
        $code = $this->request->post['name'];
        $this->load->controller('entegrasyon/genel/save_setting', array('code' => $code, 'primary_id' => $product_id, 'name' => $code . '_product_sale_price', 'value' => $price, 'controller' => 'product'));
        $result = $this->update($product_id, $code, true, $price);
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($result));


    }


    public function update($product_id = null, $code = null, $bulk = false, $price = false)
    {

        error_reporting(E_ALL);
        ini_set('display_errors', 0);
        $this->load->model("entegrasyon/general");

        if (!$this->model_entegrasyon_general->checkPermission()) {

            if ($bulk) {
                return array('status' => false, 'message' => 'Gerçek Mağaza bilgileri kullanıldığı için Demo versiyonda ürün işlemlerine izin verilmemektedir.');
            } else {
                echo json_encode(array('status' => false, 'message' => 'Gerçek Mağaza bilgileri kullanıldığı için Demo versiyonda ürün gönderilmesine izin verilmemektedir.'));
            }
            return;
        }

        //$code='gg';
        //$product_id=15943;


        if (!$product_id) {

            if (isset($this->request->get['product_id'])) {
                $product_id = $this->request->get['product_id'];

            } else if (isset($this->request->post['product_id'])) {
                $product_id = $this->request->post['product_id'];

            }


        }


        if (isset($this->request->get['mode'])) {
            $mode = $this->request->get['mode'];

        } else {
            $mode = '';
        }


        if (!$code) {

            if (isset($this->request->get['code'])) {
                $code = $this->request->get['code'];
            } else if (isset($this->request->post['code'])) {
                $code = $this->request->post['code'];
            }
        }


        $debug = false;
        if (isset($this->request->get['debug'])) {
            $debug = true;

        }


        $marketplace_data = $this->entegrasyon->getMarketPlaceProductForMarket($product_id, $code);


        if (!$marketplace_data) {

            if ($bulk) {
                return array('status' => false, 'message' => 'Ürün mağazada bulunamadı, işlem yapabilmek için önce ürünü mağazaya göndermelisiniz.');
            } else {
                echo json_encode(array('status' => false, 'message' => 'Ürün mağazada bulunamadı, işlem yapabilmek için önce ürünü mağazaya göndermelisiniz.'));
            }
            return;

        }

        $product_info = $this->entegrasyon->getProduct($product_id);


        if (!$product_info) {
            if ($bulk) {
                return array('status' => false, 'message' => 'Ürün Kataloğunuzda bulunamadı!');
            } else {
                echo json_encode(array('status' => false, 'message' => 'Ürün Kataloğunuzda bulunamadı'));
            }
            return;

        }

        $category_setting = $this->entegrasyon->getMarketPlaceCategory($product_id, $code);
        $manufacturer_setting = $this->entegrasyon->getMarketPlaceManufacturer($product_info['manufacturer_id'], $code);
        $product_setting = $this->entegrasyon->getSettingData($code, 'product', $product_id);


        if (!$marketplace_data['sale_status'] && $code != 'hb') {

            $json['status'] = false;
            $json['message'] = $product_info['name'] . ' - Satışa kapalı olduğu için güncellenmedi! ';

            if ($bulk) {
                return $json;

            } else {

                echo json_encode($json);

            }

            return;

        }


        $defaults = $this->entegrasyon->getDefaults($category_setting, $manufacturer_setting, $product_setting, $code);
        $commission = $defaults['commission'];
        $product_info = $this->entegrasyon->getProductForUpdate($code, $this->entegrasyon->getProduct($product_id), $commission, $mode, HTTPS_CATALOG);


        //print_r($product_info);return;

        if ($price) {

            $product_info['sale_price'] = $price;
        }

        $product_info['model'] = $this->config->get($code . '_setting_model_prefix') . $product_info['model'];;
        $this->load->model('entegrasyon/product/' . $code);
        $product_info = $this->{"model_entegrasyon_product_" . $code}->getExtraData($product_info);


        $post_data['request_data'] = $product_info;
        $post_data['market'] = $this->model_entegrasyon_general->getMarketPlace($code);



        if ($mode) {
            $result = $this->entegrasyon->clientConnect($post_data, 'update_all', $code, $debug, false);


        } else {
            $result = $this->entegrasyon->clientConnect($post_data, 'update_basic', $code, $debug, false);

        }


        //print_r($result);return;

        if ($result['status']) {


            $marketplace_data['price'] = $product_info['sale_price'];
            $marketplace_data['commission'] = $commission;
            $this->entegrasyon->addMarketplaceProduct($product_info['product_id'], $marketplace_data, $code);

            $json['status'] = true;
            if ($mode) {
                $json['message'] = $product_info['name'] . ' tüm özellikleri güncellendi';
            } else {
                $json['message'] = $product_info['name'] . ' stok ve fiyatı güncellendi';

            }

        } else {

            /*  if($result['message']=='Fiyat stok güncellemesi için ürün bulunamadı'){

                  $this->entegrasyon->deleteMarketplaceProduct($product_id,$code);
              }*/

            $error = $this->entegrasyon->getError($product_info['product_id'], $code);
            if ($error) {
                $this->entegrasyon->updateError($product_info['product_id'], $code, 2, $result['message']);
            } else {
                $this->entegrasyon->addError($product_info['product_id'], $code, 2, $result['message']);
            }
            $json['status'] = false;
            $json['message'] = $product_info['name'] . ' - ' . $result['message'];

        }

        $json['price'] = $this->currency->format($product_info['sale_price'], $this->config->get('config_currency'));

        $logmesage = $product_info['model'] . ' Action:Update';;
        if ($mode) {
            $logmesage .= ' - Update content:' . 'Tüm Özellikler';
        } else {
            $logmesage .= ' - Update content:' . 'Stok & Fiyat';
        }
        $logmesage .= '-Stock - :' . $product_info['quantity'] . ' - Sale Price:' . $product_info['sale_price'] . ' - List Price:' . $product_info['list_price'];

        $logmesage .= '- Result:' . $json['message'];

        $this->entegrasyon->log($code, $logmesage, $bulk);

        if ($bulk) {
            return $json;

        } else {

            echo json_encode($json);

        }
    }

    public function view_product()
    {
        $product_id = $this->request->post['product_id'];
        $code = $this->request->post['code'];
        $marketplace_data = $this->entegrasyon->getMarketPlaceProductForMarket($product_id, $code);

        if ($marketplace_data['url']) {

            echo json_encode(array('status' => true, 'url' => $marketplace_data['url'], 'action' => true));

        } else {

            echo json_encode(array('status' => false, 'message' => 'Ürün pazaryerinde onayladıktan sonra görüntülenebilir. Ürününüz onaylandıya, Ayarlar sayfasından ürünlerinizi senkronize ettiğinizde ürün görüntüleme linki oluşacaktır.'));

        }


    }


    public function product_setting()
    {


        $message = '';
        $this->load->model("entegrasyon/general");
        $product_id = $this->request->get['product_id'];
        $oc_category_id = $this->request->get['category_id'];


        $code = $this->request->get['code'];

        $category_setting = $this->entegrasyon->getMarketPlaceCategory($product_id, $code);


        $data = $this->entegrasyon->getSettingData($code, 'product', $product_id);

        $this->load->model('tool/image');

        $product_setting = $this->entegrasyon->getSettingData($code, 'product', $product_id);

        if (isset($product_setting[$code . '_main_image'])) {
            $image = $this->entegrasyon->getImagesByMarketPlace($product_id, $product_setting[$code . '_main_image'], $code, HTTPS_SERVER);
            $data['image'] = $this->model_tool_image->resize($product_setting[$code . '_main_image'], 70, 70); //$image['image']['0']['url'];

        } else {
            $data['image'] = $this->model_tool_image->resize('no_image.png', 70, 70);

        }
        /*
                $post_data['request_data']=array();
                $data['address'] =$this->entegrasyon->clientConnect($post_data,'shipping_templates','ty');
                $data['ty_setting_shipping_address'] = $this->config->get('ty_setting_shipping_address');
        */
        $data['marketplace_data'] = $this->entegrasyon->getMarketPlaceProductForMarket($product_id, $code);


        $data['product_id'] = $product_id;
        $data['easy_setting_auto_update_price'] = $this->config->get('easy_setting_auto_update_price');


//print_r( $data['s_attr']);
        //     return;


        if ($code == 'ty' && !$data['marketplace_data']) {
            //$this->load->model("catalog/product");
            $product_info = $this->entegrasyon->getProduct($product_id, $code);


            if ($product_info['manufacturer_id']) {
                $manufacturer_setting = $this->entegrasyon->getMarketPlaceManufacturer($product_info['manufacturer_id'], $code);


                if (!isset($manufacturer_setting['ty_manufacturer_id'])) {


                    if ($product_info['manufacturer_id']) {
                        $manufacturer_info = $this->entegrasyon->getManufacturer($product_info['manufacturer_id']);
                        if ($manufacturer_info) {

                            $data['manufacturer_name'] = $manufacturer_info['name'];
                            $data['manufacturer_id'] = $manufacturer_info['manufacturer_id'];

                        } else {

                            $data['manufacturer_error'] = "Seçilen Marka Bulunamadı!. Ürün Gönderimi Yapılamaz";

                        }

                    } else {

                        $data['manufacturer_error'] = "Ürününüz bir marka ile ilişkilendirilmiş olmalıdır. Ürün Gönderimi Yapılamaz";
                    }

                } else {

                    $data['ty_manufacturer_id'] = $manufacturer_setting['ty_manufacturer_id'];
                }
            } else {
                $data['manufacturer_error'] = "Ürününüz bir marka ile ilişkilendirilmiş olmalıdır. Ürün Gönderimi Yapılamaz";


            }


        } else {

            $data['ty_manufacturer_id'] = 300;

        }


        if (!isset($category_setting[$code . '_category_id']) && !$data['marketplace_data']) {


            $this->load->model('catalog/category');
            $this->load->model('catalog/product');
            $product_categories = array();

            $categories = $this->model_catalog_product->getProductCategories($product_id);


            if ($categories) {


                foreach ($categories as $category_id) {
                    $category_info = $this->model_catalog_category->getCategory($category_id);

                    if ($category_info) {
                        $product_categories[] = array(
                            'category_id' => $category_info['category_id'],
                            'name' => ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name']
                        );
                    }
                }

                $message .= 'Ürün Özelliklerini Ayarlamak için Önce Kategori Eşletirmesi Yapmanız Gerekmektedir.';


            } else {

                $message .= 'Ürününüz herhangi bir kategori ile ilişkilendirilmemiş, Lütfen önce ürününüzü mevcut kategorilerinizden biri ile ilişkilendirin yada ürüne özel kategori seçin.';

            }

            $data['product_categories'] = $product_categories;

        }


        $data['message'] = $message;
        $data['product_id'] = $product_id;
        // $data['options'] = $getOptionsNames;
        $data['token_link'] = $this->token_data['token_link'];

        $data['oc_category_id'] = $oc_category_id;

        if (isset($data[$code . '_category_id'])) {


            $data['category_id'] = $data[$code . '_category_id'];


        } else if (isset($category_setting[$code . '_category_id'])) {

            $data['category_id'] = $category_setting[$code . '_category_id'];

        }


        $data['product_category_display'] = !$this->config->get($code . '_setting_product_category') ? 'hidden' : '';
        $data['product_currency_display'] = !$this->config->get($code . '_setting_product_iscurrency') ? 'hidden' : '';


        $this->response->setOutput($this->load->view('entegrasyon/product/' . $code, $data));
    }


    public function get_buttons()
    {
        $code = $this->request->get['code'];
        $product_id = $this->request->get['product_id'];
        $data['code'] = $code;
        $data['deletetable'] = true;//$code=='n11'|| $code=='gg' ? false:true;

        $data['product_id'] = $product_id;
        $data['marketplace_data'] = $this->entegrasyon->getMarketPlaceProductForMarket($product_id, $code);


        $data['token_link'] = $this->token_data['token_link'];

        $this->response->setOutput($this->load->view('entegrasyon/product/buttons', $data));


    }


    public function send_bulk()
    {
        $this->document->addStyle('view/stylesheet/entegrasyon/bootstrap4.css');
        $code = $this->request->get['code'];

        $this->load->model('entegrasyon/general');
        $marketPlaces = $this->model_entegrasyon_general->getMarketPlace($code);
        $data['code'] = $code;
        $data['market_name'] = $marketPlaces['name'];

        $data['token_link'] = $this->token_data['token_link'];

        $this->language->load('catalog/product');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('entegrasyon/product');
        $this->load->model('entegrasyon/general');
        $this->model_entegrasyon_general->loadPageRequired();


        if (isset($this->request->get['filter_name'])) {
            $filter_name = $this->request->get['filter_name'];
        } else {
            $filter_name = null;

        }
        if (isset($this->request->get['filter_in_notname'])) {
            $filter_in_notname = $this->request->get['filter_in_notname'];
        } else {
            $filter_in_notname = null;

        }
        if (isset($this->request->get['filter_manufacturer'])) {
            $filter_manufacturer = $this->request->get['filter_manufacturer'];
        } else {
            $filter_manufacturer = null;
        }

        if (isset($this->request->get['filter_category'])) {
            $filter_category = $this->request->get['filter_category'];
        } else {
            $filter_category = null;
        }

        if (isset($this->request->get['filter_stock_prefix'])) {
            $filter_stock_prefix = html_entity_decode($this->request->get['filter_stock_prefix']);
        } else {
            $filter_stock_prefix = '';
        }

        if (isset($this->request->get['filter_stock'])) {
            $filter_stock = $this->request->get['filter_stock'];
        } else {
            $filter_stock = '';
        }
        if (isset($this->request->get['filter_price_prefix'])) {
            $filter_price_prefix = html_entity_decode($this->request->get['filter_price_prefix']);
        } else {
            $filter_price_prefix = '';
        }

        if (isset($this->request->get['filter_price'])) {
            $filter_price = $this->request->get['filter_price'];
        } else {
            $filter_price = '';
        }

        if (isset($this->request->get['filter_status'])) {
            $filter_status = $this->request->get['filter_status'];
        } else {
            $filter_status = '*';
        }


        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'pd.name';
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

        if (isset($this->request->get['limit'])) {
            $limit = $this->request->get['limit'];
        } else {
            $limit = 100;
        }

        $url = '';


        if (isset($this->request->get['filter_category'])) {
            $url .= '&filter_category=' . $this->request->get['filter_category'];
        }

        if (isset($this->request->get['filter_manufacturer'])) {
            $url .= '&filter_manufacturer=' . $this->request->get['filter_manufacturer'];
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $this->token_data['token_link'], 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('catalog/product', $this->token_data['token_link'] . $url, 'SSL')
        );


        $data['products'] = array();

        $filter_data = array(

            'filter_category' => $filter_category,
            'filter_name' => $filter_name,
            'filter_in_notname' => $filter_in_notname,
            'filter_manufacturer_id' => $filter_manufacturer,
            'filter_except' => $code,
            'filter_sub_category' => true,
            'filter_stock_prefix' => $filter_stock_prefix,
            'filter_price_prefix' => $filter_price_prefix,
            'filter_stock' => $filter_stock,
            'filter_price' => $filter_price,
            'filter_status' => $filter_status,
            'sort' => $sort,
            'order' => $order,
            'start' => 0,
            'limit' => 100000
        );


        $this->load->model('tool/image');

        $product_total = 0;//$this->model_entegrasyon_product->getTotalProducts($filter_data);

        $results = $this->model_entegrasyon_product->getProducts2($filter_data);


        $data['filter_category'] = $filter_category;

        $data['category_name'] = '';

        if ($filter_category) {
            $matched_category = $this->entegrasyon->getMatchedCategory($filter_category, $code);

            if (isset($matched_category[$code . '_category_id'])) {


                $category_info = explode('|', $matched_category[$code . '_category_id']);
                $category_id = $category_info[0];
                $category_name = $category_info[1];

                $data['category_id'] = $category_id;
                $data['oc_category_id'] = $filter_category;
                $data['category_name'] = $category_name;

                if ($filter_manufacturer) {
                    $this->load->model('catalog/manufacturer');
                    $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($filter_manufacturer);
                    $data['filter_manufacturer_name'] = $manufacturer_info['name'];

                } else {

                    $data['filter_manufacturer_name'] = '';

                }

                $this->load->model('entegrasyon/category');

                $attributes = $this->model_entegrasyon_category->getAttributes($category_id, $code);


                unset($attributes['required_attributes']);


                foreach ($results as $result) {

                    //  $getOptionsNames = $this->entegrasyon->getOptionNames($result['product_id']);
                    $special = false;

                    $product_specials = $this->model_entegrasyon_product->getProductSpecials($result['product_id']);

                    foreach ($product_specials as $product_special) {
                        if (($product_special['date_start'] == '0000-00-00' || strtotime($product_special['date_start']) < time()) && ($product_special['date_end'] == '0000-00-00' || strtotime($product_special['date_end']) > time())) {
                            $special = $this->currency->format($product_special['price'], $this->config->get('config_currency'));

                            break;
                        }
                    }
                    if (is_file(DIR_IMAGE . $result['image'])) {
                        $image = $this->model_tool_image->resize($result['image'], 40, 40);
                    } else {
                        $image = $this->model_tool_image->resize('no_image.png', 40, 40);
                    }
                    if($result['image']){


                        $data['products'][] = array(
                            'name' => $result['name'],
                            'product_id' => $result['product_id'],
                            'manufacturer' => $result['manufacturer'],
                            'image' => $image,
                            //'specs' => $attributes,
                            'model' => $result['model'],
                            'price' => $this->currency->format($result['price'], $this->config->get('config_currency')),
                            'special' => $special,
                            'quantity' => $result['quantity'],
                            // 'options' => $getOptionsNames
                        );
                    }   }
            }

        }
        $this->load->model('catalog/category');

        $results = $this->model_catalog_category->getCategories(array('sort'=>'name'));

        foreach ($results as $result) {
            if($result['name']) {
                $data['categories'][] = array(
                    'category_id' => $result['category_id'],
                    'name' => $result['name'],
                );
            }
        }

        $this->load->model('catalog/manufacturer');

        $results = $this->model_catalog_manufacturer->getManufacturers();

        foreach ($results as $result) {
            $data['manufacturers'][] = array(
                'manufacturer_id' => $result['manufacturer_id'],
                'name' => $result['name'],
            );
        }

        $data['product_total'] = count($data['products']);

        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_list'] = $this->language->get('text_list');

        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_no_results'] = $this->language->get('text_no_results');
        $data['text_confirm'] = $this->language->get('text_confirm');

        $data['column_image'] = $this->language->get('column_image');
        $data['column_name'] = $this->language->get('column_name');
        $data['column_model'] = $this->language->get('column_model');
        $data['column_price'] = $this->language->get('column_price');
        $data['column_quantity'] = $this->language->get('column_quantity');
        $data['column_status'] = $this->language->get('column_status');
        $data['column_action'] = $this->language->get('column_action');

        $data['entry_name'] = $this->language->get('entry_name');
        $data['entry_model'] = $this->language->get('entry_model');
        $data['entry_price'] = $this->language->get('entry_price');
        $data['entry_quantity'] = $this->language->get('entry_quantity');
        $data['entry_status'] = $this->language->get('entry_status');

        $data['button_copy'] = $this->language->get('button_copy');
        $data['button_add'] = $this->language->get('button_add');
        $data['button_edit'] = $this->language->get('button_edit');
        $data['button_delete'] = $this->language->get('button_delete');
        $data['button_filter'] = $this->language->get('button_filter');


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


        if (isset($this->request->get['filter_manufacturer'])) {
            $url .= '&filter_manufacturer=' . $this->request->get['filter_manufacturer'];
        }


        if (isset($this->request->get['filter_quantity'])) {
            $url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
        }
        if (isset($this->request->get['filter_stock_prefix'])) {
            $url .= '&filter_stock_prefix=' . $this->request->get['filter_stock_prefix'];
        }

        if (isset($this->request->get['filter_stock'])) {
            $url .= '&filter_stock=' . $this->request->get['filter_stock'];
        }
        if (isset($this->request->get['filter_price_prefix'])) {
            $url .= '&filter_price_prefix=' . $this->request->get['filter_price_prefix'];
        }

        if (isset($this->request->get['filter_price'])) {
            $url .= '&filter_price=' . $this->request->get['filter_price'];
        }


        if (isset($this->request->get['filter_status'])) {
            $url .= '&filter_status=' . $this->request->get['filter_status'];
        }

        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        if (isset($this->request->get['filter_category'])) {
            $url .= '&filter_category=' . $this->request->get['filter_category'];
        }


        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $pagination = new Pagination();
        $pagination->total = $product_total;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->url->link('entegrasyon/product/send_bulk', $this->token_data['token_link'] . $url . '&code=' . $code . '&page={page}', 'SSL');

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($product_total - $limit)) ? $product_total : ((($page - 1) * $limit) + $limit), $product_total, ceil($product_total / $limit));

        $data['filter_category'] = $filter_category;
        $data['filter_manufacturer'] = $filter_manufacturer;
        $data['filter_name'] = $filter_name;
        $data['filter_in_notname'] = $filter_in_notname;
        $data['filter_stock_prefix'] = $filter_stock_prefix;
        $data['filter_stock'] = $filter_stock;
        $data['filter_price_prefix'] = $filter_price_prefix;
        $data['filter_price'] = $filter_price;
        $data['filter_status'] = $filter_status;


        $data['code'] = $code;
        $data['sort'] = $sort;
        $data['order'] = $order;

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');


        $this->response->setOutput($this->load->view('entegrasyon/send_bulk', $data));
    }




    public function bulk_actions()
    {



        $this->load->model('entegrasyon/product');
        $this->load->model('entegrasyon/general');


        $product_total = $this->model_entegrasyon_product->getTotalProductsNoFilter();
        $code = $this->request->get['marketplace'];
        //$products = $this->input->get['product_list'];
        $list_type = $this->request->get['list_type'];
        if ($list_type == 'selected' or $list_type == 'all_filter') {
            $product_list = isset($this->request->get['product_list'])?explode(',', $this->request->get['product_list']):'';
            $data['product_list'] = isset($this->request->get['product_list'])?$this->request->get['product_list']:'';
            $data['filters'] = isset($this->request->get['filters'])?$this->request->get['filters']:'';
            $data_filters=explode(',',$data['filters']);
            // if ($data_filters[0] ||$data_filters[1] ||$data_filters[2] ||$data_filters[3] ||$data_filters[4] ||$data_filters[5] ||$data_filters[6] ||$data_filters[7] ){


            $data_filters['6'] = str_replace('&gt;', '>',   $data_filters['6']);
            $data_filters['6'] = str_replace('&lt;', '<',   $data_filters['6']);

            $filter_data = array(
                'filter_category' => $data_filters['5'],
                'filter_manufacturer' => $data_filters['4'],
                'filter_marketplace' => $data_filters['0'],
                'filter_marketplace_do' => $data_filters['1'],
                'filter_name' => $data_filters['3'],
                'filter_model' => $data_filters['2'],
                'filter_status' => $data_filters['8'],
                'filter_stock_prefix' => $data_filters['6'],
                'filter_stock' => $data_filters['7'],
                'sort' => "pd.name",
                'order' => "ASC"
            );
            $data['filter_data'] = serialize($filter_data);
            $product_total = count($this->model_entegrasyon_product->getProducts($filter_data,"for_bulk"));

            /*  }else{
                  $data['filter_data']=false;
                  $product_total = 0;
              }*/

            $data['total'] = $data['product_list']?substr_count($data['product_list'], ",") + 1:$product_total;

        } else {
            $data['total'] = $product_total;

            $product_list = $this->model_entegrasyon_product->getMarketPlaceProducts($code);
            $product_passive = $this->model_entegrasyon_product->getPassiveProducts($code);
            $product_close = $this->model_entegrasyon_product->getCloseMarketPlaceProducts($code);
            $data['product_list'] = '';
            $data['total_passive'] = $product_total - (count($product_list) + count($product_close));
            $data['total_close'] = count($product_close);
        }


        $marketPlace = $this->model_entegrasyon_general->getMarketPlace($code);

        $data['marketplace'] = $marketPlace['name'];
        $data['list_type'] = $list_type;
        $data['code'] = $code;
        $data['delete_permission'] = $code == 'n11' || $code == 'gg' ? true : false;
        $data['commission'] = '';
        $data['value'] = '';
        $data['total_active'] = $product_list? count($product_list):"";
        $data['token_link'] = $this->token_data['token_link'];
        $this->response->setOutput($this->load->view('entegrasyon/product/bulk_actions', $data));

    }

    //testleri devam etmekte stabil olmayabilir.. bulk_action_progress2
    public function bulk_action_progress2()
    {

        error_reporting(0);

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }
        $url = '';

        $this->load->model('entegrasyon/product');

        $product_list = array();
        $action = $this->request->get['action'];


        $is_filter=0;
        $filter_data = 0;
        if (!$this->request->get['product_list']){
            $filter_datas = $this->request->get['filter_data'];
            $filter_datas = str_replace('&quot', '"', $filter_datas);
            $filter_datas = str_replace('";', '"', $filter_datas);
            $filter_datas = str_replace(';;', ';', $filter_datas);
            $filter_datas = str_replace('&gt;', '>',  $filter_datas);
            $filter_datas = str_replace('&lt;', '<',   $filter_datas);
            $filter_data = unserialize($filter_datas);
            $is_filter=1;

        }
        $code = $this->request->get['code'];

        $list_type = $this->request->get['list_type'];


        if ($list_type == 'selected') {
            $product_list = !$is_filter?explode(',', $this->request->get['product_list']): $this->model_entegrasyon_product->getProducts($filter_data);
            $data['product_list'] = !$is_filter?$this->request->get['product_list']: "";

        } else {

            $is_filter=0;

            if ($action == 'addproduct') {

                if (!$this->request->get['next']){
                    $product_list = $this->model_entegrasyon_product->getPassiveProducts($code);
                    $easy_product_list = array(
                        'easy_product_list' => serialize($product_list)
                    );
                    $this->load->model('setting/setting');
                    $this->model_setting_setting->editSetting('easy_product',$easy_product_list);
                }else{
                    $product_list = unserialize($this->config->get('easy_update_list'));
                }


            } elseif ($action == 'open_for_sale') {
                if (!$this->request->get['next']){
                    $product_list = $this->model_entegrasyon_product->getCloseMarketPlaceProducts($code);
                    $easy_product_list = array(
                        'easy_product_list' => serialize($product_list)
                    );
                    $this->load->model('setting/setting');
                    $this->model_setting_setting->editSetting('easy_product',$easy_product_list);
                }else{
                    $product_list = unserialize($this->config->get('easy_update_list'));
                }
                // $product_list = $this->model_entegrasyon_product->getCloseMarketPlaceProducts($code);

            } else {

                //  $product_list = $this->model_entegrasyon_product->getMarketPlaceProducts($code);
                if (!$this->request->get['next']){
                    $product_list = $this->model_entegrasyon_product->getMarketPlaceProducts($code);
                    $easy_product_list = array(
                        'easy_product_list' => serialize($product_list)
                    );
                    $this->load->model('setting/setting');
                    $this->model_setting_setting->editSetting('easy_product',$easy_product_list);
                }else{
                    $product_list = unserialize($this->config->get('easy_update_list'));
                }

            }
            $data['product_list'] = '';

        }
        $json['total'] =$is_filter? $this->request->get['total']:count($product_list);

        if ($is_filter){
            $product_id = $product_list[$page - 1]['product_id'];

        }else{
            $product_id = $list_type != 'selected' ? $product_list[$page - 1]['product_id'] : $product_list[$page - 1];

        }
        $product_info = $this->model_entegrasyon_product->getProduct($product_id);


        $result = $this->{$action}($product_id, $code, true);


        $json['update_status'] = $result['status'];
        if (isset($product_info['name'])) {
            $json['message'] = $page . '-' . $product_info['name'] . ' - ' . $result['message'];
        } else {
            $json['message'] = $page . '-' . $result['message'];

        }
        //toplu işlem bilgi kısmı ürün adı -1 bul-..

        //$total = $is_filter?count($this->model_entegrasyon_product->getProducts($filter_data,"for_bulk")):count($product_list);  //Toplam ürünü burada kontrol etme total sayısını kontrol et
        $total = $this->request->get['total'];

        $page++;
        if ($is_filter){
            $url = 'index.php?route=entegrasyon/product/bulk_action_progress&code=' . $code . '&page=' . $page .  '&total='. $total.'&list_type=' . $list_type . '&action=' . $action . '&filter_data=' . $filter_datas . '&product_list=' . $data['product_list'] . '&' . $this->token_data['token_link'];

        }else{
            $url = 'index.php?route=entegrasyon/product/bulk_action_progress&code=' . $code . '&page=' . $page . '&total='. $total.'&list_type=' . $list_type . '&action=' . $action . '&product_list=' . $data['product_list'] . '&' . $this->token_data['token_link'];

        }



        $json['current'] = $page - 1;


        if ($page <= $total) {

            $json['status'] = true;
            $json['next'] = $url.'&next=next';

        } else {
            $json['status'] = false;
            //$json['message']='Tamamlandı';
        }


        echo json_encode($json);
    }

    public function bulk_action_progress()
    {

        error_reporting(0);

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }
        $url = '';

        $this->load->model('entegrasyon/product');

        $product_list = array();
        $action = $this->request->get['action'];

        $is_filter=0;
        $filter_data = 0;
        if (!$this->request->get['product_list']){
            $filter_datas = $this->request->get['filter_data'];
            $filter_datas = str_replace('&quot', '"', $filter_datas);
            $filter_datas = str_replace('";', '"', $filter_datas);
            $filter_datas = str_replace(';;', ';', $filter_datas);
            $filter_datas = str_replace('&gt;', '>',  $filter_datas);
            $filter_datas = str_replace('&lt;', '<',   $filter_datas);
            $filter_data = unserialize($filter_datas);
            $is_filter=1;

        }
        $code = $this->request->get['code'];
        $list_type = $this->request->get['list_type'];

        if ($list_type == 'selected') {
            $product_list = !$is_filter?explode(',', $this->request->get['product_list']): $this->model_entegrasyon_product->getProducts($filter_data);
            $data['product_list'] = !$is_filter?$this->request->get['product_list']: "";


        } else {

            $is_filter=0;

            if ($action == 'addproduct') {

                $product_list = $this->model_entegrasyon_product->getPassiveProducts($code);


            } elseif ($action == 'open_for_sale') {

                $product_list = $this->model_entegrasyon_product->getCloseMarketPlaceProducts($code);

            } else {

                $product_list = $this->model_entegrasyon_product->getMarketPlaceProducts($code);


            }
            $data['product_list'] = '';

        }
        $json['total'] =$is_filter?count($this->model_entegrasyon_product->getProducts($filter_data)):count($product_list);

        if ($is_filter){
            $product_id = $product_list[$page - 1]['product_id'];

        }else{
            $product_id = $list_type != 'selected' ? $product_list[$page - 1]['product_id'] : $product_list[$page - 1];

        }
        $product_info = $this->model_entegrasyon_product->getProduct($product_id);


        $result = $this->{$action}($product_id, $code, true);


        $json['update_status'] = $result['status'];
        if (isset($product_info['name'])) {
            $json['message'] = $page . '-' . $product_info['name'] . ' - ' . $result['message'];
        } else {
            $json['message'] = $page . '-' . $result['message'];

        }
        //toplu işlem bilgi kısmı ürün adı -1 bul-..
        $total = $is_filter?$this->model_entegrasyon_product->getTotalProducts($filter_data):count($product_list);

        $page++;
        if ($is_filter){
            $url = 'index.php?route=entegrasyon/product/bulk_action_progress&code=' . $code . '&page=' . $page . '&list_type=' . $list_type . '&action=' . $action . '&filter_data=' . $filter_datas . '&product_list=' . $data['product_list'] . '&' . $this->token_data['token_link'];

        }else{
            $url = 'index.php?route=entegrasyon/product/bulk_action_progress&code=' . $code . '&page=' . $page . '&list_type=' . $list_type . '&action=' . $action . '&product_list=' . $data['product_list'] . '&' . $this->token_data['token_link'];

        }


        $json['current'] = $page - 1;


        if ($page <= $total) {

            $json['status'] = true;
            $json['next'] = $url;

        } else {
            $json['status'] = false;
            //$json['message']='Tamamlandı';
        }


        echo json_encode($json);
    }


    public function update_bulk()
    {

        $marketPlaces = $this->entegrasyon->getMarkets();

        $code = $this->request->get['code'];
        $commission = $this->request->get['commission'];
        $controller = $this->request->get['controller'];
        $value = $this->request->get[$controller];
        $total = $this->request->get['total'];

        //$this->load->model('entegrasyon/product');
        /*$filter_data= array(
             'filter_marketplace'=>$code,
             'filter_'.$controller=>$value
         );


         $getProducts=$this->model_entegrasyon_product->getProducts($filter_data);
        */


        $data['marketplace'] = $marketPlaces[$code];
        $data['page_type'] = $controller == 'category' ? 'Kategori' : 'Marka';
        $data['controller'] = $controller;
        $data['code'] = $code;
        $data['commission'] = $commission;
        $data['value'] = $value;
        $data['total'] = $total;
        $data['token_link'] = $this->token_data['token_link'];

        $this->response->setOutput($this->load->view('entegrasyon/product/update_bulk', $data));

    }

    public function update_bulk_price()
    {

        $this->load->model('entegrasyon/general');

        if (!$this->model_entegrasyon_general->checkPermission()) {

            echo json_encode(array('status' => false, 'message' => 'Gerçek Mağaza bilgileri kullanıldığı için Demo versiyonda fiyat güncellemesine izin verilmemektedir.'));
            return;

        }

        $json = array();
        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }


        $code = $this->request->get['code'];
        $controller = $this->request->get['controller'];
        $value = $this->request->get[$controller];
        $commission = $this->request->get['commission'];
        $total = $this->request->get['total'];

        $this->load->model('entegrasyon/product');
        $filter_data = array(
            'filter_marketplace' => $code,
            'filter_' . $controller => $value,
            'start' => ($page - 1),
            'limit' => 1
        );

        $product = $this->model_entegrasyon_product->getProducts($filter_data)[0];
        $product_info = $this->entegrasyon->getProductForUpdate($code, $this->entegrasyon->getProduct($product['product_id']), $commission);

        $product_info['model'] = $this->config->get($code . '_setting_model_prefix') . $product_info['model'];
        //  $result= $this->{$code}->updateBasic($product_info);
        $post_data['request_data'] = $product_info;
        $post_data['market'] = $this->model_entegrasyon_general->getMarketPlace('ty');
        $result = $this->entegrasyon->clientConnect($post_data, 'update_basic', $code);

        if ($result['status']) {
            $marketplace_data = unserialize($product_info[$code]);
            if (isset($marketplace_data['url'])) {
                $url = $marketplace_data['url'];
            } else {
                $url = '';
            }
            //$data=array('commission'=>$commission,'product_id'=>$marketplace_data['product_id'],'price'=>$product_info['sale_price'],'url'=>$url);

            $marketplace_data = $this->entegrasyon->getMarketPlaceProductForMarket($product_info['product_id'], $code);

            $marketplace_data['price'] = $product_info['sale_price'];
            $marketplace_data['commission'] = $commission;
            $this->entegrasyon->addMarketplaceProduct($product_info['product_id'], $marketplace_data, $code);

            $json['update_status'] = true;
            $json['message'] = $product['name'] . ' güncellendi';

        } else {
            $error = $this->entegrasyon->getError($product_info['product_id'], $code);
            if ($error) {
                $this->entegrasyon->updateError($product_info['product_id'], $code, 2, $result['message']);
            } else {
                $this->entegrasyon->addError($product_info['product_id'], $code, 2, $result['message']);
            }

            $json['update_status'] = false;
            $json['message'] = $product['name'] . ' - ' . $result['message'];
        }

        $logmesage = $product_info['model'] . ' Action:Update bulk price';;

        $logmesage .= '-Stock - :' . $product_info['quantity'] . ' - Sale Price:' . $product_info['sale_price'] . ' - List Price:' . $product_info['list_price'];

        $logmesage .= '- Result:' . $json['message'];

        $this->entegrasyon->log($code, $logmesage, true);


        $page++;
        $url = 'index.php?route=entegrasyon/product/update_bulk_price&page=' . $page . '&total=' . $total . '&code=' . $code . '&commission=' . $commission . '&controller=' . $controller . '&' . $controller . '=' . $value . '&' . $this->token_data['token_link'];

        $json['current'] = $page - 1;

        if ($page <= $total) {

            $json['status'] = true;
            $json['next'] = $url;

        } else {
            $json['status'] = false;
            //$json['message']='Tamamlandı';
        }

        echo json_encode($json);

    }


    public function product_match()
    {
        $code = $this->request->get['code'];
        $product_id = $this->request->post['product_id'];
        $marketplaceproductid = $this->request->post['barcode'];


        $this->load->model('entegrasyon/product/' . $code);

        $marketplace_product_info = $this->{"model_entegrasyon_product_" . $code}->getProduct($marketplaceproductid);


        if ($code == 'n11') {

            if (isset($marketplace_product_info['status'])) {

                echo json_encode(array('status' => false, 'message' => 'Ürün Bulunamadı'));
                return;
            }
        } elseif ($code == 'ty') {
            if (!$marketplace_product_info['match_status']) {

                echo json_encode(array('status' => false, 'message' => 'Ürün Bulunamadı'));
                return;
            }
        }

        $oc_product_info = $this->entegrasyon->getProduct($product_id);


        $product_info = $this->entegrasyon->getProductByModel($oc_product_info['model'], $oc_product_info['model'], $oc_product_info['model'], $this->config->get($code . '_setting_model_prefix'), $this->config->get($code . '_setting_barkod_place'), $oc_product_info['model']);


        if ($product_info) {
            $oc_price = $product_info['special'] ? $product_info['special'] : $product_info['price'];
            $oc_price = $this->tax->calculate($oc_price, $product_info['tax_class_id'], true);

            if ($oc_price < $marketplace_product_info['sale_price']) {
                if ((int)$oc_price) {
                    $commission = (($marketplace_product_info['sale_price'] - $oc_price) * 100) / $oc_price;
                }

            } else {

                $commission = 0;
            }


            $url = $this->entegrasyon->getMarketPlaceUrl($code, $marketplace_product_info['market_id']);

            $data = array('commission' => $commission, 'sale_status' => $marketplace_product_info['sale_status'], 'approval_status' => $marketplace_product_info['approval_status'], 'barcode' => $marketplace_product_info['barcode'], 'product_id' => $marketplace_product_info['market_id'], 'price' => number_format($marketplace_product_info['sale_price'], 2), 'url' => $url);



            if ($code == 'n11') {
                $data['stock_id'] = $marketplace_product_info['stock_id'];
            }

            $this->entegrasyon->addMarketplaceProduct($product_info['product_id'], $data, $code);

            $this->load->controller('entegrasyon/genel/save_setting', array('code' => $code, 'primary_id' => $product_info['product_id'], 'name' => $code . '_product_code', 'value' => $marketplace_product_info['barcode'], 'controller' => 'product'));


            //  echo json_encode(array('status' => true, 'message' => 'Başarılı'));


        }


        //$this->load->model('entegrasyon/product');

        // $this->model_entegrasyon_product->getMarketPlaceProductsForMatch($product_id,$code,$product_model);

        //Test
        // $query = $this->db->query("select * from " . DB_PREFIX . "es_product_to_marketplace where product_id='" . $product_id . "'");
        // print_r(unserialize($query->row['' . $code . '']));
        //Test


    }

    public function product_match_form()
    {
        $data['token_link'] = $this->token_data['token_link'];
        $data['barcode'] = str_replace("**", " ", $this->request->get['barcode']);
        $data['code'] = $this->request->get['code'];
        $name_ham = $this->request->get['get_name'];
        $name2 = str_replace("(", "", $name_ham);
        $name3 = str_replace(")", "", $name2);
        $name = str_replace(" ", "+", $name3);

        //  print_r($name);
        // return;
        $arr = explode('**', trim($name));

        if (!isset($arr[1])) {
            $arr[1] = '';

        }
        if (!isset($arr[2])) {
            $arr[2] = '';
        }
        if (!isset($arr[3])) {
            $arr[3] = '';
        }


        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE pd.name LIKE '%" . $arr[0] . "%' AND  pd.name LIKE '%" . $arr[1] . "%' AND  pd.name LIKE '%" . $arr[2] . "%'  AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

        $products = $query->rows;


        if (!isset($products[0])) {

            $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE pd.name LIKE '%" . $arr[0] . "%' AND  pd.name LIKE '%" . $arr[1] . "%'  AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
            $products = $query->rows;
            if (!isset($products[0])) {


                $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE pd.name LIKE '%" . $arr[0] . "%'   AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
                $products = $query->rows;
            }

        } elseif (isset($products[3])) {

            $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE pd.name LIKE '%" . $arr[0] . "%' AND  pd.name LIKE '%" . $arr[1] . "%' AND  pd.name LIKE '%" . $arr[2] . "%'  AND  pd.name LIKE '%" . $arr[3] . "%' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
            $products = $query->rows;

        }

        $product_datas = array();

        foreach ($products as $product) {
            $img = $this->model_tool_image->resize($product['image'], 50, 50);
            $product_datas[] = array(
                'name' => $product['name'],
                'model' => $product['model'],
                'image' => $img,
                'product_id' => $product['product_id']
            );
        }

        if (isset($product_datas[0])) {
            $data['sample'] = true;
        } else {
            $data['sample'] = false;
        }

        $data['sample_products'] = $product_datas;


        $this->response->setOutput($this->load->view('entegrasyon/product/product_match/product_match', $data));


    }

    public function product_match_search()
    {
        $data['token_link'] = $this->token_data['token_link'];


        $gelen = $this->request->get['gelen'];
        $result = str_replace("**", " ", $gelen);
        $model = $this->request->get['model'];
        $result2 = str_replace("**", " ", $model);
        $data['gelen'] = $result;
        $data['model'] = $result2;
        $data['code'] = $this->request->get['code'];;
        $this->load->model('tool/image');


        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE pd.name LIKE '%" . $result . "%' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

        $products = $query->rows;
        $product_datas = array();

        foreach ($products as $product) {
            $img = $this->model_tool_image->resize($product['image'], 50, 50);
            $product_datas[] = array(
                'name' => $product['name'],
                'model' => $product['model'],
                'image' => $img,
                'product_id' => $product['product_id']
            );
        }

        $data['product_datas'] = $product_datas;

        $data['barcode'] = $this->request->get['barcode'];


        $this->response->setOutput($this->load->view('entegrasyon/product/product_match/search', $data));


    }


    public function download_product_bulk()
    {


        error_reporting(0);

        $this->load->model('entegrasyon/general');

        if (!$this->model_entegrasyon_general->checkPermission()) {

            echo json_encode(array('status' => false, 'message' => 'Gerçek Mağaza bilgileri kullanıldığı için Demo versiyonda ürün aktarılmasına izin verilmemektedir.'));
            return;

        }


        // if(!isset($this->request->post['product_id']))return;
        $code = $this->request->get['code'];


        $list = $this->request->post['list'];


        $product_id = $this->request->get['product_id'];
        $current_key = array_search($product_id, $list);
        $category_id = isset($this->request->get['category_id']) ? $this->request->get['category_id'] : 0;
        $manufacturer_id = isset($this->request->get['manufacturer_id']) ? $this->request->get['manufacturer_id'] : 0;


        $this->load->model('entegrasyon/product/' . $code);


        $marketplace_product_info = $this->{"model_entegrasyon_product_" . $code}->getMarketPlaceProduct($product_id, $category_id, $manufacturer_id);


        if (!$marketplace_product_info['status']) {
            echo json_encode($marketplace_product_info);
            return;
        }

        $product_info = $this->entegrasyon->getProductByModel($marketplace_product_info['product_data']['model'], $marketplace_product_info['product_data']['model']);

        if ($product_info) {

            $model = $this->startsWith($this->config->get($code . '_setting_model_prefix'), $product_info['model']);
            $product_info['model'] = $model;

        }


        if (!$product_info) {


            $product_id = $this->entegrasyon->addProduct($marketplace_product_info['product_data']);

            if ($product_id) {

                $this->entegrasyon->addMarketplaceProduct($product_id, $marketplace_product_info['marketplace_product_data'], $code);
            }
            if ($current_key + 1 < count($list)) {


                echo json_encode(array('status' => true, 'next' => true, 'item' => $list[$current_key + 1], 'list' => $list, 'current' => $current_key + 1, 'message' => "Ürün Başarıyla Mağazanıza Eklendi"));


            } else {

                echo json_encode(array('status' => true, 'next' => false, 'item' => $list[$current_key], 'list' => $list, 'current' => $current_key + 1, 'message' => "Ürün Başarıyla Mağazanıza Eklendi!"));

            }

        } else {

            if ($current_key + 1 < count($list)) {


                echo json_encode(array('status' => false, 'next' => true, 'item' => $list[$current_key + 1], 'list' => $list, 'current' => $current_key + 1, 'message' => "Ürün Mazağanızda Olduğu İçin Eklenemedi."));


            } else {

                echo json_encode(array('status' => false, 'next' => false, 'item' => $list[$current_key], 'list' => $list, 'current' => $current_key + 1, 'message' => "Ürün Mazağanızda Olduğu İçin Eklenemedi."));

            }

        }


    }

    public function get_marketplace_products()
    {

        $code = $this->request->get['code'];
        $this->load->model("entegrasyon/product/" . $code);


        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }
        $url = '';

        if ($code == 'cs') {
            $limit = 55;
        } else {
            $limit = 100;
        }

        $debug = false;
        if (isset($this->request->get['debug'])) {
            $debug = true;

        }


        $this->document->addStyle('view/stylesheet/entegrasyon/bootstrap4.css');
        $this->load->model('entegrasyon/general');
        $marketplace_info = $this->model_entegrasyon_general->getMarketPlace($code);
        $data['marketplace_info'] = $marketplace_info;
        $this->document->setTitle($marketplace_info['name'] . ' Mağazadan Ürün Aktarma');

        $products = $this->{"model_entegrasyon_product_" . $code}->getProducts(array('itemcount' => $limit, 'page' => $page - 1), $debug);

        //    print_r($products);return;

        $data['products'] = array();
        foreach ($products['products'] as $product) {

            $product['status'] = $product['model'] ? $this->entegrasyon->is_product_exists($product['model']) : false;
            if (!$product['status']) {
                $product['status'] = $product['model'] ? $this->entegrasyon->is_product_exists_in_product_to_marketplace($code, $product) : false;
            }

            if (!$product['status']) {
                $product['status'] = $product['product_code'] ? $this->entegrasyon->is_product_exists($product['product_code']) : false;
            }

            $data['products'][] = $product;
        }

        $data['status'] = $products['status'];
        $data['message'] = $products['message'];
        $product_total = $products['total'];

        $data['code'] = $code;
        $data['easy_visibility'] = $this->config->get('easy_visibility') ? '' : 'hidden';

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('entegrasyon/dashboard', $this->token_data['token_link'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => 'Ürünler',
            'href' => $this->url->link('entegrasyon/product', $this->token_data['token_link'] . $url, true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $marketplace_info['name'] . ' Ürün Aktarma',
            'href' => $this->url->link('entegrasyon/product/get_marketplace_products', $this->token_data['token_link'] . $url, true)
        );


        $pagination = new Pagination();
        $pagination->total = $product_total;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->url->link('entegrasyon/product/get_marketplace_products', $this->token_data['token_link'] . $url . '&code=' . $code . '&page={page}', true);

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($product_total - $limit)) ? $product_total : ((($page - 1) * $limit) + $limit), $product_total, ceil($product_total / $limit));

        $this->model_entegrasyon_general->loadPageRequired();
        $data['token_link'] = $this->token_data['token_link'];

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('entegrasyon/product_download_list', $data));

    }

    public function get_variants()
    {
        $this->load->model('entegrasyon/product');

        $data['token_link'] = $this->token_data['token_link'];
        $code = $this->request->get['code'];
        $product_id = $this->request->get['product_id'];
        $data['product_id'] = $product_id;
        $data['code'] = $code;
        $product_info= $this->model_entegrasyon_product->getProduct($product_id);


        $data['main_model']=$product_info['model'];
        $data['main_price']=$product_info['price'];


        $this->document->addStyle('view/stylesheet/entegrasyon/bootstrap4.css');


        $data['no_sended_variants'] = array();
        if ($this->entegrasyon->isVarianterProduct($product_id)){
            $product_variants = $this->entegrasyon->getPoductVariants($product_id);
            foreach ($product_variants as $product_variant){
                $filter_data = array(


                    'filter_barcode' => $product_variant['barcode'],
                    'code' => $code

                );

                if(!$this->model_entegrasyon_product->getMarketPlaceProductsForDownloadTotal($filter_data) ){
                    if (is_file(DIR_IMAGE . $product_variant['image'])) {
                        $image = $this->model_tool_image->resize($product_variant['image'], 70, 70);
                    } else {
                        $image = HTTPS_CATALOG.'image/'.$product_info['image'];
                    }
                    $data['no_sended_variants'][] = array(

                        'variant_id' => $product_variant['variant_id'],
                        'name' => $product_variant['name'],
                        'image' => $image,
                        'image_core' => $product_info['image'],
                        'barcode' => $product_variant['barcode'],
                        'model' => $product_variant['model'],
                        'quantity' => $product_variant['quantity'],
                        'price' => $product_info['price'] + $product_variant['price'],
                        'price_plus' => $product_variant['price']


                    );
                }
            }
        }

        $filter_data = array(

            'filter_oc_product_id' => $product_id,
            'code' => $code

        );

        $products = $this->model_entegrasyon_product->getMarketPlaceProductsForDownload($filter_data);
        $data['products'] = array();
        foreach ($products as $product) {
            // print_r( unserialize($product['custom_data']));

            if ($code == 'cs' || $code == "n11") {
                $product['image'] = unserialize($product['custom_data'])['images'][0];

            } elseif ($code == "ty") {
                $product['image'] = unserialize($product['custom_data'])['images'][0]['url'];

            } elseif ($code == "gg") {
                if (isset(unserialize($product['custom_data'])['product']['photos']['photo'])) {
                    if (isset(unserialize($product['custom_data'])['product']['photos']['photo'][0])) {
                        $product['image'] = unserialize($product['custom_data'])['product']['photos']['photo'][0]['url'];

                    } else {
                        $product['image'] = unserialize($product['custom_data'])['product']['photos']['photo']['url'];

                    }
                }


            }

            $product['status'] = $product['model'] ? $this->entegrasyon->is_product_exists($product['model']) : false;
            if (!$product['status']) {
                $product['status'] = $product['model'] ? $this->entegrasyon->is_product_exists_in_product_to_marketplace($code, $product) : false;
            }

            if (!$product['status']) {
                $product['status'] = $product['marketplace_product_id'] ? $this->entegrasyon->is_product_exists($product['marketplace_product_id']) : false;
            }

            $data['products'][] = $product;
        } //return;

        $this->response->setOutput($this->load->view('entegrasyon/product/get_variants', $data));


    }

    public function get_marketplace_products_new()
    {

        $code = $this->request->get['code'];
        $this->load->model('tool/image');

        $limit = $this->config->get('config_limit_admin');

        $this->document->addStyle('view/stylesheet/entegrasyon/bootstrap4.css');
        $this->load->model("entegrasyon/product/" . $code);

        if (isset($this->request->get['filter_stock_prefix'])) {
            $filter_stock_prefix = html_entity_decode($this->request->get['filter_stock_prefix']);
        } else {
            $filter_stock_prefix = '';
        }

        if (isset($this->request->get['filter_stock'])) {
            $filter_stock = $this->request->get['filter_stock'];
        } else {
            $filter_stock = '';
        }

        if (isset($this->request->get['filter_model'])) {
            $filter_model = $this->request->get['filter_model'];
        } else {
            $filter_model = '';
        }
        if (isset($this->request->get['filter_barcode'])) {
            $filter_barcode = $this->request->get['filter_barcode'];
        } else {
            $filter_barcode = '';
        }
        if (isset($this->request->get['filter_marketplace_product_id'])) {
            $filter_marketplace_product_id = $this->request->get['filter_marketplace_product_id'];
        } else {
            $filter_marketplace_product_id = '';
        }

        if (isset($this->request->get['filter_name'])) {
            $filter_name = $this->request->get['filter_name'];
        } else {
            $filter_name = '';
        }

        if (isset($this->request->get['filter_marketplace_do'])) {
            $filter_marketplace_do = $this->request->get['filter_marketplace_do'];
        } else {
            $filter_marketplace_do = '*';
        }

        if (isset($this->request->get['filter_match'])) {
            $filter_match = $this->request->get['filter_match'];
        } else {
            $filter_match = '*';
        }


        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $debug = false;
        if (isset($this->request->get['debug'])) {
            $debug = true;

        }


        $filter_data = array(

            'filter_name' => $filter_name,
            'filter_model' => $filter_model,
            'filter_marketplace_product_id' => $filter_marketplace_product_id,
            'filter_barcode' => $filter_barcode,
            'filter_stock_prefix' => $filter_stock_prefix,
            'filter_stock' => $filter_stock,
            'filter_marketplace_do' => $filter_marketplace_do,
            'filter_match' => $filter_match,
            'code' => $code,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        );


        $this->document->addStyle('view/stylesheet/entegrasyon/bootstrap4.css');
        $this->load->model('entegrasyon/general');
        $this->load->model('entegrasyon/product');
        $marketplace_info = $this->model_entegrasyon_general->getMarketPlace($code);
        $data['marketplace_info'] = $marketplace_info;
        $this->document->setTitle($marketplace_info['name'] . ' Mağazadan Ürün Aktarma');

        $products = $this->model_entegrasyon_product->getMarketPlaceProductsForDownload($filter_data);
        $product_total = $this->model_entegrasyon_product->getMarketPlaceProductsForDownloadTotal($filter_data);

        $data['products'] = array();
        foreach ($products as $product) {
            // print_r( unserialize($product['custom_data']));

            if ($code == 'cs' || $code == "n11") {
                $product['image'] = unserialize($product['custom_data'])['images'][0];

            } elseif ($code == "ty") {
                if (isset(unserialize($product['custom_data'])['images'][0])){
                    $product['image'] = unserialize($product['custom_data'])['images'][0]['url'];
                }else{
                    $product['image'] = $this->model_tool_image->resize('no_image.png', 100, 100);

                }

            } elseif ($code == "gg") {
                if (isset(unserialize($product['custom_data'])['product']['photos']['photo'])) {
                    if (isset(unserialize($product['custom_data'])['product']['photos']['photo'][0])) {
                        $product['image'] = unserialize($product['custom_data'])['product']['photos']['photo'][0]['url'];

                    } else {
                        $product['image'] = unserialize($product['custom_data'])['product']['photos']['photo']['url'];

                    }
                }else{
                    $product['image'] = $this->model_tool_image->resize('no_image.png', 100, 100);

                }


            }

            $product['status'] = $product['model'] ? $this->entegrasyon->is_product_exists($product['model']) : false;
            if (!$product['status']) {
                $product['status'] = $product['model'] ? $this->entegrasyon->is_product_exists_in_product_to_marketplace($code, $product) : false;
            }

            if (!$product['status']) {
                $product['status'] = $product['marketplace_product_id'] ? $this->entegrasyon->is_product_exists($product['marketplace_product_id']) : false;
            }

            $data['products'][] = $product;
        }

        $url = '';


        $data['status'] = 1;
        $data['message'] = "";
        $data['code'] = $code;
        $data['easy_visibility'] = $this->config->get('easy_visibility') ? '' : 'hidden';


        if (isset($this->request->get['filter_model'])) {
            $url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
        }
        if (isset($this->request->get['filter_barcode'])) {
            $url .= '&filter_barcode=' . urlencode(html_entity_decode($this->request->get['filter_barcode'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_name'])) {
            $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
        }


        if (isset($this->request->get['filter_manufacturer'])) {
            $url .= '&filter_manufacturer=' . $this->request->get['filter_manufacturer'];
        }

        if (isset($this->request->get['filter_category'])) {
            $url .= '&filter_category=' . $this->request->get['filter_category'];
        }

        if (isset($this->request->get['filter_status'])) {
            $url .= '&filter_status=' . $this->request->get['filter_status'];
        }


        if (isset($this->request->get['filter_marketplace'])) {
            $url .= '&filter_marketplace=' . $this->request->get['filter_marketplace'];
        }

        if (isset($this->request->get['filter_stock_prefix'])) {
            $url .= '&filter_stock_prefix=' . html_entity_decode($this->request->get['filter_stock_prefix']);
        }

        if (isset($this->request->get['filter_stock'])) {
            $url .= '&filter_stock=' . $this->request->get['filter_stock'];
        }

        if (isset($this->request->get['filter_marketplace_do'])) {
            $url .= '&filter_marketplace_do=' . $this->request->get['filter_marketplace_do'];
        }
        if (isset($this->request->get['filter_match'])) {
            $url .= '&filter_match=' . $this->request->get['filter_match'];
        }


        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('entegrasyon/dashboard', $this->token_data['token_link'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => 'Ürünler',
            'href' => $this->url->link('entegrasyon/product', $this->token_data['token_link'] . $url, true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $marketplace_info['name'] . ' Ürün Aktarma',
            'href' => $this->url->link('entegrasyon/product/get_marketplace_products', $this->token_data['token_link'] . $url, true)
        );


        $pagination = new Pagination();
        $pagination->total = $product_total;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->url->link('entegrasyon/product/get_marketplace_products_new', $this->token_data['token_link'] . $url . '&code=' . $code . '&page={page}', true);

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($product_total - $limit)) ? $product_total : ((($page - 1) * $limit) + $limit), $product_total, ceil($product_total / $limit));

        $this->model_entegrasyon_general->loadPageRequired();
        $data['token_link'] = $this->token_data['token_link'];


        $data['filter_model'] = $filter_model;
        $data['filter_barcode'] = $filter_barcode;
        $data['filter_name'] = $filter_name;
        $data['filter_stock_prefix'] = $filter_stock_prefix;
        $data['filter_stock'] = $filter_stock;
        $data['filter_marketplace_do'] = $filter_marketplace_do;
        $data['filter_match'] = $filter_match;


        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');


        $this->response->setOutput($this->load->view('entegrasyon/product_download_list_new', $data));

    }

    public function download_product()
    {

        error_reporting(0);

        $this->load->model('entegrasyon/general');

        if (!$this->model_entegrasyon_general->checkPermission()) {

            echo json_encode(array('status' => false, 'message' => 'Gerçek Mağaza bilgileri kullanıldığı için Demo versiyonda ürün aktarılmasına izin verilmemektedir.'));
            return;

        }

        // if(!isset($this->request->post['product_id']))return;
        $code = $this->request->get['code'];

        $category_id = isset($this->request->post['category_id']) ? $this->request->post['category_id'] : 0;
        $manufacturer_id = isset($this->request->post['manufacturer_id']) ? $this->request->post['manufacturer_id'] : 0;

        $product_id = $this->request->post['product_id'];


        $this->load->model('entegrasyon/product/' . $code);

        $marketplace_product_info = $this->{"model_entegrasyon_product_" . $code}->getMarketPlaceProduct($product_id, $category_id, $manufacturer_id);




        if (!$marketplace_product_info['status']) {
            echo json_encode($marketplace_product_info);
            return;
        }

        $product_info = $this->entegrasyon->getProductByModel($marketplace_product_info['product_data']['model'], $marketplace_product_info['product_data']['model']);

        if ($product_info) {

            $model = $this->startsWith($this->config->get($code . '_setting_model_prefix'), $product_info['model']);
            $product_info['model'] = $model;

        }


        if (!$product_info) {


            $product_id = $this->entegrasyon->addProduct($marketplace_product_info['product_data']);

            if ($product_id) {

                $this->entegrasyon->addMarketplaceProduct($product_id, $marketplace_product_info['marketplace_product_data'], $code);
            }


            $result = array('status' => true, 'message' => 'Ürün Başarıyla Mağazanıza Eklendi!');

        } else {

            $result = array('status' => false, 'message' => 'Ürün mağazanızda mevcut olduğu için yeniden eklenmedi!');

        }

        echo json_encode($result);

    }

    private function startsWith($string, $startString)
    {
        $len = strlen($startString);
        $result = (substr($string, 0, $len) === $startString);
        if ($result) {

            $cut = explode($startString, $string);
            return $cut[1];

        } else {

            return $string;
        }
    }

    public function autocomplete()
    {
        $json = array();

        if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_model'])) {
            $this->load->model('entegrasyon/product');
            $this->load->model('catalog/option');

            if (isset($this->request->get['filter_name'])) {
                $filter_name = $this->request->get['filter_name'];
            } else {
                $filter_name = '';
            }

            if (isset($this->request->get['filter_model'])) {
                $filter_model = $this->request->get['filter_model'];
            } else {
                $filter_model = '';
            }

            if (isset($this->request->get['limit'])) {
                $limit = $this->request->get['limit'];
            } else {
                $limit = 5;
            }

            $filter_data = array(
                'filter_name' => $filter_name,
                'filter_model' => $filter_model,
                'start' => 0,
                'limit' => $limit
            );

            $results = $this->model_entegrasyon_product->getProducts($filter_data);

            foreach ($results as $result) {
                $option_data = array();

                $product_options = $this->model_entegrasyon_product->getProductOptions($result['product_id']);

                foreach ($product_options as $product_option) {
                    $option_info = $this->model_catalog_option->getOption($product_option['option_id']);

                    if ($option_info) {
                        $product_option_value_data = array();

                        foreach ($product_option['product_option_value'] as $product_option_value) {
                            $option_value_info = $this->model_catalog_option->getOptionValue($product_option_value['option_value_id']);

                            if ($option_value_info) {
                                $product_option_value_data[] = array(
                                    'product_option_value_id' => $product_option_value['product_option_value_id'],
                                    'option_value_id' => $product_option_value['option_value_id'],
                                    'name' => $option_value_info['name'],
                                    'price' => (float)$product_option_value['price'] ? $this->currency->format($product_option_value['price'], $this->config->get('config_currency')) : false,
                                    'price_prefix' => $product_option_value['price_prefix']
                                );
                            }
                        }

                        $option_data[] = array(
                            'product_option_id' => $product_option['product_option_id'],
                            'product_option_value' => $product_option_value_data,
                            'option_id' => $product_option['option_id'],
                            'name' => $option_info['name'],
                            'type' => $option_info['type'],
                            'value' => $product_option['value'],
                            'required' => $product_option['required']
                        );
                    }
                }

                $json[] = array(
                    'product_id' => $result['product_id'],
                    'name' => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
                    'model' => $result['model'],
                    'option' => $option_data,
                    'price' => $result['price']
                );
            }
        }


        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
