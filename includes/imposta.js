newActivityTemplate ?=

function addActivity()
{
	var lastID = parseInt($('#lastID').text());
	var newAct = $('div.activity', $(this).parent()).last().clone();
	newAct.children('input[id^="activity-title"]').val('titolo');
	newAct.children('input[id^="activity-vm"]').removeAttr('checked');
	newAct.children('input[id^="activity-max"]').val('');
	newAct.children('.notnew').remove();
	newAct.children('*[id^="activity-id"]').text(lastID);
	newAct.insertBefore($(this));
	
	$('#lastID').text(lastID+1)
}


$(function() {
    $(".addButton").click(addActivity);
});

/*

<div class="activity" id="activity-#ID#">
<input type="hidden" name="activity[#ID#][block]" value="6" />
<input type="text" size="1" name="activity[#ID#][id]" value="#ID#" />
<input type="text" size="35" id="activity-title-#ID#" name="activity[#ID#][title]" value="Crea: Parkour" id="activity-title-#ID#" />
<input type="text" size="1" id="activity-max-#ID#" name="activity[#ID#][max]" value="90" />
<input id="activity-vm-#ID#" name="activity[#ID#][vm]" type="checkbox" />
</div>

*/