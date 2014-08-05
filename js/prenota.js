/* Ottiene la classe dal selettore e attiva o disattiva gli elementi */
function getClassAndToggle() {
	 var classN = parseInt($(this).children(':selected').text());
	 toggleVm(classN);
}

/* Attiva e disattiva gli elementi in base a classN.
	Le attività senza posti disponibili rimangono disabilitate. */
function toggleVm(classN) {
	var disabled = (classN < 4 || !classN ? true : false);
	$("input.vm:not(.full)").prop('disabled', disabled);
	
	// Deseleziona gli elementi disabilitati
	$('input[type="radio"]:disabled').prop('checked', false);
}

function getPopoverContent() {
	return $(this).children('.description-wrapper').html();
}

$(function(){
	/* Callback per il selettore della classe */
	$('#class-selector').change(getClassAndToggle);
	
	/* Di default le attività "VM18" sono disattivate */
	toggleVm(1);
	
	/* Popover per descrizioni attività */
	$('.popover_activity').popover({
  		trigger: 'hover',
  		container: 'body',
  		html: true,
  		content: getPopoverContent,
  		animation: false,
	});
});

/* Google Analytics */
var _gaq = _gaq || [];
_gaq.push(['_setAccount', 'UA-16776751-4']);
_gaq.push(['_trackPageview']);

(function() {
var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();
