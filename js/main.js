var test = document.createElement('input');
if (!'placeholder' in test) {
   $('input').each(function() {
       if ($(this).attr('placeholder') != "" && this.value=="") {
           $(this).val($(this).attr('placeholder')).css('color', 'grey')
                  .on({
                      focus: function() {
						console.log('focus');
                          if (this.value==$(this).attr('placeholder')) {
                              $(this).val("").css('color', '#000');
						  }
                      },
                      blur: function() {
						console.log('blur');
                          if (this.value=="") {
                              $(this).val($(this).attr('placeholder'))
                                     .css('color', 'grey');
                          }
                      }
                  });
       }
   });
}

$('.wysiwygx').wysihtml5({
   useLineBreaks:        false,
	"font-styles": true, //Font styling, e.g. h1, h2, etc. Default true
	"emphasis": true, //Italics, bold, etc. Default true
	"lists": true, //(Un)ordered lists, e.g. Bullets, Numbers. Default true
	"html": true, //Button which allows you to edit the generated HTML. Default false
	"link": true, //Button to insert a link. Default true
	"image": true //Button to insert an image. Default true
});