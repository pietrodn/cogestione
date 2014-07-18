$(function(){
	// Initial state
	setAutomaticMode($('#automatic-switch').prop("checked"));
	
	$('#automatic-switch').change(function(){
		setAutomaticMode(1);
	});
	
	$('#manual-switch').change(function(){
		setAutomaticMode(0);
	});
	
	$('#confermaTruncate').change(function(){
		$('#delete-single-reservation').toggle($(this).checked);
	});
});

function setAutomaticMode(flag) {
	$('#automatic-panel').toggle(flag);
	$('#manual-panel').toggle(!flag);
}