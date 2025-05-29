;(function( $ ) {
	'use strict';

	function calculate() {
        const parameters = $('#calculator .parameters'),
              original = $('#calculator .original-data'),
              result = $('#calculator .calculation-result')
            
        let height = parameters.find('input[name="height"]').val(),
            width  = parameters.find('input[name="width"]').val(),
            length = parameters.find('input[name="length"]').val(),
            count  = parameters.find('input[name="count"]').val(),
            price  = parameters.find('input[name="price"]').val(),
            volumePiece = (height/1000) * (width/1000) * (length/1000), 
            volume = (Math.round((volumePiece * count) * 1000))/1000,
            quantityPack = (Math.round((1 / volumePiece) * 1000))/1000,
            amountPiece = volumePiece * price,
            totalAmount = volume * price,
            countSquare = (Math.round(((width/1000) * (length/1000) * count) * 1000))/1000
            
        // Указываем вводные данные
        original.find('.height b').text( height )
        original.find('.width b').text( width )
        original.find('.length b').text( length )
        original.find('.count b').text( count )
        original.find('.price b').text( price )
        
        // Указываем итоговые данные
        result.find('.volume-piece b').text( volumePiece.toFixed(4) )
        result.find('.volume b').text( volume.toFixed(3) )
        result.find('.count-cube b').text( quantityPack.toFixed(2) )
        result.find('.count-square b').text( quantityPack.toFixed(2) )
        result.find('.amount-piece b').text( amountPiece.toFixed(2) )
        result.find('.total-amount b').text( totalAmount.toFixed(2) )
        result.find('.count-square b').text( countSquare.toFixed(2) )
    }
    
    $().ready(function() {
        $('#calculator').on('click', 'button[data-action="calculate"]', function() {
            calculate()
        })
    })

})( jQuery );