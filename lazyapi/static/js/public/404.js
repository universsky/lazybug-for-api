$(document).ready(function() {
	id = setInterval("public_404_change_time()", 1000);
});

public_404_change_time = function() {
	var time = parseInt($("#div_number>span").text());
	if (time > 1) {
		$("#div_number>span").text(--time);
	} else {
		clearInterval(id);
		window.location.href = "/";
	}
}