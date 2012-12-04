
var announcementsUI = {};

announcementsUI.prioritySlider = function() {

    var startValue = $( '#priorityValue' ).val();

    $( '#prioritySlider' ).slider( {
        min: 0,
        max: 100,
        value: startValue,
        step: 5,
        slide: function( event, ui ) {
            $( '#priorityValue' ).val( ui.value );
            $( '#priorityValueView' ).text( ui.value );
        }
    } );

};

announcementsUI.initEventDate = function() {

    $( "#eventDate" ).datepicker( {
        changeMonth: true,
        changeYear: true,
        minDate: 0,
        onClose: function( dateText ) {
            if( dateText!='' ) {
                $( '#beginDate, #endDate' ).removeAttr( 'disabled' );
                $( '#beginDate, #endDate' ).datepicker( "option", 'maxDate', dateText );
                $( '#endDate' ).val( dateText );
            } else {
                $( '#beginDate, #endDate' ).attr( 'disabled', 'disabled' );
                $( '#beginDate, #endDate' ).val( '' );
            }
        }
    } );
};

announcementsUI.initBeginDate = function() {

    $( "#beginDate, #endDate" ).datepicker( {
        changeMonth: true,
        changeYear: true,
        minDate: 0
    } );
};

announcementsUI.getPrioritySlider = function( aId, value ) {

    $( '#prioritySlider-' + aId ).slider( {
        min:0,
        max:100,
        value:value,
        step:5,
        slide:function ( event, ui ) {
            $( '#priorityValue-' + aId ).val( ui.value );
            $( '#priorityValueView-' + aId ).text( ui.value );
        }
    } );
    $( '#savePriorityButton-' + aId ).show();
};

announcementsUI.savePriority = function( aId ) {

    value = $( '#priorityValue-' + aId ).val();
    ajaxer.query( '/adm/announcements/savepriority/' + aId + '/' + value + '/' );
};

announcementsUI.editCategory = function( cId ) {

    if( $( '#ann-category-' + cId + '-edit' ).css( 'display' ) == 'none' ) {
        $( '#ann-category-' + cId ).slideUp( 'fast' );
        $( '#ann-category-' + cId + '-edit' ).slideDown( 'fast' );
    } else {
        $( '#ann-category-' + cId + '-edit' ).slideUp( 'fast' );
        $( '#ann-category-' + cId ).slideDown( 'fast' );
    }
};

announcementsUI.editAdditionals = function ( cId ) {

    if( $( '#addField-' + cId + '-edit' ).css( 'display' ) == 'none' ) {
        $( '#addField-' + cId ).slideUp( 'fast' );
        $( '#addField-' + cId + '-edit' ).slideDown( 'fast' );
    } else {
        $( '#addField-' + cId + '-edit' ).slideUp( 'fast' );
        $( '#addField-' + cId ).slideDown( 'fast' );
    }
};

// стартуем
$( document ).ready( function () {

    announcementsUI.initEventDate();
    announcementsUI.initBeginDate();
    announcementsUI.prioritySlider();
} );

$( document ).bind( 'construct', announcementsUI.initEventDate );
$( document ).bind( 'construct', announcementsUI.initBeginDate );
$( document ).bind( 'construct', announcementsUI.prioritySlider );

