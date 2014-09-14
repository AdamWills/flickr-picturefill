<script type="text/javascript">
	
	jQuery(document).ready(function($) {
		console.log();

		var apikey = $('#flickr_api_key').val();
	    var userid = $('#flickr_user_id').val();
	    var url = 'https://api.flickr.com/services/rest/?method=flickr.people.getPublicPhotos&api_key=' + apikey + '&user_id='+userid+'&format=json&per_page=50&nojsoncallback=1';
	    $.getJSON(url,

	    function (data) {
	    	console.log(data.photos);
	        $.each(data.photos.photo, function (i, item) {
	            var purl = 'http://farm' + item.farm + '.static.flickr.com/' + item.server + '/' + item.id + '_' + item.secret + '_m.jpg';
	            var pid = item.id;
	            var container = '<li class="attachment save-ready">';
	            container += '<div class="attachment-preview js--select-attachment type-image subtype-jpeg portrait">';
	            	container += '<div class="thumbnail">';
	            		container += '<div class="centered">';
            				container += '<img class="span4" alt="' + item.title + '" src="' + purl + '">';
        				container += '</div>';
    				container += '</div>';
				container += '</div>';
				container += '<a class="check" href="#" title="Deselect" tabindex="0"><div class="media-modal-icon"></div></a>';
				container += '</li>';
	            $(container).appendTo('#images');
	        });
	    });

	    $('#images').on('click', '.attachment' ,function() {
	    	console.log('fired');
	    	$(this).toggleClass('selected').toggleClass('details');
	    });

	});

</script>

<div class="embed-media-settings">	
	<ul id="images" class="attachments"></div>
	<input type="hidden" id="flickr_api_key" name="api_key" value="<?php echo get_option('flickr_api_key'); ?>">
	<input type="hidden" id="flickr_user_id" name="flickr_userid" value="<?php echo get_option('flickr_user_id'); ?>">
</div>



