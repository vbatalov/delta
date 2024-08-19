<?php

$sm_base= '../assets/modules/catalogfilter/';
$module_url= MODX_MANAGER_URL.'?a='.$_GET['a'].'&id='.$_GET['id'];


// =======================================================================
$modx->db->query("CREATE TABLE IF NOT EXISTS ".$modx->getFullTableName('_catfilter')." (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` set('value','values','price','interval') NOT NULL DEFAULT 'value',
  `name` varchar(255) NOT NULL,
  `ed` varchar(32) NOT NULL,
  `folders` text NOT NULL,
  `i` tinyint(4) NOT NULL,
  `e` set('y','n') NOT NULL DEFAULT 'y',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
$modx->db->query("CREATE TABLE IF NOT EXISTS ".$modx->getFullTableName('_catfilter_value')." (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cf_id` int(11) NOT NULL,
  `itemid` int(11) NOT NULL,
  `value` varchar(255) NOT NULL,
  `dop` varchar(255) NOT NULL,
  `e` set('y','n') NOT NULL DEFAULT 'y',
  PRIMARY KEY (`id`),
  KEY `cf_id` (`cf_id`),
  KEY `itemid` (`itemid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
$modx->db->query("CREATE TABLE IF NOT EXISTS ".$modx->getFullTableName('_catfilter_value_cache')." (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `folderid` int(11) NOT NULL,
  `itemid` int(11) NOT NULL,
  `cf_id` int(11) NOT NULL,
  `cfv_id` int(11) NOT NULL,
  `value` varchar(255) NOT NULL,
  `e` set('y','n') NOT NULL DEFAULT 'y',
  `dt` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `folderid` (`folderid`),
  KEY `itemid` (`itemid`),
  KEY `cf_id` (`cf_id`),
  KEY `cfv_id` (`cfv_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
// =======================================================================

$catalog_koren= 4;



// AJAX =======================================================================
if( isset( $_GET[ 'ajax' ] ) )
{
	
	exit();
}
// AJAX =======================================================================



if( $_GET[ 'act' ] == 'add' )
{
	$modx->db->query( "INSERT INTO ". $modx->getFullTableName( '_catfilter' ) ." SET name='Новое св-во', type='value'" );
	
	header( 'location: '. $module_url );
	exit();
}

if( $_GET[ 'act' ] == 'save' )
{
	$propid= intval( $_GET[ 'propid' ] );
	$prop_name= addslashes( trim( $_POST[ 'prop_name' ] ) );
	$prop_ed= addslashes( trim( $_POST[ 'prop_ed' ] ) );
	$prop_type= addslashes( $_POST[ 'prop_type' ] );
	
	if( $propid > 0 && $prop_type )
	{
		$modx->db->query( "UPDATE ". $modx->getFullTableName( '_catfilter' ) ." SET name='{$prop_name}', ed='{$prop_ed}', type='{$prop_type}' WHERE id={$propid} LIMIT 1" );
	}
	
	header( 'location: '. $module_url );
	exit();
}

if( $_GET[ 'act' ] == 'disablabl' )
{
	$propid= intval( $_GET[ 'propid' ] );
	
	if( $propid > 0 )
	{
		$rr= $modx->db->query( "SELECT e FROM ". $modx->getFullTableName( '_catfilter' ) ." WHERE id={$propid} LIMIT 1" );
		if( $rr && $modx->db->getRecordCount( $rr ) == 1 )
		{
			$val= ( $modx->db->getValue($rr) == 'y' ? 'n' : 'y');
			$modx->db->query( "UPDATE ". $modx->getFullTableName( '_catfilter' ) ." SET e='{$val}' WHERE id={$propid} LIMIT 1" );
		}
	}
	
	header( 'location: '. $module_url );
	exit();
}

if( $_GET[ 'act' ] == 'edit' && $_GET[ 'act2' ] == 'savetree' )
{
	$propid= intval( $_GET[ 'propid' ] );
	$galki= $_POST[ 'galka' ];
	
	$docs= '';
	
	if( $propid > 0 && ! empty( $galki ) )
	{
		foreach( $galki AS $key => $val )
		{
			$tmp= explode( '-', $key );
			$tmp2= '';
			foreach( $tmp AS $row )
			{
				$tmp2 .= ( ! empty( $tmp2 ) ? '-' : '' ) . $row;
				if( $tmp2 != $key && $galki[ $tmp2 ] == $row ) continue 2;
			}
			$docs .= ','. $row;
		}
	}
	if( ! empty( $docs ) ) $docs .= ',';
	
	$modx->db->query( "UPDATE ". $modx->getFullTableName( '_catfilter' ) ." SET folders='{$docs}' WHERE id={$propid} LIMIT 1" );
	
	header( 'location: '. $module_url .'&act=edit&propid='. $propid );
	exit();
}





if( $result != '' ) $result .= '<br /><br />';
if( $result2 != '' ) $result2 .= '<br /><br />';



function galki_to_child( $id, &$docs )
{
	global $modx;
	
	$cats= $modx->getActiveChildren( $id, 'menuindex', 'ASC', 'id,isfolder' );
	if( ! empty( $cats ) )
	{
		foreach( $cats AS $cat )
		{
			if( $cat[ 'isfolder' ] )
			{
				$docs .= ','. $cat[ 'id' ];
				galki_to_child( $cat[ 'id' ], $docs );
			}
		}
	}
}

function catalogtree( $id, $ids, &$print, $docs, $docinfo, $list='', $checkbox=false )
{
	global $modx;
	
	$ids .= ( ! empty( $ids ) ? '-' : '' ) . $id;
	
	$galka= ( $docs[ $id ] == $id ? true : false );
	
	$print .= '<div class="ctr_item"><input class="'. $list .'" '.( $galka || $checkbox ? 'checked' : '' ).' '.( $checkbox ? 'disabled="disabled"' : '' ).' type="checkbox" name="galka['. $ids .']" value="'. $id .'" /> <span>'. $docinfo[ 'pagetitle' ] .'</span> ('. $id .')</div>';
	
	if( $galka ) return;
	
	$checkbox= ( $galka || $checkbox ? true : false );
	
	$cats= $modx->getActiveChildren( $id, 'menuindex', 'ASC', 'id,pagetitle,isfolder' );
	if( ! empty( $cats ) )
	{
		foreach( $cats AS $cat )
		{
			if( $cat[ 'isfolder' ] )
			{
				$print .= '<div class="ctr_sub ctr_sub_open '.( $galka ? 'ctr_sub_disabled' : '' ).'">';
					catalogtree( $cat[ 'id' ], $ids, $print, $docs, $cat, $list .' doc'. $cat[ 'id' ], $checkbox );
				$print .= '</div>';
			}
		}
	}
}

function catalogtree_______( $id, &$print, $docs, $list='' )
{
	global $modx;
	
	$cats= $modx->getActiveChildren( $id, 'menuindex', 'ASC', 'id,pagetitle,isfolder' );
	if( ! empty( $cats ) )
	{
		foreach( $cats AS $cat )
		{
			if( $cat[ 'isfolder' ] )
			{
				$print .= '<div class="ctr_item"><input class="'. $list .'" '.( $docs[ $cat[ 'id' ] ] == $cat[ 'id' ] ? 'checked' : '' ).' type="checkbox" name="galka['. $cat[ 'id' ] .']" value="'. $cat[ 'id' ] .'" /> '. $cat[ 'pagetitle' ] .' ('. $cat[ 'id' ] .')</div>';
				
				$print .= '<div class="ctr_sub">';
					catalogtree( $cat[ 'id' ], $print, $docs, $list .' doc'. $cat[ 'id' ] );
				$print .= '</div>';
			}
		}
	}
}
?>
<div class="modul_scorn_all">
<!-- -------------------------- -->
<link rel="stylesheet" type="text/css" href="<?=$sm_base?>_styles.css" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<!-- script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css" -->


<div class="topmenu">
	<ul>
		<li><a href="<?= $module_url ?>">Главная</a></li>
		<li><a href="<?= $module_url ?>&act=add">Добавить свойство</a></li>
	</ul>
	<div class="clr">&nbsp;</div>
</div>

<script type="text/javascript">
$(document).ready(function(){
	var $= jQuery.noConflict();
	$( '.ctr_item >span' ).click(function(){
		var elem= $( '>.ctr_sub', $( this ).parent().parent() );
		if( elem.hasClass( 'ctr_sub_open' ) )
		{
			elem.removeClass( 'ctr_sub_open' );
		}else{
			elem.addClass( 'ctr_sub_open' );
		}
	});
});
</script>


<div class="propslist">
<?php if( $_GET[ 'act' ] == 'edit' ){

	$propid= intval( $_GET[ 'propid' ] );
	
	$prop= $modx->db->query( "SELECT * FROM ". $modx->getFullTableName( '_catfilter' ) ." WHERE id={$propid} LIMIT 1" );
	if( $prop && $modx->db->getRecordCount( $prop ) )
	{
		$prop= $modx->db->getRow( $prop, 'assoc' );
	}
	
	$print .= '<h1>'. $prop[ 'id' ] .'. '. $prop[ 'name' ] .'</h1>';
	
	$propdocs= explode( ",", $prop[ 'folders' ] );
	if( ! empty( $propdocs ) )
	{
		foreach( $propdocs AS $val )
		{
			$docs[ $val ]= $val;
		}
	}
	
	$print .= '<form action="'. $module_url .'&act=edit&act2=savetree&propid='. $propid .'" method="post">';
		$print .= '<div class="catalogtree">';
			//catalogtree( $catalog_koren, $print, $docs );
			catalogtree( $catalog_koren, '', $print, $docs, array( 'pagetitle'=>'Весь каталог' ) );
		$print .= '</div>';
		$print .= '<br /><br /><input type="submit" value="Сохранить привязки" />';
	$print .= '</form>';

	print $print;






//=====================================================================================================================
	}else{
	$props= $modx->db->query( "SELECT * FROM ". $modx->getFullTableName( '_catfilter' ) ."" );
	if( $props && $modx->db->getRecordCount( $props ) )
	{
		while( $prop= $modx->db->getRow( $props,'assoc' ) )
		{
			$print .= '<div class="propitem '.( $prop[ 'e' ]=='y' ? '' : 'disabled' ).'"><form action="'. $module_url .'&act=save&propid='. $prop[ 'id' ] .'" method="post">
				'. $prop[ 'id' ] .'. <input type="text" name="prop_name" value="'. $prop[ 'name' ] .'" /> &nbsp;
				<input type="text" name="prop_ed" value="'. $prop[ 'ed' ] .'" />
				<br /><br />Тип свойства: <select name="prop_type">
					<option '.( $prop[ 'type' ] == 'value' ? 'selected' : '' ).' value="value">Значение</option>
					<option '.( $prop[ 'type' ] == 'values' ? 'selected' : '' ).' value="values">Несколько значений</option>
					<option '.( $prop[ 'type' ] == 'price' ? 'selected' : '' ).' value="price">Цена от до</option>
					<option '.( $prop[ 'type' ] == 'interval' ? 'selected' : '' ).' value="interval">Значение от до</option>
				</select><br /><br />
				<input type="submit" value="Сохранить" /> &nbsp;
				<a style="color:#1f69c6;" href="'. $module_url .'&act=edit&propid='. $prop[ 'id' ] .'">Изменить привязки »</a> &nbsp; &nbsp;
				<a style="color:#1f69c6;" href="'. $module_url .'&act=disablabl&propid='. $prop[ 'id' ] .'">'.( $prop[ 'e' ]=='y' ? 'Выключить' : 'Включить' ).' свойство</a>
			</form></div>';
		}
		
		$print .= '<div class="clr">&nbsp;</div>';
	}

	print $print;
}?>
</div>



<?php



function clearCache()
{
	global $modx;
	
	$modx->clearCache();
	
	include_once MODX_BASE_PATH . 'manager/processors/cache_sync.class.processor.php';
	$sync= new synccache();
	$sync->setCachepath( MODX_BASE_PATH . "assets/cache/" );
	$sync->setReport( false );
	$sync->emptyCache();
}

