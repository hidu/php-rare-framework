<h1>content of index tpl</h1>
<p>this is  index tpl</p>
<?php echo date("Y-m-d H:i:s")?>


<p>GET Params:</p>
<?php rare_print($_GET);?>

<?php echo url("index?name=1&title=你好");?>
