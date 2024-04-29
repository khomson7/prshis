<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<form action="result.php" method="post">
  Answer 1 <input type="radio" name="ans" value="ans1" /><br />
  Answer 2 <input type="radio" name="ans" value="ans2"  /><br />
  Answer 3 <input type="radio" name="ans" value="ans3"  /><br />
  Answer 4 <input type="radio" name="ans" value="ans4"  /><br />
  <input type="button" value="submit" onclick="sendPost()" />
 </form>
 <script type="text/javascript">
    function sendPost(){
        var value = $('input[name="ans"]:checked').val();

        if (value == undefined) {
          console.log('no_value');
        } else {
          console.log(value);
        }
        //window.location.href = "sendpost.php?ans="+value;
    };
 </script>