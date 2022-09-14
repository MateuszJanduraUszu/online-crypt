<?php declare(strict_types = 1);
// salt.php

// Copyright (c) Mateusz Jandura. All rights reserved
// SPDX-License-Identifier: Apache-2.0

namespace mjx {
    function generate_salt(int $_Size = 16) : string {
        return random_bytes($_Size);
    }

    function _Salt_chunk(string $_Chunk, string $_Salt, int $_Salt_size) : string {
        $_Chunk = str_pad($_Chunk, $_Salt_size, "\x80"); // align chunk to the salt size
        for ($_Idx = 0; $_Idx < $_Salt_size; ++$_Idx) {
            $_Chunk_code_point = ord($_Chunk[$_Idx]);
            $_Salt_code_point  = ord($_Salt[$_Idx]);
            $_Chunk[$_Idx]     = chr(($_Chunk_code_point ^ $_Salt_code_point) & 0xFF); // trim to 8 bits
        }

        return $_Chunk;
    }

    function salt_data(string $_Data, string $_Salt, int $_Salt_size) : string {
        $_Chunks = str_split($_Data, $_Salt_size); // for iteration
        $_Result = "";
        foreach ($_Chunks as &$_Chunk) { // salt each chunk
            $_Chunk   = _Salt_chunk($_Chunk, $_Salt, $_Salt_size);
            $_Result .= $_Chunk;
        }

        return $_Result;
    }
} // namespace mjx
?>