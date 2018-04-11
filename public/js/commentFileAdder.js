/*
Ce programme doit être adapté pour tenir compte du fichier html spécifique

*/
function addFileInput(){
	
	// je cherche l'élément correspondant à #comment_files
	
	var prototype = $('#comment_files').data('prototype');
	var count = $('#comment_files > div').length;
	

	// je crée une nouvelle variable et je remplace le pattern __name__ par le nombre d'élément
	// que j'ai déjà compté ci-dessus
	var newForm = prototype.replace(/__name__/g, count);
	
	
	var group = $('input', newForm).parent();
	
	// j'ajoute le group que je viens de construire au bloc
	$('#comment_files').append(group);
}

// I create the new button

var button = $('<button>Add file</button>');

// Je n'utilise pas bootstrap => je n'ai pas besoin de ce code de Matthieu
// button.addClass('btn btn-success');

button.attr('type', 'button');
button.on('click', addFileInput);

// je met derrière le bouton que je viens de créer.
$("#comment_files").after(button);