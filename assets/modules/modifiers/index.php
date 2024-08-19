<?php
$module_url = MODX_MANAGER_URL . '?a=' . $_GET['a'] . '&id=' . $_GET['id'];


$modx->db->query("CREATE TABLE IF NOT EXISTS " . $modx->getFullTableName('modifiers') . " (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(63) NOT NULL,
  `file` varchar(255) NOT NULL,
  `type` set('before','replace','after') NOT NULL DEFAULT 'replace',
  `position` varchar(15) NOT NULL,
  `search` text NOT NULL,
  `code` text NOT NULL,
  `result` set('y','n') NOT NULL DEFAULT 'n',
  `tm` bigint(20) NOT NULL,
  `i` int(11) NOT NULL,
  `e` set('y','n') NOT NULL DEFAULT 'y',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");


if ($_GET['act'] == 'modify') {
    $backup = 'modifiers/backup/';

    $rr = $modx->db->query("SELECT * FROM " . $modx->getFullTableName('modifiers') . " WHERE e='y' ORDER BY file,i");
    if ($rr && $modx->db->getRecordCount($rr)) {
        $file = false;
        while ($row = $modx->db->getRow($rr)) {
            $result = 'n';

            if ($row['file'] != $file) {
                if ($file !== false) {
                    fclose($fo);

                    if ($edit) {
                        $foo = MODX_BASE_PATH . $backup . dirname($file);
                        if (!file_exists($foo))
                            mkdir($foo, 0777, true);
                        copy(MODX_BASE_PATH . $file, MODX_BASE_PATH . $backup . $file . '__' . date('YmdHis'));

                        $fo = fopen(MODX_BASE_PATH . $file, 'wb');
                        if ($fo) {
                            fwrite($fo, $content);
                            fclose($fo);
                        }
                    }
                }


                $flag = false;
                $edit = false;
                $file = $row['file'];
                if (file_exists(MODX_BASE_PATH . $file)) {
                    $fo = fopen(MODX_BASE_PATH . $file, 'rb');
                    if ($fo) {
                        $content = '';
                        while (!feof($fo))
                            $content .= fread($fo, 1024 * 1024);
                        $flag = true;
                    }
                }
            }

            if ($flag) {
                $mypos = strpos($content, $row['code']);
                if ($mypos !== false)
                    $result = 'y';
                else {
                    $pos = strpos($content, $row['search']);
                    print $row['id'] . '=' . $pos . ',';
                    if ($pos !== false) {
                        $result = 'y';

                        $newcontent = mb_substr($content, 0, $pos);
                        $len = mb_strlen($row['search']);
                        if ($row['type'] == 'before' || $row['type'] == 'replace')
                            $newcontent .= $row['code'];
                        if ($row['type'] != 'replace')
                            $newcontent .= $row['search'];
                        if ($row['type'] == 'after')
                            $newcontent .= $row['code'];
                        $newcontent .= mb_substr($content, $pos + $len);

                        $edit = true;
                        $content = $newcontent;
                    }
                }
            }

            $modx->db->query("UPDATE " . $modx->getFullTableName('modifiers') . " SET result='{$result}', tm=" . time() . " WHERE id={$row[id]} LIMIT 1");
        }
        if ($file !== false) {
            fclose($fo);

            if ($edit) {
                $foo = MODX_BASE_PATH . $backup . dirname($file);
                if (!file_exists($foo))
                    mkdir($foo, 0777, true);
                copy(MODX_BASE_PATH . $file, MODX_BASE_PATH . $backup . $file . '__' . date('YmdHis'));

                $fo = fopen(MODX_BASE_PATH . $file, 'wb');
                if ($fo) {
                    fwrite($fo, $content);
                    fclose($fo);
                }
            }
        }
    }
}
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<!-- script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css" -->

<div class="contentbox">
    <div class="content">
        <a href="<?= $module_url ?>&act=modify">Модифицировать</a>
    </div>
</div>
