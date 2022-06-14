<?php
class ControllerEntegrasyonSetting extends Controller
{
    private $error = array();
    private $check;
    private $token_data;
    private $validate=true;
    private $reg=null;

    public function __construct($registry)
    {

        parent::__construct($registry);
        $this->reg = $registry;
        $this->load->model('entegrasyon/general');
        $this->token_data=$this->model_entegrasyon_general->getToken();

    }

    public function privacy()
    {

        $data['token_link'] = $this->token_data['token_link'];

        $this->response->setOutput($this->load->view('entegrasyon/setting/account/privacy', $data));

    }



    public function fast_update_bulk()
    {


        $this->updatedb_for_request();

        $this->load->model('entegrasyon/general');
        $marketplaces = $this->model_entegrasyon_general->getMarketPlaces();
        $this->reg->set('easybulk', new Easybulk($this->reg));
        //$data['code'] =  $this->request->get['code'];
        //$data['marketplace'] =  $this->request->get['marketplace'];

        $data['catalog_url']=HTTPS_CATALOG;

        $data['marketplace_products_total'] = array();

        foreach ($marketplaces as $marketplace) {
            $filter_data = array();
            $filter_data['market'] =$marketplace['code'] ;

            $data['marketplaces'][] = array(
                'products_total' => $this->easybulk->getUpdatableProductTotal($filter_data),
                'name' => $marketplace['name'],
                'code' => $marketplace['code'],
                'status' => $marketplace['status'],
                'logo' => $marketplace['logo']
            );


        }


        $this->response->setOutput($this->load->view('entegrasyon/setting/fast_bulk_update2', $data));


    }



    public function check_products_marketplace()
    {
        $code =  $this->request->get['code'];

        try {

            $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "es_market_product where `code` = '" . $code . "' limit 0,1");

            if ($query->num_rows) {
                echo json_encode(array('status'=>true));

            }else{
                echo json_encode(array('status'=>false));

            }

        }catch (SQLiteException $exception){

            echo json_encode(array('status'=>false,'message'=>$exception->getMessage()));
        }




    }

    public function index()
    {

        if(!$this->config->get('module_entegrasyon_status')){
            $this->response->redirect($this->url->link('entegrasyon/setting/error','&error=no_module&'.$this->token_data['token_link'], true));

        }
        $this->load->language('entegrasyon/setting');

        $data = $this->language->all();
        $data['easy_visibility']=$this->config->get('easy_visibility') ? '':'hidden';

        if(isset($this->request->get['form'])){
            $form=$this->request->get['form'];
        }else {
            $form = 'login';
        }





        $this->load->model("entegrasyon/general");
        $data['permission']=$this->model_entegrasyon_general->checkPermission();

        //  $this->load->model('setting/extension');
        $this->load->model('setting/setting');

        $this->document->setTitle('Entegrasyon Ayarları');

        $this->model_entegrasyon_general->loadPageRequired();



        if($this->config->get('mir_login')){
            $data['logout']= $this->url->link('entegrasyon/setting/logout', $this->token_data['token_link'], true);
            $data['mir_username']=$this->config->get('mir_username');
        }else {
            $data['logout']='';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $this->token_data['token_link'], true),
        );
        $data['breadcrumbs'][] = array(
            'text' => 'Entegrasyonlar',
            'href' => $this->url->link('entegrasyon/setting', $this->token_data['token_link'], true),
        );

        $data['success'] = '';
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }

        //  print_r($this->config);

        //     return;



        $data['token_link'] = $this->token_data['token_link'];
        $data['form']=$form;

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('entegrasyon/setting', $data));
    }

    public function tool()
    {

        $data['domain'] =HTTP_CATALOG;
        if(!$this->config->get('module_entegrasyon_status')){
            $this->response->redirect($this->url->link('entegrasyon/setting/error','&error=no_module&'.$this->token_data['token_link'], true));

        }


        $this->load->language('entegrasyon/setting');

        $data = $this->language->all();
        $data['easy_visibility']=$this->config->get('easy_visibility') ? '':'hidden';

        if(isset($this->request->get['form'])){
            $form=$this->request->get['form'];
        }else {
            $form = 'login';
        }

        $this->load->model("entegrasyon/general");

        $data['permission']=$this->model_entegrasyon_general->checkPermission();




        //  $this->load->model('setting/extension');
        $this->load->model('setting/setting');

        $this->document->setTitle('Entegrasyon Araçları');

        $this->model_entegrasyon_general->loadPageRequired();



        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $this->token_data['token_link'], true),
        );
        $data['breadcrumbs'][] = array(
            'text' => 'Araçlar',
            'href' => $this->url->link('entegrasyon/setting/tool', $this->token_data['token_link'], true),


        );

        $data['success'] = '';
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }

        //  print_r($this->config);

        //     return;

        if (isset($this->request->get['tool'])){

            $data['success']='<b>Varyantlar Başarılı Bir Şekilde Yenilendi <b/>- '.' <'.date('d.m.Y - H:i:s').'>';
        }




        $this->load->model('entegrasyon/general');
        $data['marketplaces'] = $this->model_entegrasyon_general->getMarketPlaces();

        $data['token_link'] = $this->token_data['token_link'];
        $data['form']=$form;

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('entegrasyon/setting/tool', $data));
    }

    public function add_tool_history()
    {

        $code=$this->request->post['code'];
        $do=$this->request->post['do'];
        $tool_category=$this->request->post['tool_category'];

        $this->load->model('entegrasyon/tool_history');
        $this->model_entegrasyon_tool_history->addToolHistory($code,$do,$tool_category);

    }





    public function product_setting_delete()
    {

        $code = $this->request->post['code'];


        $this->load->model('entegrasyon/product');
        $this->model_entegrasyon_product->productSettingDelete($code);



        /*    if ($code == "all" ){

                $sql="DELETE FROM test ";


            }else {
                $sql="UPDATE test SET ".$code."= ''";

            }

            $this->db->query($sql);*/

    }

    public function manufacturer_setting_delete()
    {
        $code = $this->request->post['code'];



        $this->load->model('entegrasyon/manufacturer');
        $this->model_entegrasyon_manufacturer->manufacturerSettingDelete($code);


        /* if ($code == "all" ){

             $sql="DELETE FROM test ";


         }else {
             $sql="UPDATE test SET ".$code."= ''";

         }

         $this->db->query($sql);*/

    }

    public function category_setting_delete()
    {
        $code = $this->request->post['code'];



        $this->load->model('entegrasyon/category');
        $this->model_entegrasyon_category->categorySettingDelete($code);

        /*   if ($code == "all" ){

               $sql="DELETE FROM test ";


           }else {
               $sql="UPDATE test SET ".$code."= ''";

           }

           $this->db->query($sql);*/

    }

    public function tool_history_list()
    {
        if(!$this->config->get('module_entegrasyon_status')){
            $this->response->redirect($this->url->link('entegrasyon/setting/error','&error=no_module&'.$this->token_data['token_link'], true));

        }


        $this->load->language('entegrasyon/setting');

        $data = $this->language->all();
        $data['easy_visibility']=$this->config->get('easy_visibility') ? '':'hidden';

        if(isset($this->request->get['form'])){
            $form=$this->request->get['form'];
        }else {
            $form = 'login';
        }

        $this->load->model("entegrasyon/general");

        $data['permission']=$this->model_entegrasyon_general->checkPermission();




        //  $this->load->model('setting/extension');
        $this->load->model('setting/setting');

        $this->document->setTitle('Entegrasyon Araç Geçmiş İşlemleri');

        $this->model_entegrasyon_general->loadPageRequired();



        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => 'Araçlar',
            'href' => $this->url->link('entegrasyon/setting/tool', $this->token_data['token_link'], true),
        );
        $data['breadcrumbs'][] = array(
            'text' => 'Araç Kullanım Geçmişi',
            'href' => $this->url->link('entegrasyon/setting/tool_history_list', $this->token_data['token_link'], true),


        );


        $this->load->model('entegrasyon/general');
        $data['marketplaces'] = $this->model_entegrasyon_general->getMarketPlaces();

        $data['token_link'] = $this->token_data['token_link'];
        $data['form']=$form;

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->load->model('entegrasyon/tool_history');
        $data['historys'] = $this->model_entegrasyon_tool_history->getHistorys();


        $this->response->setOutput($this->load->view('entegrasyon/setting/tool_history_list', $data));


    }




    public function login()
    {


        $username= $this->request->post['username'];
        $password= $this->request->post['password'];

        if(!$username && !$password){

            echo json_encode(array('status'=>false,'message'=>"Email ve Parolanızı Giriniz"));

            return;
        }

        // $request_data = array();
        $post_data = array('username'=>$username,'password'=>$password);

        $result = $this->entegrasyon->clientConnect($post_data, 'login', null,false);



        if($result['status']){

            $this->load->model('setting/setting');
            $saved_data=array('mir_login'=>true,'mir_username'=>$username,'mir_password'=>$password,'mir_domain_id'=>$result['result']['domain_id'],'mir_marketplaces'=>serialize($result['result']['marketplaces']));
            $this->model_setting_setting->editSetting('mir', $saved_data);


        }

        echo  json_encode($result);


    }


    public function checkmode()
    {
        $this->load->model('entegrasyon/general');
        $mode_info=$this->model_entegrasyon_general->getDomainMode();

        if(!$mode_info){
            $mode_info=array("domain_control"=>1,"easy_visibility"=>"1","easy_ticket"=>"1");
        }

        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('easy', $mode_info);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($mode_info));
    }




    public function install_success()
    {


        $data['token_link'] = $this->token_data['token_link'];

        // print_r(HTTP_CATALOG);
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        $email = $this->config->get('config_email');
        $phone =  $this->config->get('config_telephone');



        $ch = curl_init("https://www.easyentegre.com/index.php?route=api/installed_domains");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            "phone=".$phone."&email=".$email."&domain=".HTTP_CATALOG."&ip=".$ip."");
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);

        curl_close ($ch);
        $this->response->setOutput($this->load->view('entegrasyon/setting/install_success', $data));

    }

    public function login_success()
    {
        $data['token_link'] = $this->token_data['token_link'];
        $this->load->model('entegrasyon/general');
        $data['marketplaces'] = $this->model_entegrasyon_general->getMarketPlaces();
        $data['marketplace_count']=count($data['marketplaces']);
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $data['setting_url']=$this->url->link('entegrasyon/setting', $this->token_data['token_link'], true);

        $this->response->setOutput($this->load->view('entegrasyon/setting/account/login_success', $data));

    }

    public function register()
    {

        $data['token_link'] = $this->token_data['token_link'];

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

            $status=true;
            $message='';
            $username= $this->request->post['username'];
            $password= $this->request->post['password'];
            $phone= $this->request->post['phone'];
            $password_again= $this->request->post['password_again'];
            $post_data = array('username'=>$username,'phone'=>$phone,'password'=>$password);

            $result = $this->entegrasyon->clientConnect($post_data, 'register',null, false);

            if($result['status']){

                $this->load->model('setting/setting');
                $saved_data=array('mir_login'=>true,'mir_username'=>$username,'mir_password'=>$password,'mir_domain_id'=>$result['result']['domain_id'],'mir_marketplaces'=>serialize($result['result']['marketplaces']));
                $this->model_setting_setting->editSetting('mir', $saved_data);

            }
            echo  json_encode($result);

        }else{


            $this->response->setOutput($this->load->view('entegrasyon/setting/account/register_form', $data));


        }
    }
    public function account_info()
    {

        $this->document->setTitle('Entegrasyon Ayarları');

        $this->model_entegrasyon_general->loadPageRequired();


        $data['username']= $this->config->get('mir_username');
        $data['token_link'] = $this->token_data['token_link'];

        $this->load->model('entegrasyon/general');
        $marketplaces = $this->model_entegrasyon_general->getMarketPlaces();

        $data['marketplaces']=array();

        foreach ($marketplaces as $marketplace) {
            $last_date=strtotime($marketplace['end_date']);
            $todaday =strtotime(date('Y-m-d'));
            $left_day=($last_date-$todaday)/86400;
            $marketplace['left_day']=floor($left_day);
            $marketplace['left_day']=$marketplace['left_day']<0 ? 0:$marketplace['left_day'];
            $data['marketplaces'][]=$marketplace;
        }
        $data['token_link'] = $this->token_data['token_link'];

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('entegrasyon/setting//account/account_info', $data));


    }

    public function cart()
    {

        $data['products']=array();

        if(isset($this->session->data['marketplaces'])){
            $marketplaces=$this->session->data['marketplaces'];
        }else {
            $marketplaces=array();

        }


        foreach ($marketplaces as $marketplace) {

            $data['products'][]=array(
                'code'=>$marketplace['code'],
                'name'=>$marketplace['name'],
                'price'=>'700 TL',
                'period'=> "1 Yıl"
            );
        }

        $total=count($data['products']);
        if($total==1){
            $data['total']=700;
            $data['list_price']=700;
            $data['discount']=0;
        }else if($total==2){
            $data['total']=1250;
            $data['list_price']=1500;
            $data['discount']=250;
        }else if($total==3){
            $data['total']=1750;
            $data['list_price']=2250;
            $data['discount']=500;
        }else if($total==4){
            $data['total']=2300;
            $data['list_price']=3000;
            $data['discount']=700;
        }else if($total==5){
            $data['total']=2600;
            $data['list_price']=3750;
            $data['discount']=1150;
        }else if($total==6){
            $data['total']=3000;
            $data['list_price']=4500;
            $data['discount']=1500;
        }else if($total==7){
            $data['total']=2600;
            $data['list_price']=3500;
            $data['discount']=900;
        }


        $this->response->setOutput($this->load->view('entegrasyon/setting//account/cart', $data));

    }

    public function paid()
    {

        $url='https://www.easyentegre.com/odeme.html?m=';
        $query='';
        $markets=array();
        $domain_id=0;
        $password=$this->config->get('mir_password');
        foreach ($this->session->data['marketplaces'] as $marketplace) {
            $markets[]=$marketplace['code'];
            $domain_id=$marketplace['domain_id'];


        }
        $query.=implode(',',$markets);
        $query.='&domain_id='.$domain_id.'&password='.$password;
        $query=base64_encode($query);
        $url.=$query;


        echo json_encode(array('status'=>true,'url'=>$url));

    }

    public function add_to_cart()
    {
        // unset($this->session->data['marketplaces']);
        $code=$this->request->post['code'];

        $this->load->model('entegrasyon/general');
        if(isset($this->session->data['marketplaces'])){


            if(!key_exists($code,$this->session->data['marketplaces'])){
                $this->session->data['marketplaces'][$code]=$this->model_entegrasyon_general->getMarketPlace($code);
            }

        }else {
            $this->session->data['marketplaces'][$code]=$this->model_entegrasyon_general->getMarketPlace($code);


        }

        echo json_encode(array('status'=>true));

    }

    public function delete_from_cart()
    {
        $code=$this->request->post['code'];

        unset( $this->session->data['marketplaces'][$code]);

        echo json_encode(array('status'=>true));

    }



    public function logout()
    {
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('mir');
        $this->response->redirect($this->url->link('entegrasyon/setting', $this->token_data['token_link'], true));

    }



    public function marketplaces()
    {

        $this->load->model('entegrasyon/general');
        $data['token_link'] = $this->token_data['token_link'];
        $data['permission']=$this->model_entegrasyon_general->checkPermission();
        $data['easy_visibility']=$this->config->get('easy_visibility') ? '':'hidden';
        $marketplaces = $this->model_entegrasyon_general->getMarketPlaces();

        $data['marketplaces']=array();

        foreach ($marketplaces as $marketplace) {
            $last_date=strtotime($marketplace['end_date']);
            $todaday =strtotime(date('Y-m-d'));
            $left_day=($last_date-$todaday)/86400;
            $marketplace['left_day']=floor($left_day);
            $marketplace['left_day']=$marketplace['left_day']<0 ? 0:$marketplace['left_day'];
            $data['marketplaces'][]=$marketplace;
        }
        $this->response->setOutput($this->load->view('entegrasyon/setting/marketplaces', $data));

    }


    public function main()
    {
        $this->load->language('entegrasyon/updater');
        //$markets=unserialize($this->config->get('mir_marketplaces'));
        //  print_r($markets);
        //     return;

        $data['permission']=$this->model_entegrasyon_general->checkPermission();

        if(isset($this->request->get['form'])){
            $form=$this->request->get['form'];
        }else {
            $form = 'login';
        }



        $data = $this->language->all();


        $data['token_link'] = $this->token_data['token_link'];
        $data['easy_visibility']=$this->config->get('easy_visibility') ? '':'hidden';
        if($this->config->get('mir_login')){
            $this->load->model('entegrasyon/general');
            $data['marketplaces'] = $this->model_entegrasyon_general->getMarketPlaces();

            $data['entegrasyon_version'] = $this->config->get('module_entegrasyon_version');

            $data['tool_link'] = $this->url->link('entegrasyon/setting/tool', $this->token_data['token_link'], true);
            $data['order_link'] = $this->url->link('entegrasyon/order', $this->token_data['token_link'], true);
            $data['category_link'] = $this->url->link('entegrasyon/category', $this->token_data['token_link'], true);

            $data['orders_cron']=HTTPS_CATALOG.'index.php?route=entegrasyon/update/orders&mode=auto';
            // $data['update_cron']=HTTPS_CATALOG.'index.php?route=entegrasyon/update/product';
            $data['question_cron']=HTTPS_CATALOG.'index.php?route=entegrasyon/update/questions';
            $this->response->setOutput($this->load->view('entegrasyon/setting/main', $data));

        }else {


            $this->response->setOutput($this->load->view('entegrasyon/setting/account/'.$form.'_form', $data));

        }


    }

    public function unmatch_all()
    {
        $code = $this->request->get['code'];

        $this->db->query("update ".DB_PREFIX."es_product_to_marketplace SET $code='' ");
        $this->db->query("update ".DB_PREFIX."es_market_product SET oc_product_id=0 where code='".$code."' ");

        echo json_encode(array('status' => true));
    }
    public function sync_form()
    {

        $code = $this->request->get['code'];
        $this->load->model('entegrasyon/product/' . $code);
        $this->load->model('entegrasyon/general');

        $products = $this->{"model_entegrasyon_product_" . $code}->getProducts(array('itemcount' => 1, 'page' => 0));



        $data['total'] = $products['total'];
        $data['token_link'] = $this->token_data['token_link'];
        $data['code'] = $code;
        $marketPlace=$this->model_entegrasyon_general->getMarketPlace($code);
        $data['message']=$products['message'];
        $data['marketplace']=$marketPlace['name'];
        $data['info'] = $marketPlace['name'] . ' Mağazanızda yer alan tüm ürün bilgileri aşağıda listelenmiştir. Seknronizasyon işlemi ' . $marketPlace['name'] . ' ile web sitenizde yer alan ürünleri ürün kodlarına göre karşılaştırıp eşleştirir. Senkronizasyon işlemi herhangi bir stok yada fiyat güncellemesi yapmaz.';

        $this->response->setOutput($this->load->view('entegrasyon/setting/sync_form', $data));

    }


    public function sync()
    {

        $code = $this->request->get['code'];
        $json = array();
        if (isset($this->request->get['total'])) {
            $total = $this->request->get['total'];
        } else {
            $total = 0;
        }

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 0;
        }


        if ($page == 0){
            $this->db->query("update ".DB_PREFIX."es_product_to_marketplace SET $code='' ");
            $this->db->query("update ".DB_PREFIX."es_market_product SET oc_product_id=0 where code='".$code."' ");

        }


        $debug=false;
        if (isset($this->request->get['debug'])) {
            $debug=true;

        }


        $matched_products = array();
        $matched = (int)$this->request->get['matched'];
        $unMatched = (int)$this->request->get['unmatched'];
        if($code=='hb'){

            $limit=100;
        }else if($code=='cs') {
            $limit=55;
        }else {

            $limit=100;
        }

        $this->load->model('entegrasyon/product/' . $code);
        // $debug=true;
        $products = $this->{"model_entegrasyon_product_" . $code}->getProducts(array('itemcount' => $limit, 'page' => $page,$debug));



        if($total){
            $datam['total'] = $total;
        }else if($products['status']){

            $datam['total'] = $products['total'];

        }else {
            $datam['total'] = 0;

        }

        $totalpage = ceil($datam['total'] / $limit);


        /*  if ($code == 'eptt') {
               $totalpage = 1;

           }*/

        $render_product=array();
        if($code=='eptt'){

            $start=$page*$limit;
            $a=0;
            for ($i=$start;$i<count($products['products']);$i++){

                $render_product[]=$products['products'][$i];
                $a++;
                if($a==$limit)break;
            }

        }else {
            $render_product=$products['products'];
        }


        foreach ($render_product as $key => $product) {
            $commission = 0;

            $product_name=isset($product['name'])?$product['name']:'';


            $product_info = $this->entegrasyon->getProductByModel($product['model'],$product['barcode'],$product_name,$this->config->get($code.'_setting_model_prefix'),$this->config->get($code.'_setting_barkod_place'),$product['stock_code'],$code);


            if(!$product_info){



                $product_infos = $this->entegrasyon->getProductByModelFromProductSetting($code,$product['model']);


                foreach ($product_infos as $item) {

                    $url = $this->entegrasyon->getMarketPlaceUrl($code, $product['market_id']);
                    $data = array('commission'=>$commission, 'sale_status'=>$product['sale_status'],'approval_status'=>$product['approval_status'],'barcode'=>$product['barcode'],'product_id' => $product['market_id'], 'price' => number_format($product['sale_price'], 2), 'url' => $url);
                    $this->entegrasyon->addMarketplaceProduct($item['product_id'], $data, $code);
                    $matched_products[] = $item['name'];
                    $matched++;

                }

            }


            if(!$product_info){
                $sql = "select product_id from ".DB_PREFIX."es_product_variant where model='".$this->db->escape($product['stock_code'])."' or barcode='".$this->db->escape($product['barcode'])."' ";

                if($this->config->get($code.'_setting_model_prefix')){

                    $model_without_prefix_array=explode($this->config->get($code.'_setting_model_prefix'),$product['model'],2);
                    $stock_without_prefix_array=explode($this->config->get($code.'_setting_model_prefix'),$product['stock_code'],2);
                    $barcode_without_prefix_array=explode($this->config->get($code.'_setting_model_prefix'),$product['barcode'],2);

                    if(count($model_without_prefix_array)==2){
                        $sql.=" or model='".$this->db->escape($model_without_prefix_array[1])."' ";
                    }

                    if(count($stock_without_prefix_array)==2){
                        $sql.=" or model='".$this->db->escape($stock_without_prefix_array[1])."' ";
                    }

                    if(count($barcode_without_prefix_array)==2){
                        $sql.=" or barcode='".$this->db->escape($barcode_without_prefix_array[1])."' ";
                    }

                }

                $query = $this->db->query($sql);
                if($query->num_rows){

                    $product_info=$this->entegrasyon->getProduct($query->row['product_id']);
                }

            }

            if ($product_info) {
                $oc_price = $product_info['special'] ? $product_info['special'] : $product_info['price'];
                $oc_price = $this->tax->calculate($oc_price, $product_info['tax_class_id'], true);


                if ($oc_price < $product['sale_price']) {
                    if((int)$oc_price){
                        $commission = (($product['sale_price'] - $oc_price) * 100) / $oc_price;
                    }

                } else {

                    $commission = 0;
                }

                $matched_products[] = $product_info['name'];
                $matched++;

                $url = $this->entegrasyon->getMarketPlaceUrl($code, $product['market_id']);


                $data = array('commission'=>$commission, 'sale_status'=>$product['sale_status'],'approval_status'=>$product['approval_status'],'barcode'=>$product['barcode'],'product_id' => $product['market_id'], 'price' => number_format($product['sale_price'], 2), 'url' => $url);

                if($code=='n11'){

                    $data['stock_id']=$product['stock_id'];
                }






                $get_product_to_marketplace_data_query=$this->db->query("select * from ".DB_PREFIX."es_product_to_marketplace where product_id='".$product_info['product_id']."' and $code !=''");

                if($get_product_to_marketplace_data_query->num_rows){

                    if($get_product_to_marketplace_data_query->row[$code]){

                        $marketplace_data=unserialize($get_product_to_marketplace_data_query->row[$code]);

                        if(!$marketplace_data['sale_status'] && $data['sale_status']){

                            $this->entegrasyon->addMarketplaceProduct($product_info['product_id'], $data, $code);

                        }

                    }

                } else {

                    $this->entegrasyon->addMarketplaceProduct($product_info['product_id'], $data, $code);

                }

            } else {

                $unMatched++;
            }




        }
        $page++;


        $url = 'index.php?route=entegrasyon/setting/sync&page=' . $page . '&total=' . $datam['total'] . '&code=' . $code . '&matched=' . $matched . '&unmatched=' . $unMatched . '&'.$this->token_data['token_link'];
        $json['matched'] = $matched;
        $json['unmatched'] = $unMatched;
        $json['page'] = $page;
        $json['total_page'] = $totalpage;
        $json['current_page'] = $page;
        $json['matched_products'] = $matched_products;


        if (($page) * $limit <= $datam['total']) {

            $json['status'] = true;
            $json['next'] = $url;

        } else {
            $json['message'] = 'Tamamlandı';
        }

        echo json_encode($json);


    }



    public function get_products_form()
    {


        $code = $this->request->get['code'];
        $this->load->model('entegrasyon/product/'.$code);

        $this->load->model('entegrasyon/general');

        $products = $this->{"model_entegrasyon_product_".$code}->getProducts(array('itemcount' => 1, 'page' => 0));
        
        $query=$this->db->query("select count(*) as total from ".DB_PREFIX."es_market_product where code='".$code."'");

        $data['downloaded'] = $query->row['total'];

        $query2=$this->db->query("select count(*) as total from ".DB_PREFIX."es_market_product where code='".$code."' and oc_product_id != 0");
        $data['matched'] = $query2->row['total'];


        $query3=$this->db->query("select count(*) as total from ".DB_PREFIX."es_market_product where code='".$code."' and oc_product_id =0");
        $data['unmatched'] = $query3->row['total'];
        $data['total'] = $products['total'];
        $data['token_link'] = $this->token_data['token_link'];
        $data['code'] = $code;
        $marketPlace=$this->model_entegrasyon_general->getMarketPlace($code);
        $data['message']=$products['message'];
        $data['marketplace']=$marketPlace['name'];
        $data['info'] = $marketPlace['name'] . ' Mağazanızda yer alan tüm ürün bilgileri aşağıda listelenmiştir. Seknronizasyon işlemi ' . $marketPlace['name'] . ' ile web sitenizde yer alan ürünleri ürün kodlarına göre karşılaştırıp eşleştirir. Senkronizasyon işlemi herhangi bir stok yada fiyat güncellemesi yapmaz.';

        $this->response->setOutput($this->load->view('entegrasyon/setting/get_products_form', $data));

    }


    public function delete_market_products()
    {
        $code=$this->request->get['code'];
        $this->db->query("delete from ".DB_PREFIX."es_market_product where code='".$code."'");

        $code = $this->request->get['code'];

        $this->db->query("update ".DB_PREFIX."es_product_to_marketplace SET $code='' ");
        $this->db->query("update ".DB_PREFIX."es_market_product SET oc_product_id=0 where code='".$code."' ");


        echo json_encode(array('status'=>true));
    }


    public function get_products_progress()
    {

        $code = $this->request->get['code'];
        $json = array();
        if (isset($this->request->get['total'])) {
            $total = $this->request->get['total'];
        } else {
            $total = 0;
        }

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 0;
        }


        $debug=false;
        if (isset($this->request->get['debug'])) {
            $debug=true;

        }
        $pasif = false;
        if (isset($this->request->get['pasif'])) {
            $pasif=true;

        }

        $downloaded = (int)$this->request->get['downloaded'];
        $updated = (int)$this->request->get['updated'];
        $matched = (int)$this->request->get['matched'];
        $unmatched = (int)$this->request->get['unmatched'];
        $totaldownloaded=(int)$this->request->get['totalDownloaded'];
        if($code=='cs') {
            $limit=55;
        }else {

            $limit=100;
        }

        $this->load->model('entegrasyon/product/' . $code);
        // $debug=true;
        $products = $this->{"model_entegrasyon_product_" . $code}->getProducts(array('itemcount' => $limit, 'page' => $page,$debug),$debug,$pasif);



        if($total){
            $datam['total'] = $total;
        }else if($products['status']){

            $datam['total'] = $products['total'];

        }else {
            $datam['total'] = 0;

        }

        $totalpage = ceil($datam['total'] / $limit);


        /*  if ($code == 'eptt') {
               $totalpage = 1;

           }*/

        $render_product=array();
        if($code=='eptt'){

            $start=$page*$limit;
            $a=0;
            for ($i=$start;$i<count($products['products']);$i++){

                $render_product[]=$products['products'][$i];
                $a++;
                if($a==$limit)break;
            }

        }else {
            $render_product=$products['products'];
        }

        $json['unmatchedProductInfo']=array();
        foreach ($render_product as $product) {
            if (!$product['stock_code']){ $product['stock_code'] = $product['barcode'];}

            $result = $this->entegrasyon->addMarketProduct($product['stock_code'], $product, $code);


            if($result['downloaded']){
                $downloaded++;
                $totaldownloaded++;
            }
            if($result['updated']){
                $updated++;
            }

            if($result['matched']){
                $matched++;
            }else {

                if ($code == "ty" || $code == "hb" ){


                    $json['unmatchedProductInfo'][] = array(
                        $product['stock_code']
                    );
                }else{


                    $json['unmatchedProductInfo'][] = array(
                        $product['name']." - ". $product['stock_code']
                    );
                }
                $unmatched++;
            }

        }


        $page++;



        $url = 'index.php?route=entegrasyon/setting/get_products_progress&page=' . $page . '&total=' . $datam['total'] . '&code=' . $code . '&totalDownloaded='.$totaldownloaded.'&matched='.$matched.'&unmatched='.$unmatched.'&downloaded=' . $downloaded . '&updated='.$updated.'&'.$this->token_data['token_link'];
        $json['downloaded'] =((int)$downloaded);
        $json['updated'] =((int)$updated);
        $json['matched'] =((int)$matched);
        $json['unmatched'] =((int)$unmatched);
        $json['totaldownloaded'] =((int)$totaldownloaded);
        $json['page'] = $page;
        $json['total_page'] = $totalpage;
        $json['current_page'] = $page;


        if (($page) * $limit <= $datam['total']) {

            $json['status'] = true;
            $json['next'] = $url;

        } else {
            $json['message'] = 'Tamamlandı';
        }

        echo json_encode($json);


    }




    public function edit_n11()
    {


        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {


            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting('n11_setting', $this->request->post);
            echo json_encode(array('status' => true));
            return;
        }

        $data['token_link'] = $this->token_data['token_link'];
        $data['n11_setting_maximum_order'] = $this->config->get('n11_setting_maximum_order');

        $data['n11_setting_shipping_time'] = $this->config->get('n11_setting_shipping_time');
        $data['n11_setting_shipping_template'] = $this->config->get('n11_setting_shipping_template');
        $data['n11_setting_commission'] =  str_replace("%","",$this->config->get('n11_setting_commission'));
        $data['n11_setting_subtitle'] = $this->config->get('n11_setting_subtitle');
        $data['n11_setting_product_attribute'] = $this->config->get('n11_setting_product_attribute');
        $data['n11_setting_kdv_setting'] = $this->config->get('n11_setting_kdv_setting');
        $data['n11_setting_option_setting'] = $this->config->get('n11_setting_option_setting');
        $data['n11_setting_additional_content'] = $this->config->get('n11_setting_additional_content');
        $data['n11_setting_product_special'] = $this->config->get('n11_setting_product_special');
        $data['n11_setting_shipping_price'] = $this->config->get('n11_setting_shipping_price');
        $data['n11_setting_model_prefix'] = $this->config->get('n11_setting_model_prefix');
        $data['n11_setting_variant'] = $this->config->get('n11_setting_variant');
        $data['n11_setting_barkod_place'] = $this->config->get('n11_setting_barkod_place');
        $data['n11_setting_add_tc'] = $this->config->get('n11_setting_add_tc');
        $data['n11_setting_add_tax'] = $this->config->get('n11_setting_add_tax');
        $data['n11_setting_add_tax_val'] = $this->config->get('n11_setting_add_tax_val');
        $data['n11_setting_domestic'] = $this->config->get('n11_setting_domestic');
        $data['n11_setting_product_category'] = $this->config->get('n11_setting_product_category');
        $data['n11_setting_product_iscurrency'] = $this->config->get('n11_setting_product_iscurrency');
        $data['n11_setting_oc_order'] = $this->config->get('n11_setting_oc_order');
        $data['n11_setting_customer_group'] = $this->config->get('n11_setting_customer_group');
        $data['barkod_places']=array('sku','mpn','upc','jan','ean','isbn');
        $data['n11_setting_price_multiplier'] = $this->config->get('n11_setting_price_multiplier');

        $this->load->model('catalog/information');
        $data['informations'] = $this->model_catalog_information->getInformations();



        $post_data['request_data']=array();
        $tempalates =$this->entegrasyon->clientConnect($post_data,'shipping_templates','n11');

        $data['shipping_templates']=$tempalates['result'];


        $this->response->setOutput($this->load->view('entegrasyon/setting/mainsetting/n11', $data));


    }


    public function edit_gg()
    {

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting('gg_setting', $this->request->post);
            echo json_encode(array('status' => true));
            return;
        }

        $data['token_link'] = $this->token_data['token_link'];
        $data['gg_setting_city'] = $this->config->get('gg_setting_city');
        $data['gg_setting_shipping_time'] = $this->config->get('gg_setting_shipping_time');
        $data['gg_setting_shipping_template'] = $this->config->get('gg_setting_shipping_template');
        $data['gg_setting_commission'] =  str_replace("%","",$this->config->get('gg_setting_commission'));
        $data['gg_setting_subtitle'] = $this->config->get('gg_setting_subtitle');
        $data['gg_setting_product_attribute'] = $this->config->get('gg_setting_product_attribute');
        $data['gg_setting_kdv_setting'] = $this->config->get('gg_setting_kdv_setting');
        $data['gg_setting_shipping_price'] = $this->config->get('gg_setting_shipping_price');
        $data['gg_setting_extra_shipping_price'] = $this->config->get('gg_setting_extra_shipping_price');
        $data['gg_setting_hour'] = $this->config->get('gg_setting_hour');
        $data['gg_setting_minute'] = $this->config->get('gg_setting_minute');
        $data['gg_setting_show_time'] = $this->config->get('gg_setting_show_time');
        $data['gg_setting_shipping_company'] = $this->config->get('gg_setting_shipping_company');
        $data['gg_setting_additional_content'] = $this->config->get('gg_setting_additional_content');
        $data['gg_setting_product_special'] = $this->config->get('gg_setting_product_special');
        $data['gg_setting_model_prefix'] = $this->config->get('gg_setting_model_prefix');
        $data['gg_setting_variant'] = $this->config->get('gg_setting_variant');
        $data['gg_setting_add_tc'] = $this->config->get('gg_setting_add_tc');
        $data['gg_setting_barkod_place'] = $this->config->get('gg_setting_barkod_place');
        $data['gg_setting_product_category'] = $this->config->get('gg_setting_product_category');
        $data['gg_setting_product_iscurrency'] = $this->config->get('gg_setting_product_iscurrency');
        $data['gg_setting_oc_order'] = $this->config->get('gg_setting_oc_order');
        $data['gg_setting_customer_group'] = $this->config->get('gg_setting_customer_group');
        $data['gg_setting_pay_in_the_basket'] = $this->config->get('gg_setting_pay_in_the_basket');
        $data['gg_setting_default_variant_color'] = $this->config->get('gg_setting_default_variant_color');
        $data['gg_setting_product_status'] = $this->config->get('gg_setting_product_status');
        $data['barkod_places']=array('sku','mpn','upc','jan','ean','isbn');
        $data['gg_setting_price_multiplier'] = $this->config->get('gg_setting_price_multiplier');

        $this->load->model('catalog/information');
        $data['informations'] = $this->model_catalog_information->getInformations();

        $data['hours'] = array('00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23');
        $data['minutes'] = array('00', '15', '30', '45');


        $this->load->model('entegrasyon/general');

        $data['cargo_company'] = array('aras' => 'Aras Kargo', 'mng' => 'MNG Kargo', 'yurtici' => 'Yurtiçi Kargo', 'ptt' => 'Ptt Kargo', 'surat' => 'Sürat Kargo', 'ups' => 'UPS', 'other' => 'Diğer');


        $data['cities'] = array();

        $post_data['request_data']=array();


        $get_cities = $this->entegrasyon->clientConnect($post_data, 'get_cities', 'gg',false);



        foreach ($get_cities['result']['cities']['city'] as $city) {
            //print_r($city);
            $data['cities'][] = $city;
        }

        $data['sablonlar'] = array('S' => 'Satıcı Öder', 'B' => 'Alıcı Öder', 'D' => 'İndirilebilir Ürün');// Key ler bulunacak

        $data['show_time'] = array('30' => '30 Gün', '60' => '60 Gün ', '180' => '180 Gün ', '360' => '360 Gün ');


        $this->response->setOutput($this->load->view('entegrasyon/setting/mainsetting/gg', $data));


    }

    public function edit_cs()
    {



        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting('cs_setting', $this->request->post);
            echo json_encode(array('status' => true));
            return;
        }

        $data['token_link'] = $this->token_data['token_link'];
        $data['cs_setting_commission'] =  str_replace("%","",$this->config->get('cs_setting_commission'));
        $data['cs_setting_product_attribute'] = $this->config->get('cs_setting_product_attribute');
        $data['cs_setting_kdv_setting'] = $this->config->get('cs_setting_kdv_setting');
        $data['cs_setting_additional_content'] = $this->config->get('cs_setting_additional_content');
        $data['cs_setting_product_special'] = $this->config->get('cs_setting_product_special');
        $data['cs_setting_shipping_price'] = $this->config->get('cs_setting_shipping_price');
        $data['cs_setting_model_prefix'] = $this->config->get('cs_setting_model_prefix');
        $data['cs_setting_variant'] = $this->config->get('cs_setting_variant');
        $data['cs_setting_add_tc'] = $this->config->get('cs_setting_add_tc');
        $data['cs_setting_barkod_place'] = $this->config->get('cs_setting_barkod_place');
        $data['cs_setting_main_product_id'] = $this->config->get('cs_setting_main_product_id');
        $data['cs_setting_product_category'] = $this->config->get('cs_setting_product_category');
        $data['cs_setting_product_iscurrency'] = $this->config->get('cs_setting_product_iscurrency');
        $data['cs_setting_oc_order'] = $this->config->get('cs_setting_oc_order');
        $data['cs_setting_delivery_message_type'] = $this->config->get('cs_setting_delivery_message_type');
        $data['cs_setting_delivery_type'] = $this->config->get('cs_setting_delivery_type');
        $data['cs_setting_customer_group'] = $this->config->get('cs_setting_customer_group');
        $data['barkod_places']=array('sku','mpn','upc','jan','ean','isbn');
        $data['cs_setting_price_multiplier'] = $this->config->get('cs_setting_price_multiplier');



        $this->load->model('catalog/information');
        $data['informations'] = $this->model_catalog_information->getInformations();

        $data['deliveryTypes']=array(1=>'Servis Aracı İle Gönderim',2=>'Kargo İle Gönderim',3=>'Kargo+Servis Aracı İle Gönderim');
        $data['deliveryMessageTypes']=array(

            4=>'Hediye Kargo Aynı Gün',
            5=>'Hediye Kargo 1-3 İs Günü',
            6=>'Hediye Kargo 1-5 Is Günü',
            7=>'Hediye Kargo 1-7 İs Günü',
            13=>'Hediye Kargo 3-5 İs Günü',
            18=>'Hediye Kargo 1-2 İs Günü',
            10 =>'Gurme Kargo Aynı Gün',
            20=>'Gurme Kargo 1-2 İs Günü',
            12=>'Gurme Kargo 1-3 İs Günü',
            22=>'Gurme Kargo 1-5 Is Günü'

        );

        $this->response->setOutput($this->load->view('entegrasyon/setting/mainsetting/cs', $data));


//  print_r($data);

    }


    public function edit_ty()
    {


        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {


            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting('ty_setting', $this->request->post);
            echo json_encode(array('status' => true));
            return;
        }

        $data['token_link'] = $this->token_data['token_link'];
        $data['ty_setting_commission'] =  str_replace("%","",$this->config->get('ty_setting_commission'));
        $data['ty_setting_color'] = $this->config->get('ty_setting_color');
        $data['ty_setting_product_attribute'] = $this->config->get('ty_setting_product_attribute');
        $data['ty_setting_kdv_setting'] = $this->config->get('ty_setting_kdv_setting');
        $data['ty_setting_additional_content'] = $this->config->get('ty_setting_additional_content');
        $data['ty_setting_product_special'] = $this->config->get('ty_setting_product_special');
        $data['ty_setting_shipping_price'] = $this->config->get('ty_setting_shipping_price');
        $data['ty_setting_model_prefix'] = $this->config->get('ty_setting_model_prefix');
        $data['ty_setting_variant'] = $this->config->get('ty_setting_variant');
        $data['ty_setting_barkod_place'] = $this->config->get('ty_setting_barkod_place');
        $data['ty_setting_main_product_id'] = $this->config->get('ty_setting_main_product_id');
        $data['ty_setting_product_category'] = $this->config->get('ty_setting_product_category');
        $data['ty_setting_product_iscurrency'] = $this->config->get('ty_setting_product_iscurrency');
        $data['ty_setting_oc_order'] = $this->config->get('ty_setting_oc_order');
        $data['ty_setting_customer_group'] = $this->config->get('ty_setting_customer_group');
        $data['ty_setting_add_tc'] = $this->config->get('ty_setting_add_tc');
        $data['ty_setting_shipping_company'] = $this->config->get('ty_setting_shipping_company');
        $data['ty_setting_returning_address'] = $this->config->get('ty_setting_returning_address');
        $data['ty_setting_shipping_address'] = $this->config->get('ty_setting_shipping_address');
        $data['ty_setting_price_multiplier'] = $this->config->get('ty_setting_price_multiplier');



        $data['barkod_places']=array('sku','mpn','upc','jan','ean','isbn');


        $kargo='[{"id":1,"name":"Yurtiçi Kargo","code":"YK","taxNumber":"9860008925"},{"id":2,"name":"MNG Kargo","code":"MNG","taxNumber":"6080712084"},{"id":3,"name":"Aras Kargo","code":"ARAS","taxNumber":"0720039666"},{"id":4,"name":"Yurtiçi Kargo Marketplace","code":"YKMP","taxNumber":"3130557669"},{"id":5,"name":"Aynı Gün Teslimat","code":"AGT","taxNumber":"6090414309"},{"id":6,"name":"Horoz Kargo Marketplace","code":"HOROZMP","taxNumber":"4630097122"},{"id":7,"name":"Aras Kargo Marketplace","code":"ARASMP","taxNumber":"0720039666"},{"id":8,"name":"Yurtiçi Kargo International","code":"INTYK","taxNumber":"9860008925"},{"id":9,"name":"Sürat Kargo Marketplace","code":"SURATMP","taxNumber":"7870233582"},{"id":10,"name":"MNG Kargo Marketplace","code":"MNGMP","taxNumber":"6080712084"},{"id":11,"name":"Trendyol Lojistik","code":"TEX","taxNumber":"8590921777"},{"id":12,"name":"UPS Kargo Marketplace","code":"UPSMP","taxNumber":"9170014856"},{"id":13,"name":"AGT Marketplace","code":"AGTMP","taxNumber":"6090414309"},{"id":14,"name":"Cainiao Marketplace","code":"CAIMP","taxNumber":"0"},{"id":15,"name":"PTT Kargo International","code":"PTTINT","taxNumber":"7320068060"},{"id":16,"name":"Alternative Delivery","code":"ADEL","taxNumber":"0"},{"id":17,"name":"Trendyol Express Marketplace","code":"TEXMP","taxNumber":"8590921777"},{"id":18,"name":"B2CDirect","code":"B2C","taxNumber":"1270476364"},{"id":19,"name":"PTT Kargo Marketplace","code":"PTTMP","taxNumber":"7320068060"},{"id":20,"name":"CEVA Marketplace","code":"CEVAMP","taxNumber":"8450298557"},{"id":21,"name":"Alljoy Lojistics","code":"ALLJOY","taxNumber":"0551206401"},{"id":22,"name":"B2C Lojistics","code":"B2CL","taxNumber":"1270476364"},{"id":23,"name":"ARAMEX","code":"ARAMEX","taxNumber":"0710094760"},{"id":24,"name":"PTS","code":"PTS","taxNumber":"7190041528"},{"id":25,"name":"UPS Kargo","code":"UPS","taxNumber":"9170014856"},{"id":26,"name":"MIGROS","code":"MIGROS","taxNumber":"6220529513"},{"id":27,"name":"PTT","code":"PTT","taxNumber":"7320068060"},{"id":28,"name":"Trendyol Kurye","code":"TYKMP","taxNumber":"3131188775"},{"id":29,"name":"Aykargo","code":"AYK","taxNumber":"5700267300"},{"id":30,"name":"Borusan Lojistik Marketplace","code":"BORMP","taxNumber":"1800038254"},{"id":31,"name":"Surat Kargo","code":"SURAT","taxNumber":"7870233582"}]';
        $data['cargo_company'] = json_decode($kargo,1);


        $post_data['request_data']=array();
        $data['address'] =$this->entegrasyon->clientConnect($post_data,'shipping_templates','ty');


        $this->load->model('catalog/information');
        $data['informations'] = $this->model_catalog_information->getInformations();



        $this->response->setOutput($this->load->view('entegrasyon/setting/mainsetting/ty', $data));


        //  print_r($data);

    }


    public function edit_hb()
    {


        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {


            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting('hb_setting', $this->request->post);
            echo json_encode(array('status' => true));
            return;
        }

        $data['token_link'] = $this->token_data['token_link'];
        $data['hb_setting_commission'] =  str_replace("%","",$this->config->get('hb_setting_commission'));
        $data['hb_setting_shipping_time'] = $this->config->get('hb_setting_shipping_time');
        $data['hb_setting_maximum_order'] = $this->config->get('hb_setting_maximum_order');
        $data['hb_setting_product_attribute'] = $this->config->get('hb_setting_product_attribute');
        $data['hb_setting_kdv_setting'] = $this->config->get('hb_setting_kdv_setting');
        $data['hb_setting_additional_content'] = $this->config->get('hb_setting_additional_content');
        $data['hb_setting_product_special'] = $this->config->get('hb_setting_product_special');
        $data['hb_setting_shipping_price'] = $this->config->get('hb_setting_shipping_price');
        $data['hb_setting_model_prefix'] = $this->config->get('hb_setting_model_prefix');
        $data['hb_setting_variant'] = $this->config->get('hb_setting_variant');
        $data['hb_setting_update_value'] = $this->config->get('hb_setting_update_value');
        $data['hb_setting_barkod_place'] = $this->config->get('hb_setting_barkod_place');
        $data['hb_setting_add_tc'] = $this->config->get('hb_setting_add_tc');
        $data['hb_setting_product_category'] = $this->config->get('hb_setting_product_category');
        $data['hb_setting_product_iscurrency'] = $this->config->get('hb_setting_product_iscurrency');
        $data['hb_setting_oc_order'] = $this->config->get('hb_setting_oc_order');
        $data['hb_setting_customer_group'] = $this->config->get('hb_setting_customer_group');
        $data['hb_setting_auto_approve'] = $this->config->get('hb_setting_auto_approve');
        $data['barkod_places']=array('sku','mpn','upc','jan','ean','isbn');
        $data['hb_setting_price_multiplier'] = $this->config->get('hb_setting_price_multiplier');


        $this->load->model('catalog/information');
        $data['informations'] = $this->model_catalog_information->getInformations();

        $this->response->setOutput($this->load->view('entegrasyon/setting/mainsetting/hepsiburada', $data));


    }

    public function edit_easy()
    {



        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting('easy_setting', $this->request->post);
            echo json_encode(array('status' => true));
            return;
        }




        if ( strstr($this->config->get('easy_setting_img_domain'), '//')){
            $easy_setting_img_domain = $this->config->get('easy_setting_img_domain');
        }else{
            $easy_setting_img_domain = $this->config->get('easy_setting_img_domain')."/";

        }
        $data['token_link'] = $this->token_data['token_link'];
        $data['easy_setting_commission'] =  str_replace("%","",$this->config->get('easy_setting_commission'));
        $data['easy_setting_shipping_time'] = $this->config->get('easy_setting_shipping_time');
        $data['easy_setting_notification'] = $this->config->get('easy_setting_notification');
        $data['easy_setting_email'] = $this->config->get('easy_setting_email');
        $data['easy_setting_img_domain'] = $easy_setting_img_domain;
        $data['easy_setting_maximum_order'] = $this->config->get('easy_setting_maximum_order');
        $data['easy_setting_product_attribute'] = $this->config->get('easy_setting_product_attribute');
        $data['easy_setting_kdv_setting'] = $this->config->get('easy_setting_kdv_setting');
        $data['easy_setting_additional_content'] = $this->config->get('easy_setting_additional_content');
        $data['easy_setting_product_special'] = $this->config->get('easy_setting_product_special');
        $data['easy_setting_shipping_price'] = $this->config->get('easy_setting_shipping_price');
        $data['easy_setting_model_prefix'] = $this->config->get('easy_setting_model_prefix');
        $data['easy_setting_variant'] = $this->config->get('easy_setting_variant');
        $data['easy_setting_barkod_place'] = $this->config->get('easy_setting_barkod_place');
        $data['easy_setting_product_category'] = $this->config->get('easy_setting_product_category');
        $data['easy_setting_oc_order'] = $this->config->get('easy_setting_oc_order');
        $data['easy_setting_customer_group'] = $this->config->get('easy_setting_customer_group');
        $data['easy_setting_update_after_oc_sale'] = $this->config->get('easy_setting_update_after_oc_sale');
        $data['easy_setting_order_price_with_tax'] = $this->config->get('easy_setting_order_price_with_tax');
        $data['easy_setting_price_place'] = $this->config->get('easy_setting_price_place');
        $data['easy_setting_add_after_shipping_price'] = $this->config->get('easy_setting_add_after_shipping_price');
        $data['easy_setting_invisible_image_for_bulk'] = $this->config->get('easy_setting_invisible_image_for_bulk');
        $data['easy_critical_stock'] = $this->config->get('easy_critical_stock');
        $data['easy_setting_shipping_logo'] = $this->config->get('easy_setting_shipping_logo');
        $data['easy_setting_auto_update_price'] = $this->config->get('easy_setting_auto_update_price');
        $data['easy_setting_update_after_market_sale'] = $this->config->get('easy_setting_update_after_market_sale');
        $data['easy_setting_list_price'] = $this->config->get('easy_setting_list_price');
        $data['easy_setting_resize_image'] = $this->config->get('easy_setting_resize_image');
        $data['easy_setting_sms_notification'] = $this->config->get('easy_setting_sms_notification');
        $data['easy_setting_marketplace_row_in_orders'] = $this->config->get('easy_setting_marketplace_row_in_orders');
        $data['easy_setting_sms_numbers'] = $this->config->get('easy_setting_sms_numbers');
        $data['easy_setting_critical_stock'] = $this->config->get('easy_setting_critical_stock');
        $data['customer_groups']=$this->entegrasyon->getCustomerGroups();
        $data['price_places']=array('sku','mpn','upc','jan','ean','isbn');
        $data['easy_setting_store_category'] = $this->config->get('easy_setting_store_category');


        $data['store_categories']=array('Giyim','Halı Kilim & Perde','Mücevherat','Cinsel Ürünler','Kitap','Elektronik','Gıda','Hırdavat','Diğer');

        $this->load->model('catalog/information');
        $data['informations'] = $this->model_catalog_information->getInformations();

        $this->response->setOutput($this->load->view('entegrasyon/setting/mainsetting/easy', $data));


    }


    public function edit_eptt()
    {


        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {


            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting('eptt_setting', $this->request->post);
            echo json_encode(array('status' => true));
            return;
        }

        $data['token_link'] = $this->token_data['token_link'];
        $data['eptt_setting_commission'] =  str_replace("%","",$this->config->get('eptt_setting_commission'));
        $data['eptt_setting_product_special'] = $this->config->get('eptt_setting_product_special');

        $data['eptt_setting_product_attribute'] = $this->config->get('eptt_setting_product_attribute');
        $data['eptt_setting_kdv_setting'] = $this->config->get('eptt_setting_kdv_setting');
        $data['eptt_setting_additional_content'] = $this->config->get('eptt_setting_additional_content');
        $data['eptt_setting_shipping_price'] = $this->config->get('eptt_setting_shipping_price');
        $data['eptt_setting_model_prefix'] = $this->config->get('eptt_setting_model_prefix');
        $data['eptt_setting_variant'] = $this->config->get('eptt_setting_variant');
        $data['eptt_setting_barkod_place'] = $this->config->get('eptt_setting_barkod_place');
        $data['eptt_setting_product_category'] = $this->config->get('eptt_setting_product_category');
        $data['eptt_setting_product_iscurrency'] = $this->config->get('eptt_setting_product_iscurrency');
        $data['eptt_setting_add_tc'] = $this->config->get('eptt_setting_add_tc');
        $data['eptt_setting_oc_order'] = $this->config->get('eptt_setting_oc_order');
        $data['barkod_places']=array('sku','mpn','upc','jan','ean','isbn');
        $data['eptt_setting_customer_group'] = $this->config->get('eptt_setting_customer_group');
        $data['eptt_setting_price_multiplier'] = $this->config->get('eptt_setting_price_multiplier');

        $data['customer_groups']=$this->entegrasyon->getCustomerGroups();
        $this->load->model('catalog/information');
        $data['informations'] = $this->model_catalog_information->getInformations();
        $this->response->setOutput($this->load->view('entegrasyon/setting/mainsetting/eptt', $data));


    }


    public function n11()
    {

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

            $n11_api_key = $this->request->post['n11_api_key'];
            $n11_api_secret = $this->request->post['n11_api_secret'];

            $auth = array('appKey' => $n11_api_key, 'appSecret' => $n11_api_secret);
            $this->load->model('entegrasyon/general');

            $market=$this->model_entegrasyon_general->getMarketPlace('n11');
            $request_data = array();
            $post_data = array('market'=>$market,'api_info' => $auth, 'request_data' => $request_data);
            $result = $this->entegrasyon->clientConnect($post_data, 'check_api', 'n11',false);




            if ($result['status']) {

                $this->load->model('setting/setting');
                $this->model_setting_setting->editSetting('n11', $this->request->post);
                $market['status']=1;
                $this->model_entegrasyon_general->updateMarketPlace($market);

                if(!$this->model_setting_setting->getSetting('n11_setting')) {

                    $default_setting = $this->model_entegrasyon_general->n11_default_setting();
                    $this->model_setting_setting->editSetting('n11_setting', $default_setting);

                }
                echo json_encode(array('status' => true, 'message' => 'Api Bilgileriniz Başarıyla Doğrulanmıştır'));

                return;


            } else {

                echo json_encode(array('status' => false, 'message' => 'Api Bilgileriniz Doğrulanmadı, Hata Mesajı:'.$result['message']));

                return;
            }


        }

        $data['token_link'] = $this->token_data['token_link'];
        $data['n11_api_key'] = $this->config->get('n11_api_key');
        $data['n11_api_secret'] = $this->config->get('n11_api_secret');
        $data['n11'] = $this->config->get('n11');


        $this->response->setOutput($this->load->view('entegrasyon/setting/api/n11', $data));

    }


    public function eptt()
        //BU KISIM EPTT İÇİN TAMAMLANACAK..
    {



        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

            $status = false;
            $params = array(
                'username' => $this->request->post['eptt_kullanici_adi'],
                'password' => $this->request->post['eptt_api_parola'],
                'shopId' =>  $this->request->post['eptt_magaza_id']
            );

            $this->load->model('entegrasyon/general');

            $market=$this->model_entegrasyon_general->getMarketPlace('eptt');

            $post_data = array('market'=>$market,'api_info' => $params, 'request_data' => array());
            $result = $this->entegrasyon->clientConnect($post_data, 'check_api', 'eptt');
            if($result['status']){

                $this->load->model('setting/setting');
                $this->model_setting_setting->editSetting('eptt', $this->request->post);
                $market['status']=1;
                $this->model_entegrasyon_general->updateMarketPlace($market);
                echo json_encode(array('status' => true, 'message' => 'Api Bilgileriniz Doğrulandı'));
                return;
            }else{
                echo json_encode(array('status' => false, 'message' => 'Api Bilgileriniz Doğrulanmadı, Hata Mesajı:'.$result['message']));
                return;

            }



        }else {
            $data['token_link'] = $this->token_data['token_link'];
            $data['eptt_kullanici_adi'] = $this->config->get('eptt_kullanici_adi');
            $data['eptt_api_parola'] = $this->config->get('eptt_api_parola');
            $data['eptt_magaza_id'] = $this->config->get('eptt_magaza_id');
            $data['eptt_status'] = $this->config->get('eptt_status');
            $this->load->model('entegrasyon/general');

            $this->response->setOutput($this->load->view('entegrasyon/setting/api/eptt', $data));

        }


    }

    public function gg()
        //BU KISIM GİTTİGİDİYOR İÇİN TAMAMLANACAK..
    {

        $this->load->model('entegrasyon/general');
        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $params = array(
                'appKey' => $this->request->post['gg_api_anahtari'],
                'appSecret' => $this->request->post['gg_api_sifre'],
                'nick' => $this->request->post['gg_site_kullanici_adi'],
                'password' => $this->request->post['gg_site_kullanici_sifresi'],
                'auth_user' => $this->request->post['gg_role_kullanici_adi'],
                'auth_pass' => $this->request->post['gg_role_kullanici_sifresi'],
            );
            $this->load->model('entegrasyon/general');
            $market=$this->model_entegrasyon_general->getMarketPlace('gg');
            $post_data = array('market'=>$market,'api_info' => $params, 'request_data' => array());
            $result = $this->entegrasyon->clientConnect($post_data, 'check_api', 'gg');

            if ($result['status']) {
                $this->load->model('setting/setting');
                $this->model_setting_setting->editSetting('gg', $this->request->post);
                $market['status']=1;
                $this->model_entegrasyon_general->updateMarketPlace($market);

                if(!$this->model_setting_setting->getSetting('gg_setting')) {

                    $default_setting = $this->model_entegrasyon_general->gg_default_setting();
                    $this->model_setting_setting->editSetting('gg_setting', $default_setting);

                }


                echo json_encode(array('status' => true, 'message' => 'Api Bilgileriniz Doğrulanmadı, Lütfen Api bilgilerinizi kontrol ederek tekrar giriniz'));

            } else {

                echo json_encode(array('status' => false, 'message' => 'Api Bilgileriniz Doğrulanmadı, Hata Mesajı:'.$result['message']));

            }

            //echo json_encode($result);

        } else {

            $data['token_link'] = $this->token_data['token_link'];
            $data['gg_api_anahtari'] = $this->config->get('gg_api_anahtari');
            $data['gg_api_sifre'] = $this->config->get('gg_api_sifre');
            $data['gg_site_kullanici_adi'] = $this->config->get('gg_site_kullanici_adi');
            $data['gg_site_kullanici_sifresi'] = $this->config->get('gg_site_kullanici_sifresi');
            $data['gg_role_kullanici_adi'] = $this->config->get('gg_role_kullanici_adi');
            $data['gg_role_kullanici_sifresi'] = $this->config->get('gg_role_kullanici_sifresi');
            $data['gg'] = $this->config->get('gg');

            $this->response->setOutput($this->load->view('entegrasyon/setting/api/gg', $data));
        }


    }


    public function cs(){


        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {


            $cs_api_anahtari = $this->request->post['cs_api_anahtari'];
            $cs_satici_numarasi = $this->request->post['cs_satici_numarasi'];
            $params = array(
                'apiKey' => $cs_api_anahtari,
                'shopId' => $cs_satici_numarasi
            );
            $this->load->model('entegrasyon/general');

            $market=$this->model_entegrasyon_general->getMarketPlace('cs');
            $post_data = array('market'=>$market,'api_info' => $params, 'request_data' => array());

            $result = $this->entegrasyon->clientConnect($post_data, 'check_api', 'cs',false);


            if ($result['status']) {

                $this->load->model('setting/setting');
                $this->model_setting_setting->editSetting('cs', $this->request->post);
                $market['status']=1;
                $this->model_entegrasyon_general->updateMarketPlace($market);

                if(!$this->model_setting_setting->getSetting('cs_setting')) {

                    $default_setting = $this->model_entegrasyon_general->cs_default_setting();
                    $this->model_setting_setting->editSetting('cs_setting', $default_setting);

                }
                echo json_encode(array('status' => true, 'message' => 'Api Bilgileriniz Doğrulanmıştır.'));

            } else {

                echo json_encode(array('status' => false, 'message' => 'Api Bilgileriniz Doğrulanmadı, Hata Mesajı:'.$result['message']));

            }

        } else {


            $data['token_link'] = $this->token_data['token_link'];
            $data['cs_satici_numarasi'] = $this->config->get('cs_satici_numarasi');
            $data['cs_api_anahtari'] = $this->config->get('cs_api_anahtari');
            $data['cs_status'] = $this->config->get('cs_status');
            $this->response->setOutput($this->load->view('entegrasyon/setting/api/cs', $data));
        }


    }
    public function hb()
        //BU KISIM HEPSİBURADA İÇİN TAMAMLANACAK..
    {


        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $this->load->model('entegrasyon/general');

            $hb_merchant_id = $this->request->post['hb_merchant_id'];

            $market=$this->model_entegrasyon_general->getMarketPlace('hb');
            $post_data = array('market'=>$market,'api_info' => array('merchant_id' => $hb_merchant_id), 'request_data' => array());
            $result = $this->entegrasyon->clientConnect($post_data, 'check_api', 'hb',false);




            if ($result['status']) {
                $this->load->model('setting/setting');
                $this->model_setting_setting->editSetting('hb', $this->request->post);
                $market['status']=1;

                if(!$this->model_setting_setting->getSetting('hb_setting')) {

                    $default_setting = $this->model_entegrasyon_general->hb_default_setting();
                    $this->model_setting_setting->editSetting('hb_setting', $default_setting);

                }
                $this->model_entegrasyon_general->updateMarketPlace($market);
                echo json_encode(array('status' => true, 'message' => 'Api Bilgileriniz Doğrulandı'));

                return;

            } else {

                echo json_encode(array('status' => false, 'message' => 'Api Bilgileriniz Doğrulanmadı, Hata Mesajı:'.$result['message']));
                return;
            }


        } else {

            $data['token_link'] = $this->token_data['token_link'];
            $data['hb_api_anahtari'] = $this->config->get('hb_api_anahtari');
            $data['hb_api_sifresi'] = $this->config->get('hb_api_sifresi');
            $data['hb_merchant_id'] = $this->config->get('hb_merchant_id');
            $data['hb'] = $this->config->get('hb_status');

            $this->response->setOutput($this->load->view('entegrasyon/setting/api/hb', $data));
        }


    }

    public function ty()
        //BU KISIM TRENDYOL İÇİN TAMAMLANACAK..

    {


        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {


            $ty_api_anahtari = $this->request->post['ty_api_anahtari'];
            $ty_api_sifresi = $this->request->post['ty_api_sifresi'];
            $ty_satici_numarasi = $this->request->post['ty_satici_numarasi'];
            $params = array(
                'username' => $ty_api_anahtari,
                'password' => $ty_api_sifresi,
                'shopId' => $ty_satici_numarasi
            );
            $this->load->model('entegrasyon/general');

            $market=$this->model_entegrasyon_general->getMarketPlace('ty');
            $post_data = array('market'=>$market,'api_info' => $params, 'request_data' => array());
            $result = $this->entegrasyon->clientConnect($post_data, 'check_api', 'ty');



            if ($result['status']) {

                $this->load->model('setting/setting');
                $this->model_setting_setting->editSetting('ty', $this->request->post);
                $market['status']=1;

                if(!$this->model_setting_setting->getSetting('ty_setting')) {

                    $default_setting = $this->model_entegrasyon_general->ty_default_setting();
                    $this->model_setting_setting->editSetting('ty_setting', $default_setting);

                }
                $this->model_entegrasyon_general->updateMarketPlace($market);
                echo json_encode(array('status' => true, 'message' => 'Api Bilgileriniz Doğrulanmıştır.'));

            } else {

                echo json_encode(array('status' => false, 'message' => 'Api Bilgileriniz Doğrulanmadı, Hata Mesajı:'.$result['message']));

            }

        } else {


            $data['token_link'] = $this->token_data['token_link'];
            $data['ty_satici_numarasi'] = $this->config->get('ty_satici_numarasi');
            $data['ty_api_anahtari'] = $this->config->get('ty_api_anahtari');
            $data['ty_api_sifresi'] = $this->config->get('ty_api_sifresi');
            $data['ty_status'] = $this->config->get('ty_status');
            $this->response->setOutput($this->load->view('entegrasyon/setting/api/ty', $data));
        }


    }

    public function reset()
    {
        $this->load->model('setting/setting');
        $code = $this->request->get['code'];
        $this->load->model('entegrasyon/general');

        $market=$this->model_entegrasyon_general->getMarketPlace($code);
        $market['status']=0;
        $this->model_entegrasyon_general->updateMarketPlace($market);
        $this->model_setting_setting->deleteSetting($code);
        $post_data = array('market'=>$market,'api_info' => array(), 'request_data' => array());
        $this->entegrasyon->clientConnect($post_data, 'reset_api', $code);

        echo json_encode(array('status' => true));


    }


    public function updater()
    {

        $this->load->language('entegrasyon/updater');

        $data = $this->language->all();
        $this->load->model('setting/setting');
        $this->document->setTitle($this->language->get('text_manage'));
        $this->document->addScript('view/javascript/openbay/js/faq.js');

        if (isset($this->request->get['mode'])) {
            $mode = $this->request->get['mode'];
        } else {
            $mode = '';
        }



        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'href' => $this->url->link('common/dashboard', $this->token_data['token_link'], true),
            'text' => $this->language->get('text_home'),
        );

        $data['breadcrumbs'][] = array(
            'href' => $this->url->link('entegrasyon/setting', $this->token_data['token_link'], true),
            'text' => $this->language->get('heading_title'),
        );

        $data['breadcrumbs'][] = array(
            'href' => $this->url->link('entegrasyon/setting/updater', $this->token_data['token_link'], true),
            'text' => $this->language->get('text_manage'),
        );


        $this->load->model('entegrasyon/updater');

        $versiyon_data = $this->model_entegrasyon_general->getVersionInfo($this->model_entegrasyon_updater->version()['version']);


        $data['versiyon_content'] = $versiyon_data ?  html_entity_decode($versiyon_data['info']):'';
        $data['versiyon_number'] = $versiyon_data ? $versiyon_data['versiyon']:'';

        $data['mode']=$mode;

        $data['text_version'] = $this->config->get('module_entegrasyon_version');


        $data['action'] = $this->url->link('entegrasyon/setting/updater', $this->token_data['token_link'], true);
        $data['cancel'] = $this->url->link('entegrasyon/setting', $this->token_data['token_link'], true);

        $data['token_link'] = $this->token_data['token_link'];

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('entegrasyon/setting/updater', $data));
    }


    public function update()
    {
        $this->load->model('entegrasyon/updater');
        $this->load->language('entegrasyon/updater');

        if (!isset($this->request->get['stage'])) {
            $stage = 'check_server';
        } else {
            $stage = $this->request->get['stage'];
        }

        if (!isset($this->request->get['beta']) || $this->request->get['beta'] == 0) {
            $beta = 0;
        } else {
            $beta = 1;
        }

        switch ($stage) {
            case 'check_server': // step 1
                $response = $this->model_entegrasyon_updater->updateTest();

                sleep(1);
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($response));
                break;
            case 'check_version': // step 2
                $response = $this->model_entegrasyon_updater->updateCheckVersion($beta);
                sleep(1);
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($response));

                break;
            case 'download': // step 3
                $response = $this->model_entegrasyon_updater->updateDownload($beta);

                sleep(1);
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($response));
                break;
            case 'extract': // step 4
                $response = $this->model_entegrasyon_updater->updateExtract();

                sleep(1);
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($response));
                break;
            case 'remove': // step 5 - remove any files no longer needed
                $response = $this->model_entegrasyon_updater->updateRemove($beta);

                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($response));
                break;
            case 'run_patch': // step 6 - run any db updates or other patch files


                //if($this->config->get('module_entegrasyon_version') < "1.8.58"){

                $this->updatedb();

                $response['db_update']="Güncellendi";

                //  }




                $response = array('error' => 0, 'response' => '', 'percent_complete' => 90, 'status_message' => 'Veritabanı Güncelleniyor...');
                $this->refreshTheme();
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($response));
                break;
            case 'update_version': // step 7 - update the version number

                $this->load->model('setting/setting');


                $response = $this->model_entegrasyon_updater->updateUpdateVersion($beta);

                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($response));

                $this->refresh();

                break;

            default;
        }
    }


    public function update_db_2()
    {

        try {

            $this->db->query("CREATE TABLE `".DB_PREFIX."es_market_product` (
  `market_product_id` int(11) NOT NULL,
  `code` varchar(10) CHARACTER SET utf8 NOT NULL,
  `oc_product_id` int(11) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `model` varchar(255) CHARACTER SET utf8 NOT NULL,
  `barcode` varchar(255) CHARACTER SET utf8 NOT NULL,
  `stock_code` varchar(255) CHARACTER SET utf8 NOT NULL,
  `sale_price` decimal(15,4) NOT NULL,
  `list_price` decimal(15,4) NOT NULL,
  `quantity` int(11) NOT NULL,
  `sale_status` int(1) NOT NULL,
  `approval_status` int(1) NOT NULL,
  `custom_data` longtext CHARACTER SET utf8 NOT NULL,
  `marketplace_product_id` varchar(256) NOT NULL,
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;");


            try {
                $this->db->query("ALTER TABLE `".DB_PREFIX."es_market_product` ADD PRIMARY KEY (`market_product_id`);");

            }catch (Exception $exception){


            }

            try {
                $this->db->query("ALTER TABLE `".DB_PREFIX."es_market_product` MODIFY `market_product_id` int(11) NOT NULL AUTO_INCREMENT;");

            }catch (Exception $exception){


            }



        }catch (Exception $exception){


        }


    }


    public function updatedb()
    {
        error_reporting(0);



        try {

            $this->db->query("ALTER TABLE `".DB_PREFIX."order` ADD `payment_tax_id` INT(15) NULL AFTER `payment_code`;");

        }catch (Exception $exception){
        }

        try {
            $this->db->query("ALTER TABLE `".DB_PREFIX."order` ADD `payment_company_id` INT(15) NULL AFTER `payment_code`;");

        }catch (Exception $exception){

        }


        try {
            $this->db->query("ALTER TABLE `".DB_PREFIX."order` CHANGE `payment_company_id` `payment_company_id` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;");
        }catch (Exception $exception){

        }

        try {
            $this->db->query("ALTER TABLE `".DB_PREFIX."order` CHANGE `payment_tax_id` `payment_tax_id` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;");
        }catch (Exception $exception){

        }



        try {

            $this->db->query("CREATE TABLE `".DB_PREFIX."es_market_product` (
  `market_product_id` int(11) NOT NULL,
  `code` varchar(10) CHARACTER SET utf8 NOT NULL,
  `oc_product_id` int(11) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `model` varchar(255) CHARACTER SET utf8 NOT NULL,
  `barcode` varchar(255) CHARACTER SET utf8 NOT NULL,
  `stock_code` varchar(255) CHARACTER SET utf8 NOT NULL,
  `sale_price` decimal(15,4) NOT NULL,
  `list_price` decimal(15,4) NOT NULL,
  `quantity` int(11) NOT NULL,
  `sale_status` int(1) NOT NULL,
  `approval_status` int(1) NOT NULL,
  `custom_data` longtext CHARACTER SET utf8 NOT NULL,
  `marketplace_product_id` varchar(256) NOT NULL,
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;");


            try {
                $this->db->query("ALTER TABLE `".DB_PREFIX."es_market_product` ADD PRIMARY KEY (`market_product_id`);");


            }catch (Exception $exception){


            }



            try {
                $this->db->query("ALTER TABLE `".DB_PREFIX."es_market_product` ADD PRIMARY KEY (`market_product_id`);");

            }catch (Exception $exception){


            }

            try {
                $this->db->query("ALTER TABLE `".DB_PREFIX."es_market_product` MODIFY `market_product_id` int(11) NOT NULL AUTO_INCREMENT;");

            }catch (Exception $exception){


            }



        }catch (Exception $exception){


        }


        try {
            $this->db->query("ALTER TABLE `".DB_PREFIX."es_ordered_product`  ADD `list_price` DECIMAL(15,4) NOT NULL  AFTER `price`;");

        }catch (Exception $exception){


        }


        try {
            $this->db->query("ALTER TABLE `".DB_PREFIX."es_ordered_product`  ADD `discount` DECIMAL(15,4) NOT NULL  AFTER `price`;");

        }catch (Exception $exception){



        }


        try {
            $this->db->query("ALTER TABLE `".DB_PREFIX."es_order`  ADD `shipping_info` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL  AFTER `town`");

        }catch (Exception $exception){


        }

        try {
            $this->db->query("ALTER TABLE `".DB_PREFIX."es_order`  ADD `payment_info` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL  AFTER `town`");

        }catch (Exception $exception){


        }



        try {
            $this->db->query("ALTER TABLE `".DB_PREFIX."es_ordered_product`  ADD `kdv` INT(2) NOT NULL  AFTER `price`;");

        }catch (Exception $exception){


        }

        try {

            $this->db->query("ALTER TABLE `" . DB_PREFIX . "es_attribute` CHANGE `attribute` `attribute` LONGTEXT  NOT NULL;");

        } catch (Exception $exception) {

            //  echo $exception->getMessage();
        }
    }


    public function updatedb_for_request()
    {
        try {
            $this->db->query("CREATE TABLE `".DB_PREFIX."es_request` (
  `req_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) NOT NULL,
  `sale_price` decimal(15,4) NOT NULL,
  `list_price` decimal(15,4) NOT NULL,
  `quantity` int(11) NOT NULL,
  `product_code` varchar(64) NOT NULL,
  `code` varchar(10) NOT NULL,
  `service_type` int(11) NOT NULL,
  `date_added` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

        }catch (Exception $exception ){}
        try {
            $this->db->query("ALTER TABLE `".DB_PREFIX."es_request` ADD PRIMARY KEY (`req_id`);");
        }catch (Exception $exception){}

        try {
            $this->db->query("ALTER TABLE `".DB_PREFIX."es_request` MODIFY `req_id` int(11) NOT NULL AUTO_INCREMENT;");
        } catch (Exception $exception){}

    }

    public function updatedbforProductVariants()
    {


        $this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."es_product_variant` ");

        $result=$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."es_product_variant` (
 
  `variant_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `name` varchar(50) CHARACTER SET utf8 NOT NULL,
  `image` varchar(255) CHARACTER SET utf8 NOT NULL,
  `model` varchar(50) CHARACTER SET utf8 NOT NULL,
  `barcode` varchar(255) CHARACTER SET utf8 NOT NULL,
  `quantity` int(5) NOT NULL,
  `price` float(10,2) NOT NULL,
  `variant_info` varchar(255) CHARACTER SET utf8 NOT NULL,
  `variant_count` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");


        try {
            $this->db->query("ALTER TABLE   `" . DB_PREFIX . "es_product_variant`
  ADD PRIMARY KEY (`variant_id`);");

            $this->db->query("ALTER TABLE `" . DB_PREFIX . "es_product_variant`
  MODIFY `variant_id` int(11) NOT NULL AUTO_INCREMENT;");
        }catch (Exception $exception){

        }
        /*   if ($this->request->get['tool']){


                // $this->url->link('entegrasyon/setting/tool','token=' . $this->session->data['token']);
                 $this->response->redirect($this->url->link('entegrasyon/setting/tool&tool=ture', $this->token_data['token_link'], true));


                 /*$datax = true;
                 $this->tool($datax);
                 return;
             }*/

        $query=$this->db->query("SELECT product_id FROM `".DB_PREFIX."product_option` group by product_id");
        foreach ($query->rows as $row) {
            $this->entegrasyon->getPoductVariants($row['product_id']);

        }
        print_r('Varyantlar Yenilendi');

    }


    public function updatesupoortSystem()
    {
        error_reporting(0);
        $this->load->model('setting/setting');
        $this->load->model('user/user_group');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'entegrasyon/support');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'entegrasyon/support');
    }



    public function modifyTable()
    {

        $this->runExecute("ALTER TABLE `".DB_PREFIX."es_product_question` ADD  `product` varchar(255) CHARACTER SET utf8 NOT NULL AFTER `user`;");


        /*  $query = $this->db->query("SELECT * FROM ".DB_PREFIX."es_product");
          $row = $query->row;
          if(!isset($row['cs'])){

              $this->runExecute("ALTER TABLE `".DB_PREFIX."es_option_value` CHANGE `market_option_value_id` `market_option_value_id` VARCHAR(64) NOT NULL;");
              $this->runExecute("ALTER TABLE `".DB_PREFIX."es_category` ADD  `cs` text CHARACTER SET utf8 NOT NULL AFTER `ty`;");
              $this->runExecute("ALTER TABLE `".DB_PREFIX."es_manufacturer` ADD  `cs` text CHARACTER SET utf8 NOT NULL AFTER `ty`;");
              $this->runExecute("ALTER TABLE `".DB_PREFIX."es_product` ADD  `cs` text CHARACTER SET utf8 NOT NULL AFTER `ty`;");
              $this->runExecute("ALTER TABLE `".DB_PREFIX."es_product_to_marketplace` ADD  `cs` text CHARACTER SET utf8 NOT NULL AFTER `ty`;");
              $this->runExecute("ALTER TABLE `".DB_PREFIX."es_order_status` ADD  `cs` text CHARACTER SET utf8 NOT NULL AFTER `ty`;");
          }

  */


    }

    public function update_version_number()
    {
        $version= $this->request->get['version'];
        $this->load->model('setting/setting');
        $this->entegrasyon->editSetting('module_entegrasyon', array('module_entegrasyon_last_update' => $this->config->get('module_entegrasyon_last_update'), 'module_entegrasyon_status' => $this->config->get('module_entegrasyon_status'), 'module_entegrasyon_version' => $version));
        $this->response->redirect($this->url->link('entegrasyon/setting',$this->token_data['token_link'], true));

    }



    public function modifyTable2()
    {

        $query = $this->db->query("SELECT * FROM ".DB_PREFIX."es_product");
        $row = $query->row;
        if(!isset($row['cs'])){

            $this->runExecute("ALTER TABLE `".DB_PREFIX."es_option_value` CHANGE `market_option_value_id` `market_option_value_id` VARCHAR(64) NOT NULL;");
            $this->runExecute("ALTER TABLE `".DB_PREFIX."es_category` ADD  `cs` text CHARACTER SET utf8 NOT NULL AFTER `ty`;");
            $this->runExecute("ALTER TABLE `".DB_PREFIX."es_manufacturer` ADD  `cs` text CHARACTER SET utf8 NOT NULL AFTER `ty`;");
            $this->runExecute("ALTER TABLE `".DB_PREFIX."es_product` ADD  `cs` text CHARACTER SET utf8 NOT NULL AFTER `ty`;");
            $this->runExecute("ALTER TABLE `".DB_PREFIX."es_product_to_marketplace` ADD  `cs` text CHARACTER SET utf8 NOT NULL AFTER `ty`;");
            $this->runExecute("ALTER TABLE `".DB_PREFIX."es_order_status` ADD  `cs` text CHARACTER SET utf8 NOT NULL AFTER `ty`;");
        }


    }


    public function refresh() {



        error_reporting(0);
        ini_set('display_errors', 0);


        if (true) {
            // Just before files are deleted, if config settings say maintenance mode is off then turn it on

            $this->load->model('setting/setting');

            //Log
            $log = array();

            // Clear all modification files
            $files = array();

            // Make path into an array
            $path = array(DIR_MODIFICATION . '*');

            // While the path array is still populated keep looping through
            while (count($path) != 0) {
                $next = array_shift($path);

                foreach (glob($next) as $file) {
                    // If directory add to path array
                    if (is_dir($file)) {
                        $path[] = $file . '/*';
                    }

                    // Add the file to the files to be deleted array
                    $files[] = $file;
                }
            }


            // Reverse sort the file array
            rsort($files);

            // Clear all modification files
            foreach ($files as $file) {
                if ($file != DIR_MODIFICATION . 'index.html') {
                    // If file just delete
                    if (is_file($file)) {
                        unlink($file);

                        // If directory use the remove directory function
                    } elseif (is_dir($file)) {
                        rmdir($file);
                    }
                }
            }

            // Begin
            $xml = array();

            // Load the default modification XML
            $xml[] = file_get_contents(DIR_SYSTEM . 'modification.xml');

            // This is purly for developers so they can run mods directly and have them run without upload after each change.
            $files = glob(DIR_SYSTEM . '*.ocmod.xml');

            if ($files) {
                foreach ($files as $file) {
                    $xml[] = file_get_contents($file);
                }
            }



            if(VERSION >= 3){

                $this->load->model('setting/modification');
                // Get the default modification file
                $results = $this->model_setting_modification->getModifications();

            }else {

                $this->load->model('extension/modification');
                // Get the default modification file
                $results = $this->model_extension_modification->getModifications();

            }


            foreach ($results as $result) {
                if ($result['status']) {
                    $xml[] = $result['xml'];
                }
            }

            $modification = array();

            foreach ($xml as $xml) {
                if (empty($xml)){
                    continue;
                }

                $dom = new DOMDocument('1.0', 'UTF-8');
                $dom->preserveWhiteSpace = false;
                $dom->loadXml($xml);

                // Log
                $log[] = 'MOD: ' . $dom->getElementsByTagName('name')->item(0)->textContent;

                // Wipe the past modification store in the backup array
                $recovery = array();

                // Set the a recovery of the modification code in case we need to use it if an abort attribute is used.
                if (isset($modification)) {
                    $recovery = $modification;
                }

                $files = $dom->getElementsByTagName('modification')->item(0)->getElementsByTagName('file');

                foreach ($files as $file) {
                    $operations = $file->getElementsByTagName('operation');

                    $files = explode('|', $file->getAttribute('path'));

                    foreach ($files as $file) {
                        $path = '';

                        // Get the full path of the files that are going to be used for modification
                        if ((substr($file, 0, 7) == 'catalog')) {
                            $path = DIR_CATALOG . substr($file, 8);
                        }

                        if ((substr($file, 0, 5) == 'admin')) {
                            $path = DIR_APPLICATION . substr($file, 6);
                        }

                        if ((substr($file, 0, 6) == 'system')) {
                            $path = DIR_SYSTEM . substr($file, 7);
                        }

                        if ($path) {
                            $files = glob($path, GLOB_BRACE);

                            if ($files) {
                                foreach ($files as $file) {
                                    // Get the key to be used for the modification cache filename.
                                    if (substr($file, 0, strlen(DIR_CATALOG)) == DIR_CATALOG) {
                                        $key = 'catalog/' . substr($file, strlen(DIR_CATALOG));
                                    }

                                    if (substr($file, 0, strlen(DIR_APPLICATION)) == DIR_APPLICATION) {
                                        $key = 'admin/' . substr($file, strlen(DIR_APPLICATION));
                                    }

                                    if (substr($file, 0, strlen(DIR_SYSTEM)) == DIR_SYSTEM) {
                                        $key = 'system/' . substr($file, strlen(DIR_SYSTEM));
                                    }

                                    // If file contents is not already in the modification array we need to load it.
                                    if (!isset($modification[$key])) {
                                        $content = file_get_contents($file);

                                        $modification[$key] = preg_replace('~\r?\n~', "\n", $content);
                                        $original[$key] = preg_replace('~\r?\n~', "\n", $content);

                                        // Log
                                        $log[] = PHP_EOL . 'FILE: ' . $key;
                                    }

                                    foreach ($operations as $operation) {
                                        $error = $operation->getAttribute('error');

                                        // Ignoreif
                                        $ignoreif = $operation->getElementsByTagName('ignoreif')->item(0);

                                        if ($ignoreif) {
                                            if ($ignoreif->getAttribute('regex') != 'true') {
                                                if (strpos($modification[$key], $ignoreif->textContent) !== false) {
                                                    continue;
                                                }
                                            } else {
                                                if (preg_match($ignoreif->textContent, $modification[$key])) {
                                                    continue;
                                                }
                                            }
                                        }

                                        $status = false;

                                        // Search and replace
                                        if ($operation->getElementsByTagName('search')->item(0)->getAttribute('regex') != 'true') {
                                            // Search
                                            $search = $operation->getElementsByTagName('search')->item(0)->textContent;
                                            $trim = $operation->getElementsByTagName('search')->item(0)->getAttribute('trim');
                                            $index = $operation->getElementsByTagName('search')->item(0)->getAttribute('index');

                                            // Trim line if no trim attribute is set or is set to true.
                                            if (!$trim || $trim == 'true') {
                                                $search = trim($search);
                                            }

                                            // Add
                                            $add = $operation->getElementsByTagName('add')->item(0)->textContent;
                                            $trim = $operation->getElementsByTagName('add')->item(0)->getAttribute('trim');
                                            $position = $operation->getElementsByTagName('add')->item(0)->getAttribute('position');
                                            $offset = $operation->getElementsByTagName('add')->item(0)->getAttribute('offset');

                                            if ($offset == '') {
                                                $offset = 0;
                                            }

                                            // Trim line if is set to true.
                                            if ($trim == 'true') {
                                                $add = trim($add);
                                            }

                                            // Log
                                            $log[] = 'CODE: ' . $search;

                                            // Check if using indexes
                                            if ($index !== '') {
                                                $indexes = explode(',', $index);
                                            } else {
                                                $indexes = array();
                                            }

                                            // Get all the matches
                                            $i = 0;

                                            $lines = explode("\n", $modification[$key]);

                                            for ($line_id = 0; $line_id < count($lines); $line_id++) {
                                                $line = $lines[$line_id];

                                                // Status
                                                $match = false;

                                                // Check to see if the line matches the search code.
                                                if (stripos($line, $search) !== false) {
                                                    // If indexes are not used then just set the found status to true.
                                                    if (!$indexes) {
                                                        $match = true;
                                                    } elseif (in_array($i, $indexes)) {
                                                        $match = true;
                                                    }

                                                    $i++;
                                                }

                                                // Now for replacing or adding to the matched elements
                                                if ($match) {
                                                    switch ($position) {
                                                        default:
                                                        case 'replace':
                                                            $new_lines = explode("\n", $add);

                                                            if ($offset < 0) {
                                                                array_splice($lines, $line_id + $offset, abs($offset) + 1, array(str_replace($search, $add, $line)));

                                                                $line_id -= $offset;
                                                            } else {
                                                                array_splice($lines, $line_id, $offset + 1, array(str_replace($search, $add, $line)));
                                                            }
                                                            break;
                                                        case 'before':
                                                            $new_lines = explode("\n", $add);

                                                            array_splice($lines, $line_id - $offset, 0, $new_lines);

                                                            $line_id += count($new_lines);
                                                            break;
                                                        case 'after':
                                                            $new_lines = explode("\n", $add);

                                                            array_splice($lines, ($line_id + 1) + $offset, 0, $new_lines);

                                                            $line_id += count($new_lines);
                                                            break;
                                                    }

                                                    // Log
                                                    $log[] = 'LINE: ' . $line_id;

                                                    $status = true;
                                                }
                                            }

                                            $modification[$key] = implode("\n", $lines);
                                        } else {
                                            $search = trim($operation->getElementsByTagName('search')->item(0)->textContent);
                                            $limit = $operation->getElementsByTagName('search')->item(0)->getAttribute('limit');
                                            $replace = trim($operation->getElementsByTagName('add')->item(0)->textContent);

                                            // Limit
                                            if (!$limit) {
                                                $limit = -1;
                                            }

                                            // Log
                                            $match = array();

                                            preg_match_all($search, $modification[$key], $match, PREG_OFFSET_CAPTURE);

                                            // Remove part of the the result if a limit is set.
                                            if ($limit > 0) {
                                                $match[0] = array_slice($match[0], 0, $limit);
                                            }

                                            if ($match[0]) {
                                                $log[] = 'REGEX: ' . $search;

                                                for ($i = 0; $i < count($match[0]); $i++) {
                                                    $log[] = 'LINE: ' . (substr_count(substr($modification[$key], 0, $match[0][$i][1]), "\n") + 1);
                                                }

                                                $status = true;
                                            }

                                            // Make the modification
                                            $modification[$key] = preg_replace($search, $replace, $modification[$key], $limit);
                                        }

                                        if (!$status) {
                                            // Abort applying this modification completely.
                                            if ($error == 'abort') {
                                                $modification = $recovery;
                                                // Log
                                                $log[] = 'NOT FOUND - ABORTING!';
                                                break 5;
                                            }
                                            // Skip current operation or break
                                            elseif ($error == 'skip') {
                                                // Log
                                                $log[] = 'NOT FOUND - OPERATION SKIPPED!';
                                                continue;
                                            }
                                            // Break current operations
                                            else {
                                                // Log
                                                $log[] = 'NOT FOUND - OPERATIONS ABORTED!';
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                // Log
                $log[] = '----------------------------------------------------------------';
            }

            // Log


            // Write all modification files
            foreach ($modification as $key => $value) {
                // Only create a file if there are changes
                if ($original[$key] != $value) {
                    $path = '';

                    $directories = explode('/', dirname($key));

                    foreach ($directories as $directory) {
                        $path = $path . '/' . $directory;

                        if (!is_dir(DIR_MODIFICATION . $path)) {
                            @mkdir(DIR_MODIFICATION . $path, 0777);
                        }
                    }

                    $handle = fopen(DIR_MODIFICATION . $key, 'w');

                    fwrite($handle, $value);

                    fclose($handle);
                }
            }

            // Maintance mode back to original settings
            //   $this->model_setting_setting->editSettingValue('config', 'config_maintenance', $maintenance);

            // Do not return success message if refresh() was called with $data


        }



        if(VERSION >= 3){

            $this->refreshTheme();
        }


    }



    public function refreshTheme()
    {
        error_reporting(0);


        $directories = glob(DIR_CACHE . '*', GLOB_ONLYDIR);

        if ($directories) {
            foreach ($directories as $directory) {
                $files = glob($directory . '/*');

                foreach ($files as $file) {

                    if (is_file($file) || is_dir($file)) {
                        unlink($file);
                    }
                }

                if (is_dir($directory)) {
                    rmdir($directory);
                }
            }
        }


    }


    public function notifications()
    {
        // $this->load->model('extension/openbay/openbay');

        $json = $this->model_entegrasyon_updater->getNotifications();

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function version()
    {
        $this->load->model('entegrasyon/updater');

        $json = $this->model_entegrasyon_updater->version();

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function recreatevariants()
    {
        $this->runExecute("TRUNCATE TABLE `".DB_PREFIX."es_product_variant` ");

    }


    public function empty_orders()
    {
        $this->runExecute("TRUNCATE TABLE `".DB_PREFIX."es_order` ");
        $this->runExecute("TRUNCATE TABLE `".DB_PREFIX."es_ordered_product` ");

    }


    public function emptyall()
    {
        /* $this->db->query("ALTER TABLE oc_es_order_status
 ADD COLUMN name VARCHAR(64) CHARACTER SET utf8 NOT NULL AFTER order_status_id;");
         */

        //  $this->runExecute("TRUNCATE TABLE `".DB_PREFIX."es_category` ");
        // $this->runExecute("TRUNCATE TABLE `".DB_PREFIX."es_manufacturer` ");
        // $this->runExecute("TRUNCATE TABLE `" . DB_PREFIX . "es_order` ");
        // $this->runExecute("TRUNCATE TABLE `" . DB_PREFIX . "es_ordered_product` ");
        //  $this->runExecute("TRUNCATE TABLE `".DB_PREFIX."es_product` ");
        $this->runExecute("TRUNCATE TABLE `".DB_PREFIX."es_attribute` ");
        // $this->runExecute("TRUNCATE TABLE `".DB_PREFIX."es_attribute_value` ");
        //$this->runExecute("TRUNCATE TABLE `".DB_PREFIX."es_product_to_marketplace` ");

        // $this->runExecute("TRUNCATE TABLE `" . DB_PREFIX . "es_order_status` ");
        /* $this->runExecute("INSERT INTO `" . DB_PREFIX . "es_order_status` (`order_status_id`,`name`, `oc`, `n11`, `gg`, `ty`, `eptt`, `hb`) VALUES
 (1,'Onay Bekliyor', '1', '2', '0', '0', '0', '0'),
 (2, 'Kargolanma Aşamasında','2', '5', 'STATUS_WAITING_CARGO_INFO', 'ReadyToShip', 'kargo_yapilmasi_bekleniyor', 'Open'),
 (3, 'Kargolandı','3', '6', 'STATUS_WAITING_APPROVAL', 'Shipped', 'gonderilmis', 'Unpacked');");
 */
    }


    public function update_after()
    {

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $data['token_link'] = $this->token_data['token_link'];
        $this->load->model('entegrasyon/general');
        //$this->config->get('module_entegrasyon_version')
        $versiyon_data = $this->model_entegrasyon_general->getVersionInfo($this->config->get('module_entegrasyon_version'));
        if(!$versiyon_data){
            $this->response->redirect($this->url->link('entegrasyon/setting',$this->token_data['token_link'] , true));

        }

        $data['easy_visibility']=$this->config->get('easy_visibility') ? '':'hidden';


        $data['versiyon_content'] = html_entity_decode($versiyon_data['info']);
        $data['versiyon_number'] = $versiyon_data['versiyon'];


        if($data['versiyon_number'] == null){
            $this->response->redirect($this->url->link('entegrasyon/setting',$this->token_data['token_link'] , true));

        }
        $heading_title='Easy Entegre V-'.$versiyon_data['versiyon'];;

        $data['heading_title']=$heading_title;
        $this->document->setTitle($heading_title);
        $this->response->setOutput($this->load->view('entegrasyon/setting/update_after', $data));

    }

    public function error()
    {
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $data['token_link'] = $this->token_data['token_link'];
        $error=$this->request->get['error'];

        $data['easy_visibility']=$this->config->get('easy_visibility') ? '':'hidden';


        if($error=='no_api'){
            $heading_title='Aktif Pazaryeri Bulunamadı!';
            $data['error']='Pazaryeri işlemleri yapabilmek için en az bir pazaryerini aktif hale getirmeniz gerekmektedir. Pazaryeri ayarlarınızı <strong>"Ayarlar"</strong> sayfasınından yapabilirsiniz.';
            $data['solution_link']=array('title'=>'Ayarlar Sayfasına Git','url'=>$this->url->link('entegrasyon/setting', $this->token_data['token_link'], true));

        }else if($error=='no_user'){
            $heading_title='Aktif Kullanıcı Hesabı Bulunamadı!';
            $data['error']='Pazaryeri işlemleri yapabilmek için easyentegreye üye girişi yapmanız gerekmektedir. Henüz bir üye hesabınız yoksa ücretsiz üyelik oluşurabilirsiniz. Üyelik oluştuma ve Üye girişi işlemlerini <strong>"Ayarlar"</strong> sayfasınından yapabilirsiniz.';
            $data['solution_link']=array('title'=>'Ayarlar Sayfasına Git','url'=>$this->url->link('entegrasyon/setting', $this->token_data['token_link'], true));

        } else if($error=='no_module'){
            $heading_title='Pazaryeri Entegrasyon modülü aktif değil!';
            $data['error']='Pazaryeri Entegrasyon sistemin sağlıklı çalışabilmesi için eklentiler/modüller bölümünden Pazaryeri Entegrasyon modülünü aktif etmeniz gerekmektdir.';


            if(VERSION >= 3){

                $data['solution_link']=array('title'=>'Modüller Sayfasına Git','url'=>$this->url->link('marketplace/extension', $this->token_data['token_link'], true));


            }else {

                $data['solution_link']=array('title'=>'Modüller Sayfasına Git','url'=>$this->url->link('extension/extension', $this->token_data['token_link'], true));


            }


        }


        $data['heading_title']=$heading_title;
        $this->document->setTitle($heading_title);
        $this->response->setOutput($this->load->view('entegrasyon/setting/error', $data));

    }


    private function runExecute($sql)
    {
        try {
            $this->db->query($sql);
        } catch (Exception $exception) {

            echo $exception->getMessage();
        }

    }

    public function optimize_all_images()
    {
        $this->load->model('catalog/product');
        $i=0;
        $this->session->data['chanced_paths']=array();

        $this->load->model('entegrasyon/tool');

        foreach ($this->model_catalog_product->getProducts() as $product) {
            $result = $this->model_entegrasyon_tool->optimize_image($product['image'],'main',$product['product_id']);
            $i++;
            $getProductImages=$this->entegrasyon->getProductImages($product['product_id']);
            foreach ($getProductImages as $productImage) {
                if($productImage['image']) {
                    $result = $this->model_entegrasyon_tool->optimize_image($productImage['image'], 'product_image', $productImage['product_image_id']);
                    $i++;
                }
            }

        }


        echo json_encode(array('status'=>true,'message'=>$i.' adet Görsel Optimize Edildi!'));

    }

}