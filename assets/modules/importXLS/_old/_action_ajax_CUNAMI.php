<?php
 

error_reporting(7);

$template = 7;
$catRoot = 4;
$chunkSize = 100;



define('MODX_API_MODE', true);
include_once $_SERVER['DOCUMENT_ROOT'].'/manager/includes/config.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/manager/includes/document.parser.class.inc.php';
$modx = new DocumentParser;
$modx->db->connect();
$modx->getSettings();
startCMSSession(); 
$modx->minParserPasses=2;




if (isset($_GET['dooreplace'])) {


	$table = $modx->getFullTableName( 'site_content' );   
    $result = $modx->db->select( 'id , pagetitle', $table, 'template = 7 AND isfolder = 0' , "id ASC");   

    if( $modx->db->getRecordCount( $result ) >= 1 ) {

        while( $row = $modx->db->getRow( $result ) ) {  
            $modxID = $row['id'];
            $modxPGT = trim(rd($row['pagetitle']));

 			echo "ook".$modxID.' - '.$modxPGT.'<br>';

            $fields = array('pagetitle'  => $modxPGT);  
            $resultUPD = $modx->db->update( $fields, $table, 'id = "' . $modxID . '"' );
           
        }  

    }

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
                    //echo 'fallse';
                    //return false;
               }else {
                   // echo 'mkdirOk';
               }
          }  
          
          if (($ext != 'xls' || $ext != 'xlsx' || $ext != 'ods') && $_FILES[0]['size'] > 0 && $_FILES[0]['error'] == 0)   {
               $newfileName = time().'.'.$ext;
               if (move_uploaded_file($_FILES[0]['tmp_name'], $_SERVER['DOCUMENT_ROOT'].'/xls/'.$newfileName)) {
                    return '{"result":"true","path":"'.$_SERVER['DOCUMENT_ROOT'].'/xls/'.$newfileName.'"}';
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
               echo 'rr';
               if (!mkdir($_SERVER['DOCUMENT_ROOT'].'/upload/')) {
                    echo 'fallse';
                    //return false;
               }else {
                    echo 'mkdirOk';
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







/*
function getArrValues($pathToXLS , $start,  $nColumn , $currentList = 0 , $stringsCollation = array()  , $pgtIndex = false) {
     require_once($_SERVER['DOCUMENT_ROOT'].'/assets/modules/importXLS/Classes/PHPExcel.php');
     $objPHPExcel = PHPExcel_IOFactory::load($pathToXLS);
     $objPHPExcel->setActiveSheetIndex($currentList);
     $aSheet = $objPHPExcel->getActiveSheet();
     
     $resultShhetList = $objPHPExcel->getSheetNames();
  
     if ($nColumn == 'false' || $nColumn == false) {
          $nColumn = PHPExcel_Cell::columnIndexFromString($aSheet->getHighestColumn());
          
     }

     $nRow = ($aSheet->getHighestRow());
     
     $resultArr = array();
     $resultRowArr = array(); 
     
     $strictList = false;
     if (count($stringsCollation) > 0) {
          $strictList = true;
     }



     for ($iterRow = 1; $iterRow <= $nRow; $iterRow++) {
          if ($strictList && !in_array( $iterRow,  $stringsCollation  )) {
               continue;
          } 

*/
/*
          if ($pgtIndex !== false){
               $tmp = addslashes($aSheet->getCellByColumnAndRow($pgtIndex, $iterRow)->getCalculatedValue());
                if ($tmp == ''){
                     continue;
               }
          }
           *//*
          for ($iterCol = $start; $iterCol < $nColumn; $iterCol++) { 
               $tmp = addslashes($aSheet->getCellByColumnAndRow($iterCol, $iterRow)->getCalculatedValue());
               //array_push($resultRowArr, iconv('utf-8', 'cp1251', $tmp));
               array_push($resultRowArr, $tmp);
          }
          array_push($resultArr,  $resultRowArr);
          $resultRowArr = array();
     }
     $fulldata['data'] = $resultArr;
     $fulldata['meta']['from'] = $start;
     $fulldata['meta']['to'] = $nColumn;
     $fulldata['meta']['currentList'] = $currentList;
     $fulldata['meta']['allList'] = $resultShhetList;
     
     
     return $fulldata;
}


*/















function getArrValuesChunk($pathToXLS , $start,  $currentList = 0  , $excludedRows = array() , $stringsCollation = array(), $pgtIndex = false , $currentStep = 1 ) {
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

     if (is_array($stringsCollation) && count($stringsCollation) > 0) {
        $strictList = true;
        $minRow = min($stringsCollation)-1;
        $maxRow = max($stringsCollation)+1;
        $startRow = $minRow;
        $rowLimiter  = $maxRow;
        $chunkFilter->setRows($minRow,$maxRow); 

     }else {
		$chunkFilter->setRows($startRow,$chunkSize); 
		$rowLimiter  = $startRow + $chunkSize;
     }

          //устанавливаем знаечние фильтра
     $objPHPExcel = $objReader->load($pathToXLS);       //открываем файл
     $resultShhetList = $objPHPExcel->getSheetNames();
     $objPHPExcel->setActiveSheetIndex($currentList);        //устанавливаем индекс активной страницы
     $objWorksheet = $objPHPExcel->getActiveSheet();   //делаем активной нужную страницу
     $nRow = ($objWorksheet->getHighestRow());
     $nColumn = PHPExcel_Cell::columnIndexFromString($objWorksheet->getHighestColumn());
     if ($nColumn > 25) $nColumn = 25;
     $resultArr = array();
     $itsNotEmpty = true;

     $strictList = false;
     if (count($stringsCollation) > 0) {
          $strictList = true;
     }

     for ($i = $startRow; $i < $rowLimiter; $i++)     //внутренний цикл по строкам
     {
          $emptyAllCols = false;

          if ($strictList && !in_array( $i,  $stringsCollation  )) {
               continue;
          } 

          if ($pgtIndex !== false){
               $tmp = trim(rd( (string) $objWorksheet->getCellByColumnAndRow($pgtIndex, $i)->getCalculatedValue()));
                if ($tmp == ''){
                     continue; 
               }
          }

          if (  @in_array($i-1 , $excludedRows ))  continue;

         // $value = trim(htmlspecialchars($objWorksheet->getCellByColumnAndRow(0, $i)->getValue()));      //получаем наименование  


          /*Манипуляции с данными каким Вам угодно способом, в PHPExcel их превеликое множество*/
          
          $readerColIterator = array();
          for ($col = 0; $col < $nColumn; $col++) {
               $tmp = trim(rd( (string) $objWorksheet->getCellByColumnAndRow($col, $i)->getCalculatedValue()));
               
               /*if ( empty($tmp) ){
                    $emptyAllCols = true;
               }else $emptyAllCols = false;*/
                array_push($readerColIterator,  $tmp);
          }
         // array_push($readerColIterator,  $i);

         // print_r($readerColIterator);
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


          if ($empty_value == 20 || $i >$nRow+$startRow)       //после 20 пустых значений, завершаем обработку файла, думая, что это конец
          //if ($empty_value == 20 )       //после 20 пустых значений, завершаем обработку файла, думая, что это конец
          {    
               $exit = true;
               unset($_SESSION['startRow']);
               break;         
          }

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







function rd ($str){
	$re = '/([\\"\/\'\`])/ui';
	$str = preg_replace($re, ' ', $str);
	$str = trim(preg_replace("/\s{2,}/",' ',$str));
	$str = stripslashes($str);
	return $str;
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
     $imageCollation = json_decode($_POST["imageCollation"]);
     $stringsCollation = json_decode($_POST["stringsCollation"]);
     $currentSheet = addslashes($_POST["currentSheet"]);
     $imageTVcol = addslashes($_POST["imageTVcol"]);
     $tocat = addslashes($_POST["tocat"]);
     $typeImport = addslashes($_POST["typeImport"]);
     $pathToXLS = addslashes($_POST["pathToXLS"]);
     $callationIndex = addslashes($_POST["callationIndex"]);
     $selectColCat1st_pos = json_decode($_POST["selectColCat1st_pos"]);
     $currentPos = addslashes($_POST["startFrom"]);
     $postv = addslashes($_POST["postv"]);
     $updateOnlyPrice = addslashes($_POST["updateOnlyPrice"]);
     $arrsXLS = false;

     $imageCollation  = ( get_object_vars ( $imageCollation  ));
    // print_r($imageCollation );
     if (file_exists($pathToXLS)){
          if (!is_numeric($currentPos)) {
               $currentPos = 0;  

              


          }

          if ($currentPos == 0 ) {
              disAllCats($tocat);
          }


          $pgtIndex = array_search ( "PGT" ,  $tvCollation);
          // getArrValuesChunk($pathToXLS , $start,  $currentList = 0  , $excludedRows = array() , $stringsCollation = array(), $pgtIndex = false 
          //$crossToBase = getArrValuesChunk($pathToXLS, $currentPos+1 , $currentSheet  , $excludedRows  );
          $arrsXLS  = getArrValuesChunk(addslashes($pathToXLS) , $currentPos+1, $currentSheet, false, $stringsCollation , $pgtIndex);

         // pre($arrsXLS);
     }
     

     

     if ($updateOnlyPrice === 'true') {
        if ($result = updateOnlyPrice($arrsXLS,$tvCollation, $template , $modx , $callationIndex) ) {
               $imporiRes =  $result;
          }

     }elseif ($typeImport == "allInSelected") {
          if ($result = importToOneCat($arrsXLS,$tocat,$tvCollation,  $stringsCollation , $imageCollation,$imageTVcol , $template , $modx, $callationIndex, $postv) ) {
               $imporiRes =  $result;
          }
     }elseif($typeImport == "toChangedCat"){
           
          
          if ($result = importToOneCat($arrsXLS,$tocat,$tvCollation,  $stringsCollation , $imageCollation,$imageTVcol , $template , $modx, $callationIndex, $postv ,$selectColCat1st_pos) ) {
              $imporiRes =  $result;
          }
     }


     


     $retRes['highestRow'] = $arrsXLS['meta']['highestRow'];
     $retRes['finished'] = $arrsXLS['meta']['finish'];
     $retRes['currentStep'] = $arrsXLS['meta']['currentStep'];
     $retRes['meta'] = $imporiRes;
     echo  json_encode( $retRes);
}






if (is_numeric($_GET['markDeletes'])) {
	$root=$_GET['markDeletes'];
	if (isset($_GET['doo'])) {
		echo json_encode(markDeleteItems($root , true));
	}else {
		echo json_encode(markDeleteItems($root));
	}
	

}




function updateOnlyPrice($arrsXLS,$tvCollation, $template , $modx , $callationIndex) {

  $flippedArrTv = (@array_flip($tvCollation));
 // print_r($flippedArrTv);

  //print_r($arrsXLS);


    $countEvent['added'] = 0;
     $countEvent['updated'] = 0;
     $countEvent['createNewPath'] = 0;
     $countEvent['notFound'] = 0;


    foreach($arrsXLS['data'] AS $indexString => $strCols) {
          $contentID  = false;     
          if ($callationIndex === 'false' || $callationIndex === false) {
               $inWHERE = " sc.pagetitle LIKE '".(trim($strCols[$flippedArrTv["PGT"]]))."' ";
          }else {
               $searchedTVid = array_search($callationIndex,$flippedArrTv);
               if ($searchedTVid == "PGT"){
                    $inWHERE = " sc.pagetitle LIKE '".(trim($strCols[$flippedArrTv["PGT"]]))."' ";   
               }elseif($searchedTVid == "CONTENT"){
                    $inWHERE = " sc.content = '".$strCols[$flippedArrTv["CONTENT"]]."' ";
               }elseif(is_numeric($searchedTVid)){
                    $inWHERE = " tv.value = '".$strCols[$flippedArrTv[$searchedTVid]]."' AND  tv.tmplvarid = '".$searchedTVid."' ";
               }else {
                    return false; 
               } 
          }


          $sql = "SELECT sc.id FROM ".$modx->getFullTableName("site_content")." AS sc 
          INNER JOIN ".$modx->getFullTableName("site_tmplvar_contentvalues")." AS tv ON sc.id = tv.contentid 
          WHERE ".$inWHERE."  LIMIT 1";
           
          // echo $sql;

          //if (false){ 
          if ($result = mysql_query($sql)){
               if (mysql_num_rows($result) < 1 ){
                    //echo 'nooo';
                     $countEvent['notFound']++;
               }else {
                    $tmp  = mysql_fetch_assoc($result);
                    $contentID = $tmp["id"];
                  
                    processedTVOnluUpdatePrice($contentID,$flippedArrTv,$strCols,$modx);
                    $countEvent['updated']++;

               }
          } 
          //echo mysql_error();       
     }


    return $countEvent;












}

 

function disAllCats($root){
  global $modx;

 // echo $root.'<hr>';

  $sql =   "UPDATE ".$modx->getFullTableName("site_content")."  SET statement_update = 1
            WHERE  parent = {$root} AND isfolder = 0";
  mysql_query($sql);
 // echo mysql_error(); 

 


  $sql =   "SELECT id FROM ".$modx->getFullTableName("site_content")."  
            WHERE  parent = '{$root}' AND isfolder = 1";
  if ($result = mysql_query($sql)){
    if (mysql_num_rows($result) >0 ){
      while ($row = mysql_fetch_assoc($result)){
        disAllCats($row['id']);
      }
    }
  }
  //echo mysql_error(); 
}











function markDeleteItems($root, $doo = false){
    global $modx;
    $cnt = 0;

    $sql =   "SELECT pagetitle  FROM ".$modx->getFullTableName("site_content")."  
            WHERE  id = {$root} ";
     if ($result = mysql_query($sql)){
		    if (mysql_num_rows($result) >0 ){
			    if ($row = mysql_fetch_assoc($result)){
			    	$pgt=$row['pagetitle'];
			    }
		    }
	   }


    $sqlDoo =   "UPDATE ".$modx->getFullTableName("site_content")."  SET deleted = 1
            WHERE  parent = {$root} AND isfolder = 0 AND statement_update = 1 AND deleted = 0";

    $sqlCount =   "SELECT COUNT(id) AS cnt  FROM ".$modx->getFullTableName("site_content")."  
            WHERE  parent = {$root} AND isfolder = 0 AND statement_update = 1 AND deleted = 0";


   if ($doo) {
   	mysql_query($sqlDoo);
    $cnt = mysql_affected_rows();
   }else {
   	    if ($result = mysql_query($sqlCount)){
		    if (mysql_num_rows($result) >0 ){
			    if ($row = mysql_fetch_assoc($result)){
			    	$cnt=$row['cnt'];
			    }
		    }
	   }

   }


//echo 'root - '.$root.', cnt - '.$cnt.'<br>';



  $sql =   "SELECT id FROM ".$modx->getFullTableName("site_content")."  
            WHERE  parent = '{$root}' AND isfolder = 1";
  if ($result = mysql_query($sql)){
    if (mysql_num_rows($result) >0 ){
      while ($row = mysql_fetch_assoc($result)){
      	
      	$tmp =  markDeleteItems($row['id'] , $doo);
      	//print_r($tmp);
        $cnt += $tmp['cnt'] ;
      }
     

    }

 	return array (
      		"cnt" => $cnt,
      		"pgt" => $pgt,
      		"root" => $root
      );
  }
  return 0 ;
  //echo mysql_error(); 
}











function getCatID($nameCat, $modx , &$cnt){
     global $template;
     global $catRoot;
     $contentFolderID = false;
     
     $sql =   "SELECT sc.id FROM ".$modx->getFullTableName("site_content")." AS sc 
               WHERE  sc.pagetitle = '{$nameCat}' AND sc.isfolder = 1 LIMIT 1";
     if ($result = mysql_query($sql)){
          if (mysql_num_rows($result) < 1 ){
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
                         {$catRoot},
                         {$template},
                         1,
                         1
                    )";

               if ($resultIN = mysql_query($sql)){
                    $cnt++;
                    $contentFolderID = mysql_insert_id();
               }
               
          }else {
               //getid
               //$contentFolderID = mysql_fetch_assoc($result)["id"]; // PHP 5.4  OR Higest
                  $tmp  = mysql_fetch_assoc($result); 
                  $contentFolderID = $tmp["id"];
          }
     }
     return $contentFolderID;          
}















function createPathWay($nameCats, $rootCreatePath , &$cnt){
     global $template;
     global $catRoot;
     global $modx;
     $contentFolderID = array();
     $catRootInner = $rootCreatePath;
      
      foreach($nameCats AS $nameCat) {
        if ($nameCat == '') continue;
           
           $sql =   "SELECT sc.id FROM ".$modx->getFullTableName("site_content")." AS sc 
               WHERE  sc.pagetitle = '{$nameCat}' AND sc.parent = {$catRootInner}  AND sc.isfolder = 1 LIMIT 1";
           if ($result = mysql_query($sql)){
                 if (mysql_num_rows($result) < 1 ){
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

                       if ($resultIN = mysql_query($sql)){
                              $cnt++;
                              $contentFolderID[] = mysql_insert_id();
                              $catRootInner = mysql_insert_id();
                       }
                       
                 }else {
                       //getid
                       //$contentFolderID = mysql_fetch_assoc($result)["id"]; // PHP 5.4  OR Higest
                       $tmp  = mysql_fetch_assoc($result); 
                       $contentFolderID[] = $tmp["id"];
                       $catRootInner = $tmp["id"];
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





function importToOneCat($arrsXLS,$tocat,$tvCollation, $stringsCollation,$imageCollation,$imageTVcol, $template , $modx , $callationIndex, $postv, $catPos=false){

     global $catRoot;

    // print_r($arrsXLS);
     if (! (is_array($arrsXLS) && (is_numeric($tocat) ||  ($catPos!== false) )) ) return false;
     
      

     if ($catPos!== false) {
          if (is_numeric($tocat)) {
               $rootCreatePath = $tocat;
          }else {
               $rootCreatePath = $catRoot;
          }
          
          $tocat = $noFindedPath;

     }
     
     $countEvent['added'] = 0;
     $countEvent['updated'] = 0;
     $countEvent['createNewPath'] = 0;

     $flippedArrTv = (@array_flip($tvCollation));
     foreach($arrsXLS['data'] AS $indexString => $strCols) {
          $contentID  = false;     
          
         

          if ($catPos!== false) {
                  $namesCat = array(); // сделать проверки на массив и тд 
                  $re = '/([a-zA-Zа-яА-Я\d\s\/\\-]*)/ui';
                  foreach ($catPos AS $elem){
                      // $namesCat[] = mysql_escape_string(trim($strCols[$elem])); 

                  
                      preg_match_all($re, $strCols[$elem], $matches);
                      $nameTmp = implode ( ' ' , $matches[0] );
                      $namesCat[] = trim(preg_replace("/\s{2,}/",' ',$nameTmp));


                  }
                  if ($resT = createPathWay($namesCat , $rootCreatePath , $countEvent['createNewPath'])){
                    $tocat = end($resT);
               }
          }
 
 
            
          if ($callationIndex === 'false' || $callationIndex === false) {
               
               $inWHERE = " sc.pagetitle = '".$strCols[$flippedArrTv["PGT"]]."' ";
               
          }else {
               
                
                
               $searchedTVid = array_search($callationIndex,$flippedArrTv);

               if ($searchedTVid == "PGT"){
                    $inWHERE = " sc.pagetitle = '".rd(trim($strCols[$flippedArrTv["PGT"]]))."' ";
               }elseif($searchedTVid == "CONTENT"){
                    $inWHERE = " sc.content = '".$strCols[$flippedArrTv["CONTENT"]]."' ";
               }elseif(is_numeric($searchedTVid)){
                    $inWHERE = " tv.value = '".$strCols[$flippedArrTv[$searchedTVid]]."' AND  tv.tmplvarid = '".$searchedTVid."' ";
               }else {
                    return false; 
               } 
          }

          $sql = "SELECT sc.id FROM ".$modx->getFullTableName("site_content")." AS sc 
          INNER JOIN ".$modx->getFullTableName("site_tmplvar_contentvalues")." AS tv ON sc.id = tv.contentid 
          WHERE ".$inWHERE."  AND sc.parent = {$tocat} LIMIT 1";
           

          //if (false){ 
          if ($result = mysql_query($sql)){
               if (mysql_num_rows($result) < 1 ){
                    $alias = GenerAlias($strCols[$flippedArrTv["PGT"]] , $modx);
                    $sql = "INSERT INTO  ".$modx->getFullTableName("site_content")." 
                         (
                              pagetitle,
                              alias,
                              parent,
                              content,
                              template,
                              menuindex,
                              published,
                              statement_update
                         ) VALUES (
                              '".rd(trim($strCols[$flippedArrTv["PGT"]]))."',
                              '".$alias."',
                              {$tocat},
                              '".$strCols[$flippedArrTv["CONTENT"]]."',
                              {$template},
                              {$indexString},
                              1,
                              2
                         )";  
                    if ($result = mysql_query($sql)){
                         $contentID = mysql_insert_id();
                         $countEvent['added']++;
                         processedTV($contentID,$flippedArrTv,$strCols,$modx);

                         processedClearCrossTV($contentID, $strCols[$flippedArrTv[24]] , $strCols[$flippedArrTv[26]]);

                         if ($imageCollation[$stringsCollation[$indexString]-1] != '') {
                            processedIMG($contentID,$imageTVcol,$imageCollation[$stringsCollation[$indexString]-1],$modx);
                        } 

                      //  pre($flippedArrTv);

                        addsToCross ($strCols[$flippedArrTv[24]] , $strCols[$flippedArrTv[26]], $strCols[$flippedArrTv[23]], $strCols[$flippedArrTv[25]]);
                        processedPrice($contentID,$flippedArrTv,$strCols); //its FUNC special for prolegion
                        processedPosstV($contentID,$postv); //its FUNC special for prolegion

                        
                    }
               }else {
                    //$contentID = mysql_fetch_assoc($result)["id"]; // PHP 5.4  OR Higest
                       $tmp  = mysql_fetch_assoc($result);
                       $contentID = $tmp["id"];
                  
                    $sql = "UPDATE  ".$modx->getFullTableName("site_content")." SET content ='".$strCols[$flippedArrTv["CONTENT"]]."' , statement_update = 2,  pagetitle='".$strCols[$flippedArrTv["PGT"]]."' WHERE id = {$contentID} LIMIT 1";
                    if ($result = mysql_query($sql)){
                         $countEvent['updated']++;
                        processedTV($contentID,$flippedArrTv,$strCols,$modx);

                        processedClearCrossTV($contentID, $strCols[$flippedArrTv[24]] , $strCols[$flippedArrTv[26]]);
                       // pre($flippedArrTv);
                        addsToCross ($strCols[$flippedArrTv[24]] , $strCols[$flippedArrTv[26]], $strCols[$flippedArrTv[23]], $strCols[$flippedArrTv[25]]);
                        processedPrice($contentID,$flippedArrTv,$strCols);//its FUNC special for prolegion
                        processedPosstV($contentID,$postv); //its FUNC special for prolegion
                         

  

                        if ($imageCollation[$stringsCollation[$indexString]-1] != '') {
                            processedIMG($contentID,$imageTVcol,$imageCollation[$stringsCollation[$indexString]-1],$modx);
                        } 
                         
                         //echo 'UPDATED';
                    }
               }
          } 
          //echo mysql_error();       
     }

     clearModxCache();
     return $countEvent;
}





 







function  addsToCross ($vendor_numb , $vendor_origin_numb, $vendor, $vendor_origin){
  global $modx;
  if ($vendor_numb == '' || $vendor_origin_numb == '') return false;
  //pre (func_get_args());
  $sql = "INSERT INTO  ".$modx->getFullTableName("__crossBase")." 
     (
          vendor,
          vendor_numb,
          vendor_origin,
          vendor_origin_numb,
          vendor_clear,
          vendor_numb_clear,
          vendor_origin_clear,
          vendor_origin_numb_clear
     ) VALUES (
          '".$vendor."',
          '".$vendor_numb."',
          '".$vendor_origin."',
          '".$vendor_origin_numb."',
          '".preg_replace("/[\s-_]+/ui", '' ,strtoupper($vendor))."',
          '".preg_replace("/[\s-_]+/ui", '' ,strtoupper($vendor_numb))."',
          '".preg_replace("/[\s-_]+/ui", '' ,strtoupper($vendor_origin))."',
          '".preg_replace("/[\s-_]+/ui", '' ,strtoupper($vendor_origin_numb))."'
     )ON DUPLICATE KEY UPDATE 
          vendor = '".$vendor."' , 
          vendor_origin = '".$vendor_origin."' , 
          vendor_numb_clear = '".preg_replace("/[\s-_]+/ui", '' ,strtoupper($vendor_numb))."' ,
          vendor_origin_numb_clear = '".preg_replace("/[\s-_]+/ui", '' ,strtoupper($vendor_origin_numb))."' 
          ";
  mysql_query($sql) or die (mysql_error());
}
   






function clearModxCache(){
     global $modx;
     $modx->clearCache();
     include_once MODX_BASE_PATH . '/manager/processors/cache_sync.class.processor.php';
     $sync= new synccache();
     $sync->setCachepath( MODX_BASE_PATH . "/assets/cache/" );
     $sync->setReport( false );
     $sync->emptyCache();
}
   







function processedIMG($contentID,$imageTVcol,$imageCollation,$modx) {
	
/*
	echo $contentID.'<br>';
	echo $imageTVcol.'<br>';
	echo $imageCollation.'<br>'; 
*/


     $sql = "SELECT id FROM ".$modx->getFullTableName("site_tmplvar_contentvalues")." WHERE tmplvarid = {$imageTVcol} AND contentid = {$contentID} LIMIT 1";
     if ($result = mysql_query($sql)){
          if (mysql_num_rows($result) > 0 ){
               //$tvID = mysql_fetch_assoc($result)['id'];// PHP 5.4  OR Higest
                   $tmp  = mysql_fetch_assoc($result);
                    $tvID = $tmp["id"];
               $sql = "UPDATE ".$modx->getFullTableName("site_tmplvar_contentvalues")." SET `value` = '".$imageCollation."' WHERE id = {$tvID} ";
               mysql_query($sql);               
          }else {
               $sql = "INSERT INTO ".$modx->getFullTableName("site_tmplvar_contentvalues")." (tmplvarid,contentid,value) VALUES ({$imageTVcol} ,{$contentID} , '".$imageCollation."' )";
               mysql_query($sql);
               
          }
     }     
}





function processedTV($contentID,$flippedArrTv,$strCols,$modx) {
     foreach ($flippedArrTv AS $index => $value){
          if (!is_numeric($index)) continue;
          
          if (array_key_exists (  $value ,  $strCols )) {
               
               $sql = "SELECT id FROM ".$modx->getFullTableName("site_tmplvar_contentvalues")." WHERE tmplvarid = {$index} AND contentid = {$contentID} LIMIT 1";
               if ($result = mysql_query($sql)){
                    if (mysql_num_rows($result) > 0 ){
                        //$tvID = mysql_fetch_assoc($result)['id'];// PHP 5.4  OR Higest
                              $tmp  = mysql_fetch_assoc($result);
                              $tvID = $tmp["id"];
                         //UPDATE 
                         $sql = "UPDATE ".$modx->getFullTableName("site_tmplvar_contentvalues")." SET `value` = '".$strCols[$value]."' WHERE id = {$tvID} ";
                         mysql_query($sql);
                         
                    }else {
                         $sql = "INSERT INTO ".$modx->getFullTableName("site_tmplvar_contentvalues")." (tmplvarid,contentid,value) VALUES ({$index} ,{$contentID} , '".$strCols[$value]."' )";
                         mysql_query($sql);
                         
                    }
               }
               //echo mysql_error();
          }
     }
}




function processedTVOnluUpdatePrice($contentID,$flippedArrTv,$strCols,$modx) {
/*
	echo $contentID.'<br>';

	print_r($flippedArrTv);

	print_r($strCols);

*/
     foreach ($flippedArrTv AS $index => $value){
         if (!is_numeric($index)) continue;
         if ($index !=2 && $index !=17 ) {
         	//echo $index.'-continue<br>';
          	continue;
		}
          
          if (array_key_exists (  $value ,  $strCols )) {
               
               $sql = "SELECT id FROM ".$modx->getFullTableName("site_tmplvar_contentvalues")." WHERE tmplvarid = {$index} AND contentid = {$contentID} LIMIT 1";
               if ($result = mysql_query($sql)){
                    if (mysql_num_rows($result) > 0 ){
                        //$tvID = mysql_fetch_assoc($result)['id'];// PHP 5.4  OR Higest
                              $tmp  = mysql_fetch_assoc($result);
                              $tvID = $tmp["id"];
                         //UPDATE 
                         $sql = "UPDATE ".$modx->getFullTableName("site_tmplvar_contentvalues")." SET `value` = '".$strCols[$value]."' WHERE id = {$tvID} ";
                         mysql_query($sql);
                         
                    }else {
                         $sql = "INSERT INTO ".$modx->getFullTableName("site_tmplvar_contentvalues")." (tmplvarid,contentid,value) VALUES ({$index} ,{$contentID} , '".$strCols[$value]."' )";
                         mysql_query($sql);
                         
                    }
               }
               //echo mysql_error();
          }
     }
}




function processedClearCrossTV($contentID, $numb , $numbOrigin ){
  global $modx;
  $numb = preg_replace("/[\s-_]+/ui", '' ,strtoupper($numb));
  $numbOrigin = preg_replace("/[\s-_]+/ui", '' ,strtoupper($numbOrigin));


   $sql = "SELECT id FROM ".$modx->getFullTableName("site_tmplvar_contentvalues")." WHERE tmplvarid = 28 AND contentid = {$contentID} LIMIT 1";
   if ($result = mysql_query($sql)){
        if (mysql_num_rows($result) > 0 ){
                  $tmp  = mysql_fetch_assoc($result);
                  $tvID = $tmp["id"];
             $sql = "UPDATE ".$modx->getFullTableName("site_tmplvar_contentvalues")." SET `value` = '".$numb."' WHERE id = {$tvID} ";
             mysql_query($sql);
        }else {
            $sql = "INSERT INTO ".$modx->getFullTableName("site_tmplvar_contentvalues")." (tmplvarid,contentid,value) VALUES (28 ,{$contentID} , '".$numb."' )";
             mysql_query($sql); 
      }
   }


  $sql = "SELECT id FROM ".$modx->getFullTableName("site_tmplvar_contentvalues")." WHERE tmplvarid = 29 AND contentid = {$contentID} LIMIT 1";
   if ($result = mysql_query($sql)){
        if (mysql_num_rows($result) > 0 ){
                  $tmp  = mysql_fetch_assoc($result);
                  $tvID = $tmp["id"];
             $sql = "UPDATE ".$modx->getFullTableName("site_tmplvar_contentvalues")." SET `value` = '".$numbOrigin."' WHERE id = {$tvID} ";
             mysql_query($sql);
        }else {
            $sql = "INSERT INTO ".$modx->getFullTableName("site_tmplvar_contentvalues")." (tmplvarid,contentid,value) VALUES (29 ,{$contentID} , '".$numbOrigin."' )";
             mysql_query($sql); 
      }
   }


}






function processedPosstV($contentID,$value) {
  if ($value == '') return false;
  //echo 'vv'.$value;
  global $modx;
  $index = 22;      
  $sql = "SELECT id FROM ".$modx->getFullTableName("site_tmplvar_contentvalues")." WHERE tmplvarid = {$index} AND contentid = {$contentID} LIMIT 1";
  if ($result = mysql_query($sql)){
       if (mysql_num_rows($result) > 0 ){
           //$tvID = mysql_fetch_assoc($result)['id'];// PHP 5.4  OR Higest
                 $tmp  = mysql_fetch_assoc($result);
                 $tvID = $tmp["id"];
            //UPDATE 
            $sql = "UPDATE ".$modx->getFullTableName("site_tmplvar_contentvalues")." SET `value` = '".$value."' WHERE id = {$tvID} ";
            mysql_query($sql);
            
       }else {
            $sql= "INSERT INTO ".$modx->getFullTableName("site_tmplvar_contentvalues")." (tmplvarid,contentid,value) VALUES ({$index} ,{$contentID} , '".$value."' )";
            mysql_query($sql);
            
       }
  }
  //echo mysql_error();
        
}




function processedPrice($docid,$flippedArrTv,$strCols) {

  global $modx;

  /*
  echo 'contentid'.$contentID.'<br>';
  print_r($flippedArrTv);
  print_r($strCols);
*/
  $price1c = $strCols[$flippedArrTv[2]];

if (is_numeric($price1c )) {
  $price_g1_non_cash = $price1c*1.068*1.02564; //Ценовая группа 1 (б/нал)
  $price_g2_non_cash = $price1c*1.104*1.02564; //Ценовая группа 2 (б/нал)
  $price_g3_non_cash = $price1c*1.14*1.02564; //Ценовая группа 3 (б/нал)
  $price_g4_non_cash = $price1c*1.20*1.02564; //Ценовая группа 4 (б/нал)
  $price_g5_non_cash = $price1c*1.60*1.02564; //Ценовая группа 5 (б/нал)
 
  $price_g1_cash = $price1c*1.068; //Ценовая группа 1 (нал)
  $price_g2_cash = $price1c*1.104; //Ценовая группа 2 (нал)
  $price_g3_cash = $price1c*1.14; //Ценовая группа 3 (нал)
  $price_g4_cash = $price1c*1.20; //Ценовая группа 4 (нал)
  $price_g5_cash = $price1c*1.60; //Ценовая группа 5 (нал)
     

        
   
  mysql_query( "INSERT INTO ".$modx->getFullTableName( '_1c_price' )." SET itemid='{$docid}', id1c_pricetype='Цена завышения', price='{$price1c}', enabled='y'  ON DUPLICATE KEY UPDATE `price` = '{$price1c}' " );
  
  mysql_query( "INSERT INTO ".$modx->getFullTableName( '_1c_price' )." SET itemid='{$docid}', id1c_pricetype='Ценовая группа 1 (б/нал)', price='{$price_g1_non_cash}', enabled='y'  ON DUPLICATE KEY UPDATE `price` = '{$price_g1_non_cash}' " );
  mysql_query( "INSERT INTO ".$modx->getFullTableName( '_1c_price' )." SET itemid='{$docid}', id1c_pricetype='Ценовая группа 2 (б/нал)', price='{$price_g2_non_cash}', enabled='y'  ON DUPLICATE KEY UPDATE `price` = '{$price_g2_non_cash}' " );
  mysql_query( "INSERT INTO ".$modx->getFullTableName( '_1c_price' )." SET itemid='{$docid}', id1c_pricetype='Ценовая группа 3 (б/нал)', price='{$price_g3_non_cash}', enabled='y'  ON DUPLICATE KEY UPDATE `price` = '{$price_g3_non_cash}' " );
  mysql_query( "INSERT INTO ".$modx->getFullTableName( '_1c_price' )." SET itemid='{$docid}', id1c_pricetype='Ценовая группа 4 (б/нал)', price='{$price_g4_non_cash}', enabled='y'  ON DUPLICATE KEY UPDATE `price` = '{$price_g4_non_cash}' " );
  mysql_query( "INSERT INTO ".$modx->getFullTableName( '_1c_price' )." SET itemid='{$docid}', id1c_pricetype='Ценовая группа 5 (б/нал)', price='{$price_g5_non_cash}', enabled='y'  ON DUPLICATE KEY UPDATE `price` = '{$price_g5_non_cash}' " );
 

  mysql_query( "INSERT INTO ".$modx->getFullTableName( '_1c_price' )." SET itemid='{$docid}', id1c_pricetype='Ценовая группа 1', price='{$price_g1_cash}', enabled='y'  ON DUPLICATE KEY UPDATE `price` = '{$price_g1_cash}' " );
  mysql_query( "INSERT INTO ".$modx->getFullTableName( '_1c_price' )." SET itemid='{$docid}', id1c_pricetype='Ценовая группа 2', price='{$price_g2_cash}', enabled='y'  ON DUPLICATE KEY UPDATE `price` = '{$price_g2_cash}' " );
  mysql_query( "INSERT INTO ".$modx->getFullTableName( '_1c_price' )." SET itemid='{$docid}', id1c_pricetype='Ценовая группа 3', price='{$price_g3_cash}', enabled='y'  ON DUPLICATE KEY UPDATE `price` = '{$price_g3_cash}' " );
  mysql_query( "INSERT INTO ".$modx->getFullTableName( '_1c_price' )." SET itemid='{$docid}', id1c_pricetype='Ценовая группа 4', price='{$price_g4_cash}', enabled='y'  ON DUPLICATE KEY UPDATE `price` = '{$price_g4_cash}' " );
  mysql_query( "INSERT INTO ".$modx->getFullTableName( '_1c_price' )." SET itemid='{$docid}', id1c_pricetype='Ценовая группа 5', price='{$price_g5_cash}', enabled='y'  ON DUPLICATE KEY UPDATE `price` = '{$price_g5_cash}' " );
}



}

 



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
     
     if(strlen($alias) > 4) $alias= trim(substr($alias, 0,4), '-');
     
     do{
          $rr= mysql_query("SELECT id FROM  ".$modx->getFullTableName("site_content")." WHERE alias='{$alias}' LIMIT 1");
          if($rr && mysql_num_rows($rr)==1) $alias .= azrand(1);
     }while(($rr && mysql_num_rows($rr)==1) || ! $rr);
     if( ! $rr) $alias= false;
     
     return $alias;
}




function azrand($length = 32) {
  $range = range('a', 'z');
  $index = array_rand($range, 1);
  return $range[$index];
}


function izrand($length = 32, $numeric = false) {
    $random_string = "";
    while(strlen($random_string)<$length && $length > 0) {
        if($numeric === false) {
            $randnum = mt_rand(0,61);
            $random_string .= ($randnum < 10) ?
                chr($randnum+48) : ($randnum < 36 ? 
                    chr($randnum+55) : $randnum+61);
        } else {
            $randnum = mt_rand(0,9);
            $random_string .= chr($randnum+48);
        }
    }
    return $random_string;
}

  