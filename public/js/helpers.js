function formatDollar(input, showZero) {
	if(input == null || input == '') {
		return (typeof showZero !== 'undefined') ? '$0.00' : '';
	}
	if(typeof(input) == 'string')
		input = input.replace(/\$/g, '');
	return "$" + parseFloat(input).toFixed(2);
}