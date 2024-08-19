<?php ?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<div class="module_box">
    <?php
    echo 'Страны приведены к единому регистру';
    $modx->db->query("UPDATE " . $modx->getFullTableName('site_tmplvar_contentvalues') . " SET value = UPPER(value) WHERE tmplvarid = 41");
    ?>
</div>