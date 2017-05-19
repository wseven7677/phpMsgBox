$(window).on("load", function() {
	waterfall("#latestMsgBest");
	waterfall("#latestMsgRecent");
})

function waterfall(id) {
	var $msgBoxs = $(id + ">div");
	var w = $msgBoxs.eq(0).outerWidth();
	var cols;
	if($(window).width() >= 992) {
		cols = 4;
	} else if($(window).width() >= 768) {
		cols = 2;
	} else {
		return;
	}
	var hArr = [];
	$msgBoxs.each(function(index, value) {
		var h = $msgBoxs.eq(index).outerHeight();
		if(index < cols) {
			hArr[index] = h;
		} else {
			var minH = Math.min.apply(null, hArr);
			var minHindex = $.inArray(minH, hArr);
			$(value).css({
				"position": "absolute",
				"top": minH + "px",
				"left": minHindex * w + "px"
			});
			hArr[minHindex] += $msgBoxs.eq(index).outerHeight();
		}
	});
	$(id).css({
		"height": Math.max.apply(null, hArr) +"px"
	});
}
