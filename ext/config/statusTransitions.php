<?php
return [
    'Afwezig' => [ 'Transport', 'Ligt Klaar', 'Aanwezig', 'Gereserveerd', 'Overdatum' ],
    'Transport' => [ 'Afwezig', 'Ligt Klaar', 'Aanwezig', 'Gereserveerd', 'Overdatum' ],
    'Ligt Klaar' => [ 'Afwezig', 'Transport', 'Gereserveerd', 'Overdatum' ],
    'Aanwezig' => [ 'Afwezig', 'Transport', 'Overdatum' ],
    'Gereserveerd' => [ 'Afwezig', 'Ligt Klaar', 'Overdatum' ],
    'Overdatum' => [ 'Afwezig' ],
];