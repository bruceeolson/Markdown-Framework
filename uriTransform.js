$(function() { 

	var   json = $('html').data('json')
		, docBaseUrl = json.linkPath
		;
		
	$('code').each(function(i, e) {hljs.highlightBlock(e)});
	
	// modify relative <a> links
	$('a').each(function() {
		var   href = $(this).attr('href')
			, relativeLink = href.slice(0,1) == '/' ? false : true
			;
		if ( relativeLink ) $(this).attr('href',docBaseUrl+'/'+href)
	});
	
	// modify relatvie <img> paths
	
});