function formatDollar(input, showZero) {
    if (input == null || input == '') {
        return (typeof showZero !== 'undefined') ? '$0.00' : '';
    }
    if (typeof(input) == 'string')
        input = input.replace(/\$|,/g, '');

    var n = Number(input);
    return n.toLocaleString('en-EN', {style: 'currency', currency: 'USD', minimumFractionDigits: 2});
}