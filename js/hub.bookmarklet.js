(function(){
	var v = "1.3.2";

	if(window.jQuery === undefined || window.jQuery.fn.jquery < v) {
		var done = false;
		var script = document.createElement("script");
		script.src = "http://ajax.googleapis.com/ajax/libs/jquery/" + v + "/jquery.min.js";
		script.onload = script.onreadystatechange = function(){
			if(!done && (!this.readyState || this.readyState == "loaded" || this.readyState == "complete")) {
				done = true;
				initMyBookmarklet();
			}
		};
		document.getElementsByTagName("head")[0].appendChild(script);
	}
	else {
		initMyBookmarklet();
	}
	
	function initMyBookmarklet() {
		(window.myBookmarklet = function() {
			if($("#hubframe").length == 0) {
				var title = document.title.replace("'", "");
				
				$("body").append("\
				<div id='hubframe'>\
					<div id='hubframe_loading' style=''>\
						<p>Loading...</p>\
					</div>\
					<iframe src='http://ip/load.php?page=BookmarkletWishlistAdd&title="+encodeURIComponent(title)+"' onload=\"$('#hubframe iframe').slideDown(500);$('#hubframe_loading').remove();\">Enable iFrames.</iframe>\
					<style type='text/css'>\
						#hubframe_loading { display: none; position: fixed; width: 100%; height: 100%; top: 0; left: 0; background-color: rgba(0, 0, 0, .75); cursor: pointer; z-index: 900; }\
						#hubframe_loading p { color: white; font: normal normal bold 20px/20px Helvetica, sans-serif; position: absolute; top: 50%; left: 50%; width: 10em; margin: -10px auto 0 -5em; text-align: center; }\
						#hubframe iframe { display: none; background-color: #2E436E; position: fixed; top: 0; left: 0; width: 100%; height: 50px; z-index: 999; border-bottom: 5px solid #323E56; margin: -5px 0 0 -5px; }\
					</style>\
				</div>");
				$("#hubframe_loading").fadeIn(750);
			}
			else {
				$("#hubframe_loading").fadeOut(750);
				$("#hubframe iframe").slideUp(500);
				setTimeout("$('#hubframe').remove()", 750);
			}
			
			$("html").click(function(event){
				$("#hubframe iframe").slideUp(500);
				setTimeout("$('#hubframe').remove()", 750);
			});
		})();
	}
})();