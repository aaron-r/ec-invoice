<h2>Version History</h2>

v0.9 - Initial release (05/09/2013).

<h2>Known Issues</h2>

* When all rows are deleted in a table, the table formatting breaks.
* The add/remove row icons do not fade-in initially when a job is edited. They display correctly after this though.
* Entire job is highlighted red if one line exceeds 255 characters. Just the single line should be highlighted instead.
* A blank 'misc' entry is still appended to the end of the invoice (even if it's the ONLY or LAST item).

<h2>Planned Features</h2>

* Nice-looking message box prompts to replace alert();
* Restrict the 'Notes' field to one-line when the 'CODE' field is not for technician notes.
* Check if selected client has an e-mail address within MYOB. If not, prompt to add one.
* Display invoice numbers to user in an expandable box when they are submitted successfully.
* Ability to select/type specific item code within Code field. 	(Unable to implement until inventory is complete)
* Ability to error-check avaliable item quantity and codes. 	(Unable to implement until inventory is complete)

<h2>Limitations</h2>

* Can only invoice one client at one time.