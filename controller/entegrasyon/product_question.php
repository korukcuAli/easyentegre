<?php

class ControllerEntegrasyonProductQuestion extends Controller
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
        $this->load->language('entegrasyon/productquestion');


        $this->document->setTitle($this->language->get('heading_title'));


        $this->load->model('entegrasyon/product_question');
        $this->getList();
    }


    protected function getList()
    {


        $data = $this->language->all();


        if (isset($this->request->get['filter_customer'])) {
            $filter_question_status = $this->request->get['filter_question_status'];
        } else {
            $filter_question_status = '';
        }


        if (isset($this->request->get['filter_marketplace'])) {
            $filter_marketplace = $this->request->get['filter_marketplace'];
        } else {
            $filter_marketplace = '';
        }



        if (isset($this->request->get['question'])) {
            $question = $this->request->get['question'];
        } else {
            $question = 'DESC';
        }

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $url = '';

        if (isset($this->request->get['filter_question_id'])) {
            $url .= '&filter_question_id=' . $this->request->get['filter_question_id'];
        }

        if (isset($this->request->get['filter_customer'])) {
            $url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_date_added'])) {
            $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
        }

        if (isset($this->request->get['question'])) {
            $url .= '&question=' . $this->request->get['question'];
        }

        $data['total_gg']=0;
        $data['total_ty']=0;
        $data['total_n11']=0;

        $data['easy_visibility']=$this->config->get('easy_visibility') ? '':'hidden';

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $this->token_data['token_link'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('entegrasyon/product_question', $this->token_data['token_link'] . $url, true)
        );


        $data['questions'] = array();



        $data['token_link'] = $this->token_data['token_link'];

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
        $this->document->addStyle('view/stylesheet/entegrasyon/bootstrap4.css');

        $this->document->setTitle('Pazaryeri Ürün Soruları');
        $filter_data = array(
            'filter_question_status'    => $filter_question_status,
            'filter_marketplace'     => $filter_marketplace,
            'start'                  => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit'                  => $this->config->get('config_limit_admin')
        );


        $question_total = $this->model_entegrasyon_product_question->getTotalQuestions($filter_data);

        $questions = $this->model_entegrasyon_product_question->getQuestions($filter_data);



        $data['questions'] = $questions;


        foreach ( $data['questions'] as $datas) {

            if($datas['code'] =='ty'){
             $data['total_ty']++;
            }else if($datas['code'] =='n11'){
                $data['total_n11']++;

            }else{
                $data['total_gg']++;


            }


        }



        $this->load->model('entegrasyon/general');
        $this->model_entegrasyon_general->loadPageRequired();
        $pagination = new Pagination();
        $pagination->total = $question_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('entegrasyon/product_question', $this->token_data['token_link'] . $url . '&page={page}', true);

        $data['pagination'] = $pagination->render();

        $data['filter_marketplace']=$filter_marketplace;

        $data['results'] = sprintf($this->language->get('text_pagination'), ($question_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($question_total - $this->config->get('config_limit_admin'))) ? $question_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $question_total, ceil($question_total / $this->config->get('config_limit_admin')));

        $data['question'] = $question;


        $marketplaces = $this->model_entegrasyon_general->getMarketPlaces();
        foreach ($marketplaces as $marketplace) {

            if ($marketplace['status']) {
                $filter_data[$marketplace['code']] = true;
                $data['edit_button_status'] = true;
            }

        }
        $data['marketplaces'] = $marketplaces;
        $data['catalog_url'] = HTTPS_CATALOG;


        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('entegrasyon/product_question', $data));
    }


    public function reply_form()
    {



        $this->load->model('entegrasyon/general');
        $data['token_link'] = $this->token_data['token_link'];
        $this->load->model('entegrasyon/general');
        $code = $this->request->get['code'];
        $data['role_name'] = $this->config->get('gg_role_kullanici_adi');

        $data['code']=$code;
        $data['question_id'] = $this->request->get['question_id'];
        $post_data['request_data'] = $data['question_id'];

        $post_data['market'] = $this->model_entegrasyon_general->getMarketPlace($code);

        $question_info = $this->entegrasyon->clientConnect($post_data, 'get_question', $code, false);



            if($code=='n11'){
    $data['productTitle']=$question_info['result']['productTitle'];

        }  if($code=='gg'){
    $data['productTitle']="Ürün Adı GittiGidiyor Api Tarafından Gönderilmedi"; //$question_info['result']['productTitle'];

        }

    if($code=='ty'){
        if(isset($question_info['result']['productName'])){
            $data['productTitle']=$question_info['result']['productName'];

        }
        else{
            $data['productTitle']="Ürün Adı Trendyol Api Tarafından Gönderilmedi";

        }

        }




        if($code=='gg'){

            $data['messages']=array_reverse($question_info['result']['result']['messages']['message']);
            if (!isset($data['messages']['1'])) {
                $data['messages'] = array_reverse($question_info['result']['result']['messages']);
            }



            $this->response->setOutput($this->load->view('entegrasyon/product_question/reply', $data));
        return;
        }

        $data['question_status'] = $question_info['result']['status'];
        if ( $question_info['result']['userName'] == null || $question_info['result']['userName'] == " "){

            $data['user_name'] = "İsimsiz Müşteri";
        }else {
            $data['user_name'] = mb_convert_case($question_info['result']['userName'], MB_CASE_TITLE, "UTF-8");

        }

        $data['question_text'] = $question_info['result']['text'];
        if($code=='ty'){
            $data['question_creationDate']=  date('Y-m-d H:i:s', substr($question_info['result']['creationDate'], 0, 10));
        }else if($code=='n11'){

            $data['question_creationDate']=   $question_info['result']['creationDate'];

        }
        $data['rejected_status'] = false;



        if(isset($question_info['result']['answer']['text'])){

            $data['answer_text'] = $question_info['result']['answer']['text'];
            if($code=='ty'){
                $data['answer_creationDate']=  date('Y-m-d H:i:s', substr($question_info['result']['answer']['creationDate'], 0, 10));

            }else if ($code=='n11') {

                $data['answer_creationDate']= $question_info['result']['creationDate'];

            }


        }else {

            $data['answer_text']=false;
        }

        if (isset($question_info['result']['rejectedAnswer'])) {
            $data['rejectedAnswer_text'] = $question_info['result']['rejectedAnswer']['text'];
            $data['rejectedAnswer_reason'] = $question_info['result']['rejectedAnswer']['reason'];
            $data['rejectedAnswer_creationDate']=  date('Y-m-d H:i:s', substr($question_info['result']['rejectedAnswer']['creationDate'], 0, 10));

            $data['rejected_status'] = true;
        }



            $this->response->setOutput($this->load->view('entegrasyon/product_question/reply', $data));



    }

    public function reply()
    {

        $this->load->model('entegrasyon/general');
        $code =$this->request->get['code'];
        $reply_text = $this->request->post['answer'];

        $data['question_id'] = $this->request->get['question_id'];
        $post_data['request_data'] = array('question_id' => $data['question_id'], 'text' => $reply_text);
       // $answered = $this->request->get['answered'];



        $post_data['market'] = $this->model_entegrasyon_general->getMarketPlace($code);

        $this->load->model('entegrasyon/product_question')   ;
        $this->model_entegrasyon_product_question->answeredQuestion($data['question_id']);


        $reply_result = $this->entegrasyon->clientConnect($post_data, 'reply_question', $code, false);


        echo json_encode(array("status"=>$reply_result['status'],'message'=>$reply_result['message']));

    }

    public function delete_question()
    {

        $data['product_question_id'] = $this->request->get['product_question_id'];
        $data['bulk_delete'] = $this->request->get['bulk_delete'];


        if (isset($this->request->get['product_question_id'])) {
            $product_question_id = $this->request->get['product_question_id'];

            $this->db->query("delete from ".DB_PREFIX."es_product_question where product_question_id='".$product_question_id."'");
            $url = '';

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }


            if (!isset($data['bulk_delete'])){
                $this->session->data['success'] = 'Soru Başarıyla Silindi!';
                $this->response->redirect($this->url->link('entegrasyon/product_question', $this->token_data['token_link'] . $url, true));


            }
            echo json_encode(array("status"=>true ));

        }
    }





}