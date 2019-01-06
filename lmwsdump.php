#!/usr/bin/php
<?php

/*
lmwsdump
Script extractor for Langrisser Millennium WS: The Last Century.
Version:   1.0
Author:    Derrick Sobodash <derrick@sobodash.com>
Copyright: (c) 2019 Derrick Sobodash
Web site:  https://github.com/sobodash/lmwsdump/
License:   BSD License <http://opensource.org/licenses/bsd-license.php>
*/


$rom = "resources/lmws.ws";
$tbl = "resources/lmws.tbl.txt";

// Script regions
$sc_start = array(0xa0282, 0xa08f9, 0xa205b, 0xa2a6e, 0xa3cbc, 0xa490e,
                  0xa58ef, 0xa692d, 0xa7d47, 0xa9938, 0xaa672, 0xab5bf, 
                  0xaca5a, 0xadae2, 0xb02d4, 0xb10cd, 0xb219b, 0xb30b2,
                  0xb5021, 0xb611b, 0xb7307, 0xb8937, 0xc0365, 0xc2545,
                  0xc3d5e, 0xc4bce, 0xc5c8e, 0xc7ca3, 0xc89a0, 0xdceba,
                  0xdeabe, 0xeb94a);
$sc_end   = array(0xa038a, 0xa1e43, 0xa2779, 0xa39b0, 0xa45fe, 0xa56c0,
                  0xa666b, 0xa7949, 0xa97d1, 0xaa746, 0xab2e0, 0xac80d,
                  0xad778, 0xaf630, 0xb0da0, 0xb1f17, 0xb2bef, 0xb4e22,
                  0xb5eed, 0xb6fef, 0xb86e7, 0xb971b, 0xc2229, 0xc3aaf, 
                  0xc496c, 0xc5848, 0xc7a60, 0xc875d, 0xc945a, 0xde6d0,
                  0xdf530, 0xed68c);

// Load table to memory
$table = array();
$fd = explode("\n", trim(file_get_contents($tbl)));
for($i = 0; $i < count($fd); $i++) {
  $x = explode("=", $fd[$i]);
  $y = hexdec($x[0]);
  $table[($y >> 8) + (($y & 0xff) << 8)] = $x[1];
}

// Add control codes
$table[0x0000] = " "; // Space
$table[0xfffb] = "<string>";
$table[0xfffc] = "<int>";
$table[0xfffe] = "\n";
$table[0xfffd] = "<var>";
$table[0xffff] = "<end>\n\n";

// Cleanup
unset($i, $fd, $x, $y);

// Dump script
$fd = fopen($rom, "rb");
$fo = fopen("lmws.txt", "w");

for($i = 0; $i < count($sc_start); $i++) {
  fseek($fd, $sc_start[$i]);
  // Note region
  fputs($fo, "\nDumping 0x" . dechex($sc_start[$i]) . "-" .
              "0x" . dechex($sc_end[$i]) . "\n");
  while(ftell($fd) < $sc_end[$i]) {
    $char = ord(fgetc($fd)) + (ord(fgetc($fd)) << 8);
    if(isset($table[$char]))
      fputs($fo, $table[$char]);
    else
      fputs($fo, "\\x" . dechex($char >> 8) . "\\x" . dechex($char & 0xff));
  }
}
fclose($fo);
fclose($fd);

?>
