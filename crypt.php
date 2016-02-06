<?php

error_reporting( 0 );

$key = [
    'a' => "92",
    'b' => "52",
    'c' => "42",
    'd' => "32",
    'e' => "22",
    'f' => "12",
    'g' => "02",
    'h' => "61",
    'i' => "81",
    'j' => "71",
    'k' => "91",
    'l' => "51",
    'm' => "41",
    'n' => "31",
    'o' => "21",
    'p' => "11",
    'q' => "01",
    'r' => "6",
    's' => "8",
    't' => "7",
    'u' => "9",
    'v' => "5",
    'w' => "4",
    'x' => "3",
    'y' => "2",
    'z' => "1"
];

/**
 * @param $number
 * @return bool|null|string
 */
function convert_number_to_words($number) {

    $hyphen      = '-';
    $conjunction = ' and ';
    $separator   = ', ';
    $negative    = 'negative ';
    $decimal     = ' point ';
    $dictionary  = array(
        0                   => 'zero',
        1                   => 'one',
        2                   => 'two',
        3                   => 'three',
        4                   => 'four',
        5                   => 'five',
        6                   => 'six',
        7                   => 'seven',
        8                   => 'eight',
        9                   => 'nine',
        10                  => 'ten',
        11                  => 'eleven',
        12                  => 'twelve',
        13                  => 'thirteen',
        14                  => 'fourteen',
        15                  => 'fifteen',
        16                  => 'sixteen',
        17                  => 'seventeen',
        18                  => 'eighteen',
        19                  => 'nineteen',
        20                  => 'twenty',
        30                  => 'thirty',
        40                  => 'fourty',
        50                  => 'fifty',
        60                  => 'sixty',
        70                  => 'seventy',
        80                  => 'eighty',
        90                  => 'ninety',
        100                 => 'hundred',
        1000                => 'thousand',
        1000000             => 'million',
        1000000000          => 'billion',
        1000000000000       => 'trillion',
        1000000000000000    => 'quadrillion',
        1000000000000000000 => 'quintillion'
    );

    if (!is_numeric($number)) {
        return false;
    }

    if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
        // overflow
        trigger_error(
            'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
            E_USER_WARNING
        );
        return false;
    }

    if ($number < 0) {
        return $negative . convert_number_to_words(abs($number));
    }

    $string = $fraction = null;

    if (strpos($number, '.') !== false) {
        list($number, $fraction) = explode('.', $number);
    }

    switch (true) {
        case $number < 21:
            $string = $dictionary[$number];
            break;
        case $number < 100:
            $tens   = ((int) ($number / 10)) * 10;
            $units  = $number % 10;
            $string = $dictionary[$tens];
            if ($units) {
                $string .= $hyphen . $dictionary[$units];
            }
            break;
        case $number < 1000:
            $hundreds  = $number / 100;
            $remainder = $number % 100;
            $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
            if ($remainder) {
                $string .= $conjunction . convert_number_to_words($remainder);
            }
            break;
        default:
            $baseUnit = pow(1000, floor(log($number, 1000)));
            $numBaseUnits = (int) ($number / $baseUnit);
            $remainder = $number % $baseUnit;
            $string = convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
            if ($remainder) {
                $string .= $remainder < 100 ? $conjunction : $separator;
                $string .= convert_number_to_words($remainder);
            }
            break;
    }

    if (null !== $fraction && is_numeric($fraction)) {
        $string .= $decimal;
        $words = array();
        foreach (str_split((string) $fraction) as $number) {
            $words[] = $dictionary[$number];
        }
        $string .= implode(' ', $words);
    }

    return $string;
}

/**
 * @param str $string
 * @return str
 */
function encrypt( $string = "", $spacer = "/" ) {
    global $key;
    $return = "";
    $string = preg_replace("/[0-9]+/e", "convert_number_to_words($0)", $string);
    foreach ( str_split( $string ) as $letter ) {
        $return .= ( $key[ $letter ] ?: $letter ) . $spacer;
    }
    $return = str_replace( $spacer . " " . $spacer, " ", $return );
    $return = str_replace( "\n$spacer\n", "\n", $return );
    return trim( $return, $spacer );
}

/**
 * @param str $string
 * @return str
 */
function decrypt( $string = "", $spacer = "/" ) {
    global $key;
    $flip_key = array_flip( $key );
    $return = "";
    foreach ( explode( " ", $string ) as $word ) {
        foreach ( explode( $spacer, $word ) as $letter ) {
            $return .= $flip_key[ $letter ] ?: $letter;
        }
        $return .= " ";
    }
    return trim( $return, " " );
}

$return = [ 'success' => "false" ];

if ( isset( $_REQUEST[ 'to_encrypt' ] ) || isset( $_REQUEST[ 'to_decrypt' ] ) ) {
    if ( $_REQUEST[ 'to_encrypt' ] != "" && $_REQUEST[ 'to_decrypt' ] == "" ) {
        $return[ 'success' ] = true;
        $return[ 'encrypted' ] = encrypt( $_REQUEST[ 'to_encrypt' ] );
    } else if ( $_REQUEST[ 'to_encrypt' ] == "" && $_REQUEST[ 'to_decrypt' ] != "" ) {
        $return[ 'success' ] = true;
        $return[ 'decrypted' ] = decrypt( $_REQUEST[ 'to_decrypt' ] );
    } else {
        $return[ 'error' ] = "both to_encrypt and to_decrypt contain a string, please only post one or the other";
    }
} else {
    $return[ 'error' ] = "neither to_encrypt or to_decrypt have been set, please post one or the other";
    $return[ 'request' ] = $_REQUEST;
}

print json_encode( $return );

exit;

?>