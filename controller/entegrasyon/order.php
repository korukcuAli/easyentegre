<?php
class ControllerEntegrasyonOrder extends Controller {
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

        }else if(!$this->config->get('module_entegrasyon_status')){
            $this->response->redirect($this->url->link('entegrasyon/setting/error','&error=no_module&'.$this->token_data['token_link'], true));

        }
    }

    public function index() {
        $this->load->language('entegrasyon/order');

        $this->document->setTitle($this->language->get('heading_title'));



        $this->load->model('entegrasyon/order');
        $this->getList();
    }

    public function create_barcode()
    {



        $this->load->language('sale/order');


        $data['direction'] = $this->language->get('direction');
        $data['lang'] = $this->language->get('code');

        $data['text_shipping'] = $this->language->get('text_shipping');
        $data['text_picklist'] = 'Kargo Etiketi';
        $data['text_order_detail'] = 'Satıcı ve Kargo Detayları';
        $data['text_order_id'] = $this->language->get('text_order_id');
        $data['text_invoice_no'] = $this->language->get('text_invoice_no');
        $data['text_invoice_date'] = $this->language->get('text_invoice_date');
        $data['text_date_added'] = 'Sipariş Tarihi';
        $data['text_shipping_code'] = 'Kargo Kodu';
        $data['text_telephone'] = $this->language->get('text_telephone');
        $data['text_fax'] = $this->language->get('text_fax');
        $data['text_email'] = $this->language->get('text_email');
        $data['text_website'] = $this->language->get('text_website');
        $data['text_contact'] = $this->language->get('text_contact');
        $data['text_shipping_address'] = $this->language->get('text_shipping_address');
        $data['text_shipping_method'] = 'Kargo Firması';
        $data['text_sku'] = $this->language->get('text_sku');
        $data['text_upc'] = $this->language->get('text_upc');
        $data['text_ean'] = $this->language->get('text_ean');
        $data['text_jan'] = $this->language->get('text_jan');
        $data['text_isbn'] = $this->language->get('text_isbn');
        $data['text_mpn'] = $this->language->get('text_mpn');
        $data['text_comment'] = $this->language->get('text_comment');

        $data['column_location'] = $this->language->get('column_location');
        $data['column_reference'] = $this->language->get('column_reference');
        $data['column_product'] = $this->language->get('column_product');
        $data['column_weight'] = $this->language->get('column_weight');
        $data['column_model'] = $this->language->get('column_model');
        $data['column_quantity'] = $this->language->get('column_quantity');
        $data['dir_image']=HTTPS_CATALOG;
        $data['easy_setting_shipping_logo']=$this->config->get('easy_setting_shipping_logo');

        $this->load->model('tool/image');
        $data['logo'] = $this->model_tool_image->resize($this->config->get('config_logo'), 100, 100);
        $list = $this->request->get['data'];
        $orders_id = explode(',', $list);


        $data['title'] = 'Kargo Etiketi';

        if ($this->request->server['HTTPS']) {
            $data['base'] = HTTPS_SERVER;
        } else {
            $data['base'] = HTTP_SERVER;
        }

        $data['direction'] = $this->language->get('direction');
        $data['lang'] = $this->language->get('code');
        $this->load->model('entegrasyon/order');
        $this->load->model('catalog/product');
        $this->load->model('setting/setting');
        $this->load->model('sale/order');
        $data['token_link'] = $this->token_data['token_link'];

        $data['orders'] = array();





        foreach ($orders_id as $order_id) {
            $order_info = $this->model_entegrasyon_order->getOrder($order_id);

            if (isset($this->request->get['print'])){
                $is_prnt_es =   strpos($order_info['market_order_id'], "prnt_es");
                 if (!$is_prnt_es ){
                     $new_market_order_id =  $order_info['market_order_id'] . "prnt_es"; //print after

                     $this->db->query("UPDATE ".DB_PREFIX."es_order SET market_order_id = '".$new_market_order_id."' WHERE  order_id='".$order_id."'");

//GEL
                 }

            }

            if ($order_info) {

                $data['store_name'] = $order_info['code'];
                $data['print'] = isset($this->request->get['print'])?true:false;
                $data['date_added'] = date($this->language->get('date_format_short'), strtotime($order_info['date_added']));
                $data['firstname'] = $order_info['first_name'];
                $data['lastname'] = $order_info['last_name'];
                $customer_name = $order_info['first_name'].' '.$order_info['last_name'];


                $data['email'] = $order_info['email'];
                $data['telephone'] = $order_info['phone'];


                //BARCODE **********
                $format =  '{company}' . "\n" . ' <div class="text-center"><b>{address_1}</b>' . "/" . '<b>{address_2}</b></div>' ;


                $find = array(
                    '{firstname}',
                    '{lastname}',
                    '{company}',
                    '{address_1}',
                    '{address_2}',
                    '{city}',
                    '{postcode}',
                    '{zone}',
                    '{zone_code}',
                    '{country}'
                );

                $replace = array(
                    'firstname' => $order_info['first_name'],
                    'lastname'  => $order_info['last_name'],
                    'address_1' => $order_info['shipping_address'],
                    'city'      => $order_info['city'],
                    'zone'      => $order_info['town'],
                );

                $shipping_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

                $product_data =$this->model_entegrasyon_order->getOrderedProducts($order_id);




                $shipping_info =  array(unserialize($order_info['shipping_info']));
                $data['total'] = $order_info['total'];

                $filter_data = array(
                    'filter_order_id'        => $order_id

                );


                $this->load->model('tool/image');
                $this->load->model('entegrasyon/general');
                $market_info=$this->model_entegrasyon_general->getMarketPlace($order_info['code']);




                $data['orders'][] = array(
                    'order_id'	       => $order_id,
                    'date_added'       => date($this->language->get('date_format_short'), strtotime($order_info['date_added'])),
                    'marketplace_name'       => $order_info['code'],
                    'marketplace_info'       => $market_info,
                    'email'            => $order_info['email'],
                    'market_order_id'            => $order_info['market_order_id'],
                    'shipment_method' => unserialize($order_info['shipping_info'])['shipment_method'],
                'customer_name'            => $customer_name,
                    'telephone'        => $order_info['phone'],
                    'store_name'        =>$this->config->get('config_name'),
                    'store_email'        =>$this->config->get('config_email'),
                    'store_telephone'        =>$this->config->get('config_telephone'),
                    'store_address'         =>$this->config->get('config_address'),
                    'store_url'        =>$this->config->get('config_secure') ? HTTPS_CATALOG : HTTP_CATALOG,
                    'order_status'        => $order_info['order_status'],
                    'shipping_info'     =>$shipping_info,
                    'shipping_address' => $shipping_address,
                    'total' => $order_info['total'],
                    'product'          => $product_data);


            }
        }


        // $data['barcode_img'] = $this->barcode($shipping_info[0]['shipping_code']);

        $this->response->setOutput($this->load->view('entegrasyon/order_shipping', $data));




    }

    public function print_barcode()
    {
        $list = $this->request->get['data'];
        $orders_id = explode(',', $list);

        $data['lang'] = $this->language->get('code');
        $this->load->model('entegrasyon/order');

        $data['orders'] = array();

        foreach ($orders_id as $order_id) {

            $order_info = $this->model_entegrasyon_order->getOrder($order_id);

            if ($order_info) {
                if (unserialize($order_info['shipping_info'])['campaign_number']){
                    $barkode='campaign_number';
                }else{
                    $barkode='shipping_code';
                }

                $shipping_info =  array(unserialize($order_info['shipping_info']));
            }
            echo $this->barcode($shipping_info[0][$barkode]);



            return;

        }
    }
    public function barcode($barcode)
    {


        $filepath = (isset($_GET["filepath"])?$_GET["filepath"]:"");
        $text = $barcode;
        $size = (isset($_GET["size"])?$_GET["size"]:"130");
        $orientation = (isset($_GET["orientation"])?$_GET["orientation"]:"horizontal");
        $code_type = (isset($_GET["codetype"])?$_GET["codetype"]:"code39");
        $print = (isset($_GET["print"])&&$_GET["print"]=='true'?true:true);
        $sizefactor = (isset($_GET["sizefactor"])?$_GET["sizefactor"]:"2");

// This function call can be copied into your project and can be made from anywhere in your code

        $code_string = "";
        // Translate the $text into barcode the correct $code_type
        if ( in_array(strtolower($code_type), array("code128", "code128b")) ) {
            $chksum = 104;
            // Must not change order of array elements as the checksum depends on the array's key to validate final code
            $code_array = array(" "=>"212222","!"=>"222122","\""=>"222221","#"=>"121223","$"=>"121322","%"=>"131222","&"=>"122213","'"=>"122312","("=>"132212",")"=>"221213","*"=>"221312","+"=>"231212",","=>"112232","-"=>"122132","."=>"122231","/"=>"113222","0"=>"123122","1"=>"123221","2"=>"223211","3"=>"221132","4"=>"221231","5"=>"213212","6"=>"223112","7"=>"312131","8"=>"311222","9"=>"321122",":"=>"321221",";"=>"312212","<"=>"322112","="=>"322211",">"=>"212123","?"=>"212321","@"=>"232121","A"=>"111323","B"=>"131123","C"=>"131321","D"=>"112313","E"=>"132113","F"=>"132311","G"=>"211313","H"=>"231113","I"=>"231311","J"=>"112133","K"=>"112331","L"=>"132131","M"=>"113123","N"=>"113321","O"=>"133121","P"=>"313121","Q"=>"211331","R"=>"231131","S"=>"213113","T"=>"213311","U"=>"213131","V"=>"311123","W"=>"311321","X"=>"331121","Y"=>"312113","Z"=>"312311","["=>"332111","\\"=>"314111","]"=>"221411","^"=>"431111","_"=>"111224","\`"=>"111422","a"=>"121124","b"=>"121421","c"=>"141122","d"=>"141221","e"=>"112214","f"=>"112412","g"=>"122114","h"=>"122411","i"=>"142112","j"=>"142211","k"=>"241211","l"=>"221114","m"=>"413111","n"=>"241112","o"=>"134111","p"=>"111242","q"=>"121142","r"=>"121241","s"=>"114212","t"=>"124112","u"=>"124211","v"=>"411212","w"=>"421112","x"=>"421211","y"=>"212141","z"=>"214121","{"=>"412121","|"=>"111143","}"=>"111341","~"=>"131141","DEL"=>"114113","FNC 3"=>"114311","FNC 2"=>"411113","SHIFT"=>"411311","CODE C"=>"113141","FNC 4"=>"114131","CODE A"=>"311141","FNC 1"=>"411131","Start A"=>"211412","Start B"=>"211214","Start C"=>"211232","Stop"=>"2331112");
            $code_keys = array_keys($code_array);
            $code_values = array_flip($code_keys);
            for ( $X = 1; $X <= strlen($text); $X++ ) {
                $activeKey = substr( $text, ($X-1), 1);
                $code_string .= $code_array[$activeKey];
                $chksum=($chksum + ($code_values[$activeKey] * $X));
            }
            $code_string .= $code_array[$code_keys[($chksum - (intval($chksum / 103) * 103))]];

            $code_string = "211214" . $code_string . "2331112";
        } elseif ( strtolower($code_type) == "code128a" ) {
            $chksum = 103;
            $text = strtoupper($text); // Code 128A doesn't support lower case
            // Must not change order of array elements as the checksum depends on the array's key to validate final code
            $code_array = array(" "=>"212222","!"=>"222122","\""=>"222221","#"=>"121223","$"=>"121322","%"=>"131222","&"=>"122213","'"=>"122312","("=>"132212",")"=>"221213","*"=>"221312","+"=>"231212",","=>"112232","-"=>"122132","."=>"122231","/"=>"113222","0"=>"123122","1"=>"123221","2"=>"223211","3"=>"221132","4"=>"221231","5"=>"213212","6"=>"223112","7"=>"312131","8"=>"311222","9"=>"321122",":"=>"321221",";"=>"312212","<"=>"322112","="=>"322211",">"=>"212123","?"=>"212321","@"=>"232121","A"=>"111323","B"=>"131123","C"=>"131321","D"=>"112313","E"=>"132113","F"=>"132311","G"=>"211313","H"=>"231113","I"=>"231311","J"=>"112133","K"=>"112331","L"=>"132131","M"=>"113123","N"=>"113321","O"=>"133121","P"=>"313121","Q"=>"211331","R"=>"231131","S"=>"213113","T"=>"213311","U"=>"213131","V"=>"311123","W"=>"311321","X"=>"331121","Y"=>"312113","Z"=>"312311","["=>"332111","\\"=>"314111","]"=>"221411","^"=>"431111","_"=>"111224","NUL"=>"111422","SOH"=>"121124","STX"=>"121421","ETX"=>"141122","EOT"=>"141221","ENQ"=>"112214","ACK"=>"112412","BEL"=>"122114","BS"=>"122411","HT"=>"142112","LF"=>"142211","VT"=>"241211","FF"=>"221114","CR"=>"413111","SO"=>"241112","SI"=>"134111","DLE"=>"111242","DC1"=>"121142","DC2"=>"121241","DC3"=>"114212","DC4"=>"124112","NAK"=>"124211","SYN"=>"411212","ETB"=>"421112","CAN"=>"421211","EM"=>"212141","SUB"=>"214121","ESC"=>"412121","FS"=>"111143","GS"=>"111341","RS"=>"131141","US"=>"114113","FNC 3"=>"114311","FNC 2"=>"411113","SHIFT"=>"411311","CODE C"=>"113141","CODE B"=>"114131","FNC 4"=>"311141","FNC 1"=>"411131","Start A"=>"211412","Start B"=>"211214","Start C"=>"211232","Stop"=>"2331112");
            $code_keys = array_keys($code_array);
            $code_values = array_flip($code_keys);
            for ( $X = 1; $X <= strlen($text); $X++ ) {
                $activeKey = substr( $text, ($X-1), 1);
                $code_string .= $code_array[$activeKey];
                $chksum=($chksum + ($code_values[$activeKey] * $X));
            }
            $code_string .= $code_array[$code_keys[($chksum - (intval($chksum / 103) * 103))]];

            $code_string = "211412" . $code_string . "2331112";
        } elseif ( strtolower($code_type) == "code39" ) {
            $code_array = array("0"=>"111221211","1"=>"211211112","2"=>"112211112","3"=>"212211111","4"=>"111221112","5"=>"211221111","6"=>"112221111","7"=>"111211212","8"=>"211211211","9"=>"112211211","A"=>"211112112","B"=>"112112112","C"=>"212112111","D"=>"111122112","E"=>"211122111","F"=>"112122111","G"=>"111112212","H"=>"211112211","I"=>"112112211","J"=>"111122211","K"=>"211111122","L"=>"112111122","M"=>"212111121","N"=>"111121122","O"=>"211121121","P"=>"112121121","Q"=>"111111222","R"=>"211111221","S"=>"112111221","T"=>"111121221","U"=>"221111112","V"=>"122111112","W"=>"222111111","X"=>"121121112","Y"=>"221121111","Z"=>"122121111","-"=>"121111212","."=>"221111211"," "=>"122111211","$"=>"121212111","/"=>"121211121","+"=>"121112121","%"=>"111212121","*"=>"121121211");

            // Convert to uppercase
            $upper_text = strtoupper($text);

            for ( $X = 1; $X<=strlen($upper_text); $X++ ) {
                $code_string .= $code_array[substr( $upper_text, ($X-1), 1)] . "1";
            }

            $code_string = "1211212111" . $code_string . "121121211";
        } elseif ( strtolower($code_type) == "code25" ) {
            $code_array1 = array("1","2","3","4","5","6","7","8","9","0");
            $code_array2 = array("3-1-1-1-3","1-3-1-1-3","3-3-1-1-1","1-1-3-1-3","3-1-3-1-1","1-3-3-1-1","1-1-1-3-3","3-1-1-3-1","1-3-1-3-1","1-1-3-3-1");

            for ( $X = 1; $X <= strlen($text); $X++ ) {
                for ( $Y = 0; $Y < count($code_array1); $Y++ ) {
                    if ( substr($text, ($X-1), 1) == $code_array1[$Y] )
                        $temp[$X] = $code_array2[$Y];
                }
            }

            for ( $X=1; $X<=strlen($text); $X+=2 ) {
                if ( isset($temp[$X]) && isset($temp[($X + 1)]) ) {
                    $temp1 = explode( "-", $temp[$X] );
                    $temp2 = explode( "-", $temp[($X + 1)] );
                    for ( $Y = 0; $Y < count($temp1); $Y++ )
                        $code_string .= $temp1[$Y] . $temp2[$Y];
                }
            }

            $code_string = "1111" . $code_string . "311";
        } elseif ( strtolower($code_type) == "codabar" ) {
            $code_array1 = array("1","2","3","4","5","6","7","8","9","0","-","$",":","/",".","+","A","B","C","D");
            $code_array2 = array("1111221","1112112","2211111","1121121","2111121","1211112","1211211","1221111","2112111","1111122","1112211","1122111","2111212","2121112","2121211","1121212","1122121","1212112","1112122","1112221");

            // Convert to uppercase
            $upper_text = strtoupper($text);

            for ( $X = 1; $X<=strlen($upper_text); $X++ ) {
                for ( $Y = 0; $Y<count($code_array1); $Y++ ) {
                    if ( substr($upper_text, ($X-1), 1) == $code_array1[$Y] )
                        $code_string .= $code_array2[$Y] . "1";
                }
            }
            $code_string = "11221211" . $code_string . "1122121";
        }

        // Pad the edges of the barcode
        $code_length = 20;
        if ($print) {
            $text_height = 30;
        } else {
            $text_height = 0;
        }

        for ( $i=1; $i <= strlen($code_string); $i++ ){
            $code_length = $code_length + (integer)(substr($code_string,($i-1),1));
        }

        if ( strtolower($orientation) == "horizontal" ) {
            $img_width = $code_length*$sizefactor;
            $img_height = $size;
        } else {
            $img_width = $size;
            $img_height = $code_length*$sizefactor;
        }

        $image = imagecreate($img_width, $img_height + $text_height);
        $black = imagecolorallocate ($image, 0, 0, 0);
        $white = imagecolorallocate ($image, 255, 255, 255);

        imagefill( $image, 0, 0, $white );
        if ( $print ) {
            imagestring($image, 5, 31, $img_height, $text, $black );
        }

        $location = 10;
        for ( $position = 1 ; $position <= strlen($code_string); $position++ ) {
            $cur_size = $location + ( substr($code_string, ($position-1), 1) );
            if ( strtolower($orientation) == "horizontal" )
                imagefilledrectangle( $image, $location*$sizefactor, 0, $cur_size*$sizefactor, $img_height, ($position % 2 == 0 ? $white : $black) );
            else
                imagefilledrectangle( $image, 0, $location*$sizefactor, $img_width, $cur_size*$sizefactor, ($position % 2 == 0 ? $white : $black) );
            $location = $cur_size;
        }


        // Draw barcode to the screen or save in a file
        if ( $filepath=="" ) {
            header ('Content-type: image/png');
            imagepng($image);
        } else {
            imagepng($image,$filepath);
            imagedestroy($image);
        }



    }




    protected function getList() {

        $data = $this->language->all();
        if (isset($this->request->get['filter_order_id'])) {
            $filter_order_id = $this->request->get['filter_order_id'];
        } else {
            $filter_order_id = '';
        }

        if (isset($this->request->get['filter_customer'])) {
            $filter_customer = $this->request->get['filter_customer'];
        } else {
            $filter_customer = '';
        }

        if (isset($this->request->get['filter_order_status'])) {
            $filter_order_status = $this->request->get['filter_order_status'];
        } else {
            $filter_order_status = '';
        }

        if (isset($this->request->get['filter_order_status_id'])) {
            $filter_order_status_id = $this->request->get['filter_order_status_id'];
        } else {
            $filter_order_status_id = '';
        }

        if (isset($this->request->get['filter_marketplace'])) {
            $filter_marketplace = $this->request->get['filter_marketplace'];
        } else {
            $filter_marketplace = '';
        }

        if (isset($this->request->get['filter_date_added'])) {
            $filter_date_added = $this->request->get['filter_date_added'];
        } else {
            $filter_date_added = '';
        }

        if (isset($this->request->get['filter_date_modified'])) {
            $filter_date_modified = $this->request->get['filter_date_modified'];
        } else {
            $filter_date_modified = '';
        }

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'o.order_id';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'DESC';
        }

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $url = '';

        if (isset($this->request->get['filter_order_id'])) {
            $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
        }

        if (isset($this->request->get['filter_customer'])) {
            $url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_order_status'])) {
            $url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
        }

        if (isset($this->request->get['filter_order_status_id'])) {
            $url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
        }

        if (isset($this->request->get['filter_total'])) {
            $url .= '&filter_total=' . $this->request->get['filter_total'];
        }

        if (isset($this->request->get['filter_date_added'])) {
            $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
        }

        if (isset($this->request->get['filter_date_modified'])) {
            $url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
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
            'href' => $this->url->link('entegrasyon/order', $this->token_data['token_link'] . $url, true)
        );

        $data['easy_visibility']=$this->config->get('easy_visibility') ? '':'hidden';

        $data['orders'] = array();

        $filter_data = array(
            'filter_order_id'        => $filter_order_id,
            'filter_customer'	     => $filter_customer,
            'filter_order_status'    => $filter_order_status,
            'filter_order_status_id' => $filter_order_status_id,
            'filter_marketplace'     => $filter_marketplace,
            'filter_date_added'      => $filter_date_added,
            'filter_date_modified'   => $filter_date_modified,
            'sort'                   => $sort,
            'order'                  => $order,
            'start'                  => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit'                  => $this->config->get('config_limit_admin')
        );

        $order_total = $this->model_entegrasyon_order->getTotalOrders($filter_data);

        $this->load->model('tool/image');

        $results = $this->model_entegrasyon_order->getOrders($filter_data);


//print_r();

        // $this->db->escape(unserialize(['shipping_info'])['shipping_code'])

        foreach ($results as $result) {

            $orderedProducts=$this->model_entegrasyon_order->getOrderedProducts($result['order_id']);

            $is_print_barcode = strpos($result['market_order_id'], "prnt_es");
            $result['market_order_id'] = str_replace("prnt_es","", $result['market_order_id']);


            $data['orders'][] = array(

                'order_id'=>$result['order_id'],
                'market_order_id'=>$result['market_order_id'],
                'is_print_barcode'=>$is_print_barcode,
                'shipping_code'=>$this->db->escape(unserialize($result['shipping_info'])['shipping_code']),
                'order_status'=>$result['name'],
                'city'=>$result['city'],
                'town'=>$result['town'],
                'ordered_products'=>$orderedProducts,
                'logo' =>$this->model_tool_image->resize('entegrasyon-logo/'.$result['code'].'-logo.png', 40, 40),
                'customer'=>$result['first_name'].' '.$result['last_name'],
                'total'=>$result['total'],
                'date_added'=>date('d-m-Y H:i:s', strtotime($result['date_added'])),
                'date_modified'=>$result['date_modified']?date('d-m-Y H:i:s', strtotime($result['date_modified'])):'',
                'view'         => $this->url->link('entegrasyon/order/info', $this->token_data['token_link'] . '&order_id=' . $result['order_id'] . $url, true),
                'delete'         => $this->url->link('entegrasyon/order/delete', $this->token_data['token_link'] . '&order_id=' . $result['order_id'] . $url, true),


            );
        }

        $data['order_statuses']=$this->model_entegrasyon_order->getOrderStatuses();




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

        $url = '';

        if (isset($this->request->get['filter_order_id'])) {
            $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
        }

        if (isset($this->request->get['filter_customer'])) {
            $url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_order_status'])) {
            $url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
        }

        if (isset($this->request->get['filter_order_status_id'])) {
            $url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
        }

        if (isset($this->request->get['filter_marketplace'])) {
            $filter_marketplace = $this->request->get['filter_marketplace'];
        } else {
            $filter_marketplace = '';
        }

        if (isset($this->request->get['filter_date_added'])) {
            $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
        }

        if (isset($this->request->get['filter_date_modified'])) {
            $url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
        }

        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }


        $data['sort_order'] = $this->url->link('entegrasyon/order', $this->token_data['token_link'] . '&sort=o.order_id' . $url, true);
        $data['sort_customer'] = $this->url->link('entegrasyon/order', $this->token_data['token_link'] . '&sort=customer' . $url, true);
        $data['sort_status'] = $this->url->link('entegrasyon/order', $this->token_data['token_link'] . '&sort=order_status' . $url, true);
        $data['sort_total'] = $this->url->link('entegrasyon/order', $this->token_data['token_link'] . '&sort=o.total' . $url, true);
        $data['sort_date_added'] = $this->url->link('entegrasyon/order', $this->token_data['token_link'] . '&sort=o.date_added' . $url, true);
        $data['sort_date_modified'] = $this->url->link('entegrasyon/order', $this->token_data['token_link'] . '&sort=o.date_modified' . $url, true);

        $url = '';

        if (isset($this->request->get['filter_order_id'])) {
            $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
        }

        if (isset($this->request->get['filter_customer'])) {
            $url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_order_status'])) {
            $url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
        }

        if (isset($this->request->get['filter_order_status_id'])) {
            $url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
        }

        if (isset($this->request->get['filter_marketplace'])) {
            $url .= '&filter_marketplace=' . $this->request->get['filter_marketplace'];
        }

        if (isset($this->request->get['filter_date_added'])) {
            $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
        }

        if (isset($this->request->get['filter_date_modified'])) {
            $url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
        }

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }


        $this->load->model('entegrasyon/general');
        $this->model_entegrasyon_general->loadPageRequired();

        $pagination = new Pagination();
        $pagination->total = $order_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('entegrasyon/order', $this->token_data['token_link'] . $url . '&page={page}', true);

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($order_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($order_total - $this->config->get('config_limit_admin'))) ? $order_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $order_total, ceil($order_total / $this->config->get('config_limit_admin')));

        $data['filter_order_id'] = $filter_order_id;
        $data['filter_customer'] = $filter_customer;
        $data['filter_order_status'] = $filter_order_status;
        $data['filter_order_status_id'] = $filter_order_status_id;
        $data['filter_marketplace'] = $filter_marketplace;
        $data['filter_date_added'] = $filter_date_added;
        $data['filter_date_modified'] = $filter_date_modified;
        $data['catalog_url']=HTTPS_CATALOG;

        $data['sort'] = $sort;
        $data['order'] = $order;



        $marketplaces = $this->model_entegrasyon_general->getMarketPlaces();
        foreach ($marketplaces as $marketplace) {

            if ($marketplace['status']) {
                $filter_data[$marketplace['code']] = true;
                $data['edit_button_status'] = true;
            }

        }
        $data['marketplaces'] = $marketplaces;


        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('entegrasyon/order_list', $data));
    }

    public function delete()
    {
        if (isset($this->request->get['order_id'])) {

            $order_id = $this->request->get['order_id'];
            $order_query=$this->db->query("select * from ".DB_PREFIX."es_order where order_id='".$order_id."'");

            $this->db->query("delete from ".DB_PREFIX."es_order where order_id='".$order_id."'");
            $this->db->query("delete from ".DB_PREFIX."es_ordered_product where order_id='".$order_id."'");
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
            $this->session->data['success'] = 'Sipariş Başarıyla Silindi!';


            $logmesage=$order_id.' numaralı sipariş başaroyla silindi!';

            $this->entegrasyon->log($order_query->row['code'],$logmesage,false);

            $this->response->redirect($this->url->link('entegrasyon/order', $this->token_data['token_link'] . $url, true));

        }
    }

    public function delete_bulk()
    {


        error_reporting(0);

        $this->load->model('entegrasyon/general');

        if(!$this->model_entegrasyon_general->checkPermission()){

            echo json_encode(array('status'=>false,'message'=>'Gerçek Mağaza bilgileri kullanıldığı için Demo versiyonda ürün aktarılmasına izin verilmemektedir.'));
            return;

        }


        // if(!isset($this->request->post['product_id']))return;


        $list = $this->request->post['list'];

        $order_id = $this->request->get['order_id'];
        $current_key = array_search($order_id, $list);


        if (isset($this->request->get['order_id'])) {
            $order_id = $this->request->get['order_id'];

            $this->db->query("delete from " . DB_PREFIX . "es_order where order_id='" . $order_id . "'");
            $this->db->query("delete from " . DB_PREFIX . "es_ordered_product where order_id='" . $order_id . "'");
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


            if ($current_key + 1 < count($list)) {


                echo json_encode(array('status' =>true, 'next' => true, 'item' => $list[$current_key + 1], 'list' => $list, 'current' => $current_key + 1, 'message' => "Silindi"));


            } else {

                echo json_encode(array('status' => false, 'next' => false, 'item' => $list[$current_key], 'list' => $list, 'current' => $current_key + 1, 'message' => "Silinemedi"));

            }

        }

    }


    public function bulkinfo()
    {
        $this->load->model('entegrasyon/order');


        $list = isset($this->request->get['data'])?$this->request->get['data']:0;
        $orders_id = explode(',', $list);

        if ($list){


            foreach ($orders_id as $order_id) {


                $order_info = $this->model_entegrasyon_order->getOrder($order_id);

                if ($order_info) {
                    $this->load->language('entegrasyon/order');

                    $this->document->setTitle($this->language->get('heading_title'));

                    $data['text_ip_add'] = sprintf($this->language->get('text_ip_add'), $this->request->server['REMOTE_ADDR']);
                    $data['text_order'] = sprintf($this->language->get('text_order'), $order_id);

                    $url = '';

                    $data['shipping'] = $this->url->link('entegrasyon/order/shipping', $this->token_data['token_link'] . '&order_id=' . $order_id, true);
                    $data['invoice'] = $this->url->link('entegrasyon/order/invoice', $this->token_data['token_link'] . '&order_id=' . $order_id, true);

                    $data['token_link'] = $this->token_data['token'];


                    $data['store_name'] = $order_info['code'];




                    $data['date_added'] = date($this->language->get('date_format_short'), strtotime($order_info['date_added']));

                    $data['firstname'] = $order_info['first_name'];
                    $data['lastname'] = $order_info['last_name'];


                    if (isset($order_info['customer_id'])) {
                        $data['customer'] = $this->url->link('customer/customer/edit', $this->token_data['token_link'] . '&customer_id=' . $order_info['customer_id'], true);
                    } else {
                        $data['customer'] = '';
                    }


                    $data['email'] = $order_info['email'];
                    $data['telephone'] = $order_info['phone'];




                    $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';


                    $find = array(
                        '{firstname}',
                        '{lastname}',
                        '{company}',
                        '{address_1}',
                        '{address_2}',
                        '{city}',
                        '{postcode}',
                        '{zone}',
                        '{zone_code}',
                        '{country}'
                    );

                    $replace = array(
                        'firstname' => $order_info['first_name'],
                        'lastname'  => $order_info['last_name'],
                        'address_1' => $order_info['shipping_address'],
                        'city'      => $order_info['city'],
                        'zone'      => $order_info['town'],
                    );

                    $data['payment_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

                    $data['products'] =$this->model_entegrasyon_order->getOrderedProducts($order_id);


                    $data['order_status'] = $order_info['order_status'];

                    $data['total'] = $order_info['total'];





                }
            }
            $this->response->setOutput($this->load->view('entegrasyon/bulk_order_info', $data));



        }

        }

    public function info() {
        $this->load->model('entegrasyon/order');


        $list = isset($this->request->get['data'])?$this->request->get['data']:0;
        $orders_id = explode(',', $list);





            if (isset($this->request->get['order_id'])) {
                $order_id = $this->request->get['order_id'];
            } else {
                $order_id = 0;
            }

            $order_info = $this->model_entegrasyon_order->getOrder($order_id);


            if ($order_info) {
                $this->load->language('entegrasyon/order');

                $this->document->setTitle($this->language->get('heading_title'));

                $data['text_ip_add'] = sprintf($this->language->get('text_ip_add'), $this->request->server['REMOTE_ADDR']);
                $data['text_order'] = sprintf($this->language->get('text_order'), $this->request->get['order_id']);

                $url = '';

                $data['shipping'] = $this->url->link('entegrasyon/order/shipping', $this->token_data['token_link'] . '&order_id=' . (int)$this->request->get['order_id'], true);
                $data['invoice'] = $this->url->link('entegrasyon/order/invoice', $this->token_data['token_link'] . '&order_id=' . (int)$this->request->get['order_id'], true);

                $data['token_link'] = $this->token_data['token'];


                $data['store_name'] = $order_info['code'];




                $data['date_added'] = date($this->language->get('date_format_short'), strtotime($order_info['date_added']));

                $data['firstname'] = $order_info['first_name'];
                $data['lastname'] = $order_info['last_name'];

                if (isset($order_info['customer_id'])) {
                    $data['customer'] = $this->url->link('customer/customer/edit', $this->token_data['token_link'] . '&customer_id=' . $order_info['customer_id'], true);
                } else {
                    $data['customer'] = '';
                }



                $data['email'] = $order_info['email'];
                $data['telephone'] = $order_info['phone'];




                $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';


                $find = array(
                    '{firstname}',
                    '{lastname}',
                    '{company}',
                    '{address_1}',
                    '{address_2}',
                    '{city}',
                    '{postcode}',
                    '{zone}',
                    '{zone_code}',
                    '{country}'
                );

                $replace = array(
                    'firstname' => $order_info['first_name'],
                    'lastname'  => $order_info['last_name'],
                    'address_1' => $order_info['shipping_address'],
                    'city'      => $order_info['city'],
                    'zone'      => $order_info['town'],
                );

                $data['payment_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

                $data['products'] =$this->model_entegrasyon_order->getOrderedProducts($this->request->get['order_id']);


                $data['order_status'] = $order_info['order_status'];

                $data['total'] = $order_info['total'];
                $data['header'] = $this->load->controller('common/header');
                $data['column_left'] = $this->load->controller('common/column_left');
                $data['footer'] = $this->load->controller('common/footer');

                $data['column_model'] = $this->language->get('column_model');


                $this->response->setOutput($this->load->view('entegrasyon/order_info', $data));
            } else {
                return new Action('error/not_found');
            }
        }







    public function chart() {

        $json = array();

        $this->load->model('entegrasyon/general');


        $marketplaces = $this->model_entegrasyon_general->getMarketPlaces();
        $data['marketplaces']=array();
        $this->load->model('entegrasyon/product');
        foreach ($marketplaces as $marketplace) {

            if($marketplace['code']=='n11') $marketplace['color']="red";
            if($marketplace['code']=='hb') $marketplace['color']="green";
            if($marketplace['code']=='ty') $marketplace['color']="orange";
            if($marketplace['code']=='eptt') $marketplace['color']="yellow";
            if($marketplace['code']=='gg') $marketplace['color']="blue";
            if($marketplace['status']){
                $data['marketplaces'][]=$marketplace;


            }


            $json[$marketplace['code']]['label'] = $marketplace['name'];
            $json[$marketplace['code']]['data'] = array();

        }




        $json['xaxis'] = array();
        $this->load->model('entegrasyon/order');

        if (isset($this->request->get['range'])) {
            $range = $this->request->get['range'];
        } else {
            $range = 'day';
        }

        switch ($range) {
            default:
            case 'day':

                foreach ($marketplaces as $marketplace) {

                    $results = $this->model_entegrasyon_order->getTotalOrdersByDay($marketplace['code']);

                    foreach ($results as $key => $value) {
                        $json[$marketplace['code']]['data'][] = array($key, $value['total']);
                    }

                }

                for ($i = 0; $i < 24; $i++) {
                    $json['xaxis'][] = array($i, $i);
                }
                break;
            case 'week':

                foreach ($marketplaces as $marketplace) {
                    $results = $this->model_entegrasyon_order->getTotalOrdersByWeek($marketplace['code']);

                    foreach ($results as $key => $value) {
                        $json[$marketplace['code']]['data'][] = array($key, $value['total']);
                    }
                }

                $date_start = strtotime('-' . date('w') . ' days');

                for ($i = 0; $i < 7; $i++) {
                    $date = date('Y-m-d', $date_start + ($i * 86400));

                    $json['xaxis'][] = array(date('w', strtotime($date)), date('D', strtotime($date)));
                }
                break;
            case 'month':


                foreach ($marketplaces as $marketplace) {
                    $results = $this->model_entegrasyon_order->getTotalOrdersByMonth($marketplace['code']);

                    foreach ($results as $key => $value) {
                        $json[$marketplace['code']]['data'][] = array($key, $value['total']);
                    }
                }

                for ($i = 1; $i <= date('t'); $i++) {
                    $date = date('Y') . '-' . date('m') . '-' . $i;

                    $json['xaxis'][] = array(date('j', strtotime($date)), date('d', strtotime($date)));
                }
                break;
            case 'year':

                foreach ($marketplaces as $marketplace) {
                    $results = $this->model_entegrasyon_order->getTotalOrdersByYear($marketplace['code']);


                    foreach ($results as $key => $value) {
                        $json[$marketplace['code']]['data'][] = array($key, $value['total']);
                    }}

                for ($i = 1; $i <= 12; $i++) {
                    $json['xaxis'][] = array($i, date('M', mktime(0, 0, 0, $i)));
                }
                break;
        }



        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }



}
