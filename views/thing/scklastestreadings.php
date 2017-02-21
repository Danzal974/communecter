<?php 
$cs = Yii::app()->getClientScript();
?>

<!--?php
	$cs = Yii::app()->getClientScript();
	// if(!Yii::app()->request->isAjaxRequest)
	// {
	  	/*$cssAnsScriptFilesModule = array(
	  		'/plugins/d3/d3.v3.min.js',
        '/plugins/d3/c3.min.js',
        '/plugins/d3/c3.min.css',
        '/plugins/d3/d3.v4.min.js',

	  	);*/
	  	HtmlHelper::registerCssAndScriptsFiles($cssAnsScriptFilesModule, Yii::app()->request->baseUrl);
  	// }

?-->

<div class="panel panel-white col-sm-12 col-md-10">
  <section class="col-sm-12 panel-title">
   <div class="panel-heading text-center border-light">
	<h3 class="panel-title text-blue">Dernières mesures des Smart-Citizen-Kits</h3>



   </div>
  </section>
</div>
<section class="panel-body no-padding center">
  <div class="col-md-10 table-responsive">
	<table id="tableau" class="table table-bordered table-striped table-condensed">
 		<caption> <h4>Dernière mesures </h4></caption>
   		<thead>
   		 <tr>
   			<th>id</th><th>name</th><th>value</th><th>unit</th><th>description</th>
   		 </tr>
   		</thead>
   		<tbody id='tbody'> 	</tbody></table>
  </div>
</section>


<script type="text/javascript">
	var lastestReadings = <?php echo json_encode(Thing::getLastedReadViaAPI()) ?> ;
	console.log(sensors);

	//document.getElementById("titre2").innerHTML = JSON.stringify(json);
	var tbody = document.getElementById("tbody");
	
	sensors.forEach( function(item){ 
		var ligne=[item.id,item.name,item.value,item.unit,item.description];
		var tr = document.createElement("tr");
		tbody.appendChild(tr);

		for (var i=0;i<ligne.length;i++){
		var td = document.createElement("td");
		td.innerHTML=ligne[i].toString();
		tr.appendChild(td);
		}
		tbody.appendChild(tr);

	});

  

  jQuery(document).ready(function() {
    setTitle("Dernières mesures","cog");
   //Index.init();
  });



</script>

