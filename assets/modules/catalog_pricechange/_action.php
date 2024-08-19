<?php
$sc_site = 'oboi-rnd.ru';
$sm_base = '../assets/modules/scorn_price_change/';
$module_url = MODX_MANAGER_URL . '?a=' . $_GET['a'] . '&id=' . $_GET['id'];

$catalog_koren = 14;
$tv_price = 9;


$docid = ( isset($_GET['docid']) ? intval($_GET['docid']) : 0 );

function catalogtree($id, &$print, $flag, $sub = false) {
    global $modx;

    $module_url = MODX_MANAGER_URL . '?a=' . $_GET['a'] . '&id=' . $_GET['id'];

    $cats = $modx->getActiveChildren($id, 'pagetitle', 'ASC', 'id,pagetitle,isfolder');
    if (!empty($cats)) {
        foreach ($cats AS $cat) {
            if ($cat['isfolder']) {
                if (!$sub && $flag == -1)
                    $flag = 99999999999999999;
                if ($flag && $flag != -1 && $flag == $cat['id'])
                    $flag = -1;

                $print .= '<div class="ctr_item"><a style="' . (!$flag ? '' : ( $flag == -1 ? 'color:#c00;font-weight:bold;' : 'color:#999;' ) ) . '" href="' . $module_url . '&docid=' . $cat['id'] . '">' . $cat['id'] . '. ' . $cat['pagetitle'] . '</a></div>';

                $print .= '<div class="ctr_sub">';
                catalogtree($cat['id'], $print, $flag, ( $flag == -1 ? true : false));
                $print .= '</div>';
            }
        }
    }
}

if (isset($_POST['gogo'])) {
    $type1 = ( $_POST['type1'] == 'minus' ? 'minus' : ( $_POST['type1'] == 'plus' ? 'plus' : 'ustanov' ) );
    $type2 = ( $_POST['type2'] == 'proc' ? 'proc' : ( $_POST['type2'] == 'sum' ? 'sum' : ( $_POST['type2'] == 'priceoff' ? 'priceoff' : 'priceon' ) ) );
    $id_tv_price = intval(trim($_POST['id_tv_price']));
    if ($id_tv_price != 9 && $id_tv_price != 22)
        $error .= '<p>ID TV Price - 9 или 22</p>';
    $price_name = addslashes(trim($_POST['price_name']));
    if ($id_tv_price == 22 && !$price_name)
        $error .= '<p>Наименование цены?</p>';
    $val = trim($_POST['val']);

    if (!$_POST['type1']) {
        $error .= '<p>Что сделать с ценой (снизить, повысить или установить)?</p>';
    }
    if ($type1 == 'ustanov' && $type2 == 'proc') {
        $error .= '<p>Уставновить цену в процентах нельзя!</p>';
    }
    if (!$_POST['type2']) {
        $error .= '<p>На процент, на сумму или установить цены по запросу?</p>';
    }
    if ($type2 != 'priceoff' && $type2 != 'priceon' && (empty($val) && $val !== '0')) {
        $error .= '<p>На какую выличину?</p>';
    }

    if (!$error) {
        $val = preg_replace("/[^0-9]/", ".", $val);

        price_change($docid, $type1, $type2, $val, $id_tv_price, $price_name);
    }
}

function price_change($id, $type1, $type2, $val, $tv_price, $price_name) {
    global $modx;

    $rr = mysql_query("SELECT sc.id, tv.`value` FROM " . $modx->getFullTableName('site_content') . " AS sc
		LEFT JOIN " . $modx->getFullTableName('site_tmplvar_contentvalues') . " AS tv ON tv.contentid=sc.id AND tv.tmplvarid={$tv_price}
			WHERE sc.parent={$id}");

    if ($rr && mysql_num_rows($rr) > 0) {
        while ($row = mysql_fetch_assoc($rr)) {
            if ($type2 == 'priceoff' || $type2 == 'priceon') {
                /* $value= ( $type2 == 'priceoff' ? 'y' : 'n' );
                  $rr2= mysql_query( "SELECT * FROM ".$modx->getFullTableName( 'site_tmplvar_contentvalues' )." WHERE contentid={$row[id]} AND tmplvarid=17 LIMIT 1" );
                  if( $rr2 && mysql_num_rows( $rr2 ) == 1 )
                  {
                  mysql_query( "UPDATE ".$modx->getFullTableName( 'site_tmplvar_contentvalues' )." SET `value`='{$value}' WHERE contentid={$row[id]} AND tmplvarid=17 LIMIT 1" );
                  }elseif( $rr2 ){
                  mysql_query( "INSERT INTO ".$modx->getFullTableName( 'site_tmplvar_contentvalues' )." SET contentid={$row[id]}, tmplvarid=17, `value`='{$value}'" );
                  } */
            } else {
                $price = $row['value'];
                $newval = $val;

                if ($tv_price == 22) {
                    $prices = explode("||", $price);
                    if ($prices) {
                        $flag = false;
                        foreach ($prices AS $row2) {
                            $tmp2 = explode("::", $row2);
                            if ($tmp2[0] == $price_name) {
                                $flag = true;
                                $newprice = intval($tmp2[1]);
                                break;
                            }
                        }
                    }
                } else {
                    $newprice = intval(( $price ? $price : 0));
                }

                if ($type2 == 'proc')
                    $newval = round($newprice * $val / 100);
                if ($type1 == 'minus')
                    $newval *= (-1);
                if ($type1 == 'ustanov')
                    $newprice = 0;
                $newprice += $newval;
                if ($newprice < 0)
                    $newprice = 0;

                if ($tv_price == 22) {
                    if ($flag) {
                        $tmp = '';
                        foreach ($prices AS $row2) {
                            $tmp2 = explode("::", $row2);
                            if ($tmp2[0] == $price_name) {
                                $tmp .= (!empty($row2) ? '||' : '' ) . $price_name . '::' . $newprice;
                            } else {
                                $tmp .= (!empty($row2) ? '||' : '' ) . $row2;
                            }
                        }
                        $newprice = $tmp;
                    } else {
                        $newprice = $price . (!empty($price) ? '||' : '' ) . $price_name . '::' . $newprice;
                    }
                }

                //print $row[ 'id' ] .' | '. $row[ 'value' ] .' | '. $newprice .'<br />';

                $rr2 = mysql_query("SELECT * FROM " . $modx->getFullTableName('site_tmplvar_contentvalues') . " WHERE contentid={$row[id]} AND tmplvarid={$tv_price} LIMIT 1");
                if ($rr2 && mysql_num_rows($rr2) == 1) {
                    mysql_query("UPDATE " . $modx->getFullTableName('site_tmplvar_contentvalues') . " SET `value`='{$newprice}' WHERE contentid={$row[id]} AND tmplvarid={$tv_price} LIMIT 1");
                } elseif ($rr2) {
                    mysql_query("INSERT INTO " . $modx->getFullTableName('site_tmplvar_contentvalues') . " SET contentid={$row[id]}, tmplvarid={$tv_price}, `value`='{$newprice}'");
                }
            }

            price_change($row['id'], $type1, $type2, $val, $tv_price);
        }
    }
}

if ($result != '')
    $result .= '<br /><br />';
if ($result2 != '')
    $result2 .= '<br /><br />';
?><div class="modul_scorn_all">
    <!-- -------------------------- -->
    <link rel="stylesheet" type="text/css" href="<?php print $sm_base; ?>_styles.css" />
    <script type="text/javascript" src="//yandex.st/jquery/2.1.0/jquery.min.js"></script>
    <script type="text/javascript" src="//yandex.st/jquery-ui/1.10.4/jquery-ui.min.js"></script>

    <div class="wrapper">
        <div class="catalogtree">
<?php
catalogtree($catalog_koren, $print, $docid);
print $print;
?>
        </div>

        <div class="content">
<?php if ($docid) { ?>

                <div style="color:#e00;"><?= $error ?></div><br /><br />

    <?php
    $docinfo = $modx->getDocument($docid);
    ?>

                <form action="<?= $module_url ?>&docid=<?= $docid ?>" method="post">
                    <div>Категория: <b><?= $docid ?>. <?= $docinfo['pagetitle'] ?></b></div><br /><br />

                    <div>
                        <label><input type="radio" name="type1" value="minus" <?= ( $_POST['type1'] == 'minus' ? 'checked="checked"' : '' ) ?> /> снизить цену</label><br />
                        <label><input type="radio" name="type1" value="plus" <?= ( $_POST['type1'] == 'plus' ? 'checked="checked"' : '' ) ?> /> повысить цену</label><br />
                        <label><input type="radio" name="type1" value="ustanov" <?= ( $_POST['type1'] == 'ustanov' || !$_POST['type1'] ? 'checked="checked"' : '' ) ?> /> установить цену</label>
                    </div><br /><br />

                    <div>
                        <label><input type="radio" name="type2" value="proc" <?= ( $_POST['type2'] == 'proc' ? 'checked="checked"' : '' ) ?> /> на процент (%)</label><br />
                        <label><input type="radio" name="type2" value="sum" <?= ( $_POST['type2'] == 'sum' || !$_POST['type2'] ? 'checked="checked"' : '' ) ?> /> на сумму (руб.)</label><br /><br />

    <!--<label><input type="radio" name="type2" value="priceoff" <?= ( $_POST['type2'] == 'priceoff' ? 'checked="checked"' : '' ) ?> /> цена по запросу<br /></label>
    <label><input type="radio" name="type2" value="priceon" <?= ( $_POST['type2'] == 'priceon' ? 'checked="checked"' : '' ) ?> /> цена не по запросу<br /></label>-->
                    </div><br /><br />

                    <div><label>ID TV Price &nbsp; <input type="text" name="id_tv_price" value="<?= ($id_tv_price ? $id_tv_price : '9') ?>" /></label></div><br />
                    <div><label>Наименование цены &nbsp; <input type="text" name="price_name" value="<?= $price_name ?>" /></label></div><br />
                    <div><label>Изменить на величину &nbsp; <input type="text" name="val" value="<?= $val ?>" /></label></div><br /><br />

                    <div><button type="submit" name="gogo">Изменить цены</button></div><br /><br />

                    <hr />

                    <p>ВНИМАНИЕ!</p>
                    <p>Цены изменяться у всех товарных позиций всех уровней внутри выбранной категории каталога!</p>
                </form>
<?php } ?>
        </div>
    </div>

    <script type="text/javascript">
    </script>


<?php


/*
*/