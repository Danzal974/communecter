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
        '/plugins/d3/d3.v4.min.js'
	  	);
	  	HtmlHelper::registerCssAndScriptsFiles($cssAnsScriptFilesModule, Yii::app()->request->baseUrl);
  	// }

?>
<div class="col-xs-12" id="">
<h3 class="panel-title text-blue"> Les graphes </h3>

</div>
<div id="graphs"> </div>





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
 vYm = 0;

//Variable SCK 
// TODO : recuperer les device ID sck inscrit dans les POI
var listDevice = ["2531","4139","3151", "3188", "3422", "4122", "1693", "3208"];// 
// TODO : recuperer les sensor id pour chauque device par lastest readings API SC
var sckSensorIds = [{bat : 17}, {hum : 13},{temp : 12},{no2 : 15}, { co: 16}, {noise : 7}, {solarPV : 18},{ambLight : 14 }];
var tRollup = "rollup="+30+"m";

//functions 
function setSVGForSensor(sensor) {

  var svgId = "sensor"+sensor;
  var gId = svgId+"_g";
  console.log(svgId);

  var svgObj = d3.select("#graphes")
      .append("svg").attr("width",svgwidth).attr("height",svgheight)
      .attr("id", svgId);
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
  };

  console.dir(objGraph);

   //Voir si on peu ce passer de la mise en tableau
   var indexObjGraphe = (multiGraphe.push(objGraph)) - 1 ;
   console.log(indexObjGraphe);
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
  console.log(indexGraphe);

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
      .call(d3.axisBottom(x));

    g.append("g")
      .attr("id", yAxisId)
      .attr("class", "theAxis")
      .call(d3.axisLeft(y));
  
}

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



jQuery(document).ready(function() {

  setTitle("Mesures","cog");
  var init = true;

 sckSensorIds.forEach(function (item){ 

    for( var e in item) {
      sensorId = item[e];
      var nametitle= "graphe_"+sensorId;

      var grapheTitle = d3.select("#graphs").append("h4")
        .attr("id","nametitle").text("Graphe du capteur "+sensorId+" :" );
      var svgG = setSVGForSensor(sensorId); 
      //init domain


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
        console.dir(data);
        var dRead = data;
        var readings = dRead.readings;

        var device = dRead.device_id;
        var sensor = dRead.sensor_id;
        var sensorkey= dRead.sensor_key;
        
        if (readings.length>=1){

          console.log(device);
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
    
    
   
});

</script>