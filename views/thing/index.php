<?php
$cs = Yii::app()->getClientScript();
//HtmlHelper::registerCssAndScriptsFiles( $cssAnsScriptFilesModule ,Yii::app()->request->baseUrl);
?>

<div class="col-xs-12" id="">
  <div class="panel panel-white">
      <div class="panel-heading text-center border-light">
        <h3 class="panel-title text-blue"> Smart-Citizen-Kit </h3>

      </div>

      <div class="panel-body no-padding center">
        <ul class="list-group text-left no-margin">
           

          <li class="list-group-item text-yellow col-md-4 col-sm-6 link-to-directory">
            <div class="" style="cursor:pointer;" onclick="loadByHash('#thing.graph')">
              <i class="fa fa-line-chart fa-2x"></i>
                
              <?php echo Yii::t("thing", "GRAPHES", null, Yii::app()->controller->module->id); ?>
              
            </div>
          </li>
          
          <li class="list-group-item text-yellow col-md-4 col-sm-6 link-to-directory">
            <div class="" style="cursor:pointer;" onclick="loadByHash('#thing.scklastestreadings')">
              <i class="fa fa-database fa-2x"></i>
                
              <?php echo Yii::t("thing", "DERNIERES MESURES", null, Yii::app()->controller->module->id); ?>
              
            </div>
          </li>


        </ul>
      </div>
  </div>

</div>
<script>

  jQuery(document).ready(function() {
    setTitle("Thing Reading","cog");
   //Index.init();
  });

</script>