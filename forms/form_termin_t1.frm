	<form method="POST">
	<input type="hidden" name="action" value="%action_mode%">
	<input type="hidden" name="tp_key" value="%tp_key%">

	<table class="wp-list-table widefat fixed posts" cellspacing="0">
	<thead><tr>
	<th width="200px">&nbsp;</th><th>&nbsp;</th>
	</tr></thead>

	<tr><td>#EVENTNAME#</td><td><input type="text" size="45" name="tp_bez" value="%tp_bez%"></td></tr>
	<tr><td>#EVENTDESCRIPTION#</td><td><textarea cols="50" name="tp_beschreibung">%tp_beschreibung%</textarea></td></tr>
	<tr><td>#CREATORNAME#</td><td><input type="text" size="45" name="tp_ersteller" value="%tp_ersteller%"></td></tr>
	<tr><td>#CREATORMAIL#</td><td><input type="text" size="45" name="tp_mail" value="%tp_mail%"></td></tr>
	</table>
	<p>&nbsp</p>

	<table class="wp-list-table widefat fixed posts" cellspacing="0">
	<thead><tr>
	<th width="100px">&nbsp;</th><th width="100px">#DATE#</th><th width="100px">#TIME#</th><th></th>
	</tr></thead>
