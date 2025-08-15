(function($){
let seasonIndex = 0;

function addSeason() {
let template = $('#amcb-season-template').html();
template = template.replace(/__index__/g, seasonIndex).replace(/__number__/g, seasonIndex + 1);
$('#amcb_seasons_container').append(template);
seasonIndex++;
}

$(document).on('click', '#amcb_add_season', function(e){
e.preventDefault();
addSeason();
});

$(document).on('click', '.amcb-remove-season', function(){
$(this).closest('.amcb-season-row').remove();
});

$(function(){
addSeason();
});
})(jQuery);
