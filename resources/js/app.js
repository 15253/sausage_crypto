var post = {},
    $form,
    $decrypted,
    $encrypted,
    $submit;
function handleSubmit( event ) {
    event.preventDefault();
    $.each( $form.serializeArray(), function( _, kv ) {
        post[kv.name] = kv.value;
    } );
    $.get(
        'http://holman.org.uk/sausage_crypto/crypt.php',
        post,
        function ( data ) {
            data = JSON.parse( data );
            if ( data.success ) {
                if ( typeof data[ 'encrypted' ] != "undefined" ) {
                    $decrypted.val( '' );
                    $encrypted.val( data[ 'encrypted'].replace( "\n/\n", "\n" ) );
                }
                if ( typeof data[ 'decrypted' ] != "undefined" ) {
                    $encrypted.val( '' );
                    $decrypted.val( data[ 'decrypted' ] );
                }
                check();
            } else {
                alert( data.error );
            }
        }
    );
    return false;
}
function check() {
    $( 'textarea').each(
        function ( i ) {
            var $this = $( this );
            $this.val( $this.val().replace( /\n\/\n/g, "\n" ) );
            var lines = $this.val().split(/\r*\n/).length,
                addtional = 2;
            var extra = ( lines + addtional ) * 0.25;
            $this.animate( { height: ( lines + addtional + extra ) + "em" }, 250 );
        }
    );
    if ( $decrypted.val() == "" && $encrypted.val() == "" ) $submit.attr( "disabled", "disabled" );
    else $submit.removeAttr( "disabled" );
}
$( document ).ready(
    function() {
        $form = $( '#encrypt_decrypt' );
        $decrypted = $form.find( '#to_encrypt' ),
            $encrypted = $form.find( '#to_decrypt' ),
            $submit = $form.find( '#submit' );
        $( 'body' )
            .on(
                'submit',
                '#encrypt_decrypt',
                handleSubmit
            )
            .on(
                'keyup change',
                'textarea',
                function ( event ) {
                    if ( $( this ).val() != '' ) $( 'textarea' ).not( this).val( '' );
                    check();
                }
            )
            .on(
                'focus',
                'textarea',
                function ( event ) {
                    $( this ).select();
                }
            );
        check();
    }
);