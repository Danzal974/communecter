<?php 
$cs = Yii::app()->getClientScript();
?>

<?php
	$cs = Yii::app()->getClientScript();
	// if(!Yii::app()->request->isAjaxRequest)
	// {
	  	$cssAnsScriptFilesModule = array(
	  		'/plugins/d3/d3.v3.min.js',
        '/plugins/d3/c3.min.js',
        '/plugins/d3/c3.min.css',
	  	);
	  	HtmlHelper::registerCssAndScriptsFiles($cssAnsScriptFilesModule, Yii::app()->request->baseUrl);
  	// }

?>
<div class="col-xs-12" id="">
<h3 class="panel-title text-blue"> Les graphes </h3>

</div>


<h4>Evolution de la température</h4>



<script>

  jQuery(document).ready(function() {
    setTitle("Mesures","cog");
   
  });

</script>