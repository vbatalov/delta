<?php

$module_url= MODX_MANAGER_URL.'?a='.$_GET['a'].'&id='.$_GET['id'];
// $module_url_webusers= MODX_MANAGER_URL .'?a=112&id=5';


$order= $modx->db->escape( $_GET[ 'ord' ] );
$wu= intval( $_GET[ 'wu' ] );



?>
<div class="module_box">
<!-- -------------------------- -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>


<br><br>


<script type="text/javascript">
</script>


<?php
if( $_GET[ 'act' ] == 'full' )
{
	$orderinfo= $modx->db->query( "SELECT ord.*, ml.mail
		FROM ". $modx->getFullTableName( '_shop_order' ) ." AS ord
		INNER JOIN ". $modx->getFullTableName( '_shop_mail' ) ." AS ml ON ml.code=ord.code
			WHERE ord.code='{$order}' LIMIT 1" );
	if( $orderinfo && $modx->db->getRecordCount( $orderinfo ) )
	{
		$info= $modx->db->getRow( $orderinfo );
		//$cc= md5( $info[ 'id' ] . $order . $info[ 'email' ] . $info[ 'dt' ] . $info[ 'iduser' ] );
		$print .= $info['mail'];
		
	}
	$print .= '<br /><br /><br /><br /><br /><br />';
//===============================================================================================================






	
}else{




	
	$orders= $modx->db->query( "SELECT * FROM ". $modx->getFullTableName( '_shop_order' ) ."WHERE checkout>0 ".( $wu ? "AND userid={$wu}" : "" )."
		ORDER BY checkout DESC ".( $wu ? "" : "LIMIT 200" )."" );
	if( $orders && $modx->db->getRecordCount( $orders ))
	{
		if( ! $wu ) $print .= '<p>Последних 200 заказов:</p>';
		if( $wu ) $print .= '<p>Заказы покупателя #'. $wu .'</p>';
		
		$print .= '
			<table class="orders_table" cellpadding="0" cellspacing="0">
				<tr class="tit">
					<td class="podrobn" valign="center">&nbsp;</td>
					<td class="num" valign="center">Номер<br />заказа</td>
					<td class="summa" valign="center">Сумма, руб.</td>
					<td class="fio" valign="center">Имя<br />Фамилия</td>
					<td class="email" valign="center">E-mail</td>
					<td class="phone" valign="center">Телефон</td>
					<td class="gorod" valign="center">Город</td>
					<td class="date" valign="center">Дата<br />время</td>
				</tr>';
		$ii= 0;	
		while( $row= $modx->db->getRow( $orders ) )
		{
			$ii++;
			$print .= '<tr class="item '.( $ii % 2 == 0 ? 'item_chet' : '' ).' ">
					<td class="podrobn" valign="center">
						<a href="'. $module_url .'&act=full&ord='. $row[ 'code' ] .'">Подробнее о заказе</a>';
			
			$print .= '</td>
					
					<td class="num" valign="center">'. $row[ 'code' ] .'</td>
					
					<td class="summa" valign="center">'. price( $row[ 'itogo' ] ) .'</td>
					
					<td class="fio" valign="center">'. $row[ 'fio' ] .'</td>
					
					<td class="email" valign="center"><a target="_blank" href="mailto:'. $row[ 'email' ] .'">'. $row[ 'email' ] .'</a></td>
					
					<td class="phone" valign="center">'. $row[ 'phone' ] .'</td>
					
					<td class="gorod" valign="center">'. $row[ 'city' ] .'</td>
					
					<td class="date" valign="center">'. date( "d.m.Y", $row[ 'checkout' ] ) .'<br />'. date( "H:i", $row[ 'checkout' ] ) .'</td>';
			$print .= '</tr>';
		}
	}
}

print $print;




?>

<style>


.module_box {
	font-family: Arial;
}

.clr {
	clear: both;
	font-size: 0px;
	line-height: 0px;
}

.orders_table {
	border: 1px;
	width: 100%;
	border-collapse: collapse;
}
	.orders_table tr {
	}
		.orders_table tr td {
			border: 1px solid #ddd;
			border-left: 1px solid #eee;
			border-right: 1px solid #eee;
			font-size: 12px;
			padding: 10px 6px;
		}
			.orders_table tr td a {
				font-size: 12px;
			}
			
		.orders_table .tit td {
			font-weight: bold;
			background: rgba( 0,0,0, 0.1 );
			border-left: 1px solid #d0d0d0;
			border-right: 1px solid #d0d0d0;
		}
		.orders_table .item td {
			background: #fff;
		}
		.orders_table .item_chet td {
			background: #f8f8f8;
		}
		.orders_table .status_10 td {
		}
		.orders_table .status_20 td {
		}
		.orders_table .status_30 td {
			background: #f9d7f3;
			opacity: 0.5;
		}
		.orders_table .status_40 td {
			background: #deecf6;
		}
		.orders_table .status_50 td {
			background: #deecf6;
		}
		.orders_table .status_60 td {
			background: #e2f7c8;
			opacity: 0.5;
		}
		.orders_table .status_999 td {
			background: #f9d7f3;
			opacity: 0.5;
		}

		.orders_table .tit .podrobn {
			width: 70px;
		}
		.orders_table .tit .edit {
			width: 90px;
		}
		.orders_table .tit .num {
			width: 60px;
			text-align: center;
		}
		.orders_table .tit .summa {
			width: 90px;
			text-align: right;
		}
		.orders_table .tit .fio {
			width: 150px;
			text-align: right;
		}
		.orders_table .tit .email {
			width: 180px;
		}
		.orders_table .tit .phone {
			width: 160px;
		}
		.orders_table .tit .gorod {
		}
		.orders_table .tit .status {
		}
		.orders_table .tit .action {
		}
		.orders_table .tit .date {
			width: 70px;
			text-align: center;
		}

		.orders_table .item .podrobn {
		}
		.orders_table .item .edit {
			text-align: center;
		}
		.orders_table .item .num {
			font-size: 14px;
			text-align: center;
		}
		.orders_table .item .summa {
			font-size: 14px;
			text-align: right;
			font-weight: bold;
			color: #314861;
		}
		.orders_table .item .fio {
			text-align: right;
		}
		.orders_table .item .email {
		}
			.orders_table .item .email a {
				text-decoration: none;
			}
		.orders_table .item .phone {
		}
		.orders_table .item .gorod {
		}
		.orders_table .item .status {
		}
		.orders_table .item .action {
			text-align: center;
		}
		.orders_table .item .date {
			color: #999;
			text-align: center;
		}



</style>
<?php




function price($price)
{
	global $modx;
	return $modx->runSnippet('Price',array('price'=>$price));
}