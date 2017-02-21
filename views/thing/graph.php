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
        '/plugins/d3/d3.v4.min.js',

	  	);
	  	HtmlHelper::registerCssAndScriptsFiles($cssAnsScriptFilesModule, Yii::app()->request->baseUrl);
  	// }

?>

<div class="panel panel-white col-sm-12 col-md-10">
  <section class="col-sm-12 panel-title">
    <div class="col-sm-12"> 
      <form class="form-inline"> 
        <div class="form-group col-sm-12">
          <label for="select" class="col-xs-12 col-sm-3 control-label">Graphe(s)</label>

          <select class="control-select col-xs-11 col-sm-8" name="sensor" id="sensorSelector" onchange="showSensor()">
            <option value="1">Température et humidité</option>
            <option value="2">Énergies</option>
            <option value="3">Luminosité</option>
            <option value="4">CO2 et NO2</option>
            <option value="5">Bruit</option>
            <option value="6">Tous les graphes</option>
          </select>
        </div>
        <div class="form-group col-sm-12">
          <label class="col-xs-12 col-sm-3 control-label" for="from">Période</label>
          
          <span class="input-group col-xs-12 col-sm-8">
            <input class="form-group" type="text" id="from" name="from"> 
            <input class="form-group" type="text" id="to" name="to">
          </span>
         
                
          
        </div>


      </form>
    </div>
  </section>


</div>


<div class="col-xs-12" id="graphs"> </div>





<script>

//variable globale :
  //Variable pour d3 et svg
 multiGraphe = [], strockeColorArray=[];
 
 svgwidth = 1000, svgheight = 300;
 gmargin = {top: 20, right: 20, bottom: 30, left: 40};
 gwidth = +svgwidth - gmargin.left - gmargin.right;
 gheight = +svgheight - gmargin.top - gmargin.bottom;

x = d3.scaleTime()
    .rangeRound([0, gwidth]);

y = d3.scaleLinear()
    .range([gheight, 0]);

line = d3.line()
 .x(function(d) { return x(d.timestamps); })
 .y(function(d) { return y(d.values); });

 var vXm = new Date();
 var dXmISO = vXm.toISOString();
 var vXn = new Date();
 vXn.setDate((vXn.getDate()-1));
 var dXnISO = vXn.toISOString();
 vYn = 0; //min
 vYm = 1;

//Variable SCK 
// TODO : recuperer les device ID sck inscrit dans les POI
var listDevice = ["2531","4162"];//,"4139","3151", "3188", "3422", "4122", "1693", "3208", "4164"];// 
// TODO : recuperer les sensor id pour chauque device par lastest readings API SC
var sckSensorIds = [{bat : 17}, {hum : 13},{temp : 12},{no2 : 15}, { co: 16}, {noise : 7}, {solarPV : 18},{ambLight : 14 }];
var tRollup = "rollup="+30+"m";

//functions 

function showSensor(){
  var value = $("#sensorSelector").val();
  hideAllGraph();
  var list=[];
  switch(value) {
    case "1": //temp et hum
      list.push(12,13);
      break;
    case "2": //energie : batt et solarPV
      list.push(17,18);
      break;
    case "3": //lum
      list.push(14);
      break;
    case "4": //co no2
      list.push(15,16);
      break; 
    case "5": //bruit : noise
      list.push(7);
      break;
    case "6":
      showAllGraph();
      break
    default:
      
      break;
  } 
  for(var s=0;s<list.length; s++){
    var divGraph = "graphe_"+list[s];
    showGraph(divGraph);
  }
}

// Fonctions pour cacher et montrer les graphe
  function showAllGraph(){
    $(".graphs").show();
  }

function showGraph(divGraph){
  $("#"+divGraph).show();
  //d3.select("#"+divGraph).attr("visibility","visible");
}

function hideAllGraph(){
  $(".graphs").hide();
}

function hideGraph(divGraph){
  $("#"+divGraph).hide();
  //d3.select("#"+divGraph).attr("visibility","hidden");
}

//
function setSVGForSensor(sensor) {

  var svgId = "sensor"+sensor;
  var divGraph = "graphe_"+sensor;
  var gId = svgId+"_g";
  console.log(svgId);

  var svgObj = d3.select("#"+divGraph)
      .append("svg").attr("width",svgwidth).attr("height",svgheight)
      .attr("id", svgId);   //.style("visibility","hidden");
  var g = svgObj.append("g").attr("transform", "translate(" + gmargin.left + "," + gmargin.top + ")").attr("id", gId);

    //gwidth = +svgObj.attr("width") - margin.left - margin.right,
    //gheight = +svgObj.attr("height") - margin.top - margin.bottom,
  //gG=svgObj.append("g").attr("transform", "translate(" + gmargin.left + "," + gmargin.top + ")");

  var objGraph = {svgid : svgId, 
        svg : svgObj,
        mesure : {description :  "", unit : "" }, 
        dimension : { width : +gwidth, 
              height : +gheight, 
              margin : gmargin },
      gid : gId , 
      domain : {Yn : vYn, Ym : vYm, Xn : vXn, Xm : vXm , domainInitialized : false},
      devices : [],
      divgraphid : divGraph,
  };

  //console.dir(objGraph);

   //Voir si on peu ce passer de la mise en tableau
   var indexObjGraphe = (multiGraphe.push(objGraph)) - 1 ;
   //console.log(indexObjGraphe);
  return indexObjGraphe; 

}


function updateTheDomain(xArray,yArray,indexGraphe){
  var yChanged = false;
  var xChanged = false;
  var Yn = multiGraphe[indexGraphe].domain.Yn;
  var Ym = multiGraphe[indexGraphe].domain.Ym;
  var Xn = multiGraphe[indexGraphe].domain.Xn;
  var Xm = multiGraphe[indexGraphe].domain.Xm;
 
  if( yArray[0] < Yn || Yn == 0) {
      multiGraphe[indexGraphe].domain.Yn = yArray[0]; yChanged = true; } //min
  if( yArray[1] > Ym || Yn == 0) { 
      multiGraphe[indexGraphe].domain.Ym = yArray[1]; yChanged = true; } //max
  if( yChanged == true || multiGraphe[indexGraphe].domain.domainInitialized == false ) { 
    y.domain([multiGraphe[indexGraphe].domain.Yn,multiGraphe[indexGraphe].domain.Ym]); }

  if(xArray[0].valueOf() < Xn.valueOf() ){ 
    multiGraphe[indexGraphe].domain.Xn = xArray[0]; xChanged = true;}
  if(xArray[1].valueOf() > Xm.valueOf() ){ 
    multiGraphe[indexGraphe].domain.Xm = xArray[1]; xChanged = true;}

  if( xChanged == true || multiGraphe[indexGraphe].domain.domainInitialized == false ) {
    x.domain([multiGraphe[indexGraphe].domain.Xn,multiGraphe[indexGraphe].domain.Xm]);
  multiGraphe[indexGraphe].domain.domainInitialized=true;
    }

}

function setAxisXY(indexGraphe){
  //console.log(indexGraphe);

  var gId = multiGraphe[indexGraphe].gid;
  var g = d3.select("#"+gId);
  var xAxisId="xAxis"+ multiGraphe[indexGraphe].svgid; 
  var yAxisId="yAxis"+ multiGraphe[indexGraphe].svgid; 
  var height = multiGraphe[indexGraphe].dimension.height;

  d3.select("#"+xAxisId).remove();    // TODO refaire la selection sur le graphe sensor
  d3.select("#"+yAxisId).remove();
    
    g.append("g")
      .attr("id", xAxisId)
      .attr("class", "theAxis")
      .attr("transform", "translate(0," + gheight + ")")
      .call(d3.axisBottom(x))
      .append("text")
      .attr("fill","#000")
      .attr("x", 1000)
      .attr("text-anchor","end")
      .text("time")
      ;

    g.append("g")
      .attr("id", yAxisId)
      .attr("class", "theAxis")
      .call(d3.axisLeft(y))
      .append("text")
      .attr("fill","#000")
      .attr("transform", "rotate(-90)")
      .attr("y", 8)
      .attr("dy", "0.71em")
      .attr("text-anchor","end")
      .text("unité")
      ;
  
}
//TODO : réglé le pb de color utiliser find dans array 
function setStrokeColorForDevice(device) {

  var stId = "sCol_"+device;
  if (strockeColorArray[stId] != null )
  {
    strockeColor = strockeColorArray[stId];
    console.log("strockeColor alreadySet: "+strockeColor);
  } else
  {   // || strockeColorArray["sCol_"+device] ) {
      strockeColor = "rgb("+Math.floor((Math.random()*220)+1)+","+
      Math.floor((Math.random()*220)+1)+","+
      Math.floor((Math.random()*220)+1)+")";
      console.log("new strockeColor :"+strockeColor);
      //stId = "sCol_"+device ;
      strockeColorArray.push({stId : strockeColor });

  }
  return strockeColor;
}

function fillArrayWithObjectTimestampsAndValues(readings){
  var d=[];
  readings.forEach(
    function(item){
      var ts = new Date();
      ts.setTime(Date.parse(item[0]));
      ts.setSeconds(0)
      item[1] = +item[1];
      d.push({timestamps : ts, values : item[1]});
    }
  );
  return d;
}
/**
@function tracer
@strockeColor 
*/
function tracer(da,device,sensor,strokeColor="blue", indexGraphe,strokeWidth=1.5){
  
  var g = d3.select("#"+multiGraphe[indexGraphe].gid);
  var gpathId = "gpId_"+device+"sensor_"+sensor;
  var graphClassSensor = "gcs_"+sensor;
      g.append("path")
        .datum(da)
        .attr("fill", "none")
        .attr("class", graphClassSensor)
        .attr("id", gpathId)
        .attr("stroke", strokeColor)
        .attr("stroke-linejoin", "round")
        .attr("stroke-linecap", "round")
        .attr("stroke-width", strokeWidth)
        .attr("d", line);
}

function graphe(device,sensors,readings,svgG){

  var de = fillArrayWithObjectTimestampsAndValues(readings);
  
    var xMinMax = d3.extent(de, function(d){return d.timestamps;});
    var yMinMax = d3.extent(de, function(d){return d.values;});

    updateTheDomain(xMinMax,yMinMax,svgG);
  strkCol = setStrokeColorForDevice(device);

  tracer(de,device,sensors,strkCol,svgG);
  
}
/*
  $( function() {
    var dateFormat = "mm/dd/yy",
      from = $( "#from" )
        .datepicker({
          defaultDate: "+1w",
          changeMonth: true,
          numberOfMonths: 3
        })
        .on( "change", function() {
          to.datepicker( "option", "minDate", getDate( this ) );
        }),
      to = $( "#to" ).datepicker({
        defaultDate: "+1w",
        changeMonth: true,
        numberOfMonths: 3
      })
      .on( "change", function() {
        from.datepicker( "option", "maxDate", getDate( this ) );
      });
 
    function getDate( element ) {
      var date;
      try {
        date = $.datepicker.parseDate( dateFormat, element.value );
      } catch( error ) {
        date = null;
      }
 
      return date;
    }
  } );
*/



jQuery(document).ready(function() {

  setTitle("Mesures","cog");

 sckSensorIds.forEach(function (item){ 

    for( var e in item) {
      sensorId = item[e];
      var nametitle= "graphe_"+sensorId;
    
      var grapheTitle = d3.select("#graphs")
        .append("div").attr("id",nametitle)
        .attr("class","graphs col-xs-12");
      $("#"+nametitle).hide();
      
      var svgG = setSVGForSensor(sensorId); 

      //TODO : Adapter la largeur des graphe à l'écran de l'utilisateur

    for ( var i = 0; i< listDevice.length ; i++) {
      var urlReq="https://api.smartcitizen.me/v0/devices/"+listDevice[i]+"/readings?sensor_id="+sensorId+"&"+tRollup+"&from="+dXnISO+"&to="+dXmISO;
      
      console.log(urlReq);

      $.ajax({

      //exemple api GET https://api.smartcitizen.me/v0/devices/1616/readings?sensor_id=7&rollup=4h&from=2015-07-28&to=2015-07-30
      type: 'GET',
      url: urlReq,
      dataType: "json",
      crossDomain: true,
      success: function (data) {
        //console.dir(data);
        var dRead = data;
        var readings = dRead.readings;

        var device = dRead.device_id;
        var sensor = dRead.sensor_id;
        var sensorkey= dRead.sensor_key;
        
        if (readings.length>=1){

        //  console.log(device);
          graphe(device,sensor,readings,svgG); 
        }

      },
      error: function (data) { console.log("Error : ajax not success"); 
      //console.log(data); 
      }

      }).done(function() {setAxisXY(svgG); });    
      
    }

  }
 });

 showSensor(); 
   
});

</script>