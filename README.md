<h2>Version History</h2>

Waiting for initial release!

<h2>Known Issues</h2>

* When all rows are deleted in a table, the table formatting breaks.
* The add/remove row icons fade-out when a client is selected. They should start off invisible.
* Unit Price and Line Total fields do not have dollar signs before their totals.
* Job Title positioning is not ideal. Could be placed upwards ~5-10px.
* Add/Remove row icons positioning is not ideal. Needs ~5px top padding.
* Input boxes still have default styling. Remove this styling to fit theme of other elements.
* Entire job is highlighted red if one line exceeds 255 characters. The single line should be highlighted instead.

<h2>Planned Features</h2>

* Restrict Part Code fields to only be editable up to one line.
* Check if selected client has an e-mail address within MYOB. If not, prompt to add one.
* Ability to submit multiple invoices with multiple PO numbers.
* Dynamically updated footer which displays how many invoices are currently selected and what the total amount of these invoices are combined.
* Ability to select/type specific item code within Code field. 	(Unable to implement until inventory is complete)
* Ability to error-check avaliable item quantity and codes. 	(Unable to implement until inventory is complete)

<h2>Limitations</h2>

* Can only invoice one job per one PO number.
* Can only invoice one client at one time.