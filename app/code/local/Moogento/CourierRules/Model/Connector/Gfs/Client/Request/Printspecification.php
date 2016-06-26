<?php
/**
 * Created by 4dcode.net
 * User: Alexey Bogatsky
 * Date: 10.02.15
 * Time: 21:02
 */

class Moogento_CourierRules_Model_Connector_Gfs_Client_Request_Printspecification
{
    /** @var  boolean */
    public $MergeDocs = false;
    /** @var  boolean */
    public $PrintDocs = false;
    /** @var  string */
    public $LabelPrinter;
    /** @var  string */
    public $DocumentPrinter;
    /** @var  string "DPL","EPL2","PDF","PNG","ZPLII" */
    public $LabelSpecType = 'PNG';
    /** @var string "BOTTOM_EDGE_OF_TEXT_FIRST","TOP_EDGE_OF_TEXT_FIRST" */
    public $LabelOrientType = "BOTTOM_EDGE_OF_TEXT_FIRST";
    /** @var string "PAPER_4X6","PAPER_7X4.75","STOCK_4X6","STOCK_4X6.75_LEADING_DOC_TAB","STOCK_4X8","STOCK_4X9_LEADING_DOC_TAB" */
    public $LabelStock = "PAPER_4X6";
}