<?php

error_reporting(7);

$template = 4;
$catRoot = 2;
$chunkSize = 100;
$prefixDB = "altast_";

define('MODX_API_MODE', true);
include_once $_SERVER['DOCUMENT_ROOT'] . '/manager/includes/config.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/manager/includes/document.parser.class.inc.php';
$modx = new DocumentParser;
$modx->db->connect();
$modx->getSettings();
startCMSSession();
$modx->minParserPasses = 2;


/*
$prod_id = $modx->db->query("SELECT id FROM " . $modx->getFullTableName("site_content") . " WHERE template = {$template} AND isfolder = 0");
$prod_id_res = $modx->db->makeArray($prod_id);
foreach ($prod_id_res as $p_val) {
    foreach ($p_val as $m_key => $m_val) {
        $prod_id_output[] = $m_val;
    }
}
$prod_pgt = $modx->db->query("SELECT pagetitle FROM " . $modx->getFullTableName("site_content") . " WHERE template = {$template} AND isfolder = 0");
$prod_pgt_res = $modx->db->makeArray($prod_pgt);
foreach ($prod_pgt_res as $p_val) {
    foreach ($p_val as $m_key => $m_val) {
        $prod_pgt_output[] = $m_val;
    }
}

function array_combine_($keys, $values) {
    $result = array();
    foreach ($keys as $i => $k) {
        $result[$k][] = $values[$i];
    }
    array_walk($result, create_function('&$v', '$v = (count($v) == 1)? array_pop($v): $v;'));
    return $result;
}

$prod_made = array_combine_(($prod_pgt_output), $prod_id_output);
*/
// echo "<pre>";
// print_r($prod_made);
// echo "</pre>";


$prod_made = array();
$prod_id = $modx->db->query("SELECT id, pagetitle
	FROM " . $modx->getFullTableName("site_content") . "
	WHERE template = {$template} AND isfolder = 0");
if ($prod_id && $modx->db->getRecordCount($prod_id)) {
    while ($row = $modx->db->getRow($prod_id)) {
        $prod_made[$row['pagetitle']][$row['id']] = $row['id'];
    }
}



if ($_GET['event'] == 'doBackUp') {

    echo json_encode(array('state' => true, 'text' => "tablebackUpped"));

    exit();
}

if ($_GET['event'] == 'doRedo') {
    exit();
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/assets/modules/importXLS/Classes/PHPExcel.php');

class chunkReadFilter implements PHPExcel_Reader_IReadFilter {

    private $_startRow = 0;
    private $_endRow = 0;

    /**  Set the list of rows that we want to read  */
    public function setRows($startRow, $chunkSize) {
        $this->_startRow = $startRow;
        $this->_endRow = $startRow + $chunkSize;
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
        $extArr = explode('.', $_FILES[0]['name']);
        $ext = end($extArr);


        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/xls/')) {
            if (!mkdir($_SERVER['DOCUMENT_ROOT'] . '/xls/')) {
                //echo 'fallse';
                //return false;
            } else {
                // echo 'mkdirOk';
            }
        }

        if (($ext != 'xls' || $ext != 'xlsx' || $ext != 'ods' || $ext != 'csv' || $ext != 'txt') && $_FILES[0]['size'] > 0 && $_FILES[0]['error'] == 0) {
            $newfileName = time() . '.' . $ext;
            if (move_uploaded_file($_FILES[0]['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . '/xls/' . $newfileName)) {

                if ($ext == 'xls' || $ext == 'xlsx' || $ext == 'ods') {
                    return '{"result":"true","path":"' . $_SERVER['DOCUMENT_ROOT'] . '/xls/' . $newfileName . '"}';
                } else {
                    $objReader = PHPExcel_IOFactory::createReader('CSV');
                    $objReader->setDelimiter(";");
                    // $objReader->setInputEncoding('UTF-16LE');
                    $objReader->setInputEncoding('CP1251');
                    $objPHPExcel = $objReader->load($_SERVER['DOCUMENT_ROOT'] . '/xls/' . $newfileName);
                    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                    $newfileNameCont = time() . '.xls';
                    $objWriter->save($_SERVER['DOCUMENT_ROOT'] . '/xls/' . $newfileNameCont);
                    return '{"result":"true","path":"' . $_SERVER['DOCUMENT_ROOT'] . '/xls/' . $newfileNameCont . '"}';
                }
            } else {
                return '{"result":"false","path":"Неверно определен путь к файлу"}';
            }
        }
        return false;
    }
}

function buildTreeCat($root, $modx) {
    return $modx->runSnippet("buildTreeCat", array('root' => $root));
}

function uploadimages() {

    if ($_FILES[0]) {
        $extArr = explode('.', $_FILES[0]['name']);
        $ext = end($extArr);
        array_pop($extArr);
        $nameWithOutExt = $extArr;
        $nameWithOutExt = implode('.', $nameWithOutExt);


        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/upload/')) {
            echo 'rr';
            if (!mkdir($_SERVER['DOCUMENT_ROOT'] . '/upload/')) {
                //echo 'fallse';
                //return false;
            } else {
                //echo 'mkdirOk';
            }
        }

        if (($ext != 'png' || $ext != 'jpg' || $ext != 'jpeg') && $_FILES[0]['size'] > 0 && $_FILES[0]['error'] == 0) {
            $newfileName = time() . '_' . md5($_FILES[0]['name']) . '.' . $ext;
            if (move_uploaded_file($_FILES[0]['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . '/upload/' . $newfileName)) {
                return '{"result":"true","path":"/upload/' . $newfileName . '","realname":"' . $nameWithOutExt . '"}';
            } else {
                return '{"result":"false","path":"Неверно определен путь к файлу"}';
            }
        }
        return false;
    }
}

function getArrValuesChunk($pathToXLS, $start, $currentList = 0, $excludedRows = array(), $stringsCollation = array(), $pgtIndex = false, $currentStep = 1, $stringsCollationIgnore = array()) {
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
    $chunkFilter->setRows($startRow, $chunkSize);      //устанавливаем значение фильтра
    $objPHPExcel = $objReader->load($pathToXLS);       //открываем файл
    $resultShhetList = $objPHPExcel->getSheetNames();
    $objPHPExcel->setActiveSheetIndex($currentList);        //устанавливаем индекс активной страницы
    $objWorksheet = $objPHPExcel->getActiveSheet();   //делаем активной нужную страницу
    $nRow = ($objWorksheet->getHighestRow());
    $nColumn = PHPExcel_Cell::columnIndexFromString($objWorksheet->getHighestColumn());
    if ($nColumn > 85)
        $nColumn = 85;
    $resultArr = array();
    $itsNotEmpty = true;

    $strictList = false;
    if (count($stringsCollation) > 0) {
        $strictList = true;
    }

    for ($i = $startRow; $i < $startRow + $chunkSize; $i++) {     //внутренний цикл по строкам
        $emptyAllCols = false;

        if ($strictList && !in_array($i, $stringsCollation)) {
            continue;
        }

        if (in_array($i, $stringsCollationIgnore)) {
            continue;
        }

        if ($pgtIndex !== false) {
            $tmp = addslashes($objWorksheet->getCellByColumnAndRow($pgtIndex, $i)->getCalculatedValue());
            if ($tmp == '') {
                continue;
            }
        }

        if (@in_array($i - 1, $excludedRows))
            continue;

        // $value = trim(htmlspecialchars($objWorksheet->getCellByColumnAndRow(0, $i)->getValue()));      //получаем наименование  


        /* Манипуляции с данными каким Вам угодно способом, в PHPExcel их превеликое множество */

        $readerColIterator = array();
        for ($col = 0; $col < $nColumn; $col++) {
            $tmp = trim(htmlspecialchars($objWorksheet->getCellByColumnAndRow($col, $i)->getCalculatedValue()));

            /* if ( empty($tmp) ){
              $emptyAllCols = true;
              }else $emptyAllCols = false; */
            array_push($readerColIterator, $tmp);
        }
        // array_push($readerColIterator,  $i);
        // print_r($readerColIterator);
        foreach ($readerColIterator AS $elem) {

            if (empty($elem)) {
                $emptyAllCols = true;
            } else {
                $emptyAllCols = false;
                break;
            }
        }



        if ($emptyAllCols) {
            // echo "emptyAllCols<br>";
            $empty_value++;
            $itsNotEmpty = false;
        } else {
            $itsNotEmpty = true;
        }       //проверяем значение на пустоту 


        if ($empty_value == 20 || $i >= $nRow + $startRow) {       //после 20 пустых значений, завершаем обработку файла, думая, что это конец
            // if ($empty_value == 20 )       //после 20 пустых значений, завершаем обработку файла, думая, что это конец
            $exit = true;
            unset($_SESSION['startRow']);
            break;
        }

        //echo $empty_value;   

        if ($itsNotEmpty) {
            array_push($resultArr, $readerColIterator);
        } else {
            
        }

        $fulldata['data'] = $resultArr;
        $fulldata['meta']['from'] = $startRow;
        $fulldata['meta']['nColumn'] = $nColumn;
        $fulldata['meta']['highestRow'] = $i - $empty_value;
        $fulldata['meta']['currentStep'] = $nRow + $startRow;
        $fulldata['meta']['currentList'] = $currentList;
        $fulldata['meta']['allList'] = $resultShhetList;
    }

    $objPHPExcel->disconnectWorksheets();                  //чистим 
    unset($objPHPExcel);
    $currentStopped = $startRow;                         //память
    $startRow += $chunkSize;                     //переходим на следующий шаг цикла, увеличивая строку, с которой будем читать файл


    if ($exit || $strictList) {
        $fulldata['meta']['finish'] = true;

        //echo '{"result":"TheEnd","count":"'.$startRow.'"}';
    } else {
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
    echo buildTreeCat($catRoot, $modx);
}

if (isset($_GET['getXMLdata']) && $_POST['pathToXLS'] != '') {
    if (!is_numeric($_POST['listIndex'])) {
        $listIndex = 0;
    } else {
        $listIndex = $_POST['listIndex'];
    }
    echo json_encode(getArrValuesChunk(addslashes($_POST['pathToXLS']), $_POST['from'], $listIndex));
}







if (isset($_GET['dooImportData']) && $_POST['pathToXLS'] != '') {

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

    if (file_exists($pathToXLS)) {
        if (!is_numeric($currentPos)) {
            $currentPos = 0;
        }

        if ($currentPos == 0) {
            disAllCats($tocat);
        }

        $pgtIndex = array_search("PGT", $tvCollation);
        // getArrValuesChunk($pathToXLS , $start,  $currentList = 0  , $excludedRows = array() , $stringsCollation = array(), $pgtIndex = false 
        //$crossToBase = getArrValuesChunk($pathToXLS, $currentPos+1 , $currentSheet  , $excludedRows  );
        $arrsXLS = getArrValuesChunk(addslashes($pathToXLS), $currentPos + 1, $currentSheet, false, $stringsCollation, $pgtIndex, 1, $stringsCollationIgnore);
    }

    $imporiRes = 'empty';

    if ($typeImport == "allInSelected") {

        if ($result = importToOneCat($arrsXLS, $tocat, $tvCollation, $stringsCollation, $imageCollation, $imageCollationLink, $collationImageCol, $imageTVcol, $template, $modx, $callationIndex, $postv, $selectColCat1st_pos, $selectAcolLonkImg_pos, $filterCollation, $selectAcolLocalImg_pos, $addToPGT, $vendorID)) {
            $imporiRes = $result;
        } else
            $imporiRes = 'error1';
    }elseif ($typeImport == "toChangedCat") {

        if ($result = importToOneCat($arrsXLS, $tocat, $tvCollation, $stringsCollation, $imageCollation, $imageCollationLink, $collationImageCol, $imageTVcol, $template, $modx, $callationIndex, $postv, $selectColCat1st_pos, $selectAcolLonkImg_pos, $filterCollation, $selectAcolLocalImg_pos, $addToPGT, $vendorID)) {
            $imporiRes = $result;
        } else
            $imporiRes = 'error2';
    }

    $retRes['highestRow'] = $arrsXLS['meta']['highestRow'];
    $retRes['finished'] = $arrsXLS['meta']['finish'];
    $retRes['currentStep'] = $arrsXLS['meta']['currentStep'];
    $retRes['meta'] = $imporiRes;
    echo json_encode($retRes);
}

function disAllCats($root) {
    global $modx;

    $modx->db->query("UPDATE " . $modx->getFullTableName("site_content") . " SET published = '0' WHERE template = '4'");
    $modx->db->query("UPDATE " . $modx->getFullTableName("site_content") . " SET published = '1' WHERE template = '4' AND pagetitle = 'Каталог' AND id = '4'");
    $modx->db->query("UPDATE " . $modx->getFullTableName("site_content") . " SET published = '1' WHERE template = '4' AND isfolder = '1'");

    $sql = "SELECT id FROM " . $modx->getFullTableName("site_content") . "  
            WHERE  parent = '{$root}' AND isfolder = 1";
    if ($result = $modx->db->query($sql)) {
        if ($modx->db->getRecordCount($result) > 0) {
            while ($row = $modx->db->getRow($result)) {
                disAllCats($row['id']);
            }
        }
    }
    //echo $modx->db->getLastError(); 
}

function createPathWay($nameCats, $rootCreatePath, &$cnt) {
    global $template;
    global $catRoot;
    global $modx;
    $contentFolderID = array();
    $catRootInner = $rootCreatePath;

    foreach ($nameCats AS $nameCat) {

        $sql = "SELECT sc.id FROM " . $modx->getFullTableName("site_content") . " AS sc 
               WHERE UPPER(sc.pagetitle) = '" . (strtoupper($nameCat)) . "' AND sc.parent = {$catRootInner}  AND sc.isfolder = 1 LIMIT 1";
        if ($result = $modx->db->query($sql)) {
            if ($modx->db->getRecordCount($result) < 1) {


                $alias = GenerAlias($nameCat, $modx);
                $sql = "INSERT INTO  " . $modx->getFullTableName("site_content") . " 
                              (
                                    pagetitle,
                                    alias,
                                    parent,
                                    template,
                                    isfolder,
                                    published
                              ) VALUES (
                                    '" . $nameCat . "',
                                    '" . $alias . "',
                                    {$catRootInner},
                                    {$template},
                                    1,
                                    1
                              )";

                if ($resultIN = $modx->db->query($sql)) {
                    $cnt++;
                    $contentFolderID[] = $modx->db->getInsertId();
                    $catRootInner = $modx->db->getInsertId();
                }
            } else {
                //getid
                //$contentFolderID = $modx->db->getRow($result)["id"]; // PHP 5.4  OR Higest
                $tmp = $modx->db->getRow($result);
                $contentFolderID[] = $tmp["id"];
                $catRootInner = $tmp["id"];
            }
        }
    }

    return $contentFolderID;
}

function pre($data) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}

function importToOneCat($arrsXLS, $tocat, $tvCollation, $stringsCollation, $imageCollation, $imageCollationLink, $collationImageCol, $imageTVcol, $template, $modx, $callationIndex, $postv, $catPos = false, $imageLinks = false, $filterCollation, $imageLinksLocal, $addToPGT = false, $vendorID = false) {

    global $catRoot;
    $noFindedPath = 3;

    if (is_array($catPos) && count($catPos)) {
        if (is_numeric($tocat)) {
            $rootCreatePath = $tocat;
        } else {
            $rootCreatePath = $catRoot;
        }

        $tocat = $noFindedPath;
    }
    //echo $tocat;

    $countEvent['added'] = 0;
    $countEvent['updated'] = 0;
    $countEvent['createNewPath'] = 0;

    $flippedArrTv = (@array_flip($tvCollation));
    $flippedArrFLT = (@array_flip($filterCollation));

    if (isset($arrsXLS['data']) && !empty($arrsXLS['data'])) {
        foreach ($arrsXLS['data'] AS $indexString => $strCols) {
            $contentID = false;
            if (is_array($catPos) && count($catPos)) {
                $namesCat = array(); // сделать проверки на массив и тд 
                foreach ($catPos AS $elem) {
                    if (trim($strCols[$elem]) != '') {
                        $namesCat[] = $strCols[$elem];
                    }
                }
                if ($resT = createPathWay($namesCat, $rootCreatePath, $countEvent['createNewPath'])) {
                    $tocat = end($resT);
                }
            }
            if ($imageLinks !== false) {
                $tmpLnk = '';
                foreach ($imageLinks as $keyL => $valueL) {
                    if ($strCols[$valueL] != '') {
                        if ($localLink = loadRemoteFile($strCols[$valueL])) {
                            $tmpLnk .= $tmpLnk == '' ? $localLink : '||' . $localLink;
                        }
                    }
                }
                //echo $tmpLnk."<br>"; 
            }
            if ($imageLinksLocal !== false) {
                $tmpLnkLoc = '';
                foreach ($imageLinksLocal as $keyL => $valueL) {
                    if ($strCols[$valueL] != '') {

                        //$tmpLnkLoc .= $tmpLnkLoc == '' ? "assets/images/".$strCols[$valueL] : '||'."assets/images/".$strCols[$valueL];

                        $tmpLnkLoc .= $tmpLnkLoc == '' ? $strCols[$valueL] : '||' . $strCols[$valueL];
                    }
                }
                //echo $tmpLnk."<br>"; 
            }
            if (is_numeric($addToPGT)) {
                $strCols[$flippedArrTv["PGT"]] .= ' ' . $strCols[$addToPGT];
            }

            if ($callationIndex === 'false' || $callationIndex === false) {

                $inWHERE = " sc.pagetitle = '" . $modx->db->escape($strCols[$flippedArrTv["PGT"]]) . "' ";
                $andParent = "AND sc.parent = {$tocat} ";
            } else {

                $searchedTVid = array_search($callationIndex, $flippedArrTv);

                if ($searchedTVid == "PGT") {
                    $inWHERE = " sc.pagetitle = '" . $modx->db->escape($strCols[$flippedArrTv["PGT"]]) . "' ";
                    $andParent = "AND sc.parent = {$tocat} ";
                } elseif ($searchedTVid == "CONTENT") {
                    $inWHERE = " sc.content = '" . $strCols[$flippedArrTv["CONTENT"]] . "' ";
                    $andParent = "AND sc.parent = {$tocat} ";
                } elseif (is_numeric($searchedTVid)) {
                    $inWHERE = " tv.value = '" . $strCols[$flippedArrTv[$searchedTVid]] . "' AND  tv.tmplvarid = '" . $searchedTVid . "' ";
                    $andParent = '';

                    // echo $inWHERE;
                } else {

                    return false;
                }
            }

            $sql = "SELECT sc.id  FROM " . $modx->getFullTableName("site_content") . " AS sc 
            INNER JOIN " . $modx->getFullTableName("site_tmplvar_contentvalues") . " AS tv ON sc.id = tv.contentid 
            WHERE " . $inWHERE . "  " . $andParent . "    GROUP BY sc.id   LIMIT 100";

            if ($result = $modx->db->query($sql)) {

                $finded = false;
                if ($modx->db->getRecordCount($result) > 0) {
                    $finded = true;
                    while ($ttrow = $modx->db->getRow($result)) {
                        $ttid = $ttrow['id'];
                    }
                }

                $finded = false;
                $ids = $prod_made[$strCols[$flippedArrTv["PGT"]]];
                $ttid = false;
                if (is_array($ids)) {
                    foreach ($ids AS $id) {
                        if (!$ttid) {
                            $ttid = $id;
                            $finded = true;
                            continue;
                        }

                        mysql_query("DELETE
						FROM " . $modx->getFullTableName("site_content") . "
						WHERE id={$id} LIMIT 1");
                        mysql_query("DELETE
						FROM " . $modx->getFullTableName("site_tmplvar_contentvalues") . "
						WHERE contentid={$id}");
                    }
                }

                // $ids = $prod_made[$modx->db->escape($strCols[$flippedArrTv["PGT"]])] ? $prod_made[$modx->db->escape($strCols[$flippedArrTv["PGT"]])] : TRUE;

                if (!$finded && array_search("PGT", $tvCollation) !== false) {

                    $alias = GenerAlias($modx->db->escape($strCols[$flippedArrTv["PGT"]]), $modx);
                    $sql = "INSERT INTO  " . $modx->getFullTableName("site_content") . " 
                          (
                                pagetitle,
                                alias,
                                parent,
                                content,
                                template,
                                menuindex,
                                published
                                
                          ) VALUES (
                                '" . $modx->db->escape($strCols[$flippedArrTv["PGT"]]) . "',
                                '" . $alias . "', 
                                {$tocat},
                                '" . $modx->db->escape($strCols[$flippedArrTv["CONTENT"]]) . "',
                                {$template},
                                {$indexString},
                                1
                                
                          )";

                    //  echo $sql;
                    if ($result = $modx->db->query($sql)) {
                        $contentID = $modx->db->getInsertId();
                        $countEvent['added'] ++;
                        processedTV($contentID, $flippedArrTv, $strCols, $modx, $vendorID);

                        if (($keyImg = array_search(explode('.', $strCols[$collationImageCol]) [0], $imageCollation)) !== false) {
                            $imgLinkT = $imageCollationLink[$keyImg];
                        } else {
                            $imgLinkT = false;
                        }

                        if ($imgLinkT != false || $tmpLnk != '' || $tmpLnkLoc != '') {
                            processedIMG($contentID, $imageTVcol, $imgLinkT, $modx, $tmpLnk, $tmpLnkLoc);
                        }
                    }
                } else {
                    $contentID = $ttid;

                    if ($strCols[$flippedArrTv["PGT"]] == '' && $strCols[$flippedArrTv["CONTENT"]] == '') {
                        $sql = "UPDATE  " . $modx->getFullTableName("site_content") . " SET pagetitle = pagetitle , deleted = 0, published = 1, parent = {$tocat} WHERE id = {$contentID} LIMIT 1";
                    } elseif ($strCols[$flippedArrTv["PGT"]] == '' && $strCols[$flippedArrTv["CONTENT"]] != '') {
                        $sql = "UPDATE  " . $modx->getFullTableName("site_content") . " SET content ='" . $modx->db->escape($strCols[$flippedArrTv["CONTENT"]]) . "'  , deleted = 0, published = '1', parent = {$tocat} WHERE id = {$contentID} LIMIT 1";
                    } elseif ($strCols[$flippedArrTv["PGT"]] != '' && $strCols[$flippedArrTv["CONTENT"]] == '') {
                        $sql = "UPDATE  " . $modx->getFullTableName("site_content") . " SET pagetitle ='" . $modx->db->escape($strCols[$flippedArrTv["PGT"]]) . "'  , deleted = 0, published = '1', parent = {$tocat} WHERE id = {$contentID} LIMIT 1";
                    } else {
                        $sql = "UPDATE  " . $modx->getFullTableName("site_content") . " SET content ='" . $modx->db->escape($strCols[$flippedArrTv["CONTENT"]]) . "' ,   pagetitle='" . $modx->db->escape($strCols[$flippedArrTv["PGT"]]) . "'  , deleted = 0, published = '1', parent = {$tocat} WHERE id = {$contentID} LIMIT 1";
                    }





                    if ($result = $modx->db->query($sql)) {
                        $countEvent['updated'] ++;
                        processedTV($contentID, $flippedArrTv, $strCols, $modx, $vendorID);

                        if (($keyImg = array_search(explode('.', $strCols[$collationImageCol]) [0], $imageCollation)) !== false) {
                            $imgLinkT = $imageCollationLink[$keyImg];
                        } else {
                            $imgLinkT = false;
                        }

                        if ($imgLinkT != false || $tmpLnk != '' || $tmpLnkLoc != '') {
                            processedIMG($contentID, $imageTVcol, $imgLinkT, $modx, $tmpLnk, $tmpLnkLoc);
                        }
                    }
                }
            }
            //echo $modx->db->getLastError();        
        }
    }
    clearModxCache();
    return $countEvent;
}

function loadRemoteFile($link) {
    $pattern = '/(https?|ftp):\/\//iu';
    if (preg_match($pattern, $subject) == 0) {
        $link = 'http://' . $link;
    }

    $confPath = 'assets/images/upl/';
    $acceptedExtension = array('jpg', 'jpeg', 'png', 'gif');
    $ext = @end(explode('.', $link));
    $localImgName = md5($link) . '.' . mb_strtolower($ext, "UTF-8");

    if (!in_array($ext, $acceptedExtension))
        return false;

    $dir = substr($localImgName, 0, 2) . '/';
    if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $confPath . $dir)) {
        @mkdir($_SERVER['DOCUMENT_ROOT'] . '/' . $confPath . $dir, 0755, true);
    }

    $localPath = $confPath . $dir . $localImgName;
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $localPath)) {
        return $localPath;
    } else {
        $ch = curl_init($link);
        $fp = fopen($_SERVER['DOCUMENT_ROOT'] . '/' . $localPath, "w");
        // echo $_SERVER['DOCUMENT_ROOT'].'/'.$localPath;
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
        return $localPath;
    }
}

function clearModxCache() {
    global $modx;

    $modx->clearCache();


    include_once MODX_BASE_PATH . '/manager/processors/cache_sync.class.processor.php';
    $sync = new synccache();
    $sync->setCachepath(MODX_BASE_PATH . "/assets/cache/");
    $sync->setReport(false);
    ob_start();
    $sync->emptyCache();
    ob_end_clean();

    return;
}

function processedIMG($contentID, $imageTVcol, $imageCollation = false, $modx, $dopImage = false, $dopImageLoc = false) {


    if ($imageCollation != false && $dopImage != false) {
        $imageCollation = $imageCollation . '||' . $dopImage;
    } elseif ($imageCollation == false && $dopImage != false) {
        $imageCollation = $dopImage;
    }

    if ($imageCollation != false && $dopImageLoc != false) {
        $imageCollation = $imageCollation . '||' . $dopImageLoc;
    } elseif ($imageCollation == false && $dopImageLoc != false) {
        $imageCollation = $dopImageLoc;
    }

    $sql = "SELECT id FROM " . $modx->getFullTableName("site_tmplvar_contentvalues") . " WHERE tmplvarid = {$imageTVcol} AND contentid = {$contentID} LIMIT 1";
    if ($result = $modx->db->query($sql)) {
        if ($modx->db->getRecordCount($result) > 0) {
            //$tvID = $modx->db->getRow($result)['id'];// PHP 5.4  OR Higest
            $tmp = $modx->db->getRow($result);
            $tvID = $tmp["id"];
            $sql = "UPDATE " . $modx->getFullTableName("site_tmplvar_contentvalues") . " SET `value` = '" . $imageCollation . "' WHERE id = {$tvID} ";
            $modx->db->query($sql);
        } else {

            $sql = "INSERT INTO " . $modx->getFullTableName("site_tmplvar_contentvalues") . " (tmplvarid,contentid,value) VALUES ({$imageTVcol} ,{$contentID} , '" . $imageCollation . "' )";
            $modx->db->query($sql);
        }
    }
}

function processedTV($contentID, $flippedArrTv, $strCols, $modx, $vendorID) {
    foreach ($flippedArrTv AS $index => $value) {
        if (!is_numeric($index))
            continue;

        if (array_key_exists($value, $strCols)) {

            $sql = "SELECT id FROM " . $modx->getFullTableName("site_tmplvar_contentvalues") . " WHERE tmplvarid = {$index} AND contentid = {$contentID} LIMIT 1";
            if ($result = $modx->db->query($sql)) {
                if ($modx->db->getRecordCount($result) > 0) {
                    //$tvID = $modx->db->getRow($result)['id'];// PHP 5.4  OR Higest
                    $tmp = $modx->db->getRow($result);
                    $tvID = $tmp["id"];
                    //UPDATE 
                    $sql = "UPDATE " . $modx->getFullTableName("site_tmplvar_contentvalues") . " SET `value` = '" . $strCols[$value] . "' WHERE id = {$tvID} ";
                    $modx->db->query($sql);
                } else {
                    $sql = "INSERT INTO " . $modx->getFullTableName("site_tmplvar_contentvalues") . " (tmplvarid,contentid,value) VALUES ({$index} ,{$contentID} , '" . $strCols[$value] . "' )";
                    $modx->db->query($sql);
                }
            }
            //echo $modx->db->getLastError();
        }
    }


    if ($vendorID) {

        $sql = "SELECT id FROM " . $modx->getFullTableName("site_tmplvar_contentvalues") . " WHERE tmplvarid = 23 AND contentid = {$contentID} LIMIT 1";
        if ($result = $modx->db->query($sql)) {
            if ($modx->db->getRecordCount($result) > 0) {
                //$tvID = $modx->db->getRow($result)['id'];// PHP 5.4  OR Higest
                $tmp = $modx->db->getRow($result);
                $tvID = $tmp["id"];
                //UPDATE 
                $sql = "UPDATE " . $modx->getFullTableName("site_tmplvar_contentvalues") . " SET `value` = '" . $vendorID . "' WHERE id = {$tvID} ";
                $modx->db->query($sql);
            } else {
                $sql = "INSERT INTO " . $modx->getFullTableName("site_tmplvar_contentvalues") . " (tmplvarid,contentid,value) VALUES (23 ,{$contentID} , '" . $vendorID . "' )";
                $modx->db->query($sql);
            }
        }
        //echo $modx->db->getLastError();
    }
}

function processedFILTER($contentID, $flippedArrFLT, $strCols, $modx) {
    foreach ($flippedArrFLT AS $index => $value) {
        if (!is_numeric($index))
            continue;

        //pre($strCols);
        // pre($flippedArrTv);

        if (array_key_exists($value, $strCols)) {

            $sql = "SELECT id FROM " . $modx->getFullTableName("_catfilter_value") . " WHERE cf_id = {$index} AND itemid = {$contentID} LIMIT 1";
            if ($result = $modx->db->query($sql)) {
                if ($modx->db->getRecordCount($result) > 0) {
                    //$tvID = $modx->db->getRow($result)['id'];// PHP 5.4  OR Higest
                    $tmp = $modx->db->getRow($result);
                    $tvID = $tmp["id"];
                    //UPDATE 
                    $sql = "UPDATE " . $modx->getFullTableName("_catfilter_value") . " SET `value` = '" . $strCols[$value] . "' WHERE id = {$tvID} ";
                    $modx->db->query($sql);
                } else {
                    $sql = "INSERT INTO " . $modx->getFullTableName("_catfilter_value") . " (cf_id,itemid,`value`) VALUES ({$index} ,{$contentID} , '" . $strCols[$value] . "' )";
                    $modx->db->query($sql);
                }
            }
            //echo $modx->db->getLastError();
        }
    }
}

function GenerAlias($txt, $modx) {
    $trans = array("а" => "a", "б" => "b", "в" => "v", "г" => "g", "д" => "d", "е" => "e",
        "ё" => "jo", "ж" => "zh", "з" => "z", "и" => "i", "й" => "jj", "к" => "k", "л" => "l",
        "м" => "m", "н" => "n", "о" => "o", "п" => "p", "р" => "r", "с" => "s", "т" => "t", "у" => "u",
        "ф" => "f", "х" => "kh", "ц" => "c", "ч" => "ch", "ш" => "sh", "щ" => "shh", "ы" => "y",
        "э" => "eh", "ю" => "yu", "я" => "ya", "А" => "a", "Б" => "b", "В" => "v", "Г" => "g",
        "Д" => "d", "Е" => "e", "Ё" => "jo", "Ж" => "zh", "З" => "z", "И" => "i", "Й" => "jj",
        "К" => "k", "Л" => "l", "М" => "m", "Н" => "n", "О" => "o", "П" => "p", "Р" => "r", "С" => "s",
        "Т" => "t", "У" => "u", "Ф" => "f", "Х" => "kh", "Ц" => "c", "Ч" => "ch", "Ш" => "sh",
        "Щ" => "shh", "Ы" => "y", "Э" => "eh", "Ю" => "yu", "Я" => "ya", " " => "-", "." => "-",
        "," => "-", "_" => "-", "+" => "-", ":" => "-", ";" => "-", "!" => "-", "/" => "-", "|" => "-", "\\" => "-", "'" => "-", "`" => "-", "?" => "-");

    $alias = addslashes($txt);
    $alias = strip_tags(strtr($alias, $trans));
    $alias = preg_replace("/[^a-zA-Z0-9-]/", '', $alias);
    $alias = preg_replace('/([-]){2,}/', '-', $alias);
    $alias = trim($alias, '-');

    if (strlen($alias) > 1000)
        $alias = trim(substr($alias, 0, 1000), '-');

    do {
        $rr = $modx->db->query("SELECT id FROM  " . $modx->getFullTableName("site_content") . " WHERE alias='{$alias}' LIMIT 1");
        if ($rr && $modx->db->getRecordCount($rr) == 1)
            $alias .= rand(1, 9);
    }while (($rr && $modx->db->getRecordCount($rr) == 1) || !$rr);
    if (!$rr)
        $alias = false;

    return $alias;
}
