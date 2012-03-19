$('a[id|=DownloadMultipleTorrent]').each(function() {
	$(this).qtip({
		content: {
			text: '<img src="images/spinners/ajax-light.gif" alt="Loading..." />',
			ajax: {
				url: $(this).attr('rel')
			},
		 },
		 position: {
			at: 'bottom center', // Position the tooltip above the link
			my: 'top left',
			viewport: $(window), // Keep the tooltip on-screen at all times
			effect: false, // Disable positioning animation
			container: $('#maincontent')
		 },
		 show: {
			event: 'click',
			solo: true // Only show one tooltip at a time
		 },
		 hide: 'unfocus',
		 style: {
			classes: 'ui-tooltip-shadow', 
			tip: {
				size: {
					x: 10,
					y: 5
				}
			}
		 }
	})
}).click(function(event) { event.preventDefault(); });

$('a[id|="DownloadTorrent"]').click(function(event) {
	AjaxLink(this);
});