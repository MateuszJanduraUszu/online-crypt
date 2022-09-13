<?php declare(strict_types = 1);
// hex.php

// Copyright (c) Mateusz Jandura. All rights reserved
// SPDX-License-Identifier: Apache-2.0

namespace mjx {
    require_once "core/formatted_string.php";

    function _Get_padding_size(string $_Str) : int {
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

    function _Code_point_from_hex_chunk(string $_Chunk) : string {
        // convert a 2-byte hexadecimal chunk to a 1-byte code point
        return chr(hexdec($_Chunk));
    }

    class hex {
        static function encode(string $_Data) : string {
            return hex::_Encode($_Data, strlen($_Data), " ", 0);
        }

        static function encode_aligned(string $_Data, int $_Count) : string {
            $_As_hex = hex::_Encode($_Data, strlen($_Data), "0", $_Count);
            return hex::_Align_to($_As_hex, $_Count, "0");
        }

        static function decode(string $_Data) : string {
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

        private static function _Encode(
            string $_Data, int $_Data_size, string $_Padding, int $_Padding_size) : string {
            if ($_Data_size == 0) { // empty string, do nothing
                return "";
            }
            
            $_Result = "";
            for ($_Idx = 0; $_Idx < $_Data_size; ++$_Idx) {
                $_Result .= _Code_point_to_hex_chunk($_Data[$_Idx], $_Padding, $_Padding_size, STR_PAD_LEFT);
            }

            return $_Result;
        }
        
        private static function _Align_to(string $_Str, int $_Size, string $_Padding) {
            // aligns _Str to _Padding bytes
            return str_pad($_Str, $_Size, $_Padding, STR_PAD_LEFT);
        }
    }

    class hex_string extends formatted_string { // stores string in a hexadecimal format
        function __construct(string $_Str) {
            $_Data = $this->_Convert($_Str); // assign converted string
            parent::__construct($_Data->_Str, $_Data->_Size);
        }

        function assign(string $_New_str) : void {
            $_Data = $this->_Convert($_New_str);
            $this->_Assign($_Data->_Str, $_Data->_Size);
        }

        private function _Convert(string $_Str) : _String_and_size {
            $_Converted = hex::encode($_Str);
            return new _String_and_size($_Converted, strlen($_Converted));
        }
    }
} // namespace mjx
?>