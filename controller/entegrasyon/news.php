<?php
class ControllerEntegrasyonNews extends Controller
{


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

        }else if(!$this->config->get('module_entegrasyon_status')){
            $this->response->redirect($this->url->link('entegrasyon/setting/error','&error=no_module&'.$this->token_data['token_link'], true));

        }

    }


    public function index()
    {

        $this->document->setTitle("Easyentegre Duyurular");

        header('Content-Type: text/html; charset=UTF-8');


        $this->document->addStyle('view/stylesheet/entegrasyon/bootstrap4.css');
        $this->document->addStyle('view/stylesheet/entegrasyon/entegrasyon.css');

        $this->load->model('entegrasyon/news');
        $news = $this->model_entegrasyon_news->get_news_data();
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');


        $data['news'] = array();

        $url = '';
        $s = 0;
        foreach ($news as $result) {

            $data['news'][] = array(

                'title_img' => $result['title_img'],
                'information_id' => $result['information_id'],
                'information_content' => mb_strimwidth($result['information_content'], 0, 990, "... "),
                'date_added' => $result['date_added'],
                'information_title' => $result['information_title'],
                'link' => $this->url->link('entegrasyon/news/news_page&' . $this->token_data['token_link'] .
                    "&news_id=" . $s . $url, true)
            );
            $s++;
        }



        $this->response->setOutput($this->load->view('entegrasyon/news/news_list', $data));


    }

    public function news_page()
    {
        header('Content-Type: text/html; charset=UTF-8');

        $news_id =  $this->request->get['news_id'];
        $this->load->model('entegrasyon/news');
        $news = $this->model_entegrasyon_news->get_news_data();
        $data['title'] = $news[$news_id]['information_title'];
        $data['content'] = $news[$news_id]['information_content'];
        $data['img'] = $news[$news_id]['title_img'];

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('entegrasyon/news/news_page', $data));

    }




}
