<?php declare(strict_types = 1);
// invoke.php

// Copyright (c) Mateusz Jandura. All rights reserved
// SPDX-License-Identifier: Apache-2.0

namespace mjx {
    function _Invoke(string $_Code) : void { // invokes JavaScript code
        echo '
            <script type="text/javascript">
            ' . $_Code . '
            </script>
        ';
    }
} // namespace mjx
?>