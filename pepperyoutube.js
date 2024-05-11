document.addEventListener('DOMContentLoaded', function() {
	var pepperyoutubes = document.querySelectorAll('.pepperyoutube__consentButton');

	pepperyoutubes.forEach(function(pepperyoutube) {
		pepperyoutube.addEventListener('click', function() {
			var pepperyoutubeContainer = this.closest('.pepperyoutube');
			var pepperyoutubeVideoId = pepperyoutubeContainer.getAttribute('data-id');
			var pepperyoutubeIframe = document.createElement('iframe');
			
			pepperyoutubeIframe.setAttribute('class', 'pepperyoutube__video');
			pepperyoutubeIframe.setAttribute('src', 'https://www.youtube.com/embed/' + pepperyoutubeVideoId + '?rel=0&showinfo=0&autoplay=1&color=white&rel=0');
			pepperyoutubeIframe.setAttribute('frameborder', '0');
			pepperyoutubeIframe.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture');
			pepperyoutubeIframe.setAttribute('allowfullscreen', true);

			pepperyoutubeContainer.innerHTML = ''; 
			pepperyoutubeContainer.appendChild(pepperyoutubeIframe); 
		});
	});
});
