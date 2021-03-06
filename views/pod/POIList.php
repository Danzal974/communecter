
	<div class="panel panel-white user-list">
		<div class="panel-heading border-light bg-azure">
			<h4 class="panel-title"><i class="fa fa-map-marker"></i> Points d'intérêt</h4>		
		</div> 
		<div class="panel-tools">
			<a  href="javascript:;" onclick="openForm('poi','subPoi')" 
				class="btn btn-xs btn-default tooltips" data-placement="bottom" 
				data-original-title="<?php echo Yii::t("common","Add") ?>" >
					<i class="fa fa-plus"></i> <?php echo Yii::t("common","Add") ?>
			</a>
		</div>
		<div class="panel-scroll height-230 ps-container">
			<div class="padding-10">
			
				<?php 
				
				if(empty($pois)){ ?>
				<div class="padding-10">
					<blockquote class="no-margin">
					<?php echo Yii::t("common","Ajouter des points d'interets à cet élément");  ?>
					</blockquote>
				</div>
			<?php }
			else{
				foreach ($pois as $p) { 
				?>
					<div style="border-bottom:1px solid #ccc" id="<?php echo "poi".(string)$p["_id"] ?>">
						<?php 
						
						echo '<a href="javascript:toggle(\'.poi'.InflectorHelper::slugify($p["name"]).'\', \'.poiPanel\')">'.$p["name"].'</a>';
						
						if(@$p["geo"]){?>
						<a href="javascript:showMap(true);"><i class="fa fa-map-marker"></i></a>
						<?php }?>
						
						<div class="padding-10 poiPanel poi<?php echo InflectorHelper::slugify($p["name"])?> hide">

							<?php 
							if(@$p["description"]){ ?>
							<div class=""><?php echo $p["description"] ?></div>
							<?php  }?>

							<?php 
							if(@$p["medias"]){
								echo '<div class="space10"></div>';
							foreach ($p["medias"] as $m) { ?>
								<div class="col-xs-12">
									<div class="col-xs-4 col-md-12">
										<?php if(@$m["content"]["image"]){?>
											<img src="<?php echo @$m["content"]["image"] ?>" class="img-responsive">
										<?php } ?>
									</div>
									<div class="col-xs-8  col-md-12">
										<a class="text-bold" href="<?php echo @$m["content"]["url"] ?>" target="_blank"><?php echo @$m["name"] ?></a><br/>
										<?php echo @$m["description"] ?>
									</div>
								</div>
							<?php } }?>

							<?php 
							if(@$p["tags"]){
								echo '<div class="space10"></div>';
							foreach ($p["tags"] as $t) {  ?>
								<a href="<?php echo $t?>" class="label label-inverse"><?php echo $t?></a> 
							<?php } } ?>
							<div class="space5"></div>
							<?php if(@$p["creator"] == Yii::app()->session["userId"] ){?>
								<a href="javascript:;" class="btn btn-xs btn-default text-red deleteThisBtn pull-right" data-type="poi" data-id="<?php echo (string)$p["_id"] ?>" ><i class="fa fa-trash"></i></a> 
								<a href="javascript:;" class="btn btn-xs btn-default text-green editThisBtn pull-right"  data-type="poi" data-id="<?php echo (string)$p["_id"] ?>" ><i class="fa fa-pencil-square-o"></i></a>
								<div class="space1"></div>
							<?php }?>
						</div>

					</div>
			<?php
				}
			}?>
			</div>
		</div>
	</div>

	<script type="text/javascript">
		
		$(".deleteThisBtn").off().on("click",function () 
		{
			mylog.log("deleteThisBtn click");
	        $(this).empty().html('<i class="fa fa-spinner fa-spin"></i>');
	        var btnClick = $(this);
	        var id = $(this).data("id");
	        var type = $(this).data("type");
	        var urlToSend = baseUrl+"/"+moduleId+"/element/delete/type/"+type+"/id/"+id;
	        
	        bootbox.confirm("confirm please !!",
        	function(result) 
        	{
				if (!result) {
					btnClick.empty().html('<i class="fa fa-trash"></i>');
					return;
				} else {
					$.ajax({
				        type: "POST",
				        url: urlToSend,
				        dataType : "json"
				    })
				    .done(function (data) {
				        if ( data && data.result ) {
				        	toastr.info("élément effacé");
				        	$("#"+type+id).remove();
				        	//window.location.href = "";
				        } else {
				           toastr.error("something went wrong!! please try again.");
				        }
				    });
				}
			});

		});
		$(".editThisBtn").off().on("click",function () 
		{
	        $(this).empty().html('<i class="fa fa-spinner fa-spin"></i>');
	        var btnClick = $(this);
	        var id = $(this).data("id");
	        var type = $(this).data("type");
	        editElement(type,id);
		});
	</script>