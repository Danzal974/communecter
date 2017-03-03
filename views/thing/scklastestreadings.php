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
<!--?php
$deviceId=4162 ;

$json = file_get_contents("https://api.smartcitizen.me/v0/devices/". $deviceId);

$lastReadDevice = json_decode($json ,true);
$sensors = $lastReadDevice["data"]["sensors"];

?-->

<div class="panel panel-white col-sm-12 col-md-10">
  <section class="col-sm-12 panel-title">
   <div class="panel-heading text-center border-light">
	<h3 class="panel-title text-blue">Dernières mesures Smart-Citizen-Kits</h3>
	 <form class="form-inline"> 
        <div class="form-group col-sm-12">
          <label for="select" class="col-xs-12 col-sm-3 control-label">Graphe(s)</label>

          <select class="control-select col-xs-11 col-sm-8" name="device" id="deviceSelector" onchange="getDeviceReadings()">
          	<option value="0" id="oemptydevice">Aucun Smartcitizen</option>

            <option value="4162">4162</option>
            <option value="4151">4151</option>
          </select>
        </div>
     </form>

   </div>
  </section>
</div>
<section class="panel-body no-padding">
  <div class="col-md-10 table-responsive">
	<table id="tableau" class="table table-bordered table-striped table-condensed">
 		<caption> <h4>Dernière mesures avec API SC</h4></caption>
   		<thead>
   		 <tr>
   			<th>id</th><th>name</th><th>value</th><th>unit</th><th>description</th>
   		 </tr>
   		</thead>
   		<tbody id='tbody'> 	</tbody>
   	</table>
  </div>
</section>

	<div id="resphp">
<!-- pour l'appel au fonction php -->

	</div>

	<div id="resjs"> 
			<!-- pour l'appel au fonction js -->

	</div>



<script>

function getDeviceReadings(){
  
  var device = parseInt($("#deviceSelector").val());
  console.log(device);
  //hideAllGraph();
  /*
  $.ajax({

      type: 'GET',
      url: urlReq,
      dataType: "json",
      crossDomain: true,
      success: function (data) {
        //console.dir(data);

        },
      error: function (data) { console.log("Error : ajax not success"); 
      //console.log(data); 
      }

      }).done(function() {  });
  */
   
} 














	var sensors = <?php 
		$obj = Thing::getLastedReadViaAPI();
		$sensors = $obj['sensors'];

	echo json_encode($sensors);
	 ?> ;

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

