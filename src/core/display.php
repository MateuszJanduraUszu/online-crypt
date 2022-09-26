<?php declare(strict_types = 1);
// display.php

// Copyright (c) Mateusz Jandura. All rights reserved.
// SPDX-License-Identifier: Apache-2.0

namespace mjx {
    $_Root_path = dirname(__FILE__, 2);
    require_once $_Root_path . "/core/invoke.php";

    function insert_text(string $_Id, string $_Text) : void {
        _Invoke(
            'document.getElementById("' . $_Id . '").innerHTML = "' . $_Text . '";'
        );
    }

    function assign_text(string $_Id, string $_Text) : void {
        _Invoke(
            'document.getElementById("' . $_Id . '").value = "' . $_Text . '";'
        );
    }

    function show_object(string $_Id, string $_Display = "block") : void {
        _Invoke(
            'document.getElementById("' . $_Id . '").style.display = "' . $_Display . '";'
        );
    }

    function show_object_for(string $_Id, int $_Duration, string $_Display = "block") : void {
        _Invoke(
            'let _Elem          = document.getElementById("' . $_Id . '");
            _Elem.style.display = "' . $_Display . '";
            setTimeout(() => {
                _Elem.style.display = "none";
            }, ' . $_Duration * 1000 . ');'
        );
    }

    function hide_object(string $_Id) : void {
        _Invoke(
            'document.getElementById("' . $_Id . '").style.display = "none";'
        );
    }

    function _Show_all_objects(array $_Ids) : void {
        foreach ($_Ids as $_Id) {
            show_object($_Id);
        }
    }

    function _Hide_all_objects(array $_Ids) : void {
        foreach ($_Ids as $_Id) {
            hide_object($_Id);
        }
    }
} // namespace mjx
?>