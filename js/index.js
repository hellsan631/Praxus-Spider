
$(document).ready(function(){

    $(".color").each(function() {

        var attr = "background: -webkit-gradient(linear, 50% 0%, 50% 100%, color-stop(0%, rgba(255, 255, 255, 0.3)), color-stop(100%, rgba(0, 0, 0, 0.4))), "+$(this).html()+";"+
                "background: -webkit-linear-gradient(rgba(255, 255, 255, 0.3) 0%, rgba(0, 0, 0, 0.4) 100%), "+$(this).html()+";"+
                "background: -moz-linear-gradient(rgba(255, 255, 255, 0.3) 0%, rgba(0, 0, 0, 0.4) 100%), "+$(this).html()+";"+
                "background: -o-linear-gradient(rgba(255, 255, 255, 0.3) 0%, rgba(0, 0, 0, 0.4) 100%),"+$(this).html()+";"+
                "background: linear-gradient(rgba(255, 255, 255, 0.3) 0%, rgba(0, 0, 0, 0.4) 100%), "+$(this).html()+";";

        $(this).attr("style", attr);

    });

    $(document).on("mousedown", "[id]", function (e) {

        disabledEventPropagation(e);

        if( e.which == 2 ) {
            var productLink = $('<a href="./index.php?pid='+$(this).attr('id')+'" />');

            productLink.attr("target", "_blank");
            window.open(productLink.attr("href"));

            return false;

        }else if( e.which == 1 ) {

            window.location.href = "./index.php?pid="+$(this).attr('id');

            return false;
        }

    });


});

function disabledEventPropagation(event){

    if (event.stopPropagation){

        event.stopPropagation();

    }else if(window.event){

        window.event.cancelBubble = true;

    }

}