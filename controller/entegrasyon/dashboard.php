<?php

class ControllerEntegrasyonDashboard extends Controller
{
    private $error = array();
    private $token_data;
    private $marketplaces;


    public function __construct($registry)
    {

        parent::__construct($registry);
        $this->load->model('entegrasyon/general');

        $this->token_data = $this->model_entegrasyon_general->getToken();

        $this->marketplaces = $this->model_entegrasyon_general->getActiveMarkets();



     if(!$this->config->get('mir_login')){
         
        $this->response->redirect($this->url->link('entegrasyon/setting/error','&error=no_user&'.$this->token_data['token_link'], true));

    }else if(!$this->marketplaces){

    $this->response->redirect($this->url->link('entegrasyon/setting/error','&error=no_api&'.$this->token_data['token_link'], true));

}else if(!$this->config->get('module_entegrasyon_status')){
         $this->response->redirect($this->url->link('entegrasyon/setting/error','&error=no_module&'.$this->token_data['token_link'], true));

     }

    }




    public function index() {



        $this->load->language('common/dashboard');

        $this->document->setTitle($this->language->get('heading_title'));

        $data['token_link'] = $this->token_data['token_link'];

        $data['breadcrumbs'] = array();
        $this->load->model('entegrasyon/general');

        //silinecek
        $this->model_entegrasyon_general->dbUpdate();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $this->token_data['token_link'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('common/dashboard', $this->token_data['token_link'], true)
        );

        $data['marketplaces']=array();
        $this->load->model('entegrasyon/product');
        $this->load->model('entegrasyon/order');


        foreach ($this->marketplaces as $marketplace) {


            if($marketplace['code']=='n11') $marketplace['color']="red";
            if($marketplace['code']=='hb') $marketplace['color']="orange";
            if($marketplace['code']=='ty') $marketplace['color']="black";
            if($marketplace['code']=='eptt') $marketplace['color']="yellow";
            if($marketplace['code']=='gg') $marketplace['color']="green";

            if ($marketplace['status']) {
                $filter_data[$marketplace['code']] = true;

                $marketplace['product_count']=$this->model_entegrasyon_product->getTotalProductByMarketPlace($marketplace['code']);
                $marketplace['order_today']=$this->model_entegrasyon_order->getOrderbyToday($marketplace['code']);
                $marketplace['order_total']=$this->model_entegrasyon_order->getOrderbyTotal($marketplace['code']);

              $data['marketplaces'][]=$marketplace;



                }
            }

       $this->document->addStyle('view/stylesheet/entegrasyon/bootstrap4.css');

        $data['orders'] = array();

        $filter_data = array(


            'start'                  => 0,
            'limit'                  => 5
        );


        $url='';
        $orders = $this->model_entegrasyon_order->getOrders($filter_data);
        $this->load->model('entegrasyon/support');

        $data['ticket_link'] = $this->url->link('entegrasyon/support', $this->token_data['token_link'] . $url, true);
        $data['information_link'] = $this->url->link('entegrasyon/information', $this->token_data['token_link'] . $url, true);

        $data['easy_visibility']=$this->config->get('easy_visibility') ? '':'hidden';
        $data['easy_ticket']=$this->config->get('easy_ticket') ? '':'hidden';

        foreach ($orders as $result) {

            $orderedProducts=$this->model_entegrasyon_order->getOrderedProducts($result['order_id']);



            $data['orders'][] = array(

                'order_id'=>$result['order_id'],
                'market_order_id'=>$result['market_order_id'],
                'order_status'=>$result['name'],

                'ordered_products'=>$orderedProducts,
                'logo' =>$this->model_tool_image->resize('entegrasyon-logo/'.$result['code'].'-logo.png', 40, 40),
                'customer'=>$result['first_name'].' '.$result['last_name'],
                'total'=>$result['total'],
                'date_added'=>date('d-m-Y H:i:s', strtotime($result['date_added'])),
                'date_modified'=>$result['date_modified']?date('d-m-Y H:i:s', strtotime($result['date_modified'])):'',
                'view'         => $this->url->link('entegrasyon/order/info', $this->token_data['token_link'] . '&order_id=' . $result['order_id'] . $url, true),


            );
        }


        $this->load->model('entegrasyon/product_question');

       $data['questions'] = $this->model_entegrasyon_product_question->getQuestions($filter_data);


        if (count($data['marketplaces'])>4){

            $data['col']=2;
        }
        else if (count($data['marketplaces'])==4){

            $data['col']=3;
        }
        else if (count($data['marketplaces'])==3){

            $data['col']=4;
        }

        $data['boxwidth']=100/count($data['marketplaces']);

        
        $this->model_entegrasyon_general->loadPageRequired();


        // Dashboard Extensions
        $dashboards = array();

        $data['token_link'] = $this->token_data['token_link'];


        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $data['catalog_url']=HTTPS_CATALOG;

        // Run currency update
        if ($this->config->get('config_currency_auto')) {
            $this->load->model('localisation/currency');

            $this->model_localisation_currency->refresh();
        }





        $this->load->model("entegrasyon/general");
        $market_places = $this->model_entegrasyon_general->getMarketPlace('n11');
        $data['domain_id'] = $market_places['domain_id'];









        $this->response->setOutput($this->load->view('entegrasyon/dashboard', $data));
    }






}