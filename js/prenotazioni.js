/* Ottiene la classe dal selettore */
function getClassAndToggle(selectItem) {
	 var classe = $(selectItem).children(':selected').text();
	 var arr = classe.split('', 1);
	 var classN = parseInt(arr[0]);
	 toggleVm(classN);
}

/* Attiva e disattiva gli elementi in base a classN.
	Le attività senza posti disponibili rimangono disabilitate. */
function toggleVm(classN) {
	var disabled = (classN < 4 || !classN ? true : false);
	$("input.vm:not(.full)")
		.prop('disabled', disabled); 
	$('.activity:has(input[type="radio"]:enabled)').removeClass('disabled'); 
	$('.activity:has(input[type="radio"]:disabled)').addClass('disabled').removeClass('selectedActivity');  
	$('input[type="radio"]:disabled').prop('checked', false);
}


$(function(){
	/* Di default le attività "VM18" sono disattivate */
	toggleVm(1);
	
	$('.activity').click(function(){
		if(! $('input[type="radio"]', this).prop('disabled')) {
			$('input[type="radio"]', this).prop('checked', 'checked');
		
			$('.activity.selectedActivity', $(this).parent()).removeClass('selectedActivity');
			$(this).addClass('selectedActivity');
		}
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
