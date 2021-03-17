$('#exampleModal').on('show.bs.modal', function (event) {
var button = $(event.relatedTarget) // Button that triggered the modal
var recipient = button.data('whatever') // Extract info from data-* attributes
// Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
var modal = $(this)
modal.find('.modal-title').text('New message to ' + recipient)
modal.find('.modal-body input').val(recipient)
});


$("#myInput").bind('input', function () {
$.ajax({
    url: "../topic/topic_handler.php",
    method: "POST",
    data: "function=search&name=" + $('#myInput').val(),
    success: function(result){
        if (result != "") {
            window.location = result;
        }
    }
});
});


function openNav(id) {
$.ajax({
    url: "../topic/topic_handler.php",
    method: "POST",
    data: "function=getInfo&id=" + id,
    success: function(result){
        $("#sideNavContent").html(result);
        document.getElementById("mySidenav").style.width = "550px";
        document.getElementById("main").style.marginLeft = "550px";
    }
});

}
function closeNav() {
document.getElementById("mySidenav").style.width = "0px";
document.getElementById("main").style.marginLeft= "0";
}

$(document).ready(function () {
var instance = new SlimSelect({
    select: '#prerequisite'

});
});