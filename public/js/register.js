function validate(userName){
	$.post(
			'/user/available', 				// le chemin   
			{								// l'argument
				username : userName 
			},
			// la callback function
			// Le retour sera récupéré dans responseData
			// vu que c'est un Json qui contient "available" et la valeur associée, je pourrai l'utiliser plus tard.
			function(responseData){
				console.log ("valeur reçue = " + responseData.available);
				$('.username-validation').remove();  // supprime tous les objets ayant la classe user-validation
				
				if(responseData.available){
					// si le pseudo est disponible, j'active le submit
					$('button[type="submit"]').prop('disabled', false);
					$('label[for="form_username"]').append(
						'<span class="username-available username-validation"> available</span>');
					return;
				}
				
				// si le pseudo est déjà utilisé, je désactive le submit
				$('button[type="submit"]').prop('disabled', true);
				$('label[for="form_username"]').append(
				'<span class="username-unavailable username-validation"> unavailable</span>');
			});
	}			
				


$('#form_username').on('keyup', function(){
	// console.log ("je valide " + $(this).val());
	validate($(this).val());
})