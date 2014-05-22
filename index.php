<?php

	$lang = "es_CO";

	$lang1 = " value='es_CO' ";
	$lang2 = " value='en_US' ";
	$lang3 = " value='zh_CN' ";

	if (isset($_GET['lang'])) $lang = $_GET['lang'];
	putenv("LC_ALL=$lang");
	setlocale(LC_ALL, $lang);
	bindtextdomain("otroidioma", "locale");
	bind_textdomain_codeset('otroidioma', 'UTF-8');
	textdomain("otroidioma");

	if($lang=="es_CO")
		$lang1 = "selected='selected'";
	elseif ($lang=="en_US")
		$lang2 = "selected='selected'";
	elseif ($lang=="zh_CN")
		$lang3 = "selected='selected'";

?>



<!doctype html>
<html>
   <head>
	<meta name="viewport" content="width=device-width,initial-scale=1"> 

	<link rel="stylesheet" href="http://code.jquery.com/mobile/1.2.0/jquery.mobile-1.2.0.min.css" />

	<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script> 
	
	<script src="http://code.jquery.com/jquery-1.8.3.min.js"></script>
	<script src="http://code.jquery.com/mobile/1.2.0/jquery.mobile-1.2.0.min.js"></script>

	<script type="text/javascript" src="http://jquery-ui-map.googlecode.com/svn/trunk/ui/min/jquery.ui.map.full.min.js"></script>
    <script type="text/javascript" src="http://jquery-ui-map.googlecode.com/svn/trunk/ui/jquery.ui.map.extensions.js"></script>

	<!-- <script type="text/javascript" src="cordova.js"></script> -->
	
	<script type="text/javascript">
		var instagram_client_id = "fc8041d4af1544a2939c3f5a9a1ef8cf";
		var tag = "";

		function getPhotos(){
			var map_visible = $("#map").is(":visible");
			var list_visible = $("#list").is(":visible");
			
			if (list_visible)
			{
				if($("#list .tag").val() != "" && $("#list .tag").val() != undefined)
					tag = $("#list .tag").val();
				else
					$("#list .tag").val(tag);
			} 

			if (map_visible)
			{
				if($("#map .tag").val() != "" && $("#map .tag").val() != undefined)
					tag = $("#map .tag").val();
				else
					$("#map .tag").val(tag);
			}

			if (tag == "" || tag == undefined) {
				tag = "la";
			}

			tag = tag.replace(/(# |)/g,"");
			$(".search-button").addClass("ui-disabled");
			$(".result-count").html("<?php echo _('cargando...'); ?> ");

			if (list_visible) {
				$("#element_list").empty();	
			} else if (map_visible) {
				$("#map_canvas").gmap("clear","markers");
			}
			
			var url = "https://api.instagram.com/v1/tags/" + tag + "/media/recent?client_id=" + instagram_client_id + "&callback=?";
			$.getJSON(url, function(data){
				var data_elements = data["data"];
				var showing = 0;

				$.each(data_elements, function(index, current_element){			
					var thumbnail = current_element["images"]["thumbnail"]["url"];
					var caption = "ver imagen";

					if (current_element["caption"] != null) {
						caption = current_element["caption"]["text"];
					}

					var link = current_element["link"];

					if (list_visible) {
						showing++;
						$("#element_list").append(
							$("<li>").append(
								$("<a>").attr("href",link).attr("target","_blank").append(
									$("<img>").attr("src",thumbnail)).append(caption)
									)
							);						
					} else if (map_visible && current_element["location"] != null) {
						showing++;
						var lat = current_element["location"]["latitude"];
						var lng = current_element["location"]["longitude"];
						var position = new google.maps.LatLng(lat,lng);

						var info_window = $('<span>').append(
												$('<img>').attr('src',thumbnail)).append(
													$('<br>')).append(
														$('<a>').attr('href',link).attr("target","_blank").text(caption)).html();					
						$('#map_canvas').gmap('addMarker', {'position': position}).click(function(){
							 $('#map_canvas').gmap('openInfoWindow', {'content': info_window}, this);
						 	 $('#map_canvas').gmap('getMap').panTo(position);
						});							
					}
				});
				if (list_visible) {
					$("#element_list").listview("refresh");	
				}
				
				$(".search-button").removeClass("ui-disabled");
				$(".result-count").html("<?php echo _('Mostrando'); ?> " + showing + "<?php echo _(' resultados para #'); ?>" + tag );

			});

		}

		function locationSuccess(position) {			
			var lat = position.coords.latitude;
			var lng = position.coords.longitude;
			var center = new google.maps.LatLng(lat,lng);

			$("#map_canvas").gmap({'center':center,'zoom':5});
			getPhotos();
		}

		function locationError(error) {
			$("#map_canvas").gmap({'zoom':2});	
			getPhotos();
		}

		$("#map").live("pagecreate",function(){
			if (navigator.geolocation) {				
				navigator.geolocation.getCurrentPosition(
					locationSuccess, locationError, {enableHighAccuracy: true});
			}
		});

		$("#list").live("pageshow",function(){
			getPhotos();
		});

		$(document).on('click', '[data-role="navbar"] a', function () {
            $.mobile.changePage($(this).attr("data-href"), {
                transition: "none",
                changeHash: false
        	});        	
		    return false;
		});

		$('#mySelect').live('change', function(e) {
			window.location.href = '?lang='+e.target.options[e.target.selectedIndex].value;
		});

	</script>
   </head>
   <body>
	<div id="map" data-role="page">
		<div data-role="header">
			<h1>Instagram Api</h1>
		</div>
		<div data-role="navbar">
			<ul>
				<li><a href="#" data-href="#map" class="ui-btn-active"> <?php echo _("Mapa"); ?> </a></li>
				<li><a href="#" data-href="#list"><?php echo _("Listado"); ?></a></li>
			</ul>
		</div>
		<div data-role="content">
			<fieldset class="ui-grid-a">
			    <div class="ui-block-a">
					<select id="mySelect">
					   <option <?php echo ($lang1); ?> >Español</option>
					   <option <?php echo ($lang2); ?> >English</option>
					   <option <?php echo ($lang3); ?> >中國的</option>
					</select>
			    </div>
			    <div class="ui-block-b">
			    	<input type="text" class="tag" name="tag"/>
			    </div>
			    <div class="ui-block-c">
			        <a href="javascript:getPhotos();" data-role="button" data-icon="check" data-inline="true" class="search-button"><?php echo _("Buscar"); ?></a>
			    </div>
			</fieldset>
			<div class="result-count"></div>			
			<br/>
			<div class="ui-bar-c ui-corner-all ui-shadow" style="padding:1em; background: #2a3333;">
				<div id="map_canvas" style="height:350px"></div>
			</div>
		</div>		
	</div>

	<div id="list" data-role="page">
		<div data-role="header">
			<h1>Instagram Api</h1>
		</div>
		<div data-role="navbar">
			<ul>
				<li><a href="#" data-href="#map"> <?php echo _("Mapa"); ?> </a></li>
				<li><a href="#" data-href="#list" class="ui-btn-active"><?php echo _("Listado"); ?></a></li>
			</ul>
		</div>
		<div data-role="content">
			<fieldset class="ui-grid-a">
			    <div class="ui-block-a">
			    	<input type="text" class="tag" name="tag"/>
			    </div>
			    <div class="ui-block-b">
			        <a href="javascript:getPhotos();" data-role="button" data-icon="check" data-inline="true" class="search-button"><?php echo _("Buscar"); ?></a>
			    </div>
			</fieldset>

			<div class="result-count"></div>			
			<br/>
			<ul data-role="listview" id="element_list"></ul>			
		</div>
	</div>

   </body>   
</html>
