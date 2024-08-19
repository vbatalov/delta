<?php
//v02
//DMTCaptcha
//====================================================================================
//Подключение к MODx
	//include_once(dirname(__FILE__)."/../../assets/cache/siteManager.php");
	//require_once(dirname(__FILE__).'/../../manager/includes/protect.inc.php');
	include_once(dirname(__FILE__).'/../../manager/includes/config.inc.php');
	//include_once(dirname(__FILE__).'/../../manager/includes/document.parser.class.inc.php');
	//$modx = new DocumentParser;
	//$modx->loadExtension("ManagerAPI");
	//$modx->db->connect();
	//$modx->getSettings();
	startCMSSession();
//====================================================================================

$use_symbols= "12345679abcdefghijhkmntwpuvxyz";
$use_symbols= "2345690";
$use_symbols_len= strlen( $use_symbols );

srand();

$simbol_color= 'random'; //red, blue, green или random - случайный

$inline= false;

$amplitude_min= 8; // Минимальная амплитуда волны
$amplitude_max= 13; // Максимальная амплитуда волны

$font_width= 26; // Приблизительная ширина символа в пикселях

$font_size_min= 40;
$font_size_max= 60;

$rand_bsimb_min= 0; // Минимальное расстояние между символами (можно отрицательное)
$rand_bsimb_max= 15; // Максимальное расстояние между символами

$rotate_simbol= 0; // Поворачивать случайно каждый символ 1 - д

$margin_left= rand( 10, 50 );// отступ слева
$margin_top= rand( 50, 60 ); // отступ сверху

$font_count= 4;// Количество шрифтов в папке DMT_captcha_fonts идущих по порядку от 1 до $font_count
$jpeg_quality= 80; // Качество картинки
$back_count= 5; // Количество фоновых рисунков в папке DMT_captcha_fonts идущих по порядку от 1 до $back_count
$length= rand( 3, 4 );
// Количество символов случайно от 3 до 4
// Если Вы укажите символов больше 4, 
// то увеличте ширину фонового рисунка ./DMT_captcha_fonts/back[все номера].gif
// Да и вообще нарисуйте свой фон!!!
//=======================================================================

if( $simbol_color == 'random' )
{
	$r= rand( 200, 230 );
	switch( rand( 1, 3 ) )
	{
		case 1: $scolor[ 'random' ]= array( $r, 0, 0 ); break 1;
		case 2: $scolor[ 'random' ]= array( 0, $r, 0 ); break 1;
		case 3: $scolor[ 'random' ]= array( 0, 0, $r ); break 1;
	}
}

while( true )
{
	$keystring= '';
	for( $i=0; $i<$length; $i++ ) $keystring .= $use_symbols{ rand( 0, $use_symbols_len-1 ) };
	if( ! preg_match( '/cb|rn|rm|mm|co|do|db|qp|qb|dp|ww/', $keystring ) ) break 1;
}

$im= @imagecreatefromjpeg( "back". rand( 1, $back_count ) .".jpg" );
//$im= @ImageCreateTrueColor( 200, 70 );

$width= @imagesx( $im );
$height= @imagesy( $im );		
$font_color= @imagecolorresolve( $im, $scolor[ $simbol_color ][0], $scolor[ $simbol_color ][1], $scolor[ $simbol_color ][2] );
$angle= 0;
$px= $margin_left;

if( $inline )
{
	//@imagettftext( $im, rand( $font_size_min, $font_size_max ), $angle, $px, $margin_top, $font_color, "font". rand( 1, $font_count ) .".ttf", $keystring );
	@imagettftext( $im, rand( $font_size_min, $font_size_max ), $angle, $px, $margin_top, $font_color, "font.ttf", $keystring );
}else{
	for( $i=0; $i<$length; $i++ )
	{
		if( $rotate_simbol )
		{
			$angle= rand( -30, 30 );
			if( $angle < 0 ) $angle= 360 + $angle;
		}
		@imagettftext( $im, rand( $font_size_min, $font_size_max ), $angle, $px, $margin_top, $font_color, "font.ttf", $keystring[$i] );
		$px += $font_width + rand( $rand_bsimb_min, $rand_bsimb_max );
	}
}
$_SESSION[ 'DMTCaptcha' ][ ( isset( $_GET[ 'id' ] ) ? $_GET[ 'id' ] : 'default' ) ]= $keystring;
$ncolor= $scolor[ $simbol_color ];
//=======================================================================

$foreground_color= array( 255, 255, 255 );
$background_color= array( 255, 255, 255 );
$width= @imagesx( $im );
$height= @imagesy( $im );
$center= $width /2;
$img2= @imagecreatetruecolor( $width, $height );
$foreground= @imagecolorresolve( $img2, $foreground_color[0], $foreground_color[1], $foreground_color[2] );
$background= @imagecolorresolve( $img2, $background_color[0], $background_color[1], $background_color[2] );
@imagefilledrectangle( $img2, 0, 0, $width-1, $height-1, $background );		
@imagefilledrectangle( $img2, 0, $height, $width-1, $height+12, $foreground );

$rand1= rand( 750000, 1200000 ) /10000000;
$rand2= rand( 750000, 1200000 ) /10000000;
$rand3= rand( 750000, 1200000 ) /10000000;
$rand4= rand( 750000, 1200000 ) /10000000;
$rand5= rand( 0, 31415926 ) /10000000;
$rand6= rand( 0, 31415926 ) /10000000;
$rand7= rand( 0, 31415926 ) /10000000;
$rand8= rand( 0, 31415926 ) /10000000;
$rand9= rand( 330, 420 ) /110;
$rand10= rand( 330, 450 ) /110;

for( $x=0; $x<$width; $x++ )
{
	for( $y=0; $y<$height; $y++ )
	{
		$sx= $x + ( sin( $x * $rand1 + $rand5 ) + sin( $y * $rand3 + $rand6 ) ) * $rand9 - $width / 2 + $center + 1;
		$sy= $y + ( sin( $x * $rand2 + $rand7 ) + sin( $y * $rand4 + $rand8 ) ) * $rand10;
		
		if( $sx < 0 || $sy < 0 || $sx >= $width-1 || $sy >= $height-1 )
		{
			continue 1;
		}else{
			$color= @imagecolorat( $im, $sx, $sy ) & 0xFF;
			$color_x= @imagecolorat( $im, $sx+1, $sy ) & 0xFF;
			$color_y= @imagecolorat( $im, $sx, $sy+1 ) & 0xFF;
			$color_xy= @imagecolorat( $im, $sx+1, $sy+1 ) & 0xFF;
		}
		if( $color == 255 && $color_x == 255 && $color_y == 255 && $color_xy == 255 )
		{
			continue 1;
		}elseif( $color == 0 && $color_x == 0 && $color_y == 0 && $color_xy == 0 ){
			$newred= $foreground_color[0];
			$newgreen= $foreground_color[1];
			$newblue= $foreground_color[2];
		}else{
			$newred= $ncolor[0];
			$newgreen= $ncolor[1];
			$newblue= $ncolor[2];
		}
		
		@imagesetpixel($img2, $x, $y, @imagecolorallocate($img2, $newred, $newgreen, $newblue));
	}
}
$im= $img2;

//=======================================================================
header( 'Expires: Sat, 17 May 2008 05:00:00 GMT' );
header( "Last-Modified: ". gmdate( "D, d M Y H:i:s" ) ." GMT" );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Cache-Control: post-check=0, pre-check=0', FALSE );
header( 'Pragma: no-cache' );
if( function_exists( "imagejpeg" ) )
{
	header( "Content-Type: image/jpeg" );
	@imagejpeg( $im, null, $jpeg_quality );
}elseif( function_exists( "imagegif" ) ){
	header( "Content-Type: image/gif" );
	@imagegif( $im );
}elseif( function_exists( "imagepng" ) ){
	header( "Content-Type: image/x-png" );
	@imagepng( $im );
}
exit();
