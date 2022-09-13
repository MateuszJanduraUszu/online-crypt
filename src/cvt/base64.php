<?php declare(string_types = 1);
// base64.php

// Copyright (c) Mateusz Jandura. All rights reserved
// SPDX-License-Identifier: Apache-2.0

namespace mjx {
    require_once "core/formatted_string.php";
    require_once "cvt/utf8.php";

    const _Base64_table        = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
    const _Base64_table_size   = 65;
    const _Base64_padding_byte = 64;

    function _Find_index_by_word($_Word) : int {
        for ($_Idx = 0; $_Idx < _Base64_table_size; ++$_Idx) {
            if (_Base64_table[$_Idx] == $_Word) {
                return $_Idx;
            }
        }

        return -1;
    }

    function _Append_base64_round($_Ch1, $_Ch2, $_Ch3, $_Ch4) : string {
        return _Base64_table[$_Ch1] . _Base64_table[$_Ch2]
            . _Base64_table[$_Ch3] . _Base64_table[$_Ch4];
    }

    function _Is_base64_word($_Word) : bool {
        $_As_array = str_split(_Base64_table); // for iteration
        foreach ($_As_array as $_Elem) {
            if ($_Elem == $_Word) {
                return true;
            }
        }

        return false;
    }

    class base64 { // conversion between 8-bit and 6-bit code points
        static function encode(string $_Data) : string {
            $_Size = strlen($_Data);
            if ($_Size == 0) { // empty string, do nothing
                return "";
            }

            // Note: Each round takes 3 bytes from the data (8 bits per byte)
            //       and converts them into 4 words (6 bits per word). Then it maps
            //       them to its couterparts in the Base64 table. If the data is not
            //       divisible by 3, additional padding is added.
            $_Result = "";
            $_B1     = 0; // the first byte from the current chunk
            $_B2     = 0; // the second byte from the current chunk
            $_B3     = 0; // the third byte from the current chunk
            $_Ch1    = 0; // the first encoded character from the current chunk
            $_Ch2    = 0; // the second encoded character from the current chunk
            $_Ch3    = 0; // the third encoded character from the current chunk
            $_Ch4    = 0; // the fourth encoded character from the current chunk
            for ($_Idx = 0; $_Idx < $_Size; $_Idx += 3) {
                $_B1  = ord($_Data[$_Idx]);
                $_B2  = $_Idx + 1 >= $_Size ? 0 : ord($_Data[$_Idx + 1]); // avoid buffer overrun
                $_B3  = $_Idx + 2 >= $_Size ? 0 : ord($_Data[$_Idx + 2]); // avoid buffer overrun
                $_Ch1 = $_B1 >> 2;
                $_Ch2 = (($_B1 & 3) << 4) | ($_B2 >> 4);
                $_Ch3 = (($_B2 & 15) << 2) | ($_B3 >> 6);
                $_Ch4 = $_B3 & 63;
                if ($_Idx + 1 >= $_Size) { // 2-word padding
                    $_Ch3 = _Base64_padding_byte;
                    $_Ch4 = _Base64_padding_byte;
                } else if ($_Idx + 2 >= $_Size) { // 1-word padding
                    $_Ch4 = _Base64_padding_byte;
                }

                $_Result .= _Append_base64_round($_Ch1, $_Ch2, $_Ch3, $_Ch4);
            }

            return $_Result;
        }

        static function decode(string $_Data) : string {
            $_Size = strlen($_Data);
            if ($_Size == 0) { // empty string, do nothing
                return "";
            }

            if (!base64::_Is_base64($_Data, $_Size)) { // invalid data
                return "";
            }

            // Note: Each round takes 4 words from the data (6 bits per word)
            //       and converts them into 3 bytes (8 bits per byte).
            //       If the data contains padding, it is removed.
            $_Result = "";
            $_B1     = 0; // the first byte from the current chunk
            $_B2     = 0; // the second byte from the current chunk
            $_B3     = 0; // the third byte from the current chunk
            $_Ch1    = 0; // the first decoded character from the current chunk
            $_Ch2    = 0; // the second decoded character from the current chunk
            $_Ch3    = 0; // the third decoded character from the current chunk
            $_Ch4    = 0; // the fourth decoded character from the current chunk
            for ($_Idx = 0; $_Idx < $_Size; $_Idx += 4) {
                // Note: The _Find_index_by_word() returns -1 if the word not found.
                //       We don't have to worry about it because we already checked if
                //       the data has valid Base64 words.
                $_Ch1     = _Find_index_by_word($_Data[$_Idx]);
                $_Ch2     = _Find_index_by_word($_Data[$_Idx + 1]);
                $_Ch3     = _Find_index_by_word($_Data[$_Idx + 2]);
                $_Ch4     = _Find_index_by_word($_Data[$_Idx + 3]);
                $_B1      = ($_Ch1 << 2) | ($_Ch2 >> 4);
                $_B2      = (($_Ch2 & 15) << 4) | ($_Ch3 >> 2);
                $_B3      = (($_Ch3 & 3) << 6) | $_Ch4;
                $_Result .= chr($_B1);
                if ($_Ch3 != _Base64_padding_byte) { // at most 1-word padding
                    $_Result .= chr($_B2);
                }

                if ($_Ch4 != _Base64_padding_byte) { // no padding
                    $_Result .= chr($_B3);
                }
            }

            return $_Result;
        }

        static function is_base64(string $_Data) : bool {
            return base64::_Is_base64($_Data, strlen($_Data));
        }

        private static function _Is_base64(string $_Data, int $_Size) : bool {
            if ($_Size % 4 != 0) { // must be n*4
                return false;
            }

            for ($_Idx = 0; $_Idx < $_Size; ++$_Idx) {
                if (!_Is_base64_word($_Data[$_Idx])) {
                    return false;
                }
            }

            return true;
        }
    }

    class base64_string extends formatted_string { // stores string in a Base64 format
        function __construct(string $_Str) {
            $_Data = $this->_Convert($_Str); // assign converted string
            parent::__construct($_Data->_Str, $_Data->_Size);
        }

        function assign(string $_New_str) : void {
            $_Data = $this->_Convert($_New_str);
            $this->_Assign($_Data->_Str, $_Data->_Size);
        }
        
        private function _Convert(string $_Str) : _String_and_size {
            $_Converted = base64::encode($_Str);
            return new _String_and_size($_Converted, strlen($_Converted));
        }
    }
} // namespace mjx
?>