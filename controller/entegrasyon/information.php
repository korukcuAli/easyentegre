<?php
class ControllerEntegrasyonInformation extends Controller
{



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


        $data['data'] = "test";
        $data['easy_visibility']=$this->config->get('easy_visibility') ? '':'hidden';

        $this->response->setOutput($this->load->view('entegrasyon/information', $data));


    }


}