<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <script type="text/javascript">
            function closethisasap() {
                document.forms["redirectpost"].submit();
            }
        </script>
    </head>
    <body onload="closethisasap();">
        <form name="redirectpost" method="POST" action="{{$action}}">
            <input type="hidden" id="data" name="data" value="{{json_encode($data)}}">
            <input type="hidden" id="msg" name="msg" value="{{$msg}}">
        </form>
    </body>
 </html>