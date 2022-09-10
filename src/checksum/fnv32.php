<?php
// fnv32.php

// Copyright (c) Mateusz Jandura. All rights reserved
// SPDX-License-Identifier: Apache-2.0

namespace mjx {
    class _Fnv32_params { // stores FNV-1/FNV-1a hash parameters
        const _Offset_basis = 0x811C9DC5;
        const _Prime        = 0x01000193;
    }

    function fnv132($_Data) : int {
        // see https://en.wikipedia.org/wiki/Fowler–Noll–Vo_hash_function for details
        $_As_array = str_split($_Data); // for iteration
        $_Result   = _Fnv32_params::_Offset_basis;
        foreach ($_As_array as $_Ch) {
            $_Result *= _Fnv32_params::_Prime;
            $_Result ^= ord($_Ch); // pass _Ch as a byte
            $_Result &= 0xFFFFFFFF; // trim to 32 bits
        }

        return $_Result;
    }

    function fnv1a32($_Data) : int {
        // see https://en.wikipedia.org/wiki/Fowler–Noll–Vo_hash_function for details
        $_As_array = str_split($_Data); // for iteration
        $_Result   = _Fnv32_params::_Offset_basis;
        foreach ($_As_array as $_Ch) {
            $_Result ^= ord($_Ch); // pass _Ch as a byte
            $_Result *= _Fnv32_params::_Prime;
            $_Result &= 0xFFFFFFFF; // trim to 32 bits
        }

        return $_Result;
    }
} // namespace mjx
?>