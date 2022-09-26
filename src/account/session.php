<?php declare(strict_types = 1);
// session.php

// Copyright (c) Mateusz Jandura. All rights reserved
// SPDX-License-Identifier: Apache-2.0

namespace mjx {
    $_Root_path = dirname(__FILE__, 2);
    require_once $_Root_path . "/account/userbase.php";

    class session {
        static function is_active() : bool {
            return session_status() == PHP_SESSION_ACTIVE;
        }

        static function id() : string {
            return session_id();
        }

        static function start() : bool {
            if (self::is_active()) { // session is already started
                return false;
            }

            return session_start();
        }

        static function terminate(bool $_Erase = true) : bool {
            if (!self::is_active()) { // no session is active
                return false;
            }

            if (session_destroy()) {
                if ($_Erase) { // erase all session data
                    session_unset();
                }

                return true;
            } else {
                return false;
            }
        }

        static function reset() : bool {
            if (!self::is_active()) { // no session is active
                return false;
            }

            return session_reset();
        }
    }

    class session_storage {
        static function has_stored_data() : bool {
            if (!session::is_active()) {
                return false;
            }

            return isset($_SESSION["login"]) && isset($_SESSION["password"])
                && isset($_SESSION["active"]) && isset($_SESSION["token"]) && isset($_SESSION["salt"]);
        }

        static function load_account_data() : account_data | null {
            if (!self::has_stored_data()) {
                return null;
            }

            $_Result           = new account_data;
            $_Result->login    = $_SESSION["login"];
            $_Result->password = $_SESSION["password"];
            $_Result->active   = $_SESSION["active"];
            $_Result->token->reset($_SESSION["token"]);
            $_Result->salt = $_SESSION["salt"];
            return $_Result;
        }

        static function store_account_data(account_data $_Data) : bool {
            if (!session::is_active()) {
                return false;
            }

            $_SESSION["login"]    = $_Data->login;
            $_SESSION["password"] = $_Data->password;
            $_SESSION["active"]   = $_Data->active;
            $_SESSION["token"]    = $_Data->token->value();
            $_SESSION["salt"]     = $_Data->salt;
            return true;
        }

        static function erase_account_data() : bool {
            if (!session::is_active()) {
                return false;
            }
 
            unset($_SESSION["login"]);
            unset($_SESSION["password"]);
            unset($_SESSION["active"]);
            unset($_SESSION["token"]);
            unset($_SESSION["salt"]);
            return true;
        }
    }
} // namespace mjx
?>