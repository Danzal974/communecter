<?php 
  HtmlHelper::registerCssAndScriptsFiles( array('/assets/css/default/directory.css'));
  HtmlHelper::registerCssAndScriptsFiles( array('/js/default/directory.js') , $this->module->assetsUrl);
?>

<style>
  
  .btn-add-to-directory{
    font-size: 14px;
    margin-right: 0px;
    border-radius: 6px;
    color: #666;
    border: 1px solid rgba(188, 185, 185, 0.69);
    margin-left: 3px;
    float: left;
    padding: 1px;
    width: 24px;
    margin-top: 15px;
  }
  .searchEntity{
    padding: 10px 0 10px 0 !important;
    margin: 0px !important;
    border-top: solid rgba(128, 128, 128, 0.2) 1px;
    margin-left: 0% !important;
    width: 100%;
  }
  .searchEntity:hover{
    background-color: rgba(211, 211, 211, 0.2);
  }

  #grid_around{
    margin:0 -15px 0 -15px;
  }

  .btn-groupe-around-me-km {
    display: inline-block!important;
  }

</style>

<div class="row headerDirectory bg-white padding-15">
  <h3 class="text-dark text-left">
    <i class="fa fa-crosshairs"></i> Retrouvez les éléments <b>les plus actifs autour de vous</b>, dans un rayon de 
    <select class="inline text-red" id="stepSearch" style="padding: 6px;font-size:17px;">
      <option value="2000" <?php echo $radius=="2000"?"selected":"";?>>2</option>
      <option value="5000" <?php echo $radius=="5000"?"selected":"";?>>5</option>
      <option value="10000" <?php echo $radius=="10000"?"selected":"";?>>10</option>
      <option value="25000" <?php echo $radius=="25000"?"selected":"";?>>25</option>
      <option value="50000" <?php echo $radius=="50000"?"selected":"";?>>50</option>
    </select> km
    <button class="btn btn-default text-azure" style="margin-left:20px;" onclick="javascript:showMap(true)">
      <i class="fa fa-map-marker"></i> Afficher sur la carte
    </button>
  </h3>

    
    <div class="info-no-result <?php if(sizeOf($all)>0) echo 'hidden'; ?>">
      <h3 class="text-red">
        <i class="fa fa-ban"></i> Aucun élément n'a été trouvé.
        <br><small><b>Élargissez la zone de recherche pour plus de résultat</b></small>
      </h3>
      <button class="btn bg-dark" id="reloadAuto"><i class="fa fa-binoculars"></i> Recherche automatique</button>
    </div>
    
    <div class="info-results <?php if(sizeOf($all)==0) echo 'hidden'; ?>">
      <h3 class="text-dark">
        <b>
          <i class="fa fa-angle-down"></i> 
          <span id="nbResAroundMe"></span>
        </b>
      </h3>
    </div>
  </div>

  <div id="grid_around"></div>


<script>

var mapElements = new Array();
var elementsMap = <?php echo json_encode($all) ?>;
var elementPosition = [<?php echo @$lat ?>, <?php echo @$lng ?>];

var personCOLLECTION = "<?php echo Person::COLLECTION ?>";

var radiusElement = "<?php echo $radius; ?>";
var idElement = "<?php echo $id ?>";
var typeElement = "<?php echo $type ?>";
var parentName = "<?php echo @$parentName ?>";

var noFitBoundAroundMe = true;

jQuery(document).ready(function() {
	
	setTitle("Autour de moi",
			 "<i class='fa fa-crosshairs'></i>", 
			 "Autour de moi");

  //console.log(elementsMap);

  //showMap(true);
	if(notEmpty(elementsMap)){ 
      var str = showResultsDirectoryHtml(elementsMap);
      $("#grid_around").html(str);
      initBtnLink();
      refreshUIAroundMe(elementsMap); 
  }

  $("#stepSearch").change(function(){
    radiusElement = $(this).val();
    refreshAroundMe(radiusElement);
  });
  $("#reloadAuto").click(function(){
    radiusElement = $("#stepSearch").val();
    refreshAroundMe(radiusElement);
  });

  $(".btn-groupe-around-me-km .btn-map").off().click(function(){
    var km = $(this).data("km");
    if(km>0)
    refreshAroundMe(km);
  });

  $(".btn-groupe-around-me-km .btn-map").removeClass("active");
  $(".btn-groupe-around-me-km .btn-map[data-km='"+radiusElement+"']").addClass("active");

  <?php if(isset($_GET["tpl"]) && @$_GET["tpl"]=="iframesig"){ ?>
    //iframesig TPL
    var lblParentName = "<span class='text-'>"+parentName+"</span>";
    $(".main-top-menu #menuParentName").html(parentName);
  <?php } ?>

});



function refreshUIAroundMe(elementsMap){
  
  //if(notEmpty(Sig.myPosition))
  //var myLatlng = [Sig.myPosition.position.latitude, Sig.myPosition.position.longitude];
  var nbRes = elementsMap.length;
  Sig.showMapElements(Sig.map, elementsMap);

  setTimeout(function(){
    Sig.showCircle(elementPosition, radiusElement);
    Sig.map.fitBounds(Sig.circleAroundMe.getBounds());

    <?php if(!isset($_GET["tpl"])||@$_GET["tpl"]!="iframesig"){ ?>
    setTimeout(function(){ Sig.map.panBy([100, 0]); }, 500);
    <?php } ?>

  }, 500);
 
  if(nbRes==0){
    $(".info-results").addClass("hidden");
    $(".info-no-result").removeClass("hidden");
  }else{
    $(".info-results").removeClass("hidden");
    $(".info-no-result").addClass("hidden");

    var s =  nbRes>1?"s":"";
    nbRes = nbRes + " élément" + s + " trouvé" + s;
    $("#nbResAroundMe").html(nbRes);
  }

  $("#stepSearch").val(radiusElement);

}

function refreshAroundMe(radius){
  $("#grid_around").html("<h4><i class='fa fa-refresh fa-spin' style='margin-left:15px;'></i> Nouvelle recherche en cours</h4>");
  $("#loader-aroundme").html("<i class='fa fa-refresh fa-spin'></i>");
  
  $(".btn-groupe-around-me-km .btn-map").removeClass("active");
  $(".btn-groupe-around-me-km .btn-map[data-km='"+radius+"']").addClass("active");
  
  showMapLegende("refresh fa-spin", 
                 "Chargement en cours ...<br><small>Le chargement peut prendre plusieurs secondes<br>merci de patienter...</small>");
  Sig.clearMap();

  var url = "/element/aroundme/type/"+typeElement+"/id/"+idElement+"/radius/"+radius+"/manual/true/json/true";
  $.ajax({
    type: "POST",
    url: baseUrl+"/"+moduleId+url,
    dataType: "json",
    success: function(data) {
      if (data.result) {
        radiusElement = data.radius;
        //location.hash = "#element.aroundme.type."+typeElement+".id."+idElement+".radius."+radiusElement+".manual.true";
        var str = showResultsDirectoryHtml(data.all);
         $("#grid_around").html(str);
        initBtnLink();
        refreshUIAroundMe(data.all); 
        $("#loader-aroundme").html("");
        setTimeout(function(){ hideMapLegende(); }, 300);
      } else {
        toastr.error(data.msg);
      }
    },
  });  
}

function initBtnLink(){
  $('.tooltips').tooltip();
  //parcours tous les boutons link pour vérifier si l'entité est déjà dans mon répertoire
  $.each($(".followBtn"), function(index, value){
    var id = $(value).attr("data-id");
    var type = $(value).attr("data-type");
    //console.log("error type :", type);
    if(type == "person") type = "people";
    else type = typeObj[type].col;
    //console.log("#floopItem-"+type+"-"+id);
    if($("#floopItem-"+type+"-"+id).length){
      //console.log("I FOLLOW THIS");
      if(type=="people"){
        $(value).html("<i class='fa fa-unlink text-green'></i>");
        $(value).attr("data-original-title", "Ne plus suivre cette personne");
        $(value).attr("data-ownerlink","unfollow");
      }
      else{
        $(value).html("<i class='fa fa-user-plus text-green'></i>");
        
        if(type == "organizations")
          $(value).attr("data-original-title", "Vous êtes membre de cette organization");
        else if(type == "projects")
          $(value).attr("data-original-title", "Vous êtes contributeur de ce projet");
        
        //(value).attr("onclick", "");
        $(value).removeClass("followBtn");
      }
    }
    if($(value).attr("data-isFollowed")=="true"){

      $(value).html("<i class='fa fa-unlink text-green'></i>");
      $(value).attr("data-original-title", (type == "events") ? "Ne plus participer" : "Ne plus suivre" );
      $(value).attr("data-ownerlink","unfollow");
      $(value).addClass("followBtn");
    }
  });

  //on click sur les boutons link
  $(".followBtn").click(function(){
    formData = new Object();
    formData.parentId = $(this).attr("data-id");
    formData.childId = userId;
    formData.childType = personCOLLECTION;
    var type = $(this).attr("data-type");
    var name = $(this).attr("data-name");
    var id = $(this).attr("data-id");
    //traduction du type pour le floopDrawer
    var typeOrigine = typeObj[type].col;
    if(typeOrigine == "persons"){ typeOrigine = personCOLLECTION;}
    formData.parentType = typeOrigine;
    if(type == "person") type = "people";
    else type = typeObj[type].col;

  var thiselement = this;
  $(this).html("<i class='fa fa-spin fa-circle-o-notch text-azure'></i>");
  //console.log(formData);
  var linkType = (type == "events") ? "connect" : "follow";
  if ($(this).attr("data-ownerlink")=="follow"){
    $.ajax({
      type: "POST",
      url: baseUrl+"/"+moduleId+"/link/"+linkType,
      data: formData,
      dataType: "json",
      success: function(data) {
        if(data.result){
          toastr.success(data.msg); 
          $(thiselement).html("<i class='fa fa-unlink text-green'></i>");
          $(thiselement).attr("data-ownerlink","unfollow");
          $(thiselement).attr("data-original-title", (type == "events") ? "Ne plus participer" : "Ne plus suivre");
          addFloopEntity(id, type, data.parentEntity);
        }
        else
          toastr.error(data.msg);
      },
    });
  } else if ($(this).attr("data-ownerlink")=="unfollow"){
    formData.connectType =  "followers";
    //console.log(formData);
    $.ajax({
      type: "POST",
      url: baseUrl+"/"+moduleId+"/link/disconnect",
      data : formData,
      dataType: "json",
      success: function(data){
        if ( data && data.result ) {
          $(thiselement).html("<i class='fa fa-chain'></i>");
          $(thiselement).attr("data-ownerlink","follow");
          $(thiselement).attr("data-original-title", (type == "events") ? "Participer" : "Suivre");
          removeFloopEntity(data.parentId, type);
          toastr.success(trad["You are not following"]+data.parentEntity.name);
        } else {
           toastr.error("You leave succesfully");
        }
      }
    });
  }
  });
  //on click sur les boutons link
  $(".btn-tag").click(function(){
    setSearchValue($(this).html());
  });
}


</script>