<?php



error_reporting(7);

$template = 4;
$catRoot = 3;
$chunkSize = 100;
//$id1c = 9999;




define('MODX_API_MODE', true);
include_once $_SERVER['DOCUMENT_ROOT'].'/manager/includes/config.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/manager/includes/document.parser.class.inc.php';
$modx = new DocumentParser;
$modx->db->connect();
$modx->getSettings();
startCMSSession(); 
$modx->minParserPasses=2;


//создание бэкапа

if ($_GET['event'] == 'doBackUp') {



  $numbersArray = array(); 

  if ($result = $modx->db->query('SHOW TABLES LIKE "%_site_content_redo_%"')){
    if($modx->db->getRecordCount($result)) { 
        while( $row = $modx->db->getValue( $result ) ) {  
             $numbersArray[] = end(explode("redo_" , $row)); 
          }  
    }else { 
       $numbersArray[] = 0; 
    } 

    $maxKey = max($numbersArray);


    while (count($numbersArray) > 9) {
      $minKey = min($numbersArray);
      $flippedArr = array_flip ( $numbersArray ) ;

      if ($state = $modx->db->query('DROP TABLE IF EXISTS `ruki_site_content_redo_'.($minKey).'`'  ) ) {
 
        $modx->db->query('DROP TABLE IF EXISTS `ruki_site_tmplvar_contentvalues_redo_'.($minKey).'`');
       // $modx->db->query('DROP TABLE IF EXISTS `ruki__catfilter_value_redo_'.($minKey).'`');

        unset($numbersArray[$flippedArr[$minKey]]); 
      }


    }   
 
    sort($numbersArray);
  /*  if ($state = $modx->db->query('  CREATE TABLE ruki_site_content_redo_'.(++$maxKey).' LIKE ruki_site_content')) { 
      $modx->db->query('  INSERT INTO ruki_site_content_redo_'.($maxKey).' SELECT * FROM ruki_site_content;');

      $modx->db->query('  CREATE TABLE ruki_site_tmplvar_contentvalues_redo_'.($maxKey).' LIKE ruki_site_tmplvar_contentvalues');
      $modx->db->query('  INSERT INTO ruki_site_tmplvar_contentvalues_redo_'.($maxKey).' SELECT * FROM ruki_site_tmplvar_contentvalues;');

      $modx->db->query('  CREATE TABLE ruki__catalog_redo_'.($maxKey).' LIKE ruki__catalog');
      $modx->db->query('  INSERT INTO ruki__catalog_redo_'.($maxKey).' SELECT * FROM ruki__catalog;');

      $modx->db->query('  CREATE TABLE ruki__catalog_category_redo_'.($maxKey).' LIKE ruki__catalog_category');
      $modx->db->query('  INSERT INTO ruki__catalog_category_redo_'.($maxKey).' SELECT * FROM ruki__catalog_category;');

      $modx->db->query('  CREATE TABLE ruki__product_redo_'.($maxKey).' LIKE ruki__product');
      $modx->db->query('  INSERT INTO ruki__product_redo_'.($maxKey).' SELECT * FROM ruki__product;');

      $modx->db->query('  CREATE TABLE ruki__catalog_image_redo_'.($maxKey).' LIKE ruki__catalog_image');
      $modx->db->query('  INSERT INTO ruki__catalog_image_redo_'.($maxKey).' SELECT * FROM ruki__catalog_image;');

      $modx->db->query('  CREATE TABLE ruki__product_image_redo_'.($maxKey).' LIKE ruki__product_image');
      $modx->db->query('  INSERT INTO ruki__product_image_redo_'.($maxKey).' SELECT * FROM ruki__product_image;');

      $modx->db->query('  CREATE TABLE ruki__parameter_value_redo_'.($maxKey).' LIKE ruki__parameter_value');
      $modx->db->query('  INSERT INTO ruki__parameter_value_redo_'.($maxKey).' SELECT * FROM ruki__parameter_value;');

      $modx->db->query('  CREATE TABLE ruki__product_parameter_redo_'.($maxKey).' LIKE ruki__product_parameter');
      $modx->db->query('  INSERT INTO ruki__product_parameter_redo_'.($maxKey).' SELECT * FROM ruki__product_parameter;');

      //echo json_encode(array('state' => true , 'text' => "tablebackUpped"));
    } else  echo json_encode(array('state' => false , 'text' => "error")); */

    echo json_encode(array('state' => true , 'text' => "tablebackUpped"));

  }

/*
  $vendorID = addslashes($_POST['vendorID']);
  $sql = "UPDATE ".$modx->getFullTableName("site_content")." AS sc 
      INNER JOIN ".$modx->getFullTableName("site_tmplvar_contentvalues")." AS tv ON sc.id = tv.contentid 
      SET sc.deleted = 1 WHERE tv.`value` = '{$vendorID}' AND  tv.tmplvarid = 23 ";
  $modx->db->query($sql); 
*/

 exit(); 
}


//откат базы

if ($_GET['event'] == 'doRedo') {
  $numbersArray = array(); 
  if ($result = $modx->db->query('SHOW TABLES LIKE "%_site_content_redo_%"')){
    if($modx->db->getRecordCount($result)) { 
        while( $row = $modx->db->getValue( $result ) ) {  
             $numbersArray[] = end(explode("redo_" , $row)); 
          }  
    }else { 
       echo json_encode(array('state' => false , 'text' => "noAcceptedTable" )); 
    } 

    if (count($numbersArray) > 0){
      $maxKey = max($numbersArray);
      if ($state = $modx->db->query('DROP TABLE IF EXISTS `ruki_site_content`'  ) ) {

        $modx->db->query('DROP TABLE IF EXISTS `ruki_site_tmplvar_contentvalues`' );
      //  $modx->db->query('DROP TABLE IF EXISTS `ruki__catfilter_value`' );

         if ($state = $modx->db->query('  CREATE TABLE ruki_site_content LIKE ruki_site_content_redo_'.($maxKey))) { 

          $modx->db->query('  CREATE TABLE ruki_site_tmplvar_contentvalues LIKE ruki_site_tmplvar_contentvalues_redo_'.($maxKey));
        //  $modx->db->query('  CREATE TABLE ruki__catfilter_value LIKE ruki__catfilter_value_redo_'.($maxKey));

          $modx->db->query('  INSERT INTO ruki_site_content SELECT * FROM ruki_site_content_redo_'.($maxKey));
          $modx->db->query('  INSERT INTO ruki_site_tmplvar_contentvalues SELECT * FROM ruki_site_tmplvar_contentvalues_redo_'.($maxKey));
       //   $modx->db->query('  INSERT INTO ruki__catfilter_value SELECT * FROM ruki__catfilter_value_redo_'.($maxKey));

          if ($state = $modx->db->query('DROP TABLE IF EXISTS ruki_site_content_redo_'.$maxKey  ) ) {

            $modx->db->query('DROP TABLE IF EXISTS ruki_site_tmplvar_contentvalues_redo_'.$maxKey  ) ;
         //   $modx->db->query('DROP TABLE IF EXISTS ruki__catfilter_value_redo_'.$maxKey  ) ; 

            echo json_encode(array('state' => true , 'text' => "tableRepaired" , "countStep" => count($numbersArray) - 1)); 
          }
        } 
      }

    }

  }
 exit(); 
}


require_once($_SERVER['DOCUMENT_ROOT'].'/assets/modules/importXLS/Classes/PHPExcel.php');
class chunkReadFilter implements PHPExcel_Reader_IReadFilter 
{
    private $_startRow = 0; 
    private $_endRow = 0; 
    /**  Set the list of rows that we want to read  */ 
    public function setRows($startRow, $chunkSize) { 
        $this->_startRow    = $startRow; 
        $this->_endRow      = $startRow + $chunkSize; 
    } 
     public function readCell($column, $row, $worksheetName = '') { 
        //  Only read the heading row, and the rows that are configured in $this->_startRow and $this->_endRow 
        if (($row == 1) || ($row >= $this->_startRow && $row < $this->_endRow)) { 
            return true; 
        } 
        return false; 
    } 
}


function initF() {
     //$kostyil = $_FILES[$kostyil]['name'];   
 
     if ($_FILES[0]) {
          $extArr =  explode('.', $_FILES[0]['name'] );
          $ext = end($extArr);     
        
          
          if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/xls/')) {
               if (!mkdir($_SERVER['DOCUMENT_ROOT'].'/xls/')) {
                    //echo 'false';
                    //return false;
               }else {
                   // echo 'mkdirOk';
               }
          }  
          
          if (($ext != 'XLS' || $ext != 'xls' || $ext != 'XLSX' || $ext != 'xlsx' || $ext != 'ODS'|| $ext != 'ods' || $ext != 'CSV' || $ext != 'csv' || $ext != 'TXT' || $ext != 'txt') && $_FILES[0]['size'] > 0 && $_FILES[0]['error'] == 0)   {
               $newfileName = time().'.'.$ext;
               if (move_uploaded_file($_FILES[0]['tmp_name'], $_SERVER['DOCUMENT_ROOT'].'/xls/'.$newfileName)) {

                    if ($ext == 'XLS' ||$ext == 'xls' || $ext == 'XLSX' || $ext == 'xlsx' || $ext == 'ODS' || $ext == 'ods' ) {
                      return '{"result":"true","path":"'.$_SERVER['DOCUMENT_ROOT'].'/xls/'.$newfileName.'"}';
                    }else {
                      $objReader = PHPExcel_IOFactory::createReader('CSV');
                      $objReader->setDelimiter(";");
                     // $objReader->setInputEncoding('UTF-16LE');
                      $objReader->setInputEncoding('CP1251');
                      $objPHPExcel = $objReader->load($_SERVER['DOCUMENT_ROOT'].'/xls/'.$newfileName);
                      $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                      $newfileNameCont = time().'.xls';
                      $objWriter->save($_SERVER['DOCUMENT_ROOT'].'/xls/'.$newfileNameCont);
                      return '{"result":"true","path":"'.$_SERVER['DOCUMENT_ROOT'].'/xls/'.$newfileNameCont.'"}';

                    }
                    
               }else {
                    return '{"result":"false","path":"Неверно определен путь к файлу"}';
               }
          }
          return false;
     }
}



function buildTreeCat($root, $modx) {
     return $modx->runSnippet("buildTreeCat" , array('root' => $root ));
}


function uploadimages() {
   
     if ($_FILES[0]) {
          $extArr =  explode('.', $_FILES[0]['name'] );
          $ext = end($extArr);     
            array_pop($extArr);
          $nameWithOutExt = $extArr;
            $nameWithOutExt = implode('.',$nameWithOutExt);
            
          
          if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/upload/')) {
               //echo 'rr';
               if (!mkdir($_SERVER['DOCUMENT_ROOT'].'/upload/')) {
                    //echo 'false';
                    //return false;
               }else {
                   //echo 'mkdirOk';
               }
          }  
          
          if (($ext != 'png' || $ext != 'jpg' || $ext != 'jpeg') && $_FILES[0]['size'] > 0 && $_FILES[0]['error'] == 0)   {
               $newfileName = time().'_'.md5($_FILES[0]['name']).'.'.$ext;
               if (move_uploaded_file($_FILES[0]['tmp_name'], $_SERVER['DOCUMENT_ROOT'].'/upload/'.$newfileName)) {
                    return '{"result":"true","path":"/upload/'.$newfileName.'","realname":"'.$nameWithOutExt.'"}';
               }else {
                    return '{"result":"false","path":"Неверно определен путь к файлу"}';
               }
          }
          return false;
     }
}


function getArrValuesChunk($pathToXLS , $start,  $currentList = 0  , $excludedRows = array() , $stringsCollation = array(), $pgtIndex = false , $currentStep = 1 , $stringsCollationIgnore = array() ) {
     $startRow = $start;
     $inputFileType = 'Excel5';
     //$chunkSize = 100;
     global $chunkSize;

     //set_time_limit(1800);
     //ini_set('memory_liit', '128M');
     $exit = false;           //флаг выхода
     $empty_value = 0;
     $objReader = PHPExcel_IOFactory::createReaderForFile($pathToXLS);
     $objReader->setReadDataOnly(true);
     $chunkFilter = new chunkReadFilter(); 
     $objReader->setReadFilter($chunkFilter); 
     $chunkFilter->setRows($startRow,$chunkSize);      //устанавливаем значение фильтра
     $objPHPExcel = $objReader->load($pathToXLS);       //открываем файл
     $resultShhetList = $objPHPExcel->getSheetNames();
     $objPHPExcel->setActiveSheetIndex($currentList);        //устанавливаем индекс активной страницы
     $objWorksheet = $objPHPExcel->getActiveSheet();   //делаем активной нужную страницу
     $nRow = ($objWorksheet->getHighestRow());
     $nColumn = PHPExcel_Cell::columnIndexFromString($objWorksheet->getHighestColumn());
     if ($nColumn > 85) $nColumn = 85;
     $resultArr = array();
     $itsNotEmpty = true;

     $strictList = false;
     if (count($stringsCollation) > 0) {
          $strictList = true;
     }

     for ($i = $startRow; $i < $startRow + $chunkSize; $i++)     //внутренний цикл по строкам
     {
          $emptyAllCols = false;

          if ($strictList && !in_array( $i,  $stringsCollation  )) {
               continue;
          } 

           if (in_array( $i,  $stringsCollationIgnore  )) {
               continue;
          } 

          if ($pgtIndex !== false){
               $tmp = addslashes($objWorksheet->getCellByColumnAndRow($pgtIndex, $i)->getCalculatedValue());
                if ($tmp == ''){
                     continue;
               }
          }

          if (  @in_array($i-1 , $excludedRows ))  continue;
          
          $readerColIterator = array();
          for ($col = 0; $col < $nColumn; $col++) {
               $tmp = trim(htmlspecialchars($objWorksheet->getCellByColumnAndRow($col, $i)->getCalculatedValue()));
               
                array_push($readerColIterator,  $tmp);
          }
        
          foreach ($readerColIterator AS $elem){

               if ( empty($elem) ){
                    $emptyAllCols = true;
               }else {
                    $emptyAllCols = false;
                    break;
               }
          }


          if ( $emptyAllCols){
              // echo "emptyAllCols<br>";
               $empty_value++;   
               $itsNotEmpty = false;  
          }else {
               $itsNotEmpty = true;  
          }       //проверяем значение на пустоту 


          //echo $empty_value;   

          if ( $itsNotEmpty ) {  
               array_push($resultArr,  $readerColIterator);
          }else {
          }

          $fulldata['data'] = $resultArr;
          $fulldata['meta']['from'] = $startRow;
          $fulldata['meta']['nColumn'] = $nColumn;
          $fulldata['meta']['highestRow'] = $i-$empty_value;
          $fulldata['meta']['currentStep'] = $nRow+$startRow;
          $fulldata['meta']['currentList'] = $currentList;
          $fulldata['meta']['allList'] = $resultShhetList;


          if ($empty_value == 20 || $i >=$nRow+$startRow)       //после 20 пустых значений, завершаем обработку файла, думая, что это конец
         // if ($empty_value == 20 )
          {    
               $exit = true;
               unset($_SESSION['startRow']);
               break;         
          }
     }

     $objPHPExcel->disconnectWorksheets();                  //чистим 
     unset($objPHPExcel); 
     $currentStopped =   $startRow;                         //память
     $startRow += $chunkSize;                     //переходим на следующий шаг цикла, увеличивая строку, с которой будем читать файл
     

     if ($exit || $strictList){
          $fulldata['meta']['finish'] = true; 

          //echo '{"result":"TheEnd","count":"'.$startRow.'"}';
     }else {
          //echo  '{"result":"DooTheNextPart","count":"'.$startRow.'"}';
          $_SESSION['startRow'] = $startRow;
     }

     return $fulldata;
}


if (isset($_GET['uploadfiles'])) {
     echo initF();
}


if (isset($_GET['uploadimages'])) {
     echo uploadimages();
}


if (isset($_GET['buildTreeCat'])) {
     echo buildTreeCat($catRoot , $modx); 
}


if (isset($_GET['getXMLdata']) && $_POST['pathToXLS'] != '') {
     if (!is_numeric($_POST['listIndex'])) {
          $listIndex = 0;
     }else {
          $listIndex = $_POST['listIndex']; 
     }
     echo json_encode( getArrValuesChunk(addslashes($_POST['pathToXLS']) , $_POST['from'],  $listIndex   ) ); 
}



if (isset($_GET['dooImportData']) && $_POST['pathToXLS'] != '' ) {

     $tvCollation = json_decode($_POST["tvCollation"]);
     $filterCollation = json_decode($_POST["filterCollation"]); 
     $imageCollation = json_decode($_POST["imageCollation"]);
     $imageCollationLink = json_decode($_POST["imageCollationLink"]);
     $collationImageCol = json_decode($_POST["collationImageCol"]);
     $stringsCollation = json_decode($_POST["stringsCollation"]);
     $stringsCollationIgnore = json_decode($_POST["stringsCollationIgnore"]);
     $currentSheet = addslashes($_POST["currentSheet"]);
     $imageTVcol = addslashes($_POST["imageTVcol"]);
     $tocat = addslashes($_POST["tocat"]);
     $typeImport = addslashes($_POST["typeImport"]);
     $pathToXLS = addslashes($_POST["pathToXLS"]);
     $callationIndex = addslashes($_POST["callationIndex"]);
     $selectColCat1st_pos = json_decode($_POST["selectColCat1st_pos"]);

     $addToPGT = json_decode($_POST["addToPGT"]);

     $selectAcolLonkImg_pos = json_decode($_POST["selectAcolLonkImg_pos"]);
     $selectAcolLocalImg_pos = json_decode($_POST["selectAcolLocalImg_pos"]);

     $currentPos = addslashes($_POST["startFrom"]);
     $postv = addslashes($_POST["postv"]);
     $vendorID = addslashes($_POST["vendorID"]);

     $arrsXLS = false;

     //$imageCollation  = ( get_object_vars ( $imageCollation  ));

     if (file_exists($pathToXLS)){
          if (!is_numeric($currentPos)) {
               $currentPos = 0;   
          }

          if ($currentPos == 0 ) {
              disAllCats($tocat);
          }

          $pgtIndex = array_search ( "PGT" ,  $tvCollation);
          $arrsXLS  = getArrValuesChunk(addslashes($pathToXLS) , $currentPos+1, $currentSheet, false, $stringsCollation , $pgtIndex , 1 ,  $stringsCollationIgnore);
     }
     

     $imporiRes =  'empty'; 

     if ($typeImport == "allInSelected") {
          
          if ($result = importToOneCat($arrsXLS,$tocat,$tvCollation,  $stringsCollation , $imageCollation,$imageCollationLink,$collationImageCol,$imageTVcol , $template , $modx, $callationIndex, $postv ,$selectColCat1st_pos , $selectAcolLonkImg_pos , $filterCollation , $selectAcolLocalImg_pos , $addToPGT , $vendorID) ) {
               $imporiRes =  $result; 
          } else  $imporiRes =  'error1'; 

     }elseif($typeImport == "toChangedCat"){ 
           
          if ($result = importToOneCat($arrsXLS,$tocat,$tvCollation,  $stringsCollation , $imageCollation,$imageCollationLink,$collationImageCol,$imageTVcol , $template , $modx, $callationIndex, $postv ,$selectColCat1st_pos , $selectAcolLonkImg_pos , $filterCollation , $selectAcolLocalImg_pos , $addToPGT , $vendorID) ) {
              $imporiRes =  $result;
          } else  $imporiRes =  'error2'; 

     }
    


     $retRes['highestRow'] = $arrsXLS['meta']['highestRow'];
     $retRes['finished'] = $arrsXLS['meta']['finish'];
     $retRes['currentStep'] = $arrsXLS['meta']['currentStep'];
     $retRes['meta'] = $imporiRes;
     echo  json_encode( $retRes);

}


//список категорий
function disAllCats($root){
    global $modx;

    $sql =   "SELECT id FROM ".$modx->getFullTableName("site_content")."  
              WHERE  parent = '{$root}' AND isfolder = 1";
    if ($result = $modx->db->query($sql)){
      if ($modx->db->getRecordCount($result) >0 ){
        while ($row = $modx->db->getRow($result)){
          disAllCats($row['id']);
        }
      }
    } 
}


//создание пути - категорий
function createPathWay($nameCats, $rootCreatePath , &$cnt){
     global $template;
     global $catRoot;
     global $modx;
     $contentFolderID = array();
     $catRootInner = $rootCreatePath;
      
     
      foreach($nameCats AS $nameCat) {
           
           $sql =   "SELECT sc.id FROM ".$modx->getFullTableName("site_content")." AS sc 
               WHERE UPPER(sc.pagetitle) = '".(strtoupper ( $nameCat ))."' AND sc.parent = {$catRootInner}  AND sc.isfolder = 1 LIMIT 1";

          // echo $sql;

           if ($result = $modx->db->query($sql)){
                 if ($modx->db->getRecordCount($result) < 1 ) {
                     
                     
                       $alias = GenerAlias($nameCat , $modx);
                       $sql = "INSERT INTO  ".$modx->getFullTableName("site_content")." 
                              (
                                    pagetitle,
                                    alias,
                                    parent,
                                    template,
                                    isfolder,
                                    published
                              ) VALUES (
                                    '".$nameCat."',
                                    '".$alias."',
                                    {$catRootInner},
                                    {$template},
                                    1,
                                    1
                              )";
                            //  echo $sql;
                       if ($resultIN = $modx->db->query($sql)){
                              $cnt++;
                              $contentFolderID[] =  $modx->db->getInsertId();
                              $catRootInner =  $modx->db->getInsertId();
                       }
                       
                 }else {
                       //getid
                       //$contentFolderID = $modx->db->getRow($result)["id"]; // PHP 5.4  OR Higest
                       $tmp  = $modx->db->getRow($result); 
                       $contentFolderID[] = $tmp["id"];
                       $catRootInner = $tmp["id"];
                      // echo '--- next ---';
                 }
           }
           
           
      }

     return $contentFolderID;          
}


function pre($data){
  echo '<pre>';
  print_r($data);
  echo '</pre>';
}



function importToOneCat($arrsXLS,$tocat,$tvCollation, $stringsCollation,$imageCollation,$imageCollationLink,$collationImageCol,$imageTVcol, $template , $modx , $callationIndex, $postv, $catPos=false , $imageLinks = false , $filterCollation , $imageLinksLocal , $addToPGT = false ,  $vendorID = false){

     global $catRoot;
     $noFindedPath = 11;

    // if (! (is_array($arrsXLS) && (is_numeric($tocat) ||  ($catPos!== false) )) ) {echo 'here'; return false;}
     //if ($vendorID == false || $vendorID == 'false') return false;
     
     //if ($catPos!== false) {
     if (is_array($catPos) && count($catPos)) {
          if (is_numeric($tocat)) {
               $rootCreatePath = $tocat;
          }else {
               $rootCreatePath = $catRoot;
          }
          
          $tocat = $tocat; //$noFindedPath;

     }
     //echo $tocat;
     
     $countEvent['added'] = 0;
     $countEvent['updated'] = 0;
     $countEvent['createNewPath'] = 0;


     $flippedArrTv = (@array_flip($tvCollation));
     $flippedArrFLT = (@array_flip($filterCollation));


     foreach($arrsXLS['data'] AS $indexString => $strCols) {
          $contentID  = false;     
          
          if (is_array($catPos) && count($catPos)) {
                  $namesCat = array(); // сделать проверки на массив и тд 
                 // print_r($catPos);
                  foreach ($catPos AS $elem){
                      if (trim($strCols[$elem]) !='') {
                        $namesCat[] = $strCols[$elem];                    
                      }
                  }
                 // print_r($namesCat);
                  if ($resT = createPathWay($namesCat , $rootCreatePath , $countEvent['createNewPath'])){
                    $tocat = end($resT);
               }
          }


          if ($imageLinks!== false) {
              $tmpLnk='';
              foreach ($imageLinks as $keyL => $valueL) {
                if ($strCols[$valueL] != '') {
                  if ($localLink = loadRemoteFile($strCols[$valueL])) {
                    $tmpLnk .= $tmpLnk == '' ? $localLink : '||'.$localLink;
                  }
                } 
              }
          }


          if ($imageLinksLocal!== false) { 
              $tmpLnkLoc='';
              foreach ($imageLinksLocal as $keyL => $valueL) {
                if ($strCols[$valueL] != '') {

                    //$tmpLnkLoc .= $tmpLnkLoc == '' ? "assets/images/".$strCols[$valueL] : '||'."assets/images/".$strCols[$valueL];

                    $tmpLnkLoc .= $tmpLnkLoc == '' ? $strCols[$valueL] : '||'.$strCols[$valueL];

                } 
              } 
          }


          if (is_numeric($addToPGT)) {
            $strCols[$flippedArrTv["PGT"]] .= ' '.$strCols[$addToPGT];
          }

 
          if ($callationIndex === 'false' || $callationIndex === false) {
               
              
               
          }else {
               
               $searchedTVid = array_search($callationIndex,$flippedArrTv);

               if ($searchedTVid == "PGT"){

                  $inWHERE = " c.name = '".$strCols[$flippedArrTv["PGT"]]."' ";
                  $andParent = "AND cc.id_category = {$tocat} ";

               }elseif($searchedTVid == "CONTENT"){
                 
                  $inWHERE = " c.content = '".$strCols[$flippedArrTv["CONTENT"]]."' ";
                  $andParent = "AND cc.id_category = {$tocat} ";

               } /*elseif($searchedTVid == "PRICE"){

                  $inWHERE = " c.price = '".$strCols[$flippedArrTv["PRICE"]]."' ";
                  $andParent = "AND cc.id_category = {$tocat} ";

               } */
               else {
                    return false; 
               } 
          }

          //уникальные - pagetitle и категория
          $inWHERE = " c.name = '".$strCols[$flippedArrTv["PGT"]]."' ";
          $andParent = "AND cc.id_category = {$tocat} ";



         /* $sql = "SELECT sc.id  FROM ".$modx->getFullTableName("site_content")." AS sc 
          INNER JOIN ".$modx->getFullTableName("site_tmplvar_contentvalues")." AS tv ON sc.id = tv.contentid 
          WHERE ".$inWHERE."  ".$andParent."    GROUP BY sc.id   LIMIT 100";*/

          $sql = "SELECT c.id  FROM ".$modx->getFullTableName("_catalog")." AS c 
          INNER JOIN ".$modx->getFullTableName("_catalog_category")." AS cc ON c.id = cc.id_catalog 
          WHERE ".$inWHERE."  ".$andParent."  GROUP BY c.id  LIMIT 100";

          //echo $sql ;

          if ($result = $modx->db->query($sql)){

              $finded = false; 
              if ($modx->db->getRecordCount($result) > 0 ){ 
                $finded = true; 
                while ($ttrow = $modx->db->getRow($result)) {
                    $ttid = $ttrow['id'];
                }
              }

                //echo  $ttid;


               $strCols[$flippedArrTv["PRICE"]]= str_replace(",", ".", $strCols[$flippedArrTv["PRICE"]]);
               $strCols[$flippedArrTv["PRICE"]]= preg_replace("/[^0-9\.]/", "", $strCols[$flippedArrTv["PRICE"]]);



                if (!$finded &&  array_search ( "PGT" ,  $tvCollation) !== false )  {

                    //adddd
                    //echo 'add'; 


                    $alias = GenerAlias($strCols[$flippedArrTv["PGT"]] , $modx);


                    $sql = "INSERT INTO  ".$modx->getFullTableName("_catalog")." 
                         (
                              alias,
                              name,
                              price,
                              currency,
                              content,
                              enabled

                         ) VALUES (
                              '".$alias."', 
                              '".$modx->db->escape($strCols[$flippedArrTv["PGT"]])."',
                              '".$modx->db->escape($strCols[$flippedArrTv["PRICE"]])."',
                              '".$modx->db->escape($strCols[$flippedArrTv["CURRENCY"]])."',
                              '".$strCols[$flippedArrTv["CONTENT"]]."',
                              'y'
                         )";




                    //  echo $sql;
                    if ($result = $modx->db->query($sql)){

                        $contentID = $modx->db->getInsertId();
                         

                        $sql2 = "INSERT INTO  ".$modx->getFullTableName("_catalog_category")." 
                        ( id_catalog, id_category ) VALUES ( {$contentID}, {$tocat} )";

                        $modx->db->query($sql2);

                        $sql3 = "INSERT INTO ".$modx->getFullTableName("_product")." 
                        (id_catalog, pricemod, price, status, content, enabled)
                        VALUES ({$contentID},'+0','".$modx->db->escape($strCols[$flippedArrTv["PRICE"]])."',1,'".$strCols[$flippedArrTv["CONTENT"]]."', 'y')";

                        $modx->db->query($sql3);

                        
                        $productID = $modx->db->getInsertId();


                        $countEvent['added']++;
                        processedTV($productID,$flippedArrTv,$strCols,$modx , $vendorID);
                        // processedFILTER($contentID,$flippedArrFLT,$strCols,$modx);




                         ///pre($stringsCollation);


                        if (($keyImg = array_search ( explode('.',$strCols[$collationImageCol]) [0] , $imageCollation )) !== false) {
                          $imgLinkT =  $imageCollationLink[$keyImg];
                        }else {
                          $imgLinkT = false;
                        }
                            
                        if ($imgLinkT != false ||  $tmpLnk != '' ||  $tmpLnkLoc != '') {
                           processedIMG($contentID,$productID,$imageTVcol,$imgLinkT,$modx , $tmpLnk , $tmpLnkLoc);
                        } 
                        
                    }

                }else { 
                 //echo 'update';
                 //update


                    $contentID = $ttid;
                   // echo $contentID.'update';



                    $sqlF = "SELECT id FROM ".$modx->getFullTableName("_product")." WHERE id_catalog = {$contentID} LIMIT 1";
                   // echo  $sqlF;
                    $result = $modx->db->query($sqlF);
                    $tmp  = $modx->db->getRow($result);
                    $productID = $tmp["id"];

                  //  echo  $productID;


                    $sql2 = "";


                   //  ----------------- пока так -----------
                    
         /*           $params = "name='".( $strCols[$flippedArrTv["PGT"]] !== "" ? $modx->db->escape($strCols[$flippedArrTv["PGT"]]) : " " ) . "',".
                              "price='".( $strCols[$flippedArrTv["PRICE"]] !== "" ? $modx->db->escape($strCols[$flippedArrTv["PRICE"]]) : "0" ) . "',".
                              "content='".( $strCols[$flippedArrTv["CONTENT"]] !== "" ? $modx->db->escape($strCols[$flippedArrTv["CONTENT"]]) : " " ) . "',";
*/

                    $params = ( $strCols[$flippedArrTv["PGT"]] !== "" ? "name='".$modx->db->escape($strCols[$flippedArrTv["PGT"]])."'," : "" ).
                              ( $strCols[$flippedArrTv["PRICE"]] !== "" ? "price='".$modx->db->escape($strCols[$flippedArrTv["PRICE"]])."'," : "" ).
                              ( $strCols[$flippedArrTv["CURRENCY"]] !== "" ? "currency='".$modx->db->escape($strCols[$flippedArrTv["CURRENCY"]])."'," : "" ).
                              ( $strCols[$flippedArrTv["CONTENT"]] !== "" ? "content='".$strCols[$flippedArrTv["CONTENT"]]."'," : "" );

                           /*   "price='".( $strCols[$flippedArrTv["PRICE"]] !== "" ? $modx->db->escape($strCols[$flippedArrTv["PRICE"]]) : "0" ) . "',".
                              "content='".( $strCols[$flippedArrTv["CONTENT"]] !== "" ? $modx->db->escape($strCols[$flippedArrTv["CONTENT"]]) : " " ) . "',";*/
                              

           /*         $params2 ="price='".( $strCols[$flippedArrTv["PRICE"]] !== "" ? $modx->db->escape($strCols[$flippedArrTv["PRICE"]]) : "0" ) . "',".
                              "content='".( $strCols[$flippedArrTv["CONTENT"]] !== "" ? $modx->db->escape($strCols[$flippedArrTv["CONTENT"]]) : " " )  . "',";
           */         
                    $params2 = ( $strCols[$flippedArrTv["PRICE"]] !== "" ? "price='".$modx->db->escape($strCols[$flippedArrTv["PRICE"]])."'," : "" ).
                               ( $strCols[$flippedArrTv["CONTENT"]] !== "" ? "content='".$strCols[$flippedArrTv["CONTENT"]]."'," : "" );          
                 
                    if ($strCols[$flippedArrTv["PGT"]]  == '' && $strCols[$flippedArrTv["CONTENT"]]  == '' && $strCols[$flippedArrTv["PRICE"]]  == ''){

                    }else { 
                      
                      $sql = "UPDATE  ".$modx->getFullTableName("_catalog")." SET {$params} enabled = 'y' WHERE id = {$contentID} LIMIT 1";
                      $sql2 = "UPDATE  ".$modx->getFullTableName("_product")." SET {$params2} enabled = 'y' WHERE id = {$productID} LIMIT 1";
                
                    }

                    

                    //  -----END ------------ пока так -----------



                     /*
                    if ($strCols[$flippedArrTv["PGT"]]  == '' && $strCols[$flippedArrTv["CONTENT"]]  == '' && $strCols[$flippedArrTv["PRICE"]]  == ''){

                    }elseif ($strCols[$flippedArrTv["PGT"]]  == '' && $strCols[$flippedArrTv["CONTENT"]]  != ''){

                      $sql = "UPDATE  ".$modx->getFullTableName("_catalog")." SET  content ='".$modx->db->escape($strCols[$flippedArrTv["CONTENT"]])."', enabled = 'y' WHERE id = {$contentID} LIMIT 1";
                      $sql2 = "UPDATE  ".$modx->getFullTableName("_product")." SET  content ='".$modx->db->escape($strCols[$flippedArrTv["CONTENT"]])."', enabled = 'y' WHERE id = {$productID} LIMIT 1";

                    }elseif ($strCols[$flippedArrTv["PGT"]]  != '' && $strCols[$flippedArrTv["CONTENT"]]  == ''){

                      $sql = "UPDATE  ".$modx->getFullTableName("_catalog")." SET  name ='".$modx->db->escape($strCols[$flippedArrTv["PGT"]])."', enabled = 'y' WHERE id = {$contentID} LIMIT 1";

                    }else { 
                      
                      $sql = "UPDATE  ".$modx->getFullTableName("_catalog")." SET name ='".$modx->db->escape($strCols[$flippedArrTv["PGT"]])."',  content ='".$modx->db->escape($strCols[$flippedArrTv["CONTENT"]])."', enabled = 'y' WHERE id = {$contentID} LIMIT 1";
                      $sql2 = "UPDATE  ".$modx->getFullTableName("_product")." SET  content ='".$modx->db->escape($strCols[$flippedArrTv["CONTENT"]])."', enabled = 'y' WHERE id = {$productID} LIMIT 1";
                
                    }
                    */


                    if (!empty($sql2)) $modx->db->query($sql2);
                    


                  if ($result = $modx->db->query($sql) ){
                       $countEvent['updated']++;
                      processedTV($productID,$flippedArrTv,$strCols,$modx, $vendorID);
                     // processedFILTER($contentID,$flippedArrFLT,$strCols,$modx);
                       


                      if (($keyImg = array_search ( explode('.',$strCols[$collationImageCol]) [0] , $imageCollation )) !== false) {
                        $imgLinkT =  $imageCollationLink[$keyImg];
                      }else {
                        $imgLinkT = false;
                      }

                        if ($imgLinkT != false ||  $tmpLnk != '' ||  $tmpLnkLoc != '') {
                         

                            processedIMG($contentID,$productID,$imageTVcol,$imgLinkT,$modx , $tmpLnk , $tmpLnkLoc);
                        } 

                  }
    
                }

          } 
          //echo mysql_error();        
     }

     clearModxCache();

     return $countEvent;
}

 

function  loadRemoteFile ($link){
/*
  $pattern = '/(https?|ftp):\/\//iu';

  if  ( preg_match ( $pattern ,  $subject)  == 0)  {
    $link = 'http://'.$link;
  }
*/


  //echo $link.'-----------';
   
  $confPath = 'assets/images/upl/';
  $acceptedExtension = array('jpg' , 'jpeg' , 'png' , 'gif' );
  $ext = @end(explode('.' , $link));
  $localImgName = md5($link).'.'.mb_strtolower($ext , "UTF-8");

  if (!in_array($ext , $acceptedExtension)) return false;

  //if ($path == 'good') {
   $dir = substr($localImgName , 0 , 2).'/';
   if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/'.$confPath.$dir)) {
       @mkdir($_SERVER['DOCUMENT_ROOT'].'/'.$confPath.$dir , 0777 , true);
   }

   $localPath = $confPath.$dir.$localImgName;

   $newfile = $_SERVER['DOCUMENT_ROOT'].'/'.$localPath;


   if (file_exists($_SERVER['DOCUMENT_ROOT'].'/'.$localPath)) {
    return $localPath;
   }else {

     /*
      $ch = curl_init($link); 
      $fp = fopen($_SERVER['DOCUMENT_ROOT'].'/'.$localPath, "w");
     // echo $_SERVER['DOCUMENT_ROOT'].'/'.$localPath;
      curl_setopt($ch, CURLOPT_FILE, $fp);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_exec($ch);
      curl_close($ch);
      fclose($fp);
      return $localPath;
      */

      copy($link, $newfile);
      return $localPath;

   }

  
}
  

function clearModxCache(){
     global $modx;

     $modx->clearCache();


     include_once MODX_BASE_PATH . '/manager/processors/cache_sync.class.processor.php';
     $sync= new synccache();
     $sync->setCachepath( MODX_BASE_PATH . "/assets/cache/" );
     $sync->setReport( false );
     ob_start();
     $sync->emptyCache();
     ob_end_clean();

     return;
}
   


function processedIMG($contentID,$productID,$imageTVcol,$imageCollation=false,$modx,$dopImage = false , $dopImageLoc = false) {


    if ($imageCollation != false && $dopImage != false ) {
      $imageCollation = $imageCollation.'||'.$dopImage;
    }elseif ($imageCollation == false && $dopImage != false ) {
       $imageCollation = $dopImage;
    }

    if ($imageCollation != false && $dopImageLoc != false ) {
      $imageCollation = $imageCollation.'||'.$dopImageLoc;
    }elseif ($imageCollation == false && $dopImageLoc != false ) {
       $imageCollation = $dopImageLoc;
    }

     $sql = "SELECT id FROM ".$modx->getFullTableName("_catalog_image")." WHERE id_catalog = {$contentID} LIMIT 1";
     if ($result = $modx->db->query($sql)){
          if ($modx->db->getRecordCount($result) > 0 ){
               //$tvID = $modx->db->getRow($result)['id'];// PHP 5.4  OR Higest
                   $tmp  = $modx->db->getRow($result);
                    $tvID = $tmp["id"];
               $sql = "UPDATE ".$modx->getFullTableName("_catalog_image")." SET `path` = '".$imageCollation."' WHERE id = {$tvID} ";
               $modx->db->query($sql);               
          }else {

               $sql = "INSERT INTO ".$modx->getFullTableName("_catalog_image")." (id_catalog,path,i) VALUES ({$contentID}, '".$imageCollation."',99)";
               $modx->db->query($sql);
               
          }
     }     


     $sql2 = "SELECT id FROM ".$modx->getFullTableName("_product_image")." WHERE id_product = {$productID} LIMIT 1";
     if ($result = $modx->db->query($sql2)){
          if ($modx->db->getRecordCount($result) > 0 ){
               //$tvID = $modx->db->getRow($result)['id'];// PHP 5.4  OR Higest
                   $tmp  = $modx->db->getRow($result);
                    $tvID = $tmp["id"];
               $sql2 = "UPDATE ".$modx->getFullTableName("_product_image")." SET `path` = '".$imageCollation."' WHERE id = {$tvID} ";
               $modx->db->query($sql2);               
          }else {

               $sql2 = "INSERT INTO ".$modx->getFullTableName("_product_image")." (id_product,path,i) VALUES ({$productID}, '".$imageCollation."',99)";
               $modx->db->query($sql2);
               
          }
     }     

}



//processedTV($productID,$flippedArrTv,$strCols,$modx , $vendorID);
//processedTV($contentID,$productID,$flippedArrTv,$strCols,$modx, $vendorID);

function processedTV($contentID,$flippedArrTv,$strCols,$modx, $vendorID) {

    // print_r($flippedArrTv);
     foreach ($flippedArrTv AS $index => $value){
          if (!is_numeric($index)) continue;

          if (array_key_exists (  $value ,  $strCols )) {
              
               if ($index=="27") {

                    if ($strCols[$value]=="0") {
                         $strCols[$value] = "Нет в наличии";
                    } else  $strCols[$value] = "В наличии";

               } 

              $sql = "SELECT id FROM ".$modx->getFullTableName("_parameter_value")." WHERE id_parameter = {$index} AND value = '".$strCols[$value]."' LIMIT 1";
             

              if ($result = $modx->db->query($sql)){
                   if ($modx->db->getRecordCount($result) > 0 ){
                       //$tvID = $modx->db->getRow($result)['id'];// PHP 5.4  OR Higest
                             $tmp  = $modx->db->getRow($result);
                             $tvID = $tmp["id"];
                        //UPDATE 

                        $sql2 = "SELECT * FROM ".$modx->getFullTableName("_product_parameter")." WHERE id_product = {$contentID} AND id_value = {$tvID} LIMIT 1";

                        if ($result2 = $modx->db->query($sql2)){
                          if ($modx->db->getRecordCount($result2) == 0 ){
                            
                            $sql = "INSERT INTO ".$modx->getFullTableName("_product_parameter")." (id_product,id_value) VALUES ({$contentID} ,{$tvID})";
                            $modx->db->query($sql);

                          }
                        }
                        

                   }else {

                      $sql = "INSERT INTO ".$modx->getFullTableName("_parameter_value")." (id_parameter,value,hash) VALUES ({$index} ,'".$strCols[$value]."','".md5($strCols[$value])."')";
                      $modx->db->query($sql);

                      $idValue =  $modx->db->getInsertId();

                      $sql = "INSERT INTO ".$modx->getFullTableName("_product_parameter")." (id_product,id_value) VALUES ({$contentID} ,{$idValue})";
                      $modx->db->query($sql);
                     
                        
                   }
              }
               //echo mysql_error();
          }
     }

}



/*

function processedFILTER($contentID,$flippedArrFLT,$strCols,$modx) {
     foreach ($flippedArrFLT AS $index => $value){
          if (!is_numeric($index)) continue;

          //pre($strCols);

         // pre($flippedArrTv);

          if (array_key_exists (  $value ,  $strCols )) {
               
               $sql = "SELECT id FROM ".$modx->getFullTableName("_catfilter_value")." WHERE cf_id = {$index} AND itemid = {$contentID} LIMIT 1";
               if ($result = $modx->db->query($sql)){
                    if ($modx->db->getRecordCount($result) > 0 ){
                        //$tvID = $modx->db->getRow($result)['id'];// PHP 5.4  OR Higest
                              $tmp  = $modx->db->getRow($result);
                              $tvID = $tmp["id"];
                         //UPDATE 
                         $sql = "UPDATE ".$modx->getFullTableName("_catfilter_value")." SET `value` = '".$strCols[$value]."' WHERE id = {$tvID} ";
                         $modx->db->query($sql);
                         
                    }else {
                         $sql = "INSERT INTO ".$modx->getFullTableName("_catfilter_value")." (cf_id,itemid,`value`) VALUES ({$index} ,{$contentID} , '".$strCols[$value]."' )";
                         $modx->db->query($sql);
                    }
               }
               //echo mysql_error();
          }
     }
}

*/







function GenerAlias($txt , $modx) {
     $trans = array("а"=>"a", "б"=>"b", "в"=>"v", "г"=>"g", "д"=>"d", "е"=>"e",
        "ё"=>"jo", "ж"=>"zh", "з"=>"z", "и"=>"i", "й"=>"jj", "к"=>"k", "л"=>"l",
        "м"=>"m", "н"=>"n", "о"=>"o", "п"=>"p", "р"=>"r", "с"=>"s", "т"=>"t", "у"=>"u",
        "ф"=>"f", "х"=>"kh", "ц"=>"c", "ч"=>"ch", "ш"=>"sh", "щ"=>"shh", "ы"=>"y",
        "э"=>"eh", "ю"=>"yu", "я"=>"ya", "А"=>"a", "Б"=>"b", "В"=>"v", "Г"=>"g",
        "Д"=>"d", "Е"=>"e", "Ё"=>"jo", "Ж"=>"zh", "З"=>"z", "И"=>"i", "Й"=>"jj",
        "К"=>"k", "Л"=>"l", "М"=>"m", "Н"=>"n", "О"=>"o", "П"=>"p", "Р"=>"r", "С"=>"s",
        "Т"=>"t", "У"=>"u", "Ф"=>"f", "Х"=>"kh", "Ц"=>"c", "Ч"=>"ch", "Ш"=>"sh",
        "Щ"=>"shh", "Ы"=>"y", "Э"=>"eh", "Ю"=>"yu", "Я"=>"ya", " "=>"-", "."=>"-",
        ","=>"-", "_"=>"-", "+"=>"-", ":"=>"-", ";"=>"-", "!"=>"-", "?"=>"-");
          
     $alias= addslashes($txt);
     $alias= strip_tags(strtr($alias, $trans));
     $alias= preg_replace("/[^a-zA-Z0-9-]/", '', $alias);
     $alias= preg_replace('/([-]){2,}/', '-', $alias);
     $alias= trim($alias, '-');
     
     if(strlen($alias) > 20) $alias= trim(substr($alias, 0, 20), '-');
     
     do{
      $rr= $modx->db->query("SELECT id FROM ".$modx->getFullTableName("_catalog")." WHERE alias='{$alias}' LIMIT 1");
      if($rr && $modx->db->getRecordCount($rr)==1) $alias .= rand(1, 9);
     }while(($rr && $modx->db->getRecordCount($rr)==1) || ! $rr);
     if( ! $rr) $alias= false;
     
     return $alias;
}