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
	
	/* Remembers the active tab after submitting */
	activeTab = $('#active-tab').text();
	$('#imposta-tab a[href="#' + activeTab + '"]').tab('show');
	
});

function setAutomaticMode(flag) {
	/* Don't use .toggle() because it causes a glitch in the animation. */
	if(flag) {
		$('#manual-panel').hide();
		$('#automatic-panel').show();
	} else {
		$('#automatic-panel').hide();
		$('#manual-panel').show();
	}
}