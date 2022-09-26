<?php declare(strict_types = 1);
// hex.php

// Copyright (c) Mateusz Jandura. All rights reserved
// SPDX-License-Identifier: Apache-2.0

namespace mjx {
    $_Root_path = dirname(__FILE__, 2);
    require_once $_Root_path . "/core/formatted_string.php";
    require_once $_Root_path . "/core/range.php";

    function _Code_point_to_hex_chunk(int $_Code_point, int $_Alignment = 2) : string {
        $_Chunk = dechex($_Code_point);
        if (strlen($_Chunk) == $_Alignment) { // chunk already aligned
            return $_Chunk;
        } else { // chunk not aligned, align it
            return str_pad($_Chunk, $_Alignment, "0", STR_PAD_LEFT);
        }
    }

    function _Code_point_from_hex_chunk(string $_Chunk) : int {
        return hexdec($_Chunk);
    }

    class hex { // manages conversion between decimal and hexadeciman system
        static function encode(string $_Data, int $_Alignment = 2) : string | null {
            if ($_Alignment == 0) { // alignment must be at least 1
                return null;
            }

            return self::_Encode($_Data, strlen($_Data), $_Alignment);
        }

        static function decode(string $_Data, int $_Alignment = 2) : string | null {
            if (!self::is_hex($_Data)) { // cannot decode non-hex data
                return null;
            }

            return self::_Decode($_Data, strlen($_Data), $_Alignment);
        }

        static function is_hex(string $_Data) : bool {
            $_As_array = str_split($_Data);
            foreach ($_As_array as $_Ch) {
                // check if $_Ch is a number, upper-case hex letter or lower-case hex letter
                $_Code_point = ord($_Ch);
                $_Is_num     = is_in_range(48, 57, $_Code_point); // [0, 9] from ASCII table
                $_Is_upper   = is_in_range(65, 70, $_Code_point); // [A, F] from ASCII table
                $_Is_lower   = is_in_range(97, 102, $_Code_point); // [a, f] from ASCII table
                if (!$_Is_num && !$_Is_upper && !$_Is_lower) { // non-hex number found
                    return false;
                }
            }

            return true;
        }

        static function _Encode(string $_Data, int $_Size, int $_Alignment) : string {
            if ($_Size == 0) { // empty string, do nothing
                return "";
            }

            $_Result = "";
            for ($_Idx = 0; $_Idx < $_Size; ++$_Idx) {
                $_Result .= _Code_point_to_hex_chunk(ord($_Data[$_Idx]), $_Alignment);
            }

            return $_Result;
        }

        static function _Decode(string $_Data, int $_Size, int $_Alignment) : string | null {
            if ($_Size == 0) { // empty string, do nothing
                return "";
            }

            if ($_Size % $_Alignment != 0) { // $_Data_size must be n*$_Alignment
                return null;
            }

            $_Chunks = str_split($_Data, $_Alignment);
            $_Result = "";
            foreach ($_Chunks as $_Chunk) {
                $_Result .= chr(_Code_point_from_hex_chunk($_Chunk));
            }

            return $_Result;
        }
    }

    class hex_string extends formatted_string { // stores string in a hexadecimal format
        function __construct(string $_Str, int $_Alignment = 2) {
            $_Data = self::_Convert($_Str, $_Alignment);
            parent::__construct($_Data->_Str, $_Data->_Size);
        }        

        function assign(string $_New_str, int $_Alignment = 2) : void {
            $_Data = self::_Convert($_New_str, $_Alignment);
            $this->_Assign($_Data->_Str, $_Data->_Size);
        }
        
        private static function _Convert(string $_Str, int $_Alignment) : _String_and_size {
            $_Cvt = hex::encode($_Str, $_Alignment);
            return $_Cvt != null ? // return $_Cvt and its size or an empty string and 0
                new _String_and_size($_Cvt, strlen($_Cvt)) : new _String_and_size("", 0);
        } 
    }
} // namespace mjx
?>