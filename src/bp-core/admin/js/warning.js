( function() {
    if ( typeof window.bpAdminRepairStrings === 'undefined' ) {
        return;
    }
    
    var formId = window.bpAdminRepairStrings.formId || null;

    if ( ! formId ) {
        return;
    }
    
    document.querySelector( '#' + formId ).addEventListener( 'submit', function( event ) {
        var warningList = [];
        
        for( e in event.target.elements ) {
            if ( 'undefined' !==  typeof event.target.elements[ e ].nodeName ) {
                if ( window.bpAdminRepairStrings.warnings[ event.target.elements[ e ].getAttribute( 'id' ) ] && event.target.elements[ e ].checked ) {
                    warningList.push( window.bpAdminRepairStrings.warnings[ event.target.elements[ e ].getAttribute( 'id' ) ] );
                }
            }
        }

        if ( ! warningList.length ) {
            return event;
        } else if ( ! confirm( window.bpAdminRepairStrings.title + "\n" + warningList.join( "\n" ) + "\n" + window.bpAdminRepairStrings.confirm ) ) {
            event.preventDefault();
            return;
        }

        return event;
    } );
} () );
