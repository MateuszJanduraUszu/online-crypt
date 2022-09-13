<?php declare(strict_types = 1);
// adler32.php

// Copyright (c) Mateusz Jandura. All rights reserved
// SPDX-License-Identifier: Apache-2.0

namespace mjx {
    const _Adler32_mod = 65521;

    function adler32(string $_Data) : int {
        // see https://en.wikipedia.org/wiki/Adler-32 for details
        $_As_array = str_split($_Data); // for iteration
        $_Ax       = 1;
        $_Bx       = 0;
        foreach ($_As_array as $_Ch) {
            $_Ax = ($_Ax + ord($_Ch)) % _Adler32_mod;
            $_Bx = ($_Bx + $_Ax) % _Adler32_mod;
        }

        return ($_Bx << 16) | $_Ax;
    }
} // namespace mjx
?>