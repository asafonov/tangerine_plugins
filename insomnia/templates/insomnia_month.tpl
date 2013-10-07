<script src="http://dl.dropbox.com/u/70578687/code/js/cgl5/lib/plot2d.js"></script>
<script src="http://dl.dropbox.com/u/70578687/code/js/cgl5/lib/core.js"></script>
<h2>{year}-{month}</h2>
<p><canvas id="insomnia" width="480" height="320" style="background-color:#333333;"></canvas></p>
<div id="insomnia_text"></div>
<script>
{data}
for (var i=0; i<spam.length; i++) {
    document.getElementById('insomnia_text').innerHTML += '{year}-{month}-'+(i+1)+' - <a href="/admin/insomnia/{year}/{month}/'+(i+1)+'">'+spam[i]+'</a><br>';
}
bar2D('insomnia', spam, '#00ff66');
</script>