<?php
class ControllerEntegrasyonSupport extends Controller
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


        $this->ticket_list();
    }

    public function succes_page()
    {
        $this->document->addStyle('view/stylesheet/entegrasyon/bootstrap4.css');
        $this->document->addStyle('view/stylesheet/entegrasyon/entegrasyon.css');

        $this->document->setTitle("Pazaryeri Entegrasyon Destek");

        $data['token_link'] = $this->token_data['token_link'];


        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('entegrasyon/support/succes_page', $data));

    }
    public function want_ftp_admin()
    {

        $this->load->model("entegrasyon/general");
        $this->load->model("customer/customer");

        $market_places = $this->model_entegrasyon_general->getMarketPlace('n11');
        $domain_id  =$market_places['domain_id'];
        $admin_id  = $this->request->post['admin_id'];
        $admin_pass  = $this->request->post['admin_pass'];
        $ftp_id  = $this->request->post['ftp_id'];
        $host  = $this->request->post['host'];
        $ftp_pass = $this->request->post['ftp_pass'];



        $ticket_data=array(
            'admin_id'=>$admin_id,
            'admin_pass'=>$admin_pass,
            'host'=>$host,
            'domain_id'=>$domain_id,
            'ftp_id'=>$ftp_id,
            'ftp_pass'=>$ftp_pass
        );

        $this->load->model('entegrasyon/support');
        $support_data = $this->model_entegrasyon_support->get_support_data($ticket_data,'info_ftp_admin');

        echo json_encode($support_data);


    }
    public function ticket_form()
    {
        $this->document->addStyle('view/stylesheet/entegrasyon/bootstrap4.css');
        $this->document->addStyle('view/stylesheet/entegrasyon/entegrasyon.css');

        $this->model_entegrasyon_general->loadPageRequired();


        $easy=$this->config->get('easy_visibility') ? 'Easy Entegre':'Pazaryeri Entegrasyon';

        $data['easy_visibility']=$this->config->get('easy_visibility') ?'':'hidden';
        $url = '';

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => "home",
            'href' => $this->url->link('common/dashboard', $this->token_data['token_link'], true)
        );



        $data['token_link'] = $this->token_data['token_link'];
        $data['breadcrumbs'][] = array(
            'text' =>  $easy." Destek",
            'href' => $this->url->link('entegrasyon/support', $this->token_data['token_link'] . $url, true)
        );
        $data['title']=$easy. ' Destek Talep Formu';
        $this->document->setTitle( $data['title']);
        $market_places = $this->model_entegrasyon_general->getActiveMarkets();
        $data['marketplaces'] = $market_places;

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('entegrasyon/support/ticket_form', $data));

    }


    public function ticket_list()
    {
        $this->document->addStyle('view/stylesheet/entegrasyon/bootstrap4.css');
        $this->document->addStyle('view/stylesheet/entegrasyon/entegrasyon.css');

        $data['easy_visibility']=$this->config->get('easy_visibility') ? 'Easy Entegre':'Pazaryeri Entegrasyon';

        $this->document->setTitle("Pazaryeri Entegrasyon Destek");

        $url = '';


        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => "home",
            'href' => $this->url->link('common/dashboard', $this->token_data['token_link'], true)
        );
        $data['breadcrumbs'][] = array(
            'text' =>  $data['easy_visibility']." Destek",
            'href' => $this->url->link('entegrasyon/support/ticket_list', $this->token_data['token_link'] . $url, true)
        );


        $data['token_link'] = $this->token_data['token_link'];

        $this->load->model("entegrasyon/general");
        $market_places = $this->model_entegrasyon_general->getMarketPlace('n11');
        $data['domain_id'] = $market_places['domain_id'];
        




        $ticket_data=array('domain_id'=>$data['domain_id']);
        $this->load->model('entegrasyon/support');
        $tickets= $this->model_entegrasyon_support->get_support_data($ticket_data,'get_tickets');


        $data["want_ftp_admin"] = isset($tickets[0]['want_ftp_admin'])?$tickets[0]['want_ftp_admin']?$tickets[0]['want_ftp_admin']:0:0;
        $data['tickets']=array();
      foreach ($tickets as $ticket) {
            $data['tickets'][]=array(

                'ticket_id'=>$ticket['ticket_id'],
                'domain_id'=>$ticket['domain_id'],
                'subject'=>$ticket['subject'],
                'stage'=>$ticket['stage'],
                'marketplace'=>$ticket['marketplace'],
                'statu'=>$ticket['statu'],
                'date_added'=>$ticket['date_added'],
                'date_modified'=>$ticket['date_modified'],
                'view'          => $this->url->link('entegrasyon/support/view',  $this->token_data['token_link'] . '&action=view_ticket&ticket_id=' . $ticket['ticket_id'] . $url, true),

            );
        }




        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('entegrasyon/support/ticket_list', $data));

    }





    public function view()
    {

        $this->document->addStyle('view/stylesheet/entegrasyon/bootstrap4.css');
        $this->document->addStyle('view/stylesheet/entegrasyon/entegrasyon.css');

        $easy=$this->config->get('easy_visibility') ? 'Easy Entegre':'Pazaryeri Entegrasyon';


        $url = '';

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => "home",
            'href' => $this->url->link('common/dashboard', $this->token_data['token_link'], true)
        );



        $data['breadcrumbs'][] = array(
            'text' => "Destek Taleplerim",
            'href' => $this->url->link('entegrasyon/support/ticket_list', $this->token_data['token_link'] . $url, true)
        );

           $data['breadcrumbs'][] = array(
            'text' => "Destek Konuşması",
            'href' => "javascript:window.location.reload(true)"
        );

        $data['easy_visibility']=$this->config->get('easy_visibility') ?'':'hidden';
        $data['token_link'] = $this->token_data['token_link'];
        $data['title']=$easy. ' Destek Talep Formu';
        $this->document->setTitle( $data['title']);
        $data['domain_id'] = $this->config->get('mir_domain_id');

        $data['ticket_id'] = $this->request->get["ticket_id"] ;
       // $data['title'] = $this->request->get["title"] ;

        $ticket_data=array('ticket_id'=>$data['ticket_id']);
        $this->load->model('entegrasyon/support');
     $support_data = $this->model_entegrasyon_support->get_support_data($ticket_data,'get_ticket_messages');


        $data['messages']=$support_data['message_data'];
        $data['ticket']=$support_data['ticket_data'];
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('entegrasyon/support/view_ticket', $data));

    }
    public function new_ticket()
    {
        $this->load->model("entegrasyon/general");
        $this->load->model("customer/customer");

        $market_places = $this->model_entegrasyon_general->getMarketPlace('n11');
        $domain_id  = $market_places['domain_id'];

        $message = strip_tags(html_entity_decode($this->request->post['message'])," <img>");
        $message = str_replace('&nbsp;','',$message);
        $message =str_replace("`","",$message);
        //$message =str_replace("=","",$message);
        $message =str_replace("&","",$message);
        $message =str_replace("%","",$message);
        // $message =str_replace("!","",$message);
        $message =str_replace("#","",$message);

        $message =str_replace("*","",$message);
        $message =str_replace("And","",$message);
        $message =str_replace("'","",$message);
        //print_r($message);
        //return;
        $subject = $this->request->post['subject'];
        $marketplace = $this->request->post['marketplace'];

        $ticket_data=array(
            'subject'=>$subject,
            'domain_id'=>$domain_id,
            'marketplace'=>$marketplace,
            'message'=>$message
        );






        $this->load->model('entegrasyon/support');
        $support_data = $this->model_entegrasyon_support->get_support_data($ticket_data,'new_ticket');

        echo json_encode($support_data);

    }



    public function send_message()
    {

        $ticket_id = $this->request->post['ticket_id'];
        $domain_id = $this->request->post['domain_id'];
        $message = strip_tags(html_entity_decode($this->request->post['message']),"<img>");


        $message = str_replace('&nbsp;','',$message);
        //$message =str_replace("=","",$message);
        $message =str_replace("&amp;","'Ve İşareti'",$message);
     //   $message =str_replace("%","",$message);
       // $message =str_replace("!","",$message);
     //   $message =str_replace("#","",$message);
     //   $message =str_replace("<","",$message);
      //  $message =str_replace(">","",$message);
       // $message =str_replace("*","",$message);
       // $message =str_replace("*","",$message);
      //  $message =str_replace("+","+",$message);
     //   $message =str_replace("'","",$message);
        $ticket_data=array(
            'ticket_id'=>$ticket_id,
            'domain_id'=>$domain_id,
            'message'=>$message
        );

        $this->load->model('entegrasyon/support');
        $support_data = $this->model_entegrasyon_support->get_support_data($ticket_data,'send_message');

        echo json_encode($support_data);


    }


}




