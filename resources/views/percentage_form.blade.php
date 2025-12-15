<!-- resources/views/percentage_form.blade.php -->

<form method="post" class="form-group">
	@csrf
	<label for="string1">String 1:</label>
	<input type="text" name="string1" id="string1">
	<br>
	<label for="string2">String 2:</label>
	<input type="text" name="string2" id="string2">
	<br>
	<button type="submit">Submit</button>
</form>