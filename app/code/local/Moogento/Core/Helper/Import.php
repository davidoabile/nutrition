<?php


class Moogento_Core_Helper_Import extends Mage_Core_Helper_Abstract
{
    public function prepareImportFile($importSetting)
    {
        switch ($importSetting["import_type"]) {
            case 'file':
                return $this->prepareImportPath($importSetting["file_data"]);
            case 'url':
                return $this->prepareImportUrl($importSetting["url_data"]);
            case 'ftp':
                $ftpSettings = array(
                    "host"     => $importSetting["ftp_host"],
                    "port"     => $importSetting["ftp_port"] ? $importSetting["ftp_port"] : 21,
                    "user"     => $importSetting["ftp_username"],
                    "username" => $importSetting["ftp_username"],
                    "password" => $importSetting["ftp_pass"],
                    "path"     => $importSetting["ftp_path"],
                    "file"     => $importSetting["ftp_file"],
                );
                return $this->prepareImportFtp($ftpSettings);
        }

        return false;
    }

    public function parseImport($importSetting)
    {
        $fileType
            = isset($importSetting['file_type']) && $importSetting['file_type'] ? $importSetting['file_type'] : 'csv';

            $fileToImport = $this->prepareImportFile($importSetting);

        if (!$fileToImport) {
            throw new Exception('Cannot read import file');
        }

            if ($fileType == 'xml') {
            $columns = $this->_prepareImportColumnsList($importSetting);
            $xmlRoot
                     =
                isset($importSetting['xml_element']) && $importSetting['xml_element'] ? $importSetting['xml_element']
                    : 'product';

            return $this->parseXml($fileToImport, $xmlRoot, $columns);
        } else {
            return $this->parseCsv($fileToImport,
            isset($importSetting['column_delimiter']) && $importSetting['column_delimiter']
                ? $importSetting['column_delimiter'] : ',');
        }
    }

    public function parseCsv($filename, $delimiter = ',')
    {
        $header = false;
        $data   = array();
        if (($handle = @fopen($filename, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                if (!$header) {
                    $header = $row;
                } else {
                    $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
            return $data;
        }

        return $data;
    }

    public function parseXml($filename, $root, $columns)
    {
        $doc                     = new DOMDocument();
        $doc->preserveWhiteSpace = false;
        if (!$doc->load($filename)) {
            unlink($filename);
            throw new Exception('Cannot parse xml');
        }

        $entries = $doc->getElementsByTagName($root);
        $data    = array();

        if ($entries->length) {
            foreach ($entries as $entry) {
                $productData = array();
                foreach ($entry->childNodes as $node) {
                    if (in_array($node->nodeName, $columns)) {
                        $productData[ $node->nodeName ] = $node->nodeValue;
                    }
                }
                if (count($productData)) {
                    $data[] = $productData;
                }
            }
        }
        unlink($filename);

        return $data;
    }

    public function prepareImportPath($path)
    {

        $helper = Mage::helper('moogento_core');
        if (!$path) {
            throw new Exception($helper->__('File import path is not defined'));
        }
        if ($path[0] !== '/') {
            $path = '/' . $path;
        }
        $filename = realpath(Mage::getBaseDir()) . $path;

        if (!file_exists($filename)) {
            throw new Exception($helper->__('File %s does not exists', $filename));
        }
        if (!is_readable($filename)) {
            throw new Exception($helper->__('File %s is not readable', $filename));
        }

        return $filename;
    }

    public function prepareImportUrl($url)
    {
        $helper = Mage::helper('moogento_stockeasy');

        if (!$url) {
            throw new Exception($helper->__('Import URL is not defined'));
        }

        if (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0) {
            $url = 'http://' . $url;
        }

        $filePath = DS . "temp_files" . DS . time();
        $baseDir  = Mage::getBaseDir('var');
        if (!file_exists($baseDir . DS . "temp_files")) {
            mkdir($baseDir . DS . "temp_files", 777);
        }
        $fileName = $baseDir . $filePath;

        if (false && ini_get('allow_url_fopen')) {
            $fp      = fopen($url, "rb");
            $newFile = false;
            if ($fp) {
                $newFile = fopen($fileName, "wb");

                if ($newFile) {
                    while (!feof($fp)) {
                        fwrite($newFile, fread($fp, 1024 * 8), 1024 * 8);
                    }
                }
            }

            if ($fp) {
                fclose($fp);
            }

            if ($newFile) {
                fclose($newFile);
            }

            return $fileName;
        } else if (function_exists('curl_init')) {
            $fp = fopen($fileName, "wb");
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_TIMEOUT, 28800);
            if (curl_exec($ch) === false) {
                fclose($fp);
                curl_close($ch);
                unlink($fileName);
                throw new Exception(curl_error($ch));
            }
            fclose($fp);
            curl_close($ch);

            return $fileName;
        } else {
            throw new Exception($helper->__('You need to install cURL on server or change php setting allow_url_fopen to 1'));
        }
    }

    public function prepareImportFtp($ftpSettings)
    {
        $filePath = DS . "temp_files" . DS . time();
        $baseDir  = Mage::getBaseDir('var');
        if (!file_exists($baseDir . DS . "temp_files")) {
            mkdir($baseDir . DS . "temp_files", 0777);
        }
        $fileName = realpath($baseDir) . $filePath;

        $fp       = fopen($fileName, 'w');
        try {
            $io = new Varien_Io_Ftp();
            $io->open($ftpSettings);
            $io->read($ftpSettings['file'], $fp);
            $io->close();
            fclose($fp);

            return $fileName;
        } catch (Exception $e) {
            if (function_exists('ftp_ssl_connect')) {
                try {
                    $ftpSettings['ssl'] = 1;
                    $io = new Varien_Io_Ftp();
                    $io->open($ftpSettings);

                    $io->read($ftpSettings['file'], $fp);
                    $io->close();
                    fclose($fp);
                    return $fileName;

                } catch (Exception $e) {
                    $io = new Varien_Io_Sftp();
                    $io->open($ftpSettings);
                    $io->read($ftpSettings['file'], $fp);
                    $io->close();
                    fclose($fp);

                    return $fileName;
                }
            } else {
                $io = new Varien_Io_Sftp();
                $io->open($ftpSettings);
                $io->read($ftpSettings['file'], $fp);
                $io->close();
                fclose($fp);

                return $fileName;
            }
        }
    }
} 