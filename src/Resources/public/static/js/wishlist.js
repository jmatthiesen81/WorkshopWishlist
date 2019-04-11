

$(function(){
    var modalUrl = null;

    function loadModalContent(url, clb){
        $.get(url, function(html) {
            clb(html);
        });
    }

    $('#wishlistModalButton').click(function(event) {
        event.preventDefault();
        this.blur();
        modalUrl = this.href;

        // Intial Load
        loadModalContent(modalUrl,function(html){
            $(html).appendTo('body').modal();
        });
    });

    $( "body" ).on( "click", '.jquery-modal .add-wishlist--button', function() {
        var $el = $(this);
        var $form = $el.parent('form');
        var formData = $form.serializeArray().reduce(function(a, x) { a[x.name] = x.value; return a; }, {});



        // post Form
        $.post($form.attr('action'), formData, function(html) {

            // Reload
            loadModalContent(modalUrl, function(html){
                $(html).appendTo('body').modal();
            });

        });


    });

});
