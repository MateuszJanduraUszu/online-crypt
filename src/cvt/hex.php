<?php
// hex.php

// Copyright (c) Mateusz Jandura. All rights reserved
// SPDX-License-Identifier: Apache-2.0

namespace mjx {
    function _Get_padding_size($_Str) {
        $_As_array = str_split($_Str);
        $_Size     = 0;
        foreach ($_As_array as $_Ch) {
            if (ord($_Ch) == 0x30) { // "0" code point from the ASCII table
                ++$_Size;
            } else {
                break;
            }
        }

        return $_Size;
    }
    
    function _Code_point_to_hex_chunk($_Code_point) : string {
        // convert a 1-byte code point to a 2-byte hexadecimal chunk
        return dechex(ord($_Code_point));
    }

    function _Code_point_from_hex_chunk($_Chunk) : string {
        // convert a 2-byte hexadecimal chunk to a 1-byte code point
        return chr(hexdec($_Chunk));
    }

    class hex {
        static function encode($_Data) : string {
            return hex::_Encode($_Data, strlen($_Data), " ", 0);
        }

        static function encode_aligned($_Data, $_Count) : string {
            $_As_hex = hex::_Encode($_Data, strlen($_Data), "0", $_Count);
            return hex::_Align_to($_As_hex, $_Count, "0");
        }

        static function decode($_Data) : string {
            $_Size = strlen($_Data);
            if ($_Size == 0) { // empty string, do nothing
                return "";
            }

            $_Result  = "";
            $_Padding = _Get_padding_size($_Data);
            if ($_Padding > 0) { // remove padding
                $_Data = substr($_Data, $_Padding);
            }

            for ($_Idx = 0; $_Idx < $_Size; $_Idx += 2) {
                $_Result .= _Code_point_from_hex_chunk(substr($_Data, $_Idx, 2));
            }

            return $_Result;
        }

        private static function _Encode($_Data, $_Data_size, $_Padding, $_Padding_size) : string {
            if ($_Data_size == 0) { // empty string, do nothing
                return "";
            }
            
            $_Result = "";
            for ($_Idx = 0; $_Idx < $_Data_size; ++$_Idx) {
                $_Result .= _Code_point_to_hex_chunk($_Data[$_Idx], $_Padding, $_Padding_size, STR_PAD_LEFT);
            }

            return $_Result;
        }
        
        private static function _Align_to($_Str, $_Size, $_Padding) {
            // aligns _Str to _Padding bytes
            return str_pad($_Str, $_Size, $_Padding, STR_PAD_LEFT);
        }
    }
} // namespace mjx
?>