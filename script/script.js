let editor;
var inputs = $("#input");
var activities = document.getElementById("lanaguage");

window.onload = function() {
    editor = ace.edit("editor");
    editor.setTheme("ace/theme/cobalt");
    editor.session.setMode("ace/mode/python");
}

$("#run").click(function(){
    $.ajax({
        url: "server.php",
        method: "POST",
        data: {
            code: editor.getSession().getValue(),
            input: inputs.val(),
            type: activities.value
        },
        success: function(response){
            $(".code_output").html(response);
        }
    })

})

activities.addEventListener("change", function() {
    editor.session.setMode("ace/mode/"+activities.value);
  
});