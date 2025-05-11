<form id="myForm" method="post">
  <label for="age">Enter Age:</label>
  <input type="number" id="age" name="age" />
  <div class="input-group-append">
  <button class="btn btn-secondary" type="button" onclick="onclick_map_calculate_button(event)"><i class="fas fa-calculator"></i></button>
 </div>
  <button type="submit">Submit</button>
</form>

<script>

function onclick_map_calculate_button(event){
        var dbp = $('#dbp').val();
        var sbp = $('#sbp').val();
        var map = roundNumber((((parseFloat(dbp,10)*2)+parseFloat(sbp,10))/3),0);
        $('#map').val(Number.isNaN(map) ? '':map);
    }

document.getElementById('myForm').addEventListener('submit', function(e) {
  var ageInput = document.getElementById('age');
  var age = parseInt(ageInput.value, 10);

  if (isNaN(age) || age <= 0) {
    alert('Please enter a valid positive integer for age.');
    e.preventDefault(); // stop form from submitting
  } else {
    // Optionally process the value here before submission
    console.log("Ready to submit age:", age);
  }
});
</script>