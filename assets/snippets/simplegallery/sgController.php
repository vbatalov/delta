<?php
include_once(MODX_BASE_PATH . 'assets/lib/APIHelpers.class.php');   
    
$prepare = array();
$prepare[] = \APIhelpers::getkey($modx->event->params, 'BeforePrepare', '');
$prepare[] = 'DLsgController::prepare';
$prepare[] = \APIhelpers::getkey($modx->event->params, 'AfterPrepare', '');
$modx->event->params['prepare'] = trim(implode(",", $prepare), ',');

$params = array_merge(array(
    "controller"    =>  "sg_site_content",
    "dir"        =>  "assets/snippets/simplegallery/controller/"
), $modx->event->params);
if(!class_exists("DLsgController", false)){
    class DLsgController{
        public static function prepare(array $data = array(), DocumentParser $modx, $_DocLister, prepare_DL_Extender $_extDocLister){
            if (isset($data['images'])) {
                $wrapper='';
                $imageField = $_DocLister->getCfgDef('imageField','sg_image');
                $thumbOptions = $_DocLister->getCfgDef('thumbOptions');
                $thumbSnippet = $_DocLister->getCfgDef('thumbSnippet');
                foreach ($data['images'] as $image) {
                    $ph = $image;
                    if(!empty($thumbOptions) && !empty($thumbSnippet)){
                        $_thumbOptions = json_decode($thumbOptions,true);
                        if (is_array($_thumbOptions)) {
                            foreach ($_thumbOptions as $key => $value) {
                                $postfix = $key == 'default' ? '.' : '_'.$key.'.';
                                $ph['thumb'.$postfix.$imageField] = $modx->runSnippet($thumbSnippet, array(
                                    'input' => $ph[$imageField],
                                    'options' => $value
                                )); 
                                $info = getimagesize(MODX_BASE_PATH.$ph['thumb'.$postfix.$imageField]);
                                $ph['thumb'.$postfix.'width.'.$imageField] = $info[0];
                                $ph['thumb'.$postfix.'height.'.$imageField] = $info[1];
                            }
                        } else {
                            $ph['thumb.'.$imageField] = $modx->runSnippet($thumbSnippet, array(
                                'input' => $ph[$imageField],
                                'options' => $thumbOptions
                            )); 
                            $info = getimagesize(MODX_BASE_PATH.$ph['thumb.'.$imageField]);
                            $ph['thumb.width.'.$imageField] = $info[0];
                            $ph['thumb.height.'.$imageField] = $info[1];
                        }
                    }
                    //сделали превьюшку

                    $ph['e.sg_title'] = htmlentities($image['sg_title'], ENT_COMPAT, 'UTF-8', false);
                    $ph['e.sg_description'] = htmlentities($image['sg_description'], ENT_COMPAT, 'UTF-8', false);
                    //добавили поля e.sg_title и e.sg_description
                    $properties = json_decode($image['sg_properties'],true);
                        foreach ($properties as $key => $value) {
                        $ph['properties.'.$key] = $value;
                    }
                    $wrapper .= $_DocLister->parseChunk($_DocLister->getCfgDef('sgRowTpl'), $ph);
                    //обработали чанк sgRowTpl - для каждой картинки
                }
                $data['images'] = $_DocLister->parseChunk($_DocLister->getCfgDef('sgOuterTpl'),array('wrapper'=>$wrapper));
                //обработали чанк sgOuterTpl
            }
            return $data;
        }
    }
}
return $modx->runSnippet("DocLister", $params);
?>