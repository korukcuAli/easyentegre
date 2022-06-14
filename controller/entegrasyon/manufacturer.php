<?php
class ControllerEntegrasyonManufacturer extends Controller {
    private $error = array();

    private $token_data;

    public function __construct($registry)
    {

        parent::__construct($registry);

        $this->load->model('entegrasyon/general');
        $this->token_data=$this->model_entegrasyon_general->getToken();

        $this->marketplaces = $this->model_entegrasyon_general->getActiveMarkets();



        if(!$this->config->get('mir_login')){

            $this->response->redirect($this->url->link('entegrasyon/setting/error','&error=no_user&'.$this->token_data['token_link'], true));

        }else if(!$this->marketplaces){

            $this->response->redirect($this->url->link('entegrasyon/setting/error','&error=no_api&'.$this->token_data['token_link'], true));

        }
    }

    public function index() {
        $this->load->language('entegrasyon/manufacturer');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('entegrasyon/manufacturer');
        $this->load->model('entegrasyon/general');


        $this->getList();
    }
    

    protected function getList() {


        $data = $this->language->all();
        if (isset($this->request->get['filter_manufacturer'])) {
            $filter_manufacturer = $this->request->get['filter_manufacturer'];
        } else {
            $filter_manufacturer= '';
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

        if (isset($this->request->get['filter_manufacturer'])) {
            $url .= '&filter_manufacturer=' . urlencode(html_entity_decode($this->request->get['filter_manufacturer'], ENT_QUOTES, 'UTF-8'));
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
            'href' => $this->url->link('entegrasyon/manufacturer', $this->token_data['token_link'] . $url, true)
        );

        $this->model_entegrasyon_general->loadPageRequired();


        $marketplaces = $this->model_entegrasyon_general->getMarketPlaces();

        $data['manufacturers'] = array();

        $filter_data = array(
            'sort'  => $sort,
            'order' => $order,
            'filter_manufacturer'=>$filter_manufacturer,

            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        );

        $manufacturer_total = $this->model_entegrasyon_manufacturer->getTotalManufacturers($filter_data);




        $results = $this->model_entegrasyon_manufacturer->getManufacturers($filter_data);


        $data['manufacturers']=$results;

        foreach ($marketplaces as $marketplace) {

            if ($marketplace['status']) {
                $filter_data[$marketplace['code']] = true;
            }

        }
        $data['marketplaces'] = $marketplaces;


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

        $data['easy_visibility']=$this->config->get('easy_visibility') ? '':'hidden';

        $data['sort_name'] = $this->url->link('entegrasyon/manufacturer', $this->token_data['token_link'] . '&sort=name' . $url, true);
        $data['sort_sort_order'] = $this->url->link('entegrasyon/manufacturer', $this->token_data['token_link'] . '&sort=sort_order' . $url, true);

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $pagination = new Pagination();
        $pagination->total = $manufacturer_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('entegrasyon/manufacturer', $this->token_data['token_link'] . $url . '&page={page}', true);
        $data['pagination'] = $pagination->render();

        if($filter_manufacturer){
            $this->load->model('catalog/manufacturer');
            $manufacturer_info=$this->model_catalog_manufacturer->getManufacturer($filter_manufacturer);
            $data['filter_manufacturer_name']=$manufacturer_info['name'];

        }else {

            $data['filter_manufacturer_name']='';

        }

        $data['filter_manufacturer'] = $filter_manufacturer;

        $data['results'] = sprintf($this->language->get('text_pagination'), ($manufacturer_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($manufacturer_total - $this->config->get('config_limit_admin'))) ? $manufacturer_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $manufacturer_total, ceil($manufacturer_total / $this->config->get('config_limit_admin')));
        $data['token_link'] = $this->token_data['token_link'];

        $data['sort'] = $sort;
        $data['order'] = $order;

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $data['add'] = $this->url->link('entegrasyon/manufacturer/add', $this->token_data['token_link'] . $url, true);
        $data['delete'] = $this->url->link('entegrasyon/manufacturer/delete', $this->token_data['token_link'] . $url, true);


        $this->response->setOutput($this->load->view('entegrasyon/manufacturer_list', $data));
    }
    public function setting()
    {
        $code = $this->request->get['code'];
        $manufacturer_id = $this->request->get['manufacturer_id'];
        $this->load->model("entegrasyon/general");
        $data= $this->entegrasyon->getSettingData($code,'manufacturer',$manufacturer_id);



        $data['token_link'] = $this->token_data['token_link'];
        $data['manufacturer_id'] = $manufacturer_id;
        $data['code'] = $code;

        $this->response->setOutput($this->load->view('entegrasyon/manufacturer/' . $code, $data));

    }


    public function add() {
        $this->load->language('entegrasyon/manufacturer');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('catalog/manufacturer');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {

            $this->model_catalog_manufacturer->addManufacturer($this->request->post);



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

            $this->response->redirect($this->url->link('entegrasyon/manufacturer', $this->token_data['token_link'] . $url, true));
        }

        $this->getForm();
    }

    public function edit() {
        $this->load->language('entegrasyon/manufacturer');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('entegrasyon/manufacturer');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_catalog_manufacturer->editManufacturer($this->request->get['manufacturer_id'], $this->request->post);

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

            $this->response->redirect($this->url->link('entegrasyon/manufacturer', $this->token_data['token_link'] . $url, true));
        }

        $this->getForm();
    }

    public function delete() {
        $this->load->language('entegrasyon/manufacturer');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('entegrasyon/manufacturer');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            foreach ($this->request->post['selected'] as $manufacturer_id) {
                $this->model_entegrasyon_manufacturer->deleteManufacturer($manufacturer_id);
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

            $this->response->redirect($this->url->link('entegrasyon/manufacturer', $this->token_data['token_link'] . $url, true));
        }

        $this->getList();
    }

    protected function getForm() {
        $data['text_form'] = !isset($this->request->get['manufacturer_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['name'])) {
            $data['error_name'] = $this->error['name'];
        } else {
            $data['error_name'] = '';
        }

        if (isset($this->error['keyword'])) {
            $data['error_keyword'] = $this->error['keyword'];
        } else {
            $data['error_keyword'] = '';
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
            'href' => $this->url->link('entegrasyon/manufacturer', $this->token_data['token_link'] . $url, true)
        );

        if (!isset($this->request->get['manufacturer_id'])) {
            $data['action'] = $this->url->link('entegrasyon/manufacturer/add', $this->token_data['token_link'] . $url, true);
        } else {
            $data['action'] = $this->url->link('entegrasyon/manufacturer/edit', $this->token_data['token_link'] . '&manufacturer_id=' . $this->request->get['manufacturer_id'] . $url, true);
        }

        $data['cancel'] = $this->url->link('entegrasyon/manufacturer', $this->token_data['token_link'] . $url, true);

        if (isset($this->request->get['manufacturer_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($this->request->get['manufacturer_id']);
        }



        $data['token_link'] = $this->token_data['token_link'];

        if (isset($this->request->post['name'])) {
            $data['name'] = $this->request->post['name'];
        } elseif (!empty($manufacturer_info)) {
            $data['name'] = $manufacturer_info['name'];
        } else {
            $data['name'] = '';
        }

        $this->load->model('setting/store');

        $data['stores'] = array();

        $data['stores'][] = array(
            'store_id' => 0,
            'name'     => $this->language->get('text_default')
        );

        $stores = $this->model_setting_store->getStores();

        foreach ($stores as $store) {
            $data['stores'][] = array(
                'store_id' => $store['store_id'],
                'name'     => $store['name']
            );
        }

        if (isset($this->request->post['manufacturer_store'])) {
            $data['manufacturer_store'] = $this->request->post['manufacturer_store'];
        } elseif (isset($this->request->get['manufacturer_id'])) {
            $data['manufacturer_store'] = $this->model_catalog_manufacturer->getManufacturerStores($this->request->get['manufacturer_id']);
        } else {
            $data['manufacturer_store'] = array(0);
        }

        if (isset($this->request->post['image'])) {
            $data['image'] = $this->request->post['image'];
        } elseif (!empty($manufacturer_info)) {
            $data['image'] = $manufacturer_info['image'];
        } else {
            $data['image'] = '';
        }

        $this->load->model('tool/image');

        if (isset($this->request->post['image']) && is_file(DIR_IMAGE . $this->request->post['image'])) {
            $data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
        } elseif (!empty($manufacturer_info) && is_file(DIR_IMAGE . $manufacturer_info['image'])) {
            $data['thumb'] = $this->model_tool_image->resize($manufacturer_info['image'], 100, 100);
        } else {
            $data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
        }

        $data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

        if (isset($this->request->post['sort_order'])) {
            $data['sort_order'] = $this->request->post['sort_order'];
        } elseif (!empty($manufacturer_info)) {
            $data['sort_order'] = $manufacturer_info['sort_order'];
        } else {
            $data['sort_order'] = '';
        }

        $this->load->model('localisation/language');

        $data['languages'] = $this->model_localisation_language->getLanguages();



        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('entegrasyon/manufacturer/manufacturer_form', $data));
    }

    protected function validateForm() {
        if (!$this->user->hasPermission('modify', 'entegrasyon/manufacturer')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ((utf8_strlen($this->request->post['name']) < 1) || (utf8_strlen($this->request->post['name']) > 64)) {
            $this->error['name'] = $this->language->get('error_name');
        }
        
        return !$this->error;
    }

    protected function validateDelete() {
        if (!$this->user->hasPermission('modify', 'entegrasyon/manufacturer')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        $this->load->model('catalog/product');

        foreach ($this->request->post['selected'] as $manufacturer_id) {
            $product_total = $this->model_catalog_product->getTotalProductsByManufacturerId($manufacturer_id);

            if ($product_total) {
                $this->error['warning'] = sprintf($this->language->get('error_product'), $product_total);
            }
        }

        return !$this->error;
    }

    public function autocomplete() {
        $json = array();

        if (isset($this->request->get['filter_name'])) {
            $this->load->model('catalog/manufacturer');

            $filter_data = array(
                'filter_name' => $this->request->get['filter_name'],
                'start'       => 0,
                'limit'       => 5
            );

            $results = $this->model_catalog_manufacturer->getManufacturers($filter_data);

            foreach ($results as $result) {
                $json[] = array(
                    'manufacturer_id' => $result['manufacturer_id'],
                    'name'            => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
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



}