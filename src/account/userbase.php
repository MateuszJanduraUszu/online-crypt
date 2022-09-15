<?php declare(strict_types = 1);
// userbase.php

// Copyright (c) Mateusz Jandura. All rights reserved
// SPDX-License-Identifier: Apache-2.0

namespace mjx {
    require_once "account/token.php";
    require_once "cvt/hex.php";
    require_once "hash/md5.php";

    use mysqli;
    use mysqli_result;

    class _Query_invoker { // invokes the mysqli_query() function multiple times
        function __construct(mysqli $_Handle, string $_Command) {
            $this->_Myhandle  = $_Handle;
            $this->_Mycommand = $_Command;
        }        

        function _Invoke() : mysqli_result | bool {
            return $this->_Myhandle->query($this->_Mycommand);
        }

        private mysqli $_Myhandle;
        private string $_Mycommand;
    }

    class account_data {
        function __construct() {
            $this->login    = "";
            $this->password = "";
            $this->active   = false;
            $this->token    = new token;
            $this->salt     = "";
        }

        public string $login;
        public string $password;
        public bool $active;
        public token $token;
        public string $salt; // 16-byte unique random value
    }

    class userbase {
        function __construct() {
            $this->_Myok    = false;
            $this->_Mytable = "";
        }

        function __destruct() {
            if ($this->is_open()) {
                $this->close();
            }
        }

        static function current() : userbase {
            static $_Obj = new userbase;
            return $_Obj;
        } 

        function open(string $_Name = "online_crypt", string $_Table = "users",
            string $_Host = "localhost", string $_Login = "root", string $_Password = null) : bool {
            if ($this->is_open()) { // some userbase is already open
                return false;
            }

            $this->_Myhandle = new mysqli($_Host, $_Login, $_Password);
            if ($this->_Myhandle->connect_errno != 0) {
                return false;
            }

            $this->_Myok = true;
            $_Command    = "USE " . $_Name . ";";
            if (!$this->_Myhandle->query($_Command)) { // database not found
                $this->close();
                return false;
            }

            $this->_Mytable = $_Table;
            return true;
        }

        function close() : void {
            if ($this->_Myok) {
                $this->_Myhandle->close();
                $this->_Myok    = false;
                $this->_Mytable = "";
            }
        }

        function is_open() : bool {
            return $this->_Myok;
        }

        function has_account(string $_Login) : bool {
            if (!$this->is_open()) { // no userbase is open
                return false;
            }

            $_Command = "SELECT * FROM " . $this->_Mytable . " WHERE login = \"" . $_Login . "\";";
            return $this->_Myhandle->query($_Command)->num_rows > 0;
        }

        function load_account_data(string $_Login) : account_data | null {
            if (!$this->is_open()) { // no userbase is open
                return null;
            }

            $_Command       = "SELECT * FROM " . $this->_Mytable . " WHERE login = \"" . $_Login . "\";";
            $_Invoker       = new _Query_invoker($this->_Myhandle, $_Command);
            $_Invoke_result = $_Invoker->_Invoke();
            if ($_Invoke_result->num_rows == 0) { // use not found
                return null;
            }

            $_Result           = new account_data;
            $_Result->login    = $_Login;
            $_Result->password = $_Invoke_result->fetch_column(1);
            $_Invoke_result    = $_Invoker->_Invoke();
            $_Result->active   = boolval($_Invoke_result->fetch_column(2));
            $_Invoke_result    = $_Invoker->_Invoke();
            $_Result->token->reset(intval($_Invoke_result->fetch_column(3)));
            $_Invoke_result = $_Invoker->_Invoke();
            $_Result->salt  = $_Invoke_result->fetch_column(4);
            return $_Result;
        }

        function create_account(
            string $_Login, string $_Password, bool $_Active, token $_Token, string $_Salt = null) : bool {
            if ($this->has_account($_Login)) { // account already exists
                return false;
            }

            if ($_Salt == null) { // generate a new salt
                $_Salts = $this->_Load_all_salts();
                for (;;) { // keep generating until the salt is unique
                    $_Salt = generate_salt();
                    if (!in_array($_Salt, $_Salts)) { // generated unique salt, use it
                        break;
                    }
                }
            }

            $_Password = md5($_Password, $_Salt, 16); // default salt size (16 bytes)
            $_Salt     = hex::encode($_Salt); // store as a hexadecimal string
            $_Command  = "INSERT INTO " . $this->_Mytable . " (login, password, active, token, salt) values (\""
                . $_Login . "\", \"" . $_Password . "\", " . $_Active . ", "
                    . $_Token->to_string() . ", \"" . $_Salt . "\");";
            return $this->_Myhandle->query($_Command);
        }

        function delete_account(string $_Login) : bool {
            if (!$this->has_account($_Login)) { // account not found, do nothing
                return true;
            }

            return $this->_Myhandle->query(
                "DELETE FROM " . $this->_Mytable . " WHERE login = \"" . $_Login . "\";");
        }

        function change_account_login(string $_Login, string $_New_login) : bool {
            if (!$this->has_account($_Login)) { // account not found
                return false;
            }

            if ($_Login == $_New_login) { // nothing has changed, do nothing
                return true;
            }

            $_Command = "UPDATE " . $this->_Mytable . " SET login = \"" . $_New_login
                . "\" WHERE login = \"" . $_Login . "\";";
            return $this->_Myhandle->query($_Command);    
        }

        function change_account_password(string $_Login, string $_New_password) : bool {
            if (!$this->has_account($_Login)) { // account not found
                return false;
            }

            $_Data         = $this->load_account_data($_Login);
            $_Salt         = hex::decode($_Data->salt);
            $_New_password = md5($_New_password, $_Salt, 16); // default salt size (16 bytes)
            if ($_New_password == $_Data->password) { // nothing has changed, do nothing
                return true;
            }

            $_Command = "UPDATE " . $this->_Mytable . " SET password = \"" . $_New_password
                . "\" WHERE login = \"" . $_Login . "\";";
            return $this->_Myhandle->query($_Command);    
        }

        function activate_account(string $_Login) : bool {
            if (!$this->has_account($_Login)) { // account not found
                return false;
            }

            return $this->_Myhandle->query("UPDATE " . $this->_Mytable
                . " SET active = true WHERE login = \"" . $_Login . "\";");
        }

        function deactivate_account(string $_Login) : bool {
            if (!$this->has_account($_Login)) { // account not found
                return false;
            }

            return $this->_Myhandle->query("UPDATE " . $this->_Mytable
                . " SET active = false WHERE login = \"" . $_Login . "\";");
        }

        function change_account_token(string $_Login, token $_New_token) : bool {
            if (!$this->has_account($_Login)) { // account not found
                return false;
            }

            return $this->_Myhandle->query("UPDATE " . $this->_Mytable
                . " SET token = " . $_New_token->to_string() . " WHERE login = \"". $_Login . "\";");
        }

        private function _Load_all_salts() : array {
            if (!$this->is_open()) { // no userbase is open
                return array();
            }

            $_Query_result = $this->_Myhandle->query("SELECT salt FROM " . $this->_Mytable . ";");
            $_Count        = $_Query_result->num_rows;
            if ($_Count == 0) { // salt not found
                return array();
            }

            $_Result = array_fill(0, $_Count, "");
            for ($_Idx = 0; $_Idx < $_Count; ++$_Idx) {
                $_Result[$_Idx] = implode($_Query_result->fetch_row());
            }

            return $_Result;
        }

        private mysqli $_Myhandle; // handle to the current userbase
        private string $_Mytable; // the current table name
        private bool $_Myok; // true if the userbase is open
    }
} // namespace mjx
?>