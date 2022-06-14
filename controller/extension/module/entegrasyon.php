<?php
class ControllerExtensionModuleEntegrasyon extends Controller {
    private $error = array();
    private $entegrasyon_version = "1.8.55";

    public function index() {
        $this->response->redirect($this->url->link('entegrasyon/setting', 'user_token=' . $this->session->data['user_token'], true));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/entegrasyon')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    public function refresh() {

        
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
            $ocmod = new Log('ocmod.log');
            $ocmod->write(implode("\n", $log));

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


    }


    public function install() {
        $this->load->model('setting/setting');

        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'entegrasyon/category');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'entegrasyon/category');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'entegrasyon/dashboard');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'entegrasyon/dashboard');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'entegrasyon/genel');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'entegrasyon/genel');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'entegrasyon/manufacturer');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'entegrasyon/manufacturer');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'entegrasyon/order');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'entegrasyon/order');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'entegrasyon/product');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'entegrasyon/product');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'entegrasyon/product_question');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'entegrasyon/product_question');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'entegrasyon/setting');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'entegrasyon/setting');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'entegrasyon/support');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'entegrasyon/support');

        $settings = $this->model_setting_setting->getSetting('entegrasyon');

        $settings['module_entegrasyon_status'] = 1;
        $settings['module_entegrasyon_version']=$this->entegrasyon_version;
        $settings['module_entegrasyon_last_update']=date('Y-m-d H:i:s');
        $this->model_setting_setting->editSetting('module_entegrasyon', $settings);
        $this->createDb();
        $this->refresh();

        $this->load->model('entegrasyon/general');
        $token_data=$this->model_entegrasyon_general->getToken();
        $this->response->redirect($this->url->link('entegrasyon/setting/install_success',$token_data['token_link'] , 'SSL'));

        //$this->load->controller('marketplace/modification/refresh',array('redirect'=>'marketplace/extension'));

    }


    private function createDb()
    {


        $this->runExecute("CREATE TABLE `".DB_PREFIX."es_request` (
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



        $this->runExecute("ALTER TABLE `".DB_PREFIX."es_request` ADD PRIMARY KEY (`req_id`);");

        $this->runExecute("ALTER TABLE `".DB_PREFIX."es_request`
  MODIFY `req_id` int(11) NOT NULL AUTO_INCREMENT;");



        $this->runExecute(" CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "es_category` (
`id` int(11) NOT NULL,
`category_id` int(11) NOT NULL,
`n11` text NOT NULL,
`gg` text CHARACTER SET utf8 NOT NULL,
`hb` text CHARACTER SET utf8 NOT NULL,
`ty` text CHARACTER SET utf8 NOT NULL,
`eptt` text CHARACTER SET utf8 NOT NULL,
`amz` text CHARACTER SET utf8 NOT NULL,
`cs` text CHARACTER SET utf8 NOT NULL,
`date_added` datetime NOT NULL,
`date_modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16;");

        $this->runExecute("CREATE TABLE `".DB_PREFIX."es_market_product` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf16;");
        $this->runExecute("ALTER TABLE `".DB_PREFIX."es_market_product` ADD PRIMARY KEY (`market_product_id`);");

        $this->runExecute("ALTER TABLE `".DB_PREFIX."es_market_product` MODIFY `market_product_id` int(11) NOT NULL AUTO_INCREMENT;");
//MANUFACTURER
        $this->runExecute(" CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "es_manufacturer` (
`id` int(11) NOT NULL,
`manufacturer_id` int(11) NOT NULL,
`n11` text NOT NULL,
`gg` text NOT NULL,
`hb` text NOT NULL,
`ty` text NOT NULL,
`eptt` text NOT NULL,
`amz` text NOT NULL,
`cs` text CHARACTER SET utf8 NOT NULL,
`date_added` datetime NOT NULL,
`date_modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        
        try {

            $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "es_order` (
`order_id` int(11) NOT NULL,
`code` varchar(11) NOT NULL,
`market_order_id` varchar(256) NOT NULL,
`order_status` varchar(64) CHARACTER SET utf8 NOT NULL,
`first_name` varchar(32) CHARACTER SET utf8 NOT NULL,
`last_name` varchar(32) CHARACTER SET utf8 NOT NULL,
`total` float(10,2) NOT NULL,
`shipping_address` text CHARACTER SET utf8 NOT NULL,
`billing_address` text CHARACTER SET utf8 NOT NULL,
`phone` varchar(15) NOT NULL,
`email` varchar(32) CHARACTER SET utf8 NOT NULL,
`city` varchar(32) CHARACTER SET utf8 NOT NULL,
`town` varchar(256) CHARACTER SET utf8 NOT NULL,
`shipping_info` text CHARACTER SET utf8 NOT NULL,
`payment_info` text CHARACTER SET utf8 NOT NULL,
`date_added` datetime NOT NULL,
`date_modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");



            $this->db->query("ALTER TABLE `".DB_PREFIX."es_order`
  ADD PRIMARY KEY (`order_id`);");

            $this->db->query("ALTER TABLE `" . DB_PREFIX . "es_order`
MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;");
        }catch (Exception $exception){

        }

        $this->runExecute("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "es_ordered_product` (
`order_id` int(11) NOT NULL,
`product_id` int(11) NOT NULL,
`model` varchar(64) CHARACTER SET utf8 NOT NULL,
`item_id` int(11) NOT NULL,
`quantity` int(3) NOT NULL,
`list_price` float(10,2) NOT NULL,
`price` float(10,2) NOT NULL,
`kdv`  float(10,2) NOT NULL,
`discount` float(10,2) NOT NULL,
`name` varchar(256) CHARACTER SET utf8mb4 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $this->runExecute("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "es_order_status` (
`order_status_id` int(11) NOT NULL,
`name` varchar(64) CHARACTER SET utf8 NOT NULL,
`oc` varchar(64) CHARACTER SET utf8 NOT NULL,
`n11` varchar(64) CHARACTER SET utf8 NOT NULL,
`gg` varchar(64) CHARACTER SET utf8 NOT NULL,
`ty` varchar(64) CHARACTER SET utf8 NOT NULL,
`eptt` varchar(64) CHARACTER SET utf8 NOT NULL,
`hb` varchar(64) CHARACTER SET utf8 NOT NULL,
`cs` text CHARACTER SET utf8 NOT NULL,

`amz` varchar(64) CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");



        $this->runExecute("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."es_product_variant` (
 
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

            $this->runExecute("ALTER TABLE   `".DB_PREFIX."es_product_variant`
  ADD PRIMARY KEY (`variant_id`);");
            
            $this->runExecute("ALTER TABLE `".DB_PREFIX."es_product_variant`
  MODIFY `variant_id` int(11) NOT NULL AUTO_INCREMENT;");

            $this->runExecute("ALTER TABLE `".DB_PREFIX."product_option` ADD INDEX( `product_id`);");


        $isExists=$this->db->query("select * from `" . DB_PREFIX . "es_order_status` ");
        if(!$isExists->num_rows){
            $this->runExecute("INSERT INTO `" . DB_PREFIX . "es_order_status` (`order_status_id`,`name`, `oc`, `n11`, `gg`, `ty`, `eptt`, `hb`) VALUES
(1,'Onay Bekliyor', '1', '1', '0', '0', '0', '0'),
(2, 'Kargolanma Aşamasında','2', '5', 'STATUS_WAITING_CARGO_INFO', 'ReadyToShip', 'kargo_yapilmasi_bekleniyor', 'Open'),
(3, 'Kargolandı','3', '6', 'STATUS_WAITING_APPROVAL', 'Shipped', 'gonderilmis', 'Unpacked');");
        }
        $this->runExecute("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "es_product` (
`product_id` int(11) NOT NULL,
`n11` text CHARACTER SET utf8 NOT NULL,
`gg` text CHARACTER SET utf8 NOT NULL,
`ty` text CHARACTER SET utf8 NOT NULL,
`eptt` text CHARACTER SET utf8 NOT NULL,
`hb` text CHARACTER SET utf8 NOT NULL,
`amz` text CHARACTER SET utf8 NOT NULL,
`cs` text CHARACTER SET utf8 NOT NULL,

`date_added` datetime NOT NULL,
`date_modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $this->runExecute("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."es_attribute` (
  `attribute_id` int(11) NOT NULL,
  `category_id` varchar(64) CHARACTER SET utf8 NOT NULL,
  `attribute` longtext CHARACTER SET utf8 NOT NULL,
   `required` varchar(256) NOT NULL,
  `code` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");


        $this->runExecute("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "es_product_error` (
  `product_id` int(11) NOT NULL,
   `code` varchar(10) NOT NULL,
     `type` int(1) NOT NULL,
  `error` text CHARACTER SET utf8 NOT NULL,
  `solution_id` int(11) NOT NULL,
  `date_added` datetime NOT NULL,
`date_modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");


        $this->runExecute("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "es_product_to_marketplace` (
`product_id` int(11) NOT NULL,
`n11` text CHARACTER SET utf8 NOT NULL,
`gg` text CHARACTER SET utf8 NOT NULL,
`hb` text CHARACTER SET utf8 NOT NULL,
`ty` text CHARACTER SET utf8 NOT NULL,
`eptt` text CHARACTER SET utf8 NOT NULL,
`amz` text CHARACTER SET utf8 NOT NULL,
`cs` text CHARACTER SET utf8 NOT NULL,

`date_added` datetime NOT NULL,
`date_modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");


        $this->runExecute("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."es_product_question` (
 
  `product_question_id` int(11) NOT NULL,
  `question_id` varchar(255) NOT NULL,
  `user` varchar(50) CHARACTER SET utf8 NOT NULL,
  `product` varchar(255) CHARACTER SET utf8 NOT NULL,
  `code` varchar(10) CHARACTER SET utf8 NOT NULL,
  `is_rejected` int(1) NOT NULL,
  `answered` int(1) NOT NULL,
  `message` text CHARACTER SET utf8 NOT NULL,
  `date_added` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");


        $this->runExecute("ALTER TABLE   `".DB_PREFIX."es_product_question`
  ADD PRIMARY KEY (`product_question_id`);");



        $this->runExecute("ALTER TABLE `".DB_PREFIX."es_product_question`
  MODIFY `product_question_id` int(11) NOT NULL AUTO_INCREMENT;");

        $this->runExecute("CREATE TABLE `".DB_PREFIX."es_option` (
  `option_id` int(11) NOT NULL,
  `code` varchar(10) CHARACTER SET utf8 NOT NULL,
  `category_id` varchar(11) NOT NULL,
    `order_number` int(4) NOT NULL,

  `oc_option_id` int(11) NOT NULL,
  `market_option_id` varchar(64) CHARACTER SET utf8 NOT NULL,
  `market_option_name` varchar(64) CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");


        $this->runExecute("CREATE TABLE `".DB_PREFIX."es_option_value` (
  `option_value_id` int(11) NOT NULL,
  `matched_option_id` int(11) NOT NULL,
  `oc_option_value_id` int(11) NOT NULL,
  `market_option_value_id` varchar(64) CHARACTER SET utf8 NOT NULL,
  `market_option_value_name` varchar(64) CHARACTER SET utf8 NOT NULL,
  `market_option_value_order` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        if(!$isExists->num_rows) {

            $this->runExecute("ALTER TABLE `".DB_PREFIX."es_option`
  ADD PRIMARY KEY (`option_id`);");

            $this->runExecute("ALTER TABLE `".DB_PREFIX."es_option_value`
  ADD PRIMARY KEY (`option_value_id`);");

                  $this->runExecute("ALTER TABLE `".DB_PREFIX."es_option`
  MODIFY `option_id` int(11) NOT NULL AUTO_INCREMENT;");

                  $this->runExecute("ALTER TABLE `".DB_PREFIX."es_option_value`
  MODIFY `option_value_id` int(11) NOT NULL AUTO_INCREMENT;");


            $this->runExecute("ALTER TABLE `" . DB_PREFIX . "es_category`
ADD PRIMARY KEY (`id`);");

            $this->runExecute("ALTER TABLE `" . DB_PREFIX . "es_product_to_marketplace`
ADD PRIMARY KEY (`product_id`);");
            
            $this->runExecute("ALTER TABLE `" . DB_PREFIX . "es_manufacturer`
ADD PRIMARY KEY (`id`);");

            $this->runExecute("ALTER TABLE `" . DB_PREFIX . "es_order_status`
ADD PRIMARY KEY (`order_status_id`);");

            $this->runExecute("ALTER TABLE `" . DB_PREFIX . "es_attribute`
  ADD PRIMARY KEY (`attribute_id`);");

            $this->runExecute("ALTER TABLE `" . DB_PREFIX . "es_attribute`
  MODIFY `attribute_id` int(11) NOT NULL AUTO_INCREMENT;");

            $this->runExecute("ALTER TABLE `" . DB_PREFIX . "es_category`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;");
            $this->runExecute("ALTER TABLE `" . DB_PREFIX . "es_manufacturer`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1");

            $this->runExecute("ALTER TABLE `" . DB_PREFIX . "es_order_status`
MODIFY `order_status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;");
        }

    }


    public function uninstall() {

        $this->load->model('user/user_group');
        $this->load->model('setting/setting');

        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'entegrasyon/dashboard');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'entegrasyon/dashboard');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'entegrasyon/product_question');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'entegrasyon/product_question');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'entegrasyon/category');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'entegrasyon/category');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'entegrasyon/genel');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'entegrasyon/genel');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'entegrasyon/manufacturer');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'entegrasyon/manufacturer');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'entegrasyon/order');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'entegrasyon/order');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'entegrasyon/product');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'entegrasyon/product');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'entegrasyon/setting');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'entegrasyon/setting');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'entegrasyon/support');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'entegrasyon/support');


        $settings = $this->model_setting_setting->getSetting('entegrasyon');
        $settings['module_entegrasyon_status'] = 0;

        $this->model_setting_setting->editSetting('module_entegrasyon', $settings);

        $this->deleteDb();


    }


    private function deleteDb()
    {

        // $this->runExecute("DROP TABLE `" . DB_PREFIX . "es_update_session` ");

        $this->runExecute("DROP TABLE `" . DB_PREFIX . "es_category` ");
        $this->runExecute("DROP TABLE `" . DB_PREFIX . "es_request` ");
        $this->runExecute("DROP TABLE `" . DB_PREFIX . "es_manufacturer` ");
        $this->runExecute("DROP TABLE `" . DB_PREFIX . "es_order` ");
        $this->runExecute("DROP TABLE `" . DB_PREFIX . "es_ordered_product` ");
        $this->runExecute("DROP TABLE `" . DB_PREFIX . "es_product` ");
        $this->runExecute("DROP TABLE `" . DB_PREFIX . "es_order_status` ");
        $this->runExecute("DROP TABLE `" . DB_PREFIX . "es_attribute` ");
        $this->runExecute("DROP TABLE `" . DB_PREFIX . "es_product_to_marketplace` ");
        $this->runExecute("DROP TABLE `" . DB_PREFIX . "es_product_error` ");
        $this->runExecute("DROP TABLE `" . DB_PREFIX . "es_product_question` ");
        $this->runExecute("DROP TABLE `" . DB_PREFIX . "es_option` ");
        $this->runExecute("DROP TABLE `" . DB_PREFIX . "es_option_value` ");
        $this->runExecute("DROP TABLE `" . DB_PREFIX . "es_product_variant` ");
        $this->runExecute("DROP TABLE `" . DB_PREFIX . "es_market_product` ");

    }

    private function runExecute($sql)
    {
        try {
            $this->db->query($sql);
        } catch (Exception $exception) {

            echo $exception->getMessage();
        }

    }


}
