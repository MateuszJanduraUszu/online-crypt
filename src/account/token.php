<?php declare(strict_types = 1);
// token.php

// Copyright (c) Mateusz Jandura. All rights reserved
// SPDX-License-Identifier: Apache-2.0

namespace mjx {
    // Note: Privileges description:
    //       - basic: allows using limited algorithms
    //       - unlimited algorithms: allows using all supported algorithms
    //       - unlimited data size: allows hashing/encrypting/decrypting file with unlimited size
    //       - user management: allows suspending/resuming/deleting user account and
    //                          granting/removing permissions from the other users
    //       - administrator management: same as the user management but also applies to the administrators
    const basic_privileges                    = 0x000A;
    const unlimited_algorithms_privileges     = 0x00FA;
    const unlimited_data_size_privileges      = 0x00FE;
    const user_management_privileges          = 0xFAFE;
    const administrator_management_privileges = 0xFFFF;

    // Note: Tokens description:
    //       - guest: not registered user
    //       - user: registered user
    //       - premium: registered user with premium
    //       - administrator: registered user with premium and user management privileges
    //       - root administrator: registered user with premium and user/administrator management privileges
    const guest_token              = basic_privileges;
    const user_token               = basic_privileges | unlimited_algorithms_privileges;
    const premium_token            = basic_privileges | unlimited_algorithms_privileges | unlimited_data_size_privileges;
    const administrator_token      = basic_privileges | unlimited_algorithms_privileges
        | unlimited_data_size_privileges | user_management_privileges;
    const root_administrator_token = basic_privileges | unlimited_algorithms_privileges
        | unlimited_data_size_privileges | user_management_privileges | administrator_management_privileges; 

    function _Check_privilege(int $_Privilege) : bool {
        switch ($_Privilege) {
        case basic_privileges:
        case unlimited_algorithms_privileges:
        case unlimited_data_size_privileges:
        case user_management_privileges:
        case administrator_management_privileges:
            return true;
        default: // invalid privilege
            return false;
        }
    }

    function _Check_token(int $_Token) : bool {
        switch ($_Token) {
        case guest_token:
        case user_token:
        case premium_token:
        case administrator_token:
        case root_administrator_token:
            return true;
        default: // invalid token
            return false;
        }
    }

    function _Valid_or_none_token(int $_Token) : int {
        return _Check_token($_Token) ? $_Token : 0;
    }

    class token {
        function __construct(int $_Token = 0) {
            $this->_Myval = $_Token;
        }

        function valid() : bool {
            return $this->_Myval != 0;
        }

        function value() : int {
            return $this->_Myval;
        }

        function reset(int $_New_token = 0) : void {
            $this->_Myval = _Valid_or_none_token($_New_token);
        }

        function has_privilege(int $_Privilege) : bool {
            if ($this->_Myval == 0) { // invalid token
                return false;
            }

            return _Check_privilege($_Privilege) ? ($this->_Myval & $_Privilege) == $_Privilege : false;
        }

        function to_string() : string {
            return $this->_Myval == 0 ? "" : strval($this->_Myval);
        }

        private int $_Myval;
    }
} // namespace mjx
?>