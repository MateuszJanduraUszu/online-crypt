<?php declare(strict_types = 1);
// error.php

// Copyright (c) Mateusz Jandura. All rights reserved
// SPDX-License-Identifier: Apache-2.0

namespace mjx {
    function disable_error_reporting() : void {
        error_reporting(0);
    }
} // namespace mjx
?>