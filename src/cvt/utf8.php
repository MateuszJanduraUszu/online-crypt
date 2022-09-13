<?php declare(strict_types = 1);
// utf8.php

// Copyright (c) Mateusz Jandura. All rights reserved
// SPDX-License-Identifier: Apache-2.0

namespace mjx {
    require_once "core/formatted_string.php";

    function _Get_next_utf8_chunk_size($_Word) : int {
        $_Code_point = ord($_Word);
        if ($_Code_point <= 0x7F) { // 1 byte per word
            return 1;  
        } else if ($_Code_point >= 0xC0 && $_Code_point < 0xE0) { // 2 bytes per word
            return 2;
        } else { // 3 bytes per word
            return 3;
        }
    }

    function _Encode_utf8_word($_Word) : string {
        $_Result     = "";
        $_Code_point = ord($_Word);
        if ($_Code_point <= 0x7F) { // 1 byte per word
            $_Result .= $_Word;
        } else if ($_Word > 0x7F && $_Code_point < 0x800) { // 2 bytes per word
            $_Result .= chr(($_Code_point >> 6) | 0xC0);
            $_Result .= chr(($_Code_point & 0x3F) | 0x80);
        } else { // 3 bytes per word
            $_Result .= chr(($_Code_point >> 0xC) | 0xE0);
            $_Result .= chr((($_Code_point >> 6) & 0x3F) | 0x80);
            $_Result .= chr(($_Code_point & 0x3F) | 0x80);
        }

        return $_Result;
    }

    function _Decode_utf8_chunk(string $_Chunk) : string {
        $_Result     = "";
        $_Code_point = ord($_Chunk[0]);
        if ($_Code_point <= 0x7F) { // 1 byte per word
            $_Result .= $_Chunk[0];
        } else if ($_Code_point >= 0xC0 && $_Code_point < 0xE0) { // 2 bytes per word
            $_Code_point2 = ord($_Chunk[1]);
            $_Result     .= chr((($_Code_point & 0x1F) << 6) | ($_Code_point2 & 0x3F));
        } else { // 3 bytes per word
            $_Code_point2 = ord($_Chunk[1]);
            $_Code_point3 = ord($_Chunk[2]);
            $_Result     .= chr(
                (($_Code_point & 0xF) << 0xC) | (($_Code_point2 & 0x3F) << 6) | ($_Code_point3 & 0x3F));
        }

        return $_Result;
    }

    class utf8 { // conversion between the ASCII/Unicode and the UTF-8
        static function encode(string $_Data) : string {
            $_As_array = str_split($_Data); // for iteration
            $_Result   = "";
            foreach ($_As_array as $_Ch) {
                $_Result .= _Encode_utf8_word($_Ch);
            }

            return $_Result;
        }

        static function decode(string $_Data) : string {
            $_Size       = strlen($_Data);
            $_Result     = "";
            $_Chunk_size = 0;
            for ($_Idx = 0; $_Idx < $_Size;) {
                $_Chunk_size = _Get_next_utf8_chunk_size($_Data[$_Idx]);
                $_Result    .= _Decode_utf8_chunk(substr($_Data, $_Idx, $_Chunk_size));
                $_Idx       += $_Chunk_size;
            }

            return $_Result;
        }
    }

    class utf8_string extends formatted_string { // stores string in a UTF-8 format
        function __construct(string $_Str) {
            $_Data = $this->_Convert($_Str); // assign converted string
            parent::__construct($_Data->_Str, $_Data->_Size);
        }

        function assign(string $_New_str) : void {
            $_Data = $this->_Convert($_New_str);
            $this->_Assign($_Data->_Str, $_Data->_Size);
        }

        private function _Convert(string $_Str) : _String_and_size {
            $_Converted = utf8::encode($_Str);
            return new _String_and_size($_Converted, strlen($_Converted));
        }
    }
} // namespace mjx
?>