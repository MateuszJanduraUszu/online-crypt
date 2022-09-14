<?php declare(strict_types = 1);
// md5.php

// Copyright (c) Mateusz Jandura. All rights reserved
// SPDX-License-Identifier: Apache-2.0

namespace mjx {
    require_once "core/rotation.php";
    require_once "hash/salt.php";

    const _Md5_shift = array(
        7, 12, 17, 22, 7, 12, 17, 22, 7, 12, 17, 22, 7, 12, 17, 22, 5, 9, 14, 20, 5,
        9, 14, 20, 5, 9, 14, 20, 5, 9, 14, 20, 4, 11, 16, 23, 4, 11, 16, 23, 4, 11, 16,
        23, 4, 11, 16, 23, 6, 10, 15, 21, 6, 10, 15, 21, 6, 10, 15, 21, 6, 10, 15, 21
    );

    const _Md5_precomputed_table = array( // table with sines of integers (Radians)
        0xD76AA478, 0xE8C7B756, 0x242070DB, 0xC1BDCEEE, 0xF57C0FAF, 0x4787C62A,
        0xA8304613, 0xFD469501, 0x698098D8, 0x8B44F7AF, 0xFFFF5BB1, 0x895CD7BE,
        0x6B901122, 0xFD987193, 0xA679438E, 0x49B40821, 0xF61E2562, 0xC040B340,
        0x265E5A51, 0xE9B6C7AA, 0xD62F105D, 0x02441453, 0xD8A1E681, 0xE7D3FBC8,
        0x21E1CDE6, 0xC33707D6, 0xF4D50D87, 0x455A14ED, 0xA9E3E905, 0xFCEFA3F8,
        0x676F02D9, 0x8D2A4C8A, 0xFFFA3942, 0x8771F681, 0x6D9D6122, 0xFDE5380C,
        0xA4BEEA44, 0x4BDECFA9, 0xF6BB4B60, 0xBEBFBC70, 0x289B7EC6, 0xEAA127FA,
        0xD4EF3085, 0x04881D05, 0xD9D4D039, 0xE6DB99E5, 0x1FA27CF8, 0xC4AC5665,
        0xF4292244, 0x432AFF97, 0xAB9423A7, 0xFC93A039, 0x655B59C3, 0x8F0CCC92,
        0xFFEFF47D, 0x85845DD1, 0x6FA87E4F, 0xFE2CE6E0, 0xA3014314, 0x4E0811A1,
        0xF7537E82, 0xBD3AF235, 0x2AD7D2BB, 0xEB86D391
    );

    function _Append_md5_padding(string $_Data) : string {
        $_Data .= chr(0x80); // append a single 1 bit
        while (((strlen($_Data) + 8) % 64) != 0) {
            $_Data .= chr(0x0); // append a padding (always 0)
        }

        return $_Data;
    }

    function md5(string $_Data, string $_Salt = null, int $_Salt_size = 0) : string {
        if ($_Salt != null && $_Salt_size != 0) { // salt the data before the whole process
            $_Data = salt_data($_Data, $_Salt, $_Salt_size);
        }

        list($_Ax, $_Bx, $_Cx, $_Dx) = [0x67452301, 0xEFCDAB89, 0x98BADCFE, 0x10325476]; // MD5 magic state
        $_Size_as_bits               = strlen($_Data) * 8;
        $_Data                       = _Append_md5_padding($_Data);

        // break data into 64-byte chunks
        $_Chunks = str_split($_Data, 64);
        foreach ($_Chunks as $_Chunk) {
            list($_Ax2, $_Bx2, $_Cx2, $_Dx2) = [$_Ax, $_Bx, $_Cx, $_Dx];
            $_Words                          = str_split($_Chunk, 4); // break chunk into 32-bit words
            foreach ($_Words as $_Idx => $_Chars) {
                $_Chars = str_split($_Chars); // for array_reverse()
                $_Chars = array_reverse($_Chars); // convert to little-endian
                $_Word  = "";
                foreach ($_Chars as $_Ch) {
                    $_Word .= sprintf("%08b", ord($_Ch)); // append a single word as a 8-byte binary string
                }

                $_Words[$_Idx] = bindec($_Word);
            }
            
            if (count($_Words) < 16) { // set bits for the last element (incomplete block)
                $_Words[] = 0x00000000FFFFFFFF & $_Size_as_bits; // set lower 32 bits from the input size
                $_Words[] = 0xFFFFFFFF00000000 & $_Size_as_bits; // set upper 32 bits from the input size
            }

            for ($_Iter = 0; $_Iter < 64; ++$_Iter) { // 4 rounds (16 steps each)
                if ($_Iter < 16) { // cycle 0 to 15 (F function)
                    $_Fx = ($_Bx & $_Cx) | (~$_Bx & $_Dx); // (B and C) or ((not B) and D)
                    $_Gx = $_Iter;
                } else if ($_Iter < 32) { // cycle 16 to 31 (G function)
                    $_Fx = ($_Dx & $_Bx) | (~$_Dx & $_Cx); // (D and B) or ((not D) and C)
                    $_Gx = (5 * $_Iter + 1) % 16; // (5xi + 1) mod 16
                } else if ($_Iter < 48) { // cycle 32 to 47 (H function)
                    $_Fx = $_Bx ^ $_Cx ^ $_Dx; // B xor C xor D
                    $_Gx = (3 * $_Iter + 5) % 16; // (3xi + 5) mod 16
                } else { // cycle 48 to 63 (I function)
                    $_Fx = $_Cx ^ ($_Bx | ~$_Dx); // C xor (B or (not D))
                    $_Gx = (7 * $_Iter) % 16; // (7xi) mod 16
                }
 
                $_Temp = $_Dx;
                $_Dx   = $_Cx;
                $_Cx   = $_Bx;
                $_Bx   = $_Bx + int32_rotation::left(($_Ax + $_Fx + _Md5_precomputed_table[$_Iter] + $_Words[$_Gx])
                    & 0xFFFFFFFF, _Md5_shift[$_Iter]); // trim to 32 bits
                $_Ax   = $_Temp;
            }

            $_Ax += $_Ax2 & 0xFFFFFFFF; // trim to 32 bits
            $_Bx += $_Bx2 & 0xFFFFFFFF;
            $_Cx += $_Cx2 & 0xFFFFFFFF;
            $_Dx += $_Dx2 & 0xFFFFFFFF;
        }

        // pack 4 32-byte integers into 1 binary value
        $_Bin = pack("V4", $_Ax, $_Bx, $_Cx, $_Dx);
        return bin2hex($_Bin);
    }
} // namespace mjx
?>