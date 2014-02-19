$(function() { 

	var   json = $('html').data('json')
		, mdsLinkPath = json.mdsLinkPath
		, absolutePath = json.absolutePath
		;
		
	$('code').each(function(i, e) {hljs.highlightBlock(e)});
	
	// modify relative <a> links
	$('#md-content a').each(function() {
		var   href = $(this).attr('href') ? $(this).attr('href') : false
			, isAbsoluteLink = href ? href.match(/^(\/|http).*/) : false
			;
			
		if ( !href || isAbsoluteLink ) return;  // do nothing
		
		var	  isFile = href.match(/(.*)\.([a-zA-Z]{2,4})$/)
			, filename = isFile ? isFile[1] : ''
			, extension = isFile ? isFile[2] : ''
			, isMDfile = extension == "md" ? true : false
			, isDoc = extension.match(/pdf|doc|docx|txt$/) ? true : false
			, hasHash = href.match(/(.*#.*)$/)
			, hashInCurrentDoc = href.match(/^#.*/)
			;
						
		if ( isMDfile ) $(this).attr('href',mdsLinkPath+'/'+filename+'.md');
		else if ( isDoc ) $(this).attr('href',absolutePath+'/'+href);
		else if ( hasHash && !hashInCurrentDoc ) $(this).attr('href',mdsLinkPath+hasHash[1]);
	});
	
	// modify relative img nodes
	$('#md-content img').each(function() {
		var   src = $(this).attr('src') ? $(this).attr('src') : false
			, isAbsoluteLink = src ? src.match(/^(\/|http).*/) : false
			;
			
		if ( !src || isAbsoluteLink ) return;  // do nothing
					
		$(this).attr('src',absolutePath+'/'+src);		
						
	})
		
});