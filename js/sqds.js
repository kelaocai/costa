$(function() {
    $("#wizard").scrollable({
        onSeek : function(event, i) {
            alert('seek');
        },
        onBeforeSeek : function(event, i) {
            alert('before seek '+i);
        }
    });
    
}); 