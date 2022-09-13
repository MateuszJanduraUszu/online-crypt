<?php declare(strict_types = 1);
// formatted_string.php

// Copyright (c) Mateusz Jandura. All rights reserved
// SPDX-License-Identifier: Apache-2.0

namespace mjx {
    class formatted_string { // base class for formatted string 
        function __construct(string $_Str, int $_Size) {
            $this->_Mystr  = $_Str;
            $this->_Mysize = $_Size;
        }

        function get() : string | null {
            return $this->_Mysize != 0 ? $this->_Mystr : null;
        }

        function size() : int {
            return $this->_Mysize;
        }

        function empty() : bool {
            return $this->_Mysize == 0;
        }

        function clear() : void {
            $this->_Mystr  = "";
            $this->_Mysize = 0;
        }

        protected function _Assign(string $_New_str, int $_New_size) {
            $this->_Mystr  = $_New_str;
            $this->_Mysize = $_New_size;
        }

        private string $_Mystr;
        private int $_Mysize;
    }

    class _String_and_size { // stores a string and its size 
        function __construct(string $_Str, int $_Size) {
            $this->_Str  = $_Str;
            $this->_Size = $_Size;
        }

        public string $_Str;
        public int $_Size;
    }
} // namespace mjx
?>