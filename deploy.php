<?php

$repo_dir = './.git';
$web_root_dir = './';

// Full path to git binary is required if git is not in your PHP user's path. Otherwise just use 'git'.
$git_bin_path = '/usr/bin/git';

$update = false;

// Parse data from Bitbucket hook payload
$payload = json_decode($_POST['payload']);

if (empty($payload->commits)) {
    // When merging and pushing to bitbucket, the commits array will be empty.
    // In this case there is no way to know what branch was pushed to, so we will do an update.
    $update = true;
} else {
    foreach ($payload->commits as $commit) {
        $branch = $commit->branch;
        if ($branch === 'master' || isset($commit->branches) && in_array('master', $commit->branches)) {
            $update = true;
            break;
        }
    }
}
if (null === $payload) {
    $update = true;
}
if ($update) {
  
    shell_exec('git checkout master');
    `git pull origin master`;

    // Log the deployment
    $commit_hash = shell_exec('cd ' . $repo_dir . ' && ' . $git_bin_path . ' rev-parse --short HEAD');

    date_default_timezone_set('Australia/Brisbane');
    $toupdate = include(__DIR__ . '/bitbucketDeployConfig.php');
    if ($toupdate['toupdate'] === true) {
        try {
            exec(__DIR__ . '/bitbucket.sh', $output);
        } catch (Exception $e) {
            $error = $e->getMessage();
            file_put_contents('deploy.log', date('m/d/Y h:i:s a') . " Pulling in changes... " . implode(' ', $output) . " Error: " . $error . "\n", FILE_APPEND);
        }
        //$commit_hash = shell_exec('cd ' . $repo_dir . ' && ' . $git_bin_path . ' rev-parse --short HEAD');
        file_put_contents('deploy.log', date('m/d/Y h:i:s a') . " Pulling in changes... " . implode(' ', $output) .  "\n", FILE_APPEND);

        $log = "<?php return array('toupdate' => false); \n";
        file_put_contents(__DIR__ . '/bitbucketDeployConfig.php', $log);
    }
}

/*
 * development.c4ffouecxrat.ap-southeast-2.rds.amazonaws.com
                    <username><![CDATA[root]]></username>
                    <password><![CDATA[uncs2dAPdFxyRdS5]]></password>
 */
/*
 <host><![CDATA[production.c4ffouecxrat.ap-southeast-2.rds.amazonaws.com]]></host>
                    <username><![CDATA[root]]></username>
                    <password><![CDATA[Y65UXUKwEyjLS6Jm]]></password>
                    <dbname><![CDATA[nutrition]]></dbname>
 
 * UPDATE catalog_category_product_index,
catalog_category_product SET catalog_category_product_index.position = catalog_category_product.position WHERE catalog_category_product_index.category_id = catalog_category_product.category_id AND catalog_category_product_index.product_id = catalog_category_product.product_id AND catalog_category_product.category_id =2524
 * 
 * 
 * FAST was api key:  53ec71366356216ca1e462a7bc2051ac
 * 
 * /home/www/nutritionwarehouse.com.au/logs
 * /home/www/nutritionwarehouse.com.au/public_html/var/log
 * php retailExpress.php -sku 129095 -id 184
 * sudo yum install php56-soap
 * 
 * 
  SELECT sku,city,increment_id  FROM `sales_flat_order_item` sol
  INNER JOIN sales_flat_order so ON so.entity_id = sol.order_id
  INNER JOIN sales_flat_order_address sod on so.customer_id=sod.customer_id
  WHERE city IN(
'ALKIMOS','ASHBY','BANKSIAGROVE','BARRAGUP','BELDON','BERTRAM','BURNSBEACH','BUTLER','CALISTA','CARRAMAR','CLARKSON','COODANUP','COOLOONGUP','CRAIGIE','CURRAMBINE','DUDLEYPARK','EASTROCKINGHAM','EDGEWATER','ERSKINE','FALCON','FURNISSDALE','GOLDENBAY','GREENFIELDS','HALLSHEAD','HEATHRIDGE','HILLMAN','HOCKING','HOPEVALLEY','ILUKA','JINDALEE','JOONDALUP','KALLAROO','KINROSS','KWINANA','KWINANABEACH','LAKELANDS','LEDA','MADORABAY','MANDURAH','MEADOWSPRINGS','MEDINA','MERRIWA','MINDARIE','MULLALOO','NAVALBASE','NORTHYUNDERUP','OCEANREEF','ORELIA','PARKLANDS','PARMELIA','PEARSALL','PERON','PORTKENNEDY','QUINNSROCKS','RIDGEWOOD','ROCKINGHAM','SAFETYBAY','SANREMO','SECRETHARBOUR','SHOALWATER','SILVERSANDS','SINAGRA','SINGLETON','TAMALAPARK','TAPPING','WAIKIKI','WANNANUP','WANDI','WANNEROO','WARNBRO','WELLARD','AVELEY','BALDIVIS','DAYTON','ELLENBROOK','HENLEYBROOK','HERNEHILL','THEVINES','UPPERSWAN','WESTSWAN')
 * 
 * 
 SELECT sku,city,COUNT(increment_id) AS items, increment_id  FROM `sales_flat_order_item` sol
  INNER JOIN sales_flat_order so ON so.entity_id = sol.order_id
  INNER JOIN sales_flat_order_address sod on so.customer_id=sod.customer_id
  WHERE postcode IN('6025','6027','6028','6030','6031','6036','6038','6055','6056','6065','6069','6165','6167','6168','6169','6170','6171','6172','6173','6174','6175','6180','6208','6209','6210','6946','6966','6968')
  GROUP BY sku,city,increment_id
 *
 SELECT city, count(city) as Orders FROM `sales_flat_order_item` sol
  INNER JOIN sales_flat_order so ON so.entity_id = sol.order_id
  INNER JOIN sales_flat_order_address sod on so.customer_id=sod.customer_id
  WHERE postcode IN('6025','6027','6028','6030','6031','6036','6038','6055','6056','6065','6069','6165','6167','6168','6169','6170','6171','6172','6173','6174','6175','6180','6208','6209','6210','6946','6966','6968')
  GROUP BY city 
 * 
 * 
 * sudo yum --enablerepo=epel install joe
 * 
 * //php my admin install mbstring sudo yum install php56-mbstring
location /dord {
   index index.php;
   error_log /home/www/nutritionwarehouse.com.au/logs/error.log;
   root /home/www/nutritionwarehouse.com.au/public_html;
   location ~ ^/dord/(.+\.php)$ {
         try_files $uri $uri/ =404;
        fastcgi_pass   127.0.0.1:9000;
        fastcgi_param  HTTPS $fastcgi_https;
        fastcgi_index index.php;
        # fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_param  SCRIPT_FILENAME $document_root$fastcgi_script_name;
        #fastcgi_param  HTTP_X_FORWARDED_FOR     $http_x_client_ip;
        include        fastcgi_params; ## See /etc/nginx/fastcgi_params;
        #fastcgi_read_timeout 1000;

   }
        location ~* ^/dord/(.+\.(jpg|jpeg|gif|css|png|js|ico|html|xml|txt))$ {
            root /home/www/nutritionwarehouse.com.au/public_html;
        }
}

//SELECT DISTINCT postcode FROM `geo` WHERE `iso2` LIKE '%wa%'
 * 
 * //
 * 
 * 
 * SELECT DISTINCT email FROM sales_flat_order_address INNER JOIN (
SELECT DISTINCT  postcode, code, default_name, region_id FROM geo INNER JOIN directory_country_region on directory_country_region.default_name = region1
 WHERE code = 'WA' ) geo
 * 
 * 
 * 
 * SELECT email FROM sales_flat_order_address INNER JOIN (
 * SELECT DISTINCT  postcode, code, default_name, region_id FROM geo INNER JOIN directory_country_region on directory_country_region.default_name = region1
 WHERE code = 'WA' ) geo
 * 
 * 

 *  */   