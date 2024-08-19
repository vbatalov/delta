<?php

/*
  error_reporting(E_ALL);
  ini_set("display_errors", 1);
 * 
 */

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
 * 
 */


$prod_made = array();
$prod_id = $modx->db->query("SELECT
		sc.id, sc.pagetitle, sc.parent,
		tv.value art
	FROM ".$modx->getFullTableName("site_content")." sc
	INNER JOIN ".$modx->getFullTableName("site_tmplvar_contentvalues")." tv
		ON tv.contentid=sc.id AND tv.tmplvarid=18
	WHERE sc.template={$template} AND sc.isfolder=0");
if ($prod_id && $modx->db->getRecordCount($prod_id)) {
	while ($row = $modx->db->getRow($prod_id)) {
		$prod_made['pgt'][$row['parent']][$row['pagetitle']][$row['id']] = $row['id'];
		$prod_made['tvs'][$row['art']][$row['id']] = $row['id'];
	}
}

/*
  $articles_tv = array();
  $articles_id = $modx->db->query("
  SELECT
  s_c.id, s_c.pagetitle,
  s_tv.contentid, s_tv.value
  FROM
  " . $modx->getFullTableName("site_content") . " as s_c
  LEFT JOIN
  " . $modx->getFullTableName("site_tmplvar_contentvalues") . " as s_tv
  ON
  s_c.id = s_tv.contentid
  WHERE
  s_tv.tmplvarid = 18 AND
  s_c.isfolder = 0
  ");
  if ($articles_id && $modx->db->getRecordCount($articles_id)) {
  while ($row = $modx->db->getRow($articles_id)) {
  $articles_tv[$row['value']] = $row;
  }
  }
 * 
 */

/* echo "<pre>";
  print_r($articles_tv);
  echo "</pre>";
 */


if ($_GET['event'] == 'doBackUp') {

	$dumpfile = $_SERVER['DOCUMENT_ROOT'].'/xls/dump/'.date('Y-m').'/';
	if ( ! file_exists($dumpfile)) mkdir($dumpfile,0777,true);
	$dumpfile .= 'db_'.date('Y-m-d-H-i-s').'.sql';
	$fh = fopen($dumpfile,'ab');
	if ( ! $fh) exit(json_encode(array('state'=>false,'text'=>'er_00')));

	$dump .= "# -- start / ". date('d.m.Y, H:i:s') ."\n\n";

	$tbls = array(
		$modx->getFullTableName('site_content'),
		$modx->getFullTableName('site_tmplvar_contentvalues'),
	);

	foreach ($tbls AS $tbl) {

		$dump .= "# ---------------------------- ".$tbl."" ."\n\n";

		$res2 = $modx->db->query("SHOW CREATE TABLE {$tbl}");
		if ( ! $res2) exit(json_encode(array('state'=>false,'text'=>'er_01')));
		while ($row2 = $modx->db->getRow($res2)) {
			$dump .= "DROP TABLE IF EXISTS {$tbl};" ."\n";
			$dump .= $row2['Create Table'] .";" ."\n\n";
		}
	
		$res2 = $modx->db->query("SELECT * FROM {$tbl}");
		if ( ! $res2) exit(json_encode(array('state'=>false,'text'=>'er_02')));
		$ii = 0;
		while ($row2 = $modx->db->getRow($res2)) {
			$dump .= "INSERT INTO {$tbl} SET ";
			if ( ! is_array($row2)) exit(json_encode(array('state'=>false,'text'=>'er_03')));
			$first = true;
			foreach ($row2 AS $key => $val) {
				$val = $modx->db->escape($val);
				$dump .= ($first ? "" : ",") ."`{$key}`='{$val}'";
				$first = false;
			}
			$dump .= ";" ."\n";

			$ii++;
			if ($ii >= 500 || strlen($dump) >= 1024*512) {
				fwrite($fh,$dump);
				$dump = '';
				$ii = 0;
			}
		}
	}

	$dump .= "# -- the end / ". date('d.m.Y, H:i:s') ."\n";

	fwrite($fh,$dump);
	fclose($fh);

	echo json_encode(array('state'=>true,'text'=>'tablebackUpped'));

	exit();
}

if ($_GET['event'] == 'doRedo') {
	exit();
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/assets/modules/importXLS/Classes/PHPExcel.php');

class chunkReadFilter implements PHPExcel_Reader_IReadFilter {

	private $_startRow = 0;
	private $_endRow = 0;

	/**  Установите список строк, которые мы хотим прочитать  */
	public function setRows($startRow, $chunkSize) {
		$this->_startRow = $startRow;
		$this->_endRow = $startRow + $chunkSize;
	}

	public function readCell($column, $row, $worksheetName = '') {
		//  Читать только строку заголовка и строки, настроенные в $this->_startRow and $this->_endRow 
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
		$fulldata['meta']['highestRow'] = $i - $empty_value + $startRow;
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

	$log = $_SERVER['DOCUMENT_ROOT'].'/xls/log/';
	if ( ! file_exists($log)) mkdir($log,0777,true);
	$log .= date('Y-m-d-H-i-s').'.log';
	$fh = fopen($log,'wb');
	if ($fh) {
		fwrite($fh,print_r($_POST,1));
		fclose($fh);
	}

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

	$addToPGT = json_decode(trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $_POST["addToPGT"]))));

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

		$unpublished = array();
		if ($currentPos == 0) {
			disAllCats($tocat, $unpublished);
		}

		$pgtIndex = array_search("PGT", $tvCollation);
		// getArrValuesChunk($pathToXLS , $start,  $currentList = 0  , $excludedRows = array() , $stringsCollation = array(), $pgtIndex = false 
		//$crossToBase = getArrValuesChunk($pathToXLS, $currentPos+1 , $currentSheet  , $excludedRows  );
		$arrsXLS = getArrValuesChunk(addslashes($pathToXLS), $currentPos + 1, $currentSheet, false, $stringsCollation, $pgtIndex, 1, $stringsCollationIgnore);
	}

	$imporiRes = 'empty';

	if ($typeImport == "allInSelected") {
		if ($result = importToOneCat($arrsXLS, $tocat, $tvCollation, $stringsCollation, $imageCollation, $imageCollationLink, $collationImageCol, $imageTVcol, $template, $modx, $callationIndex, $postv, $selectColCat1st_pos, $selectAcolLonkImg_pos, $filterCollation, $selectAcolLocalImg_pos, $addToPGT, $vendorID, $prod_made)) {
			$imporiRes = $result;
		} else
			$imporiRes = 'error1';
	} elseif ($typeImport == "toChangedCat") {
		if ($result = importToOneCat($arrsXLS, $tocat, $tvCollation, $stringsCollation, $imageCollation, $imageCollationLink, $collationImageCol, $imageTVcol, $template, $modx, $callationIndex, $postv, $selectColCat1st_pos, $selectAcolLonkImg_pos, $filterCollation, $selectAcolLocalImg_pos, $addToPGT, $vendorID, $prod_made)) {
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

function disAllCats($root, &$unpublished=array()) {
	global $modx;

	if ( ! $unpublished[$root]) {
		$unpublished[$root] = true;
		$modx->db->query("UPDATE ".$modx->getFullTableName('site_content')."
			SET unpub_date='".(time()+(60*60*1))."'
			WHERE parent={$root}");
	}

	$sql = "SELECT id FROM ".$modx->getFullTableName("site_content")."  
		WHERE parent='{$root}' AND isfolder=1";
	if ($result = $modx->db->query($sql)) {
		if ($modx->db->getRecordCount($result) > 0) {
			while ($row = $modx->db->getRow($result)) {
				disAllCats($row['id'], $unpublished);
			}
		}
	}
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

				$modx->db->query("UPDATE ".$modx->getFullTableName("site_content")."
					SET published=1, unpub_date=''
					WHERE id={$catRootInner} LIMIT 1");
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

function importToOneCat($arrsXLS, $tocat, $tvCollation, $stringsCollation, $imageCollation, $imageCollationLink, $collationImageCol, $imageTVcol, $template, $modx, $callationIndex, $postv, $catPos = false, $imageLinks = false, $filterCollation, $imageLinksLocal, $addToPGT = false, $vendorID = false, $prod_made = false) {

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

	$unpublished = array();

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

			$strColsPGT = trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $strCols[$flippedArrTv["PGT"]])));

			$finded = false;
			$ttid = false;
			$ids = false;

			$searchedTVid = array_search($callationIndex, $flippedArrTv);

			if ('CONTENT' == $searchedTVid) {
				return false;
				// нужно сделать, если понадобится

			} elseif (intval($searchedTVid)) {
				$searchedTVid = intval($searchedTVid);
				$tvval = $strCols[$flippedArrTv[$searchedTVid]];
				$ids = $prod_made['tvs'][$tvval];

			} else {
				$ids = $prod_made['pgt'][$tocat][$strColsPGT];
			}

			if (is_array($ids)) {
				foreach ($ids AS $id) {
					if ( ! $ttid) {
						$ttid = $id;
						$finded = true;
						continue;
					}

					$modx->db->query("UPDATE
						".$modx->getFullTableName("site_content")."
						SET deleted=1 WHERE id={$id} LIMIT 1");
				}
			}

			if ( ! $finded && ! $flippedArrTv["PGT"]) {
				return false;
				// Как я создам товар. если не выбрано поле PGT ?

			} elseif ( ! $finded) {

				$alias = GenerAlias($modx->db->escape($strColsPGT), $modx);
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
							'" . $modx->db->escape($strColsPGT) . "',
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

				if ($strColsPGT == '' && $strCols[$flippedArrTv["CONTENT"]] == '') {
					$sql = "UPDATE  " . $modx->getFullTableName("site_content") . " SET pagetitle = pagetitle , deleted = 0, published = 1, unpub_date='', parent = {$tocat} WHERE id = {$contentID} LIMIT 1";

				} elseif ($strColsPGT == '' && $strCols[$flippedArrTv["CONTENT"]] != '') {
					$sql = "UPDATE  " . $modx->getFullTableName("site_content") . " SET content ='" . $modx->db->escape($strCols[$flippedArrTv["CONTENT"]]) . "'  , deleted = 0, published = '1', unpub_date='', parent = {$tocat} WHERE id = {$contentID} LIMIT 1";

				} elseif ($strColsPGT != '' && $strCols[$flippedArrTv["CONTENT"]] == '') {
					$sql = "UPDATE  " . $modx->getFullTableName("site_content") . " SET pagetitle ='" . $modx->db->escape($strColsPGT) . "'  , deleted = 0, published = '1', unpub_date='', parent = {$tocat} WHERE id = {$contentID} LIMIT 1";

				} else {
					$sql = "UPDATE  " . $modx->getFullTableName("site_content") . " SET content ='" . $modx->db->escape($strCols[$flippedArrTv["CONTENT"]]) . "' ,   pagetitle='" . $modx->db->escape($strColsPGT) . "'  , deleted = 0, published = '1', unpub_date='', parent = {$tocat} WHERE id = {$contentID} LIMIT 1";
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

$resources_empty = array();
$resources_query = $modx->db->query("SELECT id, pagetitle FROM " . $modx->getFullTableName("site_content") . " WHERE template IN (4, 5) AND isfolder = 1 AND id > 4");
if ($resources_query && $modx->db->getRecordCount($resources_query)) {
	while ($row = $modx->db->getRow($resources_query)) {
		$resources_empty[$row['id']] = $modx->myGetAllChildren($row['id']);
		// $resources_empty[$row['id']] = $row['pagetitle'];
	}
}
$resources_all = implode(", ", array_keys($resources_empty));
$resources_not_empty = implode(", ", array_keys(array_filter($resources_empty)));
if (!empty($resources_not_empty)) {
	$modx->db->query("UPDATE " . $modx->getFullTableName("site_content") . " SET published = '0' WHERE id IN (" . $resources_all . ") ");
	$modx->db->query("UPDATE " . $modx->getFullTableName("site_content") . " SET published = '1', unpub_date='' WHERE id IN (" . $resources_not_empty . ") ");
}

$query_catalog = $modx->db->query("SELECT id, pagetitle FROM " . $modx->getFullTableName("site_content") . " WHERE template IN (4, 5) AND isfolder = 1 AND parent = 4");
if ($query_catalog && $modx->db->getRecordCount($query_catalog)) {
	while ($row = $modx->db->getRow($query_catalog)) {
		$catalog_empty[$row['id']] = $modx->myGetAllChildren($row['id']);
		// $resources_empty[$row['id']] = $row['pagetitle'];
	}
}
$catalog_all = implode(", ", array_keys($catalog_empty));
$catalog_not_empty = implode(", ", array_keys(array_filter($catalog_empty)));
if (!empty($catalog_not_empty)) {
	$modx->db->query("UPDATE " . $modx->getFullTableName("site_content") . " SET published = '0' WHERE id IN (" . $catalog_all . ") ");
	$modx->db->query("UPDATE " . $modx->getFullTableName("site_content") . " SET published = '1', unpub_date='' WHERE id IN (" . $catalog_not_empty . ") ");
}

$output = array();
$result_a = $modx->db->query("SELECT id, pagetitle, parent FROM  " . $modx->getFullTableName('site_content') . " WHERE isfolder=0 AND template=4");
while ($row = $modx->db->getRow($result_a)) {
	$output[$row['parent']] = $row['pagetitle'];
}

$pi_val = $modx->db->escape('{"fieldValue":[{"param_id":"7","cat_name":"","list_yes":"1","fltr_yes":"1","fltr_type":"6","fltr_name":"Цена","fltr_many":"","fltr_href":""},{"param_id":"22","cat_name":"","list_yes":"1","fltr_yes":"1","fltr_type":"1","fltr_name":"Страна","fltr_many":"1","fltr_href":""},{"param_id":"20","cat_name":"","list_yes":"1","fltr_yes":"1","fltr_type":"1","fltr_name":"Бренд","fltr_many":"1","fltr_href":""}],"fieldSettings":{"autoincrement":1}}');

foreach ($output as $putin_k => $putin_v) {
	$modx->db->query("UPDATE " . $modx->getFullTableName('site_content') . " SET template=5 WHERE template=4 AND id=$putin_k ");
	$modx->db->query("DELETE FROM " . $modx->getFullTableName('site_content') . " WHERE tmplvarid=23");
	$modx->db->query("INSERT INTO " . $modx->getFullTableName('site_tmplvar_contentvalues') . " SET tmplvarid=23, value='$pi_val', contentid=$putin_k ");
}


/* * ******************** */

$all_article = array();
$query_article = $modx->db->query("
  SELECT
  s_c.id, s_c.pagetitle,
  s_tv.contentid, s_tv.value
  FROM " . $modx->getFullTableName("site_content") . " as s_c
  LEFT JOIN " . $modx->getFullTableName("site_tmplvar_contentvalues") . " as s_tv
  ON s_c.id = s_tv.contentid
  WHERE s_tv.tmplvarid = 18 AND s_c.isfolder = 0 AND s_c.template = 4
  "
);
if ($query_article && $modx->db->getRecordCount($query_article) > 0) {
	while ($row = $modx->db->getRow($query_article)) {
		$all_article[$row['id']] = $row['id'];
	}
}
$all_article = implode(", ", array_keys($all_article));

/* * ******************** */

$all_content = array();
$query_content = $modx->db->query("
  SELECT
  s_c.id, s_c.pagetitle,
  s_tv.contentid, s_tv.value
  FROM " . $modx->getFullTableName("site_content") . " as s_c
  LEFT JOIN " . $modx->getFullTableName("site_tmplvar_contentvalues") . " as s_tv
  ON s_c.id = s_tv.contentid
  WHERE s_c.isfolder = 0 AND s_c.template = 4
  "
);
if ($query_content && $modx->db->getRecordCount($query_content) > 0) {
	while ($row = $modx->db->getRow($query_content)) {
		$all_content[$row['id']] = $row['id'];
	}
}
$all_content = implode(", ", array_keys($all_content));

$modx->db->query("UPDATE " . $modx->getFullTableName('site_content') . " SET deleted=1 WHERE id IN ({$all_content}) ");
$modx->db->query("UPDATE " . $modx->getFullTableName('site_content') . " SET deleted=0 WHERE id IN ({$all_article}) ");
/* * ******************** */
$modx->db->query("UPDATE " . $modx->getFullTableName('site_content') . " SET template=4 WHERE parent=4");

/*
  echo "<pre>";
  print_r($output);
  echo "</pre>";
 */

function GenerAlias($txt, $modx) {
	$trans = array("а" => "a", "б" => "b", "в" => "v", "г" => "g", "д" => "d", "е" => "e",
		"ё" => "jo", "ж" => "zh", "з" => "z", "и" => "i", "й" => "jj", "к" => "k", "л" => "l",
		"м" => "m", "н" => "n", "о" => "o", "п" => "p", "р" => "r", "с" => "s", "т" => "t", "у" => "u",
		"ф" => "f", "х" => "kh", "ц" => "c", "ч" => "ch", "ш" => "sh", "щ" => "shh", "ы" => "y",
		"э" => "eh", "ю" => "yu", "я" => "ya", "А" => "a", "Б" => "b", "В" => "v", "Г" => "g",
		"Д" => "d", "Е" => "e", "Ё" => "jo", "Ж" => "zh", "З" => "z", "�?" => "i", "Й" => "jj",
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

/*
 * В этом файле должна быть функция /manager/includes/document.parser.class.inc.php
 *  
 * после function getAllChildren
 * 
  public function myGetAllChildren($id = 0, $sort = 'menuindex', $dir = 'ASC', $fields = 'id, pagetitle, description, parent, alias, menutitle') {

  $cacheKey = md5(print_r(func_get_args(), true));
  if (isset($this->tmpCache[__FUNCTION__][$cacheKey])) {
  return $this->tmpCache[__FUNCTION__][$cacheKey];
  }

  $tblsc = $this->getFullTableName("site_content");
  $tbldg = $this->getFullTableName("document_groups");
  // modify field names to use sc. table reference
  $fields = 'sc.' . implode(',sc.', array_filter(array_map('trim', explode(',', $fields))));
  $sort = 'sc.' . implode(',sc.', array_filter(array_map('trim', explode(',', $sort))));
  // get document groups for current user
  if ($docgrp = $this->getUserDocGroups()) {
  $docgrp = implode(",", $docgrp);
  }
  // build query
  $access = ($this->isFrontend() ? "sc.privateweb=0" : "1='" . $_SESSION['mgrRole'] . "' OR sc.privatemgr=0") . (!$docgrp ? "" : " OR dg.document_group IN ($docgrp)");
  $result = $this->db->select("DISTINCT {$fields}", "{$tblsc} sc
  LEFT JOIN {$tbldg} dg on dg.document = sc.id", "sc.parent = '{$id}' AND ({$access}) AND published = 0 GROUP BY sc.id", "{$sort} {$dir}
  ");
  $resourceArray = $this->db->makeArray($result);
  $this->tmpCache[__FUNCTION__][$cacheKey] = $resourceArray;
  return $resourceArray;
  }
 * 
 * 
 */